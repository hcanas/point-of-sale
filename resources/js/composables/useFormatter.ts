const LOCALE = 'en-PH';
const CURRENCY = 'PHP';
const TIMEZONE = 'Asia/Manila';

export function useFormatter() {
    const formatNumber = (value: number | string, options?: Intl.NumberFormatOptions): string => {
        const num = typeof value === 'string' ? parseFloat(value) : value;
        if (isNaN(num)) return String(value);

        return new Intl.NumberFormat(LOCALE, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
            ...options,
        }).format(num);
    };

    const formatCurrency = (value: number | string, options?: Intl.NumberFormatOptions): string => {
        const num = typeof value === 'string' ? parseFloat(value) : value;
        if (isNaN(num)) return String(value);

        return new Intl.NumberFormat(LOCALE, {
            style: 'currency',
            currency: CURRENCY,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
            ...options,
        }).format(num);
    };

    const formatPercent = (value: number | string, options?: Intl.NumberFormatOptions): string => {
        const num = typeof value === 'string' ? parseFloat(value) : value;
        if (isNaN(num)) return String(value);

        return new Intl.NumberFormat(LOCALE, {
            style: 'percent',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
            ...options,
        }).format(num);
    };

    const formatDate = (value: Date | string | number, options?: Intl.DateTimeFormatOptions): string => {
        const d = value instanceof Date ? value : new Date(value);
        if (isNaN(d.getTime())) return String(value);

        return new Intl.DateTimeFormat(LOCALE, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            timeZone: TIMEZONE,
            ...options,
        }).format(d);
    };

    const formatDateTime = (value: Date | string | number, options?: Intl.DateTimeFormatOptions): string => {
        const d = value instanceof Date ? value : new Date(value);
        if (isNaN(d.getTime())) return String(value);

        return new Intl.DateTimeFormat(LOCALE, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: TIMEZONE,
            ...options,
        }).format(d);
    };

    const formatTime = (value: Date | string | number, options?: Intl.DateTimeFormatOptions): string => {
        const d = value instanceof Date ? value : new Date(value);
        if (isNaN(d.getTime())) return String(value);

        return new Intl.DateTimeFormat(LOCALE, {
            hour: '2-digit',
            minute: '2-digit',
            timeZone: TIMEZONE,
            ...options,
        }).format(d);
    };

    const formatRelativeTime = (value: Date | string | number, options?: Intl.RelativeTimeFormatOptions): string => {
        const d = value instanceof Date ? value : new Date(value);
        const now = new Date();
        const diffMs = d.getTime() - now.getTime();
        const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

        const rtf = new Intl.RelativeTimeFormat(LOCALE, {
            numeric: 'auto',
            ...options,
        });

        if (Math.abs(diffDays) < 1) {
            const diffHours = Math.round(diffMs / (1000 * 60 * 60));
            if (Math.abs(diffHours) < 1) {
                const diffMinutes = Math.round(diffMs / (1000 * 60));
                return rtf.format(diffMinutes, 'minute');
            }
            return rtf.format(diffHours, 'hour');
        }

        if (Math.abs(diffDays) < 30) {
            return rtf.format(diffDays, 'day');
        }

        const diffMonths = Math.round(diffDays / 30);
        if (Math.abs(diffMonths) < 12) {
            return rtf.format(diffMonths, 'month');
        }

        const diffYears = Math.round(diffDays / 365);
        return rtf.format(diffYears, 'year');
    };

    return {
        LOCALE,
        CURRENCY,
        TIMEZONE,
        formatNumber,
        formatCurrency,
        formatPercent,
        formatDate,
        formatDateTime,
        formatTime,
        formatRelativeTime,
    };
}
