<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlacementAttemptResource;
use App\Models\Category;
use App\Models\PlacementAttempt;
use App\Services\PlacementTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlacementTestController extends Controller
{
    public function __construct(
        protected PlacementTestService $placementService
    ) {
    }

    /**
     * POST /api/placement/generate
     *
     * يقبل فقط category_id
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

        $syllabusItems = $this->syllabusItemsForCategory((int) $request->category_id);

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

        if (! is_array($questions) || count($questions) !== 25) {
            return response()->json([
                'message' => 'خدمة توليد الأسئلة أعادت بنية غير صالحة.',
                'questions_received' => is_array($questions) ? count($questions) : 0,
            ], 502);
        }

        // ── ربط كل سؤال بالـ syllabus_topic من الـ block تبعه ──
        foreach ($questions as $index => &$question) {
            $question['syllabus_topic'] = $blocks[$index]['threshold_topic'] ?? null;
        }
        unset($question);

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
     *
     * يحسب النتيجة + known_syllabi
     * ثم يبعت للـ Recommender service ويرجع التوصيات
     */
    public function submit(Request $request, int $attemptId)
    {
        set_time_limit(300);
        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:placement_questions,id',
            'answers.*.selected_option_id' => 'required|integer|exists:placement_answer_options,id',
        ]);

        $attempt = PlacementAttempt::where('id', $attemptId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $result = $this->placementService->submitAnswers(
            attemptId: $attempt->id,
            answers: $request->answers
        );

        // ── جلب التوصيات من الـ Recommender service ──
        $recommendations = $this->fetchRecommendations($result['known_syllabi'] ?? []);

        return response()->json([
            'message'         => 'تم تسليم الاختبار بنجاح.',
            'score'           => $result['score'],
            'total'           => $result['total'],
            'known_syllabi'   => $result['known_syllabi'],
            'recommendations' => $recommendations,
        ]);
    }

    public function attempts(Request $request)
    {
        $attempts = PlacementAttempt::query()
            ->where('user_id', $request->user()->id)
            ->select([
                'id', 'user_id', 'category', 'generation_batch_id', 'start_time',
                'end_time', 'total_score', 'known_syllabi', 'status',
            ])
            ->latest('start_time')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return PlacementAttemptResource::collection($attempts)->response();
    }

    public function showAttempt(Request $request, int $attempt)
    {
        $placementAttempt = PlacementAttempt::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($attempt);

        return (new PlacementAttemptResource($placementAttempt))->response();
    }

    /**
     * يبعت الـ known_syllabi للـ Recommender ويرجع التوصيات.
     * لو الـ service ما اشتغل، بيرجع مصفوفة فارغة بدون ما يوقف الـ submit.
     */
    private function fetchRecommendations(array $knownSyllabi): array
    {
        if (empty($knownSyllabi)) {
            return [];
        }

        $syllabusText = implode(' ', $knownSyllabi);
        $recommenderUrl = config('services.recommender.url', 'http://localhost:8002');

        try {
            $response = Http::timeout(60)->post(
                $recommenderUrl . '/api/recommend',
                ['syllabus_text' => $syllabusText]
            );

            if ($response->successful()) {
                return $response->json('recommendations', []);
            }

            Log::warning('Recommender service returned error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Recommender service unreachable', ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Recommender service unexpected error', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * POST /api/placement-test/{categoryId}
     */
    public function startCategoryPlacementTest($categoryId)
    {
        $category = Category::findOrFail($categoryId);

        set_time_limit(300);

        $syllabusItems = $this->syllabusItemsForCategory((int) $categoryId);

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

    private function syllabusItemsForCategory(int $categoryId): array
    {
        $coursePerModule = DB::table('course_modules')
            ->selectRaw('module_id, MIN(course_id) as course_id')
            ->groupBy('module_id');

        return DB::table('syllabus')
            ->join('modules', 'syllabus.module_id', '=', 'modules.id')
            ->leftJoinSub($coursePerModule, 'module_course', function ($join) {
                $join->on('module_course.module_id', '=', 'modules.id');
            })
            ->leftJoin('courses', 'courses.id', '=', 'module_course.course_id')
            ->where('syllabus.category_id', $categoryId)
            ->select([
                'syllabus.id as syllabus_id',
                'syllabus.name as topic',
                'syllabus.order_index as position',
                'modules.id as module_id',
                'modules.name as module_name',
                'courses.id as course_id',
                'courses.title as course_title',
            ])
            ->orderBy('syllabus.module_id')
            ->orderBy('syllabus.order_index')
            ->get()
            ->map(fn ($row) => [
                'source_type' => 'syllabus',
                'syllabus_id' => (int) $row->syllabus_id,
                'module_id' => (int) $row->module_id,
                'module_name' => $row->module_name,
                'course_id' => $row->course_id ? (int) $row->course_id : null,
                'course_title' => $row->course_title,
                'topic' => $row->topic,
                'position' => (int) $row->position,
            ])
            ->all();
    }
}
