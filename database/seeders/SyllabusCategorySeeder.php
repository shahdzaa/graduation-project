<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyllabusCategorySeeder extends Seeder
{
    private array $categoryKeywords = [
        // ... (نفس المصفوفة السابقة)
    ];

    private array $categoryCache = []; // slug → id
    private int $defaultCategoryId;

    public function run(): void
    {
        $this->loadCategoryCache();

        $total = DB::table('syllabus')->count();
        $this->command->info("Found {$total} syllabus records. Processing...");

        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        $inserted = 0;
        $updated = 0;

        DB::table('syllabus')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$inserted, &$updated, $bar) {
                $pivotRows = [];

                foreach ($rows as $syllabus) {
                    $slug = $this->classifyTitle($syllabus->name);
                    $categoryId = $this->categoryCache[$slug] ?? $this->defaultCategoryId;

                    $pivotRows[] = [
                        'syllabus_id' => $syllabus->id,
                        'category_id' => $categoryId,
                    ];
                }

                // استخدام upsert لتحديث السجلات الموجودة أو إدراج جديدة
                DB::table('syllabus_category')->upsert(
                    $pivotRows,
                    ['syllabus_id'], // الأعمدة الفريدة لتحديد السجل
                    ['category_id']  // الأعمدة التي سيتم تحديثها
                );

                $inserted += count($pivotRows);
                $bar->advance(count($rows));
            });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✓ Done! Processed: {$inserted} records.");
    }

    private function loadCategoryCache(): void
    {
        $categories = DB::table('categories')->get(['id', 'slug']);
        foreach ($categories as $cat) {
            $this->categoryCache[$cat->slug] = $cat->id;
        }

        if (!isset($this->categoryCache['general'])) {
            throw new \RuntimeException('Category "general" not found. Run CategorySeeder first.');
        }

        $this->defaultCategoryId = $this->categoryCache['general'];
    }

    private function classifyTitle(string $title): string
    {
        $lower = strtolower($title);
        $best = 'general';
        $bestScore = 0;

        foreach ($this->categoryKeywords as $slug => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $slug;
            }
        }

        return $best;
    }
}