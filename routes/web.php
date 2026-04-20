<?php

use App\Livewire\Budget\DailyEntry;
use App\Livewire\Budget\Intake;
use App\Livewire\Budget\Macros;
use App\Livewire\Budget\Setup;
use App\Livewire\Budget\WeeklySummary;
use App\Livewire\Budget\WeightLog;
use App\Livewire\Budget\WorkoutLog;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/setup')->name('home');

Route::livewire('setup', Setup::class)->name('budget.setup')->middleware('intake.completed');
Route::livewire('macros', Macros::class)->name('budget.macros')->middleware('intake.completed');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard')->middleware('intake.completed');
    Route::livewire('intake', Intake::class)->name('budget.intake');

    Route::livewire('log/{date?}', DailyEntry::class)->name('budget.log')->middleware('intake.completed');
    Route::livewire('summary', WeeklySummary::class)->name('budget.summary')->middleware('intake.completed');
    Route::livewire('weight', WeightLog::class)->name('budget.weight')->middleware('intake.completed');
    Route::livewire('workouts', WorkoutLog::class)->name('budget.workouts')->middleware('intake.completed');
});

require __DIR__.'/settings.php';
