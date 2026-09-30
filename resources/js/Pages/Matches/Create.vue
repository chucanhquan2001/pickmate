<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import MemberPicker from '../../Components/MemberPicker.vue';
import { errorText } from '../../labels';

const props = defineProps({
    minigame: { type: Object, required: true },
    roster: { type: Array, required: true },
    courts: { type: Array, required: true },
});

const form = useForm({
    scheduled_at: '',
    court_id: '',
    team_1: [],
    team_2: [],
});

function submit() {
    form.transform((data) => ({
        ...data,
        court_id: data.court_id === '' ? null : Number(data.court_id),
    })).post(`/minigames/${props.minigame.id}/matches`);
}
</script>

<template>
    <Head title="Tạo trận" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Tạo trận</h1>
        <p class="mt-1 text-sm text-stone-500">{{ minigame.name }} · mỗi đội {{ minigame.players_per_team }} người</p>
        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <label class="block text-sm font-semibold">Giờ đấu
                <input v-model="form.scheduled_at" type="datetime-local" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Sân
                <select v-model="form.court_id" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option value="">Chưa chọn sân</option>
                    <option v-for="court in courts" :key="court.id" :value="court.id">{{ court.code }} · {{ court.name }}</option>
                </select>
            </label>
            <p v-if="form.errors.minigame || form.errors.team_1 || form.errors.court_id" class="text-sm text-red-700">
                {{ errorText(form.errors.minigame || form.errors.team_1 || form.errors.court_id) }}
            </p>
            <section>
                <h2 class="font-semibold">Đội 1</h2>
                <MemberPicker v-model="form.team_1" class="mt-2" :members="roster" :limit="minigame.players_per_team" :exclude-ids="form.team_2" />
            </section>
            <section>
                <h2 class="font-semibold">Đội 2</h2>
                <MemberPicker v-model="form.team_2" class="mt-2" :members="roster" :limit="minigame.players_per_team" :exclude-ids="form.team_1" />
            </section>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Tạo trận</button>
        </form>
    </AppLayout>
</template>
