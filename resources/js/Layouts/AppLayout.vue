<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
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

function active(href) {
    const path = page.url.split('?')[0];

    if (href === '/dashboard') {
        return path === '/' || path === '/dashboard';
    }

    return path === href || path.startsWith(`${href}/`);
}
</script>

<template>
    <div class="mx-auto min-h-screen max-w-lg pb-28">
        <header class="sticky top-0 z-10 flex items-center justify-between gap-3 bg-stone-100/95 px-4 py-3 backdrop-blur">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">PickMate</p>
                <p class="truncate text-sm text-stone-600">{{ current?.name || user?.name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Link href="/settings" class="rounded-full px-3 py-2 text-sm font-semibold text-stone-700">Cài đặt</Link>
                <Link href="/logout" method="post" as="button" class="rounded-full bg-white px-3 py-2 text-sm font-semibold text-stone-700">Thoát</Link>
            </div>
        </header>

        <main class="px-4">
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
