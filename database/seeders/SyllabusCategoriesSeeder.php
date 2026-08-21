<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;

class SyllabusCategoriesSeeder extends Seeder
{
    /**
     * @throws JsonException
     */
    public function run(): void
    {
        $taxonomyPath = storage_path(
            'app/imports/category_taxonomy.json'
        );

        $assignmentsPath = storage_path(
            'app/imports/module_category_assignments.json'
        );

        if (! file_exists($taxonomyPath)) {
            throw new RuntimeException(
                "Taxonomy file not found: {$taxonomyPath}"
            );
        }

        if (! file_exists($assignmentsPath)) {
            throw new RuntimeException(
                "Assignments file not found: {$assignmentsPath}"
            );
        }

        $taxonomy = json_decode(
            file_get_contents($taxonomyPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $assignments = json_decode(
            file_get_contents($assignmentsPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $domainIds = DB::table('domains')
            ->pluck('id', 'name')
            ->all();

        $categoryIds = [];
        $createdCategories = 0;
        $updatedCategories = 0;

        DB::transaction(function () use (
            $taxonomy,
            $domainIds,
            &$categoryIds,
            &$createdCategories,
            &$updatedCategories
        ) {
            foreach ($taxonomy as $domainData) {
                $domainName = $this->normalizeText(
                    $domainData['domain'] ?? ''
                );

                $domainId = $domainIds[$domainName] ?? null;

                if (! $domainId) {
                    throw new RuntimeException(
                        "Domain not found: {$domainName}"
                    );
                }

                foreach (
                    ($domainData['categories'] ?? [])
                    as $categoryData
                ) {
                    $slug = $this->normalizeText(
                        $categoryData['slug'] ?? ''
                    );

                    $name = $this->normalizeText(
                        $categoryData['name'] ?? ''
                    );

                    $orderIndex = (int) (
                        $categoryData['order_index'] ?? 0
                    );

                    if ($slug === '' || $name === '') {
                        continue;
                    }

                    $existingCategory = DB::table(
                        'categories'
                    )
                        ->where('slug', $slug)
                        ->first();

                    if ($existingCategory) {
                        DB::table('categories')
                            ->where(
                                'id',
                                $existingCategory->id
                            )
                            ->update([
                                'name' => $name,
                                'parent_id' => null,
                                'domain_id' => $domainId,
                                'icon' => '',
                                'order_index' => $orderIndex,
                                'updated_at' => now(),
                            ]);

                        $categoryId =
                            $existingCategory->id;

                        $updatedCategories++;
                    } else {
                        $categoryId = DB::table(
                            'categories'
                        )
                            ->insertGetId([
                                'name' => $name,
                                'slug' => $slug,
                                'parent_id' => null,
                                'domain_id' => $domainId,
                                'icon' => '',
                                'order_index' => $orderIndex,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                        $createdCategories++;
                    }

                    $categoryIds[$slug] = $categoryId;
                }
            }
        });

        $moduleIds = [];

        $courseModuleRows = DB::table('course_modules')
            ->join(
                'courses',
                'course_modules.course_id',
                '=',
                'courses.id'
            )
            ->get([
                'courses.title as course_title',
                'course_modules.module_id',
                'course_modules.order_index',
            ]);

        foreach ($courseModuleRows as $row) {
            $moduleIds[
                $this->moduleKey(
                    $row->course_title,
                    (int) $row->order_index
                )
            ] = $row->module_id;
        }

        $moduleIdsByCategory = [];
        $classifiedModules = 0;

        foreach ($assignments as $assignment) {
            $courseTitle = $this->normalizeText(
                $assignment['course_title'] ?? ''
            );

            $moduleOrder = (int) (
                $assignment['module_order'] ?? 0
            );

            $categorySlug = $this->normalizeText(
                $assignment['category_slug'] ?? ''
            );

            $moduleId = $moduleIds[
                $this->moduleKey(
                    $courseTitle,
                    $moduleOrder
                )
            ] ?? null;

            if (! $moduleId) {
                throw new RuntimeException(
                    "Module not found for course: ".
                    "{$courseTitle}, order: {$moduleOrder}"
                );
            }

            $categoryId =
                $categoryIds[$categorySlug] ?? null;

            if (! $categoryId) {
                throw new RuntimeException(
                    "Category not found: {$categorySlug}"
                );
            }

            $moduleIdsByCategory[$categoryId][] =
                $moduleId;

            $classifiedModules++;
        }

        $updatedTopics = 0;

        DB::transaction(function () use (
            $moduleIdsByCategory,
            &$updatedTopics
        ) {
            foreach (
                $moduleIdsByCategory
                as $categoryId => $ids
            ) {
                $ids = array_values(array_unique($ids));

                foreach (
                    array_chunk($ids, 500)
                    as $moduleChunk
                ) {
                    $updatedTopics += DB::table('syllabus')
                        ->whereIn(
                            'module_id',
                            $moduleChunk
                        )
                        ->update([
                            'category_id' => $categoryId,
                            'updated_at' => now(),
                        ]);
                }
            }
        });

        $this->command?->info(
            'Semantic syllabus categories imported successfully.'
        );

        $this->command?->info(
            "Created categories: {$createdCategories}"
        );

        $this->command?->info(
            "Updated categories: {$updatedCategories}"
        );

        $this->command?->info(
            "Classified modules: {$classifiedModules}"
        );

        $this->command?->info(
            "Updated syllabus topics: {$updatedTopics}"
        );
    }

    private function moduleKey(
        string $courseTitle,
        int $moduleOrder
    ): string {
        return $this->normalizeKey($courseTitle)
            .'|'.$moduleOrder;
    }

    private function normalizeText(mixed $value): string
    {
        return preg_replace(
            '/\s+/u',
            ' ',
            trim((string) $value)
        );
    }

    private function normalizeKey(mixed $value): string
    {
        return mb_strtolower(
            $this->normalizeText($value),
            'UTF-8'
        );
    }
}