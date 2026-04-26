<?php
namespace TSJIPPY\forms;
use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('display_post_states', __NAMESPACE__.'\postStates', 10, 2);
function postStates( $states, $post ) {
    
    if (in_array($post->ID, SETTINGS['forms-pages'] ?? [] )) {
        $states[] = __('Form selector page');
    }

    return $states;
}
