<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DomainsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $domains = [
            ['id' => 1, 'name' => 'Computer Science', 'description' => null],
            ['id' => 2, 'name' => 'DataScience', 'description' => null],
            ['id' => 3, 'name' => 'Information Technology', 'description' => null],
            ['id' => 4, 'name' => 'Math and Logic', 'description' => null],
            ['id' => 5, 'name' => 'Physical Science and Engineering', 'description' => null],
        ];

        foreach ($domains as $domain) {
            DB::table('domains')->updateOrInsert(
                ['id' => $domain['id']],
                [
                    'name' => $domain['name'],
                    'description' => $domain['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
