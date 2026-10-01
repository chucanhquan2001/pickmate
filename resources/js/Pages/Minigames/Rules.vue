<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import MinigameRulesFields from '../../Components/MinigameRulesFields.vue';

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
            <MinigameRulesFields :form="form" />
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Lưu quy chế</button>
        </form>
    </AppLayout>
</template>
