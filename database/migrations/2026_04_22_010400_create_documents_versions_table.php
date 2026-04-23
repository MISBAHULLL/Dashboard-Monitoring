<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();

            $table->unsignedInteger('version_number');
            $table->string('file_path', 500)->nullable();
            $table->string('doc_url', 500)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });   
    }
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};