<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'activity_logs' — Audit trail semua aksi di aplikasi.
     *
     * Fitur BARU. Mencatat setiap perubahan yang dilakukan user:
     * siapa, kapan, melakukan apa, pada data apa.
     * Penting untuk keamanan dan transparansi.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('action');                     // Aksi (created, updated, deleted, login, dll)
            $table->string('model_type')->nullable();     // Tabel yang diubah (Task, Team, Client, dll)
            $table->unsignedBigInteger('model_id')->nullable(); // ID record yang diubah
            $table->string('description')->nullable();    // Deskripsi aksi dalam bahasa manusia
            $table->json('old_values')->nullable();       // Nilai sebelum perubahan (snapshot)
            $table->json('new_values')->nullable();       // Nilai setelah perubahan (snapshot)
            $table->string('ip_address')->nullable();     // IP address user
            $table->string('user_agent')->nullable();     // Browser/device info
            $table->timestamps();

            // Index untuk query performa
            $table->index(['model_type', 'model_id']);    // Cari log berdasarkan record tertentu
            $table->index('action');                      // Filter berdasarkan jenis aksi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
