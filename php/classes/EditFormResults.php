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

		if(!empty($this->submission->id) && is_numeric($this->submission->id)){
			$submissionId	= $this->submission->id;
		}elseif(is_numeric($this->submissionId)){
			$submissionId	= $this->submissionId;
		}elseif(is_numeric($_POST['submission-id'])){
			$submissionId	= $_POST['submission-id'];
		}else{
			SIM\printArray('No submission id found');
			return false;
		}

		if(empty($this->submission)){
			$this->parseSubmissions(null, $submissionId);
		}

		/**
		 * Filters the form results
		 * 
		 * @param mixed			$value			The value to set
		 * @param int			$elementId		The element id of the value
		 * @param int			$subId			The sub id of the value
		 * @param object		$object			The EditFormResults Instance
		 * @param bool			$update			Whether this is an update or an new submission
		 */
		$value 				= apply_filters('sim_before_updating_formdata', $value, $elementId, $subId, $this, true);

		if(is_wp_error($value )){
			return $value;
		}

		// Always update the timelastedited
		$data = [
			'timelastedited'	=> date("Y-m-d H:i:s")
		];

		$formats = [
			'%s'
		];

		if($elementId == 'userid'){
			$data['userid']		= $value;
			$formats[]			= '%d';
		}elseif($elementId == 'submitter_id'){
			$data['submitter_id']	= $value;
			$formats[]				= '%d';
		}

		//Update the submission
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
			$message	= "No row with id $submissionId found";
			if(defined('REST_REQUEST')){
				return new \WP_Error('form error', $message);
			}else{
				SIM\printArray($message);
				SIM\printArray($this->submission);
			}
		}

		/**
		 * Filters if we should do the update, return false for no update
		 */
		$continue	= apply_filters('sim-forms-should-update-form-data', true, $elementId, $submissionId, $subId, $value, $this);
		if($elementId != 'userid' && $elementId != 'submitter_id' && $continue){			
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

			//Update the submission data
			$result = $wpdb->update(
				$this->submissionValuesTableName,
				array(
					'value'			=> maybe_serialize($value)
				),
				$where,
				[
					'%s'
				],
				$formats
			);

			if($wpdb->last_error !== ''){
				$message	= $wpdb->print_error();
				if(defined('REST_REQUEST')){
					return new \WP_Error('form error', $message);
				}else{
					SIM\printArray($message);
				}
			}
		}

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
					'element_id'	=> -6,
					'value'			=> $subId
				),
				array(
					'%d',
					'%s',
					'%s'
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
					'value'			=> $subId
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

		return false;
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
		global $wpdb;

		//get all the forms
		$this->getForms();
		
		//loop over all the forms
		foreach($this->forms as $form){
			$this->formData	= $form;
			$this->formId	= $form->id;
			$this->formName	= $form->name;
			$this->getForm($form->id);
			
			//check if auto archive is turned on for this form
			if(!isset($form->autoarchive) || !$form->autoarchive){
				continue;
			}

			$splitElementName	= '';
			if(isset($form->split)){
				$form->split				= maybe_unserialize($form->split);
				$splitElementName			= $this->getElementById($form->split[0], 'name');
				$result						= preg_match('/(.*?)\[[0-9]\]\[.*?\]/', $splitElementName, $matches);
				if($result){
					$splitElementName		= $matches[1];
				}
			}

			//Get all submissions of this form
			$this->parseSubmissions(null, null, true, true);
			
			$triggerName	= $this->getElementById($form->autoarchive_el, 'name');
			$triggerValue	= $form->autoarchive_value;

			if(!$triggerName || empty($triggerValue)){
				continue;
			}

			$pattern		= "/.*?\[[0-9]+\]\[([^\]]+)\]/i";
			if(preg_match($pattern, $triggerName, $matches)){
				$triggerName	= $matches[1];
			}
			
			//check if we need to transform a keyword to a date
			$pattern = '/%([^%;]*)%/i';
			//Execute the regex
			preg_match_all($pattern, $triggerValue, $matches);
			if(!is_array($matches[1])){
				SIM\printArray($matches[1]);
			}else{
				foreach((array)$matches[1] as $keyword){
					//If the keyword is a valid date keyword
					if(strtotime($keyword)){
						//convert to date
						$triggerValue = date("Y-m-d", strtotime(str_replace('%', '', $triggerValue)));
					}
				}
			}
			
			//loop over all submissions to see if we need to archive them
			foreach($this->submissions as &$this->submission){
				
				$this->submissionId		= $this->submission->id;

				//there is no trigger value found in the results, check multi value array
				if(
					!empty($splitElementName) &&					// we should split
					empty($this->submission->{$triggerName}) && 	// we don't have a triggerfield in the results
					isset($this->submission->{$splitElementName})	// but we do have the splitted field
				){
					$archivedCounter	= 0;

					//loop over all multi values
					foreach((array)$this->submission->{$splitElementName} as $subId => $sub){
						if(
							!is_array($sub)		||
							(isset($sub['archived']) && 	// Archive entry exists
							$sub['archived'])				// sub is already archived
						){
							$archivedCounter++;
							continue;
						}

						//we found a match for a sub entry, archive it
						$val	= $sub[$triggerName];
						if(
							$val == $triggerValue || 						//value is the same as the trigger value or
							(
								strtotime($triggerValue) 	&& 				// trigger value is a date
								strtotime($val) 			&&				// this value is a date
								strtotime($val) < strtotime($triggerValue)	// value is smaller than the trigger value
							)
						){
							$this->archiveSubmission(true, $subId); 
						}
					}
				}else{
					//if the form value is equal to the trigger value it needs to be to be archived
					if(isset($this->submission->{$triggerName}) && $this->submission->{$triggerName} == $triggerValue){
						$this->archiveSubmission(true);
					}
				}
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
