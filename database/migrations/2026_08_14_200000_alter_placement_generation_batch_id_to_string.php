<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE placement_questions MODIFY generation_batch_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE placement_attempts MODIFY generation_batch_id VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE placement_questions MODIFY generation_batch_id INT NOT NULL');
        DB::statement('ALTER TABLE placement_attempts MODIFY generation_batch_id INT NOT NULL');
    }
};
