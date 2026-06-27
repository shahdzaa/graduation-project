<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesSeeder extends Seeder
{
    public function run()
    {
        $path = database_path('seeders/data/modules.csv');

        if (!file_exists($path)) {
            throw new \Exception("File not found: " . $path);
        }

        $file = fopen($path, 'r');

        // Skip header row if your CSV has one
        // fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $module = trim($row[0]);
            $durationMinutes = isset($row[1]) ? (int) trim($row[1]) : 0;

            if (!$module) {
                continue;
            }

            DB::table('modules')->updateOrInsert(
                ['name' => $module],
                [
                    'duration_minutes' => $durationMinutes,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }

        fclose($file);
    }
}