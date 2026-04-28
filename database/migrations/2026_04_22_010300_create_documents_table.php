<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('documents', function(Blueprint $table) {
        $table->id();

        $table->foreignId('client_id')
              ->constrained('clients')
              ->cascadeOnDelete();
        
        $table->string('title', 200);
        $table->enum('type', ['UAT', 'BAST']);
        
        // File information
        $table->string('file_path')->nullable();
        $table->string('file_name')->nullable();
        $table->string('mime_type')->nullable();
        $table->unsignedBigInteger('file_size')->nullable();
        
        $table->unsignedInteger('current_version')->default(1);

        $table->foreignId('created_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();

        $table->timestamps();
        $table->softDeletes();
      });   
    }
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};