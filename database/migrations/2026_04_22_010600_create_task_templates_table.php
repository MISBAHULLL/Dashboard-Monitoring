<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_templates', function(Blueprint $table) {
            $table->id();
            $table->string('name', 100);

            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('teams')
                  ->nullOnDelete();

            $table->foreignId('client_id')
                  ->nullable()
                  ->constrained('clients')
                  ->nullOnDelete();

            $table->foreignId('engineer_id')
                  ->nullable()
                  ->constrained('teams')
                  ->nullOnDelete();

            $table->enum('category',[
                'Fitur Berbayar',
                'Regulasi',
                'Saran Fitur',
                'Prioritas',
            ])->nullable();

            $table->enum('priority', ['urgent', 'high', 'medium', 'low'])
                  ->default('medium');

            $table->text('description')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};