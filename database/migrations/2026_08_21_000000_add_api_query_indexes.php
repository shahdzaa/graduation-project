<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->index(['is_published', 'average_rating'], 'courses_published_rating_index');
            $table->index(['is_published', 'created_at'], 'courses_published_created_index');
        });

        Schema::table('syllabus', function (Blueprint $table) {
            $table->index(['category_id', 'module_id', 'order_index'], 'syllabus_category_module_order_index');
        });

        Schema::table('student_courses', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'enrolled_at'], 'student_courses_user_status_date_index');
        });

        Schema::table('placement_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'start_time'], 'placement_attempts_user_status_date_index');
        });

        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->index(['user_id', 'recommendation_date'], 'recommendation_logs_user_date_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at', 'created_at'], 'notifications_user_read_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_date_index');
        });

        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->dropIndex('recommendation_logs_user_date_index');
        });

        Schema::table('placement_attempts', function (Blueprint $table) {
            $table->dropIndex('placement_attempts_user_status_date_index');
        });

        Schema::table('student_courses', function (Blueprint $table) {
            $table->dropIndex('student_courses_user_status_date_index');
        });

        Schema::table('syllabus', function (Blueprint $table) {
            $table->dropIndex('syllabus_category_module_order_index');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_published_rating_index');
            $table->dropIndex('courses_published_created_index');
        });
    }
};
