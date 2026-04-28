<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BackupController extends Controller
{
    /**
     * Display backup management page
     */
    public function index(): Response
    {
        // Get list of existing backups
        $backups = collect(Storage::disk('local')->files('backups'))
            ->filter(fn($file) => str_ends_with($file, '.sql'))
            ->map(function ($file) {
                return [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => Storage::disk('local')->size($file),
                    'created_at' => Storage::disk('local')->lastModified($file),
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        return Inertia::render('settings/Backup', [
            'backups' => $backups,
        ]);
    }

    /**
     * Create a new database backup
     */
    public function create()
    {
        try {
            // Generate backup filename with timestamp
            $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
            $backupPath = storage_path('app/backups/' . $filename);

            // Ensure backups directory exists on the configured disk
            if (!Storage::disk('local')->exists('backups')) {
                Storage::disk('local')->makeDirectory('backups');
            }

            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            Log::info('Backup create: initialized', [
                'database' => $database,
                'filename' => $filename,
            ]);

            // Use Laravel's DB facade to get all tables and export data
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $database;
            
            $header = "-- MySQL Database Backup\n";
            $header .= "-- Generated: " . now()->toDateTimeString() . "\n";
            $header .= "-- Database: {$database}\n\n";
            $header .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            $pdo = DB::getPdo();

            // Stream output to file to avoid building a huge string in memory
            $storagePath = Storage::disk('local')->path('backups/' . $filename);
            $handle = fopen($storagePath, 'w');
            if ($handle === false) {
                throw new \Exception('Gagal membuka file backup untuk ditulis');
            }

            // write header
            fwrite($handle, $header);

            Log::info('Backup create: header written', [
                'path' => $storagePath,
            ]);

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                
                // Get CREATE TABLE statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $tableHeader = "-- Table: {$tableName}\n";
                $tableHeader .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $tableHeader .= $createTable[0]->{'Create Table'} . ";\n\n";
                fwrite($handle, $tableHeader);
                
                // Get table data
                $rows = DB::table($tableName)->get();
                
                if ($rows->count() > 0) {
                    fwrite($handle, "-- Data for table {$tableName}\n");

                    foreach ($rows as $row) {
                            $values = [];
                            foreach ((array)$row as $value) {
                                if (is_null($value)) {
                                    $values[] = 'NULL';
                                } else {
                                    $values[] = $pdo->quote($value);
                                }
                            }

                            $columns = implode('`, `', array_keys((array)$row));
                            fwrite($handle, "INSERT INTO `{$tableName}` (`{$columns}`) VALUES (" . implode(', ', $values) . ");\n");
                        }

                    fwrite($handle, "\n");
                    // log progress for large tables optionally
                    Log::info('Backup create: table dumped', ['table' => $tableName, 'rows' => $rows->count()]);
                }
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            Log::info('Backup create: file closed', [
                'path' => $storagePath,
                'size' => is_file($storagePath) ? filesize($storagePath) : null,
            ]);

            // Log activity
            ActivityLogger::created(
                'system',
                null,
                $filename,
                "Admin membuat backup database: {$filename}"
            );

            return back()->with('success', 'Backup berhasil dibuat: ' . $filename);
        } catch (\Exception $e) {
            Log::error('Backup create failed', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return back()->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file
     */
    public function download(Request $request)
    {
        $filename = $request->input('filename');
        
        // Sanitize filename to prevent path traversal
        $filename = basename($filename);
        $filePath = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($filePath)) {
            Log::warning('Backup download: file not found', ['file' => $filePath]);
            abort(404, 'File backup tidak ditemukan.');
        }

        // Log activity
        ActivityLogger::created(
            'system',
            null,
            $filename,
            "Admin mengunduh backup database: {$filename}"
        );

        Log::info('Backup download: starting download', ['file' => $filePath, 'user_id' => optional(Auth::user())->id]);

        return Storage::disk('local')->download($filePath);
    }

    /**
     * Delete a backup file
     */
    public function destroy(Request $request)
    {
        $filename = $request->input('filename');
        $filePath = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($filePath)) {
            Log::warning('Backup delete: file not found', ['file' => $filePath]);
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        Storage::disk('local')->delete($filePath);

        // Log activity
        ActivityLogger::deleted(
            'system',
            null,
            $filename,
            "Admin menghapus backup database: {$filename}"
        );

        Log::info('Backup delete: file removed', ['file' => $filePath, 'user_id' => optional(Auth::user())->id]);

        return back()->with('success', 'Backup berhasil dihapus.');
    }

    /**
     * Restore database from backup file
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql|max:102400', // Max 100MB
        ]);

        try {
            // Store uploaded file temporarily
            $file = $request->file('backup_file');
            Log::info('Backup restore: received upload', ['original_name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);

            if (!file_exists($fullPath) || filesize($fullPath) === 0) {
                // Clean up temp file
                Storage::disk('local')->delete($tempPath);
                Log::error('Backup restore: uploaded file empty or missing', ['path' => $fullPath]);
                throw new \Exception('File backup kosong atau tidak valid');
            }

            // Disable foreign key checks
            Log::info('Backup restore: disabling foreign key checks');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Execute SQL statements line-by-line to avoid excessive memory usage
            $handle = fopen($fullPath, 'r');
            if ($handle === false) {
                Storage::disk('local')->delete($tempPath);
                throw new \Exception('Gagal membuka file backup untuk dibaca');
            }

            $statement = '';
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                // Skip comments
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '*/')) {
                    continue;
                }

                $statement .= $line;

                // If line ends with semicolon, execute the accumulated statement
                if (str_ends_with(trim($line), ';')) {
                    DB::unprepared($statement);
                    $statement = '';
                }
            }

            // Execute any remaining statement
            if (trim($statement) !== '') {
                DB::unprepared($statement);
            }

            fclose($handle);

            // Clean up temp file
            Storage::disk('local')->delete($tempPath);

            Log::info('Backup restore: completed successfully', ['original_name' => $file->getClientOriginalName()]);

            // Log activity
            ActivityLogger::created(
                'system',
                null,
                $file->getClientOriginalName(),
                "Admin melakukan restore database dari file: {$file->getClientOriginalName()}"
            );

            return back()->with('success', 'Database berhasil direstore dari backup.');
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $ignored) {
                // Ignore if connection is lost
            }
            
            return back()->with('error', 'Gagal restore database: ' . $e->getMessage());
        }
    }
}
