<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    clubName: { type: String, required: true },
    counts: { type: Object, required: true },
});

const page = usePage();

const links = [
    { href: '/members', label: 'Thành viên' },
    { href: '/join-requests', label: 'Duyệt lời xin' },
    { href: '/invite', label: 'Mã QR mời vào CLB' },
    { href: '/courts', label: 'Sân' },
];
</script>

<template>
    <Head title="Quản lý CLB" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Quản lý CLB</h1>
        <p class="mt-1 text-sm text-stone-500">{{ clubName }}</p>

        <div class="mt-4 grid grid-cols-2 gap-2">
            <div class="rounded-2xl bg-white p-3">
                <p class="text-2xl font-semibold">{{ counts.members }}</p>
                <p class="text-xs text-stone-500">Thành viên</p>
            </div>
            <div class="rounded-2xl bg-white p-3">
                <p class="text-2xl font-semibold">{{ counts.pending_requests }}</p>
                <p class="text-xs text-stone-500">Lời xin chờ duyệt</p>
            </div>
            <div class="rounded-2xl bg-white p-3">
                <p class="text-2xl font-semibold">{{ counts.courts }}</p>
                <p class="text-xs text-stone-500">Sân</p>
            </div>
            <div class="rounded-2xl bg-white p-3">
                <p class="text-2xl font-semibold">{{ counts.admins }}</p>
                <p class="text-xs text-stone-500">Quản trị viên</p>
            </div>
        </div>

        <div class="mt-6 space-y-2">
            <Link
                v-for="link in links"
                :key="link.href"
                :href="link.href"
                class="flex min-h-12 items-center justify-center rounded-2xl bg-white font-semibold"
            >
                {{ link.label }}
                <span v-if="link.href === '/join-requests'" class="ml-2 inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-teal-700 px-2 text-xs text-white">{{ page.props.pendingJoinRequests }}</span>
            </Link>
            <Link
                v-if="page.props.auth.user.is_owner"
                href="/settings"
                class="flex min-h-12 items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white"
            >Cấu hình CLB</Link>
        </div>
    </AppLayout>
</template>
