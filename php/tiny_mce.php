<?php
namespace SIM\FORMS;
use SIM;

add_action('init', __NAMESPACE__.'\tinyMceInit');
function tinyMceInit(){
	//Add tinymce plugin
	add_filter('mce_external_plugins', __NAMESPACE__.'\mcePlugins' , 999);

	//add tinymce button
	add_filter('mce_buttons', __NAMESPACE__.'\mceButtons', 999);
}

function mcePlugins($plugins){
	global $wp_scripts;

	if(!isset($wp_scripts->registered['sim_script'])){
		return $plugins;
	}

	$simForms	= new SimForms();

	//Add extra variables to the main.js script
	wp_localize_script(
		'sim_forms_script',
		'formSelect',
		[$simForms->formSelect()]
	);

	wp_localize_script(
		'sim_admin_js',
		'formSelect',
		[$simForms->formSelect()]
	);

	$plugins['insert_form_shortcode']		= SIM\pathToUrl(MODULE_PATH."js/tiny_mce.js?ver=".MODULE_VERSION);

	return $plugins;
}

function mceButtons($buttons){
	array_push($buttons, 'insert_form_shortcode');
	return $buttons;
}

