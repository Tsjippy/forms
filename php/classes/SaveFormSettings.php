<?php
namespace SIM\FORMS;
use ParseSplittedFormResults;
use SIM;
use WP_Embed;
use WP_Error;

class SaveFormSettings extends SimForms{
	use CreateJs;

	/**
	 * Change an existing form element in the db
	 *
	 * @param	object|array	$element	The new element data
	 *
	 * @return	true|WP_Error				The result or error on failure
	 */
	public function updateFormElement($element){
		global $wpdb;

		unset($element->index);
		
		//Update element
		$wpdb->update(
			$this->elTableName,
			(array)$element,
			array(
				'id'		=> $element->id,
			),
		);

		if($wpdb->last_error !== ''){
			return new WP_Error('forms', $wpdb->last_error);
		}

		// Update the element in the formElements array
		$oldElement								= $this->getElementById($element->id);
		$this->formElements[$oldElement->index]	= $element;

		if($this->formData == null){
			$this->getForm($_POST['formid']);
		}

		$formVersion	= 1;
		if(is_numeric($this->formData->version)){
			$formVersion	= $this->formData->version + 1;
		}

		//Update form version
		$result = $wpdb->update(
			$this->tableName,
			['version'	=> $formVersion],
			['id'		=> $this->formData->id],
		);
		
		if($wpdb->last_error !== ''){
			return new WP_Error('forms', $wpdb->last_error);
		}

		do_action('sim-after-formelement-updated', $element, $this, $oldElement);

		return $result;
	}

	/**
	 * Inserts a new element in the db
	 *
	 * @param	object|array	$element	The new element to insert
	 *
	 * @return	int							The new element id
	 */
	public function insertElement($element){
		global $wpdb;
		
		$wpdb->insert(
			$this->elTableName,
			(array)$element
		);

		return $wpdb->insert_id;
	}

	/**
	 * Change the priority of an element
	 *
	 * @param	object|array	$element	The element to change the priority of
	 *
	 * @return	array|WP_Error				The result or error on failure
	 */
	public function updatePriority($element){
		global $wpdb;

		//Update the database
		$result = $wpdb->update($this->elTableName,
			array(
				'priority'	=> $element->priority
			),
			array(
				'id'		=> $element->id
			),
		);
		
		if($wpdb->last_error !== ''){
			return new WP_Error('forms', $wpdb->print_error());
		}

		return $result;
	}

	/**
	 * Change the order of form elements
	 *
	 * @param	array			$newIndexes		The updated array of element id - priority pairs
	 * @param	object			$element		The element to change the priority of
	 */
	public function reorderElements(array $newIndexes, $element) {
		if(!isset($this->formId) && !empty($element) && isset($element->form_id)){
			$this->formId	= $element->form_id;
		}

		// Get all elements of this form
		$this->getAllFormElements('priority', $this->formId, true);

		foreach($this->formElements	as &$element){
			if(is_numeric($newIndexes[$element->id]) && $element->priority != $newIndexes[$element->id]){
				$element->priority	= $newIndexes[$element->id];

				$this->updatePriority($element);
			}
		}
	}

	/**
	 * Checks if the current form exists in the db. If not, inserts it
	 */
	public function maybeInsertForm($formId=''){
		global $wpdb;

		if(!isset($this->formName)){
			return new WP_ERROR('forms', 'No formname given');
		}
		
		$query	= "SELECT * FROM {$this->tableName} WHERE `name` = '{$this->formName}'";
		if(is_numeric($formId)){
			$query	.= " OR id=$formId";
		}
		//check if form row already exists
		if(!$wpdb->get_var($query)){
			//Create a new form row
			$this->insertForm();
		}
	}

	/**
	 * Deletes a form
	 *
	 * @param	int		$formId	The id of the form to be deleted
	 * @param	int		$pageId	The id of a page with a formbuilder shortcode
	 *
	 * @return	string			The deletion result
	*/
	public  function deleteForm($formId){
		global $wpdb;

		// Remove the form
		$wpdb->delete(
			$this->tableName,
			['id' => $formId],
			['%d']
		);

		// remove the form elements
		$wpdb->delete(
			$this->elTableName,
			['form_id' => $formId],
			['%d']
		);

		// remove the form submissions
		$wpdb->delete(
			$this->submissionTableName,
			['form_id' => $formId],
			['%d']
		);

		$query		= "SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[formbuilder formname={$this->formData->name}]%'";
		$results	= $wpdb->get_results ($query);

		// remove the shortcode from the page
		foreach($results as $postId){
			$post	= get_post($postId);

			$post->post_content	= str_replace("[formbuilder formname={$this->formData->name}]", '', $post->post_content);

			// delete post
			if(empty($post->post_content)){
				wp_delete_post($post->ID);
			}else{
				wp_update_post( $post );
			}
		}
	}

	/**
	 * Update form settings
	 */
	public function updateFormSettings($formId='', $settings=''){
		global $wpdb;

		if(empty($formId)){
			if(!empty($this->formData->id)){
				$formId	= $this->formData->id;
			}else{
				return new \WP_Error('Error', 'Please supply a form id');
			}
		}

		if(empty($settings)){
			return new \WP_Error('Error', 'Please supply the form settings');
		}

		$newSettings	= [
			'button_text'			=> isset($settings['button_text']) 			? $settings['button_text'] 							: null,
			'succes_message'		=> isset($settings['succes_message']) 		? $settings['succes_message'] 						: null,
			'include_id'			=> isset($settings['include_id']) 			? true 												: false,
			'form_name'				=> isset($settings['form_name']) 			? $settings['form_name'] 							: null,
			'save_in_meta'			=> isset($settings['save_in_meta']) 		? true 												: false,
			'reminder_frequency'	=> isset($settings['reminder_frequency']) 	? $settings['reminder_frequency'] 					: null,
			'reminder_amount'		=> isset($settings['reminder_amount']) 		? $settings['reminder_amount'] 						: null,
			'reminder_period'		=> isset($settings['reminder_period']) 		? $settings['reminder_period'] 						: null,
			'reminder_conditions'	=> isset($settings['reminder_conditions']) 	? maybe_serialize($settings['reminder_conditions']) : null,
			'reminder_startdate'	=> isset($settings['reminder_startdate']) 	? $settings['reminder_startdate'] 					: null,
			'form_url'				=> isset($settings['form_url']) 			? $settings['form_url'] 							: null,
			'form_reset'			=> isset($settings['form_reset']) 			? true 												: false,
			'actions'				=> isset($settings['actions']) 				? maybe_serialize($settings['actions']) 			: null,
			'autoarchive'			=> isset($settings['autoarchive']) 			? true 												: false,
			'autoarchive_el'		=> isset($settings['autoarchive_el']) 		? $settings['autoarchive_el'] 						: null,
			'autoarchive_value'		=> isset($settings['autoarchive_value']) 	? $settings['autoarchive_value'] 					: null,
			'split'					=> isset($settings['split']) 				? maybe_serialize($settings['split'])				: null,
			'full_right_roles'		=> isset($settings['full_right_roles']) 	? maybe_serialize($settings['full_right_roles'])	: null,
			'submit_others_form'	=> isset($settings['submit_others_form']) 	? maybe_serialize($settings['submit_others_form'])	: null,
			'upload_path'			=> isset($settings['upload_path']) 			? $settings['upload_path'] 							: null
		];

		$newSettings	= apply_filters('sim-forms-before-saving-settings', $newSettings, $this, $formId);

		$wpdb->update($this->tableName,
			$newSettings,
			array(
				'id'		=> $formId,
			),
		);
		
		if($wpdb->last_error !== ''){
			return new \WP_Error('Error', $wpdb->print_error());
		}

		return true;
	}
}
