<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Team;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;

class MigrateOldDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Mulai Migrasi Data...');

        // Admin User

        $this->command->info('Membuat admin user...');

        $admin = User::firstOrCreate(
            ['email' => 'admin@trustmedis.com'],
            [
                'name' => 'Admin PO',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info(" Admin: {$admin->email}");

        // Migrasi Team
        $oldTeams = DB::connection('old_mtpo')->table('team')->get();
        $teamMap = [];
        $teamCount = 0;

        foreach ($oldTeams as $oldTeam) {
            $type = strtolower($oldTeam->tim) === 'product' ? 'PRODUCT' : 'ENGINEER';
            
            $team = Team::firstOrCreate(
                ['name' => $oldTeam->nama, 'type' => $type],
                [
                    'address' => $oldTeam->alamat ?? null,
                    'phone' => $oldTeam->no_hp ?? null,
                    'is_active' => true,
                ]
            );

            $teamMap[$oldTeam->nama] = $team->id;
            $teamCount++;
        }

        // Migrasi Client
        $oldClients = DB::connection('old_mtpo')->table('client')->get();
        $clientMap = [];
        $clientCount = 0;

        foreach ($oldClients as $oldClient) {
            $client = Client::firstOrCreate(
                ['name' => $oldClient->nama],
                [
                    'address' => $oldClient->alamat ?? null,
                    'city' => $oldClient->kota ?? null,
                    'type' => $oldClient->tipe ?? null,
                    'pic_name' => $oldClient->pic ?? null,
                    'pic_phone' => $oldClient->no_pic ?? null,
                    'is_active' => true,
                ]
            );

            $clientMap[$oldClient->nama] = $client->id;
            $clientCount++;
        }

        // Migrasi Tasks

        $oldTasks = DB::connection('old_mtpo')->table('task')->get();
        $taskCount = 0;
        $skippedCount = 0;

        foreach ($oldTasks as $oldTask) {
            $productId = $teamMap[$oldTask->product] ?? null;
            $engineerId = $teamMap[$oldTask->enginer] ?? null;
            $clientId = $clientMap[$oldTask->faskes] ?? null;

            if (!$clientId || !$productId) {
                $skippedCount++;
                $this->command->warn(" Task '{$oldTask->fitur}' dilewati (data tidak lengkap)");
                continue;
            }
            // mapping Status lama -> enum baru
            
            $statusMap = [
                'Belum di cek' => 'open',
                'Revisi' => 'revision',
                'Selesai' => 'completed',
            ];
            $status = $statusMap[$oldTask->status_cek] ?? 'open';

            $task = Task::create([
                'product_id' => $productId,
                'client_id' => $clientId,
                'engineer_id' => $engineerId,
                'created_by' => $admin->id,
                'title' => $oldTask->fitur ?? '-',
                'description' => $oldTask->keterangan ?? null,
                'modul' => $oldTask->modul ?? null,
                'task_url' => $oldTask->task_url ?? '-',
                'category' => in_array($oldTask->jenis, ['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas']) ? $oldTask->jenis : 'Saran Fitur',
                'priority' => 'medium',
                'status' => $status,
                'release_date' => ($oldTask->tgl_release && $oldTask->tgl_release > '2000-01-01')
                                  ?$oldTask->tgl_release
                                  :null,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);
            $taskCount++;
        }
        $this->command->info("  {$taskCount} task dimigrasikan");
        if ($skippedCount > 0) {
            $this->command->warn("  {$skippedCount} task dilewati");
        }

    // Migrasi Documents
    $docCount = 0;
    $hasDokumen = DB::connection('old_mtpo')
            ->getSchemaBuilder()
            ->hasTable('dokumen');

    if ($hasDokumen) {
        $oldDokumens = DB::connection('old_mtpo')
            ->table('dokumen')->get();
    
    
    foreach($oldDokumens as $oldDoc){
        // cari task pertama yang pakai dokumen ini
        $oldTaskWithDoc = DB::connection('old_mtpo')
        ->table('task')
        ->where('id_dokumen', $oldDoc->id)
        ->first();

        if(!$oldTaskWithDoc){
            continue;
        }
        
        $clientId = $clientMap[$oldTaskWithDoc->faskes] ?? null;
        if(!$clientId){
            continue;
        }

        // Tentukan tipe dokumen dari field jenis
        $docType = strtoupper($oldDoc->jenis ?? '') === 'BAST' ? 'BAST' : 'UAT';
        // Buat record di tabel documents (induk)
                $docId = DB::table('documents')->insertGetId([
                    'client_id' => $clientId,
                    'title' => $oldDoc->judul ?? 'Dokumen',
                    'type' => $docType,
                    'current_version' => 1,
                    'created_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        
        // Buat record di tabel document_versions (file-nya)
                DB::table('document_versions')->insert([
                    'document_id' => $docId,
                    'version_number' => 1,
                    'file_path' => $oldDoc->file_path ?? null,
                    'doc_url' => $oldDoc->doc_url ?? null,
                    'uploaded_by' => $admin->id,
                    'created_at' => now(),
                ]);

        // Update semua task baru yang terhubung ke dokumen ini
                $oldTasksWithDoc = DB::connection('old_mtpo')
                    ->table('task')
                    ->where('id_dokumen', $oldDoc->id)
                    ->get();
                foreach ($oldTasksWithDoc as $linkedTask) {
                    $linkedClientId = $clientMap[$linkedTask->faskes] ?? null;
                    Task::where('title', $linkedTask->fitur)
                        ->where('client_id', $linkedClientId)
                        ->update(['document_id' => $docId]);
                }
                $docCount++;
            }
        }
        $this->command->info("  {$docCount} dokumen dimigrasikan");

        // Migrasi Release Date Logs
        $logCount = 0;
        $hasLog = DB::connection('old_mtpo')
            ->getSchemaBuilder()
            ->hasTable('log_tgl_release');
        if ($hasLog) {
            $oldLogs = DB::connection('old_mtpo')->table('log_tgl_release')->get();
            foreach ($oldLogs as $oldLog) {
                // Cari task baru berdasarkan id task lama
                $oldTask = DB::connection('old_mtpo')
                    ->table('task')
                    ->where('id', $oldLog->task_id)
                    ->first();
                if (!$oldTask) {
                    continue;
                }
                $clientId = $clientMap[$oldTask->faskes] ?? null;
                $newTask = Task::where('title', $oldTask->fitur)
                    ->where('client_id', $clientId)
                    ->first();
                if ($newTask) {
                    DB::table('release_date_logs')->insert([
                        'task_id' => $newTask->id,
                        'changed_by' => $admin->id,
                        'old_date' => $oldLog->tgl_lama ?? now()->format('Y-m-d'),
                        'new_date' => $oldLog->tgl_baru ?? now()->format('Y-m-d'),
                        'reason' => $oldLog->alasan ?? 'Dimigrasikan dari sistem lama',
                        'created_at' => $oldLog->created_at ?? now(),
                        'updated_at' => $oldLog->created_at ?? now(),
                    ]);
                    $logCount++;
                }
            }
        }
        $this->command->info("  {$logCount} log dimigrasikan");

        // RINGKASAN
        $this->command->newLine();
        $this->command->info('Migrasi data selesai!');
        $this->command->table(
            ['Data', 'Jumlah'],
            [
                ['Admin User', 1],
                ['Teams', $teamCount],
                ['Clients', $clientCount],
                ['Tasks', $taskCount],
                ['Tasks Dilewati', $skippedCount],
                ['Documents', $docCount],
                ['Release Logs', $logCount],
            ]
        );
    }
}