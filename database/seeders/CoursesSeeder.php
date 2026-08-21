<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoursesSeeder extends Seeder
{
    public function run(): void
    {
        $datasetPath = storage_path('app/imports/afterclean.csv');

        if (! file_exists($datasetPath)) {
            throw new RuntimeException(
                "Dataset not found at: {$datasetPath}"
            );
        }

        $levels = DB::table('course_levels')
            ->pluck('id', 'name');

        $types = DB::table('course_types')
            ->pluck('id', 'name');

        $domains = DB::table('domains')
            ->pluck('id', 'name');

        $courses = $this->readCourses(
            $datasetPath,
            $levels,
            $types,
            $domains
        );

        DB::transaction(function () use ($courses) {
            foreach (array_chunk($courses, 250) as $chunk) {
                DB::table('courses')->insert($chunk);
            }
        });

        $this->command?->info(
            'Courses imported successfully.'
        );

        $this->command?->info(
            'Imported unique courses: '.count($courses)
        );
    }

    private function readCourses(
        string $path,
        $levels,
        $types,
        $domains
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

            $requiredColumns = [
                'course_title',
                'course_url',
                'rating',
                'level',
                'duration',
                'course_type',
                'schedule',
                'keyword',
            ];

            foreach ($requiredColumns as $column) {
                if (! isset($indexes[$column])) {
                    throw new RuntimeException(
                        "Missing Dataset column: {$column}"
                    );
                }
            }

            $courses = [];
            $seenTitles = [];
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

                if ($title === '') {
                    continue;
                }

                $titleKey = mb_strtolower(
                    $title,
                    'UTF-8'
                );

                // الاحتفاظ بأول ظهور لكل اسم كورس
                if (isset($seenTitles[$titleKey])) {
                    continue;
                }

                $levelName = $this->normalizeText(
                    $row[$indexes['level']] ?? ''
                );

                $typeName = $this->normalizeText(
                    $row[$indexes['course_type']] ?? ''
                );

                $domainName = $this->normalizeText(
                    $row[$indexes['keyword']] ?? ''
                );

                $typeId = $types[$typeName] ?? null;
                $domainId = $domains[$domainName] ?? null;

                if (! $typeId) {
                    throw new RuntimeException(
                        "Course type not found: {$typeName}"
                    );
                }

                if (! $domainId) {
                    throw new RuntimeException(
                        "Domain not found: {$domainName}"
                    );
                }

                $levelId = $levelName !== ''
                    ? ($levels[$levelName] ?? null)
                    : null;

                if ($levelName !== '' && ! $levelId) {
                    throw new RuntimeException(
                        "Course level not found: {$levelName}"
                    );
                }

                $ratingValue = trim(
                    (string) ($row[$indexes['rating']] ?? '')
                );

                $durationText = $this->normalizeText(
                    $row[$indexes['duration']] ?? ''
                );

                $schedule = $this->normalizeText(
                    $row[$indexes['schedule']] ?? ''
                );

                $url = trim(
                    (string) ($row[$indexes['course_url']] ?? '')
                );

                $courses[] = [
                    'title' => $title,
                    'url' => $url,
                    'thumbnail' => '',
                    'price' => 0,
                    'is_free' => true,
                    'language' => 'English',
                    'is_published' => true,
                    'duration_minutes' =>
                        $this->durationToMinutes($durationText),
                    'level_id' => $levelId,
                    'type_id' => $typeId,
                    'description' => '',
                    'schedule' => $schedule !== ''
                        ? $schedule
                        : 'Flexible schedule',
                    'domain_id' => $domainId,
                    'average_rating' =>
                        is_numeric($ratingValue)
                            ? (float) $ratingValue
                            : 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $seenTitles[$titleKey] = true;
            }

            return $courses;
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

    private function durationToMinutes(string $duration): int
    {
        if ($duration === '') {
            return 0;
        }

        $duration = mb_strtolower(
            $duration,
            'UTF-8'
        );

        $numberWords = [
            'one' => '1',
            'two' => '2',
            'three' => '3',
            'four' => '4',
            'five' => '5',
            'six' => '6',
            'seven' => '7',
            'eight' => '8',
            'nine' => '9',
            'ten' => '10',
            'eleven' => '11',
            'twelve' => '12',
        ];

        $duration = preg_replace_callback(
            '/\b(one|two|three|four|five|six|seven|eight|nine|ten|eleven|twelve)\b/',
            fn ($match) => $numberWords[$match[1]],
            $duration
        );

        // مثال: 3 months at 10 hours a week
        if (
            preg_match(
                '/(\d+(?:\.\d+)?)\s*months?\s+at\s+(\d+(?:\.\d+)?)\s*hours?\s+a\s+week/',
                $duration,
                $matches
            )
        ) {
            $months = (float) $matches[1];
            $hoursPerWeek = (float) $matches[2];

            return (int) round(
                $months * 4 * $hoursPerWeek * 60
            );
        }

        $totalMinutes = 0;

        // أمثلة: 1 hour أو 1.5 hours
        if (
            preg_match(
                '/(\d+(?:\.\d+)?)\s*hours?/',
                $duration,
                $hoursMatch
            )
        ) {
            $totalMinutes +=
                (float) $hoursMatch[1] * 60;
        }

        // أمثلة: 30 minutes أو 1 hour and 15 minutes
        if (
            preg_match(
                '/(\d+)\s*minutes?/',
                $duration,
                $minutesMatch
            )
        ) {
            $totalMinutes +=
                (int) $minutesMatch[1];
        }

        return (int) round($totalMinutes);
    }
}