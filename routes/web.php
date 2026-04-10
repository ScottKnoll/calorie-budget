<?php

use App\Livewire\Budget\DailyEntry;
use App\Livewire\Budget\Macros;
use App\Livewire\Budget\Setup;
use App\Livewire\Budget\WeeklySummary;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');

    Route::livewire('setup', Setup::class)->name('budget.setup');
    Route::livewire('macros', Macros::class)->name('budget.macros');
    Route::livewire('log/{date?}', DailyEntry::class)->name('budget.log');
    Route::livewire('summary', WeeklySummary::class)->name('budget.summary');
});

require __DIR__.'/settings.php';
