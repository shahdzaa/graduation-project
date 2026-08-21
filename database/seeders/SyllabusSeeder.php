<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;

class SyllabusSeeder extends Seeder
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

        $topicTypeId = DB::table('syllabus_types')
            ->where('name', 'Topic')
            ->value('id');

        if (! $topicTypeId) {
            throw new RuntimeException(
                'Syllabus type "Topic" was not found.'
            );
        }

        $moduleIds = [];

        $courseModuleRows = DB::table('course_modules')
            ->join(
                'courses',
                'course_modules.course_id',
                '=',
                'courses.id'
            )
            ->get([
                'courses.title as course_title',
                'course_modules.module_id',
                'course_modules.order_index',
            ]);

        foreach ($courseModuleRows as $row) {
            $key = $this->moduleKey(
                $row->course_title,
                (int) $row->order_index
            );

            $moduleIds[$key] = $row->module_id;
        }

        $processedTopics = 0;
        $buffer = [];
        $now = now();

        DB::transaction(function () use (
            $structure,
            $topicTypeId,
            $moduleIds,
            $now,
            &$processedTopics,
            &$buffer
        ) {
            foreach ($structure as $courseData) {
                $courseTitle = $this->normalizeText(
                    $courseData['course_title'] ?? ''
                );

                $modules = $courseData['modules'] ?? [];

                foreach ($modules as $moduleIndex => $moduleData) {
                    $moduleOrder = $moduleIndex + 1;

                    $moduleKey = $this->moduleKey(
                        $courseTitle,
                        $moduleOrder
                    );

                    $moduleId = $moduleIds[$moduleKey] ?? null;

                    if (! $moduleId) {
                        throw new RuntimeException(
                            "Module not found for course: ".
                            "{$courseTitle}, order: {$moduleOrder}"
                        );
                    }

                    $topics = $moduleData['topics'] ?? [];

                    foreach ($topics as $topicIndex => $topic) {
                        $topicName = $this->normalizeText(
                            $topic
                        );

                        if ($topicName === '') {
                            continue;
                        }

                        $buffer[] = [
                            'module_id' => $moduleId,
                            'name' => mb_substr(
                                $topicName,
                                0,
                                255,
                                'UTF-8'
                            ),
                            'order_index' => $topicIndex + 1,
                            'type_id' => $topicTypeId,
                            'duration_minutes' => 0,
                            'category_id' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $processedTopics++;

                        if (count($buffer) >= 1000) {
                            $this->saveBuffer($buffer);
                            $buffer = [];
                        }
                    }
                }
            }

            if ($buffer !== []) {
                $this->saveBuffer($buffer);
                $buffer = [];
            }
        });

        $this->command?->info(
            'Syllabus topics imported successfully.'
        );

        $this->command?->info(
            "Processed syllabus topics: {$processedTopics}"
        );
    }

    private function saveBuffer(array $buffer): void
    {
        DB::table('syllabus')->upsert(
            $buffer,
            [
                'module_id',
                'order_index',
            ],
            [
                'name',
                'type_id',
                'duration_minutes',
                'updated_at',
            ]
        );
    }

    private function moduleKey(
        string $courseTitle,
        int $moduleOrder
    ): string {
        return $this->normalizeKey($courseTitle)
            .'|'.$moduleOrder;
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