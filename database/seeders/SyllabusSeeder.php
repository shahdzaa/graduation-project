<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyllabusSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/syllabus.csv');

        if (!file_exists($path)) {
            throw new \Exception("File not found: " . $path);
        }

        $syllabusTypes = DB::table('syllabus_types')->pluck('id')->toArray();

        $file = fopen($path, 'r');

        // Skip header row
        fgetcsv($file, 0, ",");

        while (($row = fgetcsv($file, 0, ",")) !== false) {
            if (count($row) < 2) continue;

            $syllabusName = trim($row[0]);
            $domainName   = trim($row[1]);

            if (!$syllabusName || !$domainName) continue;

            // Find module by domain name
            $module = DB::table('modules')->where('name', $domainName)->first();

            if (!$module) continue;

            DB::table('syllabus')->updateOrInsert(
                [
                    'name'      => $syllabusName,
                    'module_id' => $module->id,
                ],
                [
                    'type_id'          => $syllabusTypes[array_rand($syllabusTypes)],
                    'duration_minutes' => rand(10, 60),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }

        fclose($file);
    }
}