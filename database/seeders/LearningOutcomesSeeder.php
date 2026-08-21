<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LearningOutcomesSeeder extends Seeder
{
    public function run(): void
    {
        $datasetPath = storage_path('app/imports/afterclean.csv');

        if (! file_exists($datasetPath)) {
            throw new RuntimeException(
                "Dataset not found at: {$datasetPath}"
            );
        }

        $courseIds = DB::table('courses')
            ->get(['id', 'title'])
            ->mapWithKeys(fn ($course) => [
                $this->normalizeKey($course->title) => $course->id,
            ])
            ->all();

        $outcomes = $this->readOutcomes(
            $datasetPath,
            $courseIds
        );

        $inserted = 0;

        DB::transaction(function () use (
            $outcomes,
            &$inserted
        ) {
            foreach ($outcomes as $outcome) {
                $exists = DB::table('learning_outcomes')
                    ->where('course_id', $outcome['course_id'])
                    ->where('content', $outcome['content'])
                    ->exists();

                if (! $exists) {
                    DB::table('learning_outcomes')
                        ->insert($outcome);

                    $inserted++;
                }
            }
        });

        $this->command?->info(
            'Learning outcomes imported successfully.'
        );

        $this->command?->info(
            "Inserted learning outcomes: {$inserted}"
        );
    }

    private function readOutcomes(
        string $path,
        array $courseIds
    ): array {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException(
                'Could not open the Dataset.'
            );
        }

        try {
            $headers = fgetcsv(
                $handle,
                null,
                ',',
                '"',
                '\\'
            );

            if ($headers === false) {
                throw new RuntimeException(
                    'The Dataset is empty.'
                );
            }

            $headers = array_map(function ($header) {
                return preg_replace(
                    '/^\xEF\xBB\xBF/',
                    '',
                    trim((string) $header)
                );
            }, $headers);

            $indexes = array_flip($headers);

            foreach (
                ['course_title', 'what_you_will_learn']
                as $column
            ) {
                if (! isset($indexes[$column])) {
                    throw new RuntimeException(
                        "Missing Dataset column: {$column}"
                    );
                }
            }

            $outcomes = [];
            $sortOrders = [];
            $now = now();

            while (
                ($row = fgetcsv(
                    $handle,
                    null,
                    ',',
                    '"',
                    '\\'
                )) !== false
            ) {
                $title = $this->normalizeText(
                    $row[$indexes['course_title']] ?? ''
                );

                $content = $this->normalizeText(
                    $row[
                        $indexes['what_you_will_learn']
                    ] ?? ''
                );

                if ($title === '' || $content === '') {
                    continue;
                }

                $courseId =
                    $courseIds[
                        $this->normalizeKey($title)
                    ] ?? null;

                if (! $courseId) {
                    continue;
                }

                $uniqueKey = $courseId.':'.sha1($content);

                if (isset($outcomes[$uniqueKey])) {
                    continue;
                }

                $sortOrders[$courseId] =
                    ($sortOrders[$courseId] ?? 0) + 1;

                $outcomes[$uniqueKey] = [
                    'course_id' => $courseId,
                    'content' => $content,
                    'sort_order' => $sortOrders[$courseId],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            return array_values($outcomes);
        } finally {
            fclose($handle);
        }
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