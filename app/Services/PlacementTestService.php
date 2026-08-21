<?php

namespace App\Services;

use App\Models\PlacementAnswerOption;
use App\Models\PlacementAttempt;
use App\Models\PlacementQuestion;
use App\Models\PlacementUserAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlacementTestService
{
    public function storeGeneratedTest(int $userId, string $category, array $questions): array
    {
        return DB::transaction(function () use ($userId, $category, $questions) {
            $batchId = (string) Str::uuid();
            $questionModels = [];
            $optionRows = [];
            $now = now();

            foreach ($questions as $q) {
                $question = PlacementQuestion::create([
                    'category'            => $category,
                    'question_number'     => $q['question_number'],
                    'question_text'       => $q['question_text'],
                    'difficulty_level'    => $q['difficulty_level'],
                    'syllabus_topic'      => $q['syllabus_topic'] ?? null,
                    'explanation'         => $q['explanation'] ?? null,
                    'generation_batch_id' => $batchId,
                ]);

                foreach ($q['options'] as $key => $text) {
                    $optionRows[] = [
                        'placement_question_id' => $question->id,
                        'option_key'            => $key,
                        'option_text'           => $text,
                        'is_correct'            => ($key === $q['correct_answer']),
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ];
                }

                $questionModels[$question->id] = $question;
            }

            DB::table('placement_answer_options')->insert($optionRows);

            $optionsByQuestion = PlacementAnswerOption::query()
                ->whereIn('placement_question_id', array_keys($questionModels))
                ->select(['id', 'placement_question_id', 'option_key', 'option_text'])
                ->orderBy('placement_question_id')
                ->orderBy('option_key')
                ->get()
                ->groupBy('placement_question_id');

            $questionsForStudent = [];
            foreach ($questionModels as $question) {
                $questionsForStudent[] = [
                    'question_id' => $question->id,
                    'question_number' => $question->question_number,
                    'question_text' => $question->question_text,
                    'difficulty_level' => $question->difficulty_level,
                    'options' => $optionsByQuestion->get($question->id, collect())
                        ->map(fn ($option) => [
                            'option_id' => $option->id,
                            'option_key' => $option->option_key,
                            'option_text' => $option->option_text,
                        ])
                        ->values(),
                ];
            }

            $attempt = PlacementAttempt::create([
                'user_id'             => $userId,
                'category'            => $category,
                'generation_batch_id' => $batchId,
                'status'              => 'pending',
            ]);

            return [
                'attempt_id'            => $attempt->id,
                'questions_for_student' => $questionsForStudent,
            ];
        });
    }

    public function submitAnswers(int $attemptId, array $answers): array
    {
        return DB::transaction(function () use ($attemptId, $answers) {
            $attempt = PlacementAttempt::query()->lockForUpdate()->findOrFail($attemptId);

            if ($attempt->status === 'completed') {
                return [
                    'score'         => $attempt->total_score,
                    'total'         => $this->resolveAttemptTotal($attempt),
                    'known_syllabi' => $attempt->known_syllabi,
                ];
            }

            $questionIds = array_values(array_unique(array_column($answers, 'question_id')));
            $optionIds = array_values(array_unique(array_column($answers, 'selected_option_id')));

            $allowedQuestionIds = PlacementQuestion::query()
                ->where('generation_batch_id', $attempt->generation_batch_id)
                ->whereIn('id', $questionIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $allowedQuestions = array_fill_keys($allowedQuestionIds, true);
            $options = PlacementAnswerOption::query()
                ->whereIn('id', $optionIds)
                ->select(['id', 'placement_question_id'])
                ->get()
                ->keyBy('id');

            $rows = [];
            $now = now();
            foreach ($answers as $answer) {
                $questionId = (int) $answer['question_id'];
                $optionId = (int) $answer['selected_option_id'];
                $option = $options->get($optionId);

                if (! isset($allowedQuestions[$questionId]) || ! $option || (int) $option->placement_question_id !== $questionId) {
                    throw new \InvalidArgumentException(
                        "Option {$optionId} لا تنتمي للسؤال {$questionId} أو للمحاولة الحالية"
                    );
                }

                $rows[] = [
                    'attempt_id' => $attemptId,
                    'placement_question_id' => $questionId,
                    'selected_option_id' => $optionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            PlacementUserAnswer::upsert(
                $rows,
                ['attempt_id', 'placement_question_id'],
                ['selected_option_id', 'updated_at']
            );

            $userAnswers = DB::table('placement_user_answers')
                ->join('placement_answer_options', 'placement_answer_options.id', '=', 'placement_user_answers.selected_option_id')
                ->join('placement_questions', 'placement_questions.id', '=', 'placement_user_answers.placement_question_id')
                ->where('placement_user_answers.attempt_id', $attemptId)
                ->select(['placement_answer_options.is_correct', 'placement_questions.syllabus_topic'])
                ->get();

            $score = 0;
            $knownSyllabi = [];

            foreach ($userAnswers as $answer) {
                if ($answer->is_correct) {
                    $score++;
                    $syllabus = $answer->syllabus_topic;
                    if ($syllabus && ! in_array($syllabus, $knownSyllabi, true)) {
                        $knownSyllabi[] = $syllabus;
                    }
                }
            }

            $attempt->update([
                'total_score'   => $score,
                'known_syllabi' => $knownSyllabi,
                'end_time'      => now(),
                'status'        => 'completed',
            ]);

            return [
                'score'         => $score,
                'total'         => $this->resolveAttemptTotal($attempt),
                'known_syllabi' => $knownSyllabi,
            ];
        });
    }

    protected function resolveAttemptTotal(PlacementAttempt $attempt): int
    {
        if (! empty($attempt->generation_batch_id)) {
            $count = PlacementQuestion::where('generation_batch_id', $attempt->generation_batch_id)->count();
            if ($count > 0) {
                return (int) $count;
            }
        }

        return 25;
    }

    /**
     * Legacy compatibility method used by the category-placement route.
     * It converts syllabus items into a balanced topic list for the AI generator.
     */
    public function generateThresholdBlocks(array $features, int $targetQuestions = 25): array
    {
        $features = $this->sanitizeFeatures($features);

        if (empty($features) || $targetQuestions < 1) {
            return [];
        }

        $selected = $this->spreadAcrossModules($features, $targetQuestions);

        if (count($selected) < $targetQuestions) {
            $selectedKeys = [];
            foreach ($selected as $item) {
                $selectedKeys[$this->featureKey($item)] = true;
            }

            $remaining = array_values(array_filter(
                $features,
                fn (array $item) => ! isset($selectedKeys[$this->featureKey($item)])
            ));

            foreach ($remaining as $item) {
                if (count($selected) >= $targetQuestions) {
                    break;
                }
                $selected[] = $item;
            }
        }

        $selected = array_slice($selected, 0, $targetQuestions);

        usort($selected, function (array $left, array $right) {
            $leftKey = sprintf('%010u', crc32(($left['module_id'] ?? '') . '|' . ($left['topic'] ?? '')));
            $rightKey = sprintf('%010u', crc32(($right['module_id'] ?? '') . '|' . ($right['topic'] ?? '')));

            return $leftKey <=> $rightKey;
        });

        $blocks = [];
        $beginnerCutoff = (int) floor($targetQuestions * 0.3);
        $intermediateCutoff = (int) ceil($targetQuestions * 0.7);

        foreach ($selected as $index => $item) {
            $difficulty = $index < $beginnerCutoff
                ? 'Beginner'
                : ($index < $intermediateCutoff ? 'Intermediate' : 'Advanced');

            $blocks[] = [
                'question_index' => $index + 1,
                'difficulty_level' => $difficulty,
                'threshold_topic' => $item['topic'],
                'source_type' => 'syllabus',
                'course_id' => $item['course_id'] ?? null,
                'course_title' => $item['course_title'] ?? null,
                'module_name' => $item['module_name'] ?? null,
                'covered_topics' => [$item['topic']],
            ];
        }

        return $blocks;
    }

    private function sanitizeFeatures(array $features): array
    {
        $clean = [];

        foreach ($features as $feature) {
            if (! is_array($feature)) {
                continue;
            }

            $sourceType = $feature['source_type'] ?? null;
            $topic = trim((string) ($feature['topic'] ?? ''));
            $moduleId = (int) ($feature['module_id'] ?? 0);

            if ($sourceType !== 'syllabus' || $moduleId < 1 || mb_strlen($topic) < 3) {
                continue;
            }

            $item = [
                'source_type' => 'syllabus',
                'module_id' => $moduleId,
                'module_name' => isset($feature['module_name']) ? trim((string) $feature['module_name']) : null,
                'course_id' => isset($feature['course_id']) && $feature['course_id'] ? (int) $feature['course_id'] : null,
                'course_title' => isset($feature['course_title']) ? trim((string) $feature['course_title']) : null,
                'topic' => mb_substr($topic, 0, 500),
                'position' => (int) ($feature['position'] ?? 0),
            ];

            $clean[$this->featureKey($item)] = $item;
        }

        return array_values($clean);
    }

    private function spreadAcrossModules(array $items, int $limit): array
    {
        if ($limit < 1 || empty($items)) {
            return [];
        }

        $byModule = [];

        foreach ($items as $item) {
            $byModule[$item['module_id']][] = $item;
        }

        ksort($byModule);

        foreach ($byModule as &$moduleItems) {
            usort(
                $moduleItems,
                fn (array $a, array $b) => [$a['position'], $a['topic']] <=> [$b['position'], $b['topic']]
            );
        }
        unset($moduleItems);

        $selected = [];

        while (count($selected) < $limit) {
            $added = false;

            foreach ($byModule as &$moduleItems) {
                if (empty($moduleItems)) {
                    continue;
                }

                $selected[] = array_shift($moduleItems);
                $added = true;

                if (count($selected) >= $limit) {
                    break;
                }
            }
            unset($moduleItems);

            if (! $added) {
                break;
            }
        }

        return $selected;
    }

    private function featureKey(array $feature): string
    {
        return implode('|', [
            $feature['source_type'] ?? '',
            (string) ($feature['module_id'] ?? ''),
            mb_strtolower(trim((string) ($feature['topic'] ?? ''))),
        ]);
    }
}
