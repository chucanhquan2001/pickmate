<script setup>
import { label, matchStatuses, minigameStatuses } from '../labels';

defineProps({
    value: { type: String, required: true },
    kind: { type: String, default: 'minigame' },
});

const tones = {
    draft: 'bg-stone-200 text-stone-700',
    active: 'bg-teal-100 text-teal-800',
    closed: 'bg-stone-300 text-stone-700',
    scheduled: 'bg-amber-100 text-amber-800',
    playing: 'bg-sky-100 text-sky-800',
    completed: 'bg-teal-100 text-teal-800',
    cancelled: 'bg-stone-200 text-stone-600',
};
</script>

<template>
    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="tones[value] || 'bg-stone-200 text-stone-700'">
        {{ label(kind === 'match' ? matchStatuses : minigameStatuses, value) }}
    </span>
</template>
