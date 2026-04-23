<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table){
            $table->id();

            // foreign keys

            $table->foreignId('product_id')
                  ->constrained('teams')
                  ->restrictOnDelete();

            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->restrictOnDelete();

            $table->foreignId('engineer_id')
                  ->nullable()
                  ->constrained('teams')
                  ->nullOnDelete();

            $table->foreignId('document_id')
                  ->nullable()
                  ->constrained('documents')
                  ->nullOnDelete();

            $table->foreignId('template_id')
                  ->nullable()
                  ->constrained('task_templates')
                  ->nullOnDelete();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Data Task

            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('modul', 100)->nullable();
            $table->string('task_url', 500)->default('-');
            
            // Kategorisasi

            $table->enum('category', [
                'Fitur Berbayar',
                'Regulasi',
                'Saran Fitur',
                'Prioritas',
            ]);

            $table->enum('priority', ['urgent', 'high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'revision', 'completed'])->default('open');

            // Tanggal

            $table->date('release_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index Untuk Performa

            $table->index('status');
            $table->index('priority');
            $table->index('category');
            $table->index('release_date');
            $table->index(['status', 'release_date']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};