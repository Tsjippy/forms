<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

// update reminder cache after each form submission and reminder settings update
add_action('tsjippy-forms-after-submit', __NAMESPACE__ . '\updateReminders');
add_action('tsjippy-forms-after-form-reminder-save', __NAMESPACE__ . '\updateReminders');
/**
 * Updates the reminder cache
 */
function updateReminders()
{
    $forms  = new FormReminders();

    $forms->updateCache();
}
