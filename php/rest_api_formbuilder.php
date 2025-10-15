<?php
namespace SIM\FORMS;
use SIM;
use stdClass;
use WP_Error;

// Allow rest api urls for non-logged in users
add_filter('sim_allowed_rest_api_urls', __NAMESPACE__.'\addRestUrls');
function addRestUrls($urls){
    $urls[] = RESTAPIPREFIX.'/forms/save_form_input';

    return $urls;
}

function checkPermissions(){
	$forms	= new SimForms();

	return $forms->editRights;
}

add_action( 'rest_api_init', __NAMESPACE__.'\restApiInitForms');
function restApiInitForms() {
	// copy element to form
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/copy_form_element',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\copyFormElement',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'element-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($elementIndex){
						return is_numeric($elementIndex);
					}
				),
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'order'		=> array(
					'required'	=> true
				),
			)
		)
	);

	// add element to form
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/add_form_element',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\addFormElement',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'formfield'		=> array(
					'required'	=> true
				),
				'element-id'		=> array(
					'required'	=> true
				)
			)
		)
	);

	// delete element
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/remove_element',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\removeElement',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'elementindex'		=> array(
					'required'	=> true,
					'validate_callback' => function($elementIndex){
						return is_numeric($elementIndex);
					}
				),
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				)
			)
		)
	);

	// request form element
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/request_form_element',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\requestFormElement',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'element-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($elementId){
						return is_numeric($elementId);
					}
				)
			)
		)
	);

	// reorder form elements
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/reorder-form-elements',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\reorderFormElements',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'el-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($elementId){
						return is_numeric($elementId);
					}
				),
				'indexes'		=> array(
					'required'	=> true
				)
			)
		)
	);

	// edit formfield width
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/edit_formfield_width',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\editFormfieldWidth',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'elementid'		=> array(
					'required'	=> true,
					'validate_callback' => function($elementId){
						return is_numeric($elementId);
					}
				),
				'new-width'		=> array(
					'required'	=> true,
					'validate_callback' => function($width){
						return is_numeric($width);
					}
				)
			)
		)
	);

	// form conditions html
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/request_form_conditions_html',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\requestFormConditionsHtml',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'elementid'		=> array(
					'required'	=> true,
					'validate_callback' => function($elementId){
						return is_numeric($elementId);
					}
				)
			)
		)
	);

	// save_element-conditions
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/save_element-conditions',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\saveElementConditions',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'elementid'		=> array(
					'required'	=> true,
					'validate_callback' => function($elementId){
						return is_numeric($elementId);
					}
				)
			)
		)
	);

	// save_form_settings
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/save_form_settings',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\saveFormSettings',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				)
			)
		)
	);

	// save_form_input
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/save_form_input',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	function(){
				$formBuilder	= new SubmitForm();
				return $formBuilder->formSubmit();
			},
			'permission_callback' 	=> '__return_true',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				)
			)
		)
	);

	// save_form_emails
	register_rest_route(
		RESTAPIPREFIX.'/forms',
		'/save_form_emails',
		array(
			'methods' 				=> 'POST',
			'callback' 				=> 	__NAMESPACE__.'\saveFormEmails',
			'permission_callback' 	=> __NAMESPACE__.'\checkPermissions',
			'args'					=> array(
				'form-id'		=> array(
					'required'	=> true,
					'validate_callback' => function($formId){
						return is_numeric($formId);
					}
				),
				'emails'		=> array(
					'required'	=> true
				),
				
			)
		)
	);
}

function getUniqueName($element, $update, $oldElement, $simForms){
	global $wpdb;

	// Remove any ' from the name, replace white space with _ as php does this automatically in post
	$element->name	= str_replace(["\\'", " "], ['', "_"], $element->name);

	// Make sure we only are working on the name
	$element->name	= end(explode('\\', $element->name));

	$elements		= $simForms->getElementByName($element->name, '', false);
	if(
		str_contains($element->name, '[]') 	||  	// Doesn't need to be unique 
		(
			$update && 
			$oldElement->name == $element->name && 	// Name didn't change
			$elements &&
			count($elements) == 1
		)
	){

		return $element->name;
	}

	$elementName = $element->name;
	
	$i = '';
	// getElementByName returns false when no match found
	while($simForms->getElementByName($elementName)){
		$i++;
		
		$elementName = "{$element->name}_$i";
	}

	//update the name
	if($i != ''){
		$element->name .= "_$i";
	}

	// only update previous submissions when an update of the name of existing element took place
	if(!$update){
		return $element->name;
	}

	// Update the name in the form elements array
	foreach($simForms->formElements as &$el){
		if($el->id == $element->id){
			$el->name	= $element->name;
			break;
		}
	}

	// update js
	$simForms->createJs();

	// Update column settings
	$displayFormResults	= new DisplayFormResults(['form-id' => $simForms->formData->id]);

	$query						= "SELECT * FROM {$displayFormResults->shortcodeTable} WHERE form_id = '{$simForms->formData->id}'";
	foreach($wpdb->get_results($query) as $data){
		//$displayFormResults->shortcodeId	= $data->id;
		//$displayFormResults->loadShortcodeData();
		$columnSettings	= $displayFormResults->addColumnSetting($element);
		if($columnSettings === false){
			continue;
		}

		saveColumnSettings($columnSettings, $displayFormResults->shortcodeId);
	}

	// Update submission data
	$displayFormResults->showArchived	= true;
	$displayFormResults->getForm($simForms->formData->id);
	$displayFormResults->parseSubmissions(null, null, true);

	$submitForm	= new SubmitForm();

	foreach($displayFormResults->submissions as $submission){
		if(isset($submission->formresults[$oldElement->name])){
			$submission->formresults[$element->name]	= $submission->formresults[$oldElement->name];
			unset($submission->formresults[$oldElement->name]);
		}
		$wpdb->update(
			$submitForm->submissionTableName,
			array(
				'formresults'	=> maybe_serialize($submission->formresults),
			),
			array(
				'id'			=> $submission->id
			)
			);
	}

	return $element->name;
}

function prepareProperties($prop){
	if(is_array($prop)){
		foreach($prop as &$p){
			prepareProperties($p);
		}
	}else{
		$prop 	= wp_kses_post(wp_kses_stripslashes($prop));
		$prop	= str_replace('\\\\', '\\', $prop);
		$prop	= str_replace("\\'", "'", $prop);
	}

	return $prop;
}

function copyFormElement(){
	return addFormElement(true);
}

// DONE
function addFormElement($copy=false){
	global $wpdb;

	$simForms	= new SaveFormSettings();
	$simForms->getForm($_POST['form-id']);

	$index		= 0;
	$oldElement	= new stdClass();

	//copy an existing element
	if($copy === true){
		$element			= $simForms->getElementById($_POST['element-id']);

		$element->name		= $element->nicename;

		$element->infotext	= $element->text;
	}

	// Get element from $_POST
	else{
		$element			= $_POST["formfield"];
		$element			= (object)$element;

		// make sure all data is clean
		foreach($element as $prop => $val){
			if(empty($val)){
				continue;
			}

			$element->$prop = prepareProperties($val);

			if($val == "true"){
				$val 	= true;
			}

			if(is_array($val)){
				$val	= serialize($val);
			}else{
				$val	= SIM\deslash($val);
			}
		}
	}

	if(is_numeric($_POST['element-id'])){
		if(!$copy){
			$update			= true;
		}

		$element->id	= $_POST['element-id'];

		$oldElement		= $simForms->getElementById($element->id);

		//$index			= $oldElement->index;
	}else{
		$update	= false;
	}
	
	if($element->type == 'php'){
		//we store the functionname in the html variable replace any double \ with a single \
		$element->name			= $element->functionname;
		
		//only continue if the function exists
		if ( ! function_exists( $element->functionname ) ){
			return new WP_Error('forms', "A function with name $element->functionname does not exist!");
		}
	}
	
	if(in_array($element->type, ['label', 'button', 'formstep'])){
		$element->name	= $element->text;
	}elseif(empty($element->name)){
		return new \WP_Error('Error', "Please enter a formfieldname");
	}

	$element->nicename	= ucfirst(trim($element->name, '[]'));

	if(
		in_array($element->type, $simForms->nonInputs) 		&& 	// this is a non-input
		$element->type != 'datalist'						&& 	// but not a datalist
		!str_contains($element->name, $element->type)			// and the type is not yet added to the name
	){
		$element->name	.= '_'.$element->type;
	}
	
	//Give an unique name
	$element->name		= getUniqueName($element, $update, $oldElement, $simForms);
	if(is_wp_error($element->name)){
		return $element->name;
	}

	//Store info text in text column
	if(in_array($element->type, ['info', 'p'])){
		$element->text 	= wp_kses_post($element->infotext);
	}
	
	if($update){
		$message								= "Succesfully updated '{$element->name}'";
		$result									= $simForms->updateFormElement($element);
		if(is_wp_error($result)){
			return $result;
		}
	}else{
		$message								= "Succesfully added '{$element->name}' to this form";
		if(!is_numeric($_POST['insertafter'])){
			$element->priority	= $wpdb->get_var( "SELECT COUNT(`id`) FROM `{$simForms->elTableName}` WHERE `form_id`={$element->form_id}") +1;
		}else{
			$element->priority	= $_POST['insertafter'] + 1;
		}

		$element->id	= $simForms->insertElement($element);

		if(!empty($_POST['extra'])){
			// The current indexes without the new element
			$newIndexes 	= (array)json_decode(sanitize_text_field(stripslashes($_POST['extra'])));

			// The new element has an unknow id of -1, replace it with the real id.
			$newIndexes[$element->id]	= $newIndexes[-1];
			unset($newIndexes[-1]);
			
			$simForms->reorderElements($newIndexes, $element);
		}
	}
		
	$formBuilderForm	= new FormBuilderForm();
	$formBuilderForm->getForm($_POST['form-id']);

	$html = $formBuilderForm->buildHtml($element, $index);
	
	return [
		'message'		=> $message,
		'html'			=> $html
	];
}

// DONE
function removeElement(){
	global $wpdb;

	$formBuilder	= new SaveFormSettings();

	$elementId		= $_POST['elementindex'];

	$wpdb->delete(
		$formBuilder->elTableName,
		['id' => $elementId],
	);

	// Fix priorities
	// Get all elements of this form
	$formBuilder->getAllFormElements('priority', $_POST['form-id']);
	
	//Loop over all elements and give them the new priority
	foreach($formBuilder->formElements as $key=>$el){
		if($el->priority != $key + 1){
			$el->priority = $key + 1;
			//Update the database

			$formBuilder->updatePriority($el);
		}
	}

	return "Succesfully removed the element";
}

// DONE
function requestFormElement(){
	$formBuilderForm				= new FormBuilderForm();
	
	$formId 				= $_POST['form-id'];
	$elementId 				= $_POST['element-id'];
	
	$formBuilderForm->getForm($formId);
	
	$conditionForm			= $formBuilderForm->elementConditionsForm($elementId);

	$elementForm			= $formBuilderForm->elementBuilderForm($elementId);
	
	return [
		'elementForm'		=> $elementForm,
		'conditionsHtml'	=> $conditionForm
	];
}

// DONE
function reorderFormElements(){
	$formBuilder			= new SaveFormSettings();

	$formBuilder->formId	= $_POST['form-id'];
	
	$newIndexes 			= (array)json_decode(sanitize_text_field(stripslashes($_POST['indexes'])));

	$element				= $formBuilder->getElementById($_POST['el-id']);
	
	$formBuilder->reorderElements($newIndexes, $element);
	
	return "Succesfully saved new form order";
}

// DONE
function editFormfieldWidth(){
	$formBuilder	= new SaveFormSettings();
	
	$elementId 		= $_POST['elementid'];
	$element		= $formBuilder->getElementById($elementId);
	
	$newwidth 		= $_POST['new-width'];
	$element->width = min($newwidth,100);
	
	$formBuilder->updateFormElement($element);
	
	return "Succesfully updated formelement width to $newwidth%";
}

// DONE
function requestFormConditionsHtml(){
	$formBuilder	= new FormBuilderForm();

	$elementID = $_POST['elementid'];
	
	$formBuilder->getForm($_POST['form-id']);
	
	return $formBuilder->elementConditionsForm($elementID);
}

// DONE
function saveElementConditions(){
	$formBuilder		= new SaveFormSettings();

	$elementId			= $_POST['elementid'];
	if(!$elementId){
		return new \WP_Error('forms', "First save the element before adding conditions to it");
	}
	$formId				= $_POST['form-id'];
	
	$formBuilder->getForm($formId);
	
	$element 			= $formBuilder->getElementById($elementId);
	
	$elementConditions	= $_POST['element-conditions'];
	if(empty($elementConditions)){
		$element->conditions	= '';
		
		$message = "Succesfully removed all conditions for {$element->name}";
	}else{
		$element->conditions 	= serialize($elementConditions);
		
		$message 				= "Succesfully updated conditions for {$element->name}";
	}

	$formBuilder->updateFormElement($element);
	
	//Create new js
	$errors		 = $formBuilder->createJs();

	if(is_wp_error($errors)){
		return $errors;
	}elseif(!empty($errors)){
		$message	.= "\n\nThere were some errors:\n";
		$message	.= implode("\n", $errors);
	}

	return $message;
}

// DONE
function saveFormSettings(){
	$formBuilder			= new SaveFormSettings();
	
	$formSettings 			= $_POST;
	unset($formSettings['_wpnonce']);
	unset($formSettings['form-id']);

	$formBuilder->formName	= $formSettings['form_name'];
	
	//remove double slashes
	$formSettings['upload_path']	= str_replace('\\\\', '\\', $formSettings['upload_path']);
	
	$formBuilder->maybeInsertForm($_POST['form-id']);
	
	$result	= $formBuilder->updateFormSettings($_POST['form-id'], $formSettings);
	
	if(is_wp_error($result)){
		return $result;
	}
	return "Succesfully saved your form settings";
}

// DONE
function saveFormEmails(){
	global $wpdb;
	
	$formBuilder	= new SaveFormSettings();
	$formBuilder->getForm($_POST['form-id']);
	
	$formEmails 	= $_POST['emails'];

	// Remove deleted emails
	$existingEmails	= $wpdb->get_col("SELECT id FROM {$formBuilder->formEmailTable} WHERE form_id = {$_POST['form-id']}");
	$emailsToKeep	= array_column($formEmails, 'email-id');
	$emailsToDelete	= array_diff($existingEmails, $emailsToKeep);
	if(!empty($emailsToDelete)){
		$idsToDelete	= implode(',', $emailsToDelete);
		$wpdb->query("DELETE FROM {$formBuilder->formEmailTable} WHERE id IN ($idsToDelete)");
	}	
	
	// Update each email
	foreach($formEmails as &$email){
		$email['message']	= trim(SIM\deslash($email['message']));

		$result	= $formBuilder->insertOrUpdateData($formBuilder->formEmailTable, $email, ['id' => $email['email-id']]);
		
		if(is_wp_error($result)){
			return $result;
		}
	}
	
	return "Succesfully saved your form e-mail configuration";
}