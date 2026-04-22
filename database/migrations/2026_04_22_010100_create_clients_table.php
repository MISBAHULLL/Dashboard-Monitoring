<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'clients' — Data client/faskes.
     *
     * Mapping dari database lama (mtpo.client):
     * - nama    → name
     * - alamat  → address
     * - kota    → city      (untuk filter dropdown)
     * - tipe    → type      (A/B/C/PRATAMA)
     * - pic     → pic_name
     * - no_pic  → pic_phone
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('type', ['A', 'B', 'C', 'PRATAMA'])->nullable();
            $table->string('pic_name', 100)->nullable();
            $table->string('pic_phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('city');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
