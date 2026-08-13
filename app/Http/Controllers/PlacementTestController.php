<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\PlacementTestService;
use Illuminate\Support\Facades\DB;

class PlacementTestController extends Controller
{
    public function __construct(
        protected PlacementTestService $placementService
    ) {
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
            // عنصر الـ syllabus ممكن يتكرر إذا الـ module تبعو مرتبط بأكتر
            // من كورس (join مع course_modules)؛ منحتفظ بأول سياق كورس بس
            // لكل syllabus_id حتى ما يصير عندنا نفس السؤال مرتين.
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
            'category' => $category->name,
            'data_sources' => [
                'syllabus_items' => count($syllabusItems),
            ],
            // هذا الحقل هو الذي يُرسل إلى FastAPI.
            'placement_topics' => $blocks,
            // إبقاؤه مؤقتاً كي لا تتعطل الواجهة القديمة.
            'threshold_topics' => array_column($blocks, 'threshold_topic'),
            'blocks' => $blocks,
        ]);
    }
}
