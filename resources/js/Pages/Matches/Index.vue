<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import { formatWhen, teamLabel } from '../../labels';

defineProps({
    minigame: { type: Object, required: true },
    matches: { type: Object, required: true },
});

const page = usePage();
</script>

<template>
    <Head title="Lịch đấu" />
    <AppLayout>
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">Lịch đấu</h1>
                <p class="text-sm text-stone-500">{{ minigame.name }}</p>
            </div>
            <Link v-if="page.props.auth.user.can_manage && minigame.status === 'active'" :href="`/minigames/${minigame.id}/matches/create`" class="rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Tạo</Link>
        </div>
        <ul class="mt-4 space-y-2">
            <li v-for="match in matches.data" :key="match.id">
                <Link :href="`/minigames/${minigame.id}/matches/${match.id}`" class="block rounded-3xl bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm text-stone-500">{{ formatWhen(match.scheduled_at) }}<span v-if="match.court"> · {{ match.court.code }}</span></p>
                        <StatusBadge :value="match.status" kind="match" />
                    </div>
                    <p class="mt-2 font-semibold">{{ teamLabel(match.team_1) }}</p>
                    <p class="text-sm text-stone-400">VS</p>
                    <p class="font-semibold">{{ teamLabel(match.team_2) }}</p>
                </Link>
            </li>
        </ul>
        <p v-if="matches.data.length === 0" class="mt-4 text-sm text-stone-500">Chưa có trận.</p>
    </AppLayout>
</template>
