<script setup>
import { errorText } from '../labels';

defineProps({
    form: { type: Object, required: true },
});

const fields = [
    ['default_score', 'Điểm set', [11, 15, 21], null],
    ['best_of', 'Best of', [1, 3, 5], null],
    ['participation_points', 'Điểm tham gia', null, 'Cộng cho mọi người mỗi trận đã đá, kể cả thua'],
    ['win_points', 'Điểm thắng', null, 'Cộng thêm khi thắng trận'],
    ['loss_points', 'Điểm thua', null, 'Cộng thêm khi thua trận (thường = 0)'],
    ['clean_win_bonus', 'Thưởng thắng sạch', null, 'Cộng thêm khi thắng tất cả các set'],
];
</script>

<template>
    <label v-for="field in fields" :key="field[0]" class="block text-sm font-semibold">
        {{ field[1] }}
        <select v-if="field[2]" v-model="form[field[0]]" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
            <option v-for="option in field[2]" :key="option" :value="option">{{ option }}</option>
        </select>
        <input v-else v-model.number="form[field[0]]" type="number" min="0" class="mt-1 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base font-normal">
        <span v-if="field[3]" class="mt-1 block font-normal text-stone-500">{{ field[3] }}</span>
        <span v-if="form.errors[field[0]]" class="mt-1 block font-normal text-red-700">{{ errorText(form.errors[field[0]]) }}</span>
    </label>
</template>
