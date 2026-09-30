<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ScoreStepper from '../../Components/ScoreStepper.vue';
import { errorText, teamLabel } from '../../labels';

const props = defineProps({
    minigame: { type: Object, required: true },
    match: { type: Object, required: true },
});

const form = useForm({
    sets: [{ team_1_score: 0, team_2_score: 0 }],
});

function addSet() {
    if (form.sets.length < props.minigame.best_of) {
        form.sets.push({ team_1_score: 0, team_2_score: 0 });
    }
}

function submit() {
    form.post(`/minigames/${props.minigame.id}/matches/${props.match.id}/result`);
}
</script>

<template>
    <Head title="Nhập kết quả" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Nhập kết quả</h1>
        <p class="mt-1 text-sm text-stone-500">Đến {{ minigame.default_score }} · best of {{ minigame.best_of }}</p>
        <div class="mt-4 grid grid-cols-2 gap-3 text-center">
            <p class="rounded-2xl bg-white p-3 font-semibold">{{ teamLabel(match.team_1) }}</p>
            <p class="rounded-2xl bg-white p-3 font-semibold">{{ teamLabel(match.team_2) }}</p>
        </div>
        <p v-if="form.errors.sets || form.errors.minigame" class="mt-3 text-sm text-red-700">{{ errorText(form.errors.sets || form.errors.minigame) }}</p>
        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <section v-for="(set, index) in form.sets" :key="index" class="rounded-3xl bg-white p-4">
                <p class="mb-3 text-center text-sm font-semibold text-stone-500">Set {{ index + 1 }}</p>
                <div class="grid grid-cols-2 gap-3">
                    <ScoreStepper v-model="set.team_1_score" />
                    <ScoreStepper v-model="set.team_2_score" />
                </div>
            </section>
            <button v-if="form.sets.length < minigame.best_of" type="button" class="w-full rounded-2xl border border-stone-300 bg-white py-3 font-semibold" @click="addSet">Thêm set</button>
            <button class="flex min-h-14 w-full items-center justify-center rounded-2xl bg-teal-700 text-lg font-semibold text-white" :disabled="form.processing">Lưu kết quả</button>
        </form>
    </AppLayout>
</template>
