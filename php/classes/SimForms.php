<?php
namespace SIM\FORMS;
use SIM;
use stdClass;
use WP_Error;

class SimForms{
	
	public $isFormStep;
	public $isMultiStepForm;
	protected $clonableFormStep;
	public $formStepCounter;
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
	public $tableName;
	public $formReminderTable;
	public $elTableName;
	public $submissionTableName;
	public $formEmailTable;
	public $shortcodeTable;
	public $shortcodeColumnSettingsTable;
	public $emailSettings;
	public $formReminder;

	public function __construct(){
		global $wpdb;

		$this->isFormStep					= false;
		$this->isMultiStepForm				= '';
		$this->formStepCounter				= 0;
		$this->submissionTableName			= $wpdb->prefix . 'sim_form_submissions';
		$this->tableName					= $wpdb->prefix . 'sim_forms';
		$this->formReminderTable			= $wpdb->prefix . 'sim_form_reminders';
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

		$this->multiwrap					= false;
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
	}

	/**
	 * Creates the tables for this module
	 */
	public function createDbTables(){
		if ( !function_exists( 'maybe_create_table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
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

		// Form Reminders Table
		$sql = "CREATE TABLE {$this->formReminderTable} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id int,
			frequency int,
			period text,
			reminder_startdate date,
			reminder_amount int,
			reminder_period text,
			window_start date,
			window_end date,
			conditions LONGTEXT,

			PRIMARY KEY  (id)
		) $charsetCollate;";

		maybe_create_table($this->formReminderTable, $sql );

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
			width int,
			element_id int,
			`show` boolean,
			name tinytext,
			nice_name tinytext,
			`priority` int,
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
	 * Gets the form reminders from the db
	 * @param	int	$formId		the form id for which to get the reminders
	 */
	public function getFormReminder($formId=''){
		global $wpdb;

		if(empty($formId)){
			$formId	= $this->formData->id;
		}
		$this->formReminder	= new stdClass();
		
		$query = "select * from $this->formReminderTable where form_id={$formId}";
		
		$results	= $wpdb->get_results($query);

		if(empty($results)){
			return;
		}

		$this->formReminder	= $results[0];

		foreach($this->formReminder as &$setting){
			$setting	= maybe_unserialize($setting);
		}
	}

 	/**
	* Retrieves e-mail settings from the database
	*/
	public function getEmailSettings(){
		global $wpdb;

		if(empty($this->formData)){
			return new WP_Error('forms', "no form is loaded");
		}
		
		$query = "select * from $this->formEmailTable where form_id={$this->formData->id}";
		
		$this->emailSettings				= $wpdb->get_results($query);

		foreach($this->emailSettings as &$emailSetting){
			foreach($emailSetting as &$setting){
				$setting	= maybe_unserialize($setting);
			}
		}
		
		if(empty($this->emailSettings)){
			$emails[0]["from"]			= "";
			$emails[0]["to"]			= "";
			$emails[0]["subject"]		= "";
			$emails[0]["message"]		= "";
			$emails[0]["headers"]		= "";
			$emails[0]["files"]			= "";
			$emails[0]["email_trigger"]	= "";

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
		if(empty($element)){
			return false;
		}
		
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
		$userIdKey	= 'user-id';

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

		$elements	= $wpdb->get_results($query);
		foreach($elements as &$element){
			if(!empty($element->conditions)){
				$element->conditions	= maybe_unserialize($element->conditions);
			}
		}

		$this->formElements 		=  apply_filters('sim-forms-elements', $elements, $this, false);
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
					'shortcodeid'	=> '',
					'shortcode-id'	=> '',
					'id'			=> '',
					'formid'		=> '',
					'form-id'		=> '',
					'only-own'		=> false,
					'onlyown'		=> false,
					'archived'		=> false,
					'all'			=> false,
				),
				$atts
			);

			if(empty($atts['form-name'])){
				$atts['form-name']	= $atts['formname'];
			}

			if(empty($atts['user-id'])){
				$atts['user-id']	= $atts['userid'];
			}

			if(empty($atts['shortcode-id'])){
				$atts['shortcode-id']	= $atts['shortcodeid'];
			}

			if(empty($atts['form-id'])){
				$atts['form-id']	= $atts['formid'];
			}

			if(empty($atts['only-own'])){
				$atts['only-own']	= $atts['onlyown'];
			}

			$this->shortcodeId	= $atts['shortcode-id'];
			if(empty($this->shortcodeId)){
				$this->shortcodeId	= $atts['id'];
			}

			$this->onlyOwn		= $atts['only-own'];
			if(isset($_GET['only-own'])){
				$this->onlyOwn	= $_GET['only-own'];
			}

			$this->all			= $atts['all'];
			$this->showArchived	= $atts['archived'];
			if(isset($_GET['archived'])){
				$this->showArchived	= $_GET['archived'];
			}

			if(isset($_GET['all'])){
				$this->all	= $_GET['all'];
			}
			
			if(!empty($atts['user-id']) && is_numeric($atts['user-id'])){
				$this->userId	= $atts['user-id'];
			}

			$this->formName 	= strtolower(sanitize_text_field($atts['form-name']));
			$this->formId		= sanitize_text_field($atts['form-id']);

			$this->getForm();
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
					$url	 = add_query_arg('formbuilder', 1, SIM\getCurrentUrl());
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

	/**
	 * Replaces placeholder with the value
	 *
	 * @param	string	$string			The string to check for placeholders
	 * @param	array	$replaceValues	An indexed array where the index is the keyword and the value the keyword should be replaced with. Default empty, in that case form results are used.
	 *
	 * @return	string					The filtered string
	 */
	public function processPlaceholders($string, $replaceValues=''){
		if(empty($string)){
			return $string;
		}

		if(!empty($this->submission->formresults)){
			if(empty($replaceValues)){
				$replaceValues = $this->submission->formresults;
			}

			if(empty($this->submission->formresults['submissiondate'])){
				$this->submission->formresults['submissiondate']	= date('d F y', strtotime($this->submission->formresults['submissiontime']));
				$this->submission->formresults['editdate']			= date('d F y', strtotime($this->submission->formresults['edittime']));
			}
			
			if(isset($_REQUEST['subid']) && empty($this->submission->formresults['subid'])){
				$this->submission->formresults['subid']	= $_REQUEST['subid'];
			}
		}

		$pattern = '/%([^%;]*)%/i';
		//Execute the regex
		preg_match_all($pattern, $string, $matches);
		
		//loop over the results
		foreach($matches[1] as $match){
			$replaceValue	= $replaceValues[$match];

			// Empty
			if(empty($replaceValue)){
				$replaceValue	= apply_filters('sim-forms-transform-empty', $replaceValue, $this, $match);
				if(empty($replaceValue)){
					//remove the placeholder, there is no value
					$string = str_replace("%$match%", '', $string);

					// mention it in the log
					SIM\printArray("No value found for transform value '%$match%' on form '{$this->formData->name}' with id {$this->formData->id}");
				}
				$string 		= str_replace("%$match%", $replaceValue, $string);
			}
			
			// Valid file(s)
			elseif(
				is_array($replaceValue)									&&	// the form results are an array
				file_exists( ABSPATH.array_values($replaceValue)[0])		// and the first entry is a valid file
			){
				// add the ABSPATH to the file paths
				$string = array_map(function($value){
					return ABSPATH.$value;
				}, $replaceValue);
			}
			
			else{
				if(is_array($replaceValue) && count($replaceValue) == 1){
					$replaceValue	= array_values($replaceValue)[0];
				}

				if(is_array($replaceValue)){
					$replaceValue	= apply_filters('sim-forms-transform-array', implode(',', $replaceValue), $replaceValue, $this, $match);
				}elseif(preg_match('/^(\d{4}-\d{2}-\d{2})$/', $replaceValue, $matches)){
					$replaceValue	= date(get_option('date_format'), strtotime((string)$matches[1]));
				}

				//replace the placeholder with the value
				if(!file_exists($replaceValue)){
					$replaceValue	= str_replace('_', ' ', $replaceValue);
				}

				// wordpress sometimes adds http:// automatically
				if($match == 'formurl'){
					$string 		= str_replace("http://%$match%", $replaceValue, $string);
				}
				$string 		= str_replace("%$match%", $replaceValue, $string);
			}
		}
		
		return $string;
	}
}
