<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { errorText } from '../../labels';

defineProps({
    courts: { type: Object, required: true },
});

const page = usePage();
const form = useForm({
    name: '',
    code: '',
    note: '',
});

function submit() {
    form.post('/courts');
}

function toggle(court) {
    router.patch(`/courts/${court.id}`, {
        status: court.status === 'active' ? 'inactive' : 'active',
    });
}
</script>

<template>
    <Head title="Sân" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Sân</h1>
        <ul class="mt-4 space-y-2">
            <li v-for="court in courts.data" :key="court.id" class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                <div>
                    <p class="font-semibold">{{ court.code }} · {{ court.name }}</p>
                    <p class="text-sm text-stone-500">{{ court.status === 'active' ? 'Đang dùng' : 'Ngừng' }}</p>
                </div>
                <button v-if="page.props.auth.user.can_manage" type="button" class="rounded-full bg-stone-100 px-3 py-2 text-sm font-semibold" @click="toggle(court)">Đổi</button>
            </li>
        </ul>
        <p v-if="courts.data.length === 0" class="mt-4 text-sm text-stone-500">Chưa có sân.</p>

        <form v-if="page.props.auth.user.can_manage" class="mt-6 space-y-3" @submit.prevent="submit">
            <h2 class="font-semibold">Thêm sân</h2>
            <input v-model="form.name" placeholder="Tên sân" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <input v-model="form.code" placeholder="Mã sân" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <input v-model="form.note" placeholder="Ghi chú" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <p v-if="form.errors.code || form.errors.name" class="text-sm text-red-700">{{ errorText(form.errors.code || form.errors.name) }}</p>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Lưu sân</button>
        </form>
    </AppLayout>
</template>
