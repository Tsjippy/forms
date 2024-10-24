<?php
namespace SIM\FORMS;
use SIM;

add_filter('login_redirect', function($redirect, $requestedRedirect, $user){
    $accountPage  = SIM\ADMIN\getDefaultPageLink('usermanagement', 'account_page');

    if( empty($accountPage)){
        return $redirect;
    }

    // Get mandatory or recommended fields
    $fieldList   = getAllEmptyRequiredElements($user->ID, 'all');

    //redirect to account page to fill in required fields
    if (!empty($fieldList)){
        $redirect   = $accountPage;
    }

    return $redirect;
}, 10, 3);