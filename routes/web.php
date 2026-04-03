<?php

use App\Livewire\Budget\DailyEntry;
use App\Livewire\Budget\Setup;
use App\Livewire\Budget\WeeklySummary;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('setup', Setup::class)->name('budget.setup');
    Route::livewire('log/{date?}', DailyEntry::class)->name('budget.log');
    Route::livewire('summary', WeeklySummary::class)->name('budget.summary');
});

require __DIR__.'/settings.php';
