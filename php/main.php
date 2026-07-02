<?php

namespace TSJIPPY\forms;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

add_filter('display_post_states', __NAMESPACE__ . '\postStates', 10, 2);
/**
 * Adds a post state to the forms page
 *
 * @param   array   $states     The post states
 * @param   object  $post       The post object
 */
function postStates($states, $post)
{

    if ($post->ID == (SETTINGS['forms-page'] ?? '')) {
        $states[] = __('Form selector page', '%TEXTDOMAIN%');
    }

    return $states;
}

add_filter('tsjippy-file-upload-delete-permission', function($permission){
    $displayForm    = new DisplayFormResults(['form-id' => (int) $_POST['form-id'] ?? -1]);

    return  $displayForm->tableEditPermissions;
});