<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Goal
            $table->string('main_goal');
            $table->string('main_goal_other')->nullable();
            $table->text('why_now')->nullable();

            // Current state
            // Lifestyle
            $table->string('work_schedule');
            $table->string('work_schedule_other')->nullable();
            $table->string('daily_steps');
            $table->string('sleep_hours');
            $table->string('stress_level');

            // Nutrition
            $table->string('tracks_currently');
            $table->text('typical_day_of_eating')->nullable();
            $table->text('dietary_restrictions')->nullable();
            $table->string('dietary_preference')->nullable();
            $table->string('dietary_preference_other')->nullable();

            // Expectations
            $table->string('workout_days_per_week');
            $table->string('open_to_tracking');
            $table->text('past_consistency_struggles')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_responses');
    }
};
