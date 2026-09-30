<?php

namespace Database\Seeders;

use App\Enums\ClubStatus;
use App\Models\Club;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) config('pickmate.club.name');
        $slug = Str::slug($name) ?: 'pickmate';

        Club::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'logo' => null,
                'timezone' => config('pickmate.club.timezone'),
                'language' => config('pickmate.club.language'),
                'status' => ClubStatus::Active,
                'default_score' => 11,
                'default_best_of' => 1,
                'default_participation_points' => 1,
                'default_win_points' => 3,
                'default_loss_points' => 0,
                'default_clean_win_bonus' => 1,
            ],
        );
    }
}
