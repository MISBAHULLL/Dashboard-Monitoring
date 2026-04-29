<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah kolom type dari ENUM ke VARCHAR agar bisa menerima tipe bebas
        DB::statement("ALTER TABLE documents MODIFY COLUMN type VARCHAR(100) NOT NULL");

        // 2. Buat tabel document_types untuk menyimpan daftar tipe
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        // 3. Seed tipe default: UAT, MOM, BAST
        DB::table('document_types')->insert([
            ['name' => 'UAT',  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MOM',  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BAST', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('UAT', 'MOM') NOT NULL");
    }
};
