<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StudentActivitySeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('en_US');
        $faker->seed(20260820);

        $studentIds = User::role('student')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($studentIds === []) {
            throw new RuntimeException(
                'No users with student role were found.'
            );
        }

        $courses = DB::table('courses')
            ->orderBy('id')
            ->get([
                'id',
                'average_rating',
            ])
            ->keyBy('id');

        if ($courses->isEmpty()) {
            throw new RuntimeException(
                'No courses were found.'
            );
        }

        $courseIds = $courses->keys()->all();

        $reviewComments = [
            'The course was clear and well structured.',
            'I learned useful skills and practical concepts.',
            'The content was helpful and easy to follow.',
            'A valuable course with good explanations.',
            'The modules were organized and informative.',
            'The course provided a strong introduction to the topic.',
            'I enjoyed the practical examples and exercises.',
            'The instructor explained the concepts clearly.',
            'The course met my learning expectations.',
            'Good course, but some topics required more examples.',
        ];

        $studentCourseCount = 0;
        $reviewCount = 0;

        DB::transaction(function () use (
            $faker,
            $studentIds,
            $courses,
            $courseIds,
            $reviewComments,
            &$studentCourseCount,
            &$reviewCount
        ) {
            foreach ($studentIds as $studentId) {
                $numberOfCourses = $faker->numberBetween(
                    8,
                    15
                );

                $selectedCourseIds = $faker->randomElements(
                    $courseIds,
                    $numberOfCourses
                );

                foreach ($selectedCourseIds as $courseId) {
                    $statusRoll = $faker->numberBetween(
                        1,
                        100
                    );

                    if ($statusRoll <= 20) {
                        $status = 'active';
                        $progress = 0;
                    } elseif ($statusRoll <= 75) {
                        $status = 'active';
                        $progress = $faker->numberBetween(
                            5,
                            95
                        );
                    } else {
                        $status = 'completed';
                        $progress = 100;
                    }

                    $enrolledAt = Carbon::instance(
                        $faker->dateTimeBetween(
                            '-12 months',
                            'now'
                        )
                    );

                    DB::table('student_courses')
                        ->updateOrInsert(
                            [
                                'user_id' => $studentId,
                                'course_id' => $courseId,
                            ],
                            [
                                'enrolled_at' => $enrolledAt,
                                'status' => $status,
                                'progress_percent' => $progress,
                                'created_at' => $enrolledAt,
                                'updated_at' => now(),
                            ]
                        );

                    $studentCourseCount++;

                    if (
                        $status !== 'completed' ||
                        ! $faker->boolean(70)
                    ) {
                        continue;
                    }

                    $courseRating = (float) (
                        $courses[$courseId]->average_rating
                        ?? 0
                    );

                    if ($courseRating <= 0) {
                        $courseRating = 4;
                    }

                    $ratingAdjustment = $faker->randomElement([
                        -1,
                        0,
                        0,
                        0,
                        1,
                    ]);

                    $rating = (int) round(
                        $courseRating + $ratingAdjustment
                    );

                    $rating = max(
                        1,
                        min(5, $rating)
                    );

                    $reviewedAt = Carbon::instance(
                        $faker->dateTimeBetween(
                            $enrolledAt,
                            'now'
                        )
                    );

                    DB::table('course_reviews')
                        ->updateOrInsert(
                            [
                                'course_id' => $courseId,
                                'user_id' => $studentId,
                            ],
                            [
                                'rating' => $rating,
                                'comment' => $faker->randomElement(
                                    $reviewComments
                                ),
                                'created_at' => $reviewedAt,
                                'updated_at' => $reviewedAt,
                            ]
                        );

                    $reviewCount++;
                }
            }
        });

        $this->command?->info(
            'Fake student activity created successfully.'
        );

        $this->command?->info(
            "Student-course records processed: {$studentCourseCount}"
        );

        $this->command?->info(
            "Course reviews processed: {$reviewCount}"
        );
    }
}