<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DomainsSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            [
                'name' => 'Computer Science',
                'description' => 'Courses related to computer science and programming.',
            ],
            [
                'name' => 'DataScience',
                'description' => 'Courses related to data science and data analysis.',
            ],
            [
                'name' => 'Information Technology',
                'description' => 'Courses related to information technology.',
            ],
            [
                'name' => 'Math and Logic',
                'description' => 'Courses related to mathematics and logical thinking.',
            ],
            [
                'name' => 'Physical Science and Engineering',
                'description' => 'Courses related to physical sciences and engineering.',
            ],
        ];

        foreach ($domains as $domain) {
            $exists = DB::table('domains')
                ->where('name', $domain['name'])
                ->exists();

            if (! $exists) {
                DB::table('domains')->insert([
                    'name' => $domain['name'],
                    'description' => $domain['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command?->info(
            'Domains imported successfully.'
        );
    }
}