<?php

namespace Database\Factories;

use App\Enums\CourtStatus;
use App\Models\Club;
use App\Models\Court;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
{
    protected $model = Court::class;

    public function definition(): array
    {
        $code = 'C'.fake()->unique()->numerify('##');

        return [
            'club_id' => Club::factory(),
            'name' => 'Court '.$code,
            'code' => $code,
            'status' => CourtStatus::Active,
            'note' => null,
        ];
    }
}
