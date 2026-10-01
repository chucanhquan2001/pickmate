<script setup>
import { errorText } from '../labels';

defineProps({
    dupr: { type: String, default: '' },
    spcn: { type: String, default: '' },
    errors: { type: Object, default: () => ({}) },
});

defineEmits(['update:dupr', 'update:spcn']);
</script>

<template>
    <div class="space-y-3">
    <label class="block text-sm font-semibold">DUPR
        <input
            :value="dupr"
            inputmode="decimal"
            placeholder="2.5"
            required
            class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal"
            @input="$emit('update:dupr', $event.target.value)"
        >
    </label>
    <p v-if="errors.dupr_rating" class="text-sm text-red-700">{{ errorText(errors.dupr_rating) }}</p>
    <label class="block text-sm font-semibold">SPCN
        <input
            :value="spcn"
            inputmode="decimal"
            placeholder="2.0"
            required
            class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal"
            @input="$emit('update:spcn', $event.target.value)"
        >
    </label>
    <p v-if="errors.spcn_rating" class="text-sm text-red-700">{{ errorText(errors.spcn_rating) }}</p>
    <p class="text-xs text-stone-500">Đúng một chữ số thập phân, từ 1.0 đến 8.0. Ví dụ 2.0, 2.5.</p>
    </div>
</template>
