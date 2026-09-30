<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    members: { type: Array, required: true },
    modelValue: { type: Array, required: true },
    limit: { type: Number, default: null },
    excludeIds: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);
const search = ref('');

const visible = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.members.filter((member) => {
        if (props.excludeIds.includes(member.id)) {
            return false;
        }

        if (term === '') {
            return true;
        }

        return [member.name, member.nickname, member.email].filter(Boolean).some((value) => value.toLowerCase().includes(term));
    });
});

function toggle(id) {
    const selected = props.modelValue.includes(id)
        ? props.modelValue.filter((value) => value !== id)
        : [...props.modelValue, id];

    if (props.limit && selected.length > props.limit) {
        return;
    }

    emit('update:modelValue', selected);
}
</script>

<template>
    <div>
        <input
            v-model="search"
            type="search"
            placeholder="Tìm thành viên"
            class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base"
        >
        <ul class="mt-3 space-y-2">
            <li v-for="member in visible" :key="member.id">
                <button
                    type="button"
                    class="flex min-h-14 w-full items-center gap-3 rounded-2xl border px-3 text-left"
                    :class="modelValue.includes(member.id) ? 'border-teal-700 bg-teal-50' : 'border-stone-200 bg-white'"
                    @click="toggle(member.id)"
                >
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-stone-200 text-sm font-semibold">
                        {{ member.name.slice(0, 1) }}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate font-semibold">{{ member.name }}</span>
                        <span class="block truncate text-sm text-stone-500">{{ member.nickname || member.email || member.gender }}</span>
                    </span>
                </button>
            </li>
        </ul>
        <p v-if="visible.length === 0" class="mt-3 text-sm text-stone-500">Không thấy thành viên phù hợp.</p>
    </div>
</template>
