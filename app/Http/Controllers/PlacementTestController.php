<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserTestAttempt;
use App\Services\PlacementTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PlacementTestController extends Controller
{
    public function __construct(
        protected PlacementTestService $placementService
    ) {
    }

    /**
     * POST /api/placement/generate
     *
     * Frontend يرسل payload كامل من category + placement_topics
     * ثم يطلب من AI service توليد 25 سؤال، وبعدها يخزنها في DB
     * ويرجع attempt_id + الأسئلة بدون correct_answer.
     */
    /**
     * POST /api/placement/generate
     *
     * جديد: يقبل فقط category_id
     * ثم يجلب الـ syllabus items بنفسه ويبني blocks
     * ثم يرسلها للـ AI service
     */
    public function generate(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $category = Category::findOrFail($request->category_id);

        // جلب الـ syllabus items من DB
        $syllabusItems = DB::table('syllabus')
            ->join('modules', 'syllabus.module_id', '=', 'modules.id')
            ->leftJoin('course_modules', 'course_modules.module_id', '=', 'modules.id')
            ->leftJoin('courses', 'courses.id', '=', 'course_modules.course_id')
            ->where('syllabus.category_id', $request->category_id)
            ->select([
                'syllabus.id as syllabus_id',
                'syllabus.name as topic',
                'modules.id as module_id',
                'modules.name as module_name',
                'courses.id as course_id',
                'courses.title as course_title',
                'course_modules.order_index as position',
            ])
            ->orderBy('modules.id')
            ->orderBy('courses.id')
            ->orderBy('course_modules.order_index')
            ->get()
            ->unique('syllabus_id')
            ->map(fn ($row) => [
                'source_type' => 'syllabus',
                'module_id' => (int) $row->module_id,
                'module_name' => $row->module_name,
                'course_id' => $row->course_id ? (int) $row->course_id : null,
                'course_title' => $row->course_title,
                'topic' => $row->topic,
                'position' => (int) ($row->position ?? 0),
            ])
            ->values()
            ->all();

        if (empty($syllabusItems)) {
            return response()->json([
                'error' => 'لا توجد عناصر syllabus لهذه الفئة.',
            ], 400);
        }

        // توليد الـ blocks (topics للـ AI service)
        $blocks = $this->placementService->generateThresholdBlocks($syllabusItems, 25);

        if (count($blocks) < 25) {
            return response()->json([
                'error' => 'لا توجد عناصر syllabus كافية لتوليد اختبار من 25 سؤالاً.',
                'available_topics' => count($blocks),
            ], 422);
        }

        // إرسال الـ blocks للـ AI service
        $serviceUrl = config('services.placement_test.url');

        try {
            $pythonResponse = Http::timeout(120)->post(
                $serviceUrl . '/api/generate-quiz',
                [
                    'category' => $category->name,
                    'placement_topics' => $blocks,
                ]
            );

            if ($pythonResponse->failed()) {
                return response()->json([
                    'message' => 'فشل توليد الأسئلة من الـ AI service.',
                    'detail' => $pythonResponse->json('detail') ?? $pythonResponse->body(),
                ], 502);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'خطأ في الاتصال بـ AI service.',
                'service_url' => $serviceUrl,
                'error' => 'تأكد من أن الخدمة الخارجية تعمل.',
            ], 503);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json([
                'message' => 'خطأ في طلب AI service.',
                'error' => $e->getMessage(),
            ], 502);
        }

        $questions = $pythonResponse->json('questions');

        $result = $this->placementService->storeGeneratedTest(
            userId: Auth::id(),
            category: $category->name,
            questions: $questions
        );

        return response()->json([
            'attempt_id' => $result['attempt_id'],
            'category' => $category->name,
            'questions' => $result['questions_for_student'],
        ], 201);
    }

    /**
     * POST /api/placement/{attempt}/submit
     */
    public function submit(Request $request, int $attemptId)
    {
        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.selected_option_id' => 'required|integer|exists:answer_options,id',
        ]);

        $attempt = UserTestAttempt::where('id', $attemptId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $result = $this->placementService->submitAnswers(
            attemptId: $attempt->id,
            answers: $request->answers
        );

        return response()->json([
            'message' => 'تم تسليم الاختبار بنجاح.',
            'score' => $result['score'],
            'total' => $result['total'],
            'known_syllabi' => $result['known_syllabi'],
        ]);
    }

    /**
     * المصدر الآن هو syllabus المرتبط بالـ category المختارة (عبر
     * syllabus.category_id)، مش modules الكورسات ضمن domain كامل.
     * كل عنصر syllabus بيمثّل موضوع فرعي داخل module معيّن، وبنستخدم
     * الـ module + الكورس (إذا موجود) كـ context بس للسؤال.
     */
    public function startCategoryPlacementTest($categoryId)
    {
        $category = Category::findOrFail($categoryId);

        set_time_limit(300);

        $syllabusItems = DB::table('syllabus')
            ->join('modules', 'syllabus.module_id', '=', 'modules.id')
            ->leftJoin('course_modules', 'course_modules.module_id', '=', 'modules.id')
            ->leftJoin('courses', 'courses.id', '=', 'course_modules.course_id')
            ->where('syllabus.category_id', $categoryId)
            ->select([
                'syllabus.id as syllabus_id',
                'syllabus.name as topic',
                'modules.id as module_id',
                'modules.name as module_name',
                'courses.id as course_id',
                'courses.title as course_title',
                'course_modules.order_index as position',
            ])
            ->orderBy('modules.id')
            ->orderBy('courses.id')
            ->orderBy('course_modules.order_index')
            ->get()
            ->unique('syllabus_id')
            ->map(fn ($row) => [
                'source_type' => 'syllabus',
                'syllabus_id' => (int) $row->syllabus_id,
                'module_id' => (int) $row->module_id,
                'module_name' => $row->module_name,
                'course_id' => $row->course_id ? (int) $row->course_id : null,
                'course_title' => $row->course_title,
                'topic' => $row->topic,
                'position' => (int) ($row->position ?? 0),
            ])
            ->values()
            ->all();

        if (empty($syllabusItems)) {
            return response()->json([
                'error' => 'لا توجد عناصر syllabus لهذه الفئة.',
            ], 400);
        }

        $blocks = $this->placementService->generateThresholdBlocks($syllabusItems, 25);

        if (count($blocks) < 25) {
            return response()->json([
                'error' => 'لا توجد عناصر syllabus كافية لتوليد اختبار من 25 سؤالاً.',
                'available_topics' => count($blocks),
            ], 422);
        }

        session(['placement_test_blocks_' . $categoryId => $blocks]);

        return response()->json([
            'status' => 'success',
            'category_id' => $category->id,
            'category' => $category->name,
            'data_sources' => [
                'syllabus_items' => count($syllabusItems),
            ],
            'placement_topics' => $blocks,
            'threshold_topics' => array_column($blocks, 'threshold_topic'),
            'blocks' => $blocks,
        ]);
    }
}
