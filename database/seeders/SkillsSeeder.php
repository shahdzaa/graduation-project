<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/skills.csv');

        if (!file_exists($path)) {
            throw new \Exception("File not found: " . $path);
        }

        $file = fopen($path, 'r');

        // Skip header row
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 2) continue;

            $skillName  = trim($row[0]);
            $moduleName = trim($row[1]);

            if (!$skillName || !$moduleName) continue;

            $module = DB::table('modules')->where('name', $moduleName)->first();

            if (!$module) {
                $this->command->warn("Module not found: '{$moduleName}'");
                continue;
            }

            DB::table('skills')->updateOrInsert(
                [
                    'name'      => $skillName,
                    'module_id' => $module->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        fclose($file);
    }
}