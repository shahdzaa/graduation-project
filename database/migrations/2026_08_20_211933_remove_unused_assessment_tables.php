<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // فك ارتباط سجل التوصيات بمحاولة الاختبار القديمة
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->dropForeign('recommendation_logs_attempt_id_foreign');
        });

        // ربط سجل التوصيات بمحاولة Placement المستخدمة حاليًا
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->foreign('attempt_id')
                ->references('id')
                ->on('placement_attempts')
                ->cascadeOnDelete();
        });

        // حذف جداول نظام الاختبار القديم
        Schema::dropIfExists('aptitude_score_mappings');
        Schema::dropIfExists('user_answers');
        Schema::dropIfExists('answer_options');
        Schema::dropIfExists('user_test_attempts');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('assessments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
