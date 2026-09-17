<?php

namespace App\Support;

use Carbon\CarbonInterface;

class ScheduleCountdown
{
    public static function for(CarbonInterface $scheduledAt): string
    {
        $today = now()->startOfDay();
        $scheduledDay = $scheduledAt->copy()->startOfDay();
        $days = (int) $today->diffInDays($scheduledDay, false);

        return match (true) {
            $days === 0 => 'today',
            $days === 1 => 'tomorrow',
            $days === -1 => 'yesterday',
            $days > 0 => 'in '.$today->longAbsoluteDiffForHumans($scheduledDay),
            default => $scheduledDay->longAbsoluteDiffForHumans($today).' ago',
        };
    }
}
