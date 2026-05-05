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

    public function tracksCurrentlyOptions(): array
    {
        return [
            'yes' => 'Yes, I track consistently',
            'loosely' => 'Loosely / occasionally',
            'no' => 'No, I don\'t track',
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

    public function hasInjuriesOptions(): array
    {
        return [
            'no' => 'No',
            'yes' => 'Yes',
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
            'yes_comfortable' => 'Yes — I\'m comfortable tracking',
            'open_to_trying' => 'Open to trying',
            'tried_struggled' => 'I\'ve tried it before but struggled to stick with it',
            'simpler_approach' => 'Prefer a simpler approach',
        ];
    }

    public function mealTimingPatternOptions(): array
    {
        return [
            'no_pattern' => 'No set pattern — I eat when I\'m hungry',
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
