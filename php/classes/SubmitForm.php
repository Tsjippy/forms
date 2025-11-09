<?php
namespace SIM\FORMS;
use SIM;
use WP_Embed;
use WP_Error;

class SubmitForm extends SaveFormSettings{
	public $submission;

	public function __construct($formData=''){
		parent::__construct();
		
		if(!empty($formData)){
			$this->formData	= $formData;
		}
	}
	
	/**
	 * Returns conditional e-mails with a valid condition
	 *
	 * @param	array	$conditions		The conditions of a conditional e-mail
	 *
	 * @return	string|false			The e-mail adres or false if none found
	 */
	public function findConditionalEmail($conditions){
		//loop over all conditions
		foreach($conditions as $condition){

			$elementName	= $this->getElementById($condition['fieldid'], 'name');

			//get the submitted form value
			$formValue = $this->submission->{$elementName};
					
			//if the value matches the conditional value
			if(strtolower($formValue) == strtolower($condition['value'])){
				return $condition['email'];
			}
		}

		return false;
	}

	/**
	 * Replaces the url with form url
	 *
	 * @param	array	$footer		The footer array
	 *
	 * @return	array				The filtered footer array
	 */
	public function emailFooter($footer){
		$footer['url']		= $_POST['formurl'];
		$footer['text']		= $_POST['formurl'];

		return $footer;
	}

	/**
	 * check if an e-mail should be send
	 */
	private function checkEmailConditions($email, $trigger){
		if(
			$email->email_trigger	!= $trigger && 					// trigger of the e-mail does not match the trigger exactly
			(
				$email->email_trigger	!= 'submittedcond' ||		// trigger of the e-mail is not submittedcond
				(
					$email->email_trigger	== 'submittedcond' &&	// trigger of the e-mail is submittedcond
					$trigger				!= 'submitted'			// the trigger is not submitted
				)
			)
		){
			return false;
		}

		$changedElementId	= $_POST['id'];
		
		// check if a certain element is changed to a certain value
		if( $trigger == 'fieldchanged' ){

			// the changed element is not the conditional element)
			if($changedElementId != $email->conditional_field){
				return false;
			}

			// get the element value
			$elementName	= str_replace('[]', '', $this->getElementById($changedElementId, 'name'));

			$formValue 		= $this->submission->{$elementName};
			if(is_array($formValue)){
				$formValue	= $formValue[0];
			}
			$formValue 		= strtolower($formValue);

			// get the compare value
			$compareValue	= strtolower($email->conditional_value);

			//do not proceed if there is no match
			if($formValue != $compareValue && $formValue != str_replace(' ', '_', $compareValue)){
				return false;
			}
		}elseif(
			$trigger == 'fieldschanged'									&&		// an element has been changed
			!in_array($changedElementId, $email->conditional_fields)			// and the element is not in the conditional fields array
		){
			return false;
		}elseif($trigger == 'submitted' && $email->email_trigger == 'submittedcond'){	// check if the submit condition is matched
			if(!is_array($email->submitted_trigger)){
				return false;
			}

			// get element and the form result of that element
			$element	= $this->getElementById($email->submitted_trigger['element']);
			if(empty($this->submission->{$element->name})){
				$elValue	= '';
			}else{
				$elValue	= $this->submission->{$element->name};
			}
			
			// get the value to compare with
			if(is_numeric($email->submitted_trigger['value-element'])){
				$compareElement	= $this->getElementById($email->submitted_trigger['value-element']);
				$compareElValue	= $this->submission->{$compareElement->name};
			}else{
				$compareElValue	= $email->submitted_trigger['value'];
			}

			if(is_array($elValue)){
				$elValue	= $elValue[0];
			}

			if(is_array($compareElValue)){
				$compareElValue	= $compareElValue[0];
			}

			// Do the comparisson, do not proceed if no match
			if(!version_compare($elValue, $compareElValue, $email->submitted_trigger['equation'])){
				return false;
			}
		}

		return true;
	}

	/**
	 * Send an e-mail
	 *
	 * @param	string	$trigger	One of 'submitted' or 'fieldchanged'. Default submitted
	 */
	public function sendEmail($trigger='submitted', $replaceValues=[]){
		$this->getEmailSettings();
		
		foreach($this->emailSettings as $key => $email){
			$email	= (object)$email;

			if(!$this->checkEmailConditions($email, $trigger)){
				continue;
			}
			
			$from	= '';
			//Send e-mail from conditional e-mail adress
			if($email->from_email == 'conditional'){
				$from 	= $this->findConditionalEmail($email->conditional_from_email);

				if(!$from){
					$from	= $email->else_from;
				}
			}elseif($email->from_email == 'fixed'){
				$from	= $this->processPlaceholders($email->from, $replaceValues);
			}

			if(empty($from)){
				SIM\printArray("No from email found for email $key");
			}
							
			$to		= '';
			if($email->email_to == 'conditional'){
				$to = $this->findConditionalEmail($email->conditional_email_to);

				if(!$to){
					$to	= $email->else_to;
				}
			}elseif($email->email_to == 'fixed'){
				$to		= $this->processPlaceholders($email->to);

				// if no e-mail found, find any numbers and assume they are user ids
				// than replace the id with the e-mail of that user
				if(!str_contains($to, '@')){
					$pattern 	= '/[0-9\.]+/i';
					$to			= preg_replace_callback(
						$pattern,
						function ($match){
							$user	= get_userdata($match[0]);

							if($user && !str_contains($user->user_email, 'empty')){
								return $user->user_email;
							}
							return $match[0];
						},
						$to
					);
				}
			}

			$recipients	= [];
			foreach(explode(',', $to) as $t){
				if(str_contains($t, '@')){
					$recipients[]	= $t;
				}
			}
			
			if(empty($recipients)){
				SIM\printArray("No to email found for email $key on form {$this->formData->name} with id {$this->formData->id}");
				continue;
			}

			$subject	= $this->processPlaceholders($email->subject);
			$message	= $this->processPlaceholders($email->message);

			$headers	= $email->headers;
			if(!is_array($headers)){
				if(!empty(trim($headers))){
					$headers	= explode("\n", trim($email->headers));
				}else{
					$headers	= [];
				}
			}

			if(!empty($from)){
				$headers[]	= "Reply-To: $from";
			}
			
			if(is_string($email->files)){
				$files		= $this->processPlaceholders($email->files);

				if(is_string($files)){
					$files		= explode(',', trim($files));
				}
			}

			// add the form specific footer filter
			add_filter('sim_email_footer_url', [$this, 'emailFooter']);

			add_filter('wp_mail', [$this, 'addFormData'], 1);

			//Send the mail
			$result = wp_mail($to , $subject, $message, $headers, $files);

			remove_filter('wp_mail', [$this, 'addFormData'], 1);

			if($result === false){
				SIM\printArray("Sending the e-mail failed");
				SIM\printArray([
					$to,
					$subject,
					$message,
					$headers,
					$files
				]);
			}

			// remove the form specific footer filter
			remove_filter('sim_email_footer_url', [$this, 'emailFooter']);
		}
	}

	/**
	 * Rename any existing files to include the form id.
	 */
	public function processFiles($uploadedFiles, $inputName){
		//loop over all files uploaded in this fileinput
		foreach ($uploadedFiles as $key => $url){
			$urlParts 	= explode('/',$url);
			$fileName	= end($urlParts);
			$path		= SIM\urlToPath($url);
			$targetDir	= str_replace($fileName,'',$path);
			
			//add input name to filename
			$fileName	= "{$inputName}_$fileName";
			
			//also add submission id if not saving to meta
			if(empty($this->formData->save_in_meta)){
				$fileName	= $this->submission->id."_$fileName";
			}
			
			//Create the filename
			$i = 0;
			$targetFile = $targetDir.$fileName;

			//add a number if the file already exists
			while (file_exists($targetFile)) {
				$i++;
				$targetFile = "$targetDir.$fileName($i)";
			}
	
			//if rename is succesfull
			if (rename($path, $targetFile)) {
				//update in formdata
				$this->submission->{$inputName}[$key]	= str_replace(ABSPATH, '', $targetFile);
			}else {
				//update in formdata
				$this->submission->{$inputName}[$key]	= str_replace(ABSPATH, '', $path);
			}
		}
	}

	/**
	 * Save a form submission to the db
	 */
	public function formSubmit(){
		global $wpdb;

		$this->submission					= new \stdClass();

		$this->submission->form_id			= $_POST['form-id'];
		
		$this->getForm($this->submission->form_id);

		// The user id of the current user
		$this->userId	= $this->user->ID;

		// Check if we are submitting for another user
		if(isset($_POST['userid']) && is_numeric($_POST['userid'])){
			//If we are submitting for someone else and we do not have the right to save the form for someone else
			if(
				array_intersect($this->userRoles, $this->submitRoles) === false &&
				$this->user->ID != $_POST['userid']
			){
				return new \WP_Error('Error', 'You do not have permission to save data for user with id '.$_POST['userid']);
			}else{
				$this->userId = $_POST['userid'];
			}
		}

		$this->submission->timecreated		= date("Y-m-d H:i:s");

		$this->submission->timelastedited	= date("Y-m-d H:i:s");
		
		$this->submission->userid			= $this->userId;

		$formresults 		= $_POST;

		// check for required empty elements
		foreach($this->formElements as $element){
			// element is required but has no value
			if($element->required && $formresults[$element->name] === '' ){
				return new \WP_Error('Error', "$element->nicename is required!");
			}
		}

		$this->submission->archived 		= false;

		$formUrl	= $formresults['formurl'];
			
		//remove the action and the formname
		unset($formresults['formname']);
		unset($formresults['fileupload']);
		unset($formresults['userid']);		
		unset($formresults['form-id']);
		unset($formresults['_wpnonce']);
		unset($formresults['formurl']);
		unset($formresults['form-id']);

		// remove empty splitted entries
		if(isset($this->formData->split)){
			foreach($this->formData->split as $index => $id){
				$name	= $this->getElementById($id, 'name');

				// Check if we are dealing with an split element with form name[X]name
				preg_match('/(.*?)\[[0-9]\](\[.*?\])/', $name, $matches);

				if(
					$matches && 
					isset($matches[1]) && 
					is_array($formresults[$matches[1]])
				){
					// loop over all the sub entries of the split field to see if they are empty
					foreach($formresults[$matches[1]] as $index=>&$sub){
						$empty	= true;
						if(is_array($sub)){
							foreach($sub as $s){
								if(!empty($s)){
									$empty	= false;
									break;
								}
							}
						}

						if($empty){
							// remove from results
							unset($formresults[$matches[1]][$index]);
						}

						$sub['elementindex']	= $index; // store the elementname so we can get the original element for editing
					}

					// reindex
					$formresults[$matches[1]] = array_values(	$formresults[$matches[1]]);
				}
			}
		}

		// Add a security hash for submissions from outside
		$formresults['viewhash']		= wp_hash($this->submission->id);
		
		/**
		 * Filters the form results
		 * 
		 * @param array		$formresults	The form results
		 * @param object	$object			The SubmitForm Instance
		 * @param bool		$update			Whether this is an update or an new submission
		 */
		$formresults 					= apply_filters('sim_before_saving_formdata', (object)$formresults, $this, false);

		if(is_wp_error($formresults)){
			return $formresults;
		}

		$message = $this->formData->succes_message;
		if(empty($message)){
			$message = 'succes';
		}
		
		//save to submission table
		if(empty($this->formData->save_in_meta)){
			// Insert Submission
			$this->submission->id	= $this->insertOrUpdateData($this->submissionTableName, $this->submission);

			//sort arrays
			foreach($formresults as $key => &$result){
				if(is_array($result)){
					//check if this an aray of uploaded files
					if(!is_array(array_values($result)[0]) && str_contains(array_values($result)[0],'wp-content/uploads/')){
						//rename the file
						$this->processFiles($result, $key);
						$result	= $this->submission->{$key};
					}else{
						//sort the array
						ksort($result);
					}
				}elseif(str_contains($result,'wp-content/uploads/')){
					//rename the file
					$this->processFiles([$result], $key);
					$result	= $this->submission->{$key}[0];
				}
			}

			// Insert Submission Data
			foreach($formresults as $key => $value){
				$data	= [
					'submission_id'	=> $this->submission->id,
					'key'			=> $key,
					'value' 		=> $value
				];

				$this->insertOrUpdateData(
					$this->submissionValuesTableName, 
					$data
				);
			}

			$placeholders				= (array) $formresults;

			$placeholders['id']			= $this->submission->id;

			$placeholders['formurl']	= $formUrl;

			$placeholders['formid']		= $this->submission->form_id;
			
			$this->sendEmail('submitted', $placeholders);
				
			if($wpdb->last_error !== ''){
				$message	=  new \WP_Error('error', $wpdb->last_error);
			}elseif(empty($this->formData->include_id) || $this->formData->include_id){
				$message	.= "\nYour id is {$this->submission->id}";
			}
		//save to user meta
		}else{			
			//get user data as array
			$userData		= (array)get_userdata($this->userId)->data;
			foreach($formresults as $key => &$result){
				$subKey	= false;

				//remove empty elements from the array
				if(is_array($result)){
					$result	= SIM\cleanUpNestedArray($result);

					//check if we should only update one entry of the array
					$el	= $this->getElementByName($key.'['.array_keys($result)[0].']');
					if(count(array_keys($result)) == 1 && $el){
						$subKey	= array_keys($result)[0];
					}
				}

				//update in the _users table
				if(isset($userData[$key])){
					if($subKey){
						$userData[$key][$subKey]		= $result;
						$updateuserData					= true;
					}elseif($userData[$key]	!= $result){
						$userData[$key]		= $result;
						$updateuserData		= true;
					}
				//update user meta
				}else{
					// update an indexed value
					if($subKey){
						$curValue	= get_user_meta($this->userId, $key, true);
						if(empty($result)){
							// remove subkey
							if(isset($curValue[$subKey])){
								unset($curValue[$subKey]);
							}
						}else{
							if(!is_array($curValue)){
								$curValue	= [];
							}

							//update subkey
							$curValue[$subKey]	= $result[$subKey];
						}

						update_user_meta($this->userId, $key, $result);
					}else{
						if(empty($result)){
							delete_user_meta($this->userId, $key);
						}else{
							update_user_meta($this->userId, $key, $result);
						}
					}
				}
			}

			if($updateuserData){
				wp_update_user($userData);
			}
		}

		$message	= apply_filters('sim_after_saving_formdata', $message, $this);

		do_action('sim-after-form-submit', $this);

		return $message;
	}
}
