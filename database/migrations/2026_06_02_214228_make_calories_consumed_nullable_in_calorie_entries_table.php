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
        Schema::table('calorie_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('calories_consumed')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calorie_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('calories_consumed')->nullable(false)->change();
        });
    }
};
