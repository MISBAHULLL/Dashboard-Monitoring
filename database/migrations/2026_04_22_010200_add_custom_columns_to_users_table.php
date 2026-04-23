<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'member'])->default('member')->after('email');
            $table->string('avatar', 255)->nullable()->after('role');
            $table->string('phone', 20)->nullable()->after('avatar');
            $table->foreignId('team_id')->nullable()->after('phone')
                  ->constrained('teams')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('team_id');
            $table->enum('theme', ['light', 'dark'])->default('light')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn(['role', 'avatar', 'phone', 'team_id', 'is_active', 'theme']);
        });
    }
};
