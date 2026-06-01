<?php

use App\Livewire\Budget\CheckIn;
use App\Livewire\Budget\CheckIns;
use App\Livewire\Budget\DailyEntry;
use App\Livewire\Budget\Intake;
use App\Livewire\Budget\IntakeReview;
use App\Livewire\Budget\Macros;
use App\Livewire\Budget\MyPlan;
use App\Livewire\Budget\Setup;
use App\Livewire\Budget\WeeklySummary;
use App\Livewire\Budget\WeightLog;
use App\Livewire\Budget\WorkoutLog;
use App\Livewire\Coach\ClientProfile;
use App\Livewire\Coach\Dashboard as CoachDashboard;
use App\Livewire\Coach\PlanEditor;
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
    Route::livewire('my-plan', MyPlan::class)->name('budget.my-plan');
    Route::livewire('check-in', CheckIn::class)->name('budget.check-in');
    Route::livewire('check-in/{checkIn}/edit', CheckIn::class)->name('budget.check-in.edit');
    Route::livewire('check-ins', CheckIns::class)->name('budget.check-ins');

    // Legacy redirect — keep the old named route working
    Route::livewire('admin/intake-review', IntakeReview::class)->name('budget.intake-review')->middleware('coach');

    // Coach area
    Route::middleware('coach')->prefix('coach')->name('coach.')->group(function () {
        Route::livewire('/', CoachDashboard::class)->name('dashboard');
        Route::livewire('/clients/{client}', ClientProfile::class)->name('clients.show');
        Route::livewire('/clients/{client}/plans/create', PlanEditor::class)->name('clients.plans.create');
        Route::livewire('/clients/{client}/plans/{plan}/edit', PlanEditor::class)->name('clients.plans.edit');
    });
});

require __DIR__.'/settings.php';
