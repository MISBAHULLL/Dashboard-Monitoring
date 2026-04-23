<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function(Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('color', 7)->default('#6B7280');
            $table->timestamps();
        });

        Schema::create('task_tags', function(Blueprint $table) {
            $table->foreignId('task_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();

            $table->foreignId('tag_id')
                  ->constrained('tags')
                  ->cascadeOnDelete();
            $table->primary(['task_id', 'tag_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('task_tags');
        Schema::dropIfExists('tags');
    }
};