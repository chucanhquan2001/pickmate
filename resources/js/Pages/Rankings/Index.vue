<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { rulesSummary } from '../../labels';

defineProps({
    minigame: { type: Object, required: true },
    rankings: { type: Array, required: true },
});
</script>

<template>
    <Head title="Bảng xếp hạng" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">BXH</h1>
        <p class="mt-1 text-sm text-stone-500">{{ minigame.name }}</p>
        <p class="mt-1 text-sm text-stone-500">{{ rulesSummary(minigame) }}</p>
        <ol class="mt-4 space-y-2">
            <li v-for="row in rankings" :key="row.member_id" class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                <div>
                    <p class="font-semibold">{{ row.rank }}. {{ row.member }}</p>
                    <p class="text-sm text-stone-500">{{ row.wins }} thắng · {{ row.losses }} thua</p>
                </div>
                <p class="text-lg font-semibold">{{ row.points }}</p>
            </li>
        </ol>
        <p v-if="rankings.length === 0" class="mt-4 text-sm text-stone-500">Chưa có bảng xếp hạng.</p>
    </AppLayout>
</template>
