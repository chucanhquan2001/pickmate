<?php

use App\Support\PickleballServe;

it('scores side-out singles from the server score and switches sides', function () {
    $state = PickleballServe::initial(1, false, 'side_out');

    expect($state['score_call'])->toBe('0-0')
        ->and($state['server_side'])->toBe('right')
        ->and($state['server_number'])->toBe(1);

    $state = PickleballServe::apply($state, 1);

    expect($state['team_1_score'])->toBe(1)
        ->and($state['team_2_score'])->toBe(0)
        ->and($state['serving_team'])->toBe(1)
        ->and($state['server_side'])->toBe('left')
        ->and($state['event'])->toBe('hold')
        ->and($state['score_call'])->toBe('1-0');

    $state = PickleballServe::apply($state, 2);

    expect($state['team_1_score'])->toBe(1)
        ->and($state['team_2_score'])->toBe(0)
        ->and($state['serving_team'])->toBe(2)
        ->and($state['server_side'])->toBe('right')
        ->and($state['event'])->toBe('side_out')
        ->and($state['score_call'])->toBe('0-1');
});

it('starts side-out doubles at second server and rotates within the team', function () {
    $state = PickleballServe::initial(1, true, 'side_out');

    expect($state['score_call'])->toBe('0-0-2')
        ->and($state['server_role'])->toBe('starting')
        ->and($state['server_side'])->toBe('right');

    $state = PickleballServe::apply($state, 1);

    expect($state['score_call'])->toBe('1-0-2')
        ->and($state['server_role'])->toBe('starting')
        ->and($state['server_side'])->toBe('left')
        ->and($state['event'])->toBe('hold');

    $state = PickleballServe::apply($state, 2);

    expect($state['team_1_score'])->toBe(1)
        ->and($state['team_2_score'])->toBe(0)
        ->and($state['serving_team'])->toBe(2)
        ->and($state['server_number'])->toBe(1)
        ->and($state['server_role'])->toBe('starting')
        ->and($state['server_side'])->toBe('right')
        ->and($state['event'])->toBe('side_out')
        ->and($state['score_call'])->toBe('0-1-1');

    $state = PickleballServe::apply($state, 2);

    expect($state['score_call'])->toBe('1-1-1')
        ->and($state['server_role'])->toBe('starting')
        ->and($state['server_side'])->toBe('left')
        ->and($state['event'])->toBe('hold');

    $state = PickleballServe::apply($state, 1);

    expect($state['team_2_score'])->toBe(1)
        ->and($state['serving_team'])->toBe(2)
        ->and($state['server_number'])->toBe(2)
        ->and($state['server_role'])->toBe('partner')
        ->and($state['server_side'])->toBe('right')
        ->and($state['event'])->toBe('second_server')
        ->and($state['score_call'])->toBe('1-1-2');

    $state = PickleballServe::apply($state, 1);

    expect($state['serving_team'])->toBe(1)
        ->and($state['server_number'])->toBe(1)
        ->and($state['server_role'])->toBe('partner')
        ->and($state['server_side'])->toBe('right')
        ->and($state['event'])->toBe('side_out')
        ->and($state['score_call'])->toBe('1-1-1');
});

it('awards rally singles points to either player and serves from the new score', function () {
    $state = PickleballServe::initial(1, false, 'rally');

    expect($state['score_call'])->toBe('0-0')
        ->and($state['server_side'])->toBe('right');

    $state = PickleballServe::apply($state, 1);

    expect($state['team_1_score'])->toBe(1)
        ->and($state['serving_team'])->toBe(1)
        ->and($state['server_side'])->toBe('left')
        ->and($state['event'])->toBe('hold');

    $state = PickleballServe::apply($state, 2);

    expect($state['team_1_score'])->toBe(1)
        ->and($state['team_2_score'])->toBe(1)
        ->and($state['serving_team'])->toBe(2)
        ->and($state['server_side'])->toBe('left')
        ->and($state['event'])->toBe('side_out')
        ->and($state['score_call'])->toBe('1-1');
});

it('uses one server in rally doubles and starts each turn from the right court', function () {
    $state = PickleballServe::initial(1, true, 'rally');

    expect($state['score_call'])->toBe('0-0')
        ->and($state['server_number'])->toBe(1)
        ->and($state['server_role'])->toBe('starting')
        ->and($state['server_side'])->toBe('right')
        ->and($state['receiver_role'])->toBe('starting');

    $state = PickleballServe::apply($state, 1);

    expect($state['score_call'])->toBe('1-0')
        ->and($state['server_role'])->toBe('starting')
        ->and($state['server_side'])->toBe('left')
        ->and($state['event'])->toBe('hold')
        ->and($state['receiver_role'])->toBe('partner');

    $state = PickleballServe::apply($state, 2);

    expect($state['team_1_score'])->toBe(1)
        ->and($state['team_2_score'])->toBe(1)
        ->and($state['serving_team'])->toBe(2)
        ->and($state['server_number'])->toBe(1)
        ->and($state['server_role'])->toBe('partner')
        ->and($state['server_side'])->toBe('right')
        ->and($state['event'])->toBe('side_out')
        ->and($state['score_call'])->toBe('1-1');

    $state = PickleballServe::apply($state, 2);

    expect($state['team_2_score'])->toBe(2)
        ->and($state['serving_team'])->toBe(2)
        ->and($state['server_role'])->toBe('partner')
        ->and($state['server_side'])->toBe('left')
        ->and($state['event'])->toBe('hold')
        ->and($state['score_call'])->toBe('2-1');
});
