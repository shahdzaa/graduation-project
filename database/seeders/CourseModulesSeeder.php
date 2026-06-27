<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseModulesSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/course_modules.csv');

        if (!file_exists($path)) {
            throw new \Exception("File not found: " . $path);
        }

        // 1. حمل كل الكورسات والموديولز مرة واحدة بالذاكرة
        $courses = DB::table('courses')->pluck('id', 'title');
        $modules = DB::table('modules')->pluck('id', 'name');

        $this->command->info("Loaded {$courses->count()} courses and {$modules->count()} modules");

        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        $batch = [];
        $orderIndex = 1;
        $batchSize = 500; // حجم الباتش
        $inserted = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 2) continue;

            $moduleName  = trim($row[0]);
            $courseTitle = trim($row[1]);

            if (!$moduleName || !$courseTitle) continue;

            $moduleId = $modules[$moduleName] ?? null;
            $courseId = $courses[$courseTitle] ?? null;

            if (!$moduleId) {
                $this->command->warn("Module not found: '{$moduleName}'");
                $skipped++;
                continue;
            }

            if (!$courseId) {
                $this->command->warn("Course not found: '{$courseTitle}'");
                $skipped++;
                continue;
            }

            $batch[] = [
                'course_id'   => $courseId,
                'module_id'   => $moduleId,
                'order_index' => $orderIndex++,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // لما يوصل الباتش للحجم المطلوب، نفذه
            if (count($batch) >= $batchSize) {
                $this->insertBatch($batch);
                $inserted += count($batch);
                $this->command->info("Inserted {$inserted} rows...");
                $batch = [];
            }
        }

        // الباتش الأخير
        if (!empty($batch)) {
            $this->insertBatch($batch);
            $inserted += count($batch);
        }

        fclose($file);

        $this->syncCourseSkills();

        $this->command->info("✅ Done! Inserted: {$inserted}, Skipped: {$skipped}");
    }

    private function insertBatch(array $rows): void
    {
        DB::transaction(function () use ($rows) {
            // نحذف التكرارات بناءً على (course_id, module_id) ونحتفظ بآخر قيمة
            $unique = [];
            foreach ($rows as $row) {
                $key = $row['course_id'] . '-' . $row['module_id'];
                $unique[$key] = $row; // آخر قيمة بتكتب على السابقة
            }

            DB::table('course_modules')->insert(array_values($unique));
        });
    }

    private function syncCourseSkills(): void
    {
        if (! Schema::hasTable('course_skills')) {
            return;
        }

        DB::table('course_modules')
            ->join('skills', 'skills.module_id', '=', 'course_modules.module_id')
            ->select('course_modules.course_id', 'skills.id as skill_id')
            ->orderBy('course_modules.course_id')
            ->chunk(1000, function ($rows) {
                $courseSkills = $rows
                    ->map(fn ($row) => [
                        'course_id' => $row->course_id,
                        'skill_id' => $row->skill_id,
                    ])
                    ->unique(fn ($row) => $row['course_id'].'-'.$row['skill_id'])
                    ->values()
                    ->all();

                DB::table('course_skills')->insertOrIgnore($courseSkills);
            });
    }
}
