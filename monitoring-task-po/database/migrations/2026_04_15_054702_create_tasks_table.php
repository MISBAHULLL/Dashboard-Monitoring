<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'tasks' — Inti dari aplikasi. Menyimpan semua task.
     *
     * Di database lama (mtpo), tabel ini bernama 'task' dengan kolom:
     * - product, faskes, enginer (STRING — ini masalah! harusnya FK integer)
     * - jenis, fitur, keterangan, task_url, tgl_release, status_cek
     *
     * Di versi baru:
     * - FK integer ke tabel teams, clients, users
     * - Tambah priority, progress, start_date, due_date
     * - Status & type pakai enum yang terstruktur
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // === Foreign Keys (relasi ke tabel lain) ===
            $table->foreignId('product_id')                        // FK ke teams (tipe: product)
                  ->constrained('teams')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();                            // Cegah hapus tim yang masih punya task

            $table->foreignId('engineer_id')                       // FK ke teams (tipe: engineer)
                  ->nullable()
                  ->constrained('teams')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->foreignId('client_id')                         // FK ke clients
                  ->constrained('clients')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->foreignId('assigned_to')                       // FK ke users (siapa yang di-assign)
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->foreignId('created_by')                        // FK ke users (siapa yang buat task)
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            // === Data Task ===
            $table->string('feature');                              // Nama fitur (dari kolom 'fitur' lama)
            $table->text('description')->nullable();                // Keterangan detail
            $table->string('task_url')->nullable();                 // Link ke task tracker (Jira, Trello, dll)

            // === Kategorisasi ===
            $table->enum('type', [                                 // Jenis task (dari kolom 'jenis' lama)
                'fitur_berbayar',
                'regulasi',
                'saran_fitur',
                'prioritas',
                'bug_fix',
                'improvement'
            ])->default('saran_fitur');

            $table->enum('priority', [                             // Level prioritas (fitur baru)
                'low', 'medium', 'high', 'critical'
            ])->default('medium');

            $table->enum('status', [                               // Status task (dari 'status_cek' lama)
                'backlog',                                         // Belum dikerjakan
                'in_progress',                                     // Sedang dikerjakan
                'review',                                          // Sedang direview
                'done',                                            // Selesai
                'released'                                         // Sudah rilis ke production
            ])->default('backlog');

            // === Tanggal ===
            $table->date('start_date')->nullable();                // Tanggal mulai (fitur baru)
            $table->date('due_date')->nullable();                  // Tanggal deadline (fitur baru)
            $table->date('release_date')->nullable();              // Tanggal rilis (dari 'tgl_release' lama)

            // === Progress ===
            $table->unsignedTinyInteger('progress')->default(0);   // Progress 0-100% (fitur baru)

            $table->timestamps();
            $table->softDeletes();

            // === Index untuk performa query ===
            $table->index('status');                                // Sering filter berdasarkan status
            $table->index('priority');                              // Sering filter berdasarkan priority
            $table->index('release_date');                          // Sering sort berdasarkan release date
            $table->index(['status', 'due_date']);                  // Untuk query overdue & due soon
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
