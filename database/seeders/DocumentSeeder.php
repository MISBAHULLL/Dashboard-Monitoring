<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── Klinik Sehat Sejahtera (client_id: 2) — 3 dokumen ──
        $docs = [
            // client 2
            ['client_id' => 2, 'title' => 'UAT Modul Antrian Online',        'type' => 'UAT', 'created_at' => $now->copy()->subDays(10)],
            ['client_id' => 2, 'title' => 'MOM Rapat Kick-off Q1',           'type' => 'MOM', 'created_at' => $now->copy()->subDays(7)],
            ['client_id' => 2, 'title' => 'BAST Optimasi Query Database',     'type' => 'BAST','created_at' => $now->copy()->subDays(3)],
            // client 4
            ['client_id' => 4, 'title' => 'UAT Modul Kasir Baru',            'type' => 'UAT', 'created_at' => $now->copy()->subDays(20)],
            ['client_id' => 4, 'title' => 'MOM Rapat Evaluasi Sprint 1',     'type' => 'MOM', 'created_at' => $now->copy()->subDays(15)],
            ['client_id' => 4, 'title' => 'MOM Rapat Evaluasi Sprint 2',     'type' => 'MOM', 'created_at' => $now->copy()->subDays(10)],
            ['client_id' => 4, 'title' => 'BAST Ganti Database',             'type' => 'BAST','created_at' => $now->copy()->subDays(5)],
            ['client_id' => 4, 'title' => 'UAT Fitur Multi-User Role Access', 'type' => 'UAT', 'created_at' => $now->copy()->subDays(2)],
        ];

        foreach ($docs as $doc) {
            DB::table('documents')->insert([
                'client_id'       => $doc['client_id'],
                'title'           => $doc['title'],
                'type'            => $doc['type'],
                'current_version' => 1,
                'created_by'      => 1,
                'created_at'      => $doc['created_at'],
                'updated_at'      => $doc['created_at'],
            ]);
        }
    }
}
