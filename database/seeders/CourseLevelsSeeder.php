<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseLevelsSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            'Beginner level',
            'Intermediate level',
            'Advanced level',
        ];

        foreach ($levels as $level) {
            $exists = DB::table('course_levels')
                ->where('name', $level)
                ->exists();

            if (! $exists) {
                DB::table('course_levels')->insert([
                    'name' => $level,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command?->info(
            'Course levels imported successfully.'
        );
    }
}