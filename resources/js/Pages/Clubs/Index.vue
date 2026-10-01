<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import SkillRatings from '../../Components/SkillRatings.vue';
import { errorText, genders, label, roles } from '../../labels';

defineProps({
    memberships: { type: Array, required: true },
    pendingRequests: { type: Array, required: true },
});

const page = usePage();
const form = useForm({
    name: '',
    nickname: '',
    gender: 'male',
    dupr_rating: '',
    spcn_rating: '',
});

function createClub() {
    form.post('/clubs');
}

function switchClub(clubId) {
    router.post(`/clubs/${clubId}/switch`);
}
</script>

<template>
    <Head title="Câu lạc bộ" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Câu lạc bộ</h1>
        <p class="mt-1 text-sm text-stone-500">{{ page.props.auth.user.name }}</p>

        <section class="mt-4 space-y-2">
            <article v-for="membership in memberships" :key="membership.club_id" class="rounded-2xl bg-white p-3">
                <p class="font-semibold">{{ membership.name }}</p>
                <p class="text-sm text-stone-500">{{ label(roles, membership.role) }}</p>
                <p v-if="membership.current" class="mt-2 text-sm font-semibold text-teal-700">Đang dùng</p>
                <Link
                    v-if="membership.current && (membership.role === 'owner' || membership.role === 'admin')"
                    href="/invite"
                    class="mt-3 flex min-h-11 items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white"
                >Mã QR mời vào CLB</Link>
                <button
                    v-else
                    type="button"
                    class="mt-3 flex min-h-11 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white"
                    @click="switchClub(membership.club_id)"
                >
                    Chuyển sang CLB này
                </button>
            </article>
            <p v-if="memberships.length === 0" class="rounded-2xl bg-white p-4 text-sm text-stone-500">Bạn chưa ở câu lạc bộ nào.</p>
        </section>

        <section v-if="pendingRequests.length > 0" class="mt-6 space-y-2">
            <h2 class="font-semibold">Đang chờ duyệt</h2>
            <p v-for="request in pendingRequests" :key="request.id" class="rounded-2xl bg-white px-3 py-3 text-sm">
                {{ request.club_name }}
            </p>
        </section>

        <form class="mt-8 space-y-3" @submit.prevent="createClub">
            <h2 class="font-semibold">Tạo câu lạc bộ</h2>
            <label class="block text-sm font-semibold">Tên CLB
                <input v-model="form.name" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <p v-if="form.errors.name" class="text-sm text-red-700">{{ errorText(form.errors.name) }}</p>
            <label class="block text-sm font-semibold">Nickname của bạn
                <input v-model="form.nickname" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Giới tính
                <select v-model="form.gender" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option v-for="(name, value) in genders" :key="value" :value="value">{{ name }}</option>
                </select>
            </label>
            <SkillRatings v-model:dupr="form.dupr_rating" v-model:spcn="form.spcn_rating" :errors="form.errors" />
            <p v-if="form.errors.gender" class="text-sm text-red-700">{{ errorText(form.errors.gender) }}</p>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Tạo CLB</button>
        </form>
    </AppLayout>
</template>
