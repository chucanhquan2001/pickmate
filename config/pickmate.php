<?php

return [
    'owner_email' => env('PICKMATE_OWNER_EMAIL'),

    'club' => [
        'name' => env('PICKMATE_CLUB_NAME', 'PickMate Club'),
        'timezone' => env('PICKMATE_CLUB_TIMEZONE', 'Asia/Ho_Chi_Minh'),
        'language' => env('PICKMATE_CLUB_LANGUAGE', 'vi'),
    ],
];
