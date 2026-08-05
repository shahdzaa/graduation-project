<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyllabusCategoriesSeeder extends Seeder
{
    /**
     * These are content-topic categories built specifically for the syllabus
     * table (not the broader course/domain categories). Each syllabus row
     * gets tagged with one of these so the placement test can group
     * questions by concept (e.g. all "Loops" items together), regardless
     * of which course or module they came from.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Strings & Text Processing', 'slug' => 'strings-text-processing', 'order_index' => 1],
            ['name' => 'Input/Output & File Handling', 'slug' => 'input-output-file-handling', 'order_index' => 2],
            ['name' => 'Booleans & Basic Data Types', 'slug' => 'booleans-basic-data-types', 'order_index' => 3],
            ['name' => 'Programming Environment & Tools', 'slug' => 'programming-environment-tools', 'order_index' => 4],
            ['name' => 'Complex Numbers & Numeric Types', 'slug' => 'complex-numbers-numeric-types', 'order_index' => 5],
            ['name' => 'Course Orientation & Meta', 'slug' => 'course-orientation-meta', 'order_index' => 6],
            ['name' => 'Recursion & Divide-and-Conquer', 'slug' => 'recursion-divide-and-conquer', 'order_index' => 7],
            ['name' => 'Sorting & Searching Algorithms', 'slug' => 'sorting-searching-algorithms', 'order_index' => 8],
            ['name' => 'Graphs & Trees', 'slug' => 'graphs-trees', 'order_index' => 9],
            ['name' => 'Data Structures & Algorithm Analysis', 'slug' => 'data-structures-algorithm-analysis', 'order_index' => 10],
            ['name' => 'Control Flow (Conditionals & Loops)', 'slug' => 'control-flow-conditionals-loops', 'order_index' => 11],
            ['name' => 'Functions & Modular Programming', 'slug' => 'functions-modular-programming', 'order_index' => 12],
            ['name' => 'Variables, Data Types & Expressions', 'slug' => 'variables-data-types-expressions', 'order_index' => 13],
            ['name' => 'Arrays, Lists & Collections', 'slug' => 'arrays-lists-collections', 'order_index' => 14],
            ['name' => 'Object-Oriented Programming', 'slug' => 'object-oriented-programming', 'order_index' => 15],
            ['name' => 'Exception Handling & Debugging', 'slug' => 'exception-handling-debugging', 'order_index' => 16],
            ['name' => 'Software Testing & QA', 'slug' => 'software-testing-qa', 'order_index' => 17],
            ['name' => 'Software Engineering & Architecture', 'slug' => 'software-engineering-architecture', 'order_index' => 18],
            ['name' => 'Version Control & DevOps', 'slug' => 'version-control-devops', 'order_index' => 19],
            ['name' => 'Python Programming', 'slug' => 'python-programming', 'order_index' => 20],
            ['name' => 'Java Programming', 'slug' => 'java-programming', 'order_index' => 21],
            ['name' => 'JavaScript & Frontend Web', 'slug' => 'javascript-frontend-web', 'order_index' => 22],
            ['name' => 'Web Development & APIs', 'slug' => 'web-development-apis', 'order_index' => 23],
            ['name' => 'Databases & SQL', 'slug' => 'databases-sql', 'order_index' => 24],
            ['name' => 'Networking', 'slug' => 'networking', 'order_index' => 25],
            ['name' => 'Cloud Computing', 'slug' => 'cloud-computing', 'order_index' => 26],
            ['name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'order_index' => 27],
            ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'order_index' => 28],
            ['name' => 'Deep Learning & Neural Networks', 'slug' => 'deep-learning-neural-networks', 'order_index' => 29],
            ['name' => 'Statistics & Probability', 'slug' => 'statistics-probability', 'order_index' => 30],
            ['name' => 'Mathematics & Linear Algebra', 'slug' => 'mathematics-linear-algebra', 'order_index' => 31],
            ['name' => 'Data Analysis & Visualization', 'slug' => 'data-analysis-visualization', 'order_index' => 32],
            ['name' => 'Graphics, Audio & Multimedia', 'slug' => 'graphics-audio-multimedia', 'order_index' => 33],
            ['name' => 'Blockchain', 'slug' => 'blockchain', 'order_index' => 34],
            ['name' => 'Business, Management & Marketing', 'slug' => 'business-management-marketing', 'order_index' => 35],
            ['name' => 'Career & Professional Skills', 'slug' => 'career-professional-skills', 'order_index' => 36],
            ['name' => 'Health, Medicine & Life Sciences', 'slug' => 'health-medicine-life-sciences', 'order_index' => 37],
            ['name' => 'Programming Fundamentals (General)', 'slug' => 'programming-fundamentals-general', 'order_index' => 38],
            ['name' => 'General / Uncategorized', 'slug' => 'general-uncategorized', 'order_index' => 39],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name'         => $category['name'],
                    'parent_id'    => null,
                    'domain_id'    => null,
                    'icon'         => null,
                    'order_index'  => $category['order_index'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );
        }
    }
}
