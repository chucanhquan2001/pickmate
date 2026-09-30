<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { errorText } from '../../labels';

const props = defineProps({
    minigame: { type: Object, required: true },
});

const form = useForm({
    default_score: props.minigame.default_score,
    best_of: props.minigame.best_of,
    participation_points: props.minigame.participation_points,
    win_points: props.minigame.win_points,
    loss_points: props.minigame.loss_points,
    clean_win_bonus: props.minigame.clean_win_bonus,
});

const fields = [
    ['default_score', 'Điểm set', [11, 15, 21]],
    ['best_of', 'Best of', [1, 3, 5]],
    ['participation_points', 'Điểm tham gia'],
    ['win_points', 'Điểm thắng'],
    ['loss_points', 'Điểm thua'],
    ['clean_win_bonus', 'Thưởng thắng sạch'],
];

function submit() {
    form.patch(`/minigames/${props.minigame.id}/rules`);
}
</script>

<template>
    <Head title="Quy chế" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Quy chế</h1>
        <p class="mt-1 text-sm text-stone-500">{{ minigame.name }}</p>
        <form class="mt-4 space-y-3" @submit.prevent="submit">
            <label v-for="field in fields" :key="field[0]" class="block text-sm font-semibold">
                {{ field[1] }}
                <select v-if="field[2]" v-model="form[field[0]]" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option v-for="option in field[2]" :key="option" :value="option">{{ option }}</option>
                </select>
                <input v-else v-model.number="form[field[0]]" type="number" min="0" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                <span v-if="form.errors[field[0]]" class="mt-1 block font-normal text-red-700">{{ errorText(form.errors[field[0]]) }}</span>
            </label>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Lưu quy chế</button>
        </form>
    </AppLayout>
</template>
