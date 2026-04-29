<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah doc_url ke tabel documents
        Schema::table('documents', function (Blueprint $table) {
            $table->string('doc_url', 500)->nullable()->after('type');
        });

        // Tambah kolom status ke pivot document_task
        Schema::table('document_task', function (Blueprint $table) {
            $table->enum('status', ['revision', 'completed'])->nullable()->after('task_id');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('doc_url');
        });
        Schema::table('document_task', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
