<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('workout_entries')
            ->where('workout_type', 'other')
            ->update([
                'workout_type' => 'custom',
                'custom_type' => 'Other',
            ]);
    }

    public function down(): void
    {
        DB::table('workout_entries')
            ->where('workout_type', 'custom')
            ->whereNotNull('custom_type')
            ->where('custom_type', 'Other')
            ->update([
                'workout_type' => 'other',
                'custom_type' => null,
            ]);
    }
};
