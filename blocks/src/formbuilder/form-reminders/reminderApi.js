import apiFetch from '@wordpress/api-fetch';

export function getReminder(blockId) {
    return apiFetch({
        path: `tsjippy/v2/forms/get_form_reminders`,
        method: 'POST',
        data: {
            blockId: blockId
        }
    });
}

export function saveReminder(blockId, reminder) {
    return apiFetch({
        path: `tsjippy/v2/forms/save_form_reminders`,
        method: 'POST',
        data: {
            blockId: blockId,
            reminder: reminder
        }
    });
}