<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calorie_profiles', function (Blueprint $table) {
            $table->string('formula')->default('standard')->after('exercise_factor');
            $table->decimal('body_fat_pct', 5, 2)->nullable()->after('formula');
        });
    }

    public function down(): void
    {
        Schema::table('calorie_profiles', function (Blueprint $table) {
            $table->dropColumn(['formula', 'body_fat_pct']);
        });
    }
};
