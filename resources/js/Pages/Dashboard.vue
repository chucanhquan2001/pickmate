<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import StatusBadge from '../Components/StatusBadge.vue';
import { formats, formatWhen, label, teamLabel } from '../labels';

defineProps({
    minigames: { type: Array, required: true },
    selected: { type: Object, default: null },
});

const page = usePage();

function clearSelection() {
    router.delete('/current-minigame');
}
</script>

<template>
    <Head title="Home" />
    <AppLayout>
        <Link href="/guide" class="mb-4 block rounded-3xl bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Hướng dẫn</p>
            <p class="mt-1 font-semibold">Tạo lịch đấu ở đâu?</p>
            <p class="mt-1 text-sm text-stone-600">Nút + chỉ tạo trận khi minigame đang chạy. Xem từng bước.</p>
        </Link>

        <section v-if="selected" class="space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold">{{ selected.minigame.name }}</h1>
                    <p class="mt-1 text-sm text-stone-600">{{ label(formats, selected.minigame.format) }}</p>
                </div>
                <StatusBadge :value="selected.minigame.status" />
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div class="rounded-2xl bg-white p-3">
                    <p class="text-2xl font-semibold">{{ selected.roster_count }}</p>
                    <p class="text-xs text-stone-500">Trong roster</p>
                </div>
                <div class="rounded-2xl bg-white p-3">
                    <p class="text-2xl font-semibold">{{ selected.matches_this_week }}</p>
                    <p class="text-xs text-stone-500">Tuần này</p>
                </div>
                <div class="rounded-2xl bg-white p-3">
                    <p class="text-2xl font-semibold">{{ selected.matches_played }}</p>
                    <p class="text-xs text-stone-500">Đã đấu</p>
                </div>
            </div>

            <section class="rounded-3xl bg-white p-4">
                <h2 class="font-semibold">BXH minigame</h2>
                <ol class="mt-3 space-y-2">
                    <li v-for="row in selected.top_rankings" :key="row.member_id" class="flex items-center justify-between">
                        <span>{{ row.rank }}. {{ row.member }}</span>
                        <span class="font-semibold">{{ row.points }} điểm</span>
                    </li>
                    <li v-if="selected.top_rankings.length === 0" class="text-sm text-stone-500">Chưa có điểm.</li>
                </ol>
            </section>

            <section class="space-y-3">
                <h2 class="font-semibold">Hôm nay</h2>
                <article v-for="match in selected.today" :key="match.id" class="rounded-3xl bg-white p-4">
                    <p class="text-sm text-stone-500">{{ formatWhen(match.scheduled_at) }}<span v-if="match.court"> · {{ match.court.name }}</span></p>
                    <p class="mt-2 font-semibold">{{ teamLabel(match.team_1) }}</p>
                    <p class="text-sm text-stone-500">VS</p>
                    <p class="font-semibold">{{ teamLabel(match.team_2) }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <Link :href="`/minigames/${selected.minigame.id}/matches/${match.id}`" class="rounded-2xl border border-stone-300 px-3 py-3 text-center text-sm font-semibold">Sửa lịch</Link>
                        <Link v-if="page.props.auth.user.can_manage" :href="`/minigames/${selected.minigame.id}/matches/${match.id}/result`" class="rounded-2xl bg-teal-700 px-3 py-3 text-center text-sm font-semibold text-white">Nhập kết quả</Link>
                    </div>
                </article>
                <p v-if="selected.today.length === 0" class="text-sm text-stone-500">Không có trận hôm nay.</p>
            </section>

            <button type="button" class="text-sm font-semibold text-teal-700" @click="clearSelection">Xem tất cả minigame</button>
        </section>

        <section v-else class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Đang chạy</h1>
                <Link v-if="page.props.auth.user.can_manage" href="/minigames/create" class="rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Tạo minigame</Link>
            </div>

            <article v-for="minigame in minigames" :key="minigame.id" class="rounded-3xl bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">{{ minigame.name }}</h2>
                        <p class="text-sm text-stone-500">{{ label(formats, minigame.format) }}</p>
                    </div>
                    <StatusBadge :value="minigame.status" />
                </div>
                <p class="mt-3 text-sm text-stone-600">{{ minigame.roster_count || 0 }} người · {{ minigame.match_count || 0 }} trận</p>
                <Link :href="`/minigames/${minigame.id}/select`" method="post" as="button" class="mt-3 flex min-h-12 items-center justify-center rounded-2xl bg-teal-700 px-4 font-semibold text-white">
                    Mở minigame
                </Link>
            </article>

            <p v-if="minigames.length === 0" class="rounded-3xl bg-white p-4 text-sm text-stone-500">Chưa có minigame đang chạy.</p>

            <Link v-if="page.props.auth.user.can_manage" href="/members/create" class="flex min-h-12 items-center justify-center rounded-2xl border border-stone-300 bg-white font-semibold">
                Thêm thành viên
            </Link>
        </section>
    </AppLayout>
</template>
