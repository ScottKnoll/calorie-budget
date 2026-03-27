<?php

use App\Livewire\Budget\DailyEntry;
use App\Livewire\Budget\Setup;
use App\Livewire\Budget\WeeklySummary;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('setup', Setup::class)->name('budget.setup');
    Route::livewire('log', DailyEntry::class)->name('budget.log');
    Route::livewire('summary', WeeklySummary::class)->name('budget.summary');
});
