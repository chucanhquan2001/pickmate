<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { errorText, genders, label, levels } from '../../labels';

const form = useForm({
    name: '',
    nickname: '',
    gender: 'male',
    email: '',
    phone: '',
    level: 'beginner',
});

function submit() {
    form.post('/members');
}
</script>

<template>
    <Head title="Thêm thành viên" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Thêm thành viên</h1>
        <form class="mt-4 space-y-3" @submit.prevent="submit">
            <label class="block text-sm font-semibold">Họ tên
                <input v-model="form.name" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <p v-if="form.errors.name" class="text-sm text-red-700">{{ errorText(form.errors.name) }}</p>
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
            <label class="block text-sm font-semibold">Email
                <input v-model="form.email" type="email" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Điện thoại
                <input v-model="form.phone" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <button type="submit" class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Lưu</button>
        </form>
    </AppLayout>
</template>
