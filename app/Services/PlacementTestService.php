<?php

namespace App\Services;

class PlacementTestService
{
    /**
     * يبني أسئلة الاختبار من عناصر syllabus المرتبطة بالـ category
     * المختارة. نوزّع الاختيار Round-robin بين الـ modules حتى ما يحتكر
     * module غني بعناصر syllabus الاختبار على حساب باقي المودولز داخل
     * نفس الـ category. الموديل (Gemini) هو من يحدد شو الطالب بيعرف
     * وشو لأ من خلال إجاباته على الأسئلة.
     */
    public function generateThresholdBlocks(array $features, int $targetQuestions = 25): array
    {
        $features = $this->sanitizeFeatures($features);

        if (empty($features) || $targetQuestions < 1) {
            return [];
        }

        $selected = $this->spreadAcrossModules($features, $targetQuestions);

        if (count($selected) < $targetQuestions) {
            // إذا ما كفى الـ round-robin (modules قليلة عناصر syllabus)،
            // نكمّل من الباقي بدون تكرار.
            $selectedKeys = [];
            foreach ($selected as $item) {
                $selectedKeys[$this->featureKey($item)] = true;
            }

            $remaining = array_values(array_filter(
                $features,
                fn (array $item) => !isset($selectedKeys[$this->featureKey($item)])
            ));

            foreach ($remaining as $item) {
                if (count($selected) >= $targetQuestions) {
                    break;
                }
                $selected[] = $item;
            }
        }

        $selected = array_slice($selected, 0, $targetQuestions);

        // نخلط ترتيب عناصر الـ syllabus بشكل حتمي حتى ما تتجمع كل عناصر
        // module وحد وراء بعض.
        usort($selected, function (array $left, array $right) {
            $leftKey = sprintf('%010u', crc32($left['module_id'] . '|' . $left['topic']));
            $rightKey = sprintf('%010u', crc32($right['module_id'] . '|' . $right['topic']));

            return $leftKey <=> $rightKey;
        });

        $blocks = [];

        // نسب الصعوبة: ~30% Beginner، ~40% Intermediate، ~30% Advanced.
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
                'course_id' => $item['course_id'],
                'course_title' => $item['course_title'],
                'module_name' => $item['module_name'],
                'covered_topics' => [$item['topic']],
            ];
        }

        return $blocks;
    }

    private function sanitizeFeatures(array $features): array
    {
        $clean = [];

        foreach ($features as $feature) {
            if (!is_array($feature)) {
                continue;
            }

            $sourceType = $feature['source_type'] ?? null;
            $topic = trim((string) ($feature['topic'] ?? ''));
            $moduleId = (int) ($feature['module_id'] ?? 0);

            if (
                $sourceType !== 'syllabus'
                || $moduleId < 1
                || mb_strlen($topic) < 3
            ) {
                continue;
            }

            $topic = mb_substr($topic, 0, 500);

            $item = [
                'source_type' => 'syllabus',
                'module_id' => $moduleId,
                'module_name' => isset($feature['module_name'])
                    ? trim((string) $feature['module_name'])
                    : null,
                // course_id/course_title اختياريان: عنصر syllabus بيبقى
                // صالح حتى لو الـ module تبعو مش مرتبط بأي كورس بعد.
                'course_id' => isset($feature['course_id']) && $feature['course_id']
                    ? (int) $feature['course_id']
                    : null,
                'course_title' => isset($feature['course_title'])
                    ? trim((string) $feature['course_title'])
                    : null,
                'topic' => $topic,
                'position' => (int) ($feature['position'] ?? 0),
            ];

            $clean[$this->featureKey($item)] = $item;
        }

        return array_values($clean);
    }

    /**
     * Round-robin بين الـ modules حتى ما يحتكر module غني بعناصر syllabus
     * الاختبار على حساب باقي modules نفس الـ category.
     */
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
                fn (array $a, array $b) =>
                    [$a['position'], $a['topic']] <=> [$b['position'], $b['topic']]
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

            if (!$added) {
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
