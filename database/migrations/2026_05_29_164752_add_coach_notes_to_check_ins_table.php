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
        Schema::table('check_ins', function (Blueprint $table) {
            $table->longText('coach_workout')->nullable()->after('need_help');
            $table->longText('coach_nutrition')->nullable()->after('coach_workout');
            $table->longText('coach_habits')->nullable()->after('coach_nutrition');
            $table->longText('coach_general')->nullable()->after('coach_habits');
            $table->longText('coach_focus_next_week')->nullable()->after('coach_general');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropColumn(['coach_workout', 'coach_nutrition', 'coach_habits', 'coach_general', 'coach_focus_next_week']);
        });
    }
};
