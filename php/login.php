<?php
namespace TSJIPPY\FORMS;
use TSJIPPY;

if ( ! defined('ABSPATH')) {
    exit;
}

add_filter('login_redirect', __NAMESPACE__ . '\loginRedirect', 10, 3);
function loginRedirect($redirect, $requestedRedirect, $user) {

    if (rtrim($redirect, '/') != SITEURL) {
        return $redirect;
    }

    $url            = false;
    if (defined('TSJIPPY\USERMANAGEMENT\SETTINGS') && !empty(TSJIPPY\USERMANAGEMENT\SETTINGS['account_page'])) {
        $url            = get_permalink(TSJIPPY\USERMANAGEMENT\SETTINGS['account_page']);
    }

    if ( !$url) {
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