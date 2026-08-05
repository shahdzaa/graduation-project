<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Services\PlacementTestService;
use Illuminate\Support\Facades\DB;

class PlacementTestController extends Controller
{
    public function __construct(
        protected PlacementTestService $placementService
    ) {
    }

    public function startDomainPlacementTest($domainId)
    {
        $domain = Domain::findOrFail($domainId);

        set_time_limit(300);

        /*
         * المصدر الوحيد الآن هو modules الكورسات ضمن الدومين.
         * الموديل هو من يقرر شو الطالب بيعرف وشو لأ من خلال الأسئلة.
         */
        $modules = DB::table('course_modules')
            ->join('courses', 'course_modules.course_id', '=', 'courses.id')
            ->join('modules', 'course_modules.module_id', '=', 'modules.id')
            ->where('courses.domain_id', $domainId)
            ->select([
                'courses.id as course_id',
                'courses.title as course_title',
                'modules.id as module_id',
                'modules.name as topic',
                'course_modules.order_index as position',
            ])
            ->orderBy('courses.id')
            ->orderBy('course_modules.order_index')
            ->get()
            ->map(fn ($row) => [
                'source_type' => 'module',
                'course_id' => (int) $row->course_id,
                'course_title' => $row->course_title,
                'module_name' => $row->topic,
                'topic' => $row->topic,
                'position' => (int) $row->position,
            ])
            ->all();

        if (empty($modules)) {
            return response()->json([
                'error' => 'لا توجد modules لهذا المجال.',
            ], 400);
        }

        $blocks = $this->placementService->generateThresholdBlocks($modules, 25);

        if (count($blocks) < 25) {
            return response()->json([
                'error' => 'لا توجد modules كافية لتوليد اختبار من 25 سؤالاً.',
                'available_topics' => count($blocks),
            ], 422);
        }

        session(['placement_test_blocks_' . $domainId => $blocks]);

        return response()->json([
            'status' => 'success',
            'domain' => $domain->name,
            'data_sources' => [
                'modules' => count($modules),
            ],
            // هذا الحقل هو الذي يُرسل إلى FastAPI.
            'placement_topics' => $blocks,
            // إبقاؤه مؤقتاً كي لا تتعطل الواجهة القديمة.
            'threshold_topics' => array_column($blocks, 'threshold_topic'),
            'blocks' => $blocks,
        ]);
    }
}
