<?php

namespace App\Livewire\Budget;

use App\Concerns\IntakeLabelOptions;
use App\Models\IntakeResponse;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Client Intake')]
class Intake extends Component
{
    use IntakeLabelOptions;

    // Goal
    public string $main_goal = '';

    public string $main_goal_other = '';

    public string $why_now = '';

    // Current state
    // Lifestyle
    public string $work_schedule = 'nine_to_five';

    public string $work_schedule_other = '';

    public string $open_to_tracking_steps = 'yes';

    public string $daily_steps = 'moderate';

    public string $sleep_hours = 'seven_to_eight';

    public string $stress_level = 'moderate';

    // Workout / Fitness
    public array $fitness_access = [];

    public string $current_activity = '';

    public array $workout_preferences = [];

    public string $workout_preferences_other = '';

    public string $has_injuries = 'no';

    public string $injury_description = '';

    // Nutrition
    public string $tracks_currently = 'no';

    public string $meal_timing_pattern = '';

    public string $meal_timing_pattern_other = '';

    public string $typical_day_of_eating = '';

    public string $dietary_restrictions = '';

    // Nutrition
    public string $dietary_preference = 'none';

    public string $dietary_preference_other = '';

    // Expectations
    public string $workout_days_per_week = 'two_three';

    public string $open_to_tracking = 'yes_comfortable';

    public string $past_consistency_struggles = '';

    public function mount(): void
    {
        if (! auth()->user()->isClient()) {
            $this->redirectRoute('dashboard', navigate: true);
        }

        if (auth()->user()->hasCompletedIntake()) {
            $this->redirectRoute('budget.setup', navigate: true);
        }
    }

    public function hasInjuriesOptions(): array
    {
        return [
            'no' => 'No',
            'yes' => 'Yes',
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'main_goal' => ['required', 'string', 'in:fat_loss,build_muscle,overall_health,energy,maintain,other'],
            'main_goal_other' => [$this->main_goal === 'other' ? 'required' : 'nullable', 'string', 'max:200'],
            'why_now' => ['nullable', 'string', 'max:1000'],
            'work_schedule' => ['required', 'string', 'in:nine_to_five,shift_work,remote,stay_at_home,other'],
            'work_schedule_other' => [$this->work_schedule === 'other' ? 'required' : 'nullable', 'string', 'max:200'],
            'open_to_tracking_steps' => ['required', 'string', 'in:yes,open_to_it,prefer_not'],
            'daily_steps' => [$this->open_to_tracking_steps !== 'prefer_not' ? 'required' : 'nullable', 'string', 'in:not_sure,low,moderate,high,very_high'],
            'sleep_hours' => ['required', 'string', 'in:under_six,six_to_seven,seven_to_eight,eight_plus'],
            'stress_level' => ['required', 'string', 'in:low,moderate,high,very_high'],
            'fitness_access' => ['nullable', 'array'],
            'fitness_access.*' => ['string', 'in:gym,home_equipment,classes_studio,none'],
            'current_activity' => ['nullable', 'string', 'max:500'],
            'workout_preferences' => ['nullable', 'array'],
            'workout_preferences.*' => ['string', 'in:strength_training,classes,walking_cardio,flexibility_mobility,at_home,open_to_anything,other'],
            'workout_preferences_other' => [in_array('other', $this->workout_preferences) ? 'required' : 'nullable', 'string', 'max:200'],
            'has_injuries' => ['required', 'string', 'in:no,yes'],
            'injury_description' => [$this->has_injuries === 'yes' ? 'required' : 'nullable', 'string', 'max:500'],
            'tracks_currently' => ['required', 'string', 'in:yes,no,loosely'],
            'meal_timing_pattern' => ['nullable', 'string', 'in:no_pattern,skip_breakfast,intermittent_fasting,consistent_times,other'],
            'meal_timing_pattern_other' => [$this->meal_timing_pattern === 'other' ? 'required' : 'nullable', 'string', 'max:200'],
            'typical_day_of_eating' => ['nullable', 'string', 'max:2000'],
            'dietary_restrictions' => ['nullable', 'string', 'max:500'],
            'dietary_preference' => ['nullable', 'string', 'in:none,vegetarian,vegan,pescatarian,paleo,keto,carnivore,other'],
            'dietary_preference_other' => [$this->dietary_preference === 'other' ? 'required' : 'nullable', 'string', 'max:200'],
            'workout_days_per_week' => ['required', 'string', 'in:one_two,two_three,three_four,four_five'],
            'open_to_tracking' => ['required', 'string', 'in:yes_comfortable,open_to_trying,tried_struggled,simpler_approach'],
            'past_consistency_struggles' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($this->open_to_tracking_steps === 'prefer_not') {
            $validated['daily_steps'] = null;
        }

        if (($validated['meal_timing_pattern'] ?? '') === '') {
            $validated['meal_timing_pattern'] = null;
        }

        $user = auth()->user();

        IntakeResponse::updateOrCreate(
            ['user_id' => $user->id],
            $validated,
        );

        $user->update(['intake_completed_at' => now()]);

        $this->redirectRoute('budget.setup', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.budget.intake');
    }
}
