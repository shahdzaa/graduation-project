<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyllabusTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Video'],
            ['name' => 'Chapter'],
            ['name' => 'Article'],
            ['name' => 'Quiz'],
            ['name' => 'Assignment'],
            ['name' => 'Live Session'],
            ['name' => 'PDF'],
            ['name' => 'Exercise'],
        ];

        foreach ($types as $type) {
            DB::table('syllabus_types')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}