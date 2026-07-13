<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

/**
 * Plugin Name:          Tsjippy Forms
 * Description:          Versatile form builder
 * Version:              11.8.3
 * Author:               Ewald Harmsen
 * AuthorURI:            harmseninnigeria.nl
 * Requires at least:    6.3
 * Requires PHP:         8.3
 * Tested up to:         7.0
 * Plugin URI:            https://github.com/Tsjippy/forms/
 * Tested:               7.0
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

    createDefaultPages();

    if(function_exists('TSJIPPY\activate')){
        \TSJIPPY\activate();
    }
});

register_deactivation_hook(__FILE__, function () {
    // Remove the auto created page
    wp_delete_post(SETTINGS['forms-page'] ?? 0, true);
});


/**
 * Creates default pages if needed
 * 
 * @param string    $returnKey  The key to return a value for, default empty
 */
function createDefaultPages($returnKey=''){
    /**
     *  Default pages
     */
    $settings    = SETTINGS;

    // Create frontend posting page
    if(!isset($settings['forms-page'])){
        $settings['forms-page']    = TSJIPPY\ADMIN\createDefaultPage('Form selector', '<!-- wp:tsjippy-forms/form-selector /-->');
    }

    update_option('tsjippy_forms_settings', $settings);

    if(!empty($returnKey) && isset($settings[$returnKey])){
        return $settings[$returnKey];
    }
}