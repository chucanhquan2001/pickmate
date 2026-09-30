<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { errorText, formats } from '../../labels';

const form = useForm({
    name: '',
    description: '',
    format: 'double_male',
});

function submit() {
    form.post('/minigames');
}
</script>

<template>
    <Head title="Tạo minigame" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Tạo minigame</h1>
        <form class="mt-4 space-y-3" @submit.prevent="submit">
            <label class="block text-sm font-semibold">Tên
                <input v-model="form.name" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <p v-if="form.errors.name" class="text-sm text-red-700">{{ errorText(form.errors.name) }}</p>
            <label class="block text-sm font-semibold">Thể thức
                <select v-model="form.format" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option v-for="(name, value) in formats" :key="value" :value="value">{{ name }}</option>
                </select>
            </label>
            <label class="block text-sm font-semibold">Mô tả
                <textarea v-model="form.description" rows="3" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal" />
            </label>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Tạo</button>
        </form>
    </AppLayout>
</template>
