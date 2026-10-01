<script setup>
import { usePage } from '@inertiajs/vue3';
import { errorText } from '../labels';

const page = usePage();
</script>

<template>
    <div class="mx-auto flex min-h-screen max-w-lg flex-col justify-center px-4 py-10">
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">PickMate</p>
        <h1 class="mt-2 text-3xl font-semibold">Play. Track. Rank.</h1>
        <p class="mt-3 text-stone-600">Đăng nhập bằng Google. Không dùng mật khẩu.</p>

        <p v-if="page.props.errors.auth" class="mt-6 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ errorText(page.props.errors.auth) }}
        </p>

        <div class="mt-8">
            <a href="/auth/google" class="flex min-h-14 items-center justify-center rounded-2xl bg-teal-700 px-4 text-base font-semibold text-white">
                Tiếp tục với Google
            </a>
        </div>
    </div>
</template>
