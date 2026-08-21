<?php

namespace Database\Seeders;

use App\Models\InstructorProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UsersFromDatasetSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $instructorRole = Role::firstOrCreate([
            'name' => 'instructor',
            'guard_name' => 'web',
        ]);

        $studentRole = Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $datasetPath = storage_path('app/imports/afterclean.csv');

        if (! file_exists($datasetPath)) {
            throw new RuntimeException(
                "Dataset not found at: {$datasetPath}"
            );
        }

        $instructors = $this->readInstructorsFromDataset($datasetPath);

        $adminPassword = Hash::make('Admin@123456');
        $instructorPassword = Hash::make('Instructor@123456');
        $studentPassword = Hash::make('Student@123456');

        $studentCount = 30;
        $faker = fake('en_US');

        DB::transaction(function () use (
            $instructors,
            $adminRole,
            $instructorRole,
            $studentRole,
            $adminPassword,
            $instructorPassword,
            $studentPassword,
            $studentCount,
            $faker
        ) {
            /*
            |--------------------------------------------------------------------------
            | Admin
            |--------------------------------------------------------------------------
            */

            $admin = User::firstOrNew([
                'email' => 'admin@masar.test',
            ]);

            $admin->name = 'Masar Admin';
            $admin->avatar = '';
            $admin->is_active = true;
            $admin->last_login_at = null;
            $admin->timezone = 'UTC';
            $admin->email_verified_at = now();
            $admin->password = $adminPassword;
            $admin->remember_token = null;
            $admin->save();

            $admin->syncRoles([$adminRole]);

            /*
            |--------------------------------------------------------------------------
            | Instructors imported from Dataset
            |--------------------------------------------------------------------------
            */

            foreach ($instructors as $instructorData) {
                $name = $instructorData['name'];
                $email = $this->makeInstructorEmail($name);

                $user = User::firstOrNew([
                    'email' => $email,
                ]);

                $user->name = $name;
                $user->avatar = '';
                $user->is_active = true;
                $user->last_login_at = null;
                $user->timezone = 'UTC';
                $user->email_verified_at = now();
                $user->password = $instructorPassword;
                $user->remember_token = null;
                $user->save();

                $user->syncRoles([$instructorRole]);

                $specialization = implode(
                    ', ',
                    array_keys($instructorData['specializations'])
                );

                if ($specialization === '') {
                    $specialization = 'General';
                }

                $ratings = $instructorData['ratings'];

                $averageRating = count($ratings) > 0
                    ? round(array_sum($ratings) / count($ratings), 2)
                    : 0;

                $profile = InstructorProfile::firstOrNew([
                    'user_id' => $user->id,
                ]);

                $profile->bio = 'Instructor imported from the course dataset.';
                $profile->specialization = Str::limit(
                    $specialization,
                    250,
                    ''
                );
                $profile->linkedin_url = '';
                $profile->years_experience = 0;
                $profile->website_url = '';
                $profile->average_rating = $averageRating;
                $profile->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Fake Students
            |--------------------------------------------------------------------------
            */

            for ($number = 1; $number <= $studentCount; $number++) {
                $formattedNumber = str_pad(
                    (string) $number,
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                $user = User::firstOrNew([
                    'email' => "student{$formattedNumber}@masar.test",
                ]);

                $user->name = $faker->name();
                $user->avatar = '';
                $user->is_active = true;
                $user->last_login_at = null;
                $user->timezone = 'UTC';
                $user->email_verified_at = now();
                $user->password = $studentPassword;
                $user->remember_token = null;
                $user->save();

                $user->syncRoles([$studentRole]);

                $profile = StudentProfile::firstOrNew([
                    'user_id' => $user->id,
                ]);

                $profile->phone = $faker->phoneNumber();
                $profile->github_url =
                    "https://github.com/masar-student-{$formattedNumber}";
                $profile->birth_date = $faker
                    ->dateTimeBetween('-35 years', '-18 years')
                    ->format('Y-m-d');
                $profile->country = $faker->country();
                $profile->save();
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(
            'Users, roles, instructor profiles and student profiles imported successfully.'
        );

        $this->command?->info(
            'Imported instructors: '.count($instructors)
        );

        $this->command?->info(
            "Created fake students: {$studentCount}"
        );
    }

    private function readInstructorsFromDataset(string $path): array
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

            $columnIndexes = array_flip($headers);

            foreach (['instructor', 'keyword', 'rating'] as $column) {
                if (! isset($columnIndexes[$column])) {
                    throw new RuntimeException(
                        "Missing Dataset column: {$column}"
                    );
                }
            }

            $instructors = [];

            while (
                ($row = fgetcsv(
                    $handle,
                    null,
                    ',',
                    '"',
                    '\\'
                )) !== false
            ) {
                $rawInstructors = trim(
                    (string) ($row[$columnIndexes['instructor']] ?? '')
                );

                $keyword = trim(
                    (string) ($row[$columnIndexes['keyword']] ?? '')
                );

                $ratingValue = trim(
                    (string) ($row[$columnIndexes['rating']] ?? '')
                );

                $names = $this->parseList($rawInstructors);

                foreach ($names as $name) {
                    $name = preg_replace(
                        '/\s+/u',
                        ' ',
                        trim($name)
                    );

                    if ($name === '') {
                        continue;
                    }

                    $key = mb_strtolower($name, 'UTF-8');

                    if (! isset($instructors[$key])) {
                        $instructors[$key] = [
                            'name' => $name,
                            'specializations' => [],
                            'ratings' => [],
                        ];
                    }

                    if ($keyword !== '') {
                        $instructors[$key]['specializations'][$keyword] = true;
                    }

                    if (
                        $ratingValue !== '' &&
                        is_numeric($ratingValue)
                    ) {
                        $instructors[$key]['ratings'][] =
                            (float) $ratingValue;
                    }
                }
            }

            return array_values($instructors);
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

    private function makeInstructorEmail(string $name): string
    {
        $normalizedName = mb_strtolower(
            preg_replace('/\s+/u', ' ', trim($name)),
            'UTF-8'
        );

        $identifier = substr(
            sha1($normalizedName),
            0,
            16
        );

        return "instructor.{$identifier}@masar.test";
    }
}