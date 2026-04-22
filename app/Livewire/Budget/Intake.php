<?php

namespace App\Livewire\Budget;

use App\Models\IntakeResponse;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Client Intake')]
class Intake extends Component
{
    // Goal
    public string $main_goal = '';

    public string $main_goal_other = '';

    public string $why_now = '';

    // Current state
    // Lifestyle
    public string $work_schedule = 'nine_to_five';

    public string $daily_steps = 'moderate';

    public string $sleep_hours = 'seven_to_eight';

    public string $stress_level = 'moderate';

    // Nutrition
    public string $tracks_currently = 'no';

    public string $typical_day_of_eating = '';

    public string $dietary_restrictions = '';

    // Nutrition
    public string $dietary_preference = 'none';

    public string $dietary_preference_other = '';

    // Expectations
    public string $workout_days_per_week = 'three_four';

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

    public function mainGoalOptions(): array
    {
        return [
            'fat_loss' => 'Fat loss',
            'consistency' => 'Consistency',
            'energy' => 'More energy',
            'strength' => 'Build strength',
            'other' => 'Other',
        ];
    }

    public function workScheduleOptions(): array
    {
        return [
            'nine_to_five' => 'Traditional 9–5',
            'shift_work' => 'Shift work / irregular hours',
            'remote' => 'Remote / work from home',
            'stay_at_home' => 'Stay-at-home',
            'other' => 'Other',
        ];
    }

    public function dailyStepsOptions(): array
    {
        return [
            'low' => 'Low (under 5,000 steps)',
            'moderate' => 'Moderate (5,000–10,000 steps)',
            'high' => 'High (10,000+ steps)',
        ];
    }

    public function sleepHoursOptions(): array
    {
        return [
            'under_six' => 'Under 6 hours',
            'six_to_seven' => '6–7 hours',
            'seven_to_eight' => '7–8 hours',
            'eight_plus' => '8+ hours',
        ];
    }

    public function stressLevelOptions(): array
    {
        return [
            'low' => 'Low',
            'moderate' => 'Moderate',
            'high' => 'High',
            'very_high' => 'Very high',
        ];
    }

    public function tracksCurrentlyOptions(): array
    {
        return [
            'yes' => 'Yes, I track consistently',
            'loosely' => 'Loosely / occasionally',
            'no' => 'No, I don\'t track',
        ];
    }

    public function workoutDaysOptions(): array
    {
        return [
            'one_two' => '1–2 days',
            'three_four' => '3–4 days',
            'five_six' => '5–6 days',
            'every_day' => 'Every day',
        ];
    }

    public function openToTrackingOptions(): array
    {
        return [
            'yes_comfortable' => 'Yes — I\'m comfortable tracking',
            'open_to_trying' => 'Open to trying',
            'simpler_approach' => 'Prefer a simpler approach',
        ];
    }

    public function dietaryPreferenceOptions(): array
    {
        return [
            'none' => 'None',
            'vegetarian' => 'Vegetarian',
            'vegan' => 'Vegan',
            'pescatarian' => 'Pescatarian',
            'paleo' => 'Paleo',
            'keto' => 'Keto',
            'carnivore' => 'Carnivore',
            'other' => 'Other',
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'main_goal' => ['required', 'string', 'in:fat_loss,consistency,energy,strength,other'],
            'main_goal_other' => [$this->main_goal === 'other' ? 'required' : 'nullable', 'string', 'max:200'],
            'why_now' => ['nullable', 'string', 'max:1000'],
            'work_schedule' => ['required', 'string', 'in:nine_to_five,shift_work,remote,stay_at_home,other'],
            'daily_steps' => ['required', 'string', 'in:low,moderate,high'],
            'sleep_hours' => ['required', 'string', 'in:under_six,six_to_seven,seven_to_eight,eight_plus'],
            'stress_level' => ['required', 'string', 'in:low,moderate,high,very_high'],
            'tracks_currently' => ['required', 'string', 'in:yes,no,loosely'],
            'typical_day_of_eating' => ['nullable', 'string', 'max:2000'],
            'dietary_restrictions' => ['nullable', 'string', 'max:500'],
            'dietary_preference' => ['nullable', 'string', 'in:none,vegetarian,vegan,pescatarian,paleo,keto,carnivore,other'],
            'dietary_preference_other' => [$this->dietary_preference === 'other' ? 'required' : 'nullable', 'string', 'max:200'],
            'workout_days_per_week' => ['required', 'string', 'in:one_two,three_four,five_six,every_day'],
            'open_to_tracking' => ['required', 'string', 'in:yes_comfortable,open_to_trying,simpler_approach'],
            'past_consistency_struggles' => ['nullable', 'string', 'max:2000'],
        ]);

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
