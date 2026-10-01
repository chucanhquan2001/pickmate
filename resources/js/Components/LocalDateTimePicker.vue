<script setup>
import { computed, ref, watch } from 'vue';
import {
    combineLocalDateTime,
    defaultLocalDateTime,
    localDateTimeOptions,
    splitLocalDateTime,
} from '../datetime';

const props = defineProps({
    modelValue: { type: String, default: '' },
    timezone: { type: String, default: 'Asia/Ho_Chi_Minh' },
    dateLabel: { type: String, default: 'Ngày' },
    timeLabel: { type: String, default: 'Giờ' },
});

const emit = defineEmits(['update:modelValue']);

const { hours, minutes } = localDateTimeOptions();
const parts = ref(defaultLocalDateTime(props.timezone));

function syncFromModel(value) {
    parts.value = splitLocalDateTime(value, props.timezone);
}

watch(
    () => props.modelValue,
    (value) => syncFromModel(value),
    { immediate: true },
);

watch(
    () => props.timezone,
    () => syncFromModel(props.modelValue),
);

watch(
    parts,
    (value) => {
        emit('update:modelValue', combineLocalDateTime(value.date, value.hour, value.minute));
    },
    { deep: true },
);

const summary = computed(() => {
    const { date, hour, minute } = parts.value;

    if (!date) {
        return '';
    }

    const [year, month, day] = date.split('-');

    return `${hour}:${minute} · ${day}/${month}/${year}`;
});

function useToday() {
    const today = defaultLocalDateTime(props.timezone);
    parts.value = {
        ...parts.value,
        date: today.date,
    };
}

function addMinutes(amount) {
    let hour = Number(parts.value.hour);
    let minute = Number(parts.value.minute) + amount;

    while (minute >= 60) {
        minute -= 60;
        hour += 1;
    }

    if (hour >= 24) {
        hour = 23;
        minute = 55;
    }

    parts.value = {
        ...parts.value,
        hour: String(hour).padStart(2, '0'),
        minute: String(minute).padStart(2, '0'),
    };
}
</script>

<template>
    <div class="space-y-3">
        <p v-if="summary" class="rounded-2xl bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-900">{{ summary }}</p>

        <label class="block text-sm font-semibold">{{ dateLabel }}
            <input
                v-model="parts.date"
                type="date"
                required
                class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal"
            >
        </label>

        <div>
            <p class="text-sm font-semibold">{{ timeLabel }}</p>
            <div class="mt-1 grid grid-cols-2 gap-2">
                <select
                    v-model="parts.hour"
                    required
                    class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal"
                >
                    <option v-for="hour in hours" :key="hour" :value="hour">{{ hour }} giờ</option>
                </select>
                <select
                    v-model="parts.minute"
                    required
                    class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal"
                >
                    <option v-for="minute in minutes" :key="minute" :value="minute">{{ minute }} phút</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" class="rounded-full bg-stone-100 px-3 py-2 text-xs font-semibold text-stone-700" @click="useToday">Hôm nay</button>
            <button type="button" class="rounded-full bg-stone-100 px-3 py-2 text-xs font-semibold text-stone-700" @click="addMinutes(30)">+30 phút</button>
            <button type="button" class="rounded-full bg-stone-100 px-3 py-2 text-xs font-semibold text-stone-700" @click="addMinutes(60)">+1 giờ</button>
        </div>

        <p class="text-xs text-stone-500">Giờ theo múi giờ CLB (24 giờ). Chọn phút theo bước 5.</p>
    </div>
</template>
