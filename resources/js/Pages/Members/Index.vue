<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { errorText, label, roles, skillLabel } from '../../labels';

defineProps({
    members: { type: Object, required: true },
    filters: { type: Object, required: true },
});

const page = usePage();
const search = ref(page.props.filters.search || '');

function submit() {
    router.get('/members', { search: search.value }, { preserveState: true, replace: true });
}

function changeRole(member, role) {
    router.patch(`/settings/users/${member.user_id}`, { role });
}
</script>

<template>
    <Head title="Thành viên" />
    <AppLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Thành viên</h1>
            <Link v-if="page.props.auth.user.can_manage" href="/join-requests" class="inline-flex items-center gap-1 rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white">
                Duyệt
                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1.5 text-xs text-teal-800">{{ page.props.pendingJoinRequests }}</span>
            </Link>
        </div>
        <Link v-if="page.props.auth.user.can_manage" href="/invite" class="mt-4 flex min-h-12 items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white">Mã QR mời vào CLB</Link>

        <p v-if="page.props.errors?.role" class="mt-3 text-sm text-red-700">{{ errorText(page.props.errors.role) }}</p>

        <form class="mt-4" @submit.prevent="submit">
            <input v-model="search" type="search" placeholder="Tìm tên, nickname, email" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base">
        </form>

        <ul class="mt-4 space-y-2">
            <li v-for="member in members.data" :key="member.id" class="rounded-2xl bg-white px-3 py-3">
                <Link :href="`/members/${member.id}`" class="flex min-h-16 items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-stone-200 font-semibold">{{ member.name.slice(0, 1) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate font-semibold">{{ member.name }}</span>
                        <span class="block truncate text-sm text-stone-500">{{ member.nickname || member.email || 'Chưa có nickname' }}</span>
                        <span class="block truncate text-sm text-stone-500">{{ skillLabel(member.dupr_rating, member.spcn_rating) }}</span>
                        <span v-if="member.account_role" class="block truncate text-sm text-teal-700">{{ label(roles, member.account_role) }}</span>
                    </span>
                </Link>
                <div v-if="member.can_change_role" class="mt-2 space-y-2">
                    <button
                        v-if="member.account_role !== 'admin'"
                        type="button"
                        class="flex min-h-11 w-full items-center justify-center rounded-xl bg-stone-100 text-sm font-semibold"
                        @click="changeRole(member, 'admin')"
                    >Đặt quản trị viên</button>
                    <button
                        v-if="member.account_role !== 'member'"
                        type="button"
                        class="flex min-h-11 w-full items-center justify-center rounded-xl bg-stone-100 text-sm font-semibold"
                        @click="changeRole(member, 'member')"
                    >Đặt thành viên</button>
                </div>
            </li>
        </ul>
        <p v-if="members.data.length === 0" class="mt-4 text-sm text-stone-500">Chưa có thành viên.</p>

        <div v-if="members.prev_page_url || members.next_page_url" class="mt-4 grid grid-cols-2 gap-2">
            <Link v-if="members.prev_page_url" :href="members.prev_page_url" class="rounded-2xl bg-white px-4 py-3 text-center font-semibold">Trước</Link>
            <Link v-if="members.next_page_url" :href="members.next_page_url" class="rounded-2xl bg-white px-4 py-3 text-center font-semibold">Sau</Link>
        </div>
    </AppLayout>
</template>
