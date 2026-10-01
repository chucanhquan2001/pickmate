<script setup>
import { reactive } from 'vue';
import ScoreStepper from './ScoreStepper.vue';
import { teamLabel } from '../labels';
import { applyRally, initialServe } from '../pickleballServe';

const props = defineProps({
    team1: { type: Array, required: true },
    team2: { type: Array, required: true },
    doubles: { type: Boolean, required: true },
    scoringType: { type: String, required: true },
    targetScore: { type: Number, default: null },
    title: { type: String, default: '' },
});

const team1Score = defineModel('team1Score', { type: Number, required: true });
const team2Score = defineModel('team2Score', { type: Number, required: true });

const board = reactive({
    mode: 'manual',
    locked: false,
    rally: null,
    history: [],
    serving_team: 1,
    starting_1: props.team1[0]?.member_id ?? null,
    starting_2: props.team2[0]?.member_id ?? null,
    last_event: null,
});

function useManual() {
    if (board.locked || board.mode === 'manual') {
        return;
    }

    board.mode = 'manual';
    board.rally = null;
    board.history = [];
    board.last_event = null;
}

function beginRally() {
    if (board.locked || board.mode === 'rally') {
        return;
    }

    board.mode = 'rally';
    board.history = [];
    board.last_event = null;
    board.serving_team = 1;
    team1Score.value = 0;
    team2Score.value = 0;
    board.rally = initialServe(1, props.doubles, props.scoringType);
}

function chooseServer(team) {
    if (board.locked || board.mode !== 'rally' || board.history.length > 0) {
        return;
    }

    board.serving_team = team;
    board.rally = initialServe(team, props.doubles, props.scoringType);
}

function point(winner) {
    if (board.locked || board.mode !== 'rally' || !board.rally) {
        return;
    }

    board.history.push(board.rally);
    const next = applyRally(board.rally, winner);
    board.rally = next;
    team1Score.value = next.team_1_score;
    team2Score.value = next.team_2_score;
    board.last_event = next.event;
}

function undo() {
    const previous = board.history.pop();

    if (!previous) {
        return;
    }

    board.rally = previous;
    team1Score.value = previous.team_1_score;
    team2Score.value = previous.team_2_score;
    board.last_event = previous.event;
}

function decisive() {
    return props.targetScore !== null
        && team1Score.value !== team2Score.value
        && Math.max(team1Score.value, team2Score.value) >= props.targetScore;
}

function lockSet() {
    if (decisive()) {
        board.locked = true;
    }
}

function playersOf(team) {
    return team === 1 ? props.team1 : props.team2;
}

function playerName(team, role) {
    const players = playersOf(team) || [];

    if (players.length === 0) {
        return '';
    }

    const startingId = team === 1 ? board.starting_1 : board.starting_2;
    const starting = players.find((player) => player.member_id === startingId) || players[0];

    if (!props.doubles || role === 'starting') {
        return starting.name;
    }

    return players.find((player) => player.member_id !== starting.member_id)?.name || '';
}

function sideLabel(side) {
    return side === 'left' ? 'Tay trái' : 'Tay phải';
}

function eventLabel(event) {
    if (event === 'hold') {
        return 'Giữ giao';
    }

    if (event === 'second_server') {
        return 'Sang giao 2';
    }

    if (event === 'side_out') {
        return 'Đổi giao';
    }

    return '';
}

function calledScore(scoreCall) {
    return String(scoreCall || '').split('-').join(' – ');
}
</script>

<template>
    <section class="rounded-3xl bg-white p-4">
        <p v-if="title" class="mb-3 text-center text-sm font-semibold text-stone-500">{{ title }}</p>
        <div class="grid grid-cols-2 gap-2">
            <button type="button" class="min-h-11 rounded-2xl border px-3 py-2 text-sm font-semibold" :class="board.mode === 'manual' ? 'border-teal-700 bg-teal-700 text-white' : 'border-stone-300 bg-white'" :disabled="board.locked" @click="useManual">Nhập tay</button>
            <button type="button" class="min-h-11 rounded-2xl border px-3 py-2 text-sm font-semibold" :class="board.mode === 'rally' ? 'border-teal-700 bg-teal-700 text-white' : 'border-stone-300 bg-white'" :disabled="board.locked" @click="beginRally">Chấm theo pha</button>
        </div>

        <div v-if="board.mode === 'rally' && board.rally" class="mt-4 space-y-3">
            <div v-if="board.history.length === 0" class="space-y-3">
                <div>
                    <p class="text-sm font-semibold">Đội giao trước</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <button type="button" class="min-h-11 rounded-2xl border px-3 py-2 text-sm font-semibold" :class="board.serving_team === 1 ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-stone-300'" @click="chooseServer(1)">{{ teamLabel(team1) }}</button>
                        <button type="button" class="min-h-11 rounded-2xl border px-3 py-2 text-sm font-semibold" :class="board.serving_team === 2 ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-stone-300'" @click="chooseServer(2)">{{ teamLabel(team2) }}</button>
                    </div>
                </div>
                <div v-if="doubles">
                    <p class="text-sm font-semibold">Người giao gốc</p>
                    <p class="mt-1 text-xs text-stone-500">Đứng tay phải khi điểm đội chẵn.</p>
                    <div class="mt-2 space-y-2">
                        <div>
                            <p class="text-xs font-semibold text-stone-500">{{ teamLabel(team1) }}</p>
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <button v-for="player in team1" :key="player.member_id" type="button" class="min-h-11 rounded-2xl border px-3 py-2 text-sm font-semibold" :class="board.starting_1 === player.member_id ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-stone-300'" @click="board.starting_1 = player.member_id">{{ player.name }}</button>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-500">{{ teamLabel(team2) }}</p>
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <button v-for="player in team2" :key="player.member_id" type="button" class="min-h-11 rounded-2xl border px-3 py-2 text-sm font-semibold" :class="board.starting_2 === player.member_id ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-stone-300'" @click="board.starting_2 = player.member_id">{{ player.name }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-stone-50 p-3 text-sm">
                <p class="text-center text-2xl font-semibold tabular-nums">{{ calledScore(board.rally.score_call) }}</p>
                <p class="mt-2">Người phát: <span class="font-semibold">{{ playerName(board.rally.serving_team, board.rally.server_role) }}</span></p>
                <p>Tay phát: <span class="font-semibold">{{ sideLabel(board.rally.server_side) }}</span><span v-if="doubles && scoringType === 'side_out'"> · Giao {{ board.rally.server_number }}</span></p>
                <p>Người đỡ: <span class="font-semibold">{{ playerName(board.rally.receiver_team, board.rally.receiver_role) }}</span> · {{ sideLabel(board.rally.receiver_side) }}</p>
                <p v-if="eventLabel(board.last_event)" class="mt-2 font-semibold text-teal-700">{{ eventLabel(board.last_event) }}</p>
            </div>

            <div v-if="!board.locked" class="grid grid-cols-2 gap-2">
                <button type="button" class="min-h-14 rounded-2xl bg-teal-700 px-3 py-2 text-sm font-semibold text-white" @click="point(1)">{{ teamLabel(team1) }} thắng pha</button>
                <button type="button" class="min-h-14 rounded-2xl bg-stone-900 px-3 py-2 text-sm font-semibold text-white" @click="point(2)">{{ teamLabel(team2) }} thắng pha</button>
            </div>
            <button v-if="!board.locked" type="button" class="w-full rounded-2xl border border-stone-300 py-3 text-sm font-semibold disabled:opacity-40" :disabled="board.history.length === 0" @click="undo">Hoàn tác</button>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <ScoreStepper v-model="team1Score" :disabled="board.locked || board.mode === 'rally'" />
            <ScoreStepper v-model="team2Score" :disabled="board.locked || board.mode === 'rally'" />
        </div>
        <p v-if="board.locked" class="mt-3 text-center text-sm font-semibold text-teal-700">Đã chốt set</p>
        <button v-if="!board.locked && decisive()" type="button" class="mt-3 w-full rounded-2xl border border-teal-700 py-3 text-sm font-semibold text-teal-800" @click="lockSet">Chốt set</button>
        <button v-if="board.locked" type="button" class="mt-3 w-full rounded-2xl border border-stone-300 py-3 text-sm font-semibold" @click="board.locked = false">Sửa set</button>
    </section>
</template>
