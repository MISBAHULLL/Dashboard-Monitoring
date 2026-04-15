<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Tambah kolom 'role' ke tabel users.
     * Role menentukan hak akses user dalam aplikasi:
     * - admin  → Product Owner, bisa kelola semua data
     * - member → Engineer/anggota tim, hanya lihat task yang di-assign
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'member'])
                  ->default('member')       // User baru = member by default
                  ->after('email');          // Letakkan setelah kolom email
        });
    }

    /**
     * Rollback: hapus kolom role jika migration di-undo.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
