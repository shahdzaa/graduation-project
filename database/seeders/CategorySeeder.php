<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $domains = DB::table('domains')->pluck('id', 'name');

        // Mapping الدومينات بالاسم
        $csId   = $domains['Computer Science'] ?? null;                    // 1
        $dsId   = $domains['DataScience'] ?? null;                         // 2
        $itId   = $domains['Information Technology'] ?? null;              // 3
        $mathId = $domains['Math and Logic'] ?? null;                      // 4
        $engId  = $domains['Physical Science and Engineering'] ?? null;    // 5

        $now = now();

        $categories = [
            // ========== Computer Science (id=1) ==========
            ['name' => 'Programming Languages', 'slug' => 'programming-languages', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'code', 'order_index' => 1],
            ['name' => 'Web Development', 'slug' => 'web-development', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'globe', 'order_index' => 2],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'smartphone', 'order_index' => 3],
            ['name' => 'Algorithms & Data Structures', 'slug' => 'algorithms-data-structures', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'git-branch', 'order_index' => 4],
            ['name' => 'Databases', 'slug' => 'databases', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'database', 'order_index' => 5],
            ['name' => 'Software Engineering', 'slug' => 'software-engineering', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'settings', 'order_index' => 6],
            ['name' => 'Version Control (Git)', 'slug' => 'version-control-git', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'git-commit', 'order_index' => 7],
            ['name' => 'Operating Systems', 'slug' => 'operating-systems', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'cpu', 'order_index' => 8],
            ['name' => 'Computer Networks', 'slug' => 'computer-networks', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'wifi', 'order_index' => 9],
            ['name' => 'Game Development', 'slug' => 'game-development', 'parent_id' => null, 'domain_id' => $csId, 'icon' => 'controller', 'order_index' => 10],

            // ========== Data Science (id=2) ==========
            ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'brain', 'order_index' => 1],
            ['name' => 'Deep Learning', 'slug' => 'deep-learning', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'layers', 'order_index' => 2],
            ['name' => 'Data Analysis', 'slug' => 'data-analysis', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'bar-chart-2', 'order_index' => 3],
            ['name' => 'Big Data', 'slug' => 'big-data', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'hard-drive', 'order_index' => 4],
            ['name' => 'Data Visualization', 'slug' => 'data-visualization', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'pie-chart', 'order_index' => 5],
            ['name' => 'Natural Language Processing (NLP)', 'slug' => 'nlp', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'message-circle', 'order_index' => 6],
            ['name' => 'AI & Robotics', 'slug' => 'ai-robotics', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'bot', 'order_index' => 7],
            ['name' => 'Statistical Methods', 'slug' => 'statistical-methods', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'trending-up', 'order_index' => 8],
            ['name' => 'Business Intelligence', 'slug' => 'business-intelligence', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'briefcase', 'order_index' => 9],
            ['name' => 'Data Mining', 'slug' => 'data-mining', 'parent_id' => null, 'domain_id' => $dsId, 'icon' => 'search', 'order_index' => 10],

            // ========== Information Technology (id=3) ==========
            ['name' => 'Cloud Computing', 'slug' => 'cloud-computing', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'cloud', 'order_index' => 1],
            ['name' => 'DevOps', 'slug' => 'devops', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'terminal', 'order_index' => 2],
            ['name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'shield', 'order_index' => 3],
            ['name' => 'Network Security', 'slug' => 'network-security', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'lock', 'order_index' => 4],
            ['name' => 'IT Infrastructure', 'slug' => 'it-infrastructure', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'server', 'order_index' => 5],
            ['name' => 'System Administration', 'slug' => 'system-administration', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'settings', 'order_index' => 6],
            ['name' => 'Virtualization', 'slug' => 'virtualization', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'box', 'order_index' => 7],
            ['name' => 'IT Support', 'slug' => 'it-support', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'headphones', 'order_index' => 8],
            ['name' => 'Blockchain', 'slug' => 'blockchain', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'link', 'order_index' => 9],
            ['name' => 'IoT (Internet of Things)', 'slug' => 'iot', 'parent_id' => null, 'domain_id' => $itId, 'icon' => 'zap', 'order_index' => 10],

            // ========== Math and Logic (id=4) ==========
            ['name' => 'Mathematics', 'slug' => 'mathematics', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'sigma', 'order_index' => 1],
            ['name' => 'Calculus', 'slug' => 'calculus', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'activity', 'order_index' => 2],
            ['name' => 'Linear Algebra', 'slug' => 'linear-algebra', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'grid', 'order_index' => 3],
            ['name' => 'Statistics', 'slug' => 'statistics', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'pie-chart', 'order_index' => 4],
            ['name' => 'Probability', 'slug' => 'probability', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'dice', 'order_index' => 5],
            ['name' => 'Discrete Mathematics', 'slug' => 'discrete-mathematics', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'hash', 'order_index' => 6],
            ['name' => 'Numerical Analysis', 'slug' => 'numerical-analysis', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'calculator', 'order_index' => 7],
            ['name' => 'Logic', 'slug' => 'logic', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'check-circle', 'order_index' => 8],
            ['name' => 'Optimization', 'slug' => 'optimization', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'target', 'order_index' => 9],
            ['name' => 'Cryptography', 'slug' => 'cryptography', 'parent_id' => null, 'domain_id' => $mathId, 'icon' => 'key', 'order_index' => 10],

            // ========== Physical Science and Engineering (id=5) ==========
            ['name' => 'Physics', 'slug' => 'physics', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'atom', 'order_index' => 1],
            ['name' => 'Chemistry', 'slug' => 'chemistry', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'flask', 'order_index' => 2],
            ['name' => 'Mechanical Engineering', 'slug' => 'mechanical-engineering', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'cog', 'order_index' => 3],
            ['name' => 'Electrical Engineering', 'slug' => 'electrical-engineering', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'zap', 'order_index' => 4],
            ['name' => 'Civil Engineering', 'slug' => 'civil-engineering', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'home', 'order_index' => 5],
            ['name' => 'Aerospace Engineering', 'slug' => 'aerospace-engineering', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'plane', 'order_index' => 6],
            ['name' => 'Chemical Engineering', 'slug' => 'chemical-engineering', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'flask', 'order_index' => 7],
            ['name' => 'Biomedical Engineering', 'slug' => 'biomedical-engineering', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'heart', 'order_index' => 8],
            ['name' => 'Environmental Engineering', 'slug' => 'environmental-engineering', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'leaf', 'order_index' => 9],
            ['name' => 'Robotics', 'slug' => 'robotics', 'parent_id' => null, 'domain_id' => $engId, 'icon' => 'bot', 'order_index' => 10],

            // ========== General (No Domain) ==========
            ['name' => 'Design & UX', 'slug' => 'design-ux', 'parent_id' => null, 'domain_id' => null, 'icon' => 'palette', 'order_index' => 1],
            ['name' => 'Business & Management', 'slug' => 'business-management', 'parent_id' => null, 'domain_id' => null, 'icon' => 'briefcase', 'order_index' => 2],
            ['name' => 'Marketing', 'slug' => 'marketing', 'parent_id' => null, 'domain_id' => null, 'icon' => 'trending-up', 'order_index' => 3],
            ['name' => 'Finance & Accounting', 'slug' => 'finance-accounting', 'parent_id' => null, 'domain_id' => null, 'icon' => 'dollar-sign', 'order_index' => 4],
            ['name' => 'Soft Skills', 'slug' => 'soft-skills', 'parent_id' => null, 'domain_id' => null, 'icon' => 'users', 'order_index' => 5],
        ];

        // إضافة timestamps لكل فئة
        foreach ($categories as &$cat) {
            $cat['created_at'] = $now;
            $cat['updated_at'] = $now;
        }

        // إدراج الفئات في قاعدة البيانات
        DB::table('categories')->upsert($categories, ['slug'], ['name', 'domain_id', 'icon', 'order_index', 'parent_id', 'updated_at']);
    }
}