<?php

use App\Support\LocalDateTime;

it('stores a local datetime as utc using the club timezone', function () {
    expect(LocalDateTime::toUtc('2026-10-02T08:03', 'Asia/Ho_Chi_Minh'))
        ->toBe('2026-10-02 01:03:00');
});

it('keeps an explicit timezone offset when parsing scheduled times', function () {
    expect(LocalDateTime::toUtc('2026-10-02T08:03:00+00:00', 'Asia/Ho_Chi_Minh'))
        ->toBe('2026-10-02 08:03:00');
});
