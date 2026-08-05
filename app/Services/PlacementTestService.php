<?php

namespace App\Services;

class PlacementTestService
{
    /**
     * يختار modules موزعة على الكورسات (round-robin) بدل تقسيم قائمة
     * مرتبة أبجدياً إلى كتل لا تعبّر عن الصعوبة أو التغطية الحقيقية.
     * المصدر الوحيد الآن هو modules؛ الموديل هو من يحدد شو الطالب
     * بيعرف وشو لأ من خلال إجاباته على الأسئلة.
     */
    public function generateThresholdBlocks(array $features, int $targetQuestions = 25): array
    {
        $features = $this->sanitizeFeatures($features);

        if (empty($features) || $targetQuestions < 1) {
            return [];
        }

        $selected = $this->spreadAcrossCourses($features, $targetQuestions);

        if (count($selected) < $targetQuestions) {
            // إذا ما كفت الـ round-robin (كورسات قليلة الـ modules)، نكمّل
            // من الباقي بدون تكرار.
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

        // نخلط ترتيب المودولز بشكل حتمي حتى ما تتجمع كل مودولز كورس وحد وراء بعض.
        usort($selected, function (array $left, array $right) {
            $leftKey = sprintf('%010u', crc32($left['course_id'] . '|' . $left['topic']));
            $rightKey = sprintf('%010u', crc32($right['course_id'] . '|' . $right['topic']));

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
                'source_type' => 'module',
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
            $courseTitle = trim((string) ($feature['course_title'] ?? ''));
            $courseId = (int) ($feature['course_id'] ?? 0);

            if (
                $sourceType !== 'module'
                || $courseId < 1
                || $courseTitle === ''
                || mb_strlen($topic) < 3
            ) {
                continue;
            }

            $topic = mb_substr($topic, 0, 500);

            $item = [
                'source_type' => 'module',
                'course_id' => $courseId,
                'course_title' => $courseTitle,
                'module_name' => isset($feature['module_name'])
                    ? trim((string) $feature['module_name'])
                    : null,
                'topic' => $topic,
                'position' => (int) ($feature['position'] ?? 0),
            ];

            $clean[$this->featureKey($item)] = $item;
        }

        return array_values($clean);
    }

    /**
     * Round-robin بين الكورسات حتى لا يحتكر كورس غني بالمودولز الاختبار.
     */
    private function spreadAcrossCourses(array $items, int $limit): array
    {
        if ($limit < 1 || empty($items)) {
            return [];
        }

        $byCourse = [];

        foreach ($items as $item) {
            $byCourse[$item['course_id']][] = $item;
        }

        ksort($byCourse);

        foreach ($byCourse as &$courseItems) {
            usort(
                $courseItems,
                fn (array $a, array $b) =>
                    [$a['position'], $a['topic']] <=> [$b['position'], $b['topic']]
            );
        }
        unset($courseItems);

        $selected = [];

        while (count($selected) < $limit) {
            $added = false;

            foreach ($byCourse as &$courseItems) {
                if (empty($courseItems)) {
                    continue;
                }

                $selected[] = array_shift($courseItems);
                $added = true;

                if (count($selected) >= $limit) {
                    break;
                }
            }
            unset($courseItems);

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
            (string) ($feature['course_id'] ?? ''),
            mb_strtolower(trim((string) ($feature['topic'] ?? ''))),
        ]);
    }
}
