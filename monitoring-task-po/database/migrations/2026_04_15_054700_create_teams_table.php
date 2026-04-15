<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'teams' — Menyimpan data tim (Product & Engineer).
     *
     * Di database lama (mtpo), tabel ini bernama 'team' dengan kolom:
     * - nama (string), tim (enum: PRODUCT/ENGINER)
     *
     * Di versi baru, kita pisahkan tipe tim sebagai enum yang lebih rapi,
     * dan tambahkan deskripsi + soft deletes untuk keamanan data.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();                                          // Primary key auto-increment
            $table->string('name');                                 // Nama tim (misal: "Andi Setiawan")
            $table->enum('type', ['product', 'engineer']);          // Tipe tim: product owner atau engineer
            $table->text('description')->nullable();                // Deskripsi (opsional)
            $table->boolean('is_active')->default(true);            // Status aktif/non-aktif
            $table->timestamps();                                  // created_at & updated_at otomatis
            $table->softDeletes();                                 // Soft delete: data tidak benar-benar dihapus
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
