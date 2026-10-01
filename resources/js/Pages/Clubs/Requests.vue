<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { genders, label, levels } from '../../labels';

defineProps({
    requests: { type: Array, required: true },
});

function approve(id) {
    router.post(`/join-requests/${id}/approve`);
}

function reject(id) {
    router.post(`/join-requests/${id}/reject`);
}
</script>

<template>
    <Head title="Lời xin vào CLB" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Lời xin vào CLB</h1>
        <ul class="mt-4 space-y-2">
            <li v-for="request in requests" :key="request.id" class="rounded-2xl bg-white p-3">
                <p class="font-semibold">{{ request.name }}</p>
                <p class="text-sm text-stone-500">
                    {{ request.nickname || request.email || 'Chưa có email' }}
                    · {{ label(genders, request.gender) }}
                    · {{ label(levels, request.level) }}
                </p>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button type="button" class="min-h-11 rounded-2xl bg-teal-700 font-semibold text-white" @click="approve(request.id)">Duyệt</button>
                    <button type="button" class="min-h-11 rounded-2xl bg-stone-100 font-semibold" @click="reject(request.id)">Từ chối</button>
                </div>
            </li>
        </ul>
        <p v-if="requests.length === 0" class="mt-4 text-sm text-stone-500">Chưa có lời xin.</p>
    </AppLayout>
</template>
