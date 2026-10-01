<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import LocalDateTimePicker from '../../Components/LocalDateTimePicker.vue';
import MemberPicker from '../../Components/MemberPicker.vue';
import { combineLocalDateTime, defaultLocalDateTime } from '../../datetime';
import { errorText, scoringTypes } from '../../labels';

defineProps({
    members: { type: Array, required: true },
    courts: { type: Array, required: true },
});

const page = usePage();
const timezone = computed(() => page.props.club?.timezone ?? 'Asia/Ho_Chi_Minh');
const defaults = defaultLocalDateTime(timezone.value);

const form = useForm({
    format: 'single',
    scoring_type: 'side_out',
    scheduled_at: combineLocalDateTime(defaults.date, defaults.hour, defaults.minute),
    court_id: '',
    item: '',
    quantity: '',
    expected_amount: '',
    team_1: [],
    team_2: [],
});

const perTeam = computed(() => (form.format === 'double' ? 2 : 1));

function setFormat(value) {
    form.format = value;
    form.team_1 = form.team_1.slice(0, perTeam.value);
    form.team_2 = form.team_2.slice(0, perTeam.value);
}

function submit() {
    form.transform((data) => ({
        ...data,
        court_id: data.court_id === '' ? null : Number(data.court_id),
        quantity: data.quantity === '' ? null : Number(data.quantity),
        expected_amount: data.expected_amount === '' ? null : Number(data.expected_amount),
    })).post('/keo');
}
</script>

<template>
    <Head title="Tạo kèo" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Tạo kèo độ</h1>
        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <fieldset>
                <legend class="text-sm font-semibold">Thể thức</legend>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <button type="button" class="min-h-12 rounded-2xl border font-semibold" :class="form.format === 'single' ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-stone-300 bg-white'" @click="setFormat('single')">Đơn</button>
                    <button type="button" class="min-h-12 rounded-2xl border font-semibold" :class="form.format === 'double' ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-stone-300 bg-white'" @click="setFormat('double')">Đôi</button>
                </div>
            </fieldset>
            <label class="block text-sm font-semibold">Loại tính điểm
                <select v-model="form.scoring_type" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option v-for="(name, value) in scoringTypes" :key="value" :value="value">{{ name }}</option>
                </select>
            </label>
            <LocalDateTimePicker
                v-model="form.scheduled_at"
                :timezone="timezone"
                date-label="Ngày kèo"
                time-label="Giờ kèo"
            />
            <label class="block text-sm font-semibold">Sân
                <select v-model="form.court_id" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
                    <option value="">Chọn sân</option>
                    <option v-for="court in courts" :key="court.id" :value="court.id">{{ court.code }} · {{ court.name }}</option>
                </select>
            </label>
            <p v-if="courts.length === 0" class="text-sm text-stone-500">CLB chưa có sân đang mở. Nhờ quản trị thêm sân trước.</p>
            <label class="block text-sm font-semibold">Vật phẩm
                <input v-model="form.item" required maxlength="100" placeholder="nước, trứng, chuối" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Số lượng
                <input v-model="form.quantity" type="number" min="1" step="1" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <label class="block text-sm font-semibold">Tiền dự kiến (đ)
                <input v-model="form.expected_amount" type="number" min="0" step="1" required class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            </label>
            <p v-if="form.errors.format || form.errors.scoring_type || form.errors.scheduled_at || form.errors.court_id || form.errors.item || form.errors.quantity || form.errors.expected_amount || form.errors.team_1 || form.errors.team_2" class="text-sm text-red-700">
                {{ errorText(form.errors.format || form.errors.scoring_type || form.errors.scheduled_at || form.errors.court_id || form.errors.item || form.errors.quantity || form.errors.expected_amount || form.errors.team_1 || form.errors.team_2) }}
            </p>
            <section>
                <h2 class="font-semibold">Đội 1 · {{ perTeam }} người</h2>
                <MemberPicker v-model="form.team_1" class="mt-2" :members="members" :limit="perTeam" :exclude-ids="form.team_2" />
            </section>
            <section>
                <h2 class="font-semibold">Đội 2 · {{ perTeam }} người</h2>
                <MemberPicker v-model="form.team_2" class="mt-2" :members="members" :limit="perTeam" :exclude-ids="form.team_1" />
            </section>
            <button class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Tạo kèo</button>
        </form>
    </AppLayout>
</template>
