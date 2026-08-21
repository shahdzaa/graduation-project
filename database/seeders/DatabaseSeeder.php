<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UsersFromDatasetSeeder::class,
            CourseLevelsSeeder::class,
            CourseTypesSeeder::class,
            DomainsSeeder::class,
            OrganizationsSeeder::class,
            CoursesSeeder::class,
            CourseOrganizationsSeeder::class,
            CourseInstructorsSeeder::class,
            LearningOutcomesSeeder::class,
            SyllabusTypesSeeder::class,
            CourseModulesSeeder::class,
            SyllabusSeeder::class,
            SkillsSeeder::class,
            SyllabusCategoriesSeeder::class,
            StudentActivitySeeder::class,
        ]);
    }
}
