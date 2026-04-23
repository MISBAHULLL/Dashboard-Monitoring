<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_configs', function (Blueprint $table) {
            $table->id();
            $table->enum('category', [
                'Fitur Berbayar',
                'Regulasi',
                'Saran Fitur',
                'Prioritas',
            ])->unique();
            $table->unsignedInteger('max_days');
            $table->unsignedInteger('warning_days')->default(3);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('sla_configs');
    }
};