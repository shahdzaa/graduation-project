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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title', 250);
            $table->string('url', 1000);
            $table->string('thumbnail')->nullable();  
            $table->decimal('price', 8, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->string('language', 10)->default('en');
            $table->boolean('is_published')->default(true);
            $table->integer('duration_minutes');
            $table->foreignId('level_id')->constrained('course_levels');
            $table->foreignId('type_id')->constrained('course_types');
            $table->mediumText('description')->nullable();
            $table->string('schedule')->nullable()->default('Flexible schedule');
            $table->foreignId('domain_id')->constrained('domains');
            $table->float('average_rating')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
