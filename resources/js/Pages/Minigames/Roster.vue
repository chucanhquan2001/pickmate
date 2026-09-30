<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import MemberPicker from '../../Components/MemberPicker.vue';
import { errorText } from '../../labels';

const props = defineProps({
    minigame: { type: Object, required: true },
    members: { type: Array, required: true },
});

const form = useForm({
    member_ids: (props.minigame.roster || []).map((member) => member.id),
});

function submit() {
    form.put(`/minigames/${props.minigame.id}/roster`);
}
</script>

<template>
    <Head title="Roster" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Roster</h1>
        <p class="mt-1 text-sm text-stone-500">{{ minigame.name }}</p>
        <p v-if="form.errors.member_ids" class="mt-3 text-sm text-red-700">{{ errorText(form.errors.member_ids) }}</p>
        <form class="mt-4" @submit.prevent="submit">
            <MemberPicker v-model="form.member_ids" :members="members" />
            <button class="mt-4 flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" :disabled="form.processing">Lưu roster</button>
        </form>
    </AppLayout>
</template>
