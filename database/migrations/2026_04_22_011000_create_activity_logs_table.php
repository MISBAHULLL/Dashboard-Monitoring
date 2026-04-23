<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function(Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->enum('action', [
                'created',
                'updated',
                'deleted',
                'imported',
                'exported',
                'status_changed',
                'logged_in',
            ]);

            $table->enum('module', [
                'task',
                'team',
                'client',
                'document',
                'user',
                'system',
            ]);

            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_title', 255)->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['module', 'target_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};