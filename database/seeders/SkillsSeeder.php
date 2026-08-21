<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;

class SkillsSeeder extends Seeder
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

        $uniqueSkills = [];

        foreach ($structure as $courseData) {
            foreach (
                ($courseData['skills'] ?? [])
                as $skillName
            ) {
                $skillName = $this->normalizeText(
                    $skillName
                );

                if ($skillName === '') {
                    continue;
                }

                $uniqueSkills[
                    $this->normalizeKey($skillName)
                ] = $skillName;
            }
        }

        $existingSkillKeys = DB::table('skills')
            ->get(['name'])
            ->mapWithKeys(fn ($skill) => [
                $this->normalizeKey($skill->name) => true,
            ])
            ->all();

        $newSkills = [];
        $now = now();

        foreach ($uniqueSkills as $key => $skillName) {
            if (isset($existingSkillKeys[$key])) {
                continue;
            }

            $newSkills[] = [
                'name' => $skillName,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($newSkills) {
            foreach (
                array_chunk($newSkills, 500)
                as $chunk
            ) {
                DB::table('skills')->insert($chunk);
            }
        });

        $skillIds = DB::table('skills')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($skill) => [
                $this->normalizeKey($skill->name)
                    => $skill->id,
            ])
            ->all();

        $links = [];

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

            foreach (
                ($courseData['skills'] ?? [])
                as $skillName
            ) {
                $skillName = $this->normalizeText(
                    $skillName
                );

                if ($skillName === '') {
                    continue;
                }

                $skillId =
                    $skillIds[
                        $this->normalizeKey($skillName)
                    ] ?? null;

                if (! $skillId) {
                    throw new RuntimeException(
                        "Skill not found: {$skillName}"
                    );
                }

                $key = "{$courseId}:{$skillId}";

                $links[$key] = [
                    'course_id' => $courseId,
                    'skill_id' => $skillId,
                ];
            }
        }

        $links = array_values($links);

        DB::transaction(function () use ($links) {
            foreach (
                array_chunk($links, 500)
                as $chunk
            ) {
                DB::table('course_skills')
                    ->insertOrIgnore($chunk);
            }
        });

        $this->command?->info(
            'Skills imported successfully.'
        );

        $this->command?->info(
            'New skills inserted: '.count($newSkills)
        );

        $this->command?->info(
            'Course-skill links processed: '.count($links)
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