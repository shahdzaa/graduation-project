<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIQuizController;
use App\Http\Controllers\AnswerOptionController;
use App\Http\Controllers\AptitudeScoreMappingController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseInstructorController;
use App\Http\Controllers\CourseLevelController;
use App\Http\Controllers\CourseModuleController;
use App\Http\Controllers\CourseOrganizationController;
use App\Http\Controllers\CoursePrerequisiteController;
use App\Http\Controllers\CourseReviewController;
use App\Http\Controllers\CourseSkillController;
use App\Http\Controllers\CourseTypeController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\InstructorProfileController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PlacementTestController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\RecommendationLogController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentSkillMatrixController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\SyllabusTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserAnswerController;
use App\Http\Controllers\UserTestAttemptController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| ✅ PUBLIC ROUTES — لا يحتاج تسجيل دخول
|--------------------------------------------------------------------------
*/

// Auth
Route::post('login',    [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// استعراض عام
Route::apiResource('courses',      CourseController::class)->only(['index', 'show']);
Route::apiResource('domains',      DomainController::class)->only(['index', 'show']);
Route::apiResource('categories',   CategoryController::class)->only(['index', 'show']);
Route::apiResource('course-levels', CourseLevelController::class)->only(['index', 'show']);
Route::apiResource('course-types',  CourseTypeController::class)->only(['index', 'show']);
Route::apiResource('organizations', OrganizationController::class)->only(['index', 'show']);
Route::apiResource('skills',        SkillController::class)->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| 🔒 PROTECTED ROUTES — يحتاج auth:sanctum
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // --- المستخدم الحالي ---
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('logout', [AuthController::class, 'logout']);

    /*
    |----------------------------------------------------------------------
    | 👤 STUDENT ROUTES — دور student
    |----------------------------------------------------------------------
    */
    Route::middleware('role:student')->group(function () {

        // Profile الطالب
        Route::apiResource('student-profiles', StudentProfileController::class)
            ->only(['show', 'update']);
        Route::get('/student/dashboard', [StudentProfileController::class, 'dashboard']);

        // الكورسات المسجل فيها
        Route::apiResource('student-courses', StudentCourseController::class)
            ->only(['index', 'show', 'store', 'destroy']);

        // التقييمات
        Route::apiResource('course-reviews', CourseReviewController::class)
            ->only(['store', 'update', 'destroy']);

        // Skill Matrix الطالب
        Route::apiResource('student-skill-matrices', StudentSkillMatrixController::class)
            ->only(['index', 'show']);

        // Placement Test
        Route::post('/placement/generate',                        [PlacementTestController::class, 'generate']);
        Route::post('/placement/{attempt}/submit',                [PlacementTestController::class, 'submit']);
        Route::post('/placement-test/{categoryId}',              [PlacementTestController::class, 'startCategoryPlacementTest']);

        // محاولات الاختبار
        Route::apiResource('user-test-attempts', UserTestAttemptController::class)
            ->only(['index', 'show', 'store']);

        // إجابات الطالب
        Route::apiResource('user-answers', UserAnswerController::class)
            ->only(['store']);

        // Recommendation logs (قراءة فقط)
        Route::apiResource('recommendation-logs', RecommendationLogController::class)
            ->only(['index', 'show']);

        // Notifications
        Route::apiResource('notifications', NotificationController::class)
            ->only(['index', 'show', 'update']); // update = mark as read

        // Certificates
        Route::apiResource('certificates', CertificateController::class)
            ->only(['index', 'show']);
    });

    /*
    |----------------------------------------------------------------------
    | 🎓 INSTRUCTOR ROUTES — دور instructor
    |----------------------------------------------------------------------
    */
    Route::middleware('role:instructor')->group(function () {

        // Profile المدرس
        Route::apiResource('instructor-profiles', InstructorProfileController::class)
            ->only(['show', 'update']);
        Route::get('/instructor/me', [InstructorProfileController::class, 'me']);   
        // إدارة الكورسات
        Route::apiResource('courses', CourseController::class)
            ->only(['store', 'update', 'destroy']);

        // موديولات الكورس
        Route::apiResource('course-modules', CourseModuleController::class);
        Route::apiResource('modules',        ModuleController::class);

        // سيلابس
        Route::apiResource('syllabi',       SyllabusController::class);
        Route::apiResource('syllabus-types', SyllabusTypeController::class)
            ->only(['index', 'show']);

        // مهارات الكورس
        Route::apiResource('course-skills', CourseSkillController::class);

        // متطلبات مسبقة
        Route::apiResource('course-prerequisites', CoursePrerequisiteController::class);

        // مراجعة تقييمات الطلاب (قراءة فقط)
        Route::apiResource('assessments', AssessmentController::class)
            ->only(['index', 'show']);

        // إنشاء أسئلة
        Route::apiResource('questions',      QuestionController::class);
        Route::apiResource('answer-options', AnswerOptionController::class);

        // تقييمات الكورس (قراءة فقط)
        Route::apiResource('course-reviews', CourseReviewController::class)
            ->only(['index', 'show']);
    });

    /*
    |----------------------------------------------------------------------
    | 🛡️ ADMIN ROUTES — دور admin
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        // إدارة المستخدمين
        Route::apiResource('users', UserController::class);

        // Profiles
        Route::apiResource('student-profiles',    StudentProfileController::class);
        Route::apiResource('instructor-profiles', InstructorProfileController::class);

        // Lookup Tables
        Route::apiResource('course-levels',  CourseLevelController::class);
        Route::apiResource('course-types',   CourseTypeController::class);
        Route::apiResource('organizations',  OrganizationController::class);
        Route::apiResource('domains',        DomainController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('categories',     CategoryController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('skills',         SkillController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('syllabus-types', SyllabusTypeController::class)->only(['store', 'update', 'destroy']);

        // Course relations
        Route::apiResource('course-instructors',   CourseInstructorController::class);
        Route::apiResource('course-organizations', CourseOrganizationController::class);

        // Assessments & Questions (كامل)
        Route::apiResource('assessments',    AssessmentController::class);
        Route::apiResource('questions',      QuestionController::class);
        Route::apiResource('answer-options', AnswerOptionController::class);

        // Aptitude Score Mappings
        Route::apiResource('aptitude-score-mappings', AptitudeScoreMappingController::class);

        // Student Skill Matrices
        Route::apiResource('student-skill-matrices', StudentSkillMatrixController::class);

        // Recommendation Logs (قراءة + حذف)
        Route::apiResource('recommendation-logs', RecommendationLogController::class)
            ->only(['index', 'show', 'destroy']);

        // Test Attempts (كامل الصلاحيات)
        Route::apiResource('user-test-attempts', UserTestAttemptController::class);
        Route::apiResource('user-answers',        UserAnswerController::class);

        // AI Quiz
        Route::post('/ai-quiz/generate', [AIQuizController::class, 'generate']);

        // Certificates
        Route::apiResource('certificates', CertificateController::class);

        // Notifications (كامل)
        Route::apiResource('notifications', NotificationController::class);
    });

    /*
    |----------------------------------------------------------------------
    | 🔄 SHARED — instructor|admin معاً
    |----------------------------------------------------------------------
    */
    Route::middleware('role:instructor|admin')->group(function () {

        // Syllabus (المدرس والأدمن يقدرون يشوفوا)
        Route::apiResource('syllabi', SyllabusController::class)->only(['index', 'show']);
    });
});