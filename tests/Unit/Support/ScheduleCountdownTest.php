<?php

use App\Support\ScheduleCountdown;
use Carbon\CarbonImmutable;

it('shows two weeks for a date fourteen calendar days away at midnight', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-16 20:42:00'));

    $scheduledAt = CarbonImmutable::parse('2026-09-30 00:00:00');

    expect(ScheduleCountdown::for($scheduledAt))->toBe('in 2 weeks');
});

it('shows six days ago for a date six calendar days in the past', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-16 12:00:00'));

    $scheduledAt = CarbonImmutable::parse('2026-09-10 08:00:00');

    expect(ScheduleCountdown::for($scheduledAt))->toBe('6 days ago');
});

it('shows today for the same calendar day', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-16 20:42:00'));

    $scheduledAt = CarbonImmutable::parse('2026-09-16 08:00:00');

    expect(ScheduleCountdown::for($scheduledAt))->toBe('today');
});
