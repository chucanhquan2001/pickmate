<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { errorText, roles, label } from '../../labels';

const props = defineProps({
    club: { type: Object, required: true },
    users: { type: Array, required: true },
});

const page = usePage();
const form = useForm({
    name: props.club.name,
    timezone: props.club.timezone,
    language: props.club.language,
    default_score: props.club.default_score,
    default_best_of: props.club.default_best_of,
    default_participation_points: props.club.default_participation_points,
    default_win_points: props.club.default_win_points,
    default_loss_points: props.club.default_loss_points,
    default_clean_win_bonus: props.club.default_clean_win_bonus,
});

function saveClub() {
    form.put('/settings');
}

function changeRole(user, role) {
    router.patch(`/settings/users/${user.id}`, { role });
}
</script>

<template>
    <Head title="Cài đặt" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Cài đặt CLB</h1>
        <p class="mt-1 text-sm text-stone-500">{{ club.name }}</p>
        <Link href="/courts" class="mt-4 flex min-h-12 items-center justify-center rounded-2xl bg-white font-semibold">Quản lý sân</Link>

        <form v-if="page.props.auth.user.is_owner" class="mt-6 space-y-3" @submit.prevent="saveClub">
            <label class="block text-sm font-semibold">Tên CLB
                <input v-model="form.name" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Múi giờ
                <input v-model="form.timezone" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Ngôn ngữ
                <input v-model="form.language" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Điểm set mặc định
                <select v-model.number="form.default_score" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option :value="11">11</option>
                    <option :value="15">15</option>
                    <option :value="21">21</option>
                </select>
            </label>
            <label class="block text-sm font-semibold">Best of mặc định
                <select v-model.number="form.default_best_of" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option :value="1">1</option>
                    <option :value="3">3</option>
                    <option :value="5">5</option>
                </select>
            </label>
            <label class="block text-sm font-semibold">Điểm tham gia
                <input v-model.number="form.default_participation_points" type="number" min="0" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Điểm thắng
                <input v-model.number="form.default_win_points" type="number" min="0" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Điểm thua
                <input v-model.number="form.default_loss_points" type="number" min="0" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Thưởng thắng sạch
                <input v-model.number="form.default_clean_win_bonus" type="number" min="0" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <p v-if="form.errors.name" class="text-sm text-red-700">{{ errorText(form.errors.name) }}</p>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Lưu CLB</button>
        </form>

        <section v-if="page.props.auth.user.is_owner" class="mt-8 space-y-2">
            <h2 class="font-semibold">Người dùng</h2>
            <article v-for="user in users" :key="user.id" class="rounded-2xl bg-white p-3">
                <p class="font-semibold">{{ user.name }}</p>
                <p class="text-sm text-stone-500">{{ user.email }} · {{ label(roles, user.role) }}</p>
                <div v-if="user.id !== page.props.auth.user.id" class="mt-2 grid grid-cols-2 gap-2">
                    <button type="button" class="rounded-xl bg-stone-100 py-2 text-sm font-semibold" @click="changeRole(user, 'admin')">Admin</button>
                    <button type="button" class="rounded-xl bg-stone-100 py-2 text-sm font-semibold" @click="changeRole(user, 'member')">Member</button>
                </div>
            </article>
        </section>
    </AppLayout>
</template>
