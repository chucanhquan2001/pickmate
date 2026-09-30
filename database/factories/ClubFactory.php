<?php

namespace Database\Factories;

use App\Enums\ClubStatus;
use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
{
    protected $model = Club::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'logo' => null,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'language' => 'vi',
            'status' => ClubStatus::Active,
            'default_score' => 11,
            'default_best_of' => 1,
            'default_participation_points' => 1,
            'default_win_points' => 3,
            'default_loss_points' => 0,
            'default_clean_win_bonus' => 1,
        ];
    }
}
