<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\registerScripts');
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\registerScripts');

/**
 * Registers the scripts and styles for the forms
 */
function registerScripts()
{
    /**
     * CSS
     */
    wp_register_style('tsjippy_forms_style', TSJIPPY\pathToUrl(PLUGINPATH . 'css/forms.min.css'), array(), PLUGINVERSION);

    wp_register_style('tsjippy_formtable_style', TSJIPPY\pathToUrl(PLUGINPATH . 'css/formtable.min.css'), array(), PLUGINVERSION);

    /**
     * Modules
     */
    wp_register_script_module('@tsjippy/multi_input', TSJIPPY\pathToUrl(PLUGINPATH ."js/modules/multi-input.esm.js"), array(), PLUGINVERSION);

    wp_register_script_module('@tsjippy/field_value', TSJIPPY\pathToUrl(PLUGINPATH ."js/modules/field_value.js"), array(), PLUGINVERSION);

    $deps   = SCRIPT_DEBUG ? [ 
        "@tsjippy/field_value",
        "@tsjippy/tabs",
        "@tsjippy/nice_select"
    ] :
    [];
    wp_register_script_module('@tsjippy/form_exports', TSJIPPY\pathToUrl(PLUGINPATH ."js/modules/form_exports.js"), $deps, PLUGINVERSION);

    $deps   = SCRIPT_DEBUG ? [ 
        "@tsjippy/show_loader", 
        "@tsjippy/display_message"
    ] :
    [];

    $deps[] = "@tsjippy/nonce_script";
    wp_register_script_module('@tsjippy/form_submit_functions', TSJIPPY\pathToUrl(PLUGINPATH ."js/modules/form_submit_functions.js"), $deps, PLUGINVERSION);

    /**
     * Scripts
     */
    $deps   = SCRIPT_DEBUG ? [  
        '@tsjippy/form_submit_functions', 
        "@tsjippy/field_value", 
        "@tsjippy/show_loader", 
        "@tsjippy/display_message", 
        "@tsjippy/modals",
        "@tsjippy/alert",
        "@tsjippy/nice_select"
    ] :
    [];

    $deps[] = "@tsjippy/nonce_script";
    wp_register_script_module('@tsjippy/forms_table_script', TSJIPPY\pathToUrl(PLUGINPATH . "js/forms_table" . TSJIPPY\JSEXTENSION), $deps, PLUGINVERSION);

    $deps   = SCRIPT_DEBUG ? [  
        '@tsjippy/form_submit_functions', 
        "@tsjippy/form_exports", 
        "@tsjippy/display_message",
        '@tsjippy/multi_input'
    ] :
    [];
    wp_register_script_module('@tsjippy/forms_script', TSJIPPY\pathToUrl(PLUGINPATH . "js/forms" . TSJIPPY\JSEXTENSION), $deps, PLUGINVERSION);

    $deps   = SCRIPT_DEBUG ? [  
        '@tsjippy/form_submit_functions'
    ] :
    [];

    $deps[] = "@tsjippy/nonce_script";
    wp_register_script_module('@tsjippy/formsubmit_script', TSJIPPY\pathToUrl(PLUGINPATH ."js/formsubmit" . TSJIPPY\JSEXTENSION), $deps, PLUGINVERSION);

}
