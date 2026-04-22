<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'teams' — Anggota tim Product Owner & Engineer.
     *
     * Mapping dari database lama (mtpo.team):
     * - nama   → name
     * - alamat  → address
     * - no_hp   → phone
     * - tim     → type (PRODUCT/ENGINEER, perbaiki typo 'ENGINER')
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['PRODUCT', 'ENGINEER']);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
