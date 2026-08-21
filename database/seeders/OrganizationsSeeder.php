<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrganizationsSeeder extends Seeder
{
    public function run(): void
    {
        $datasetPath = storage_path('app/imports/afterclean.csv');

        if (! file_exists($datasetPath)) {
            throw new RuntimeException(
                "Dataset not found at: {$datasetPath}"
            );
        }

        $organizations = $this->readOrganizations($datasetPath);

        foreach ($organizations as $organizationName) {
            $exists = DB::table('organizations')
                ->where('name', $organizationName)
                ->exists();

            if (! $exists) {
                DB::table('organizations')->insert([
                    'name' => $organizationName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command?->info(
            'Organizations imported successfully.'
        );

        $this->command?->info(
            'Imported organizations: '.count($organizations)
        );
    }

    private function readOrganizations(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Could not open the Dataset.');
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
                throw new RuntimeException('The Dataset is empty.');
            }

            $headers = array_map(function ($header) {
                return preg_replace(
                    '/^\xEF\xBB\xBF/',
                    '',
                    trim((string) $header)
                );
            }, $headers);

            $columnIndex = array_search(
                'offered_by',
                $headers,
                true
            );

            if ($columnIndex === false) {
                throw new RuntimeException(
                    'Missing Dataset column: offered_by'
                );
            }

            $organizations = [];

            while (
                ($row = fgetcsv(
                    $handle,
                    null,
                    ',',
                    '"',
                    '\\'
                )) !== false
            ) {
                $rawValue = trim(
                    (string) ($row[$columnIndex] ?? '')
                );

                foreach ($this->parseList($rawValue) as $name) {
                    $name = preg_replace(
                        '/\s+/u',
                        ' ',
                        trim($name)
                    );

                    if ($name === '') {
                        continue;
                    }

                    $key = mb_strtolower($name, 'UTF-8');

                    if (! isset($organizations[$key])) {
                        $organizations[$key] = $name;
                    }
                }
            }

            return array_values($organizations);
        } finally {
            fclose($handle);
        }
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