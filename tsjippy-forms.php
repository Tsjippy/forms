<?php
namespace TSJIPPY\FORMS;
use TSJIPPY;

/**
 * Plugin Name:  		Tsjippy Forms
 * Description:  		Versatile form builder
 * Version:      		11.0.9
 * Author:       		Ewald Harmsen
 * AuthorURI:			harmseninnigeria.nl
 * Requires at least:	6.3
 * Requires PHP: 		8.3
 * Tested up to: 		6.9
 * Plugin URI:			https://github.com/Tsjippy/forms/
 * Tested:				6.9
 * TextDomain:			tsjippy
 * Requires Plugins:	tsjippy-shared-functionality
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @author Ewald Harmsen
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pluginData = get_plugin_data(__FILE__, false, false);

// Define constants
define(__NAMESPACE__ .'\PLUGIN', plugin_basename(__FILE__));
define(__NAMESPACE__ .'\PLUGINPATH', __DIR__.'/');
define(__NAMESPACE__ .'\PLUGINVERSION', $pluginData['Version']);
define(__NAMESPACE__ .'\PLUGINSLUG', str_replace('tsjippy-', '', basename(__FILE__, '.php')));
define(__NAMESPACE__ .'\SETTINGS', get_option('tsjippy_forms_settings', []));

// run right before activation
register_activation_hook( __FILE__, function(){
	require_once(PLUGINPATH.'php/classes/Forms.php');
	
    $forms = new Forms();
	$forms->createDbTables();

	$settings	= SETTINGS;

	// Create frontend posting page
	$settings['forms-page']	= TSJIPPY\ADMIN\createDefaultPage('Form selector', '[formselector]');
	update_option('tsjippy_forms_settings', $settings);

	TSJIPPY\scheduleTask('auto_archive_action', 'daily');
    
    TSJIPPY\scheduleTask('form_reminder_action', 'daily');
});

register_deactivation_hook( __FILE__, function(){
	foreach(SETTINGS['forms-pages'] ?? [] as $page){
		// Remove the auto created page
		wp_delete_post($page, true);
	}

	wp_clear_scheduled_hook( 'auto_archive_action' );
	wp_clear_scheduled_hook( 'mandatory_fields_reminder_action' );
} );

