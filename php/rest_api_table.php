<?php
namespace SIM\FORMS;
use SIM;
use stdClass;
use WP_Error;

// Allow rest api urls for non-logged in users
add_filter('sim_allowed_rest_api_urls', __NAMESPACE__.'\addFormResultUrls');
function addFormResultUrls($urls){
    $urls[] = RESTAPIPREFIX.'/forms/edit_value';
	$urls[] = RESTAPIPREFIX.'/forms/get_input_html';

    return $urls;
}

add_action( 'rest_api_init', __NAMESPACE__.'\restApiInitTable' );
function restApiInitTable() {
	//save_table_prefs
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/save_table_prefs',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\saveTablePrefs',
			'permission_callback' 	=> '__return_true',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'column-name'	=> array('required'	=> true),
			)
		)
	);

	//delete_table_prefs
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/delete_table_prefs',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\deleteTablePrefs',
			'permission_callback' 	=> '__return_true',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
			)
		)
	);

	//save_column_settings
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/save_column_settings',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\saveColumnSettings',
			'permission_callback' 	=> function(){
				$formsTable		= new DisplayFormResults($_POST);
				return $formsTable->tableEditPermissions;
			},
			'args'					=> array(
				'shortcode-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($shortcodeId){
						return is_numeric($shortcodeId);
					}
				),
				'column-settings'		=> array(
					'required'	=> true,
				),
			)
		)
	);

	// save_table_prefs
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/save_table_settings',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\saveTableSettings',
			'permission_callback' 	=> function(){
				$formsTable		= new DisplayFormResults($_POST);
				return $formsTable->tableEditPermissions;
			},
			'args'					=> array(
				'shortcode-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($shortcodeId){
						return is_numeric($shortcodeId);
					}
				),
				'table-settings'		=> array(
					'required'	=> true,
				),
			)
		)
	);

	//remove submission
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/remove_submission',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\removeSubmission',
			'permission_callback' 	=> '__return_true',
			'args'					=> array(
				'submission-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($submissionId){
						return is_numeric($submissionId);
					}
				)
			)
		)
	);

	//archive submission
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/archive_submission',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\archiveSubmission',
			'permission_callback' 	=> '__return_true',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'submission-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($submissionId){
						return is_numeric($submissionId);
					}
				)
			)
		)
	);

	// edit value
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/edit_value',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\editValue',
			'permission_callback' 	=> '__return_true',
			'args'					=> array(
				'submission-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($submissionId){
						return is_numeric($submissionId);
					}
				),
				'element-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($submissionId){
						return is_numeric($submissionId);
					}
				),
				'new-value'		=> array(
					'required'	=> true,
				),
			)
		)
	);

	//get_input_html
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/get_input_html',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\getInputHtml',
			'permission_callback' 	=> '__return_true',
			'args'					=> array(
				'element-id'		=> array(
					'required'	=> true,
				),
				'submission-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($submissionId){
						return is_numeric($submissionId);
					}
				),
			)
		)
	);

	// get next or prev page
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/get_page',
		array(
			'methods' 				=> \WP_REST_Server::CREATABLE,
			'callback' 				=> __NAMESPACE__.'\getPage',
			'permission_callback' 	=> '__return_true',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'page-number'		=> array(
					'required'	=> true,
					'validate_callback' => function($submissionId){
						return is_numeric($submissionId);
					}
				)
			)
		)
	);
}

function getPage(){
	$displayFormResults		= new DisplayFormResults($_POST);

	$displayFormResults->loadShortcodeData();

	return $displayFormResults->renderTable($_POST['type'], true);
}

function saveTablePrefs( \WP_REST_Request $request ) {
	if (is_user_logged_in()) {
		$columnName					= $request['column-name'];

		$userId						= get_current_user_id();
		$hiddenColumns				= (array)get_user_meta($userId, 'hidden_columns_'.$request['form-id'], true);

		$hiddenColumns[$columnName]	= 'hidden';

		update_user_meta($userId, 'hidden_columns_'.$request['form-id'], $hiddenColumns);

		return 'Succesfully updated column settings';
	}else{
		return new \WP_Error('Error','You are not logged in');
	}
}

function deleteTablePrefs( \WP_REST_Request $request ) {
	if (is_user_logged_in()) {
		$userId		= get_current_user_id();
		delete_user_meta($userId, 'hidden_columns_'.$request['form-id']);

		return 'Succesfully reset column visibility';
	}else{
		return new \WP_Error('error','You are not logged in');
	}
}

function saveColumnSettings($settings=[], $shortcodeId=''){
	$simForms	= new SaveFormSettings($_POST);
	
	if($settings instanceof \WP_REST_Request){
		$params			= $settings->get_params();

		$settings 		= $params['column-settings'];
	}

	$result = $simForms->saveColumnSettings($settings, $shortcodeId);

	if(is_wp_error($result)){
		return $result;
	}
	
	return "Succesfully saved your column settings";
}

function saveTableSettings(){	
	$tableSettings 	= $_POST['table-settings'];

	// Check invalid filter names
	if(isset($tableSettings->filter)){
		foreach($tableSettings->filter as $filter){
			if(in_array($filter['name'], ['accept-charset', 'action', 'autocomplete', 'enctype', 'method', 'name', 'novalidate', 'rel', 'target'])){
				return new WP_Error('forms', "Invalid filter name '{$filter['name']}', use a different one");
			}
		}
	}

	//update table settings
	$simForms	= new SaveFormSettings($_POST);
	
	$result = $simForms->insertOrUpdateData($simForms->shortcodeTable, $tableSettings, ['id' => $_POST['shortcode-id']]);

	if(is_wp_error($result)){
		return $result;
	}
	
	//also update form setings if needed
	$formSettings = $_POST['form-settings'];
	if(is_array($formSettings) && is_numeric($_POST['form-id'])){
		$simForms->getForm($_POST['form-id']);
		
		//update existing
		$result = $simForms->insertOrUpdateData($simForms->tableName, $formSettings, ['id' => $simForms->formData->id]);

		if(is_wp_error($result)){
			return $result;
		}
	}
	
	return "Succesfully saved your table settings";
}

function removeSubmission(){
	$formTable	= new EditFormResults($_POST);

	$result		= $formTable->deleteSubmission($_POST['submission-id']);
	
	if(is_wp_error($result)){
		return $result;
	}

	do_action('sim-forms-entry-removed', $formTable, $_POST['submission-id']);

	return "Entry with id {$_POST['submission-id']} succesfully removed";
}

/**
 * Archive or unarchive a subsubmission
 */
function archiveSubmission(){
	$formTable					= new EditFormResults($_POST);
	$formTable->submissionId	= $_POST['submission-id'];
	$action						= $_POST['action'];

	if($action	== 'archive'){
		$archive = true;
	}else{
		$archive = false;
	}

	$subId		= null;
	if(isset($_POST['subid']) && is_numeric($_POST['subid'])){
		$subId		= $_POST['subid'];
	}
	
	$message	= $formTable->archiveSubmission($archive, $subId);
	
	return $message;
}

/**
 * Retrieves the element html needed to be able to update a form result entry
 */
function getInputHtml(){
	$formTable		= new DisplayFormResults($_POST);

	$formTable->parseSubmissions('', $_POST['submission-id']);

	// Get the form id from the submission and load the form
	$formTable->getForm($formTable->submission->form_id);

	$userId											= $formTable->submission->userid;

	$formTable->userId								= $userId;
	$formTable->elementHtmlBuilder->userId			= $userId;
	$formTable->elementHtmlBuilder->formData		= $formTable->formData;
	$formTable->elementHtmlBuilder->formElements	= $formTable->formElements;

	$elementId										= sanitize_text_field($_POST['element-id']);

	$element										= $formTable->getElementById($elementId);

	if(!$element){
		return new \WP_Error('No element found', "No element found with id '$elementId'");
	}

	$value	= $formTable->getSubmissionValue($_POST['submission-id'], $elementId, $_POST['subid']);

	// Get element html
	
	$html 		= $formTable->elementHtmlBuilder->getElementHtml($element, '', $value);
	
	/**
	 * Check if this element needs a datalist
	 */
	// Get all options
	$options	= explode("\n", trim($element->options));
		
	//Loop over the options array
	foreach($options as $option){
		//Remove starting or ending spaces and make it lowercase
		$option 		= explode('=', trim($option));

		$optionType		= $option[0];
		$optionValue	= str_replace('\\\\', '\\', $option[1]);

		// This option is a list option
		if($optionType == 'list'){
			$datalist	= $formTable->getElementByName($optionValue);

			if($datalist == $element){
				$datalist	= $formTable->getElementByName($optionValue.'-list');
				SIM\printArray("Datalist '$optionValue' cannot have the same name as the element depending on it");
			}

			// Get the html of the datalist element
			if($datalist){
				$html .= $formTable->elementHtmlBuilder->getElementHtml($datalist, '');
			}
		}
	}
		
	// prepend html with the html of previous element that wrap this elemnt
	$index			= $element->priority - 2;
	$prevElement 	= $formTable->formElements[$index];
	while($prevElement && $prevElement->wrap){
		$index--;
		$html 			= $formTable->elementHtmlBuilder->getElementHtml($prevElement, '').$html;
		$prevElement 	= $formTable->formElements[$index];
	}
		
	// add next elements if they are wrapped in this one
	$index			= $element->priority;
	while($element->wrap){
		$element = $formTable->formElements[$index];
		$html 	.= $formTable->elementHtmlBuilder->getElementHtml($element, $parent);
		$index++;
	}

	return $html;
}

/**
 * Updates a value in the submission results table with a new value
 */
function editValue(){
	$formTable					= new EditFormResults($_POST);
		
	$formTable->submissionId	= $_POST['submission-id'];

	$elementId					= sanitize_text_field($_POST['element-id']);

	$subId						= sanitize_text_field($_POST['subid']);
	if($subId == ''){
		$subId	= null;
	}

	$newValue 					= json_decode(sanitize_textarea_field(stripslashes($_POST['new-value'])));

	$oldValue					= $formTable->getSubmissionValue($formTable->submissionId, $elementId, $subId);

	if($oldValue == $newValue){
		if(is_array($oldValue)){
			$oldValue	= implode(' ', $oldValue);
		}
		return new WP_Error('sim-forms', "Old value '$oldValue' is the same as the new value!");
	}

	// update the submissiom
	$result		= $formTable->updateSubmission($elementId, $newValue, $subId);
	if(is_wp_error($result)){
		return $result;
	}

	//get transformed value
	$elementName	= $formTable->getElementById($elementId, ' name' );
	$submissions	= $formTable->getSubmissions('', $formTable->submissionId);
	$transValue		= $formTable->transformInputData($newValue, $elementName, $submissions[0]);

	//send message back to js
	return [
		'message'			=> "Succesfully updated the value to $transValue",
		'new-value'			=> $transValue,
	];
}