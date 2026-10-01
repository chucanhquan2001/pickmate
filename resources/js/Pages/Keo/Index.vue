<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { formatVnd, formatWhen, label, scoringTypes, stakeFormats, teamLabel } from '../../labels';

defineProps({
    matches: { type: Object, required: true },
});
</script>

<template>
    <Head title="Kèo độ" />
    <AppLayout>
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold">Kèo đã tham gia</h1>
            <Link href="/keo/create" class="rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Tạo kèo</Link>
        </div>

        <ul class="mt-4 space-y-2">
            <li v-for="match in matches.data" :key="match.id">
                <Link :href="`/keo/${match.id}`" class="block rounded-2xl bg-white p-3">
                    <p class="text-sm text-stone-500">
                        {{ label(stakeFormats, match.format) }}
                        · {{ label(scoringTypes, match.scoring_type) }}
                        · {{ formatWhen(match.scheduled_at) }}
                        <span v-if="match.court"> · {{ match.court.code }}</span>
                    </p>
                    <p class="mt-1 font-semibold">{{ teamLabel(match.team_1) }} vs {{ teamLabel(match.team_2) }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ match.item }} × {{ match.quantity }} · {{ formatVnd(match.expected_amount) }}</p>
                </Link>
            </li>
        </ul>
        <p v-if="matches.data.length === 0" class="mt-4 text-sm text-stone-500">Bạn chưa tham gia kèo nào.</p>

        <div v-if="matches.prev_page_url || matches.next_page_url" class="mt-4 grid grid-cols-2 gap-2">
            <Link v-if="matches.prev_page_url" :href="matches.prev_page_url" class="rounded-2xl bg-white px-4 py-3 text-center font-semibold">Trước</Link>
            <Link v-if="matches.next_page_url" :href="matches.next_page_url" class="rounded-2xl bg-white px-4 py-3 text-center font-semibold">Sau</Link>
        </div>
    </AppLayout>
</template>
