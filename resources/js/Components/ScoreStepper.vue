<script setup>
const props = defineProps({
    modelValue: { type: Number, required: true },
});

const emit = defineEmits(['update:modelValue']);

function set(next) {
    emit('update:modelValue', Math.max(0, Math.min(99, next)));
}
</script>

<template>
    <div class="flex items-center justify-center gap-3">
        <button type="button" class="h-14 w-14 rounded-full bg-stone-200 text-2xl font-semibold" @click="set(props.modelValue - 1)">−</button>
        <span class="w-16 text-center text-4xl font-semibold tabular-nums">{{ props.modelValue }}</span>
        <button type="button" class="h-14 w-14 rounded-full bg-teal-700 text-2xl font-semibold text-white" @click="set(props.modelValue + 1)">+</button>
    </div>
</template>
