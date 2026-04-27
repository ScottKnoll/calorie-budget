<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('intake_responses', 'current_activity_other')) {
            Schema::table('intake_responses', function (Blueprint $table) {
                $table->dropColumn('current_activity_other');
            });
        }
    }

    public function down(): void
    {
        Schema::table('intake_responses', function (Blueprint $table) {
            $table->string('current_activity_other')->nullable()->after('current_activity');
        });
    }
};
