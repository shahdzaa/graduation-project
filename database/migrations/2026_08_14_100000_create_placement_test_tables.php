<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. الأسئلة المولّدة من AI ─────────────────────────────────
        Schema::create('placement_questions', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->unsignedTinyInteger('question_number');
            $table->text('question_text');
            $table->enum('difficulty_level', ['Beginner', 'Intermediate', 'Advanced']);
            $table->string('syllabus_topic')->nullable();
            $table->text('explanation')->nullable();
            $table->string('generation_batch_id');
            $table->timestamps();

            $table->index(['category', 'generation_batch_id']);
        });

        // ── 2. الخيارات A B C D ───────────────────────────────────────
        Schema::create('placement_answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_question_id')
                  ->constrained('placement_questions')
                  ->onDelete('cascade');
            $table->char('option_key', 1);   // A, B, C, D
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['placement_question_id', 'option_key']);
        });

        // ── 3. محاولة الطالب ──────────────────────────────────────────
        Schema::create('placement_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->string('generation_batch_id');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->unsignedTinyInteger('total_score')->nullable();
            $table->json('known_syllabi')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'category']);
        });

        // ── 4. إجابات الطالب ──────────────────────────────────────────
        Schema::create('placement_user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')
                  ->constrained('placement_attempts')
                  ->onDelete('cascade');
            $table->foreignId('placement_question_id')
                  ->constrained('placement_questions')
                  ->onDelete('cascade');
            $table->foreignId('selected_option_id')
                  ->constrained('placement_answer_options')
                  ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['attempt_id', 'placement_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_user_answers');
        Schema::dropIfExists('placement_attempts');
        Schema::dropIfExists('placement_answer_options');
        Schema::dropIfExists('placement_questions');
    }
};
