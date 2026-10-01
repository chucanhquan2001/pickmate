export function initialServe(servingTeam, doubles, scoringType) {
    return present({
        team_1_score: 0,
        team_2_score: 0,
        serving_team: servingTeam,
        server_number: doubles && scoringType === 'side_out' ? 2 : 1,
        server_role: 'starting',
        doubles,
        scoring_type: scoringType,
    }, null);
}

export function applyRally(state, winnerTeam) {
    const next = {
        team_1_score: state.team_1_score,
        team_2_score: state.team_2_score,
        serving_team: state.serving_team,
        server_number: state.server_number,
        server_role: state.server_role,
        doubles: state.doubles,
        scoring_type: state.scoring_type,
    };

    const serving = next.serving_team;
    let event = 'hold';

    if (next.scoring_type === 'side_out') {
        if (winnerTeam === serving) {
            addPoint(next, serving);
        } else if (next.doubles && next.server_number === 1) {
            next.server_number = 2;
            next.server_role = next.server_role === 'starting' ? 'partner' : 'starting';
            event = 'second_server';
        } else {
            next.serving_team = serving === 1 ? 2 : 1;
            next.server_number = 1;
            next.server_role = next.doubles ? roleOnRight(next) : 'starting';
            event = 'side_out';
        }
    } else {
        addPoint(next, winnerTeam);

        if (winnerTeam !== serving) {
            next.serving_team = winnerTeam;
            next.server_number = 1;
            next.server_role = next.doubles ? roleOnRight(next) : 'starting';
            event = 'side_out';
        }
    }

    return present(next, event);
}

function addPoint(state, team) {
    if (team === 1) {
        state.team_1_score += 1;
    } else {
        state.team_2_score += 1;
    }
}

function roleOnRight(state) {
    const score = state.serving_team === 1 ? state.team_1_score : state.team_2_score;

    return score % 2 === 0 ? 'starting' : 'partner';
}

function present(state, event) {
    const serving = state.serving_team;
    const serverScore = serving === 1 ? state.team_1_score : state.team_2_score;
    const receiverScore = serving === 1 ? state.team_2_score : state.team_1_score;
    const serverSide = side(state.server_role, serverScore);
    const receivingTeam = serving === 1 ? 2 : 1;
    const receiverScoreForSide = receivingTeam === 1 ? state.team_1_score : state.team_2_score;
    const receiverRole = state.doubles
        ? roleOnSide(receiverScoreForSide, serverSide)
        : 'starting';
    const scoreCall = state.doubles && state.scoring_type === 'side_out'
        ? `${serverScore}-${receiverScore}-${state.server_number}`
        : `${serverScore}-${receiverScore}`;

    return {
        ...state,
        server_side: serverSide,
        receiver_team: receivingTeam,
        receiver_role: receiverRole,
        receiver_side: serverSide,
        score_call: scoreCall,
        event,
    };
}

function side(role, score) {
    const startingOnRight = score % 2 === 0;

    if (role === 'starting') {
        return startingOnRight ? 'right' : 'left';
    }

    return startingOnRight ? 'left' : 'right';
}

function roleOnSide(score, courtSide) {
    const startingSide = score % 2 === 0 ? 'right' : 'left';

    return startingSide === courtSide ? 'starting' : 'partner';
}
