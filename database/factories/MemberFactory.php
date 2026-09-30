<?php

namespace Database\Factories;

use App\Enums\MemberGender;
use App\Enums\MemberLevel;
use App\Enums\MemberStatus;
use App\Models\Club;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'nickname' => fake()->optional()->firstName(),
            'avatar' => null,
            'gender' => MemberGender::Male,
            'birthday' => fake()->optional()->date(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => null,
            'level' => MemberLevel::Beginner,
            'joined_at' => now()->toDateString(),
            'status' => MemberStatus::Active,
        ];
    }

    public function female(): static
    {
        return $this->state(fn () => ['gender' => MemberGender::Female]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => MemberStatus::Inactive]);
    }
}
