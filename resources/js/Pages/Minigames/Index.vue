<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import { formats, label } from '../../labels';

defineProps({
    minigames: { type: Object, required: true },
});

const page = usePage();
</script>

<template>
    <Head title="Minigame" />
    <AppLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Minigame</h1>
            <Link v-if="page.props.auth.user.can_manage" href="/minigames/create" class="rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Tạo</Link>
        </div>
        <ul class="mt-4 space-y-2">
            <li v-for="minigame in minigames.data" :key="minigame.id">
                <Link :href="`/minigames/${minigame.id}`" class="block rounded-3xl bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-semibold">{{ minigame.name }}</p>
                            <p class="text-sm text-stone-500">{{ label(formats, minigame.format) }} · {{ minigame.roster_count || 0 }} người</p>
                        </div>
                        <StatusBadge :value="minigame.status" />
                    </div>
                </Link>
            </li>
        </ul>
        <p v-if="minigames.data.length === 0" class="mt-4 text-sm text-stone-500">Chưa có minigame.</p>
    </AppLayout>
</template>
