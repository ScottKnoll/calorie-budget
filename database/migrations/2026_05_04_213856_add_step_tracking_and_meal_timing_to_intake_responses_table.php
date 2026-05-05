<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intake_responses', function (Blueprint $table) {
            $table->string('open_to_tracking_steps')->nullable()->after('work_schedule_other');
            $table->string('daily_steps')->nullable()->change();
            $table->string('meal_timing_pattern')->nullable()->after('tracks_currently');
            $table->string('meal_timing_pattern_other')->nullable()->after('meal_timing_pattern');
        });
    }

    public function down(): void
    {
        Schema::table('intake_responses', function (Blueprint $table) {
            $table->dropColumn(['open_to_tracking_steps', 'meal_timing_pattern', 'meal_timing_pattern_other']);
            $table->string('daily_steps')->nullable(false)->change();
        });
    }
};
