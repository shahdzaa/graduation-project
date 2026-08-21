<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyllabusTypesSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('syllabus_types')
            ->where('name', 'Topic')
            ->exists();

        if (! $exists) {
            DB::table('syllabus_types')->insert([
                'name' => 'Topic',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command?->info(
            'Syllabus type imported successfully.'
        );
    }
}