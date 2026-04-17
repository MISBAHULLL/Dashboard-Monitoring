<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'notifications' — Notifikasi in-app untuk user.
     *
     * Fitur BARU. Memberitahu user tentang:
     * - Task baru yang di-assign ke mereka
     * - Perubahan status task
     * - Komentar baru di task mereka
     * - Deadline yang mendekat
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('title');                          // Judul notifikasi
            $table->text('message')->nullable();              // Isi pesan
            $table->enum('type', [                            // Tipe notifikasi
                'task_assigned',                              // Task baru di-assign
                'status_changed',                             // Status task berubah
                'comment_added',                              // Komentar baru
                'deadline_approaching',                       // Deadline mendekat
                'release_date_changed',                       // Tanggal rilis berubah
                'general'                                     // Umum
            ])->default('general');
            $table->string('icon')->nullable();               // Icon notifikasi (optional)
            $table->json('data')->nullable();                 // Data tambahan (link, task_id, dll)
            $table->timestamp('read_at')->nullable();         // Null = belum dibaca
            $table->timestamps();

            $table->index(['user_id', 'read_at']);            // Cari notif belum dibaca per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
