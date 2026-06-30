<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('next_check_in_date', 'next_check_in_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('next_check_in_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('next_check_in_at')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('next_check_in_at', 'next_check_in_date');
        });
    }
};
