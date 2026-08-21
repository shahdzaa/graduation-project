<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('skills', 'module_id')) {
            return;
        }

        $foreignKey = DB::table(
            'information_schema.KEY_COLUMN_USAGE'
        )
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'skills')
            ->where('COLUMN_NAME', 'module_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        if ($foreignKey) {
            Schema::table(
                'skills',
                function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey);
                }
            );
        }

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('module_id');
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();
        });
    }
};