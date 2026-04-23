<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_date_logs', function(Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();

            $table->foreignId('changed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->date('old_date');
            $table->date('new_date');
            $table->text('reason');
            $table->timestamps();

        });
    }
    public function down(): void
    {
        Schema::dropIfExists('release_date_logs');
    }
};