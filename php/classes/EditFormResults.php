<?php
namespace SIM\FORMS;
use SIM;

class EditFormResults extends DisplayFormResults{
	public $submissionId;
	
	/**
	 * Update an existing form submission
	 *
	 * @param	int		$elementId	The element id of the value
	 * @param	mixed	$value		The value to set
	 * @param	int		$subId		The sub id in case of multiple values for the same key, default null
	 *
	 * @return	true|WP_Error		The result or error on failure
	 */
	public function updateSubmission($elementId, $value, $subId = null){
		global $wpdb;

		$value	= SIM\cleanUpNestedArray($value);

		if(is_numeric($this->submissionId)){
			$submissionId	= $this->submissionId;
		}elseif(!empty($this->submission->id) && is_numeric($this->submission->id)){
			$submissionId	= $this->submission->id;
		}elseif(is_numeric($_POST['submission-id'])){
			$submissionId	= $_POST['submission-id'];
		}else{
			SIM\printArray('No submission id found');
			return false;
		}

		if(empty($this->submission) || $this->submission->id != $submissionId){
			$this->parseSubmissions(null, $submissionId);
		}

		/**
		 * Filters the form results
		 * 
		 * @param mixed			$value			The value to set
		 * @param int			$elementId		The element id of the value
		 * @param int			$subId			The sub id of the value
		 * @param object		$object			The EditFormResults Instance
		 */
		$value 				= apply_filters('sim_before_updating_formdata', $value, $elementId, $subId, $this);

		if($value === null || is_wp_error($value)){
			return $value;
		}

		/**
		 * Update the main submission data if we are updating the userid or submitter_id, or if we are updating a field that is used in the auto archive settings
		 * We also always update the timelastedited field to be able to track when the submission was last edited, and to trigger the auto archive if needed
		 */

		// Always update the timelastedited
		$data = [
			'timelastedited'	=> date("Y-m-d H:i:s")
		];

		$formats = [
			'%s'
		];

		if($elementId == 'userid'){
			$data['userid']			= $value;
			$formats[]				= '%d';
		}elseif($elementId == 'submitter_id'){
			$data['submitter_id']	= $value;
			$formats[]				= '%d';
		}

		// Update the main submission data
		$result = $wpdb->update(
			$this->submissionTableName,
			$data,
			array(
				'id'				=> $submissionId,
			),
			$formats
		);
		
		if($wpdb->last_error !== ''){
			$message	= $wpdb->print_error();
			if(defined('REST_REQUEST')){
				return new \WP_Error('form error', $message);
			}else{
				SIM\printArray($message);
			}
		}elseif(!$result){
			$column		= array_keys($data)[0];

			$curValue	= $wpdb->get_var(
				$wpdb->prepare(
					"select $column from %i where id = %d",
					$this->submissionTableName,
					$submissionId
				)
			);

			if($curValue != $data[$column]){
				$message	= "No row with id $submissionId found\nQuery used is '$wpdb->last_query'";
				SIM\printArray($message);
				SIM\printArray($this->submission);
			}
		}

		/**
		 * Update submission values
		 */
		// Filters if we should do the update, return false for no update
		$continue	= apply_filters('sim-forms-should-update-form-data', true, $elementId, $submissionId, $subId, $value, $this);
		if($elementId != 'userid' && $elementId != 'submitter_id' && $continue){
			//Update the submission data	
			$where	= array(
				'submission_id'	=> $submissionId,
				'element_id'	=> $elementId,
			);

			$formats	= array(
				'%d',
				'%s'
			);

			if(is_numeric($subId)){
				$where['sub_id']	= $subId;
				$formats[]			= '%d';
			}

			$data	= [
				'submission_id'	=> $submissionId,	// include in case we need to create instead of update
				'sub_id'		=> $subId,			// include in case we need to create instead of update
				'element_id'	=> $elementId,		// include in case we need to create instead of update
				'value' 		=> $value
			];

			//Update the submission data
			$result	= $this->insertOrUpdateData(
				$this->submissionValuesTableName,
				$data,
				$where,
				$formats
			);

			if(is_wp_error($result)){
				return $result;
			}
		}

		do_action('sim_after_updating_formdata', $value, $elementId, $subId, $this);

		$this->sendEmail('fieldchanged');
		$this->sendEmail('fieldschanged');
		
		return $result;
	}

	/**
	 * (un)Archive an existing form submission
	 *
	 * @param	bool	$archive	Whether we should archive or unarchive the submission. Default false
	 *
	 * @return	true|WP_Error		The result or error on failure
	 */
	public function archiveSubSubmission($archive, $subId, $submissionId){
		//we are archiving a sub-entry
		if(!is_numeric($subId)){
			return false;
		}

		global $wpdb;

		// Add the index to the archived indexes
		if($archive){
			$result = $wpdb->insert(
				$this->submissionValuesTableName,
				array(
					'submission_id'	=> $submissionId,
					'sub_id'		=> $subId,
					'element_id'	=> -6,
					'value'			=> 1
				),
				array(
					'%d',
					'%d',
					'%d',
					'%d'
				)
			);
		}
		
		// Remove the index from the archived indexes
		else{
			$result = $wpdb->delete(
				$this->submissionValuesTableName,
				array(
					'submission_id'	=> $submissionId,
					'element_id'	=> -6,
					'sub_id'		=> $subId
				)
			);
		}

		if($wpdb->last_error !== ''){
			$message	= $wpdb->print_error();
			if(defined('REST_REQUEST')){
				return new \WP_Error('form error', $message);
			}else{
				SIM\printArray($message);
			}
		}elseif(!$result){
			$message	= "No row with id $submissionId found";
			if(defined('REST_REQUEST')){
				return new \WP_Error('form error', $message);
			}else{
				SIM\printArray($message);
				SIM\printArray($this->submission);
			}
		}	
		
		// We did not archive the whole submission, so stop here
		if(!$this->checkIfAllArchived()){
			//not all subentries are archived, no need to archive the whole submission
			return "Entry with id {$this->submissionId} and sub-id $subId succesfully " . ($archive ? 'archived' : 'unarchived');
		}

		return true;
	}

	/**
	 * (un)Archive an existing form submission
	 *
	 * @param	bool	$archive	Whether we should archive or unarchive the submission. Default false
	 *
	 * @return	true|WP_Error		The result or error on failure
	 */
	public function archiveSubmission($archive, $subId = null){
		global $wpdb;

		if(is_numeric($this->submissionId)){
			$submissionId	= $this->submissionId;
		}elseif(is_numeric($_POST['submission-id'])){
			$submissionId	= $_POST['submission-id'];
		}elseif(!empty($this->submission->id)){
			$submissionId	= $this->submission->id;
		}else{
			SIM\printArray('No submission id found');
			return false;
		}
	
		// If we are archiving a sub entry, we need to check if all other sub entries are also archived, if so we archive the whole submission
		$result	= $this->archiveSubSubmission($archive, $subId, $submissionId);	

		if($result){
			return $result;
		}

		// Mark as (un)archived
		$result = $wpdb->update(
			$this->submissionTableName,
			array(
				'timelastedited'	=> date("Y-m-d H:i:s"),
				'archived'			=> $archive
			),
			array(
				'id'				=> $submissionId,
			),
			array(
				'%s',
				'%d'
			)
		);
		
		if($wpdb->last_error !== ''){
			$message	= $wpdb->print_error();
			if(defined('REST_REQUEST')){
				return new \WP_Error('form error', $message);
			}else{
				SIM\printArray($message);
			}
		}elseif(!$result){
			$message	= "No row with id $submissionId found";
			if(defined('REST_REQUEST')){
				return new \WP_Error('form error', $message);
			}else{
				SIM\printArray($message);
				SIM\printArray($this->submission);
			}
		}else{
			$message	= "Entry with id {$this->submissionId} succesfully " . ($archive ? 'archived' : 'unarchived');
		}

		if($archive){
			$this->sendEmail('removed');

			do_action('sim-forms-entry-archived', $this, $submissionId);
		}
		
		return $message;
	}
	
	/**
	 * Auto archive form submission based on the form settings
	 */
	public function autoArchive(){
		//get all the forms
		$this->getForms();
		
		//loop over all the forms
		foreach($this->forms as $form){
			//check if auto archive is turned on for this form
			if(!isset($form->autoarchive) || !$form->autoarchive || empty($form->autoarchive_el)){
				continue;
			}

			$this->formData	= $form;
			$this->formId	= $form->id;
			$this->formName	= $form->name;
			$this->getForm($form->id);
			
			$triggerId		= $form->autoarchive_el;
			$triggerValue	= $form->autoarchive_value;

			if(empty($triggerId) || empty($triggerValue)){
				continue;
			}

			/**
			 * Process placeholder in the triggervalue
			 */

			// Regex pattern to search for %words%
			$pattern = '/%([^%;]*)%/i';

			// Get all the replace patterns
			preg_match_all($pattern, $triggerValue, $matches);
			if(!is_array($matches[1])){
				SIM\printArray($matches[1]);
			}else{
				// Loop over all the replacements
				foreach((array)$matches[1] as $keyword){
					//If the keyword is a valid date keyword
					if(strtotime($keyword)){
						//convert to date
						$triggerValue = date("Y-m-d", strtotime(str_replace('%', '', $triggerValue)));
					}
				}
			}

			$compare	= '=';

			// If this looks like a date, we want to compare if the date is in the past, so we change the compare operator to <
			if(preg_match('/\d{4}-\d{2}-\d{2}/', $triggerValue)){
				$compare	= '<';
			}

			// Get potential split
			$splittedElements	= $this->findSplitElementIds();

			// Find the name of the trigger element
			foreach($splittedElements as $baseName){
				foreach($baseName as $name => $splitElementIds){
					if(in_array($triggerId, $splitElementIds)){
						$triggerId	= $name;

						break 2;
					}
				}
			}

			$submissions		= $this->getSubmissions(
				null, 
				null, 
				true,
				[
					"$triggerId $compare %s"
				],
				[
					$triggerValue
				]
			);

			foreach($submissions as $submission){
				$this->submissionId	= $submission->id;

				$this->archiveSubmission(true, $submission->sub_id);
			}
		}
	}

	 /**
	 * Checks if all sub entries are archived, if so archives the whole
	 */
	public function checkIfAllArchived(){
		//check if all subfields are archived or empty
		$allArchived = true;

		$splitIds	= $this->formData->split;

		foreach($splitIds as $id){
			if(!$id){
				continue;
			}

			$elementName			= $this->getElementById($id, 'name');
			if(!$elementName){
				continue;
			}

			preg_match('/(.*?)\[[0-9]\]\[.*?\]/', $elementName, $matches);

			if(isset($matches[1])){
				$elementName	= $matches[1];
			}

			$archivedCount	= count($this->getSubmissionValue($this->submission->id, -6, '', true));

			// Check id there are still non archived entries
			if(
				isset($this->submission->{$elementName}) && 
				count($this->submission->{$elementName}) > $archivedCount
			){
				$allArchived = false;
			}
		}

		return $allArchived;
	}

	/**
	 * Removes an existing submission from the database
	 *
	 * @param	int	$submissionId		The id of the submission to delete
	 *
	 * @return	int|WP_Error			The number of rows updated, or an WP_Error on error.
	 */
	public function deleteSubmission($submissionId){
		global $wpdb;

		$result = $wpdb->delete(
			$this->submissionTableName,
			array(
				'id'		=> $submissionId
			)
		);
		
		if($result === false){
			return new \WP_Error('sim forms', "Submission removal failed");
		}

		$this->sendEmail('removed');

		return $result;
	}
}
