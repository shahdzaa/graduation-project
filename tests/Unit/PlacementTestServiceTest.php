<?php

namespace Tests\Unit;

use App\Models\PlacementAnswerOption;
use App\Models\PlacementAttempt;
use App\Models\PlacementQuestion;
use App\Models\User;
use App\Services\PlacementTestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlacementTestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_generated_test_persists_questions_and_returns_student_payload(): void
    {
        $service = new PlacementTestService();
        $user = User::create([
            'name' => 'Ali User',
            'email' => 'ali@example.com',
            'password' => bcrypt('password123'),
        ]);

        $questions = [
            [
                'question_number' => 1,
                'question_text' => 'What is PHP?',
                'difficulty_level' => 'Beginner',
                'syllabus_topic' => 'Variables',
                'options' => ['A' => 'Language', 'B' => 'Animal', 'C' => 'Fruit', 'D' => 'City'],
                'correct_answer' => 'A',
                'explanation' => 'PHP is a language.',
            ],
        ];

        $result = $service->storeGeneratedTest($user->id, 'Web Development', $questions);

        $this->assertSame(1, PlacementQuestion::count());
        $this->assertSame(1, PlacementAttempt::count());
        $this->assertArrayHasKey('attempt_id', $result);
        $this->assertArrayHasKey('questions_for_student', $result);
        $this->assertSame('What is PHP?', $result['questions_for_student'][0]['question_text'] ?? null);
        $this->assertSame(4, count($result['questions_for_student'][0]['options']));
        $this->assertArrayNotHasKey('correct_answer', $result['questions_for_student'][0]);
        $this->assertSame('A', $result['questions_for_student'][0]['options'][0]['option_key']);
    }

    public function test_submit_answers_scores_correct_answers_and_collects_known_syllabi(): void
    {
        $service = new PlacementTestService();
        $user = User::create([
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => bcrypt('password123'),
        ]);

        $question1 = PlacementQuestion::create([
            'category' => 'Web Development',
            'question_number' => 1,
            'question_text' => 'Which language is used for frontend?',
            'difficulty_level' => 'Beginner',
            'syllabus_topic' => 'HTML',
            'generation_batch_id' => 'batch-1',
        ]);

        $option1A = PlacementAnswerOption::create([
            'placement_question_id' => $question1->id,
            'option_key' => 'A',
            'option_text' => 'HTML',
            'is_correct' => true,
        ]);

        $option1B = PlacementAnswerOption::create([
            'placement_question_id' => $question1->id,
            'option_key' => 'B',
            'option_text' => 'PHP',
            'is_correct' => false,
        ]);

        $question2 = PlacementQuestion::create([
            'category' => 'Web Development',
            'question_number' => 2,
            'question_text' => 'Which CSS property changes color?',
            'difficulty_level' => 'Beginner',
            'syllabus_topic' => 'CSS',
            'generation_batch_id' => 'batch-1',
        ]);

        $option2A = PlacementAnswerOption::create([
            'placement_question_id' => $question2->id,
            'option_key' => 'A',
            'option_text' => 'color',
            'is_correct' => true,
        ]);

        $option2B = PlacementAnswerOption::create([
            'placement_question_id' => $question2->id,
            'option_key' => 'B',
            'option_text' => 'margin',
            'is_correct' => false,
        ]);

        $attempt = PlacementAttempt::create([
            'user_id' => $user->id,
            'category' => 'Web Development',
            'generation_batch_id' => 'batch-1',
            'status' => 'pending',
        ]);

        $result = $service->submitAnswers($attempt->id, [
            [
                'question_id' => $question1->id,
                'selected_option_id' => $option1A->id,
            ],
            [
                'question_id' => $question2->id,
                'selected_option_id' => $option2B->id,
            ],
        ]);

        $this->assertSame(1, $result['score']);
        $this->assertSame(2, $result['total']);
        $this->assertSame(['HTML'], $result['known_syllabi']);
        $this->assertSame('completed', $attempt->fresh()->status);
    }
}
