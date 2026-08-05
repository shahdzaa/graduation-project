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
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// API Resource Routes
Route::apiResource('users', UserController::class);

// عام - أي زائر يقدر يشوف بدون تسجيل دخول
Route::apiResource('courses', CourseController::class)->only(['index', 'show']);
Route::apiResource('domains', DomainController::class)->only(['index', 'show']);

// محمي - محتاج تسجيل دخول + دور مناسب
Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('role:instructor|admin')->group(function () {
        Route::apiResource('courses', CourseController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('domains', DomainController::class)->only(['store', 'update', 'destroy']);
    });

    Route::post('/placement-test/{domainId}', [PlacementTestController::class, 'startDomainPlacementTest']);

});