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

// API Resource Routes
Route::apiResource('users', UserController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('courses', CourseController::class);
Route::apiResource('assessments', AssessmentController::class);
Route::apiResource('questions', QuestionController::class);
Route::apiResource('answer-options', AnswerOptionController::class);
Route::apiResource('user-test-attempts', UserTestAttemptController::class);
Route::apiResource('user-answers', UserAnswerController::class);
Route::apiResource('recommendation-logs', RecommendationLogController::class);

// Additional Resource Routes
Route::apiResource('course-instructors', CourseInstructorController::class);
Route::apiResource('course-levels', CourseLevelController::class);
Route::apiResource('course-modules', CourseModuleController::class);
Route::apiResource('course-organizations', CourseOrganizationController::class);
Route::apiResource('course-prerequisites', CoursePrerequisiteController::class);
Route::apiResource('course-reviews', CourseReviewController::class);
Route::apiResource('course-skills', CourseSkillController::class);
Route::apiResource('course-types', CourseTypeController::class);
Route::apiResource('domains', DomainController::class);
Route::apiResource('instructor-profiles', InstructorProfileController::class);
Route::apiResource('modules', ModuleController::class);
Route::apiResource('notifications', NotificationController::class);
Route::apiResource('organizations', OrganizationController::class);
Route::apiResource('skills', SkillController::class);
Route::apiResource('student-courses', StudentCourseController::class);
Route::apiResource('student-profiles', StudentProfileController::class);
Route::apiResource('student-skill-matrices', StudentSkillMatrixController::class);
Route::apiResource('syllabi', SyllabusController::class);
Route::apiResource('syllabus-types', SyllabusTypeController::class);
Route::apiResource('certificates', CertificateController::class);
Route::apiResource('aptitude-score-mappings', AptitudeScoreMappingController::class);
Route::get('ai-quizzes/{courseId}', [AIQuizController::class, 'generateCourseQuiz']);
