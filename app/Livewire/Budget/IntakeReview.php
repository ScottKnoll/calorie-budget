<?php

namespace App\Livewire\Budget;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Intake Review')]
class IntakeReview extends Component
{
    public function mount(): void
    {
        if (auth()->user()->isClient()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::with('intakeResponse')
            ->whereNotNull('intake_completed_at')
            ->orderBy('name')
            ->get();
    }

    public function mainGoalOptions(): array
    {
        return [
            'fat_loss' => 'Lose fat / lean out',
            'build_muscle' => 'Build muscle / get stronger',
            'overall_health' => 'Improve overall health & fitness',
            'energy' => 'Increase energy & feel better day-to-day',
            'maintain' => 'Maintain weight / stay on track',
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

    public function openToTrackingStepsOptions(): array
    {
        return [
            'yes' => 'Yes, I already track my steps',
            'open_to_it' => 'Open to it / just starting out',
            'prefer_not' => "I'd prefer not to",
        ];
    }

    public function dailyStepsOptions(): array
    {
        return [
            'not_sure' => 'Not sure / rough estimate',
            'low' => 'Low (under 5,000)',
            'moderate' => 'Moderate (5,000–8,000)',
            'high' => 'High (8,000–12,000)',
            'very_high' => 'Very high (12,000+)',
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

    public function fitnessAccessOptions(): array
    {
        return [
            'gym' => 'Gym',
            'home_equipment' => 'Home equipment (dumbbells, bands, etc.)',
            'classes_studio' => 'Classes/studio',
            'none' => 'None',
        ];
    }

    public function workoutPreferenceOptions(): array
    {
        return [
            'strength_training' => 'Strength training',
            'classes' => 'Classes (yoga, pilates, spin, etc.)',
            'walking_cardio' => 'Walking / cardio',
            'flexibility_mobility' => 'Flexibility / mobility',
            'at_home' => 'At-home workouts',
            'open_to_anything' => 'Open to anything',
            'other' => 'Other',
        ];
    }

    public function tracksCurrentlyOptions(): array
    {
        return [
            'yes' => 'Yes, I track consistently',
            'loosely' => 'Loosely / occasionally',
            'no' => "No, I don't track",
        ];
    }

    public function mealTimingPatternOptions(): array
    {
        return [
            'no_pattern' => "No set pattern — I eat when I'm hungry",
            'skip_breakfast' => 'I tend to skip breakfast / eat later in the day',
            'intermittent_fasting' => 'Yes, I do intermittent fasting (e.g. 16:8, 18:6)',
            'consistent_times' => 'I try to eat at consistent times each day',
            'other' => 'Other',
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

    public function workoutDaysOptions(): array
    {
        return [
            'one_two' => '1–2 days',
            'two_three' => '2–3 days',
            'three_four' => '3–4 days',
            'four_five' => '4–5 days',
        ];
    }

    public function openToTrackingOptions(): array
    {
        return [
            'yes_comfortable' => "Yes — I'm comfortable tracking",
            'open_to_trying' => 'Open to trying',
            'tried_struggled' => "I've tried it before but struggled to stick with it",
            'simpler_approach' => 'Prefer a simpler approach',
        ];
    }

    public function render(): View
    {
        return view('livewire.budget.intake-review');
    }
}
