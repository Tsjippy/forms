<?php
namespace SIM\FORMS;
use ParseSplittedFormResults;
use SIM;
use WP_Embed;
use WP_Error;

class SaveFormSettings extends SimForms{
	use CreateJs;

	public function __construct(){
		parent::__construct();
	}

	public function getUniqueName($element, $update, $oldElement){
		global $wpdb;

		// Make sure we only are working on the name
		$element->name	= end(explode('\\', $element->name));

		// Replace spaces with _
		$element->name	= str_replace(" ", "_", $element->name);

		// Make lowercase
		$element->name	= strtolower($element->name);

		// Keep only valid chars
		$element->name = preg_replace('/[^a-zA-Z0-9_\[\]]/', '', $element->name);

		// Remove ending _
		$element->name	= trim($element->name, " \n\r\t\v\0_");

		// Make sure the first char is a letter or _
		$element->name[0] = preg_replace('/[^a-zA-Z_]/', '_', $element->name[0]);

		// Check if name is unique
		// Get all elements with this name
		$elements		= $this->getElementByName($element->name, '', false);
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
		while(true){
			$existingElement	= $this->getElementByName($elementName);

			if(
				!$existingElement || 							// No existing element found
				$existingElement->name != $element->name ||		// Different name found
				( 
					$update && 									// Updating existing element
					$existingElement->id == $element->id 		// Same element
				) 
			){
				break;
			}

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
		foreach($this->formElements as &$el){
			if($el->id == $element->id){
				$el->name	= $element->name;
				break;
			}
		}

		// update js
		$this->createJs();

		return $element->name;
	}

	/**
	 * Prepares an data for storages in db
	 * 
	 * @param	string			$table		The table to insert/update the data into	
	 * @param 	object|array	$data		The data to be stored
	 * @param	array			$formats	The format the data should follow
	 * @param	array			$where		The where clause for updates
	 * @param	array			$whereFormat The format of the where clause
	 * 
	 * @return	array						The data ready for db injection
	 */
	public function insertOrUpdateData($table, &$data, $where=[], $whereFormat=['%d']){
		if(empty($table) || empty($data)){
			return new WP_Error('forms', 'Please supply a table and data to insert/update');
		}
		
		global $wpdb;

		$shouldObject	= false;
		if(is_object($data)){
			$data			= (array)$data;
			$shouldObject	= true;
		}

		// fix possible where indexes
		foreach($where as $index => &$val){
			if(!is_string($val) ){
				continue;
			}

			if(!is_numeric($val) && !empty($data[$val])){
				$val	= $data[$val];
				continue;
			}

			$newVal	= str_replace('_', '-', $val);
			if(!is_numeric($val) && !empty($data[$newVal])){
				$val	= $data[$newVal];
			}

			if(empty($val)){
				unset($where[$index]);
			}
		}
		unset($val);

		$formats	= $this->tableFormats[$table];

		// Fix indexes
		foreach($data as $index => $value){
			unset($data[$index]);

			$value	= SIM\cleanUpNestedArray($value);

			if(!empty($value)){
				$value	= maybe_serialize($value);
			}
			
			$data[str_replace('-', '_', $index)] = $value;
		}

		// Remove data without a column in the db
		foreach(array_diff_key($data, $formats) as $key => $val){
			unset($data[$key]);
		}

		// Remove unnecesary formats
		foreach(array_diff_key($formats, $data) as $key => $val){
			unset($formats[$key]);	
		}

		ksort($data);

		if(empty($where)){
			$wpdb->insert(
				$table,
				$data,
				$formats
			);

			$result	= $wpdb->insert_id;
		}else{
			//Update element
			$wpdb->update(
				$table,
				$data,
				$where,
				$formats,
				$whereFormat
			);

			$result	= $wpdb->rows_affected;

			// Nothing got updated, maybe we should create instead of update
			if($result == false){
				// check if this already exists
				$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE ".implode('=%s AND ', array_keys($where))."=%s", array_values($where)));
				
				if($wpdb->num_rows === 0){
					// Insert instead
					$wpdb->insert(
						$table,
						$data,
						$formats
					);

					$result	= $wpdb->insert_id;
				}
			}
		}

		// unserialize again
		foreach($data as $index => &$value){
			if(!empty($value)){
				$value	= maybe_unserialize($value);
			}
		}

		if($shouldObject){
			$data	= (object)$data;
		}
		
		if($wpdb->last_error !== ''){
			return new WP_Error('forms', $wpdb->last_error);
		}

		return $result;
	}

	/**
	 * Change an existing form element in the db
	 *
	 * @param	object|array	$element	The new element data
	 *
	 * @return	true|WP_Error				The result or error on failure
	 */
	public function updateFormElement($element){
		global $wpdb;

		$elId		= $element->id;
		$oldElement	= $this->getElementById($elId);

		$this->insertOrUpdateData($this->elTableName, $element, ['id' => $elId]);

		$element->id	= $elId;

		// Update the element in the formElements array
		$this->formElements[$oldElement->index]	= (object)$element;

		if($this->formData == null){
			$this->getForm($_POST['form-id']);
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
		$id	= $this->insertOrUpdateData($this->elTableName, $element);

		return $id;
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
	 * Update form settings
	 */
	public function updateFormSettings($formId='', $settings=''){
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

		$settings	= apply_filters('sim-forms-before-saving-settings', $settings, $this, $formId);

		$result	= $this->insertOrUpdateData($this->tableName, $settings, ['id' => $formId]);
		if(is_wp_error($result)){
			return $result;
		}

		return true;
	}

	/**
	 * Stores the form reminder settings in the db
	 */
	public function updateFormReminder($formId, $settings){
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

		$result	= $this->insertOrUpdateData($this->formReminderTable, $settings, ['form_id' => $formId]);
		if(is_wp_error($result)){
			return $result;
		}

		do_action('sim-after-form-reminder-save', $settings, $this);

		return true;
	}

	/**
	 * Saves the column settings for a table shortcode
	 * @param	array		$settings		The column settings to be saved
	 * @param	int|string	$shortcodeId	The id of the shortcode these settings belong
	 * @return	true|WP_Error				The result or error on failure
	 */
	public function saveColumnSettings($settings=[], $shortcodeId=''){
		$priority	= 0;

		if( empty($shortcodeId) && isset($_POST['shortcode-id']) && is_numeric($_POST['shortcode-id'])){
			$shortcodeId	= $_POST['shortcode-id'];
		}

		foreach($settings as $elementId => $column){
			if(!is_array($column)){
				continue;
			}

			$priority++;
			$column['priority']		= $priority;

			$column['element_id']	= $elementId;

			$column['shortcode_id']	= $shortcodeId;
			
			//if there are edit rights defined
			if(!empty($column['edit-right-roles'])){
				//create view array if it does not exist
				if(empty($column['view-right-roles']) || !is_array($column['view-right-roles'])){
					$column['view-right-roles'] = [];
				}
				
				//merge and save
				$column['view-right-roles'] = array_merge($column['view-right-roles'], $column['edit-right-roles']);
			}

			$where	= [];

			if(!empty($column['column-id'])){
				$where	= [
					'id'	=> $column['column-id']
				];
			}

			$result	= $this->insertOrUpdateData($this->shortcodeColumnSettingsTable, $column, $where);

			if(is_wp_error($result)){
				return $result;
			}
		}
		
		return true;
	}

	/**
	 * Updates the form e-mails in the db
	 */
	public function saveFormEmails($formEmails, $formId){
		global $wpdb;

		// Remove deleted emails
		$existingEmails	= $wpdb->get_col("SELECT id FROM {$this->formEmailTable} WHERE form_id = $formId");

		$emailsToKeep	= array_column($formEmails, 'email-id');

		$emailsToDelete	= array_diff($existingEmails, $emailsToKeep);

		// Remove any deleted e-mails
		if(!empty($emailsToDelete)){
			$idsToDelete	= implode(',', $emailsToDelete);

			$wpdb->query("DELETE FROM {$this->formEmailTable} WHERE id IN ($idsToDelete)");
		}	
		
		// Update each email
		foreach($formEmails as $email){
			$email['form_id']	= $formId;
			$email['message']	= trim(SIM\deslash($email['message']));

			$where				= [];

			// Its an update to an existing one
			if(!empty($email['email-id'])){
				$where			= [
					'id' => $email['email-id']
				];
			}

			$result	= $this->insertOrUpdateData($this->formEmailTable, $email, $where);

			if(is_wp_error($result)){
				return $result;
			}
		}

		return $result;
	}
}
