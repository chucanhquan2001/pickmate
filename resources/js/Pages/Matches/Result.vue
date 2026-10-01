<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import RallyBoard from '../../Components/RallyBoard.vue';
import { errorText, label, scoringTypes, teamLabel } from '../../labels';

const props = defineProps({
    minigame: { type: Object, required: true },
    match: { type: Object, required: true },
});

const doubles = computed(() => props.minigame.players_per_team === 2);
const scoringType = computed(() => props.match.scoring_type || 'side_out');

const form = useForm({
    sets: [{ team_1_score: 0, team_2_score: 0 }],
});

function addSet() {
    if (form.sets.length < props.minigame.best_of) {
        form.sets.push({ team_1_score: 0, team_2_score: 0 });
    }
}

function submit() {
    form.transform((data) => ({
        sets: data.sets.map((set) => ({
            team_1_score: Number(set.team_1_score),
            team_2_score: Number(set.team_2_score),
        })),
    })).post(`/minigames/${props.minigame.id}/matches/${props.match.id}/result`);
}
</script>

<template>
    <Head title="Nhập kết quả" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Nhập kết quả</h1>
        <p class="mt-1 text-sm text-stone-500">{{ label(scoringTypes, scoringType) }} · Đến {{ minigame.default_score }} · best of {{ minigame.best_of }}</p>
        <div class="mt-4 grid grid-cols-2 gap-3 text-center">
            <p class="rounded-2xl bg-white p-3 font-semibold">{{ teamLabel(match.team_1) }}</p>
            <p class="rounded-2xl bg-white p-3 font-semibold">{{ teamLabel(match.team_2) }}</p>
        </div>
        <p v-if="form.errors.sets || form.errors.minigame" class="mt-3 text-sm text-red-700">{{ errorText(form.errors.sets || form.errors.minigame) }}</p>
        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <RallyBoard
                v-for="(set, index) in form.sets"
                :key="index"
                v-model:team1-score="set.team_1_score"
                v-model:team2-score="set.team_2_score"
                :team1="match.team_1"
                :team2="match.team_2"
                :doubles="doubles"
                :scoring-type="scoringType"
                :target-score="minigame.default_score"
                :title="`Set ${index + 1}`"
            />
            <button v-if="form.sets.length < minigame.best_of" type="button" class="w-full rounded-2xl border border-stone-300 bg-white py-3 font-semibold" @click="addSet">Thêm set</button>
            <button class="flex min-h-14 w-full items-center justify-center rounded-2xl bg-teal-700 text-lg font-semibold text-white" :disabled="form.processing">Lưu kết quả</button>
        </form>
    </AppLayout>
</template>
