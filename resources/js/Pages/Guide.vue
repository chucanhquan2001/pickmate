<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import { label, minigameStatuses } from '../labels';

const page = usePage();
const current = computed(() => page.props.currentMinigame);
const canManage = computed(() => Boolean(page.props.auth.user?.can_manage));

const next = computed(() => {
    if (!canManage.value) {
        return {
            title: 'Tài khoản này không tạo được lịch',
            body: 'Chỉ Owner và Admin thấy nút tạo trận. Member xem lịch, bảng xếp hạng và kết quả.',
            href: '/minigames',
            action: 'Xem minigame',
        };
    }

    if (current.value?.status === 'active') {
        return {
            title: `Tạo lịch cho ${current.value.name}`,
            body: 'Minigame này đang chạy. Nút + ở giữa thanh dưới cũng mở form tạo trận.',
            href: `/minigames/${current.value.id}/matches/create`,
            action: 'Tạo trận',
        };
    }

    if (current.value?.status === 'draft') {
        return {
            title: `${current.value.name} vẫn là nháp`,
            body: 'Chưa kích hoạt nên chưa có nút tạo lịch. Vào trang minigame và bấm Kích hoạt.',
            href: `/minigames/${current.value.id}`,
            action: 'Mở để kích hoạt',
        };
    }

    if (current.value?.status === 'closed') {
        return {
            title: `${current.value.name} đã đóng`,
            body: 'Minigame đã đóng không nhận trận mới. Tạo minigame khác, chọn roster, rồi kích hoạt.',
            href: '/minigames/create',
            action: 'Tạo minigame mới',
        };
    }

    return {
        title: 'Chưa mở minigame đang chạy',
        body: 'Nút + lúc này tạo minigame mới, không tạo lịch đấu. Tạo hoặc mở một minigame, kích hoạt, rồi mới xếp trận.',
        href: '/minigames',
        action: 'Đến danh sách minigame',
    };
});

const steps = computed(() => {
    const id = current.value?.id;

    return [
        {
            title: 'Thêm người chơi',
            body: 'Vào tab Members, bấm thêm thành viên. Người này phải đang hoạt động thì mới vào được roster.',
            href: canManage.value ? '/members/create' : '/members',
            action: canManage.value ? 'Thêm thành viên' : 'Xem thành viên',
        },
        {
            title: 'Thêm sân nếu cần',
            body: 'Cài đặt → Quản lý sân. Sân không bắt buộc: lúc tạo trận có thể để “Chưa chọn sân”.',
            href: '/courts',
            action: 'Quản lý sân',
        },
        {
            title: 'Tạo minigame',
            body: 'Tab Minigame → Tạo, hoặc nút + khi chưa có minigame đang chạy. Minigame mới luôn ở trạng thái Nháp.',
            href: canManage.value ? '/minigames/create' : '/minigames',
            action: canManage.value ? 'Tạo minigame' : 'Xem minigame',
        },
        {
            title: 'Chọn roster',
            body: 'Mở minigame → Roster. Chỉ người trong roster mới được xếp vào đội.',
            href: id ? `/minigames/${id}/roster` : '/minigames',
            action: id ? 'Sửa roster' : 'Chọn minigame',
        },
        {
            title: 'Kích hoạt',
            body: 'Trên trang minigame, bấm Kích hoạt. Khi còn Nháp, trang Lịch đấu không có nút Tạo, và nút + vẫn tạo minigame chứ không tạo trận.',
            href: id ? `/minigames/${id}` : '/minigames',
            action: id ? 'Mở minigame' : 'Chọn minigame',
        },
        {
            title: 'Tạo lịch đấu',
            body: 'Khi trạng thái là Đang chạy, điền giờ đấu, sân, đội 1 và đội 2. Có đúng ba chỗ để vào form này.',
            href: current.value?.status === 'active' ? `/minigames/${id}/matches/create` : (id ? `/minigames/${id}/matches` : '/minigames'),
            action: current.value?.status === 'active' ? 'Tạo trận' : 'Xem lịch đấu',
        },
        {
            title: 'Đánh trận và nhập kết quả',
            body: 'Mở trận → Bắt đầu → Nhập kết quả. Điểm chỉ cộng vào bảng xếp hạng của minigame đó.',
            href: id ? `/minigames/${id}/rankings` : '/minigames',
            action: 'Xem bảng xếp hạng',
        },
    ];
});
</script>

<template>
    <Head title="Hướng dẫn" />
    <AppLayout>
        <h1 class="text-2xl font-semibold">Hướng dẫn sử dụng</h1>
        <p class="mt-1 text-sm text-stone-600">Lịch đấu nằm trong từng minigame. Nút + chỉ tạo trận sau khi minigame đó đang chạy.</p>

        <section class="mt-4 rounded-3xl bg-teal-800 p-4 text-white">
            <p class="text-xs font-semibold uppercase tracking-wide text-teal-100">Bạn đang ở bước này</p>
            <h2 class="mt-1 text-lg font-semibold">{{ next.title }}</h2>
            <p class="mt-1 text-sm text-teal-100">{{ next.body }}</p>
            <Link :href="next.href" class="mt-4 flex min-h-12 items-center justify-center rounded-2xl bg-white font-semibold text-teal-800">
                {{ next.action }}
            </Link>
        </section>

        <ol class="mt-6 space-y-3">
            <li v-for="(step, index) in steps" :key="step.title" class="rounded-3xl bg-white p-4">
                <div class="flex gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-700 text-sm font-semibold text-white">{{ index + 1 }}</span>
                    <div class="min-w-0">
                        <h2 class="font-semibold">{{ step.title }}</h2>
                        <p class="mt-1 text-sm text-stone-600">{{ step.body }}</p>
                    </div>
                </div>
                <Link :href="step.href" class="mt-3 flex min-h-11 items-center justify-center rounded-2xl bg-stone-100 text-sm font-semibold text-stone-800">
                    {{ step.action }}
                </Link>
            </li>
        </ol>

        <section class="mt-6 rounded-3xl bg-white p-4">
            <h2 class="font-semibold">Ba chỗ tạo lịch đấu</h2>
            <p class="mt-1 text-sm text-stone-600">Cả ba chỉ hiện khi bạn là Owner hoặc Admin và minigame đang chạy.</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li class="rounded-2xl bg-stone-100 px-3 py-3">
                    <p class="font-semibold">Nút + giữa thanh dưới</p>
                    <p class="text-stone-600">Đang mở đúng minigame đang chạy thì nút này vào form tạo trận. Chưa mở thì nó tạo minigame.</p>
                </li>
                <li class="rounded-2xl bg-stone-100 px-3 py-3">
                    <p class="font-semibold">Trang minigame → Tạo trận</p>
                    <p class="text-stone-600">Nút lớn màu teal, phía dưới Roster, Quy chế, Lịch đấu, BXH.</p>
                </li>
                <li class="rounded-2xl bg-stone-100 px-3 py-3">
                    <p class="font-semibold">Lịch đấu → Tạo</p>
                    <p class="text-stone-600">Góc phải tiêu đề “Lịch đấu”.</p>
                </li>
            </ul>
        </section>

        <section class="mt-3 rounded-3xl bg-white p-4 text-sm text-stone-600">
            <h2 class="font-semibold text-stone-900">Vì sao không thấy nút?</h2>
            <ul class="mt-2 list-disc space-y-2 pl-5">
                <li>Minigame còn <span class="font-semibold text-stone-900">{{ label(minigameStatuses, 'draft') }}</span>: bấm Kích hoạt trước.</li>
                <li>Chưa bấm “Mở minigame” trên trang chủ: nút + đang tạo minigame.</li>
                <li>Minigame <span class="font-semibold text-stone-900">{{ label(minigameStatuses, 'closed') }}</span>: không xếp thêm trận.</li>
                <li>Đăng nhập bằng quyền Member: không có quyền tạo lịch.</li>
            </ul>
            <p v-if="current" class="mt-3">
                Minigame đang chọn: <span class="font-semibold text-stone-900">{{ current.name }}</span>
                · {{ label(minigameStatuses, current.status) }}.
            </p>
        </section>
    </AppLayout>
</template>
