<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', __NAMESPACE__ . '\initTasks');
/**
 * Initializes the scheduled tasks for the plugin
 */
function initTasks()
{
    TSJIPPY\scheduleTask('tsjippy-forms-auto-archive', 'daily', __NAMESPACE__, 'autoArchiveFormEntries');

    TSJIPPY\scheduleTask('tsjippy-forms-reminder', 'daily', __NAMESPACE__, 'formReminder');
}

/**
 * Archives form entries that are older than the specified number of days
 */
function autoArchiveFormEntries()
{
    $editFormResults = new EditFormResults([]);
    $editFormResults->autoArchive();
}

/**
 * Sends reminders by e-mail and Signal to fill in a form
 */
function formReminder()
{
    // Also send a reminder for any mandatory forms
    $forms   = new FormReminders();

    $forms->sendFormReminders();
}
