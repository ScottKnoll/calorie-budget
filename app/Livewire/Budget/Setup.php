<?php

namespace App\Livewire\Budget;

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\FormulaType;
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

    public string $formula = 'standard';

    public ?float $body_fat_pct = null;

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
            $this->formula = $profile->formula->value;
            $this->body_fat_pct = $profile->body_fat_pct;
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
        if (! $this->weight_lbs) {
            return 0;
        }

        $formula = FormulaType::from($this->formula);

        if ($formula === FormulaType::LeanMass && ! $this->body_fat_pct) {
            return 0;
        }

        if ($formula === FormulaType::Standard && (! $this->age || ! $this->height_feet)) {
            return 0;
        }

        return TdeeCalculator::calculate(
            Gender::from($this->gender),
            $this->age ?? 0,
            $this->height_feet ?? 0,
            $this->height_inches,
            $this->weight_lbs,
            ActivityFactor::from($this->activity_factor),
            ExerciseFactor::from($this->exercise_factor),
            $formula,
            $this->body_fat_pct,
        );
    }

    #[Computed]
    public function computedDailyTarget(): int
    {
        return TdeeCalculator::dailyTarget($this->computedTdee, $this->goal, $this->calorie_deficit_pct);
    }

    #[Computed]
    public function computedBmr(): int
    {
        if (! $this->weight_lbs) {
            return 0;
        }

        $formula = FormulaType::from($this->formula);

        if ($formula === FormulaType::LeanMass && ! $this->body_fat_pct) {
            return 0;
        }

        if ($formula === FormulaType::Standard && (! $this->age || ! $this->height_feet)) {
            return 0;
        }

        return TdeeCalculator::bmr(
            Gender::from($this->gender),
            $this->age ?? 0,
            $this->height_feet ?? 0,
            $this->height_inches,
            $this->weight_lbs,
            $formula,
            $this->body_fat_pct,
        );
    }

    #[Computed]
    public function computedDaysToGoal(): ?int
    {
        if (! $this->goal_weight_lbs || ! $this->weight_lbs) {
            return null;
        }

        if (! in_array($this->goal, ['cut', 'bulk'])) {
            return null;
        }

        return TdeeCalculator::daysToGoal(
            $this->weight_lbs,
            $this->goal_weight_lbs,
            $this->computedTdee,
            $this->daily_calorie_target,
        );
    }

    #[Computed]
    public function computedTargetDate(): ?string
    {
        if ($this->computedDaysToGoal === null) {
            return null;
        }

        return now()->addDays($this->computedDaysToGoal)->format('M j, Y');
    }

    public function updated(string $property): void
    {
        $tdeeProps = [
            'gender', 'age', 'height_feet', 'height_inches',
            'weight_lbs', 'activity_factor', 'exercise_factor',
            'formula', 'body_fat_pct',
        ];

        $targetProps = ['goal', 'calorie_deficit_pct', 'goal_weight_lbs', 'daily_calorie_target'];

        if (in_array($property, $tdeeProps) || in_array($property, $targetProps)) {
            $this->syncSuggestedTarget();
        }
    }

    public function save(): void
    {
        $isLeanMass = $this->formula === FormulaType::LeanMass->value;

        $validated = $this->validate([
            'gender' => ['required', new Enum(Gender::class)],
            'age' => [$isLeanMass ? 'nullable' : 'required', 'integer', 'min:1', 'max:120'],
            'height_feet' => [$isLeanMass ? 'nullable' : 'required', 'integer', 'min:1', 'max:9'],
            'height_inches' => ['required', 'integer', 'min:0', 'max:11'],
            'weight_lbs' => ['required', 'integer', 'min:50', 'max:1500'],
            'goal_weight_lbs' => ['nullable', 'integer', 'min:50', 'max:1500'],
            'start_date' => ['nullable', 'date'],
            'calorie_deficit_pct' => ['required', 'integer', 'min:5', 'max:50'],
            'activity_factor' => ['required', new Enum(ActivityFactor::class)],
            'exercise_factor' => ['required', new Enum(ExerciseFactor::class)],
            'formula' => ['required', new Enum(FormulaType::class)],
            'body_fat_pct' => [$isLeanMass ? 'required' : 'nullable', 'numeric', 'min:1', 'max:70'],
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

    public function formulaOptions(): array
    {
        return collect(FormulaType::cases())
            ->mapWithKeys(fn (FormulaType $f) => [$f->value => $f->label()])
            ->all();
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
