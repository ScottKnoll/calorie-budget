<?php

use App\Enums\MacroPreset;
use App\Livewire\Budget\Macros;
use App\Models\CalorieProfile;
use App\Models\User;
use Livewire\Livewire;

it('shows the macros page to guests', function () {
    $this->get(route('budget.macros'))->assertSuccessful();
});

it('redirects guests to the register page when they try to save macros', function () {
    Livewire::test(Macros::class)
        ->call('save')
        ->assertRedirect(route('register'));
});

it('shows the macros page to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.macros'))
        ->assertSuccessful();
});

it('mounts with default values when no profile exists', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->assertSet('macro_preset', null)
        ->assertSet('carb_pct', 50)
        ->assertSet('protein_pct', 30)
        ->assertSet('fat_pct', 20);
});

it('mounts with saved macro values from the profile', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create([
        'macro_preset' => MacroPreset::Keto->value,
        'carb_pct' => 5,
        'protein_pct' => 25,
        'fat_pct' => 70,
        'daily_calorie_target' => 2000,
    ]);

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->assertSet('macro_preset', MacroPreset::Keto->value)
        ->assertSet('carb_pct', 5)
        ->assertSet('protein_pct', 25)
        ->assertSet('fat_pct', 70);
});

it('auto-fills percentages when a preset is selected', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('macro_preset', MacroPreset::HighCarb->value)
        ->assertSet('carb_pct', 50)
        ->assertSet('protein_pct', 30)
        ->assertSet('fat_pct', 20);
});

it('auto-fills percentages for the high protein preset', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('macro_preset', MacroPreset::HighProtein->value)
        ->assertSet('carb_pct', 30)
        ->assertSet('protein_pct', 40)
        ->assertSet('fat_pct', 30);
});

it('auto-fills percentages for the keto preset', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('macro_preset', MacroPreset::Keto->value)
        ->assertSet('carb_pct', 5)
        ->assertSet('protein_pct', 25)
        ->assertSet('fat_pct', 70);
});

it('sets preset to custom when a slider is manually adjusted', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('macro_preset', MacroPreset::Balanced->value)
        ->set('carb_pct', 45)
        ->assertSet('macro_preset', MacroPreset::Custom->value);
});

it('computes macro total correctly', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Macros::class)
        ->set('carb_pct', 40)
        ->set('protein_pct', 30)
        ->set('fat_pct', 30);

    expect($component->get('macroTotal'))->toBe(100);
});

it('computes gram values from the daily calorie target', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    // High Carb preset: 50/30/20 at 2000 cal
    // Carbs: round(0.50 * 2000 / 4) = 250g
    // Protein: round(0.30 * 2000 / 4) = 150g
    // Fat: round(0.20 * 2000 / 9) = 44g
    $component = Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('macro_preset', MacroPreset::HighCarb->value);

    expect($component->get('computedCarbGrams'))->toBe(250);
    expect($component->get('computedProteinGrams'))->toBe(150);
    expect($component->get('computedFatGrams'))->toBe(44);
});

it('returns zero grams when no calorie profile exists', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Macros::class);

    expect($component->get('computedCarbGrams'))->toBe(0);
    expect($component->get('computedProteinGrams'))->toBe(0);
    expect($component->get('computedFatGrams'))->toBe(0);
});

it('saves macro settings to the calorie profile', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('macro_preset', MacroPreset::Balanced->value)
        ->call('save');

    $profile = $user->fresh()->calorieProfile;

    expect($profile)
        ->macro_preset->toBe(MacroPreset::Balanced)
        ->carb_pct->toBe(40)
        ->protein_pct->toBe(30)
        ->fat_pct->toBe(30);
});

it('does not save when no calorie profile exists yet', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('macro_preset', MacroPreset::LowCarb->value)
        ->call('save')
        ->assertHasErrors(['macro_total']);

    expect(CalorieProfile::where('user_id', $user->id)->exists())->toBeFalse();
});

it('rejects save when percentages do not sum to 100', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('carb_pct', 50)
        ->set('protein_pct', 30)
        ->set('fat_pct', 30)
        ->call('save')
        ->assertHasErrors(['macro_total']);
});

it('rejects save when percentages are under 100', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('carb_pct', 30)
        ->set('protein_pct', 30)
        ->set('fat_pct', 30)
        ->call('save')
        ->assertHasErrors(['macro_total']);
});

it('persists custom macro percentages when preset is custom', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    Livewire::actingAs($user)
        ->test(Macros::class)
        ->set('macro_preset', MacroPreset::Custom->value)
        ->set('carb_pct', 45)
        ->set('protein_pct', 35)
        ->set('fat_pct', 20)
        ->call('save');

    $profile = $user->fresh()->calorieProfile;

    expect($profile)
        ->macro_preset->toBe(MacroPreset::Custom)
        ->carb_pct->toBe(45)
        ->protein_pct->toBe(35)
        ->fat_pct->toBe(20);
});
