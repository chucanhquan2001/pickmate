<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import RallyBoard from '../../Components/RallyBoard.vue';
import { errorText, formatVnd, formatWhen, label, scoringTypes, stakeFormats, teamLabel } from '../../labels';

const props = defineProps({
    match: { type: Object, required: true },
});

const doubles = computed(() => props.match.format === 'double');
const form = useForm({
    team_1_score: props.match.team_1_score ?? 0,
    team_2_score: props.match.team_2_score ?? 0,
});

function submit() {
    form.post(`/keo/${props.match.id}/score`);
}
</script>

<template>
    <Head title="Kèo độ" />
    <AppLayout>
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">{{ label(stakeFormats, match.format) }} · {{ label(scoringTypes, match.scoring_type) }}</p>
        <h1 class="mt-1 text-2xl font-semibold">{{ teamLabel(match.team_1) }} vs {{ teamLabel(match.team_2) }}</h1>
        <p class="mt-2 text-sm text-stone-500">
            {{ formatWhen(match.scheduled_at) }}
            <span v-if="match.court"> · {{ match.court.code }} · {{ match.court.name }}</span>
        </p>

        <section class="mt-4 rounded-3xl bg-white p-4">
            <h2 class="font-semibold">Kèo</h2>
            <p class="mt-2">{{ match.item }} × {{ match.quantity }}</p>
            <p class="mt-1 text-sm text-stone-600">Tiền dự kiến {{ formatVnd(match.expected_amount) }}</p>
        </section>

        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-3 text-center">
                <p class="rounded-2xl bg-white p-3 font-semibold">{{ teamLabel(match.team_1) }}</p>
                <p class="rounded-2xl bg-white p-3 font-semibold">{{ teamLabel(match.team_2) }}</p>
            </div>
            <p v-if="form.errors.team_1_score || form.errors.team_2_score" class="text-sm text-red-700">{{ errorText(form.errors.team_1_score || form.errors.team_2_score) }}</p>
            <RallyBoard
                v-model:team1-score="form.team_1_score"
                v-model:team2-score="form.team_2_score"
                :team1="match.team_1"
                :team2="match.team_2"
                :doubles="doubles"
                :scoring-type="match.scoring_type || 'side_out'"
            />
            <button class="flex min-h-14 w-full items-center justify-center rounded-2xl bg-teal-700 text-lg font-semibold text-white" :disabled="form.processing">Lưu tỷ số</button>
        </form>
    </AppLayout>
</template>
