<?php

use App\Livewire\Budget\DailyEntry;
use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('redirects guests away from the log page', function () {
    $this->get(route('budget.log'))->assertRedirect(route('login'));
});

it('shows the log page to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.log'))
        ->assertSuccessful();
});

it('creates a new calorie entry for today', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1800)
        ->call('save');

    expect(CalorieEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->first())
        ->not->toBeNull()
        ->calories_consumed->toBe(1800);
});

it('updates an existing entry for the same day', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 1500]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1900)
        ->call('save');

    expect(CalorieEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->count())
        ->toBe(1);

    expect(CalorieEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->first()->calories_consumed)
        ->toBe(1900);
});

it('mounts with existing entry values', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 1650]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->assertSet('calories_consumed', 1650);
});

it('computes over/under vs daily target correctly', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $component = Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 2300);

    expect($component->instance()->overUnder)->toBe(300);
});

it('validates that calories consumed must be a non-negative integer', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', -1)
        ->call('save')
        ->assertHasErrors(['calories_consumed']);
});

it('returns zero for consumedThroughYesterday when today is Monday', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    // Travel to a Monday so there are no prior days this week.
    Carbon::setTestNow(Carbon::parse('next monday'));

    $component = Livewire::actingAs($user)->test(DailyEntry::class);

    expect($component->instance()->consumedThroughYesterday)->toBe(0);

    Carbon::setTestNow();
});

it('defaults unlogged prior days to the daily target in the weekly bank', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    // Travel to Wednesday — Mon and Tue should both default to 2000.
    Carbon::setTestNow(Carbon::parse('next monday')->addDays(2));

    $component = Livewire::actingAs($user)->test(DailyEntry::class);

    expect($component->instance()->consumedThroughYesterday)->toBe(4000); // 2 days × 2000

    Carbon::setTestNow();
});

it('uses logged values when prior days have entries', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    Carbon::setTestNow(Carbon::parse('next monday')->addDays(2)); // Wednesday
    $monday = Carbon::now()->startOfWeek();
    $tuesday = $monday->copy()->addDay();

    CalorieEntry::factory()->for($user)->create(['date' => $monday, 'calories_consumed' => 1600]);
    CalorieEntry::factory()->for($user)->create(['date' => $tuesday, 'calories_consumed' => 2400]);

    $component = Livewire::actingAs($user)->test(DailyEntry::class);

    // 1600 (logged Mon) + 2400 (logged Tue) = 4000
    expect($component->instance()->consumedThroughYesterday)->toBe(4000);

    Carbon::setTestNow();
});

it('computes todays allowance by spreading remaining budget across remaining days', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    // Wednesday: weekly budget = 14000, Mon defaulted to 2000, Tue logged 1600.
    Carbon::setTestNow(Carbon::parse('next monday')->addDays(2));
    $monday = Carbon::now()->startOfWeek();
    $tuesday = $monday->copy()->addDay();

    CalorieEntry::factory()->for($user)->create(['date' => $tuesday, 'calories_consumed' => 1600]);

    $component = Livewire::actingAs($user)->test(DailyEntry::class);

    // consumed through yesterday = 2000 (Mon default) + 1600 (Tue logged) = 3600
    // remaining = 14000 - 3600 = 10400
    // days remaining = Wed through Sun = 5
    // allowance = 10400 / 5 = 2080
    expect($component->instance()->todaysAllowance)->toBe(2080);

    Carbon::setTestNow();
});

it('computes remaining today as allowance minus consumed', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    Carbon::setTestNow(Carbon::parse('next monday')->addDays(2)); // Wednesday
    $tuesday = Carbon::now()->startOfWeek()->addDay();
    CalorieEntry::factory()->for($user)->create(['date' => $tuesday, 'calories_consumed' => 1600]);

    $component = Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1500);

    // todaysAllowance = 2080, consumed = 1500, remaining = 580
    expect($component->instance()->remainingToday)->toBe(580);

    Carbon::setTestNow();
});

it('mounts with a specific past date from the route parameter', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    $pastDate = Carbon::yesterday()->toDateString();

    CalorieEntry::factory()->for($user)->create([
        'date' => $pastDate,
        'calories_consumed' => 1750,
    ]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class, ['date' => $pastDate])
        ->assertSet('date', $pastDate)
        ->assertSet('calories_consumed', 1750);
});

it('defaults to today when no date parameter is given', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->assertSet('date', Carbon::today()->toDateString());
});

it('clamps future dates to today', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(DailyEntry::class, ['date' => Carbon::tomorrow()->toDateString()])
        ->assertSet('date', Carbon::today()->toDateString());
});

it('navigates to the previous day', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->assertSet('date', Carbon::today()->toDateString())
        ->call('previousDay')
        ->assertSet('date', Carbon::yesterday()->toDateString());
});

it('does not navigate past today when calling nextDay', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->assertSet('date', Carbon::today()->toDateString())
        ->call('nextDay')
        ->assertSet('date', Carbon::today()->toDateString());
});

it('loads the correct entry when navigating to a previous day', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    $yesterday = Carbon::yesterday();
    CalorieEntry::factory()->for($user)->create([
        'date' => $yesterday,
        'calories_consumed' => 1400,
    ]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->call('previousDay')
        ->assertSet('calories_consumed', 1400);
});

it('clears the form when navigating to a day with no entry', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'calories_consumed' => 2000,
    ]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->assertSet('calories_consumed', 2000)
        ->call('previousDay')
        ->assertSet('calories_consumed', null);
});
