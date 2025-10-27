<?php
namespace SIM\FORMS;
use ParseSplittedFormResults;
use SIM;
use WP_Embed;
use WP_Error;

class SaveFormSettings extends SimForms{
	use CreateJs;

	protected $tableFormats;

	public function __construct(){
		parent::__construct();

		$this->tableFormats();
	}

	/**
	 * Defines the formats of each column in each table for use in $wpdb->insert and $wpdb->update
	 */
	private function tableFormats(){
		// Form Settings
		$formats			= [
			'name'					=> '%s',
			'version'				=> '%s',
			'button_text'			=> '%s',
			'succes_message'		=> '%s',
			'include_id'			=> '%d',
			'form_name'				=> '%s',
			'save_in_meta'			=> '%d',
			'reminder_frequency'	=> '%s',
			'reminder_amount'		=> '%s',
			'reminder_period'		=> '%s',
			'reminder_conditions'	=> '%s',
			'reminder_startdate'	=> '%s',
			'form_url'				=> '%s',
			'actions'				=> '%s',
			'autoarchive'			=> '%d',
			'autoarchive_el'		=> '%d',
			'autoarchive_value'		=> '%s',
			'split'					=> '%s',
			'full_right_roles'		=> '%s',
			'submit_others_form'	=> '%s',
			'upload_path'			=> '%s'
		];

		$this->tableFormats[$this->tableName]			= apply_filters('forms-form-table-formats', $formats, $this);

		// From Reminder Settings
		$formats			= [
			'form_id'				=> '%d',
			'frequency'				=> '%d',
			'period'				=> '%s',
			'reminder_startdate'	=> '%s',
			'reminder_amount'		=> '%d',
			'reminder_period'		=> '%s',
			'window_start'			=> '%s',
			'window_end'			=> '%s',
			'conditions'			=> '%s'
		];

		$this->tableFormats[$this->formReminderTable]			= apply_filters('forms-form-reminder-formats', $formats, $this);

		// Form Elements
		$formats		= [
			'form_id'				=> '%d',
			'type'					=> '%s',
			'priority'				=> '%d',
			'width'					=> '%d',
			'functionname'			=> '%s',
			'foldername'			=> '%s',
			'name'					=> '%s',
			'nicename'				=> '%s',
			'text'					=> '%s',
			'html'					=> '%s',
			'valuelist'				=> '%s',
			'default_value'			=> '%s',
			'default_array_value'	=> '%s',
			'options'				=> '%s',
			'required'				=> '%d',
			'mandatory'				=> '%d',
			'recommended'			=> '%d',
			'wrap'					=> '%d',
			'hidden'				=> '%d',
			'multiple'				=> '%d',
			'library'				=> '%d',
			'editimage'				=> '%d',
		  	'conditions'			=> '%s',
			'remove'				=> '%s',
			'add'					=> '%s',
		];

		$this->tableFormats[$this->elTableName]		= apply_filters('forms-element-table-formats', $formats, $this);

		// Form Emails
		$formats	= [
			'form_id'				=> '%d',	
			'email_trigger'			=> '%s',	
			'submitted_trigger'		=> '%s',	
			'conditional_field'		=> '%s',	
			'conditional_fields'	=> '%s',
			'conditional_value'		=> '%s',
			'from_email'			=> '%s',
			'from'					=> '%s',
			'conditional_from_email'=> '%s',
			'else_from'				=> '%s',
			'email_to'				=> '%s',
			'to'					=> '%s',
			'conditional_email_to'	=> '%s',
			'else_to'				=> '%s',
			'subject'				=> '%s',
			'message'				=> '%s',
			'headers'				=> '%s',
			'files'					=> '%s'
		];

		$this->tableFormats[$this->formEmailTable]	= apply_filters('forms-email-table-formats', $formats, $this);

		// Form Submissions
		$formats	= [
			'form_id'				=> '%d',	
			'timecreated'			=> '%s',	
			'timelastedited'		=> '%s',	
			'userid'				=> '%d',
			'formresults'			=> '%s',
			'archived'				=> '%d',
			'archivedsubs'			=> '%s'
		];

		$this->tableFormats[$this->submissionTableName]	= apply_filters('forms-submission-table-formats', $formats, $this);

		// Table Settings
		$formats	= [
			'form_id'				=> '%d',
			'title' 				=> '%s',
			'default_sort'			=> '%s',	
			'sort_direction'		=> '%s',
			'filter'				=> '%s',	
			'hide_row'				=> '%d',
			'result_type'			=> '%s',
			'split_table'			=> '%s',
			'archived'				=> '%d',
			'view_right_roles'		=> '%s',
			'edit_right_roles'		=> '%s'
		];

		$this->tableFormats[$this->shortcodeTable]	= apply_filters('forms-shortcode-table-formats', $formats, $this);

		// Column Settings
		$formats	= [
			'shortcode_id'			=> '%d',
			'element_id'			=> '%d',
			'width'					=> '%d',
			'show'					=> '%d',	
			'name'					=> '%s',	
			'nice_name'				=> '%s',
			'priority'				=> '%d',	
			'view_right_roles'		=> '%s',
			'edit_right_roles'		=> '%s'
		];

		$this->tableFormats[$this->shortcodeColumnSettingsTable]	= apply_filters('forms-shortcode-settings-table-formats', $formats, $this);

		// Sort formats by key to make sure they are in the same order as the data
		foreach($this->tableFormats as &$format){
			ksort($format);
		}
	}

	public function getUniqueName($element, $update, $oldElement){
		global $wpdb;

		// Remove any ' from the name, replace white space with _ as php does this automatically in post
		$element->name	= str_replace(["\\'", " "], ['', "_"], $element->name);

		// Make sure we only are working on the name
		$element->name	= end(explode('\\', $element->name));

		// Make lowercase
		$element->name	= strtolower($element->name);

		// Remove ending _
		$element->name	= trim($element->name, ' \n\r\t\v\0_');

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
		while($this->getElementByName($elementName)){
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

		// Update column settings
		$displayFormResults	= new DisplayFormResults(['form-id' => $this->formData->id]);

		$query						= "SELECT * FROM {$displayFormResults->shortcodeTable} WHERE form_id = '{$this->formData->id}'";
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
		$displayFormResults->getForm($this->formData->id);
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

	/**
	 * Prepares an data for storages in db
	 * 
	 * @param 	object|array	$data		The data to be stored
	 * @param	array			$formats	The format the data should follow
	 * @param	array			$where		The where clause for updates
	 * @param	array			$whereFormat The format of the where clause
	 * 
	 * @return	array						The data ready for db injection
	 */
	public function insertOrUpdateData($table, &$data, $where=[], $whereFormat=['%d']){
		global $wpdb;

		$shouldObject	= false;
		if(is_object($data)){
			$data			= (array)$data;
			$shouldObject	= true;
		}

		// fix possible where indexes
		foreach($where as &$val){
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
		}
		unset($val);

		$formats	= $this->tableFormats[$table];

		// Fix indexes
		foreach($data as $index => $value){
			unset($data[$index]);
			
			$data[str_replace('-', '_', $index)] = maybe_serialize($value);
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
		}

		// Nothing got updated, maybe we should create instead of update
		if($wpdb->rows_affected == false){
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
	public function updateFormReminder($formId='', $settings){
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
		foreach($settings as $elementId => $column){
			if(!is_array($column)){
				continue;
			}

			$priority++;
			$column['priority']	= $priority;

			$column['element_id']	= $elementId;
			if(empty($column['shortcode_id']) && is_numeric($shortcodeId)){
				$column['shortcode_id']	= $shortcodeId;
			}elseif(empty($column['shortcode_id']) && isset($_POST['shortcode-id']) && is_numeric($_POST['shortcode-id'])){
				$column['shortcode_id']	= $_POST['shortcode-id'];
			}
			
			//if there are edit rights defined
			if(!empty($column['edit_right_roles'])){
				//create view array if it does not exist
				if(!is_array($column['view_right_roles'])){
					$column['view_right_roles'] = [];
				}
				
				//merge and save
				$column['view_right_roles'] = array_merge($column['view_right_roles'], $column['edit_right_roles']);
			}

			$result	= $this->insertOrUpdateData($this->shortcodeColumnSettingsTable, $column, ['id' => 'column_id']);

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
