<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

add_filter('login_redirect', __NAMESPACE__ . '\loginRedirect', 10, 3);
function loginRedirect($redirect, $requestedRedirect, $user)
{

    if (rtrim($redirect, '/') != TSJIPPY\SITEURL) {
        return $redirect;
    }

    $url            = get_edit_profile_url($user->ID);

    if (!$url) {
        return $redirect;
    }

    // Get mandatory or recommended fields
    $forms      = new FormReminders();
    $fieldList  = $forms->getUserFormReminders($user->ID);

    //redirect to account page to fill in required fields
    if (!empty($fieldList)) {
        $redirect   = $url;
    }

    return $redirect;
}
