<?php

namespace Database\Factories;

use App\Enums\MinigameFormat;
use App\Enums\MinigameStatus;
use App\Models\Club;
use App\Models\Minigame;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Minigame>
 */
class MinigameFactory extends Factory
{
    protected $model = Minigame::class;

    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'name' => fake()->words(3, true),
            'description' => null,
            'format' => MinigameFormat::DoubleMale,
            'status' => MinigameStatus::Draft,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'default_score' => 11,
            'best_of' => 1,
            'participation_points' => 1,
            'win_points' => 3,
            'loss_points' => 0,
            'clean_win_bonus' => 1,
            'created_by' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => MinigameStatus::Active]);
    }
}
