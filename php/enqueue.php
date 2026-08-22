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

    wp_register_script('tsjippy_forms_script', TSJIPPY\pathToUrl(PLUGINPATH . 'js/forms.min.js'), array('tsjippy_formsubmit_script', 'tsjippy_fileupload_script'), PLUGINVERSION, true);

    wp_register_script('tsjippy_forms_table_script', TSJIPPY\pathToUrl(PLUGINPATH . 'js/forms_table.min.js'), array('tsjippy_forms_script', 'tsjippy_table_script', 'wp-blocks', 'wp-element', 'wp-dom-ready'), PLUGINVERSION, true);
}
