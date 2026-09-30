<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import { errorText, formatWhen, teamLabel } from '../../labels';

const props = defineProps({
    minigame: { type: Object, required: true },
    match: { type: Object, required: true },
    courts: { type: Array, required: true },
});

const page = usePage();
const form = useForm({
    scheduled_at: props.match.scheduled_at ? props.match.scheduled_at.slice(0, 16) : '',
    court_id: props.match.court_id || '',
});

const mutable = ['scheduled', 'playing'].includes(props.match.status);

function save() {
    form.transform((data) => ({
        ...data,
        court_id: data.court_id === '' ? null : Number(data.court_id),
    })).put(`/minigames/${props.minigame.id}/matches/${props.match.id}`);
}
</script>

<template>
    <Head title="Trận đấu" />
    <AppLayout>
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">{{ minigame.name }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ formatWhen(match.scheduled_at) }}</p>
            </div>
            <StatusBadge :value="match.status" kind="match" />
        </div>

        <section class="mt-4 rounded-3xl bg-white p-4">
            <p class="text-lg font-semibold">{{ teamLabel(match.team_1) }}</p>
            <p class="my-2 text-center text-sm text-stone-400">VS</p>
            <p class="text-lg font-semibold">{{ teamLabel(match.team_2) }}</p>
            <p v-if="match.winner_team" class="mt-3 text-sm font-semibold text-teal-700">Đội {{ match.winner_team }} thắng</p>
            <ul class="mt-3 space-y-1 text-sm">
                <li v-for="set in match.sets" :key="set.set_number">Set {{ set.set_number }}: {{ set.team_1_score }} - {{ set.team_2_score }}</li>
            </ul>
        </section>

        <p v-if="page.props.errors.status || page.props.errors.minigame" class="mt-3 text-sm text-red-700">
            {{ errorText(page.props.errors.status || page.props.errors.minigame) }}
        </p>

        <div v-if="page.props.auth.user.can_manage" class="mt-4 grid grid-cols-2 gap-2">
            <button v-if="match.status === 'scheduled'" type="button" class="min-h-12 rounded-2xl bg-teal-700 font-semibold text-white" @click="router.post(`/minigames/${minigame.id}/matches/${match.id}/start`)">Bắt đầu</button>
            <button v-if="match.status === 'scheduled' || match.status === 'playing'" type="button" class="min-h-12 rounded-2xl border border-stone-300 bg-white font-semibold" @click="router.post(`/minigames/${minigame.id}/matches/${match.id}/cancel`)">Hủy trận</button>
            <Link v-if="match.status !== 'cancelled' && match.status !== 'completed'" :href="`/minigames/${minigame.id}/matches/${match.id}/result`" class="col-span-2 flex min-h-12 items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white">Nhập kết quả</Link>
        </div>

        <form v-if="page.props.auth.user.can_manage && mutable" class="mt-6 space-y-3" @submit.prevent="save">
            <h2 class="font-semibold">Sửa lịch</h2>
            <input v-model="form.scheduled_at" type="datetime-local" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <select v-model="form.court_id" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
                <option value="">Chưa chọn sân</option>
                <option v-for="court in courts" :key="court.id" :value="court.id">{{ court.code }} · {{ court.name }}</option>
            </select>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-stone-900 font-semibold text-white" :disabled="form.processing">Lưu lịch</button>
        </form>
    </AppLayout>
</template>
