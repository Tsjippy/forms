<?php
namespace SIM\FORMS;
use SIM;
use stdClass;

class FormExport extends FormBuilderForm{
    public function __construct(){
		parent::__construct();
	}

    /**
     * Writes the form export to the output buffer for download
     */
    public function exportForm($formId){
		global $wpdb;

		$this->getForm($formId);

		/**
		 * Form Settings
		 */
		unset($this->formData->form_url);

		// Remove the id
		unset($this->formData->id);

		// Set form version to 1
		$this->formData->version 	= 1;

		$content	= "form: ".json_encode(serialize($this->formData))."\n";

		/**
		 * Form Elements
		 */
		foreach($this->formElements as &$element){
			unset($element->form_id);
		}

		$content	.= "elements: ".json_encode(serialize($this->formElements))."\n";

		/**
		 * Form E-mails
		 */
		$emailSettings	= $wpdb->get_results(
			$wpdb->prepare("select * from %i where form_id=%d", $this->formEmailTable, $this->formData->id)
		);
		
		foreach($emailSettings as &$emailSetting){
			unset($emailSetting->form_id);
		}

		if(!empty($emailSettings)){
			$content	.= "emails: ".json_encode(serialize($emailSettings))."\n";
		}

		/**
		 * Form Reminders
		 */
        $reminders			= $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM %i WHERE form_id = %d", $this->formReminderTable, $formId)
		);

		if(!empty($reminders)){
			unset($reminders->id);
			$content	.= "reminders: ".json_encode(serialize($reminders))."\n";
		}

		$backupName = $this->formData->name.".sform";
		SIM\clearOutput();

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=$backupName");

        echo $content;
		exit;
	}

	/**
	 * Inserts form elements, while updating conditions with new element ids
	 * 
	 * @param array	$formElements		Array of form elements to insert
	 * @param array	$elementIdMapping 	Mapping of old element ids to new element ids
	 * 
	 * @return array|WP_Error			Array of old element ids to new element ids or WP_Error on failure
	 */
	protected function insertFormElements($formElements, $elementIdMapping = []){
		$procesLater		= [];

		// Form elements
		foreach($formElements as $element){
			/**
			 * Update contidions with new element ids
			 */
			if(!empty($element->conditions)){
				foreach($element->conditions as $key => &$condition){
					foreach($condition['rules'] as &$rule){
						if(is_numeric($rule['conditional-field'])){
							if(empty($elementIdMapping[$rule['conditional-field']])){
								$procesLater[]	= $element;
								continue 3;
							}

							$rule['conditional-field']	= $elementIdMapping[$rule['conditional-field']];
						}

						if(is_numeric($rule['conditional-field-2'])){
							if(empty($elementIdMapping[$rule['conditional-field-2']])){
								$procesLater[]	= $element;
								continue 3;
							}

							$rule['conditional-field-2']	= $elementIdMapping[$rule['conditional-field-2']];
						}

						if(is_numeric($rule['conditional-value'])){
							if(empty($elementIdMapping[$rule['conditional-value']])){
								$procesLater[]	= $element;
								continue 3;
							}

							$rule['conditional-value']	= $elementIdMapping[$rule['conditional-value']];
						}
					}

					if($key	=== 'copyto'){
						foreach($condition as $k => $copyId){
							// add with new id
							$condition[$elementIdMapping[$k]]	= $elementIdMapping[$copyId];

							// remove the old id
							unset($condition[$k]);
						}
					}

					if(is_numeric($condition['property-value'])){
						if(empty($elementIdMapping[$condition['property-value']])){
							$procesLater[]	= $element;
							continue 2;
						}

						$condition['property-value']	= $elementIdMapping[$condition['property-value']];
					}
				}
			}

			$oldElementId	= $element->id;
			unset($element->id);

			$element->form_id	= $this->formId;
			$elementId 		= $this->insertOrUpdateData($this->elTableName, $element);

			if(is_wp_error($elementId)){
				return $elementId;
			}

			$elementIdMapping[$oldElementId]	= $elementId;
		}

		// Now rerun this one for elements which could not be processed before
		if(!empty($procesLater)){
			$this->insertFormElements($procesLater, $elementIdMapping);
		}

		return $elementIdMapping;
	}

	/**
	 * Inserts form elements, while updating conditions with new element ids
	 * 
	 * @param array	$formElements		Array of form elements to insert
	 * @param array	$elementIdMapping 	Mapping of old element ids to new element ids
	 * 
	 * @return array|WP_Error			Array of old element ids to new element ids or WP_Error on failure
	 */
	protected function insertFormEmails($formEmails, $elementIdMapping){
		// Form elements
		foreach($formEmails as $email){

			$email->form_id	= $this->formId;

			if(!empty($email->submitted_trigger)){
				$triggers	= maybe_unserialize($email->submitted_trigger);

				foreach($triggers as &$trigger){
					if(is_numeric($trigger['element'])){
						$trigger['element']	= $elementIdMapping[$trigger['element']];
					}

					if(is_numeric($trigger['valueelement'])){
						$trigger['valueelement']	= $elementIdMapping[$trigger['valueelement']];
					}
				}

				$email->submitted_trigger	= serialize($triggers);
			}

			if(!empty($email->conditional_field)){
				$email->conditional_field	= $elementIdMapping[$email->conditional_field];
			}

			if(!empty($email->conditional_fields)){
				$conditionalFields	= maybe_unserialize($email->conditional_fields);

				foreach($conditionalFields as &$conditionalFieldId){
					$conditionalFieldId	= $elementIdMapping[$conditionalFieldId];
					
				}

				$email->conditional_fields	= serialize($conditionalFields);
			}

			if(!empty($email->conditional_from_email)){
				$conditionalFromEmails	= maybe_unserialize($email->conditional_from_email);

				foreach($conditionalFromEmails as &$conditionalFromEmail){
					$conditionalFromEmail['fieldid']	= $elementIdMapping[$conditionalFromEmail['fieldid']];
					
				}

				$email->conditional_from_email	= serialize($conditionalFromEmails);
			}

			if(!empty($email->conditional_email_to)){
				$conditionalEmailTo	= maybe_unserialize($email->conditional_email_to);

				foreach($conditionalEmailTo as &$conditionalEmailToField){
					$conditionalEmailToField['fieldid']	= $elementIdMapping[$conditionalEmailToField['fieldid']];
					
				}

				$email->conditional_email_to	= serialize($conditionalEmailTo);
			}

			$emailId 		= $this->insertOrUpdateData($this->elTableName, $email);

			if(is_wp_error($emailId)){
				return $emailId;
			}
		}

		return true;
	}

	public function importForm($path){
		if(!file_exists($path)){
			return new \WP_Error('forms', "$path does not exist");
		}
		global $wpdb;

		// Include the necessary file for the backend
		require_once(ABSPATH . 'wp-admin/includes/file.php');

		// Initialize the filesystem
		WP_Filesystem();

		// Access global variable
		global $wp_filesystem;

		$contents 		= $wp_filesystem->get_contents($path);
		$lines			= explode("\n", $contents);

		$autoArchiveEl	= null;

		foreach ( $lines as $line ) {
			if(empty($line)){
				continue;
			}

			extract(array_combine(['type', 'data'], explode(': ', $line, 2)));
			$object	= unserialize(json_decode($data));

			if($type	== 'form'){
				$autoArchiveEl	= $object->autoarchive_el;

				// add a new page
				$formName	= ucfirst(str_replace('_', ' ', $object->name));

				$post = array(
					'post_type'		=> 'page',
					'post_title'    => "$formName form",
					'post_content'  => "[formbuilder formname={$object->name}]",
					'post_status'   => "publish",
					'post_author'   => '1'
				);
				$url	= get_permalink(wp_insert_post( $post, true, false));

				// Form data
				$object->form_url	= $url;

				$this->formId 			= $this->insertOrUpdateData($this->tableName, $object);

				if(is_wp_error($this->formId)){
					return $this->formId;
				}

			}elseif($type	== 'elements'){
				$elementIdMapping	= $this->insertFormElements($object);

				if(is_wp_error($elementIdMapping)){
					return $elementIdMapping;
				}
			}elseif($type	== 'emails'){
				// Form e-mails
				$this->insertFormEmails($object, $elementIdMapping);
			}elseif($type	== 'reminders'){
				// Form reminders
				foreach($object as $reminder){
					if(empty($reminder->frequency) || empty($reminder->period)){
						continue;
					}

					$reminder->form_id	= $this->formId;

					$this->insertOrUpdateData($this->formReminderTable, $reminder);
				}
			}else{
				SIM\printArray("Unknown import type: $type");
				continue;
			}
		}

		// update autoarchive element id
		if(!empty($autoArchiveEl)){
			$wpdb->update(
				$this->tableName,
				array(
					'autoarchive_el' 	=> $elementIdMapping[$autoArchiveEl]
				),
				array(
					'id'		=> $formId,
				),
			);
		}

		echo "<div class='success'>Import of the form '$formName' finished successfully.<br>Visit the created form <a href='$url' target='_blank'>here</a></div>";
	}
}