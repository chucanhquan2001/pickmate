<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import MinigameRulesFields from '../../Components/MinigameRulesFields.vue';
import { errorText, formats } from '../../labels';

const props = defineProps({
    clubDefaults: { type: Object, required: true },
});

const page = usePage();

const form = useForm({
    name: '',
    description: '',
    format: 'double_male',
    default_score: props.clubDefaults.default_score,
    best_of: props.clubDefaults.best_of,
    participation_points: props.clubDefaults.participation_points,
    win_points: props.clubDefaults.win_points,
    loss_points: props.clubDefaults.loss_points,
    clean_win_bonus: props.clubDefaults.clean_win_bonus,
});

function submit() {
    form.post('/minigames');
}
</script>

<template>
    <Head title="Tạo minigame" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Tạo minigame</h1>
        <p class="mt-1 text-sm text-stone-500">CLB {{ page.props.club?.name }}</p>
        <form class="mt-4 space-y-3" @submit.prevent="submit">
            <label class="block text-sm font-semibold">Tên
                <input v-model="form.name" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <p v-if="form.errors.name" class="text-sm text-red-700">{{ errorText(form.errors.name) }}</p>
            <label class="block text-sm font-semibold">Thể thức
                <select v-model="form.format" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option v-for="(name, value) in formats" :key="value" :value="value">{{ name }}</option>
                </select>
            </label>
            <label class="block text-sm font-semibold">Mô tả
                <textarea v-model="form.description" rows="3" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal" />
            </label>

            <section class="space-y-3 pt-2">
                <div>
                    <h2 class="font-semibold">Quy chế tính điểm</h2>
                    <p class="mt-1 text-sm font-normal text-stone-500">Mặc định lấy từ CLB, có thể sửa cho minigame này.</p>
                </div>
                <MinigameRulesFields :form="form" />
            </section>

            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Tạo</button>
        </form>
    </AppLayout>
</template>
