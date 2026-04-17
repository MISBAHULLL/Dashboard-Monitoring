<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel 'tags' & 'task_tags' — Sistem labeling untuk task.
     *
     * Fitur BARU. Memungkinkan task diberi tag/label warna-warni
     * untuk kategori custom (misal: "urgent", "frontend", "API", "fase-1").
     *
     * Relasi: Many-to-Many (1 task bisa punya banyak tag,
     * 1 tag bisa dipasang di banyak task) → butuh pivot table 'task_tags'.
     */
    public function up(): void
    {
        // Tabel master tag
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();             // Nama tag (unik, misal: "urgent")
            $table->string('color', 7)->default('#6B7280'); // Warna hex (misal: "#FF5733")
            $table->timestamps();
        });

        // Pivot table: menghubungkan tasks <-> tags (Many-to-Many)
        Schema::create('task_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();                    // Hapus task = hapus relasinya
            $table->foreignId('tag_id')
                  ->constrained('tags')
                  ->cascadeOnDelete();                    // Hapus tag = hapus relasinya
            $table->timestamps();

            $table->unique(['task_id', 'tag_id']);        // Cegah duplikasi: 1 tag hanya 1x per task
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_tags');                // Hapus pivot dulu (karena ada FK)
        Schema::dropIfExists('tags');                     // Baru hapus master
    }
};
