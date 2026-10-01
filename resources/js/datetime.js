const minuteOptions = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];

export function localTimeParts(date, timezone = 'Asia/Ho_Chi_Minh') {
    const parts = Object.fromEntries(
        new Intl.DateTimeFormat('en-CA', {
            timeZone: timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        })
            .formatToParts(date)
            .filter((part) => part.type !== 'literal')
            .map((part) => [part.type, part.value]),
    );

    return {
        date: `${parts.year}-${parts.month}-${parts.day}`,
        hour: parts.hour,
        minute: parts.minute,
    };
}

export function snapMinute(minute) {
    const value = Number(minute);

    if (!Number.isFinite(value)) {
        return '00';
    }

    const snapped = Math.min(55, Math.round(value / 5) * 5);

    return String(snapped).padStart(2, '0');
}

export function splitLocalDateTime(value, timezone = 'Asia/Ho_Chi_Minh') {
    if (!value) {
        return defaultLocalDateTime(timezone);
    }

    const parts = localTimeParts(new Date(value), timezone);
    const minute = minuteOptions.includes(parts.minute) ? parts.minute : snapMinute(parts.minute);

    return {
        ...parts,
        minute,
    };
}

export function combineLocalDateTime(date, hour, minute) {
    if (!date || hour === '' || minute === '') {
        return '';
    }

    return `${date}T${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
}

export function defaultLocalDateTime(timezone = 'Asia/Ho_Chi_Minh') {
    const now = new Date(Date.now() + 5 * 60 * 1000);
    const parts = localTimeParts(now, timezone);
    let hour = Number(parts.hour);
    let minute = Number(parts.minute) + 5;

    if (minute >= 60) {
        minute -= 60;
        hour += 1;
    }

    if (hour >= 24) {
        hour = 23;
        minute = 55;
    }

    return {
        date: parts.date,
        hour: String(hour).padStart(2, '0'),
        minute: snapMinute(String(minute)),
    };
}

export function localDateTimeOptions() {
    return {
        hours: Array.from({ length: 24 }, (_, index) => String(index).padStart(2, '0')),
        minutes: minuteOptions,
    };
}
