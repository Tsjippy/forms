<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

add_filter('tsjippy-mandatory-html-filter', __NAMESPACE__ . '\addChildFields', 10, 3);
/**
 * Adds the mandatory fields for children to the html
 *
 * @param   string  $html       The html to add to
 * @param   int     $userId     The user id to get the children for
 * @param   object  $object     The object to get the reminder html from
 */
function addChildFields($html, $userId, $object)
{
    // Add warnings for child fields
    $family = new TSJIPPY\FAMILY\Family();

    // Loop over children
    foreach ($family->getChildren($userId) as $child) {
        $userData = get_userdata($child);
        // Valid user account
        if ($userData) {
            // Add html for each field as well
            $html    .= $object->getReminderHtml($child, 'mandatory');
        }
    }

    return $html;
}

add_action('tsjippy-user-management-dashboard-warnings', __NAMESPACE__ . '\dashboardWarnings');
/**
 * Displays the dashboard warnings for the user
 *
 * @param   int     $userId     The user id to get the warnings for
 */
function dashboardWarnings($userId)
{
    $forms    = new FormReminders();

    $html     = $forms->getReminderHtml($userId, 'recommended');

    if (empty($html)) {
        ?>
        <p>
            All your data is up to date, well done.
        </p>
        <?php
    } else {
        ?>
        <h3>
            Please finish your account:
        </h3>
        <?php
        echo wp_kses_post($html);
    }
}
