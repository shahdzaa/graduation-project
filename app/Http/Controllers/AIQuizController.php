<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Course;
use App\Models\Question; 

class AIQuizController extends Controller
{
    public function generateCourseQuiz($courseId)
    {
        // 1. جلب الكورس من قاعدة البيانات
        $course = Course::findOrFail($courseId); 

        // 2. تجهيز البيانات بالشكل الجديد اللي بيفهمه سيرفر البايثون
        $payload = [
            'course_title' => $course->course_title, // تأكد أن اسم العمود مطابق لقاعدة بياناتك
            'modules'      => json_decode($course->modules), // جلب عناوين الوحدات
            'syllabus'     => json_decode($course->syllabus) // جلب تفاصيل الدروس
        ];

        try {
            // 3. إرسال طلب لـ Microservice البايثون الشغال على منفذ 8000
            $response = Http::timeout(60) // إعطاء مهلة دقيقة للتوليد
                            ->post('http://127.0.0.1:8000/api/generate-quiz', $payload);

            if ($response->successful()) {
                $quizData = $response->json();
                
                // هنا الأسئلة رجعت لك في المصفوفة $quizData['questions']
                foreach ($quizData['questions'] as $q) {
                    // بمجرد تجهيز جدول الأسئلة، قم بإلغاء التعليق عن هذا الكود لحفظها:
                    // Question::create([
                    //     'course_id'      => $courseId,
                    //     'question_text'  => $q['question'],
                    //     'options'        => json_encode($q['options']),
                    //     'correct_answer' => $q['correct_answer'],
                    //     'module_title'   => $q['module_title'] // إضافة عنوان الوحدة
                    // ]);
                }

                return response()->json([
                    'message' => 'تم توليد الأسئلة وحفظها بنجاح!',
                    'data' => $quizData
                ], 200);
            }

            return response()->json(['error' => 'فشل السيرفر في توليد الأسئلة'], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => 'تعذر الاتصال بـ AI Service: ' . $e->getMessage()], 500);
        }
    }
}