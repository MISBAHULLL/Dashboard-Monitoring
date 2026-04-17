<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'release_date_logs' — Log perubahan tanggal rilis.
     *
     * Fitur yang DIPERTAHANKAN dari database lama.
     * Setiap kali tanggal rilis task berubah, alasan perubahannya dicatat di sini.
     * Ini penting untuk audit dan akuntabilitas.
     */
    public function up(): void
    {
        Schema::create('release_date_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();
            $table->foreignId('changed_by')              // Siapa yang mengubah
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->date('old_date')->nullable();         // Tanggal lama
            $table->date('new_date');                     // Tanggal baru
            $table->text('reason')->nullable();           // Alasan perubahan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_date_logs');
    }
};
