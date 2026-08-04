import apiFetch from '@wordpress/api-fetch';

export function getReminder(blockId) {
    return apiFetch({
        path: `/${tsjippy.restApiPrefix}/forms/get_form_reminders`,
        method: 'POST',
        data: blockId,
    });
}

export function saveReminder(blockId, reminder) {
    return apiFetch({
        path: `/${tsjippy.restApiPrefix}/forms/save_form_reminders`,
        method: 'POST',
        data: {
            blockId: blockId,
            reminder: reminder
        }
    });
}