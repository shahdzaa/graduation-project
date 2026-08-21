<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\StudentCourse;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_users' => User::count(),
                'courses' => Course::count(),
                'instructors' => User::role('instructor', 'web')->count(),
                'students' => User::role('student', 'web')->count(),
                'categories' => Category::count(),
                'total_enrollments' => StudentCourse::count(),
                'active_enrollments' => StudentCourse::where('status', 'active')->count(),
            ],
        ]);
    }
}
