<?php

namespace App\Livewire\Budget;

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Models\CalorieProfile;
use App\Services\TdeeCalculator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Calorie Setup')]
class Setup extends Component
{
    public string $gender = 'male';

    public ?int $age = null;

    public ?int $height_feet = null;

    public int $height_inches = 0;

    public ?int $weight_lbs = null;

    public ?int $goal_weight_lbs = null;

    public ?string $start_date = null;

    public int $calorie_deficit_pct = 20;

    public string $activity_factor = 'sedentary';

    public string $exercise_factor = 'none';

    public string $goal = 'maintain';

    public int $suggestedDailyTarget = 0;

    public int $daily_calorie_target = 0;

    public function mount(): void
    {
        $profile = auth()->user()->calorieProfile;

        if ($profile) {
            $this->gender = $profile->gender->value;
            $this->age = $profile->age;
            $this->height_feet = $profile->height_feet;
            $this->height_inches = $profile->height_inches;
            $this->weight_lbs = $profile->weight_lbs;
            $this->goal_weight_lbs = $profile->goal_weight_lbs;
            $this->start_date = $profile->start_date?->toDateString();
            $this->calorie_deficit_pct = $profile->calorie_deficit_pct;
            $this->activity_factor = $profile->activity_factor->value;
            $this->exercise_factor = $profile->exercise_factor->value;
            $this->goal = $profile->goal->value;
            $this->daily_calorie_target = $profile->daily_calorie_target;
        } else {
            $this->daily_calorie_target = 0;
        }

        $this->suggestedDailyTarget = TdeeCalculator::dailyTarget(
            $this->computedTdee,
            $this->goal,
            $this->calorie_deficit_pct,
        );
    }

    #[Computed]
    public function computedTdee(): int
    {
        if (! $this->age || ! $this->weight_lbs || ! $this->height_feet) {
            return 0;
        }

        return TdeeCalculator::calculate(
            Gender::from($this->gender),
            $this->age,
            $this->height_feet,
            $this->height_inches,
            $this->weight_lbs,
            ActivityFactor::from($this->activity_factor),
            ExerciseFactor::from($this->exercise_factor),
        );
    }

    #[Computed]
    public function computedDailyTarget(): int
    {
        return TdeeCalculator::dailyTarget($this->computedTdee, $this->goal, $this->calorie_deficit_pct);
    }

    public function updated(string $property): void
    {
        $tdeeProps = [
            'gender', 'age', 'height_feet', 'height_inches',
            'weight_lbs', 'activity_factor', 'exercise_factor',
        ];

        $targetProps = ['goal', 'calorie_deficit_pct'];

        if (in_array($property, $tdeeProps) || in_array($property, $targetProps)) {
            $this->syncSuggestedTarget();
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'gender' => ['required', new Enum(Gender::class)],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'height_feet' => ['required', 'integer', 'min:1', 'max:9'],
            'height_inches' => ['required', 'integer', 'min:0', 'max:11'],
            'weight_lbs' => ['required', 'integer', 'min:50', 'max:1500'],
            'goal_weight_lbs' => ['nullable', 'integer', 'min:50', 'max:1500'],
            'start_date' => ['nullable', 'date'],
            'calorie_deficit_pct' => ['required', 'integer', 'min:5', 'max:50'],
            'activity_factor' => ['required', new Enum(ActivityFactor::class)],
            'exercise_factor' => ['required', new Enum(ExerciseFactor::class)],
            'goal' => ['required', new Enum(Goal::class)],
            'daily_calorie_target' => ['required', 'integer', 'min:500', 'max:9999'],
        ]);

        $tdee = $this->computedTdee;

        CalorieProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            array_merge($validated, ['tdee' => $tdee]),
        );

        session()->flash('status', 'saved');
    }

    public function genderOptions(): array
    {
        return collect(Gender::cases())
            ->mapWithKeys(fn (Gender $g) => [$g->value => $g->label()])
            ->all();
    }

    public function goalOptions(): array
    {
        return collect(Goal::cases())
            ->mapWithKeys(fn (Goal $goal) => [$goal->value => $goal->label()])
            ->all();
    }

    public function activityFactorOptions(): array
    {
        return collect(ActivityFactor::cases())
            ->mapWithKeys(fn (ActivityFactor $af) => [$af->value => $af->label()])
            ->all();
    }

    public function exerciseFactorOptions(): array
    {
        return collect(ExerciseFactor::cases())
            ->mapWithKeys(fn (ExerciseFactor $ef) => [$ef->value => $ef->label()])
            ->all();
    }

    private function syncSuggestedTarget(): void
    {
        $suggested = TdeeCalculator::dailyTarget(
            $this->computedTdee,
            $this->goal,
            $this->calorie_deficit_pct,
        );

        if ($this->daily_calorie_target === $this->suggestedDailyTarget) {
            $this->daily_calorie_target = $suggested;
        }

        $this->suggestedDailyTarget = $suggested;
    }

    public function render(): View
    {
        return view('livewire.budget.setup');
    }
}
