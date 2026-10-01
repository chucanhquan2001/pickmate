<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    members: { type: Object, required: true },
    filters: { type: Object, required: true },
});

const page = usePage();
const search = ref(page.props.filters.search || '');

function submit() {
    router.get('/members', { search: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Thành viên" />
    <AppLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Thành viên</h1>
            <Link v-if="page.props.auth.user.can_manage" href="/join-requests" class="rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Duyệt</Link>
        </div>

        <form class="mt-4" @submit.prevent="submit">
            <input v-model="search" type="search" placeholder="Tìm tên, nickname, email" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base">
        </form>

        <ul class="mt-4 space-y-2">
            <li v-for="member in members.data" :key="member.id">
                <Link :href="`/members/${member.id}`" class="flex min-h-16 items-center gap-3 rounded-2xl bg-white px-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-stone-200 font-semibold">{{ member.name.slice(0, 1) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate font-semibold">{{ member.name }}</span>
                        <span class="block truncate text-sm text-stone-500">{{ member.nickname || member.email || 'Chưa có nickname' }}</span>
                    </span>
                </Link>
            </li>
        </ul>
        <p v-if="members.data.length === 0" class="mt-4 text-sm text-stone-500">Chưa có thành viên.</p>

        <div v-if="members.prev_page_url || members.next_page_url" class="mt-4 grid grid-cols-2 gap-2">
            <Link v-if="members.prev_page_url" :href="members.prev_page_url" class="rounded-2xl bg-white px-4 py-3 text-center font-semibold">Trước</Link>
            <Link v-if="members.next_page_url" :href="members.next_page_url" class="rounded-2xl bg-white px-4 py-3 text-center font-semibold">Sau</Link>
        </div>
    </AppLayout>
</template>
