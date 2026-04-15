<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'clients' — Menyimpan data klien/faskes.
     *
     * Di database lama (mtpo), tabel ini bernama 'client' dengan kolom:
     * - nama (string) — hanya nama saja
     *
     * Di versi baru, kita tambahkan kode unik, kontak, alamat, dan status.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                 // Nama faskes (misal: "RS Moewardi")
            $table->string('code')->unique()->nullable();           // Kode unik faskes (misal: "RSM-001")
            $table->text('address')->nullable();                    // Alamat faskes
            $table->string('contact_person')->nullable();           // Nama PIC/kontak
            $table->string('contact_email')->nullable();            // Email PIC
            $table->string('contact_phone')->nullable();            // Telepon PIC
            $table->boolean('is_active')->default(true);            // Status aktif/non-aktif
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
