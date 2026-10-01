<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { errorText, genders, label, levels } from '../../labels';

const props = defineProps({
    target: { type: Object, required: true },
    token: { type: String, required: true },
    state: { type: String, required: true },
});

const page = usePage();
const form = useForm({
    nickname: '',
    gender: 'male',
    level: 'beginner',
});

function submit() {
    form.post(`/join/${props.token}`);
}

function switchClub() {
    router.post(`/clubs/${props.target.id}/switch`);
}
</script>

<template>
    <Head title="Xin vào câu lạc bộ" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Xin vào {{ target.name }}</h1>
        <p class="mt-1 text-sm text-stone-500">Tài khoản {{ page.props.auth.user.name }}</p>

        <section v-if="state === 'member'" class="mt-6 rounded-3xl bg-white p-4">
            <p>Bạn đã là thành viên của câu lạc bộ này.</p>
            <button type="button" class="mt-4 flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" @click="switchClub">
                Dùng CLB này
            </button>
        </section>

        <section v-else-if="state === 'pending'" class="mt-6 rounded-3xl bg-white p-4 text-stone-600">
            Lời xin đang chờ chủ hoặc admin duyệt. Khi được duyệt, bạn sẽ thành thành viên.
        </section>

        <form v-else class="mt-6 space-y-3" @submit.prevent="submit">
            <label class="block text-sm font-semibold">Nickname
                <input v-model="form.nickname" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Giới tính
                <select v-model="form.gender" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option v-for="(name, value) in genders" :key="value" :value="value">{{ name }}</option>
                </select>
            </label>
            <label class="block text-sm font-semibold">Trình độ
                <select v-model="form.level" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option v-for="(name, value) in levels" :key="value" :value="value">{{ label(levels, value) }}</option>
                </select>
            </label>
            <p v-if="form.errors.join || form.errors.gender" class="text-sm text-red-700">{{ errorText(form.errors.join || form.errors.gender) }}</p>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Gửi lời xin</button>
        </form>
    </AppLayout>
</template>
