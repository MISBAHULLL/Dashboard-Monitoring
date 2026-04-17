<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Team;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;

/**
 * MigrateOldDataSeeder — Memindahkan data dari database lama (mtpo)
 * ke database baru (monitoring_task_po).
 *
 * Cara kerja:
 * 1. Baca data dari koneksi 'old_mtpo'
 * 2. Mapping kolom lama → kolom baru
 * 3. Simpan ke database baru via Eloquent
 *
 * Jalankan: php artisan db:seed --class=MigrateOldDataSeeder
 */
class MigrateOldDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Memulai migrasi data dari database mtpo...');
        $this->command->newLine();

        // ==========================================
        // STEP 1: Buat admin user (karena database lama tidak punya tabel users)
        // ==========================================
        $this->command->info('📦 Step 1: Membuat admin user...');

        $admin = User::firstOrCreate(
            ['email' => 'admin@trustmedis.com'],           // Cek apakah sudah ada
            [                                               // Kalau belum, buat baru
                'name' => 'Admin PO',
                'password' => Hash::make('password'),       // Password default (GANTI NANTI!)
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("   ✅ Admin user: {$admin->email}");

        // ==========================================
        // STEP 2: Migrasi tabel TEAM → teams
        // ==========================================
        $this->command->info('📦 Step 2: Migrasi data Team...');

        // Baca dari database lama
        $oldTeams = DB::connection('old_mtpo')->table('team')->get();
        $teamMap = [];  // Untuk mapping: nama_lama → id_baru
        $migratedTeams = 0;

        foreach ($oldTeams as $oldTeam) {
            // Mapping tipe: 'PRODUCT' → 'product', 'ENGINER' → 'engineer'
            $type = strtolower($oldTeam->tim) === 'product' ? 'product' : 'engineer';

            $team = Team::firstOrCreate(
                ['name' => $oldTeam->nama, 'type' => $type],
                ['is_active' => true]
            );

            // Simpan mapping: "Andi Setiawan" → 5 (id baru)
            $teamMap[$oldTeam->nama] = $team->id;
            $migratedTeams++;
        }

        $this->command->info("   ✅ {$migratedTeams} tim berhasil dimigrasikan");

        // ==========================================
        // STEP 3: Migrasi tabel CLIENT → clients
        // ==========================================
        $this->command->info('📦 Step 3: Migrasi data Client/Faskes...');

        $oldClients = DB::connection('old_mtpo')->table('client')->get();
        $clientMap = [];  // Mapping: nama_faskes → id_baru
        $migratedClients = 0;

        foreach ($oldClients as $oldClient) {
            $client = Client::firstOrCreate(
                ['name' => $oldClient->nama],
                ['is_active' => true]
            );

            $clientMap[$oldClient->nama] = $client->id;
            $migratedClients++;
        }

        $this->command->info("   ✅ {$migratedClients} client berhasil dimigrasikan");

        // ==========================================
        // STEP 4: Migrasi tabel TASK → tasks
        // ==========================================
        $this->command->info('📦 Step 4: Migrasi data Task...');

        $oldTasks = DB::connection('old_mtpo')->table('task')->get();
        $migratedTasks = 0;
        $skippedTasks = 0;

        foreach ($oldTasks as $oldTask) {
            // --- Mapping FK: cari ID dari nama string ---

            // Product: cari ID berdasarkan nama
            $productId = $teamMap[$oldTask->product] ?? null;

            // Engineer: cari ID berdasarkan nama
            $engineerId = $teamMap[$oldTask->enginer] ?? null;

            // Client/Faskes: cari ID berdasarkan nama
            $clientId = $clientMap[$oldTask->faskes] ?? null;

            // Skip jika client tidak ditemukan (data corrupt)
            if (!$clientId || !$productId) {
                $skippedTasks++;
                $this->command->warn("   ⚠️  Task '{$oldTask->fitur}' dilewati (client/product tidak ditemukan)");
                continue;
            }

            // --- Mapping jenis task lama → enum baru ---
            $typeMap = [
                'Fitur Berbayar' => 'fitur_berbayar',
                'Regulasi'       => 'regulasi',
                'Saran Fitur'    => 'saran_fitur',
                'Prioritas'      => 'prioritas',
            ];
            $type = $typeMap[$oldTask->jenis] ?? 'saran_fitur';

            // --- Mapping status lama → enum baru ---
            $statusMap = [
                'Belum di cek' => 'backlog',
                'Revisi'       => 'review',
                'Selesai'      => 'done',
            ];
            $status = $statusMap[$oldTask->status_cek] ?? 'backlog';

            // --- Mapping task_url: '-' di database lama berarti belum ada ---
            // Jika ada task_url dan status selesai, berarti sudah released
            if ($oldTask->task_url !== '-' && $status === 'done') {
                $status = 'released';
            }

            // --- Buat task baru ---
            Task::create([
                'product_id'   => $productId,
                'engineer_id'  => $engineerId,
                'client_id'    => $clientId,
                'created_by'   => $admin->id,
                'feature'      => $oldTask->fitur ?? '-',
                'description'  => $oldTask->keterangan ?? null,
                'task_url'     => ($oldTask->task_url !== '-') ? $oldTask->task_url : null,
                'type'         => $type,
                'status'       => $status,
                'priority'     => 'medium',                  // Default, database lama tidak punya priority
                'release_date' => ($oldTask->tgl_release && $oldTask->tgl_release > '2000-01-01')
                                    ? $oldTask->tgl_release
                                    : null,
                'progress'     => $status === 'done' || $status === 'released' ? 100 : 0,
            ]);

            $migratedTasks++;
        }

        $this->command->info("   ✅ {$migratedTasks} task berhasil dimigrasikan");
        if ($skippedTasks > 0) {
            $this->command->warn("   ⚠️  {$skippedTasks} task dilewati karena data tidak lengkap");
        }

        // ==========================================
        // STEP 5: Migrasi tabel DOKUMEN → documents
        // ==========================================
        $this->command->info('📦 Step 5: Migrasi data Dokumen...');

        // Cek apakah tabel dokumen ada di database lama
        $hasDokumen = DB::connection('old_mtpo')
            ->getSchemaBuilder()
            ->hasTable('dokumen');

        if ($hasDokumen) {
            $oldDokumens = DB::connection('old_mtpo')->table('dokumen')->get();
            $migratedDocs = 0;

            foreach ($oldDokumens as $oldDoc) {
                // Cari task yang terhubung (via id_dokumen di tabel task lama)
                $oldTaskWithDoc = DB::connection('old_mtpo')
                    ->table('task')
                    ->where('id_dokumen', $oldDoc->id)
                    ->first();

                if ($oldTaskWithDoc) {
                    // Cari task baru berdasarkan fitur + client
                    $clientId = $clientMap[$oldTaskWithDoc->faskes] ?? null;
                    $newTask = Task::where('feature', $oldTaskWithDoc->fitur)
                        ->where('client_id', $clientId)
                        ->first();

                    if ($newTask) {
                        DB::table('documents')->insert([
                            'task_id'     => $newTask->id,
                            'uploaded_by'  => $admin->id,
                            'title'       => $oldDoc->judul ?? 'Dokumen',
                            'file_path'   => $oldDoc->file_path ?? '',
                            'file_type'   => $oldDoc->jenis ?? null,
                            'notes'       => $oldDoc->doc_url ?? null,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                        $migratedDocs++;
                    }
                }
            }
            $this->command->info("   ✅ {$migratedDocs} dokumen berhasil dimigrasikan");
        } else {
            $this->command->warn("   ⚠️  Tabel 'dokumen' tidak ditemukan di database lama");
        }

        // ==========================================
        // RINGKASAN
        // ==========================================
        $this->command->newLine();
        $this->command->info('🎉 Migrasi data selesai!');
        $this->command->table(
            ['Data', 'Jumlah'],
            [
                ['Admin User', 1],
                ['Teams', $migratedTeams],
                ['Clients', $migratedClients],
                ['Tasks', $migratedTasks],
                ['Tasks Dilewati', $skippedTasks],
            ]
        );

        $this->command->warn('⚠️  Jangan lupa ganti password admin: admin@trustmedis.com / password');
    }
}
