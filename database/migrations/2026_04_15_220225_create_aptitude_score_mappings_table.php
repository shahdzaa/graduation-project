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
        Schema::create('aptitude_score_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_option_id')->constrained('answer_options')->cascadeOnDelete();
            $table->foreignId('domain_id')->constrained('domains');
            $table->foreignId('skill_id')->constrained('skills');
            $table->float('weight_score');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aptitude_score_mappings');
    }
};
