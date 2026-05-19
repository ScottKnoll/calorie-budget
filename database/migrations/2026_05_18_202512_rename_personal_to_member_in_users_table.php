<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('user_type', 'personal')
            ->update(['user_type' => 'member']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type')->default('member')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')
            ->where('user_type', 'member')
            ->update(['user_type' => 'personal']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type')->default('personal')->change();
        });
    }
};
