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

export const levels = {
    beginner: 'Mới chơi',
    intermediate: 'Trung bình',
    advanced: 'Nâng cao',
};

export const roles = {
    owner: 'Owner',
    admin: 'Admin',
    member: 'Member',
};

export function label(map, value) {
    return map[value] ?? value ?? '';
}

export function formatWhen(value) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
    }).format(new Date(value));
}

export function teamLabel(players) {
    return players?.map((player) => player.name).filter(Boolean).join(' / ') || 'Chưa chọn';
}

export function errorText(value) {
    if (!value) {
        return '';
    }

    return Array.isArray(value) ? value[0] : value;
}
