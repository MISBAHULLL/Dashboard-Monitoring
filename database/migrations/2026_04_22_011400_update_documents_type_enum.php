<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum type dari ['UAT','BAST'] menjadi ['UAT','MOM']
        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('UAT', 'MOM') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('UAT', 'BAST') NOT NULL");
    }
};
