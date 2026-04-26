<?php
namespace TSJIPPY\FORMS;
use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('init', __NAMESPACE__.'\tinyMceInit');
function tinyMceInit(){
	//Add tinymce plugin
	add_filter('mce_external_plugins', __NAMESPACE__.'\mcePlugins' , 999);

	//add tinymce button
	add_filter('mce_buttons', __NAMESPACE__.'\mceButtons', 999);
}

function mcePlugins($plugins){
	global $wp_scripts;

	if(!isset($wp_scripts->registered['tsjippy_script'])){
		return $plugins;
	}

	$forms	= new Forms();

	//Add extra variables to the main.js script
	wp_localize_script(
		'tsjippy_forms_script',
		'formSelect',
		[$forms->formSelect()]
	);

	wp_localize_script(
		'tsjippy_admin_js',
		'formSelect',
		[$forms->formSelect()]
	);

	$plugins['insert_form_shortcode']		= TSJIPPY\pathToUrl(PLUGINPATH."js/tiny_mce.js?ver=".PLUGINVERSION);

	return $plugins;
}

function mceButtons($buttons){
	array_push($buttons, 'insert_form_shortcode');
	return $buttons;
}

