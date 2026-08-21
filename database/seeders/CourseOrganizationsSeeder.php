<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CourseOrganizationsSeeder extends Seeder
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

        $organizationIds = DB::table('organizations')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($organization) => [
                $this->normalizeKey($organization->name) =>
                    $organization->id,
            ])
            ->all();

        $links = $this->readLinks(
            $datasetPath,
            $courseIds,
            $organizationIds
        );

        DB::transaction(function () use ($links) {
            foreach (array_chunk($links, 500) as $chunk) {
                DB::table('course_organizations')
                    ->insertOrIgnore($chunk);
            }
        });

        $this->command?->info(
            'Course organization links imported successfully.'
        );

        $this->command?->info(
            'Imported links: '.count($links)
        );
    }

    private function readLinks(
        string $path,
        array $courseIds,
        array $organizationIds
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
                ['course_title', 'offered_by'] as $column
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
                $title = $this->normalizeText(
                    $row[$indexes['course_title']] ?? ''
                );

                if ($title === '') {
                    continue;
                }

                $courseId =
                    $courseIds[$this->normalizeKey($title)] ?? null;

                if (! $courseId) {
                    continue;
                }

                $rawOrganizations = trim(
                    (string) (
                        $row[$indexes['offered_by']] ?? ''
                    )
                );

                foreach (
                    $this->parseList($rawOrganizations)
                    as $organizationName
                ) {
                    $organizationName = $this->normalizeText(
                        $organizationName
                    );

                    if ($organizationName === '') {
                        continue;
                    }

                    $organizationId =
                        $organizationIds[
                            $this->normalizeKey($organizationName)
                        ] ?? null;

                    if (! $organizationId) {
                        throw new RuntimeException(
                            "Organization not found: {$organizationName}"
                        );
                    }

                    $key = "{$courseId}:{$organizationId}";

                    $links[$key] = [
                        'course_id' => $courseId,
                        'organization_id' => $organizationId,
                    ];
                }
            }

            return array_values($links);
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