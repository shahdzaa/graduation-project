<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('syllabus', function (Blueprint $table) {
            $table->unsignedInteger('order_index')
                ->default(0)
                ->after('name');

            $table->unique(
                ['module_id', 'order_index'],
                'syllabus_module_order_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('syllabus', function (Blueprint $table) {
            $table->dropUnique(
                'syllabus_module_order_unique'
            );

            $table->dropColumn('order_index');
        });
    }
};