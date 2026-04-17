<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'documents' — Dokumen yang terkait dengan task.
     *
     * Di database lama (mtpo), tabel ini bernama 'dokumen'.
     * Di versi baru, kita tambahkan relasi FK ke task dan user,
     * serta tracking file size dan tipe.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();                  // Hapus task = hapus dokumen terkait
            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('title');                     // Judul dokumen
            $table->string('file_path');                 // Path file di storage
            $table->string('file_type')->nullable();     // Ekstensi (pdf, docx, xlsx)
            $table->unsignedBigInteger('file_size')->default(0); // Ukuran file (bytes)
            $table->text('notes')->nullable();           // Catatan dokumen
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
