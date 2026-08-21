<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Course',
            'Professional Certificates',
            'Specializations',
            'Guided Project',
            'Project',
        ];

        foreach ($types as $type) {
            $exists = DB::table('course_types')
                ->where('name', $type)
                ->exists();

            if (! $exists) {
                DB::table('course_types')->insert([
                    'name' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command?->info(
            'Course types imported successfully.'
        );
    }
}