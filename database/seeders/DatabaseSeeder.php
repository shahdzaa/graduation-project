<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
         DB::table('course_levels')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Beginner',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('course_levels')->updateOrInsert(
            ['id' => 2],
            [
                'name' => 'Intermediate',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('course_levels')->updateOrInsert(
            ['id' => 3],
            [
                'name' => 'Advanced',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('course_types')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Course',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('course_types')->updateOrInsert(
            ['id' => 2],
            [
                'name' => 'Specialization',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('course_types')->updateOrInsert(
            ['id' => 3],
            [
                'name' => 'Guided Project',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('course_types')->updateOrInsert(
            ['id' => 4],
            [
                'name' => 'Professional Certificate',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('course_types')->updateOrInsert(
            ['id' => 5],
            [
                'name' => 'Project',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $this->call([
        DomainsSeeder::class,
        CoursesSeeder::class,
        ModulesSeeder::class,
        SyllabusTypesSeeder::class,
        SyllabusSeeder::class,
        SkillsSeeder::class,
        CourseModulesSeeder::class,
        OrganizationsSeeder::class,
        CoursesOrganizationSeeder::class,
        InstructorSeeder::class,
        CourseInstructorSeeder::class,
        LearningOutcomeSeeder::class, 
        CategorySeeder::class,
         ]);
        
    }
}
