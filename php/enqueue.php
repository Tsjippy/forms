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
    wp_register_style('tsjippy_forms_style', TSJIPPY\pathToUrl(PLUGINPATH . 'css/forms.min.css'), array(), PLUGINVERSION);
    wp_register_style('tsjippy_formtable_style', TSJIPPY\pathToUrl(PLUGINPATH . 'css/formtable.min.css'), array(), PLUGINVERSION);

    wp_register_script_module('@tsjippy/forms_script', TSJIPPY\pathToUrl(PLUGINPATH . "js/forms" . TSJIPPY\JSEXTENSION), array('@tsjippy/formsubmit_script', '@tsjippy/fileupload_script'), PLUGINVERSION);

    wp_register_script_module('@tsjippy/forms_table_script', TSJIPPY\pathToUrl(PLUGINPATH . "js/forms_table" . TSJIPPY\JSEXTENSION), array('@tsjippy/forms_script', '@tsjippy/table_script'), PLUGINVERSION);

    wp_register_script_module('@tsjippy/formsubmit_script', TSJIPPY\pathToUrl(PLUGINPATH ."js/formsubmit" . TSJIPPY\JSEXTENSION), array('@tsjippy\main'), PLUGINVERSION);
}
