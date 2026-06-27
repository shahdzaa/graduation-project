<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursesSeeder extends Seeder
{
    public function run()
    {
        $csvPath = database_path('seeders/data/courses.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("❌ الملف ما موجود: {$csvPath}");
            return;
        }

        // حمل البيانات المرجعية
        $domains = DB::table('domains')->pluck('id', 'name')->toArray();
        $levels  = DB::table('course_levels')->pluck('id', 'name')->toArray();
        $types   = DB::table('course_types')->pluck('id', 'name')->toArray();

        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("📊 البيانات المرجعية المحملة:");
        $this->command->info("   • النطاقات: " . count($domains));
        $this->command->info("   • المستويات: " . count($levels));
        $this->command->info("   • الأنواع: " . count($types));
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        if (empty($domains) || empty($levels) || empty($types)) {
            $this->command->error("❌ البيانات المرجعية ناقصة!");
            return;
        }

        $handle = fopen($csvPath, 'r');
        
        // قراءة وتخطي الرأس
        $header = fgetcsv($handle);
        $this->command->info("📋 أعمدة CSV: " . implode(', ', $header) . "\n");

        $inserted = 0;
        $skipped = 0;
        $lineNumber = 2;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            // تحقق من عدد الأعمدة
            if (count($row) < 8 || empty(trim($row[0]))) {
                $skipped++;
                $lineNumber++;
                continue;
            }

            // استخرج البيانات
            $levelName = trim($row[2]);
            // نظّف اسم المستوى: أزل "level" إذا كانت موجودة
            $levelName = str_ireplace(' level', '', $levelName);
            
            $courseData = [
                'title'       => trim($row[0]),
                'domain'      => trim($row[1]),
                'level'       => $levelName,
                'type'        => trim($row[3]),
                'schedule'    => trim($row[4]),
                'url'         => trim($row[5]),
                'duration'    => (int)trim($row[6]),
                'rating'      => (float)trim($row[7]),
            ];

            // تحقق من المفاتيح المرجعية
            if (!isset($domains[$courseData['domain']])) {
                $skipped++;
                $errors[] = "السطر {$lineNumber}: النطاق '{$courseData['domain']}' غير موجود";
                $lineNumber++;
                continue;
            }

            if (!isset($levels[$courseData['level']])) {
                $skipped++;
                $errors[] = "السطر {$lineNumber}: المستوى '{$courseData['level']}' غير موجود";
                $lineNumber++;
                continue;
            }

            if (!isset($types[$courseData['type']])) {
                $skipped++;
                $errors[] = "السطر {$lineNumber}: النوع '{$courseData['type']}' غير موجود";
                $lineNumber++;
                continue;
            }

            // حاول الإدراج
            try {
                $course = DB::table('courses')->where('url', $courseData['url'])->first();
                
                if ($course) {
                    // تحديث إذا كان موجود
                    DB::table('courses')
                        ->where('id', $course->id)
                        ->update([
                            'title'            => $courseData['title'],
                            'domain_id'        => $domains[$courseData['domain']],
                            'level_id'         => $levels[$courseData['level']],
                            'type_id'          => $types[$courseData['type']],
                            'schedule'         => $courseData['schedule'],
                            'duration_minutes' => $courseData['duration'],
                            'average_rating'   => $courseData['rating'],
                            'is_published'     => true,
                            'updated_at'       => now(),
                        ]);
                } else {
                    // إدراج جديد
                    DB::table('courses')->insert([
                        'title'            => $courseData['title'],
                        'url'              => $courseData['url'],
                        'domain_id'        => $domains[$courseData['domain']],
                        'level_id'         => $levels[$courseData['level']],
                        'type_id'          => $types[$courseData['type']],
                        'schedule'         => $courseData['schedule'],
                        'duration_minutes' => $courseData['duration'],
                        'average_rating'   => $courseData['rating'],
                        'price'            => 0.00,
                        'is_free'          => true,
                        'is_published'     => true,
                        'language'         => 'en',
                        'thumbnail'        => null,
                        'description'      => null,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }

                $inserted++;

                // طباعة التقدم كل 50 صف
                if ($inserted % 50 === 0) {
                    $this->command->line("⏳ تم معالجة {$inserted} كورس...");
                }
            } catch (\Exception $e) {
                $skipped++;
                $errors[] = "السطر {$lineNumber}: " . $e->getMessage();
            }

            $lineNumber++;
        }

        fclose($handle);

        // طباعة النتائج النهائية
        $this->command->info("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("✅ النتائج النهائية:");
        $this->command->info("   • تم إدراج/تحديث: {$inserted}");
        $this->command->info("   • تم تخطيه: {$skipped}");
        
        if (!empty($errors)) {
            $this->command->warn("\n⚠️  الأخطاء:");
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->command->warn("   " . $error);
            }
            if (count($errors) > 10) {
                $this->command->warn("   ... و" . (count($errors) - 10) . " أخطاء أخرى");
            }
        }
        
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
    }
}