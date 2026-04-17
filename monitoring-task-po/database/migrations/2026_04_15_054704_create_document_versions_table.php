<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'document_versions' — Versioning dokumen.
     *
     * Fitur BARU (tidak ada di database lama).
     * Setiap kali dokumen diupdate/re-upload, versi lama disimpan di sini,
     * sehingga user bisa melihat riwayat perubahan dan download versi lama.
     */
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();                   // Hapus dokumen = hapus semua versi
            $table->unsignedInteger('version_number');    // Nomor versi (1, 2, 3, ...)
            $table->string('file_path');                  // Path file versi ini
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('change_notes')->nullable();     // Catatan perubahan versi ini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
