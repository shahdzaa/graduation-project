<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_outcomes', function (Blueprint $table) {
            $table->id();
            
            // Link to the course
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->cascadeOnDelete();

            // The actual learning point (e.g., "Master Laravel Migrations")
            $table->string('content', 500); 

            // Optional: To arrange the points in a specific order
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_outcomes');
    }
};
