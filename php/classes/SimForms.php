<?php
namespace SIM\FORMS;
use SIM;
use WP_Error;

class SimForms{
	
	public $isFormStep;
	public $isMultiStepForm;
	public $formStepCounter;
	public $submissionTableName;
	public $tableName;
	public $elTableName;
	public $nonInputs;
	public $multiInputsHtml;
	public $user;
	public $userRoles;
	public $userId;
	public $pageSize;
	public $multiwrap;
	public $submitRoles;
	public $showArchived;
	public $editRights;
	public $formName;
	public $formData;
	public $forms;
	public $formId;
	public $formElements;
	public $jsFileName;
	public $names;
	public $shortcodeId;
	public $onlyOwn;
	public $all;
	public $submission;
	public $submissionTableFormats;
	public $formTableFormats;
	public $formEmailTable;
	public $formEmailTableFormats;
	public $shortcodeTable;
	public $shortcodeTableFormats;
	public $shortcodeColumnSettingsTable;
	public $shortcodeTableColumnFormats;
	public $elementTableFormats;
	public $emailSettings;

	public function __construct(){
		global $wpdb;

		$this->isFormStep					= false;
		$this->isMultiStepForm				= '';
		$this->formStepCounter				= 0;
		$this->submissionTableName			= $wpdb->prefix . 'sim_form_submissions';
		$this->tableName					= $wpdb->prefix . 'sim_forms';
		$this->elTableName					= $wpdb->prefix . 'sim_form_elements';
		$this->formEmailTable				= $wpdb->prefix . 'sim_form_emails';
		$this->shortcodeColumnSettingsTable	= $wpdb->prefix . 'sim_form_shortcode_column_settings';
		$this->shortcodeTable				= $wpdb->prefix . 'sim_form_shortcodes';
		$this->nonInputs					= ['label', 'button', 'datalist', 'formstep', 'info', 'p', 'php', 'multi-start', 'multi-end', 'div-start', 'div-end'];
		$this->multiInputsHtml				= [];
		$this->user 						= wp_get_current_user();
		$this->userRoles					= $this->user->roles;
		$this->userId						= $this->user->ID;
		if(isset($_REQUEST['all'])){
			$this->pageSize					= 99999;
		}elseif(isset($_REQUEST['pagesize']) && is_numeric($_REQUEST['pagesize'])){
			$this->pageSize					= $_REQUEST['pagesize'];
		}else{
			$this->pageSize					= 100;
		}

		$this->multiwrap					= '';
		$this->submitRoles					= [];
		$this->showArchived					= false;

		//calculate full form rights
		$object		= get_queried_object();
		$postAuthor	= 0;
		if(!empty($object->post_author)){
			$postAuthor	= $object->post_author;
		}elseif(!empty($_REQUEST['post'])){
			$postAuthor	= get_post($_REQUEST['post'])->post_author;
		}elseif(!empty($_POST['form-url'])){
			$postId		= url_to_postid($_POST['form-url']);
			
			if($postId){
				$postAuthor	= get_post($postId)->post_author;
			}
		}else{
			echo '';
		}

		if(array_intersect(['administrator','editor'], $this->userRoles) || $postAuthor == $this->user->ID){
			$this->editRights		= true;
		}else{
			$this->editRights		= false;
		}

		$this->tableFormats();
	}

	/**
	 * Defines the formats of each column in each table for use in $wpdb->insert and $wpdb->update
	 */
	private function tableFormats(){

		$this->formTableFormats			= [
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
			'form_reset'			=> '%d',
			'actions'				=> '%s',
			'autoarchive'			=> '%d',
			'autoarchive_el'		=> '%d',
			'autoarchive_value'		=> '%s',
			'split'					=> '%s',
			'full_right_roles'		=> '%s',
			'submit_others_form'	=> '%s',
			'upload_path'			=> '%s'
		];

		$this->formTableFormats			= apply_filters('forms-form-table-formats', $this->formTableFormats, $this);

		$this->elementTableFormats		= [
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

		$this->elementTableFormats		= apply_filters('forms-element-table-formats', $this->elementTableFormats, $this);

		$this->formEmailTableFormats	= [
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

		$this->formEmailTableFormats	= apply_filters('forms-submission-table-formats', $this->formEmailTableFormats, $this);

		$this->submissionTableFormats	= [
			'form_id'				=> '%d',	
			'timecreated'			=> '%s',	
			'timelastedited'		=> '%s',	
			'userid'				=> '%d',
			'formresults'			=> '%s',
			'archived'				=> '%d',
			'archivedsubs'			=> '%s'
		];

		$this->submissionTableFormats	= apply_filters('forms-submission-table-formats', $this->submissionTableFormats, $this);

		$this->shortcodeTableFormats	= [
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

		$this->shortcodeTableFormats	= apply_filters('forms-submission-table-formats', $this->shortcodeTableFormats, $this);

		$this->shortcodeTableColumnFormats	= [
			'shortcode_id'			=> '%d',
			'form_id'				=> '%d',
			'element_id'			=> '%d',
			'show'					=> '%d',	
			'name'					=> '%s',	
			'nice_name'				=> '%s',	
			'view_right_roles'		=> '%s',
			'edit_right_roles'		=> '%s'
		];

		$this->shortcodeTableColumnFormats	= apply_filters('forms-submission-table-formats', $this->shortcodeTableColumnFormats, $this);

		ksort($this->submissionTableFormats);
		ksort($this->formTableFormats);
		ksort($this->elementTableFormats);
		ksort($this->formEmailTableFormats);
		ksort($this->shortcodeTableFormats);
		ksort($this->shortcodeTableColumnFormats);
	}
	
	/**
	 * Creates the tables for this module
	 */
	public function createDbTables(){
		if ( !function_exists( 'maybe_create_table' ) ) {
			require_once ABSPATH . '/wp-admin/install-helper.php';
		}

		add_option( "forms_db_version", "1.0" );
		
		//only create db if it does not exist
		global $wpdb;
		$charsetCollate = $wpdb->get_charset_collate();

		//Main table
		$sql = "CREATE TABLE {$this->tableName} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name tinytext NOT NULL,
			version text NOT NULL,
			button_text text,
			succes_message text,
			include_id boolean,
			formname text,
			save_in_meta boolean,
			reminder_frequency text,
			reminder_amount text,
			reminder_period text,
			reminder_startdate date,
			reminder_conditions LONGTEXT,
			form_url text,
			form_reset boolean,
			actions text,
			autoarchive boolean,
			autoarchive_el integer,
			autoarchive_value text,
			split text,
			full_right_roles LONGTEXT,
			submit_others_form LONGTEXT,
			emails LONGTEXT,
			upload_path LONGTEXT,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		maybe_create_table($this->tableName, $sql );

		// Form element table
		$sql = "CREATE TABLE {$this->elTableName} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id int NOT NULL,
			type text NOT NULL,
			priority int,
			width int default 100,
			functionname text,
			foldername text,
			name text,
			nicename text,
			text text,
			html text,
			valuelist text,
			default_value text,
			default_array_value text,
			options text,
			required boolean default False,
			mandatory boolean default False,
			recommended boolean default False,
			wrap boolean default False,
			hidden boolean default False,
			multiple boolean default False,
			library boolean default False,
			editimage boolean default False,
		  	conditions longtext,
			warning-conditions longtext,
			add longtext,
			remove longtext,
			PRIMARY KEY  (id)
		  ) $charsetCollate;";
  
		maybe_create_table($this->elTableName, $sql );

		// form e-mails table
		$sql = "CREATE TABLE {$this->formEmailTable} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id int NOT NULL,
			email_trigger tinytext,
			submitted_trigger tinytext,
			conditional_field tinytext,
			conditional_fields longtext,
			conditional_value tinytext,
			from_email tinytext,
			`from` tinytext,
			conditional_from_email longtext,
			else_from tinytext,
			email_to tinytext,
			`to` tinytext,
			conditional_email_to longtext,
			else_to tinytext,
			subject longtext,
			message longtext,
			headers longtext,
			files longtext,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		maybe_create_table($this->formEmailTable, $sql );

		// shortcodeTableSettings table
		$sql = "CREATE TABLE {$this->shortcodeTable} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id int NOT NULL,
			title tinytext,
			default_sort tinytext,
			sort_direction tinytext,
			filter longtext,
			hide_row tinytext,
			result_type tinytext,
			split_table boolean,
			archived boolean,
			`view_right_roles` longtext,
			edit_right_roles longtext,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		maybe_create_table($this->shortcodeTable, $sql );

		// shortcode Column Settings table
		$sql = "CREATE TABLE {$this->shortcodeColumnSettingsTable} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			shortcode_id int NOT NULL,
			form_id int NOT NULL,
			element_id int,
			`show` boolean,
			name tinytext,
			nice_name tinytext,
			view_right_roles longtext,
			edit_right_roles longtext,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		maybe_create_table($this->shortcodeColumnSettingsTable, $sql );

		//submission table
		$sql = "CREATE TABLE {$this->submissionTableName} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id	int NOT NULL,
			timecreated datetime DEFAULT NULL,
			timelastedited datetime DEFAULT NULL,
			userid mediumint(9) NOT NULL,
			formresults longtext NOT NULL,
			archived BOOLEAN,
			archivedsubs tinytext,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		maybe_create_table($this->submissionTableName, $sql );
	}

	/**
	 * Inserts a new form in the db
	 *
	 * @param	string	$name	The form name
	 *
	 * @return	int|WP_Error	The form id or error ion failure
	 */
	public function insertForm($name=''){
		global $wpdb;

		if(empty($name)){
			$name = $this->formName;
		}

		$name	= strtolower($name);

		// Check if name already exists
		$newName	= $name;
		$i			= 1;
		while(true){
			$query	= "SELECT * FROM {$this->tableName} WHERE name = '$newName'";
			$result	= $wpdb->get_results($query);

			if(empty($result)){
				break;
			}

			$newName	= "$name$i";
			$i++;
		}

		$this->formName	= $newName;

		$wpdb->insert(
			$this->tableName,
			array(
				'name'			=> $this->formName,
				'version' 		=> 1
			)
		);
		
		if(!empty($wpdb->last_error)){
			return new \WP_Error('error', $wpdb->print_error());
		}

		return $wpdb->insert_id;
	}
	
	/**
	 * Gets all forms from the db
	 */
	public function getForms(){
		global $wpdb;
		
		$query							= "SELECT * FROM {$this->tableName}";
		
		$this->forms					= $wpdb->get_results($query);
	}

	/**
	 * Load a specific form or creates it if it does not exist
	 *
	 * @param	int		$formId	the form id to load. Default empty
	 */
	public function getForm($formId=''){
		global $wpdb;

		// first check if needed
		if(empty($this->formData) || (
			(
				!empty($this->formId)		&&
				$this->formData->id	!= $this->formId
			)	||
			(
				!empty($this->formName)		&&
				$this->formData->name	!= $this->formName
			)	||
			(
				empty($this->formId)		&&
				!empty($formId)
			)
		)){
			// Get the form data
			$query				= "SELECT * FROM {$this->tableName} WHERE ";
			if(is_numeric($formId)){
				$query	.= "id= '$formId'";
			}elseif(is_numeric($this->formId)){
				$query	.= "id= '$this->formId'";
			}elseif(!empty($this->formName)){
				$query	.= "name= '$this->formName'";
			}else{
				return new \WP_Error('forms', 'No form name or id given');
			}

			$result				= $wpdb->get_results($query);

			// Form does not exist yet
			if(empty($result)){
				global $post;

				$url	= get_page_link($post);

				SIM\printArray("Form requested on {$post->post_type} on $url does not exist. Query used is '$query'");
				$this->insertForm();
				$this->formData 	=  new \stdClass();
			}else{
				$this->formData 					=  (object)$result[0];
				$this->formData->actions			= maybe_unserialize($this->formData->actions);
				$this->formData->split				= maybe_unserialize($this->formData->split);
				$this->formData->full_right_roles	= maybe_unserialize($this->formData->full_right_roles);
				$this->formData->submit_others_form	= maybe_unserialize($this->formData->submit_others_form);				
				
				$formId								= $this->formData->id;
			}
		}

		$this->elementMapper($formId);

		if(!$this->editRights){
			$editRoles	= ['administrator', 'editor'];
			if(!empty($this->formData->full_right_roles)){
				$editRoles	= (array)$this->formData->full_right_roles;
			}

			//calculate full form rights
			$object	= get_queried_object();
			
			if(array_intersect($editRoles, (array)$this->userRoles) || (!empty($object) && $object->post_author == $this->user->ID)){
				$this->editRights		= true;
			}else{
				$this->editRights		= false;
			}
		}

		if(isset($this->formData->submit_others_form)){
			$this->submitRoles	= (array)$this->formData->submit_others_form;
		}
		
		if($wpdb->last_error !== ''){
			SIM\printArray($wpdb->print_error());
		}
		
		$this->jsFileName	= plugin_dir_path(__DIR__)."../js/dynamic/{$this->formData->name}forms";

		return true;
	}

 	/**
	* Retrieves e-mail settings from the database
	*/
	public function getEmailSettings(){
		global $wpdb;
		
		$query = "select * from $this->formEmailTable where form_id={$this->formData->id}";
		
		$this->emailSettings				= $wpdb->get_results($query);

		foreach($this->emailSettings as &$emailSetting){
			foreach($emailSetting as &$setting){
				$setting	= maybe_unserialize($setting);
			}
		}
		
		if(empty($this->emailSettings)){
			$emails[0]["from"]		= "";
			$emails[0]["to"]			= "";
			$emails[0]["subject"]	= "";
			$emails[0]["message"]	= "";
			$emails[0]["headers"]	= "";
			$emails[0]["files"]		= "";

			$this->emailSettings = $emails;
		}
	}

	/**
	 * Creates the element mappers to find elements based on id, name or type
	 *
	 * @param	bool	$force		Whether to requery, default false
	 */
	public function elementMapper($force = false){
		if(
			empty($this->formData) || 
			(
				isset($this->formData->elementMapping) && 
				!empty($this->formData->elementMapping['type']) && 
				!$force
			)
		){
			return;
		}

		//used to find the index of an element based on its unique id, type or name
		$this->formData->elementMapping									= [];
		$this->formData->elementMapping['type']							= [];
		$this->formData->elementMapping['name']							= [];

		$this->getAllFormElements('priority', $this->formData->id, true);

		foreach($this->formElements as $index=>$element){
			$this->formData->elementMapping['id'][$element->id]			= $index;
			$this->formData->elementMapping['name'][$element->name][] 	= $index;
			$this->formData->elementMapping['type'][$element->type][] 	= $index;
		}
	}

	/**
	 * Creates a dropdown with all the forms
	 *
	 * @return	string	the select html
	 */
	public function formSelect(){
		$this->getForms();
		
		$this->names				= [];
		foreach($this->forms as $form){
			$this->names[]			= $form->name;
		}
		
		$html = "<select name='form-selector'>";
			$html .= "<option value=''>---</option>";
			foreach ($this->names as $name){
				$html .= "<option value='$name'>$name</option>";
			}
		$html .= "</select>";
		
		return $html;
	}
	
	/**
	 * Creates the tables for this module
	 */
	public function createDbTable(){
		if ( !function_exists( 'maybe_create_table' ) ) {
			require_once ABSPATH . '/wp-admin/install-helper.php';
		}

		add_option( "forms_db_version", "1.0" );
		
		//only create db if it does not exist
		global $wpdb;
		$charsetCollate = $wpdb->get_charset_collate();

		//Main table
		$sql = "CREATE TABLE {$this->tableName} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name tinytext NOT NULL,
			version text NOT NULL,
			button_text text,
			succes_message text,
			include_id boolean,
			formname text,
			save_in_meta boolean,
			reminder_frequency text,
			reminder_amount text,
			reminder_period text,
			reminder_startdate date,
			reminder_conditions LONGTEXT,
			form_url text,
			form_reset boolean,
			actions text,
			autoarchive boolean,
			autoarchive_el integer,
			autoarchive_value text,
			split text,
			full_right_roles LONGTEXT,
			submit_others_form LONGTEXT,
			emails LONGTEXT,
			upload_path LONGTEXT,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		maybe_create_table($this->tableName, $sql );

		// Form element table
		$sql = "CREATE TABLE {$this->elTableName} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id int NOT NULL,
			type text NOT NULL,
			priority int,
			width int default 100,
			functionname text,
			foldername text,
			name text,
			nicename text,
			text text,
			html text,
			valuelist text,
			default_value text,
			default_array_value text,
			options text,
			required boolean default False,
			mandatory boolean default False,
			recommended boolean default False,
			wrap boolean default False,
			hidden boolean default False,
			multiple boolean default False,
			library boolean default False,
			editimage boolean default False,
		  	conditions longtext,
			warning-conditions longtext,
			add longtext,
			remove longtext,
			PRIMARY KEY  (id)
		  ) $charsetCollate;";
  
		maybe_create_table($this->elTableName, $sql );

		//submission table
		$sql = "CREATE TABLE {$this->submissionTableName} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id	int NOT NULL,
			timecreated datetime DEFAULT NULL,
			timelastedited datetime DEFAULT NULL,
			userid mediumint(9) NOT NULL,
			formresults longtext NOT NULL,
			archived BOOLEAN,
			archivedsubs tinytext,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		maybe_create_table($this->submissionTableName, $sql );
	}
	
	/**
	 * Finds an element by its id
	 *
	 * @param	int		$id		the element id
	 * @param	string	$key	A specific element attribute to return. Default empty
	 *
	 * @return	object|array|string|false			The element or element property
	 */
	public function getElementById($id, $key=''){
		global $post;

		if(empty($id)){
			return false;
		}

		if(!is_numeric($id) && gettype($id) == 'string'){
			return $this->getElementByName($id, $key);
		}
		
		//load if needed
		if(empty($this->formData->elementMapping)){
			$this->getForm();
		}
		
		if(!isset($this->formData->elementMapping['id'][$id])){
			$this->elementMapper(true);

			$url	= get_page_link($post);

			SIM\printArray("Element with id '$id' not found on form '{$this->formData->name}' with id  '{$this->formData->id}' on page $url", false);
			return false;
		}
		$elementIndex	= $this->formData->elementMapping['id'][$id];
					
		$element		= $this->formElements[$elementIndex];
		$element->index	= $elementIndex;
		
		if(empty($key)){
			return $element;
		}else{
			return $element->$key;
		}
	}

	/**
	 * Finds an element by its name
	 *
	 * @param	string	$name	The element name
	 * @param	string	$key	A specific element attribute to return. Default empty
	 * @param	bool	$single	Wheter to return a singel element, default true
	 *
	 * @return	object|array|string|false			The element or an array of elements or an element property of false if not found
	 */
	public function getElementByName($name, $key='', $single=true){
		if(empty($name)){
			return false;
		}
		
		//load if needed
		if(empty($this->formData->elementMapping)){
			$result	= $this->getForm();

			if(is_wp_error($result)){
				return $result;
			}
		}

		if(!isset($this->formData->elementMapping['name'][$name])){
			// first part of the name, remove anything after [
			$nameNew	= explode('[', $name)[0];

			if(isset($this->formData->elementMapping['name'][$nameNew])){
				// remove '[]'
				$name	.= $nameNew;
			}elseif(isset($this->formData->elementMapping['name'][$name.'[]'])){
				// add []
				$name	.= '[]';
			}elseif(!empty($this->formData->split)){
				// only the last part of a splitted name is give
				$mainName	= explode('[', $this->getElementById($this->formData->split[0], 'name'))[0];

				// we already tried adding splits, did not work
				if(str_contains($name, $mainName.'[1][')){
					return false;
				}elseif($mainName == $nameNew){
					$orgName	= trim(end(explode('[',$name)), ']');
					$name		= $mainName."[1][$orgName]";
				}else{
					$name		= $mainName."[0][$name]";
				}

				return $this->getElementByName($name, $key, $single);
			}else{
				//SIM\printArray("Element with name $name not found on form {$this->formData->name} with id {$this->formData->id}");
				return false;
			}
		}
		$elementIndexes	= $this->formData->elementMapping['name'][$name];
	
		$elements		= [];
		
		foreach($elementIndexes as $index){
			$element		= $this->formElements[$index];
			$element->index	= $index;
			$elements[]		= $element;
		}

		if(!$single){
			return $elements;
		}

		$element	= $elements[0];
		
		if(empty($key)){
			return $element;
		}else{
			return $element->$key;
		}
	}

	/**
	 * Finds an element by its type
	 *
	 * @param	string	$type	The element type
	 * @param	bool	$load	Try to load the formdata if empty default true
	 *
	 * @return	object|array|string|false			An array of elements
	 */
	public function getElementByType($type, $load=true){
		if(empty($type)){
			return false;
		}
		
		//load if needed
		if(empty($this->formData->elementMapping['type']) && $load){
			$result	= $this->getForm();

			if(is_wp_error($result)){
				return $result;
			}
		}

		if(!isset($this->formData->elementMapping['type'][$type])){
			//SIM\printArray("Element with id $type not found");
			return false;
		}
		
		$elementIndexes	= $this->formData->elementMapping['type'][$type];

		$elements		= [];
		
		foreach($elementIndexes as $index){
			$element		= $this->formElements[$index];
			$element->index	= $index;
			$elements[]		= $element;
		}

		return $elements;
	}

	/**
	 * Finds the user id element in a form
	 *
	 * @return	string|false|WP_error	the element name, a wp error object or false if no user id element is found
	 */
	public function findUserIdElementName(){
		// find the user id element
		$userIdKey	= 'userid';

		$result		= $this->getElementByName('user_id');

		if(is_wp_error($result)){
			return $result;
		}

		if($result){
			$userIdKey	= 'user_id';
		}elseif($this->getElementByName('userid')){
			$userIdKey	= 'userid';
		}elseif($this->getElementByName('user-id')){
			$userIdKey	= 'user-id';
		}

		return $userIdKey;
	}

	/**
	 * Finds the user name element in a form
	 *
	 * @return	string	the element name or false if no name element is found
	 */
	public function findUserNameElementName(){
		// find the user id element
		$userNameKey	= false;

		if($this->getElementByName('name')){
			$userNameKey	= 'name';
		}elseif($this->getElementByName('fullname')){
			$userNameKey	= 'fullname';
		}elseif($this->getElementByName('firstname')){
			$userNameKey	= 'firstname';
		}elseif($this->getElementByName('lasttname')){
			$userNameKey	= 'lasttname';
		}

		return $userNameKey;
	}

	/**
	 * Finds the phonenumber element in a form
	 *
	 * @return	string	the element name or false if no phonenumber element is found
	 */
	public function findPhoneNumberElementName(){
		// find the user id element
		$phonenumberKey	= false;

		if($this->getElementByName('phone')){
			$phonenumberKey	= 'phone';
		}elseif($this->getElementByName('phonenumber')){
			$phonenumberKey	= 'phonenumber';
		}

		return $phonenumberKey;
	}

	/**
	 * Finds the e-mail element in a form
	 *
	 * @return	string	the element name or false if no e-mail element is found
	 */
	public function findEmailElementName(){
		// find the user id element
		$emailKey	= false;

		if($this->getElementByName('email')){
			$emailKey	= 'email';
		}elseif($this->getElementByName('e-mail')){
			$emailKey	= 'e-mail';
		}

		return $emailKey;
	}

	/**
	 * Get all elements belonging to the current form
	 *
	 * @param	string	$sortCol	the column to sort on. Default empty
	 * @param	int		$formId		The id of the form to get elements for, default empty
	 * @param	bool	$force		Whether to requery, default false
	 */
	public function getAllFormElements($sortCol = '', $formId='', $force=false){
		if(isset($this->formElements) && !$force){
			return '';
		}
		
		global $wpdb;

		if(!is_numeric($formId) && $this->formData && is_numeric($this->formData->id)){
			$formId	= $this->formData->id;
		}

		if(!is_numeric($formId) && isset($this->formId) && is_numeric($this->formId)){
			$formId	= $this->formId;
		}

		if(!is_numeric($formId)){
			return new \WP_Error('forms', 'No form id given');
		}

		// Get all form elements
		$query						= "SELECT * FROM {$this->elTableName} WHERE form_id= '$formId'";

		if(!empty($sortCol)){
			$query .= " ORDER BY {$this->elTableName}.`$sortCol` ASC";
		}

		$this->formElements 		=  apply_filters('sim-forms-elements', $wpdb->get_results($query), $this, false);
	}

	/**
	 * Parses all WP Shortcode attributes
	 */
	public function processAtts($atts){
		if(!isset($this->formName)){
			$atts	= shortcode_atts(
				array(
					'formname'		=> '',
					'form-name'		=> '',
					'userid'		=> '',
					'user-id'		=> '',
					'search'		=> '',
					'shortcode-id'	=> '',
					'id'			=> '',
					'form-id'		=> '',
					'onlyown'		=> false,
					'archived'		=> false,
					'all'			=> false,
				),
				$atts
			);
			
			$this->formName 	= strtolower(sanitize_text_field($atts['formname']));
			$this->formId		= sanitize_text_field($atts['form-id']);

			$this->getForm();

			$this->shortcodeId	= $atts['shortcode-id'];
			if(empty($this->shortcodeId)){
				$this->shortcodeId	= $atts['id'];
			}
			$this->onlyOwn		= $atts['onlyown'];
			if(isset($_GET['onlyown'])){
				$this->onlyOwn	= $_GET['onlyown'];
			}
			$this->all			= $atts['all'];
			$this->showArchived	= $atts['archived'];
			if(isset($_GET['archived'])){
				$this->showArchived	= $_GET['archived'];
			}

			if(isset($_GET['all'])){
				$this->all	= $_GET['all'];
			}
			
			if(!empty($atts['userid']) && is_numeric($atts['userid'])){
				$this->userId	= $atts['userid'];
			}
		}
	}

	/**
	 * Check if we should show the formbuilder or the form itself
	 */
	public function determineForm($atts){
		global $wpdb;

		$this->processAtts($atts);

		wp_enqueue_style('sim_forms_style');

		$query				= "SELECT * FROM {$this->elTableName} WHERE `form_id`=";

		if(is_numeric($this->formId)){
			$query	.= $this->formId;
		}elseif(!empty($this->formName)){
			$query	.= "(SELECT `id` FROM {$this->tableName} WHERE name='$this->formName' LIMIT 1)";
		}else{
			return new WP_Error('forms', 'Which form do you have?');
		}
		
		$formElements 		=  $wpdb->get_results($query);

		if(isset($_REQUEST['formbuilder']) && is_user_logged_in()){
			$formBuilderForm	= new FormBuilderForm($atts);

			return $formBuilderForm->showForm();
		}elseif(empty($formElements)){
			$html	= "<div class='warning'>This form has no elements yet.<br>";
				if($this->editRights){
					$url	 = add_query_arg('formbuilder', 'true', SIM\getCurrentUrl());
					$html	.= "<br><a href='$url' class='button small sim'>Start Building the form</a>";
				}else{
					$html	.= "Ask an user with the editor role to start working on it";
				}
			return $html."</div>";
		}else{
			$displayForm	= new DisplayForm($atts);
			return $displayForm->showForm();
		}
	}

	/**
	 * Get a singleform submission
	 *
	 * @param	int		$submissionId	The id of a submission
	 *
	 * @return	object|false			The submission or false if not found
	 */
	public function getSubmission($submissionId){
		global $wpdb;

		$query	= "SELECT * FROM $this->submissionTableName WHERE id = $submissionId";

		$result	= $wpdb->get_results($query);

		if(isset($result[0])){

			$this->submission	= $result[0];

			$this->submission->formresults	= maybe_unserialize($this->submission->formresults);

			return $this->submission;
		}

		return false;
	}

	/**
     * Add signal data to wp_mail args
     */
    public function addFormData($args){
        $args['formresults'] = $this->submission->formresults;

        return $args;
    }
}
