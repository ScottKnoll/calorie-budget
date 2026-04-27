<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intake_responses', function (Blueprint $table) {
            // Workout / Fitness
            $table->json('fitness_access')->nullable()->after('stress_level');
            $table->string('current_activity')->nullable()->after('fitness_access');
            $table->json('workout_preferences')->nullable()->after('current_activity');
            $table->string('has_injuries')->default('no')->after('workout_preferences');
            $table->text('injury_description')->nullable()->after('has_injuries');
        });
    }

    public function down(): void
    {
        Schema::table('intake_responses', function (Blueprint $table) {
            $table->dropColumn([
                'fitness_access',
                'current_activity',
                'workout_preferences',
                'has_injuries',
                'injury_description',
            ]);
        });
    }
};
