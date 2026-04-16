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

    public string $why_now = '';

    // Current state
    public ?int $current_weight_lbs = null;

    public ?int $current_height_feet = null;

    public int $current_height_inches = 0;

    public string $activity_level = 'sedentary';

    public string $workout_experience = 'beginner';

    // Lifestyle
    public string $work_schedule = 'nine_to_five';

    public string $daily_steps = 'moderate';

    public string $sleep_hours = 'seven_to_eight';

    public string $stress_level = 'moderate';

    // Nutrition
    public string $tracks_currently = 'no';

    public string $typical_day_of_eating = '';

    public string $dietary_restrictions = '';

    // Expectations
    public string $workout_days_per_week = 'three_four';

    public string $open_to_tracking = 'loosely';

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

    public function activityLevelOptions(): array
    {
        return [
            'sedentary' => 'Sedentary (desk job, little movement)',
            'lightly_active' => 'Lightly active (some walking, light daily movement)',
            'moderately_active' => 'Moderately active (on your feet most of the day)',
            'very_active' => 'Very active (physically demanding job)',
            'extra_active' => 'Extra active (extremely demanding physical job)',
        ];
    }

    public function workoutExperienceOptions(): array
    {
        return [
            'beginner' => 'Beginner (just starting out)',
            'intermediate' => 'Intermediate (1–3 years consistent training)',
            'advanced' => 'Advanced (3+ years, structured programming)',
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
            'yes' => 'Yes, fully committed',
            'loosely' => 'Loosely — I\'ll try',
            'no' => 'No, prefer not to',
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'main_goal' => ['required', 'string', 'in:fat_loss,consistency,energy,strength,other'],
            'why_now' => ['nullable', 'string', 'max:1000'],
            'current_weight_lbs' => ['nullable', 'integer', 'min:50', 'max:1500'],
            'current_height_feet' => ['nullable', 'integer', 'min:1', 'max:9'],
            'current_height_inches' => ['nullable', 'integer', 'min:0', 'max:11'],
            'activity_level' => ['required', 'string', 'in:sedentary,lightly_active,moderately_active,very_active,extra_active'],
            'workout_experience' => ['required', 'string', 'in:beginner,intermediate,advanced'],
            'work_schedule' => ['required', 'string', 'in:nine_to_five,shift_work,remote,stay_at_home,other'],
            'daily_steps' => ['required', 'string', 'in:low,moderate,high'],
            'sleep_hours' => ['required', 'string', 'in:under_six,six_to_seven,seven_to_eight,eight_plus'],
            'stress_level' => ['required', 'string', 'in:low,moderate,high,very_high'],
            'tracks_currently' => ['required', 'string', 'in:yes,no,loosely'],
            'typical_day_of_eating' => ['nullable', 'string', 'max:2000'],
            'dietary_restrictions' => ['nullable', 'string', 'max:500'],
            'workout_days_per_week' => ['required', 'string', 'in:one_two,three_four,five_six,every_day'],
            'open_to_tracking' => ['required', 'string', 'in:yes,no,loosely'],
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
