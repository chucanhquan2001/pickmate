<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import InviteQr from '../../Components/InviteQr.vue';

defineProps({
    clubName: { type: String, required: true },
    inviteUrl: { type: String, required: true },
});

const page = usePage();

function rotateInvite() {
    router.post('/settings/invite');
}
</script>

<template>
    <Head title="Mã QR" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Mã QR mời vào CLB</h1>
        <p class="mt-1 text-sm text-stone-500">{{ clubName }}</p>
        <section class="mt-4 rounded-3xl bg-white p-4">
            <p class="text-sm text-stone-500">Người khác quét mã này bằng camera điện thoại, đăng nhập, rồi chờ duyệt.</p>
            <InviteQr :value="inviteUrl" :name="clubName" class="mt-4" />
            <p class="mt-3 break-all text-sm text-stone-600">{{ inviteUrl }}</p>
            <button v-if="page.props.auth.user.is_owner" type="button" class="mt-4 flex min-h-12 w-full items-center justify-center rounded-2xl bg-teal-700 font-semibold text-white" @click="rotateInvite">Tạo mã mới</button>
        </section>
    </AppLayout>
</template>
