<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CourseInstructorsSeeder extends Seeder
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

        $userIdsByEmail = DB::table('users')
            ->pluck('id', 'email')
            ->all();

        $links = $this->readLinks(
            $datasetPath,
            $courseIds,
            $userIdsByEmail
        );

        DB::transaction(function () use ($links) {
            foreach (array_chunk($links, 500) as $chunk) {
                DB::table('course_instructors')
                    ->insertOrIgnore($chunk);
            }
        });

        $this->command?->info(
            'Course instructor links imported successfully.'
        );

        $this->command?->info(
            'Imported links: '.count($links)
        );
    }

    private function readLinks(
        string $path,
        array $courseIds,
        array $userIdsByEmail
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
                ['course_title', 'instructor'] as $column
            ) {
                if (! isset($indexes[$column])) {
                    throw new RuntimeException(
                        "Missing Dataset column: {$column}"
                    );
                }
            }

            $links = [];

            while (
                ($row = fgetcsv(
                    $handle,
                    null,
                    ',',
                    '"',
                    '\\'
                )) !== false
            ) {
                $courseTitle = $this->normalizeText(
                    $row[$indexes['course_title']] ?? ''
                );

                if ($courseTitle === '') {
                    continue;
                }

                $courseId =
                    $courseIds[
                        $this->normalizeKey($courseTitle)
                    ] ?? null;

                if (! $courseId) {
                    continue;
                }

                $rawInstructors = trim(
                    (string) (
                        $row[$indexes['instructor']] ?? ''
                    )
                );

                foreach (
                    $this->parseList($rawInstructors)
                    as $instructorName
                ) {
                    $instructorName = $this->normalizeText(
                        $instructorName
                    );

                    if ($instructorName === '') {
                        continue;
                    }

                    $email = $this->makeInstructorEmail(
                        $instructorName
                    );

                    $userId =
                        $userIdsByEmail[$email] ?? null;

                    if (! $userId) {
                        throw new RuntimeException(
                            "Instructor user not found: {$instructorName}"
                        );
                    }

                    $key = "{$courseId}:{$userId}";

                    $links[$key] = [
                        'course_id' => $courseId,
                        'user_id' => $userId,
                    ];
                }
            }

            return array_values($links);
        } finally {
            fclose($handle);
        }
    }

    private function makeInstructorEmail(string $name): string
    {
        $normalizedName = $this->normalizeKey($name);

        $identifier = substr(
            sha1($normalizedName),
            0,
            16
        );

        return "instructor.{$identifier}@masar.test";
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

    private function parseList(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        if (
            ! str_starts_with($value, '[') ||
            ! str_ends_with($value, ']')
        ) {
            return [$value];
        }

        $content = trim(substr($value, 1, -1));

        if ($content === '') {
            return [];
        }

        $result = [];
        $buffer = '';
        $quote = null;
        $escaped = false;
        $length = strlen($content);

        for ($index = 0; $index < $length; $index++) {
            $character = $content[$index];

            if ($escaped) {
                $buffer .= $character;
                $escaped = false;
                continue;
            }

            if ($quote !== null) {
                if ($character === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($character === $quote) {
                    $quote = null;
                    continue;
                }

                $buffer .= $character;
                continue;
            }

            if ($character === "'" || $character === '"') {
                $quote = $character;
                continue;
            }

            if ($character === ',') {
                $item = trim($buffer);

                if ($item !== '') {
                    $result[] = $item;
                }

                $buffer = '';
                continue;
            }

            $buffer .= $character;
        }

        $lastItem = trim($buffer);

        if ($lastItem !== '') {
            $result[] = $lastItem;
        }

        return array_values(array_unique($result));
    }
}