<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calorie_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->change();
            $table->unsignedTinyInteger('height_feet')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('calorie_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable(false)->change();
            $table->unsignedTinyInteger('height_feet')->nullable(false)->change();
        });
    }
};
