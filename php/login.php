<?php
namespace TSJIPPY\FORMS;
use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('login_redirect', __NAMESPACE__.'\loginRedirect', 10, 3);
function loginRedirect($redirect, $requestedRedirect, $user){

    if(rtrim($redirect, '/') != SITEURL){
        return $redirect;
    }

    $accountPage  = TSJIPPY\ADMIN\getDefaultPageLink('usermanagement', 'account_page');

    if( empty($accountPage)){
        return $redirect;
    }

    // Get mandatory or recommended fields
    $forms      = new FormReminders();
    $fieldList  = $forms->getUserFormReminders($user->ID, 'all');

    //redirect to account page to fill in required fields
    if (!empty($fieldList)){
        $redirect   = $accountPage;
    }

    return $redirect;
}