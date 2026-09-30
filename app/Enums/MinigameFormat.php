<?php

namespace App\Enums;

enum MinigameFormat: string
{
    case SingleMale = 'single_male';
    case SingleFemale = 'single_female';
    case DoubleMale = 'double_male';
    case DoubleFemale = 'double_female';
    case DoubleMixed = 'double_mixed';

    public function playersPerTeam(): int
    {
        return match ($this) {
            self::SingleMale, self::SingleFemale => 1,
            self::DoubleMale, self::DoubleFemale, self::DoubleMixed => 2,
        };
    }

    public function allows(MemberGender $gender): bool
    {
        return match ($this) {
            self::SingleMale, self::DoubleMale => $gender === MemberGender::Male,
            self::SingleFemale, self::DoubleFemale => $gender === MemberGender::Female,
            self::DoubleMixed => true,
        };
    }
}
