export function normaliseReminderResponse(response, emptyReminder) {
    return {
        ...emptyReminder,
        ...response,
        block_id: response?.block_id || '',
        frequency: response?.frequency || '',
        period: response?.period || '',
        reminder_start_date: response?.reminder_start_date || '',
        reminder_amount: response?.reminder_amount || '',
        reminder_period: response?.reminder_period || '',
        window_start: response?.window_start || '',
        window_end: response?.window_end || '',
        conditions: Array.isArray(response?.conditions) ? response.conditions : [],
    };
}

export function getDateWindowLimits(reminder) {
    const frequency = parseInt(reminder.frequency, 10);
    const period = reminder.period;

    if (!frequency || !period) {
        return {
            min: undefined,
            max: undefined,
        };
    }

    const today = new Date();

    const minDate = new Date(today);
    const maxDate = new Date(today);

    if (period === 'days') {
        minDate.setDate(today.getDate() - frequency + 1);
        maxDate.setDate(today.getDate() + frequency - 1);
    }

    if (period === 'months') {
        minDate.setMonth(today.getMonth() - frequency);
        minDate.setDate(minDate.getDate() + 1);

        maxDate.setMonth(today.getMonth() + frequency);
        maxDate.setDate(maxDate.getDate() - 1);
    }

    if (period === 'years') {
        minDate.setFullYear(today.getFullYear() - frequency);
        minDate.setDate(minDate.getDate() + 1);

        maxDate.setFullYear(today.getFullYear() + frequency);
        maxDate.setDate(maxDate.getDate() - 1);
    }

    return {
        min: formatDateForInput(minDate),
        max: formatDateForInput(maxDate),
    };
}

export function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}