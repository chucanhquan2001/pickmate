<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const club = computed(() => page.props.club);
const current = computed(() => page.props.currentMinigame);

const createHref = computed(() => {
    if (!user.value?.can_manage) {
        return '/minigames';
    }

    if (current.value?.status === 'active') {
        return `/minigames/${current.value.id}/matches/create`;
    }

    return '/minigames/create';
});

const rankingHref = computed(() => (
    current.value ? `/minigames/${current.value.id}/rankings` : '/minigames'
));

const items = computed(() => [
    { name: 'Home', href: '/dashboard', icon: 'home' },
    { name: 'Minigame', href: '/minigames', icon: 'grid' },
    { name: 'Create', href: createHref.value, icon: 'plus' },
    { name: 'Ranking', href: rankingHref.value, icon: 'rank' },
    { name: 'Members', href: '/members', icon: 'people' },
]);

const path = computed(() => page.url.split('?')[0]);

const showBack = computed(() => {
    const currentPath = path.value;

    if (currentPath === '/' || currentPath === '/dashboard' || currentPath === '/minigames' || currentPath === '/members' || currentPath === '/keo') {
        return false;
    }

    return !(current.value && currentPath === `/minigames/${current.value.id}/rankings`);
});

const fallbackHref = computed(() => {
    const currentPath = path.value;
    const minigame = currentPath.match(/^\/minigames\/(\d+)/);
    const match = currentPath.match(/^\/minigames\/(\d+)\/matches\/(\d+)/);

    if (currentPath.startsWith('/members/')) {
        return '/members';
    }

    if (currentPath.startsWith('/keo/')) {
        return '/keo';
    }

    if (currentPath === '/minigames/create') {
        return '/minigames';
    }

    if (match && currentPath.endsWith('/result')) {
        return `/minigames/${match[1]}/matches/${match[2]}`;
    }

    if (minigame && currentPath.endsWith('/matches/create')) {
        return `/minigames/${minigame[1]}/matches`;
    }

    if (match) {
        return `/minigames/${match[1]}/matches`;
    }

    if (minigame && (currentPath.endsWith('/roster') || currentPath.endsWith('/rules') || currentPath.endsWith('/matches'))) {
        return `/minigames/${minigame[1]}`;
    }

    if (minigame) {
        return '/minigames';
    }

    if (currentPath === '/manage') {
        return '/dashboard';
    }

    if (['/settings', '/courts', '/invite', '/join-requests'].includes(currentPath) && user.value?.can_manage) {
        return '/manage';
    }

    return '/dashboard';
});

function active(href) {
    if (href === '/dashboard') {
        return path.value === '/' || path.value === '/dashboard';
    }

    return path.value === href || path.value.startsWith(`${href}/`);
}

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }

    router.visit(fallbackHref.value);
}
</script>

<template>
    <div class="mx-auto min-h-screen max-w-lg pb-28">
        <header class="sticky top-0 z-10 flex items-center justify-between gap-3 bg-stone-100/95 px-4 py-3 backdrop-blur">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">PickMate</p>
                <Link href="/clubs" class="block truncate text-sm font-semibold text-stone-800">{{ club?.name || 'Chọn câu lạc bộ' }}</Link>
                <p v-if="current" class="truncate text-xs text-stone-500">{{ current.name }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-1">
                <Link href="/guide" class="rounded-full px-3 py-2 text-sm font-semibold text-teal-700">Hướng dẫn</Link>
                <Link v-if="user?.can_manage" href="/manage" class="rounded-full px-3 py-2 text-sm font-semibold text-stone-700">Quản lý</Link>
                <Link href="/settings" class="rounded-full px-3 py-2 text-sm font-semibold text-stone-700">Cài đặt</Link>
                <Link href="/logout" method="post" as="button" class="rounded-full bg-white px-3 py-2 text-sm font-semibold text-stone-700">Thoát</Link>
            </div>
        </header>

        <main class="px-4">
            <button v-if="showBack" type="button" class="mb-3 text-sm font-semibold text-teal-700" @click="goBack">Quay lại</button>
            <slot />
        </main>

        <nav class="fixed inset-x-0 bottom-0 z-20 border-t border-stone-200 bg-white pb-[env(safe-area-inset-bottom)]">
            <div class="mx-auto grid max-w-lg grid-cols-5">
                <Link
                    v-for="item in items"
                    :key="item.name"
                    :href="item.href"
                    class="flex min-h-16 flex-col items-center justify-center gap-1 text-xs"
                    :class="item.name === 'Create' ? 'text-white' : (active(item.href) ? 'font-semibold text-teal-700' : 'text-stone-500')"
                >
                    <span
                        v-if="item.name === 'Create'"
                        class="-mt-6 flex h-14 w-14 items-center justify-center rounded-full bg-teal-700 text-2xl shadow-lg"
                    >+</span>
                    <span v-else>{{ item.name }}</span>
                    <span v-if="item.name === 'Create'" class="font-semibold text-teal-700">Create</span>
                </Link>
            </div>
        </nav>
    </div>
</template>
