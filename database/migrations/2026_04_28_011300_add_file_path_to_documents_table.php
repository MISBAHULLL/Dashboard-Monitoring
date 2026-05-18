<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('documents', 'file_path')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('file_path')->nullable()->after('type');
            });
        }

        if (! Schema::hasColumn('documents', 'file_name')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('file_name')->nullable()->after('file_path');
            });
        }

        if (! Schema::hasColumn('documents', 'mime_type')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('mime_type')->nullable()->after('file_name');
            });
        }

        if (! Schema::hasColumn('documents', 'file_size')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            });
        }
    }

    public function down(): void
    {
        $columns = collect(['file_path', 'file_name', 'mime_type', 'file_size'])
            ->filter(fn (string $column): bool => Schema::hasColumn('documents', $column))
            ->values()
            ->all();

        if ($columns !== []) {
            Schema::table('documents', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
