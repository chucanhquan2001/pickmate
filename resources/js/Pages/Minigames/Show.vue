<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import { errorText, formats, label } from '../../labels';

const props = defineProps({
    minigame: { type: Object, required: true },
});

const page = usePage();
const form = useForm({
    name: props.minigame.name,
    description: props.minigame.description || '',
    format: props.minigame.format,
});

function save() {
    form.put(`/minigames/${props.minigame.id}`);
}
</script>

<template>
    <Head :title="minigame.name" />
    <AppLayout>
        <div class="flex items-start justify-between gap-3">
            <h1 class="text-2xl font-semibold">{{ minigame.name }}</h1>
            <StatusBadge :value="minigame.status" />
        </div>
        <p class="mt-1 text-sm text-stone-500">{{ label(formats, minigame.format) }} · {{ minigame.roster_count || 0 }} người · {{ minigame.match_count || 0 }} trận</p>
        <p v-if="page.props.errors.minigame" class="mt-3 text-sm text-red-700">{{ errorText(page.props.errors.minigame) }}</p>

        <div class="mt-4 grid grid-cols-2 gap-2">
            <Link :href="`/minigames/${minigame.id}/roster`" class="rounded-2xl bg-white px-3 py-4 text-center font-semibold">Roster</Link>
            <Link :href="`/minigames/${minigame.id}/rules`" class="rounded-2xl bg-white px-3 py-4 text-center font-semibold">Quy chế</Link>
            <Link :href="`/minigames/${minigame.id}/matches`" class="rounded-2xl bg-white px-3 py-4 text-center font-semibold">Lịch đấu</Link>
            <Link :href="`/minigames/${minigame.id}/rankings`" class="rounded-2xl bg-white px-3 py-4 text-center font-semibold">BXH</Link>
        </div>

        <div v-if="page.props.auth.user.can_manage" class="mt-4 grid grid-cols-2 gap-2">
            <button v-if="minigame.status === 'draft'" type="button" class="min-h-12 rounded-2xl bg-teal-700 font-semibold text-white" @click="router.post(`/minigames/${minigame.id}/activate`)">Kích hoạt</button>
            <button v-if="minigame.status !== 'closed'" type="button" class="min-h-12 rounded-2xl border border-stone-300 bg-white font-semibold" @click="router.post(`/minigames/${minigame.id}/close`)">Đóng</button>
            <Link v-if="minigame.status === 'active'" :href="`/minigames/${minigame.id}/matches/create`" class="col-span-2 flex min-h-12 items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white">Tạo trận</Link>
            <p v-else class="col-span-2 text-sm text-stone-600">
                <template v-if="minigame.status === 'draft'">Bấm Kích hoạt rồi mới tạo được lịch. Nút + ở thanh dưới cũng chỉ tạo trận khi minigame đang chạy.</template>
                <template v-else>Minigame đã đóng nên không tạo thêm trận.</template>
                <Link href="/guide" class="font-semibold text-teal-700"> Hướng dẫn</Link>
            </p>
        </div>

        <form v-if="page.props.auth.user.can_manage && minigame.status !== 'closed'" class="mt-6 space-y-3" @submit.prevent="save">
            <h2 class="font-semibold">Sửa thông tin</h2>
            <input v-model="form.name" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
            <select v-model="form.format" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3">
                <option v-for="(name, value) in formats" :key="value" :value="value">{{ name }}</option>
            </select>
            <textarea v-model="form.description" rows="3" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3" />
            <p v-if="form.errors.format" class="text-sm text-red-700">{{ errorText(form.errors.format) }}</p>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-stone-900 font-semibold text-white" :disabled="form.processing">Lưu</button>
        </form>
    </AppLayout>
</template>
