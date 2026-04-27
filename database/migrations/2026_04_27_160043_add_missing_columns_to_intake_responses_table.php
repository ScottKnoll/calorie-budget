<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intake_responses', function (Blueprint $table) {
            if (! Schema::hasColumn('intake_responses', 'main_goal_other')) {
                $table->string('main_goal_other')->nullable()->after('main_goal');
            }

            if (! Schema::hasColumn('intake_responses', 'work_schedule_other')) {
                $table->string('work_schedule_other')->nullable()->after('work_schedule');
            }

            if (! Schema::hasColumn('intake_responses', 'dietary_preference')) {
                $table->string('dietary_preference')->nullable()->after('dietary_restrictions');
            }

            if (! Schema::hasColumn('intake_responses', 'dietary_preference_other')) {
                $table->string('dietary_preference_other')->nullable()->after('dietary_preference');
            }

            if (! Schema::hasColumn('intake_responses', 'past_consistency_struggles')) {
                $table->text('past_consistency_struggles')->nullable()->after('open_to_tracking');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intake_responses', function (Blueprint $table) {
            $table->dropColumn([
                'main_goal_other',
                'work_schedule_other',
                'dietary_preference',
                'dietary_preference_other',
                'past_consistency_struggles',
            ]);
        });
    }
};
