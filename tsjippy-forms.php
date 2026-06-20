<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

/**
 * Plugin Name:          Tsjippy Forms
 * Description:          Versatile form builder
 * Version:              11.5.8
 * Author:               Ewald Harmsen
 * AuthorURI:            harmseninnigeria.nl
 * Requires at least:    6.3
 * Requires PHP:         8.3
 * Tested up to:         6.9
 * Plugin URI:            https://github.com/Tsjippy/forms/
 * Tested:                6.9
 * TextDomain:            tsjippy
 * Requires Plugins:    
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @author Ewald Harmsen
 */
if (! defined('ABSPATH')) {
    exit;
}

// Load shared code
if(file_exists(__DIR__  . '/shared-functionality/loader.php')){
    require_once(__DIR__  . '/shared-functionality/loader.php');
}

// Define constants
define(__NAMESPACE__ . '\PLUGIN', plugin_basename(__FILE__));
define(__NAMESPACE__ . '\PLUGINPATH', __DIR__ . '/');
define(__NAMESPACE__ . '\PLUGINVERSION', get_plugin_data(__FILE__, false, false)['Version']);
define(__NAMESPACE__ . '\PLUGINSLUG', str_replace('tsjippy-', '', basename(__FILE__, '.php')));
define(__NAMESPACE__ . '\SETTINGS', get_option('tsjippy_forms_settings', []));

// run right before activation
register_activation_hook(__FILE__, function () {
    if(file_exists(__DIR__  . '/shared-functionality/loader.php')){
        require_once(__DIR__  . '/shared-functionality/loader.php');
    }

    $forms = new Forms();
    $forms->createDbTables();

    $settings    = SETTINGS;

    // Create frontend posting page
    $settings['forms-page']    = TSJIPPY\ADMIN\createDefaultPage('Form selector', '[tsjippy_formselector]');
    update_option('tsjippy_forms_settings', $settings);
});

register_deactivation_hook(__FILE__, function () {
    foreach (SETTINGS['forms-pages'] ?? [] as $page) {
        // Remove the auto created page
        wp_delete_post($page, true);
    }
});
