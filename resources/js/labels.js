export const scoringTypes = {
    side_out: 'Side-out (truyền thống)',
    rally: 'Rally (theo pha bóng)',
};

export const formats = {
    single_male: 'Đơn nam',
    single_female: 'Đơn nữ',
    double_male: 'Đôi nam',
    double_female: 'Đôi nữ',
    double_mixed: 'Đôi nam nữ',
};

export const minigameStatuses = {
    draft: 'Nháp',
    active: 'Đang chạy',
    closed: 'Đã đóng',
};

export const matchStatuses = {
    scheduled: 'Sắp diễn ra',
    playing: 'Đang đấu',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy',
};

export const genders = {
    male: 'Nam',
    female: 'Nữ',
};

export const stakeFormats = {
    single: 'Đơn',
    double: 'Đôi',
};

export function skillLabel(dupr, spcn) {
    const one = (value) => {
        if (value === null || value === undefined || value === '') {
            return '—';
        }

        const number = Number(value);

        return Number.isFinite(number) ? number.toFixed(1) : String(value);
    };

    return `DUPR ${one(dupr)} · SPCN ${one(spcn)}`;
}

export function formatVnd(value) {
    return `${new Intl.NumberFormat('vi-VN').format(Number(value) || 0)} đ`;
}

export const roles = {
    owner: 'Chủ CLB',
    admin: 'Quản trị viên',
    member: 'Thành viên',
};

export function label(map, value) {
    return map[value] ?? value ?? '';
}

export function formatWhen(value, timezone = 'Asia/Ho_Chi_Minh') {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
        timeZone: timezone,
        hour12: false,
    }).format(new Date(value));
}

export function teamLabel(players) {
    return players?.map((player) => player.name).filter(Boolean).join(' / ') || 'Chưa chọn';
}

export function rulesSummary(minigame) {
    return `Thắng +${minigame.win_points} · Thua +${minigame.loss_points} · Tham gia +${minigame.participation_points}`;
}

export function errorText(value) {
    if (!value) {
        return '';
    }

    return Array.isArray(value) ? value[0] : value;
}
