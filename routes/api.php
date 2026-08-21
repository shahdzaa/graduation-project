<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
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
use App\Http\Controllers\RecommendationLogController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentSkillMatrixController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\SyllabusTypeController;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::apiResource('courses', CourseController::class)->only(['index', 'show']);
Route::apiResource('domains', DomainController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::get('categories/{category}/syllabi', [CategoryController::class, 'syllabi'])
    ->name('categories.syllabi');
Route::apiResource('course-levels', CourseLevelController::class)->only(['index', 'show']);
Route::apiResource('course-types', CourseTypeController::class)->only(['index', 'show']);
Route::apiResource('organizations', OrganizationController::class)->only(['index', 'show']);
Route::apiResource('skills', SkillController::class)->only(['index', 'show']);
Route::apiResource('syllabus-types', SyllabusTypeController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', function (Request $request) {
        return new UserResource($request->user()->load('roles'));
    });
    Route::post('logout', [AuthController::class, 'logout']);

    Route::middleware('role:student')->group(function () {
        Route::get('student/dashboard', [StudentProfileController::class, 'dashboard']);
        Route::apiResource('student-courses', StudentCourseController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::apiResource('course-reviews', CourseReviewController::class)
            ->only(['store', 'update', 'destroy']);
        Route::get('student-skill-matrices', [StudentSkillMatrixController::class, 'index']);
        Route::get('student-skill-matrices/{skill}', [StudentSkillMatrixController::class, 'show'])
            ->whereNumber('skill');

        Route::post('placement/generate', [PlacementTestController::class, 'generate']);
        Route::post('placement/{attemptId}/submit', [PlacementTestController::class, 'submit'])
            ->whereNumber('attemptId');
        Route::get('placement-attempts', [PlacementTestController::class, 'attempts']);
        Route::get('placement-attempts/{attempt}', [PlacementTestController::class, 'showAttempt'])
            ->whereNumber('attempt');
        Route::post('placement-test/{categoryId}', [PlacementTestController::class, 'startCategoryPlacementTest'])
            ->whereNumber('categoryId');
    });

    Route::middleware('role:student|admin')->group(function () {
        Route::apiResource('student-profiles', StudentProfileController::class)->only(['show', 'update']);
        Route::apiResource('recommendation-logs', RecommendationLogController::class)->only(['index', 'show']);
        Route::apiResource('notifications', NotificationController::class)->only(['index', 'show', 'update']);
        Route::apiResource('certificates', CertificateController::class)->only(['index', 'show']);
    });

    Route::middleware('role:instructor')->group(function () {
        Route::get('instructor/me', [InstructorProfileController::class, 'me']);
    });

    Route::middleware('role:instructor|admin')->group(function () {
        Route::apiResource('instructor-profiles', InstructorProfileController::class)->only(['show', 'update']);
        Route::apiResource('courses', CourseController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('course-modules', CourseModuleController::class);
        Route::apiResource('modules', ModuleController::class);
        Route::apiResource('syllabi', SyllabusController::class);
        Route::apiResource('course-prerequisites', CoursePrerequisiteController::class);

        Route::get('course-instructors', [CourseInstructorController::class, 'index']);
        Route::post('course-instructors', [CourseInstructorController::class, 'store']);
        Route::delete('course-instructors/{course}/{user}', [CourseInstructorController::class, 'destroy'])
            ->whereNumber(['course', 'user']);

        Route::get('course-organizations', [CourseOrganizationController::class, 'index']);
        Route::post('course-organizations', [CourseOrganizationController::class, 'store']);
        Route::delete('course-organizations/{course}/{organization}', [CourseOrganizationController::class, 'destroy'])
            ->whereNumber(['course', 'organization']);

        Route::get('course-skills', [CourseSkillController::class, 'index']);
        Route::post('course-skills', [CourseSkillController::class, 'store']);
        Route::delete('course-skills/{course}/{skill}', [CourseSkillController::class, 'destroy'])
            ->whereNumber(['course', 'skill']);

        Route::apiResource('course-reviews', CourseReviewController::class)->only(['index', 'show']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('admin/dashboard', [AdminDashboardController::class, 'index']);
        Route::apiResource('users', UserController::class);
        Route::apiResource('student-profiles', StudentProfileController::class)->only(['index', 'store', 'destroy']);
        Route::apiResource('instructor-profiles', InstructorProfileController::class)->only(['index', 'store', 'destroy']);

        Route::apiResource('course-levels', CourseLevelController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('course-types', CourseTypeController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('organizations', OrganizationController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('domains', DomainController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('skills', SkillController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('syllabus-types', SyllabusTypeController::class)->only(['store', 'update', 'destroy']);

        Route::post('student-skill-matrices', [StudentSkillMatrixController::class, 'store']);
        Route::get('admin/student-skill-matrices', [StudentSkillMatrixController::class, 'index']);
        Route::put('admin/student-skill-matrices/{user}/{skill}', [StudentSkillMatrixController::class, 'update'])
            ->whereNumber(['user', 'skill']);
        Route::delete('admin/student-skill-matrices/{user}/{skill}', [StudentSkillMatrixController::class, 'destroy'])
            ->whereNumber(['user', 'skill']);

        Route::delete('recommendation-logs/{recommendation_log}', [RecommendationLogController::class, 'destroy']);
        Route::apiResource('certificates', CertificateController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('notifications', NotificationController::class)->only(['store', 'destroy']);
    });
});
