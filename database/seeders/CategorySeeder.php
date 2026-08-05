<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'Technology' => [
                'icon' => 'monitor',
                'children' => ['Web Development', 'Data Science & AI', 'IT & Software', 'Cybersecurity'],
            ],
            'Business' => [
                'icon' => 'briefcase',
                'children' => ['Management & Leadership', 'Finance & Accounting', 'Marketing'],
            ],
            'Design & Art' => [
                'icon' => 'palette',
                'children' => ['UI/UX Design', 'Graphic Design'],
            ],
            'Personal Development' => [
                'icon' => 'user',
                'children' => ['Productivity', 'Communication Skills'],
            ],
            'Photography & Video' => [
                'icon' => 'camera',
                'children' => ['Photography', 'Video Editing'],
            ],
            'Health & Wellness' => [
                'icon' => 'heart',
                'children' => ['Fitness', 'Nutrition'],
            ],
        ];

        $parentOrder = 0;

        foreach ($structure as $parentName => $data) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'icon' => $data['icon'],
                    'order_index' => $parentOrder,
                ]
            );

            $childOrder = 0;
            foreach ($data['children'] as $childName) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'order_index' => $childOrder,
                    ]
                );
                $childOrder++;
            }

            $parentOrder++;
        }
    }
}