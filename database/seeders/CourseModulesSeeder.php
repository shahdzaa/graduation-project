<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;

class CourseModulesSeeder extends Seeder
{
    /**
     * @throws JsonException
     */
    public function run(): void
    {
        $jsonPath = storage_path(
            'app/imports/course_structure.json'
        );

        if (! file_exists($jsonPath)) {
            throw new RuntimeException(
                "Structure file not found: {$jsonPath}"
            );
        }

        $structure = json_decode(
            file_get_contents($jsonPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $courseIds = DB::table('courses')
            ->get(['id', 'title'])
            ->mapWithKeys(fn ($course) => [
                $this->normalizeKey($course->title)
                    => $course->id,
            ])
            ->all();

        $insertedModules = 0;
        $updatedModules = 0;
        $createdLinks = 0;

        DB::transaction(function () use (
            $structure,
            $courseIds,
            &$insertedModules,
            &$updatedModules,
            &$createdLinks
        ) {
            foreach ($structure as $courseData) {
                $courseTitle = $this->normalizeText(
                    $courseData['course_title'] ?? ''
                );

                $courseId =
                    $courseIds[
                        $this->normalizeKey($courseTitle)
                    ] ?? null;

                if (! $courseId) {
                    throw new RuntimeException(
                        "Course not found: {$courseTitle}"
                    );
                }

                $modules = $courseData['modules'] ?? [];

                foreach ($modules as $index => $moduleData) {
                    $orderIndex = $index + 1;

                    $moduleName = $this->normalizeText(
                        $moduleData['name'] ?? ''
                    );

                    if ($moduleName === '') {
                        $moduleName = "Module {$orderIndex}";
                    }

                    $existingModuleId = DB::table(
                        'course_modules'
                    )
                        ->where('course_id', $courseId)
                        ->where('order_index', $orderIndex)
                        ->value('module_id');

                    if ($existingModuleId) {
                        DB::table('modules')
                            ->where('id', $existingModuleId)
                            ->update([
                                'name' => $moduleName,
                                'description' => '',
                                'duration_minutes' => 0,
                                'updated_at' => now(),
                            ]);

                        $updatedModules++;
                        continue;
                    }

                    $moduleId = DB::table('modules')
                        ->insertGetId([
                            'name' => $moduleName,
                            'description' => '',
                            'duration_minutes' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    DB::table('course_modules')->insert([
                        'course_id' => $courseId,
                        'module_id' => $moduleId,
                        'order_index' => $orderIndex,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $insertedModules++;
                    $createdLinks++;
                }
            }
        });

        $this->command?->info(
            'Course modules imported successfully.'
        );

        $this->command?->info(
            "Inserted modules: {$insertedModules}"
        );

        $this->command?->info(
            "Updated modules: {$updatedModules}"
        );

        $this->command?->info(
            "Created course-module links: {$createdLinks}"
        );
    }

    private function normalizeText(mixed $value): string
    {
        return preg_replace(
            '/\s+/u',
            ' ',
            trim((string) $value)
        );
    }

    private function normalizeKey(mixed $value): string
    {
        return mb_strtolower(
            $this->normalizeText($value),
            'UTF-8'
        );
    }
}