<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { errorText, formats, genders, label, levels } from '../../labels';
import { formatWhen, teamLabel } from '../../labels';

const props = defineProps({
    member: { type: Object, required: true },
});

const page = usePage();
const form = useForm({
    name: props.member.name,
    nickname: props.member.nickname || '',
    gender: props.member.gender,
    email: props.member.email || '',
    phone: props.member.phone || '',
    level: props.member.level,
    status: props.member.status,
});

function submit() {
    form.put(`/members/${props.member.id}`);
}
</script>

<template>
    <Head :title="member.name" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">{{ member.name }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ member.nickname }} · {{ label(genders, member.gender) }} · {{ label(levels, member.level) }}</p>

        <section class="mt-4 space-y-2">
            <h2 class="font-semibold">Minigame</h2>
            <article v-for="row in member.minigames" :key="row.id" class="rounded-2xl bg-white p-3">
                <p class="font-semibold">{{ row.name }}</p>
                <p class="text-sm text-stone-500">{{ label(formats, row.format) }} · hạng {{ row.rank }} · {{ row.points }} điểm</p>
            </article>
            <p v-if="member.minigames.length === 0" class="text-sm text-stone-500">Chưa có bảng xếp hạng.</p>
        </section>

        <section class="mt-4 space-y-2">
            <h2 class="font-semibold">Lịch sử trận</h2>
            <article v-for="match in member.matches" :key="match.id" class="rounded-2xl bg-white p-3">
                <p class="text-sm text-stone-500">{{ match.minigame?.name }} · {{ formatWhen(match.scheduled_at) }}</p>
                <p class="mt-1 font-semibold">{{ teamLabel(match.team_1) }} vs {{ teamLabel(match.team_2) }}</p>
            </article>
        </section>

        <form v-if="page.props.auth.user.can_manage" class="mt-6 space-y-3" @submit.prevent="submit">
            <h2 class="font-semibold">Cập nhật</h2>
            <input v-model="form.name" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <p v-if="form.errors.name" class="text-sm text-red-700">{{ errorText(form.errors.name) }}</p>
            <input v-model="form.nickname" placeholder="Nickname" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <select v-model="form.gender" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
                <option v-for="(name, value) in genders" :key="value" :value="value">{{ name }}</option>
            </select>
            <select v-model="form.level" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
                <option v-for="(name, value) in levels" :key="value" :value="value">{{ name }}</option>
            </select>
            <select v-model="form.status" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
                <option value="active">Đang chơi</option>
                <option value="inactive">Ngừng</option>
            </select>
            <input v-model="form.email" type="email" placeholder="Email" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <input v-model="form.phone" placeholder="Điện thoại" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Lưu</button>
        </form>
    </AppLayout>
</template>
