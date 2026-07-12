<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CourseraSeeder extends Seeder
{
    protected bool $includeSyllabus = false;

    public function run(): void
    {
        $config = config('database.connections.mysql');

        $mysqli = new \mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port'] ?? 3306
        );

        if ($mysqli->connect_errno) {
            $this->command->error('Connection failed: ' . $mysqli->connect_error);
            return;
        }

        $mysqli->set_charset('utf8mb4');

        // تنظيف الجداول
        $tables = [
            'course_reviews', 'course_skills', 'skills',
            'course_modules', 'modules', 'learning_outcomes',
            'course_instructors', 'course_organizations', 'courses',
            'instructor_profiles', 'users', 'organizations',
            'domains', 'syllabus_types', 'course_types', 'course_levels',
        ];

        if ($this->includeSyllabus) {
            array_unshift($tables, 'syllabus');
        }

        $mysqli->query('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            $mysqli->query("TRUNCATE TABLE `$table`");
        }
        $this->command->info('🧹 تم تنظيف الجداول');

        // الملفات الأساسية
        $files = [
            '01_course_levels.sql',
            '02_course_types.sql',
            '03_syllabus_types.sql',
            '04_domains.sql',
            '05_organizations.sql',
            '06_users_instructors.sql',
            '06b_instructor_profiles.sql',
            // '07_courses.sql',
            '08_course_organizations.sql',
            '09_course_instructors.sql',
            '10_learning_outcomes.sql',
            '11_modules.sql',
            '12_course_modules.sql',
            '13_skills.sql',
            '13b_course_skills.sql',
            '15_course_reviews.sql',
        ];

        foreach ($files as $file) {
            $path = database_path('seeders/sql/' . $file);

            if (!file_exists($path)) {
                $this->command->warn("⚠️  ملف مش موجود: $file");
                continue;
            }

            $sql = file_get_contents($path);
            $ok  = $this->runSql($mysqli, $sql, $file);

            if (!$ok) {
                $this->command->error("⛔ توقف بسبب خطأ في $file");
                break;
            }
        }

        // syllabus (اختياري)
        if ($this->includeSyllabus) {
            $this->command->info('⏳ جاري إدخال الـ syllabus...');
            $path = database_path('seeders/sql/14_syllabus.sql');
            if (file_exists($path)) {
                $this->runSql($mysqli, file_get_contents($path), '14_syllabus.sql');
            }
        } else {
            $this->command->warn('ℹ️  الـ syllabus تم تخطيه — شغّليه يدوياً من phpMyAdmin إذا أردت');
        }

        $mysqli->query('SET FOREIGN_KEY_CHECKS=1');
        $mysqli->close();

        $this->command->newLine();
        $this->command->info('✅ تم بنجاح!');
    }

    protected function runSql(\mysqli $mysqli, string $sql, string $label): bool
    {
        // أزل التعليقات
        $sql = preg_replace('/^--.*$/m', '', $sql);
        $sql = trim($sql);

        if (empty($sql)) {
            $this->command->warn("⚠️  $label فاضي");
            return true;
        }

        // قسّم على INSERT statements
        $parts = preg_split('/^\s*(?=INSERT\s+INTO\b)/mi', $sql, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            // لو الجزء كبير قسّمه على rows
            if (strlen($part) > 256 * 1024) {
                if (!$this->runBigInsert($mysqli, $part, $label)) {
                    return false;
                }
            } else {
                if (!$mysqli->query($part)) {
                    $this->command->error("❌ خطأ في $label: " . $mysqli->error);
                    return false;
                }
            }
        }

        $this->command->info("✅ $label");
        return true;
    }

    protected function runBigInsert(\mysqli $mysqli, string $insertSql, string $label): bool
    {
        // استخرج الـ header
        if (!preg_match('/^(INSERT\s+INTO\s+`[^`]+`\s*\([^)]+\)\s*VALUES\s*)/is', $insertSql, $m)) {
            return (bool) $mysqli->query($insertSql);
        }

        $header     = rtrim($m[1]);
        $valuesPart = substr($insertSql, strlen($m[0]));
        $valuesPart = rtrim($valuesPart, "; \n\r");

        // قسّم الـ rows بدقة مع مراعاة الـ strings
        $rows  = [];
        $depth = 0;
        $start = 0;
        $len   = strlen($valuesPart);
        $i     = 0;

        while ($i < $len) {
            $ch = $valuesPart[$i];

            if ($ch === "'") {
                $i++;
                while ($i < $len) {
                    if ($valuesPart[$i] === '\\') { $i += 2; continue; }
                    if ($valuesPart[$i] === "'")  { break; }
                    $i++;
                }
            } elseif ($ch === '(') {
                if ($depth === 0) $start = $i;
                $depth++;
            } elseif ($ch === ')') {
                $depth--;
                if ($depth === 0) {
                    $rows[] = substr($valuesPart, $start, $i - $start + 1);
                }
            }

            $i++;
        }

        if (empty($rows)) {
            return (bool) $mysqli->query($insertSql);
        }

        // أدخل 100 row في كل مرة
        $chunkSize = 100;
        for ($j = 0; $j < count($rows); $j += $chunkSize) {
            $chunk    = array_slice($rows, $j, $chunkSize);
            $chunkSql = $header . "\n" . implode(",\n", $chunk) . ';';

            if (!$mysqli->query($chunkSql)) {
                $this->command->error("❌ خطأ في chunk [{$j}] من $label: " . $mysqli->error);
                return false;
            }
        }

        return true;
    }
}
