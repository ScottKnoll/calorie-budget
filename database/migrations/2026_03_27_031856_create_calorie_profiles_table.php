<?php

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\Gender;
use App\Enums\Goal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calorie_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gender')->default(Gender::Male->value);
            $table->unsignedTinyInteger('age');
            $table->unsignedTinyInteger('height_feet');
            $table->unsignedTinyInteger('height_inches')->default(0);
            $table->unsignedSmallInteger('weight_lbs');
            $table->unsignedSmallInteger('goal_weight_lbs')->nullable();
            $table->date('start_date')->nullable();
            $table->unsignedTinyInteger('calorie_deficit_pct')->default(20);
            $table->string('activity_factor')->default(ActivityFactor::Sedentary->value);
            $table->string('exercise_factor')->default(ExerciseFactor::None->value);
            $table->unsignedSmallInteger('tdee');
            $table->string('goal')->default(Goal::Maintain->value);
            $table->unsignedSmallInteger('daily_calorie_target');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calorie_profiles');
    }
};
