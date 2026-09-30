<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'avatar' => null,
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ];
    }

    public function owner(): static
    {
        return $this->state(fn () => ['role' => UserRole::Owner]);
    }

    public function member(): static
    {
        return $this->state(fn () => ['role' => UserRole::Member]);
    }
}
