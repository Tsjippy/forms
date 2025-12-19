<?php
namespace SIM\FORMS;
use SIM;
use stdClass;

class FormBuilderForm extends DisplayForm{
	public $isInDiv;
	public $defaultArrayValues;
	public $defaultValues;
	public $nextElement;
	public $prevElement;
	public $currentElement;
	public $elementHtmlBuilder;
	protected $inMultiAnswer;
	public $reminders;
	public $usermeta;

	public function __construct($atts=[]){
		parent::__construct();

		if(!empty($atts)){
			$this->processAtts($atts);
			$this->getForm();
			$this->getAllFormElements();
		}

		$this->inMultiAnswer	= false;
	}

	/**
	 * Creates a dropdown with all form elements
	 *
	 * @param	int		$selectedId	The id of the current selected element in the dropdown. Default empty
	 * @param	int		$elementId	the id of the element
	 *
	 * @return	string	The dropdown html
	 */
	protected function inputDropdown($selectedId, $elementId=''){
		$html = "";
		if($selectedId == ''){
			$html = "<option value='' selected>---</option>";
		}else{
			$html = "<option value=''>---</option>";
		}

		// Add booking date elements
		/**
		 * Filters the elements of this form,
		 * @param	array	$elements		The elements array
		 * @param	object	$object			The form instance
		 * @param	bool	$force			Wheter to force a requery	
		 */
		$elements	= apply_filters('sim-forms-elements', $this->formElements, $this, true);
		
		foreach($elements as $element){
			//do not include the element itself do not include non-input types
			if($element->id != $elementId && !in_array($element->type, ['label', 'info', 'datalist', 'formstep', 'div-end'])){
				$name = ucfirst(str_replace('_', ' ', $element->name));

				// add the id if non-unique name
				if(str_contains($name, '[]')){
					$name	.= " ($element->id)";
				}
				
				//Check which option is the selected one
				if(
					!empty($selectedId) &&					// there is an option selected
					(
						$selectedId == $element->id	||		// its the current element
						is_array($selectedId)		&&		// multiple elements are selected
						in_array($element->id, $selectedId)	// current element is one of the selected

					)
				){
					$selected = 'selected="selected"';
				}else{
					$selected = '';
				}
				$html .= "<option value='{$element->id}' $selected>$name</option>";
			}
		}
		
		return $html;
	}

	protected function addMetaSymbols($parent, $element){
		//Add a symbol if this field has conditions or is required
		if(empty($element->conditions) && !$element->required && !$element->mandatory){
			return;
		}

		$icons		= [];
		if(!empty($element->conditions)){
			$icons[]		= [
				'content' 	=> '*',
				'explainer'	=> 'This element has conditions',
				'class'		=> '',
				'right'		=> 20
			];
		}

		if(!empty($element->required)){
			$right			= 20;
			if(count($icons) > 0){
				$right	= 50;
			}
			$icons[]		= [
				'content' 	=> '!',
				'explainer'	=> 'This element is required',
				'class'		=> '',
				'right'		=> $right
			];
		}

		if($element->mandatory){
			$right			= 20;
			if(count($icons) == 1){
				$right	= 50;
			}elseif(count($icons) == 2){
				$right	= 80;
			}

			$icons[]		= [
				'content' 	=> '!',
				'explainer'	=> 'This element is conditionally required',
				'class'		=> 'conditional',
				'right'		=> $right
			];
		}

		$right	= $icons[array_key_last($icons)]['right'] + 30;
		
		foreach($icons as $icon){
			$div	= $this->addElement(
				'div',
				$parent,
				[
					'class'	=> 'info-box',
					'style'	=> 'position: absolute;top: 0;width: 100%;'
				]
				);

			$this->addElement(
				'span',
				$div,
				[
					'class' => "conditions-info formfield-button {$icon['class']}",
					'style' => "right:{$icon['right']}px"
				],
				$icon['content']
			);

			$this->addElement(
				'span',
				$div,
				[
					'class' => 'info-text conditions', 
					'style' => "position: absolute;margin: 0;right: {$right}px;top: 5px;height: 30px;"
				],
				$icon['explainer']
			);

		}
	}

	/**
	 * Build all html for a particular element including edit controls.
	 *
	 * @param	object	$element		The element
	 * @param	object	$parent			The parent node
	 * @param	int		$key			The key in case of a multi element. Default 0
	 *
	 * @return	string					The html
	 */
	public function buildHtml($element, $parent='', $key=0){
		$returnHtml = false;

		if(empty($parent)){
			// Create a new DOMDocument object
			$dom 		= new \DOMDocument();
			
			$parent 	= $dom;
			
   			$returnHtml = true;
		}				

		$class		= 'form-element-wrapper';
		if($this->inMultiAnswer){
			$class	.= 'multi-answer-element';
		}

		$style	= 'display: flex;';

		// Visualy show that an element is wrapped in a div container
		if($this->isInDiv && $element->type != 'div-end'){
			$style	.= 'margin-left: 30px;';
		}

		//Add form edit controls if needed
		$controlsWrapper	= $this->addElement(
			'div',
			$parent,
			[
				'class' 			=> $class,
				'data-element-id' 	=> $element->id,
				'data-form-id' 		=> $this->formData->id,
				'data-priority' 	=> $element->priority,
				'data-type' 		=> $element->type,
				'style' 			=> $style
			]
		);

		$span	= $this->addElement(
			'span',
			$controlsWrapper,
			[
				'class' 		=> 'movecontrol formfield-button',
				'aria-hidden' 	=> 1
			],
			':::'
		);

		$this->addElement('br', $span);

		$class	= 'element-id';
		if(!isset($_REQUEST['show-id'])){
			$class	.= ' hidden';
		}
		$this->addElement(
			'span',
			$span,
			[
				'class' => $class,
				'style' => 'font-size:xx-small'
			],
			$element->id
		);

		$resizerWrapper	= $this->addElement('div', $controlsWrapper, ['class'=> 'resizer-wrapper']);

		//Check if element needs to be hidden
		$hidden = '';
		if(!empty($element->hidden) && $element->hidden == true){
			$hidden = ' hidden';
		}
		
		//if the current element is required or this is a label and the next element is required
		if(
			$element->required == true			||
			$element->mandatory == true			||
			$element->type == 'label'			&&
			(
				$this->nextElement->required	||
				$this->nextElement->mandatory
			)
		){
			$hidden .= ' required';
		}
		
		if($element->type == 'info'){
			$attributes	= ["class"=> "show input-wrapper$hidden"];
		}else{
			//Set the element width to 85 percent so that the info icon floats next to it
			if($key != 0 && $this->prevElement->type == 'info'){
				$width = 85;
			//We are dealing with a label which is wrapped around the next element
			}elseif($element->type == 'label'	&& !isset($element->wrap) && is_numeric($this->nextElement->width)){
				$width = $this->nextElement->width;
			}elseif(is_numeric($element->width)){
				$width = $element->width;
			}else{
				$width = 100;
			}

			$attributes	= [
				"class" 				=> "resizer show input-wrapper$hidden",
				"data-width-percentage" => "$width",
				"style" 				=> "width:$width%;"
			];
		}

		$text	= '';
		$name	= ucfirst(str_replace('_', ' ', $element->name));
		if($element->type == 'formstep'){
			$text = ' ***Formstep element***';
		}elseif($element->type == 'datalist'){
			$text = " ***Datalist element $element->name***";
		}elseif($element->type == 'multi-start'){
			$text = ' ***Multi answer start***';
			$this->inMultiAnswer	= true;
		}elseif($element->type == 'multi-end'){
			$text = ' ***Multi answer end***';
			$this->inMultiAnswer	= false;
		}elseif($element->type == 'div-start'){
			$text 			= " ***$name div container start***";
			$this->isInDiv	= true;
		}elseif($element->type == 'div-end'){
			$name			= ucfirst(str_replace('_', ' ', $element->name));
			$text 			= " ***$name div container end***";
			$this->isInDiv	= false;
		}

		$resizer	= $this->addElement('div', $resizerWrapper, $attributes, $text);

		if(!in_array($element->type, ['multi-start', 'multi-end', 'div-start', 'div-end'])){
			//Load default values for this element
			$this->getElementHtml($element, $resizer);
		}

		$hidden	= ' hidden';
		if(isset($_REQUEST['show-name'])){
			$hidden	= '';
		}

		$this->addElement(
			'span',
			$resizer,
			[
				'class' => "element-name $hidden",
				'style' => 'font-size:xx-small;'
			],
			$element->name
		);
					
		$this->addMetaSymbols($resizer, $element);

		$this->addElement('span', $resizer, ['class' => 'width-percentage formfield-button']);

		$this->addElement(
			'button',
			$controlsWrapper,
			[
				'type'	=> 'button',
				'class'	=> 'add-form-element button formfield-button',
				'title'	=> 'Add an element after this one'
			],
			'+'
		);

		$this->addElement(
			'button',
			$controlsWrapper,
			[
				'type'	=> 'button',
				'class'	=> 'remove-form-element button formfield-button',
				'title'	=> 'Remove this element'
			],
			'-'
		);

		$this->addElement(
			'button',
			$controlsWrapper,
			[
				'type'	=> 'button',
				'class'	=> 'edit-form-element button formfield-button',
				'title'	=> 'Change this element'
			],
			'Edit'
		);

		$copyButton	= $this->addElement(
			'button',
			$controlsWrapper,
			[
				'type'	=> 'button',
				'class'	=> 'copy-form-element button formfield-button',
				'title'	=> 'Duplicate this element'
			]
		);
		
		$this->addElement(
			'img',
			$copyButton,
			[
				'class' 	=> 'copy copy-form-element',
				'src'   	=> SIM\pathToUrl(MODULE_PATH.'pictures/copy_white.png'),
				'loading'	=> 'lazy'
			]
		);

		if($returnHtml){
			return $dom->saveHtml();
		}

		return $controlsWrapper;
	}

	/**
	 * Main function to show all
	 *
	 * @param	array	$atts	The attribute array of the WP Shortcode
	 */
	public function showForm(){
		if(!is_user_logged_in()){
			return false;
		}
		
		// Load js
		wp_enqueue_script('sim_forms_script');

		if(isset($_POST['export-form']) && is_numeric($_POST['export-form'])){
			$formExport	= new FormExport();
			$formExport->exportForm($_POST['export-form']);
		}

		if(isset($_POST['delete-form']) && is_numeric($_POST['delete-form'])){
			$saveFormSettings	= new SaveFormSettings();
			$saveFormSettings->deleteForm($_POST['delete-form']);

			return "<div class='success'>Form successfully deleted.</div>";
		}

		//Formbuilder js
		wp_enqueue_script( 'sim_formbuilderjs');

		// make sure we use unique priorities
		ob_start();
		
		?>
		<div class="sim-form-wrapper">
			<?php
			$this->addElementModal();

			?>
			<button class="button tablink formbuilder-form<?php if(!empty($this->formElements)){echo ' active';}?>"	id="show-element-form" 		data-target="element-form">
				Form elements
			</button>
			<button class="button tablink formbuilder-form<?php if(empty($this->formElements)){echo ' active';}?>"	id="show-form-settings" 	data-target="form-settings">
				Form settings
			</button>
			<button class="button tablink formbuilder-form"															id="show-form-reminders" 	data-target="form-reminders">
				Form reminders
			</button>
			<button class="button tablink formbuilder-form"															id="show-form-emails" 		data-target="form-emails">
				Form emails
			</button>
			
			<div class="tabcontent<?php if(empty($this->formElements)){echo ' hidden';}?>" id="element-form">
				<?php $this->formElementsForm();?>
			</div>
				
			<div class="tabcontent<?php if(!empty($this->formElements)){echo ' hidden';}?>" id="form-settings">
				<?php $this->formSettingsForm();?>
			</div>
			
			<div class="tabcontent<?php if(!empty($this->formElements)){echo ' hidden';}?>" id="form-reminders">
				<?php $this->formReminderForm();?>
			</div>
			
			<div class="tabcontent hidden" id="form-emails">
				<?php $this->formEmailsForm();?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Outputs the form builder
	 */
	public function formElementsForm(){
		if(empty($this->formElements)){
			?>
			<div name="formbuildbutton">
				<p>No formfield defined yet.</p>
				<button name='createform' class='button' data-formname='<?php echo $this->formName;?>'>Add fields to this form</button>
			</div>
			<?php
		}else{
			?>
			<div class="form-edit-buttons-wrapper">
				<button name='show-id' class='button' data-action='show' style='padding-top:0px;padding-bottom:0px;'>Show element id's</button>
				<button name='show-name' class='button' data-action='show' style='padding-top:0px;padding-bottom:0px;'>Show element name's</button>
				<button class='button formbuilder-switch-back small'>Show enduser form</button>
			</div>
			<?php
		}

		$this->dom	= new \DOMDocument();

		$form		= $this->addElement(
			'form', 
			$this->dom, 
			[
				'action'	=> '',
				'method'	=>'post',
				'class'	 	=> 'sim-form builder'
			]
		);

		$wrapper	= $this->addElement('div', $form, ['class'	=> 'form-elements']);

		$this->addElement(
			'input', 
			$wrapper, 
			[
				'type'	=> 'hidden',
				'class'	=> 'no-reset',
				'name'	=> 'form-id',
				'value'	=> $this->formData->id
			]
		);
	
		$this->nextElement		= '';
		foreach($this->formElements as $key => $element){
			if(isset($this->formElements[$key + 1])){
				$this->nextElement		= $this->formElements[$key + 1];
			}else{
				$this->nextElement		= '';
			}

			$this->currentElement	= $element;

			$this->buildHtml($element, $wrapper, $key);

			$this->prevElement	= $element;
		}

		// Print the html
		echo $this->dom->saveHtml();
	}
	
	/**
	 * The modal to add an element to the form
	 */
	public function addElementModal(){
		?>
		<div class="modal add-form-element-modal hidden">
			<!-- Modal content -->
			<div class="modal-content" style='max-width:90%; width:max-content;'>
				<span id="modal-close" class="close">&times;</span>
				
				<button class="button tablink formbuilder-form active"	id="show-element-builder" data-target="element-builder">Form element</button>
				<button class="button tablink formbuilder-form"			id="show-element-conditions" data-target="element-conditions">Element conditions</button>
				
				<div class="tabcontent" id="element-builder">
				</div>
				
				<div class="tabcontent hidden" id="element-conditions">
					<div class="element-conditions-wrapper"></div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Form to change form settings
	 */
	public function formSettingsForm(){
		global $wp_roles;

		//Get all available roles
		$userRoles = $wp_roles->role_names;
		
		//Sort the roles
		asort($userRoles);

		$splittedElements	= $this->findSplitElementIds();

		$keyWords			= [];
		foreach($splittedElements as $baseName => $names){
			$keyWords		= array_merge($keyWords, array_keys($names));
		}
		
		?>
		<div class="element-settings-wrapper">
			<form action='' method='post' class='sim-form builder'>
				<div class='form-elements'>
					<input type='hidden' class='no-reset' class='formbuilder' name='form-id'	value='<?php echo $this->formData->id;?>'>
					
					<label class="block">
						<h4>Submit button text</h4>
						<input type='text' class='formbuilder form-element-setting' name='button-text' value="<?php echo $this->formData->button_text?>">
					</label>
					
					<label class="block">
						<h4>Succes message</h4>
						<input type='text' class='formbuilder form-element-setting' name='succes-message' value="<?php echo $this->formData->succes_message?>">
					</label>

					<label class="block">
						<h4>Include submission ID in message</h4>
						<label>
							<input type='radio' class='formbuilder form-element-setting' name='include-id' value="1" <?php if(!isset($this->formData->include_id) || $this->formData->include_id){echo 'checked';}?>>
							Yes
						</label>
						<label>
							<input type='radio' class='formbuilder form-element-setting' name='include-id' value="0" <?php if(isset($this->formData->include_id) && !$this->formData->include_id){echo 'checked';}?>>
							No
						</label>
					</label>
					
					<label class="block">
						<h4>Form name</h4>
						<input type='text' class='formbuilder form-element-setting' name='form-name' value="<?php echo $this->formData->form_name?>">
					</label>
					<br>
					
					<label class='block'>
						<?php
						if($this->formData->save_in_meta){
							$checked = 'checked';
						}else{
							$checked = '';
						}
						?>
						<input type='checkbox' class='formbuilder form-element-setting' name='save-in-meta' value='1' <?php echo $checked;?>>
						Save submissions in usermeta table
					</label>
					<br>
					
					<label class="block">
						<h4>Form url</h4>
						<?php
						if(!empty($this->formData->form_url)){
							$url	= $this->formData->form_url;
						}else{
							$url	= str_replace(['?formbuilder=yes', '&formbuilder=yes'], '', SIM\currentUrl(true));
						}

						?>
						<input type='url' class='formbuilder form-element-setting' name='form-url' value="<?php echo $url?>">
					</label>
					<br>
					
					<?php
					//check if we have any upload fields in this form
					$hideUploadEl	= true;
					foreach($this->formElements as $el){
						if($el->type == 'file' || $el->type == 'image'){
							$hideUploadEl	= false;
							break;
						}
					}
					?>
					<label class='block <?php if($hideUploadEl){echo 'hidden';}?>'>
						<h4>Save form uploads in this subfolder of the uploads folder:<br>
						If you leave it empty the default form_uploads will be used</h4>
						<input type='text' class='formbuilder form-element-setting' name='upload-path' value='<?php echo $this->formData->upload_path;?>'>
					</label>
					<br>
						
					<h4>Available actions</h4>
					<?php
					$actions = ['archive','delete'];
					foreach($actions as $action){
						if(!empty($this->formData->actions[$action])){
							$checked = 'checked';
						}else{
							$checked = '';
						}
						?>
						<label class='option-label'>
							<input type='checkbox' class='formbuilder form-element-setting' name='actions[<?php echo $action;?>]' value='<?php echo $action;?>' <?php echo $checked;?>>
							<?php echo ucfirst($action);?>
						</label><br>
						<?php
					}
					?>
					
					<div class="formsettings-wrapper">
						<label class="block">
							<h4>Auto archive results</h4>
							<br>
							<?php
							if($this->formData->autoarchive){
								$checked1	= 'checked';
								$checked2	= '';
							}else{
								$checked1	= '';
								$checked2	= 'checked';
							}
							?>
							<label>
								<input type="radio" name="autoarchive" value="1" <?php echo $checked1;?>>
								Yes
							</label>
							<label>
								<input type="radio" name="autoarchive" value="0" <?php echo $checked2;?>>
								No
							</label>
						</label>
						<br>
						<div class='auto-archive-logic <?php if(empty($checked1)){echo 'hidden';}?>' style="display: flex;width: 100%;">
							Auto archive a (sub) entry when field
							<select name="autoarchive-el" style="margin-right:10px;">
								<?php
								if(empty($this->formData->autoarchive_el)){
									?><option value='' selected>---</option><?php
								}else{
									?><option value=''>---</option><?php
								}
								
								$processed = [];
								foreach($this->formElements as $key => $element){
									if(in_array($element->type, $this->nonInputs)){
										continue;
									}
									
									$pattern			= "/\[[0-9]+\]\[([^\]]+)\]/i";
									
									$name = $element->name;
									if(preg_match($pattern, $element->name,$matches)){
										//We found a keyword, check if we already got the same one
										if(!in_array($matches[1], $processed)){
											//Add to the processed array
											$processed[]	= $matches[1];
											
											//replace the name
											$name		= $matches[1];
										}else{
											//do not show this element
											continue;
										}
									}
									
									//Check which option is the selected one
									if(!empty($this->formData->autoarchive_el) && $this->formData->autoarchive_el == $element->id){
										$selected = 'selected="selected"';
									}else{
										$selected = '';
									}
									echo "<option value='{$element->id}' $selected>$name</option>";
								}
								
								?>
							</select>
							<label style="margin:0 10px;">equals</label>
							<input type='text' name="autoarchive-value" value="<?php echo $this->formData->autoarchive_value;?>">

							<?php
							echo $this->infoBoxHtml("You can use placeholders like '%today%+3days' for a value");
							?>
						</div>
					</div>

					<?php do_action('sim-forms-extra-form-settings', $this); ?>

					<div style='margin-top:10px;'>
						<button class='button builder-permissions-rights-form' type='button'>Advanced</button>
						<div class='permission-wrapper hidden'>
							<?php
							// Splitted fields
							$foundElements = [];
							foreach($this->formElements as $key => $element){
								if($element->type == 'multi-start'){
									$nextKey	= $key;
									while(true){
										$nextKey++;
										$nextElement	= $this->formElements[$nextKey];

										if(!in_array($nextElement->type, $this->nonInputs)){
											$foundElements[$nextElement->id] = $nextElement->name;
										}

										if($nextElement->type == 'multi-end'){
											break;
										}
									}
								}

								$pattern = "/([^\[]+)\[[0-9]*\]/i";
								
								if(preg_match($pattern, $element->name, $matches)){
									//Only add if not found before
									if(!in_array($matches[1], $foundElements)){
										$foundElements[$element->id]	= $matches[1];
									}
								}
							}

							if(!empty($foundElements)){
								?>
								<h4>Select fields where you want to create seperate rows for</h4>
								<?php

								foreach($foundElements as $id=>$element){
									$name	= ucfirst(strtolower(str_replace('_', ' ', $element)));
									
									//Check which option is the selected one
									if(is_array($this->formData->split) && in_array($id, $this->formData->split)){
										$checked = 'checked';
									}else{
										$checked = '';
									}
									echo "<label>";
										echo "<input type='checkbox' name='split[]' value='$id' $checked>   ";
										echo $name;
									echo "</label><br>";
								}
							}
							?>

							<h4>Select roles with form edit rights</h4>
							<select name='full-right-roles[]' multiple>
								<option value=''>---</option>
								<?php
								foreach($userRoles as $key => $roleName){
									if(in_array($key, (array)$this->formData->full_right_roles)){
										$selected = 'selected';
									}else{
										$selected = '';
									}
									echo "<option value='$key' $selected>$roleName</option>";
								}
								?>
							</select>
							<br>
							<h4>Select users with form edit rights</h4>
							<?php
							echo SIM\userSelect('', true, false, '', 'full_right_roles', [], $this->formData->full_right_roles, [1], 'select', '', true);
							?>

							<h4>Select roles who can submit the form on behalve of somebody else</h4>
							<select name='submit-others-form[]' multiple>
								<option value=''>---</option>
								<?php
								foreach($userRoles as $key=>$roleName){
									if(in_array($key, (array)$this->formData->submit_others_form)){
										$selected = 'selected';
									}else{
										$selected = '';
									}
									echo "<option value='$key' $selected>$roleName</option>";
								}
								?>
							</select>

							<h4>Select users who can submit the form on behalve of somebody else</h4>
							<?php
							echo SIM\userSelect('', true, false, '', 'submit_others_form', [], $this->formData->submit_others_form, [1], 'select', '', true);
							?>
						</div>
					</div>
				</div>
				<?php
				echo SIM\addSaveButton('submit-form-setting',  'Save form settings');
				?>
			</form>
			<form method="POST" style='display: inline-block;'>
				<button type='submit' class='button' name="export-form" value='<?php echo $this->formData->id;?>'>Export this form</button>
			</form>
			<form method="POST" style='display: inline-block;'>
				<input type="hidden" class="no-reset" name="page-id" value='<?php echo get_the_ID();?>'>
				<button type='submit' class='button' name="delete-form" value='<?php echo $this->formData->id;?>'>Delete this form</button>
			</form>
		</div>
		<?php
	}
	
	/**
	* Form to specify form reminders
	*/
	public function formReminderForm(){
		$this->getFormReminder();

		/**
		 * Show a warning when no e-mail is set
		 */
		$this->getEmailSettings();

		$triggerFound = false;
		foreach($this->emailSettings as $setting){
			$setting = (object)$setting;

			if($setting->email_trigger == 'shouldsubmit'){
				$triggerFound	= true;
				break;
			}
		}

		if(!$triggerFound){
			?>
			<div class='warning'>
				If you define form reminders you should also define an e-mail with the 'The form is due for submission' trigger
			</div>
			<?php
		}

		?>
		<form action='' method='post' class='sim-form builder' style='margin-top:10px;'>
			<input type='hidden' name='form-id' value='<?php echo $this->formData->id;?>'>

			<?php
			// recurring submission can only happen with forms that are not saved in meta
			if(empty($this->formData->save_in_meta)){

				?>
				Enable Recurring Form Submissions
				<label class="switch">
					<input type="checkbox" name="enable" <?php if(!empty($this->formReminder->frequency)){echo 'checked';}?>>
					<span class="slider round"></span>
				</label>
				<br>
				<br>
				
				<div class='recurring-submissions <?php if(empty($this->formReminder->frequency)){echo 'hidden';}?>'>
					<label>
						<h4>Recurring Submissions</h4>
						Request new form submissions every 
						<input type='number' name='frequency' value='<?php echo $this->formReminder->frequency;?>' style='max-width: 70px;' >
					</label>

					<?php
						foreach(['years', 'months', 'days'] as $period) {
							if(isset($this->formReminder->period) && $this->formReminder->period == $period){
								$checked = 'checked';	
							}else{
								$checked = '';
							}

							?>
							<label>
								<input type='radio' name='period' id='period' value='<?php echo $period;?>' <?php echo $checked;?>>
								<?php echo $period;?>
							</label>
							<?php
						}
						
						$min='';
						$max='';
						if(!empty($this->formReminder->frequency) && !empty($this->formReminder->period)){
							// Selected data can not be in a previous window
							$min = 'min="'.date("Y-m-d", strtotime("-{$this->formReminder->frequency} {$this->formReminder->period} + 1 day")).'"';
							
							// Selected date cannot be in the newxt window
							$max = 'max="'.date("Y-m-d", strtotime("+{$this->formReminder->frequency} {$this->formReminder->period} - 1 day")).'"';
						}
					?>

					<br>
					<label>
						<h4>Date Window</h4>
						Allow Submissions Within This Date Window<br>
						From <input type="date" name='window-start' value='<?php echo $this->formReminder->window_start;?>' <?php echo $min;?> >
						To <input type="date" name='window-end' value='<?php echo $this->formReminder->window_end;?>' <?php echo $max;?> >
					</label>				
				</div>
			<?php
			}
			?>

			<label>
				<h4>Reminder Amount</h4>
				How many times should people be reminded?<br>
				Leave empty for unlimited.<br>
				Once every 
				<?php
				foreach(['week', 'day'] as $period) {
					if(isset($this->formReminder->reminder_period) && $this->formReminder->reminder_period == $period){
						$checked = 'checked';	
					}else{
						$checked = '';
					}

					?>
					<label>
						<input type='radio' name='reminder-period' id='reminder-period' value='<?php echo $period;?>' <?php echo $checked;?>>
						<?php echo $period;?>
					</label>
					<?php
				}
				?>
				for <input type="number" name='reminder-amount' value='<?php echo $this->formReminder->reminder_amount;?>' style='width: 70px;'>
			</label>
			times.
			<br>
			<label>
				<h4>Start reminding from </h4>
				<input type='date' name='reminder-startdate' value='<?php echo $this->formReminder->reminder_startdate;?>' <?php echo "$min $max";?> >
			</label>

			<h4>Warning Exclusions</h4>
			<?php echo $this->warningConditionsForm('conditions', maybe_unserialize($this->formReminder->conditions));?>

			<?php
			echo SIM\addSaveButton('submit-form-reminder',  'Save form reminder');
			?>
		</form>
		<?php
	}

	/**
	 * Form to add warning conditions to an element
	 *
	 * @param	string	$name			The basename for the form conditions inputs.
	 * @param	int		$conditions		The existing conditions
	 */
	public function warningConditionsForm($name, $conditions = ''){
		global $wpdb;
		global $wp_roles;

		if(empty($conditions) || !is_array($conditions)){
			$conditions	= [
				[
					"user_meta_key"	=> '',
					"equation"		=> ''
				]
			];
		}	

		if(!isset($conditions['roles'])){
			$conditions['roles']	= [];
		}
		
		// get all possible user meta keys, not just the one the current user has
		$userMetaKeys	= apply_filters('sim-forms-user-meta-keys', $wpdb->get_col("SELECT DISTINCT `meta_key` FROM `{$wpdb->usermeta}` ORDER BY `meta_key` ASC"));
		
		sort($userMetaKeys, SORT_STRING | SORT_FLAG_CASE);

		$userMetas		= get_user_meta($this->user->ID);

		//Get all available roles
		$userRoles = $wp_roles->role_names;
		
		//Sort the roles
		asort($userRoles);

		ob_start();
		?>
		<datalist id="meta-key">
			<?php
			foreach($userMetaKeys as $key){
				if(isset($userMetas[$key])){
					$value	= $userMetas[$key][0];
				}else{
					$value	= $wpdb->get_var("SELECT `meta_value` FROM `{$wpdb->usermeta}` WHERE meta_key = '$key'");
				}

				// Check if array, store array keys
				$value 	= maybe_unserialize($value);
				$data	= '';
				if(is_array($value)){
					$keys	= implode(',', array_keys($value));
					$data	= "data-keys=$keys";
				}
				echo "<option value='$key' $data>";
			}

			?>
		</datalist>
		<label>Do not warn if user has role</label>
		<select name='<?php echo $name;?>[roles][]' multiple>
			<option value=''>---</option>
			<?php
			foreach($userRoles as $key=>$roleName){
				if(in_array($key, $conditions['roles'])){
					$selected = 'selected';
				}else{
					$selected = '';
				}
				echo "<option value='$key' $selected>$roleName</option>";
			}
			?>
		</select>
		<br>
		<label>Or this user meta evaluation is true</label>
		<div class="conditions-wrapper" style='width: 90vw;z-index: 9999;position: relative;'>
			<?php
			foreach($conditions as $conditionIndex => $condition){
				if(!is_numeric($conditionIndex)){
					continue;
				}

				$arrayKeys	= [];
				if(!empty($condition['meta-key']) && !empty($userMetaKeys[$condition['meta-key']])){
					$arrayKeys	= maybe_unserialize($userMetaKeys[$condition['meta-key']][0]);
				}
				?>
				<div class='warning-conditions element-conditions' data-index='<?php echo $conditionIndex;?>'>
					<input type="hidden" class="no-reset warning-condition combinator" name="<?php echo $name;?>[<?php echo $conditionIndex;?>][combinator]" value="<?php echo $condition['combinator'];?>">

					<input type="text" class="warning-condition meta-key" name="<?php echo $name;?>[<?php echo $conditionIndex;?>][meta-key]" value="<?php echo $condition['meta-key'];?>" list="meta-key" style="width: fit-content;">

					<span class="index-wrapper <?php if(empty($condition['meta-key-index'])){echo 'hidden';}?>">
						<span>and index</span>
						<input type="text" class="warning-condition meta-key-index" name='<?php echo $name;?>[<?php echo $conditionIndex;?>][meta-key-index]' value="<?php echo $condition['meta-key-index'];?>" list="meta-key-index[<?php echo $conditionIndex;?>]" style="width: fit-content;">
						<datalist class="meta-key-index-list warning-condition" id="meta-key-index[<?php echo $conditionIndex;?>]">
							<?php
							if(is_array($arrayKeys)){
								foreach(array_keys($arrayKeys) as $key){
									echo esc_html("<option value='$key'>");
								}
							}
							?>
						</datalist>
					</span>
					
					<select class="warning-condition inline" name='<?php echo $name;?>[<?php echo $conditionIndex;?>][equation]'>
						<?php
						$optionArray	= [
							''			=> '---',
							'=='		=> 'equals',
							'!='		=> 'is not',
							'>'			=> 'greather than',
							'<'			=> 'smaller than',
							'submitted'	=> 'has submitted',
						];
						foreach($optionArray as $option=>$optionLabel){
							if($condition['equation'] == $option){
								$selected	= 'selected=selected';
							}else{
								$selected	= '';
							}
							echo "<option value='$option' $selected>$optionLabel</option>";
						}
						?>
					</select>
					<input  type='text'   class='warning-condition' name='<?php echo $name;?>[<?php echo $conditionIndex;?>][conditional-value]' value="<?php echo $condition['conditional-value'];?>" style="width: fit-content; <?php if($condition['equation'] == 'submitted'){echo 'visibility:hidden;';}?>">
					
					<button type='button' class='warn-cond button <?php if(!empty($condition['combinator']) && $condition['combinator'] == 'and'){echo 'active';}?>'	title='Add a new "AND" rule' value="and">AND</button>
					<button type='button' class='warn-cond button  <?php if(!empty($condition['combinator']) && $condition['combinator'] == 'or'){echo 'active';}?>'	title='Add a new "OR"  rule' value="or">OR</button>
					<button type='button' class='remove-warn-cond  button' title='Remove rule'>-</button>

					<br>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}
		
	/**
	 * Form to setup form e-mails
	 */
	public function formEmailsForm(){
		$this->getEmailSettings();
		$emails 		= $this->emailSettings;
		$defaultFrom	= get_option( 'admin_email' );

		?>
		<div class="emails-wrapper">
			<form action='' method='post' class='sim-form builder'>
				<div class='form-elements'>
					<input type='hidden' class='no-reset' class='formbuilder' name='form-id'	value='<?php echo $this->formData->id;?>'>
					
					<label class="formfield form-label">
						Define any e-mails you want to send.<br>
						You can use placeholders in your inputs.<br>
						These default ones are available:<br><br>
					</label>
					<span class='placeholders' title="Click to copy">%id%</span>
					<?php
					if(!empty($this->formData->split)){
						?>
						<span class='placeholders' title="Click to copy">%subid%</span>
						<?php
					}
					?>
					<span class='placeholders' title="Click to copy">%formurl%</span>
					<span class='placeholders' title="Click to copy">%submissiondate%</span>
					<span class='placeholders' title="Click to copy">%editdate%</span>
					<span class='placeholders' title="Click to copy">%submissiontime%</span>
					<span class='placeholders' title="Click to copy">%edittime%</span>
					<span class='placeholders' title="Click to copy">%viewhash%</span>(include this in any url send to non-logged in users)
					<br>
					All your fieldvalues are available as well:
					<select class='nonice placeholderselect'>
						<option value=''>Select to copy to clipboard</option><?php
						foreach($this->formElements as $element){
							$element->name	= str_replace('[]', '', $element->name);
							if(!in_array($element->type, ['label','info','button','datalist','formstep'])){
								echo "<option>%{$element->name}%</option>";
							}
						}
						do_action('sim-add-email-placeholder-option', $this);
						?>
					</select>
					
					<br>
					<div class='clone-divs-wrapper'>
						<?php
						// Render tab buttons
						foreach($emails as $key => $email){
							$nr		= $key + 1;
							$active	= '';

							if($key === 0){
								$active = 'active';
							}

							echo "<button class='button tablink formbuilder-form $active' type='button' id='show-email-$key' data-target='email-$key' style='margin-right:4px;'>E-mail $nr</button>";
						}

						// Render tab contents
						foreach($emails as $key => $email){
							$email 	= (object) $email;

							$hidden	= 'hidden';
							if($key === 0){
								$hidden = '';
							}
							
							$triggerElementId		= isset($email->submitted_trigger['element']) ? $email->submitted_trigger['element'] : '';
							$triggerEquation		= isset($email->submitted_trigger['equation']) ? $email->submitted_trigger['equation'] : '';
							$triggerValue			= isset($email->submitted_trigger['value']) ? $email->submitted_trigger['value'] : '';
							$triggerValueElementId	= isset($email->submitted_trigger['value-element']) ? $email->submitted_trigger['value-element'] : '';

							?>
							<div class='clone-div tabcontent <?php echo $hidden;?>' id="email-<?php echo $key;?>" data-div-id='<?php echo $key;?>'>
								<h4 class="formfield" style="margin-top:50px; display:inline-block;">E-mail <?php echo $key+1;?></h4>
								<button type='button' class='add button' style='flex: 1;'>+</button>
								<button type='button' class='remove button' style='flex: 1;'>-</button>
								<div style='width:100%;'>
									<input type='hidden' class='no-reset' name='emails[<?php echo $key;?>][email-id]' value='<?php echo $email->id;?>'>
									<input type='hidden' class='no-reset' name='emails[<?php echo $key;?>][form-id]' value='<?php echo $email->form_id;?>'>

									<div class="formfield form-label" style="margin-top:10px;">
										<h4>Trigger</h4>
										Send e-mail when:<br>
										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-trigger]' class='email-trigger' value='submitted' <?php if($email->email_trigger == 'submitted'){echo 'checked';}?>>
											The form is submitted
										</label><br>

										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-trigger]' class='email-trigger' value='shouldsubmit' <?php if($email->email_trigger == 'shouldsubmit'){echo 'checked';}?>>
											The form is due for submission
										</label><br>

										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-trigger]' class='email-trigger' value='submittedcond' <?php if($email->email_trigger == 'submittedcond'){echo 'checked';}?>>
											The form is submitted and meets a condition
										</label><br>

										<div class='submitted-type <?php if($email->email_trigger != 'submittedcond'){echo 'hidden';}?>'>
											<div class='submitted-trigger-type'>
												Element 
												<select class='' name='emails[<?php echo $key;?>][submitted-trigger][element]'>
													<?php
													echo $this->inputDropdown($triggerElementId, "emails[$key][submitted-trigger']['element']");
													?>
												</select>

												<select class='' name='emails[<?php echo $key;?>][submitted-trigger][equation]'>
													<?php
														$optionArray	= [
															''			=> '---',
															'=='		=> 'equals',
															'!='		=> 'is not',
															'>'			=> 'greather than',
															'<'			=> 'smaller than',
															'checked'	=> 'is checked',
															'!checked'	=> 'is not checked',
															'== value'	=> 'equals the value of',
															'!= value'	=> 'does not equal the value of',
															'> value'	=> 'greather than the value of',
															'< value'	=> 'smaller than the value of'
														];

														foreach($optionArray as $option => $optionLabel){
															if($triggerEquation == $option){
																$selected	= 'selected="selected"';
															}else{
																$selected	= '';
															}
															echo "<option value='$option' $selected>$optionLabel</option>";
														}
													?>
												</select>

												<label class='staticvalue <?php if(empty($triggerEquation) || !in_array($triggerEquation, ['==', '!=', '>', '<'])){echo 'hidden';}?>'>
													<input type='text' name='emails[<?php echo $key;?>][submitted-trigger][value]' value="<?php echo $triggerValue;?>" style='width: auto;'>
												</label>

												<select class='dynamicvalue <?php if(empty($triggerEquation) || in_array($triggerEquation, ['==', '!=', '>', '<', 'checked', '!checked'])){echo 'hidden';}?>' name='emails[<?php echo $key;?>][submitted-trigger][value-element]'>
													<?php
														echo $this->inputDropdown($triggerValueElementId, "emails[$key][submitted-trigger][value-element]");
													?>
												</select>
											</div>
										</div>

										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-trigger]' class='email-trigger' value='fieldchanged' <?php if($email->email_trigger == 'fieldchanged'){echo 'checked';}?>>
											A field has changed to a value
										</label>
										<div class='conditional-field-wrapper <?php if($email->email_trigger != 'fieldchanged'){echo 'hidden';}?>'>
											<label class="formfield form-label">Field</label>
											<select name='emails[<?php echo $key;?>][conditional-field]'>
												<?php
												echo $this->inputDropdown( $email->conditional_field );
												?>
											</select>
											
											<label class="formfield form-label">
												Value
												<input type='text' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][conditional-value]' value="<?php echo $email->conditional_value; ?>" style='width:fit-content;'>
											</label>
										</div>

										<br>
										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-trigger]' class='email-trigger' value='fieldschanged' <?php if($email->email_trigger == 'fieldschanged'){	echo 'checked';}?>>
											One or more fields have changed
										</label>
										<div class='conditional-fields-wrapper <?php if($email->email_trigger != 'fieldschanged'){echo 'hidden';}?>'>
											<label class="formfield form-label">Field(s)</label>
											<select name='emails[<?php echo $key;?>][conditional-fields][]' multiple='multiple'>
												<?php
												echo $this->inputDropdown( $email->conditional_fields );
												?>
											</select>
										</div>

										<br>
										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-trigger]' class='email-trigger' value='removed' <?php if($email->email_trigger == 'removed'){echo 'checked';}?>>
											The submission is archived or deleted
										</label>
										<br>
										<?php do_action('sim-forms-after-email-triggers', $key, $email);?>
										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-trigger]' class='email-trigger' value='disabled' <?php if($email->email_trigger == 'disabled'){echo 'checked';}?>>
											Do not send this e-mail
										</label>
										<br>
									</div>
									
									<br>
									<div class="formfield form-label">
										<h4>Sender address</h4>
										Sender e-mail should be:<br>
										<label>
											<input type='radio' name='emails[<?php echo $key;?>][from-email]' class='from-email' value='fixed' <?php if(empty($email->from_email) || $email->from_email == 'fixed'){echo 'checked';}?>>
											Fixed e-mail adress
										</label><br>
										<label>
											<input type='radio' name='emails[<?php echo $key;?>][from-email]' class='from-email' value='conditional' <?php if($email->from_email == 'conditional'){echo 'checked';}?>>
											Conditional e-mail adress
										</label><br>
									</div>
									
									<div class='emailfromfixed <?php if(!empty($email->from_email) && $email->from_email != 'fixed'){echo 'hidden';}?>'>
										<label class="formfield form-label">
											From e-mail
											<input type='text' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][from]' value="<?php if(empty($email->from)){echo $defaultFrom;} else{echo $email->from;} ?>">
										</label>
									</div>
									
									<div class='emailfromconditional <?php if($email->from_email != 'conditional'){echo 'hidden';}?>'>
										<div class='clone-divs-wrapper'>
											<?php
											if(!is_array($email->conditional_from_email)){
												$email->conditional_from_email = [
													[
														'fieldid'	=> '',
														'value'		=> '',
														'email'		=> ''
													]
												];
											}
											foreach(array_values($email->conditional_from_email) as $fromKey => $fromEmail){
												?>
												<div class='clone-div' data-div-id='<?php echo $fromKey;?>'>
													<fieldset class='form-email-fieldset'>
														<legend class="formfield button-wrapper">
															<span class='text'>Condition <?php echo $fromKey+1;?></span>
															<button type='button' class='add button' style='flex: 1;'>+</button>
															<button type='button' class='remove button' style='flex: 1;'>-</button>
														</legend>
														If
														<select name='emails[<?php echo $key;?>][conditional-from-email][<?php echo $fromKey;?>][fieldid]'>
															<?php
															echo $this->inputDropdown($fromEmail['fieldid']);
															?>
														</select>
														<label class="formfield form-label">
															equals
															<input type='text' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][conditional-from-email][<?php echo $fromKey;?>][value]' value="<?php echo $fromEmail['value'];?>">
														</label>
														<label class="formfield form-label">
															then from e-mail address should be:<br>
															<input type='email' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][conditional-from-email][<?php echo $fromKey;?>][email]' value="<?php echo $fromEmail['email'];?>">
														</label>
													</fieldset>
												</div>
												<?php
											}
											?>
											<br>
											<label class="formfield form-label">
												Else the e-mail will be
												<input type='text' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][else-from]' value="<?php echo $email->else_from; ?>">
											</label>
										</div>
									</div>
									
									<br>
									<h4>Recipient address</h4>
									<div class="formfield tofieldlabel">
										Recipient e-mail should be:<br>
										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-to]' class='email-to' value='fixed' <?php if(empty($email->email_to) || $email->email_to == 'fixed'){echo 'checked';}?>>
											Fixed e-mail adress
										</label><br>
										<label>
											<input type='radio' name='emails[<?php echo $key;?>][email-to]' class='email-to' value='conditional' <?php if($email->email_to == 'conditional'){echo 'checked';}?>>
											Conditional e-mail adress
										</label><br>
									</div>
									<br>
									<div class='email-tofixed <?php if(!empty($email->email_to) && $email->email_to != 'fixed'){echo 'hidden';}?>'>
										<label class="formfield form-label">
											To e-mail
											<input type='text' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][to]' value="<?php if(empty($email->to)){echo '%email%';}else{echo $email->to;} ?>">
										</label>
									</div>

									<div class='email-toconditional <?php if($email->email_to != 'conditional'){echo 'hidden';}?>'>
										<div class='clone-divs-wrapper'>
											<?php
											if(!is_array($email->conditional_email_to)){
												$email->conditional_email_to = [
													[
														'fieldid'	=> '',
														'value'		=> '',
														'email'		=> ''
													]
												];
											}
											
											foreach($email->conditional_email_to as $toKey => $toEmail){
												?>
												<div class='clone-div' data-div-id='<?php echo $toKey;?>'>
													<fieldset class='form-email-fieldset button-wrapper'>
														<legend class="formfield">
															<span class='text'>Condition <?php echo $toKey+1;?></span>
															<button type='button' class='add button' style='flex: 1;'>+</button>
															<button type='button' class='remove button' style='flex: 1;'>-</button>
														</legend>
														If
														<select name='emails[<?php echo $key;?>][conditional-email-to][<?php echo $toKey;?>][fieldid]'>
															<?php
															echo $this->inputDropdown($toEmail['fieldid']);
															?>
														</select>
														<label class="formfield form-label">
															equals
															<input type='text' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][conditional-email-to][<?php echo $toKey;?>][value]' value="<?php echo $toEmail['value'];?>">
														</label>
														<label class="formfield form-label">
															then from e-mail address should be:<br>
															<input type='email' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][conditional-email-to][<?php echo $toKey;?>][email]' value="<?php echo $toEmail['email'];?>">
														</label>
													</fieldset>
												</div>
												<?php
											}
											?>
											<br>
											<label class="formfield form-label">
												Else the e-mail will be
												<input type='text' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][else-to]' value="<?php echo $email->else_to; ?>">
											</label>
										</div>
									</div>

									<br>
									<div class="formfield form-label">
										<h4>Subject</h4>
										<input type='text' class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][subject]' value="<?php echo $email->subject?>">
									</div>
									
									<br>
									<div class="formfield form-label">
										<h4>Content</h4>
										<?php
										$settings = array(
											'wpautop' => false,
											'media_buttons' => false,
											'forced_root_block' => true,
											'convert_newlines_to_brs'=> true,
											'textarea_name' => "emails[$key][message]",
											'textarea_rows' => 10
										);
									
										echo wp_editor(
											$email->message,
											"{$this->formName}_email_message_$key",
											$settings
										);
										?>
									</div>
									
									<br>
									<div class="formfield form-label">
										<h4>Additional headers like 'Reply-To'</h4>
										<textarea class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][headers]'><?php
											echo $email->headers?>
										</textarea>
									</div>
									
									<br>
									<div class="formfield form-label">
										<h4>Attachments</h4>
										Form values that should be attached to the e-mail
										<textarea class='formbuilder form-element-setting' name='emails[<?php echo $key;?>][files]'><?php
											echo $email->files?>
										</textarea>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
				echo SIM\addSaveButton('submit-form-emails','Save form email configuration');
				?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Form to add or edit a new form element
	 */
	public function elementBuilderForm($element=null){
		$heading	= "Please fill in the form to add a new form element";

		if(is_numeric($element)){
			$element = $this->getElementById($element);

			if($element ){
				$heading	= "Change this element";
			}else{
				$element	= null;
			}
		}

		$numericElements	= [];
		$dateElements		= [];
		foreach($this->formElements as $el){
			if(in_array($el->type, ['date', 'number', 'range', 'week', 'month']) ){
				$numericElements[]	= $el->id;
			}
			if(in_array($el->type, ['date', 'week', 'month']) ){
				$dateElements[]	= $el->id;
			}
		}

		$nonInputClasses	= 'non-'.implode(' non-', $this->nonInputs);
		
		ob_start();
		?>
		<div class="form-wrapper">
			<h4><?php echo $heading;?></h4><br>

			<input type="hidden" class="no-reset" name="form-id" value="<?php echo $this->formData->id;?>">

			<input type="hidden" class="no-reset" name="formfield[form-id]" value="<?php echo $this->formData->id;?>">
			
			<input type="hidden" class="no-reset" name="element-id" value="<?php if( $element != null){echo $element->id;}?>">
			
			<input type="hidden" class="no-reset" name="insert-after">
			
			<input type="hidden" class="no-reset" name="formfield[width]" value="100">
			
			<label>Element type</label><br>
			<select class="formbuilder element-type " name="formfield[type]" required>
				<optgroup label="Normal elements">
					<?php
					$options=[
						"button"	=> "Button",
						"checkbox"	=> "Checkbox",
						"color"		=> "Color",
						"date"		=> "Date",
						"select"	=> "Dropdown",
						"email"		=> "E-mail",
						"file"		=> "File upload",
						"image"		=> "Image upload",
						"label"		=> "Label",
						"month"		=> "Month",
						"number"	=> "Number",
						"password"	=> "Password",
						"tel"		=> "Phonenumber",
						"radio"		=> "Radio",
						"range"		=> "Range",
						"text"		=> "Text",
						"textarea"	=> "Text (multiline)",
						"time"		=> "Time",
						"url"		=> "Url",
						"week"		=> "Week"
					];

					foreach($options as $key=>$option){
						if($element != null && $element->type == $key){
							$selected = 'selected="selected"';
						}else{
							$selected = '';
						}
						echo "<option value='$key' $selected>$option</option>";
					}
					?>
					
				</optgroup>
				<optgroup label="Special elements">
					<?php
					$options	= [
						"hcaptcha"		=> "hCaptcha",
						"recaptcha"		=> "reCaptcha",
						"turnstile"		=> "Cloudflare Turnstile",
						"datalist"		=> "Datalist",
						"div-start"		=> "Div Container - start",
						"div-end"		=> "Div Container - end",
						"formstep"		=> "Multistep",
						"info"			=> "Infobox",
						"multi-start"	=> "Multi-answer - start",
						"multi-end"		=> "Multi-answer - end",
						"p"				=> "Paragraph",
						"php"			=> "Custom code"
					];

					$options	= apply_filters('sim-special-form-elements', $options);

					foreach($options as $key=>$option){
						if($element != null && $element->type == $key){
							$selected = 'selected="selected"';
						}else{
							$selected = '';
						}
						echo "<option value='$key' $selected>$option</option>";
					}
					?>
				</optgroup>
			</select>
			<br>
			
			<div name='elementname' class='element-option wide reverse not-label not-php not-formstep button shouldhide' style='background-color: unset;'>
				<label>
					<div style='text-align: left;'>Specify a name for the element</div>
					<input type="text" class="formbuilder wide" name="formfield[name]" value="<?php if($element != null){echo $element->name;}?>">
				</label>
				<br><br>
			</div>
			
			<div name='add-text' class='element-option multi-start shouldhide'>
				<label>
					<div style='text-align: left;'>Specify the text for the 'add' button</div>
					<input type="text" class="formbuilder wide" name="formfield[add]" value="<?php if($element != null && $element->add != null){echo $element->add;}else{echo '+';}?>">
				</label>
				<br><br>
			</div>

			<div name='remove-text' class='element-option multi-start shouldhide'>
				<label>
					<div style='text-align: left;'>Specify the text for the 'remove' button</div>
					<input type="text" class="formbuilder wide" name="formfield[remove]" value="<?php if($element != null && $element->remove != null){echo $element->remove;}else{echo '-';}?>">
				</label>
				<br><br>
			</div>

			<div name='functionname' class='element-option wide hidden php'>
				<label>
					Specify the functionname
					<input type="text" class="formbuilder wide" name="formfield[functionname]" value="<?php if($element != null){echo $element->functionname;}?>">
				</label>
				<br><br>
			</div>
			
			<div name='label-text' class='element-option label button formstep hidden wide' style='background-color: unset;'>
				<label>
					<div style='text-align: left;'>Specify the <span class='element-type '>label</span> text</div>
					<input type="text" class="formbuilder wide" name="formfield[text]" value="<?php if($element != null){echo $element->text;}?>">
				</label>
				<br><br>
			</div>

			<div name='upload-options' class='element-option hidden file image'>
				<label>
					<input type="checkbox" class="formbuilder" name="formfield[library]" value="1" <?php if($element != null && $element->library){echo 'checked';}?>>
					Add the <span class='filetype'>file</span> to the library
				</label>
				<br><br>

				<label>
					<input type="checkbox" class="formbuilder" name="formfield[editimage]" value="1" <?php if($element != null && $element->editimage){echo 'checked';}?>>
					Allow people to edit an image before uploading it
				</label>
				<br>
				<br>

				<label>
					Name of the folder the <span class='filetype'>file</span> should be uploaded to.<br>
					<input type="text" class="formbuilder" name="formfield[foldername]" value="<?php if($element != null){echo $element->foldername;}?>">
				</label>
			</div>
			
			<div name='wrap' class='element-option reverse not-p not-php not-file not-image not-multi-start not-multi-end shouldhide'>
				<label>
					<input type="checkbox" class="formbuilder" name="formfield[wrap]" value="1" <?php if($element != null && $element->wrap){echo 'checked';}?>>
					Group together with next element
				</label>
				<br><br>
			</div>
			
			<div name='infotext' class='element-option info p hidden'>
				<label>
					Specify the text for the <span class='type'>info-box</span>
					<?php
					$settings = array(
						'wpautop'					=> false,
						'media_buttons'				=> false,
						'forced_root_block'			=> true,
						'convert_newlines_to_brs'	=> true,
						'textarea_name'				=> "formfield[infotext]",
						'textarea_rows'				=> 10,
						'editor_class'				=> 'formbuilder'
					);
				
					if(empty($element->text)){
						$content	= '';
					}else{
						$content	= $element->text;
					}
					echo wp_editor(
						$content,
						$this->formData->name."_infotext",	//editor should always have an unique id
						$settings
					);
					?>
					
				</label>
				<br>
			</div>
			
			<div name='multiple' class='element-option reverse <?php echo $nonInputClasses;?> shouldhide'>
				<label>
					<input type="checkbox" class="formbuilder" name="formfield[multiple]" value="1" <?php if($element != null && $element->multiple){echo 'checked';}?>>
					Allow multiple answers
				</label>
				<br>
				<br>
			</div>
			
			<div name='valuelist' class='element-option datalist radio select checkbox hidden'>
				<label>
					Specify the values, one per line
					<textarea class="formbuilder" name="formfield[valuelist]"><?php if($element != null){echo trim($element->valuelist);}?></textarea>
				</label>
				<br>
			</div>

			<div name='select-options' class='element-option datalist radio select checkbox multi-answer-element hidden'>
				<label class='block'>Specify an options group if desired</label>
				<select class="formbuilder" name="formfield[default-array-value]">
					/*<option value="">---</option>*/
					<?php
					$this->buildDefaultsArray();
					foreach($this->defaultArrayValues as $key=>$field){
						if($element != null && $element->default_array_value == $key){
							$selected = 'selected="selected"';
						}else{
							$selected = '';
						}
						$optionName	= ucfirst(str_replace('_', ' ', $key));
						echo "<option value='$key' $selected>$optionName</option>";
					}
					?>
				</select>
			</div>

			<div name='defaults' class='element-option reverse not-php not-file <?php echo $nonInputClasses;?> shouldhide'>
				<label class='block'>Specify a default value if desired</label>

				<input type='text' class="formbuilder" name="formfield[default-value]" list='defaults' value='<?php if($element != null){echo trim($element->default_value);}?>'>

				<datalist id='defaults'>
					<?php
					foreach($this->defaultValues as $key=>$field){
						$optionName	= ucfirst(str_replace('_',' ',$key));
						echo "<option value='$key'>$optionName</option>";
					}
					?>
				</datalist>
			</div>

			<?php
			do_action('sim-after-formbuilder-element-options', $element);
			?>
			<br>
			<div name='element-options' class='element-option reverse not-php <?php echo $nonInputClasses;?> shouldhide'>
				<label>
					Specify any options like styling
					<textarea class="formbuilder" name="formfield[options]"><?php if($element != null){echo trim($element->options);}?></textarea>
				</label><br>
				<br>
				
				<?php
				$meta	= false;
				if(!empty($this->formData->save_in_meta)){
					$meta	= true;
				}

				if($meta){
					?>
					<h3>Warning conditions</h3>
					<label class="option-label">
						<input type="checkbox" class="formbuilder" name="formfield[mandatory]" value="1" <?php if($element != null && $element->mandatory){echo 'checked';}?>>
						People should be warned by e-mail/signal if they have not filled in this field.
					</label><br>
					<br>

					<label class="option-label">
						<input type="checkbox" class="formbuilder" name="formfield[recommended]" value="1" <?php if($element != null && $element->recommended){echo 'checked';}?>>
						People should be notified on their homepage if they have not filled in this field.
					</label><br>
					<br>

					<div <?php if($element == null || (!$element->mandatory && !$element->recommended)){echo "class='hidden'";}?>>
						<?php
						if($element == null){
							$conditions	= '';
						}else{
							$conditions = $element->warning_conditions;
						}
						echo $this->warningConditionsForm('formfield[warning-conditions]', $conditions);
						?>
					</div>
					<?php
				}else{
					?>
					<label class="option-label">
						<input type="checkbox" class="formbuilder" name="formfield[required]" value="1" <?php if($element != null && $element->required){echo 'checked';}?>>
						This should be a required field
					</label><br>
					<label class="option-label">
						<input type="checkbox" class="formbuilder" name="formfield[mandatory]" value="1" <?php if($element != null && $element->mandatory){echo 'checked';}?>>
						This should be a conditional required field: its only required when visible
					</label><br>
					<br>
					<?php
				}
				?>
			</div>
			<label class="option-label element-option not-multi-start not-multi-end reverse shouldhide">
				<input type="checkbox" class="formbuilder" name="formfield[hidden]" value="1" <?php if($element != null && $element->hidden){echo 'checked';}?>>
				Hidden field
			</label><br>

			<?php
			if($element == null){
				$text	= "Add";
			}else{
				$text	= "Change";
			}
		?>
		</div>
		<?php

		/**
		 * Filters the form contents of adding or editing an element
		 * 
		 * @param	string	$html		The form html
		 * @param	object	$object		The FormBuilderForm Instance
		 * @param	object	$element	The current element which is edited
		 */
		$formContents	= apply_filters('sim-forms-element-form-content', ob_get_clean(), $this, $element);

		ob_start();
		?>
		<script>
			const numericElements	= <?php echo json_encode($numericElements); ?>;
			const dateElements		= <?php echo json_encode($dateElements); ?>;
		</script>
		<form action="" method="post" name="add-form-element-form" class="form-element-form sim-form" data-add-empty=1>
			<div style="display: none;" class="error"></div>
			<?php

			echo $formContents;

			echo SIM\addSaveButton('submit-form-element',"$text form element"); ?>
		</form>
		<?php

		return ob_get_clean();
	}

	/**
	 * Form to add conditions to an element
	 *
	 * A field can have one or more conditions applied to it like:
	*		1) hide when field X is Y
	*		2) Show when field X is Z
	*	Each condition can have multiple rules like:
	*		Hide when field X is Y and field A is B
	*
	*	The array structure is therefore:
	*		[
	*			[0][
	*				[rules]
	*						[0]
	*						[1]
	*				[action]
	*			[1]
	*				[rules]
	*						[0]
	*				[action]
	*		]
	*
	*	It is also stored at the conditional fields to be able to create efficient JavaScript
	 *
	 * @param int	$elementId	The id of the element. Default -1 for empty
	 */
	public function elementConditionsForm($elementId = -1){
		if($elementId != -1){
			$element	= $this->getElementById($elementId);
		}

		if($elementId == -1 || empty($element->conditions)){
			if(gettype($element) != 'object'){
				$element	= new stdClass();
			}

			$dummyFieldCondition['rules'][0]["conditional-field"]	= "";
			$dummyFieldCondition['rules'][0]["equation"]				= "";
			$dummyFieldCondition['rules'][0]["conditional-field-2"]	= "";
			$dummyFieldCondition['rules'][0]["equation-2"]			= "";
			$dummyFieldCondition['rules'][0]["conditional-value"]	= "";
			$dummyFieldCondition["action"]					= "";
			$dummyFieldCondition["target_field"]			= "";
			
			if($elementId == -1){
				$elementId			= 0;
			}
			$element->conditions = [$dummyFieldCondition];
		}
		
		$conditions = $element->conditions;
		
		ob_start();
		$counter = 0;
		foreach($this->formElements as $el){
			$copyTo	= (array)maybe_unserialize($el->conditions);
			if(!empty($copyTo['copyto']) && in_array($elementId, $copyTo['copyto'])){
				$counter++;
				?>
				<div class="form-element-wrapper" data-element-id="<?php echo $el->id;?>" data-form-id="<?php echo $this->formData->id;?>">
					<button type="button" class="edit-form-element button" title="Jump to conditions element">View conditions of '<?php echo $el->name;?>'</button>
				</div>
				<?php
			}
		}

		if($counter > 0){
			$jumbButtonHtml =  ob_get_clean();
			if($counter==1){
				$counter	= 'another element';
				$any 		= 'the';
			}else{
				$counter	= "$counter other elements";
				$any 		= 'any';
			}
			
			ob_start();
			?>
			<div>
				This element has some conditions defined by <?php echo $counter;?>.<br>
				Click on <?php echo $any;?> button below to view.
				<?php echo $jumbButtonHtml;?>
			</div><br><br>
			<?php
		}
		?>
		
		<form action='' method='post' name='add-form-element-conditions-form'>
			<h3>Form element conditions</h3>
			<input type='hidden' class='no-reset' class='element-condition' name='form-id' value='<?php echo $this->formData->id;?>'>
			
			<input type='hidden' class='no-reset' class='element-condition' name='elementid' value='<?php echo $elementId;?>'>

			<?php
			// get the last numeric array key
			$lastCondtionKey = end(array_filter(array_keys($conditions), 'is_int'));
			foreach($conditions as $conditionIndex=>$condition){
				if(!is_numeric($conditionIndex)){
					continue;
				}
				?>
				<div class='condition-row' data-condition-index='<?php echo $conditionIndex;?>'>
					<span style='font-weight: 600;'>If</span>
					<br>
					<?php
					$lastRuleKey = array_key_last($condition['rules']);
					foreach($condition['rules'] as $ruleIndex=>$rule){
						?>
						<div class='rule-row' data-rule-index='<?php echo $ruleIndex;?>'>
							<input type='hidden' class='no-reset' class='element-condition combinator' name='element-conditions[<?php echo $conditionIndex;?>][rules][<?php echo $ruleIndex;?>][combinator]' value='<?php echo $rule['combinator']; ?>'>
						
							<select class='element-condition condition-select conditional-field' name='element-conditions[<?php echo $conditionIndex;?>][rules][<?php echo $ruleIndex;?>][conditional-field]' required>
								<?php
									echo $this->inputDropdown($rule['conditional-field'], $elementId);
								?>
							</select>

							<select class='element-condition condition-select equation' name='element-conditions[<?php echo $conditionIndex;?>][rules][<?php echo $ruleIndex;?>][equation]' required>
								<?php
									$optionArray	= [
										''			=> '---',
										'changed'	=> 'has changed',
										'clicked'	=> 'is clicked',
										'=='		=> 'equals',
										'!='		=> 'is not',
										'>'			=> 'greather than',
										'<'			=> 'smaller than',
										'checked'	=> 'is checked',
										'!checked'	=> 'is not checked',
										'== value'	=> 'equals the value of',
										'!= value'	=> 'does not equal the value of',
										'> value'	=> 'greather than the value of',
										'< value'	=> 'smaller than the value of',
										'-'			=> 'minus the value of',
										'+'			=> 'plus the value of',
										'visible'	=> 'is visible',
										'invisible'	=> 'is not visible',
									];

									foreach($optionArray as $option=>$optionLabel){
										if($rule['equation'] == $option){
											$selected	= 'selected="selected"';
										}else{
											$selected	= '';
										}
										echo "<option value='$option' $selected>$optionLabel</option>";
									}
								?>
							</select>

							<?php
							//show if -, + or value field is target value
							if($rule['equation'] == '-' || $rule['equation'] == '+' || str_contains($rule['equation'], 'value')){
								$hidden = '';
							}else{
								$hidden = 'hidden';
							}
							?>
							
							<span class='<?php echo $hidden;?> condition-form conditional-field-2'>
								<select class='element-condition condition-select' name='element-conditions[<?php echo $conditionIndex;?>][rules][<?php echo $ruleIndex;?>][conditional-field-2]'>
								<?php
									echo $this->inputDropdown($rule['conditional-field-2'], $elementId);
								?>
								</select>
							</span>
							
							<?php
							if($rule['equation'] == '-' || $rule['equation'] == '+'){
								$hidden = '';
							}else{
								$hidden = 'hidden';
							}
							?>

							<span class='<?php echo $hidden;?> condition-form equation-2'>
								<select class='element-condition condition-select' name='element-conditions[<?php echo $conditionIndex;?>][rules][<?php echo $ruleIndex;?>][equation-2]'>
									<?php
										$optionArray	= [
											''			=> '---',
											'=='		=> 'equals',
											'!='		=> 'is not',
											'>'			=> 'greather than',
											'<'			=> 'smaller than',
										];
										foreach($optionArray as $option=>$optionLabel){
											if($rule['equation-2'] == $option){
												$selected	= 'selected="selected"';
											}else{
												$selected	= '';
											}
											echo "<option value='$option' $selected>$optionLabel</option>";
										}
									?>
								</select>
							</span>
							<?php
							if(str_contains($rule['equation'], 'value') || in_array($rule['equation'], ['changed','checked','!checked', 'visible', 'invisible'])){
								$hidden = 'hidden';
							}else{
								$hidden = '';
							}
							?>
							<input  type='text'   class='<?php echo $hidden;?> element-condition condition-form' name='element-conditions[<?php echo $conditionIndex;?>][rules][<?php echo $ruleIndex;?>][conditional-value]' value="<?php echo $rule['conditional-value'];?>">
							
							<button type='button' class='element-condition and-rule condition-form button <?php if(!empty($rule['combinator']) && $rule['combinator'] == 'AND'){echo 'active';}?>'	title='Add a new "AND" rule to this condition'>AND</button>
							<button type='button' class='element-condition or-rule condition-form button  <?php if(!empty($rule['combinator']) && $rule['combinator'] == 'OR'){echo 'active';}?>'	title='Add a new "OR"  rule to this condition'>OR</button>
							<button type='button' class='remove-condition condition-form button' title='Remove rule or condition'>-</button>
							<?php
							if($conditionIndex == $lastCondtionKey && $ruleIndex == $lastRuleKey){
								?>
								<button type='button' class='add-condition condition-form button' title='Add a new condition'>+</button>
								<button type='button' class='add-condition opposite condition-form button' title='Add a new condition, opposite to to the previous one'>Add opposite</button>
							<?php
							}
							?>
						</div>
						<?php
					}
					?>
					<br>
					<span style='font-weight: 600;'>then</span><br>
					
					<div class='action-row'>
						<div class='radio-wrapper condition-form'>
							<label>
								<input type='radio' name='element-conditions[<?php echo $conditionIndex;?>][action]' class='element-condition' value='show' <?php if($condition['action'] == 'show'){echo 'checked';}?> required>
								Show this element
							</label><br>
							
							<label>
								<input type='radio' name='element-conditions[<?php echo $conditionIndex;?>][action]' class='element-condition' value='hide' <?php if($condition['action'] == 'hide'){echo 'checked';}?> required>
								Hide this element
							</label><br>
							
							<label>
								<input type='radio' name='element-conditions[<?php echo $conditionIndex;?>][action]' class='element-condition' value='toggle' <?php if($condition['action'] == 'toggle'){echo 'checked';}?> required>
								Toggle this element
							</label><br>
							
							<label>
								<input type='radio' name='element-conditions[<?php echo $conditionIndex;?>][action]' class='element-condition' value='value' <?php if($condition['action'] == 'value'){echo 'checked';}?> required>
								Set property
							</label>
							<input type="text" list="propertylist" name="element-conditions[<?php echo $conditionIndex;?>][property-name1]" class='element-condition' placeholder="property name" value="<?php echo $condition['property-name1'];?>">
							<label> to:</label>
							<textarea class='element-condition' name="element-conditions[<?php echo $conditionIndex;?>][action-value]" rows='1'><?php echo $condition['action-value'];?></textarea>
							<br>
							<label>
								<input type='radio' name='element-conditions[<?php echo $conditionIndex;?>][action]' class='element-condition' value='property' <?php if($condition['action'] == 'property'){echo 'checked';}?> required>
								Set the
							</label>
						
							<datalist id="propertylist">
								<option value="value">
								<option value="min">
								<option value="max">
							</datalist>
							<label>
								<input type="text" list="propertylist" name="element-conditions[<?php echo $conditionIndex;?>][property-name]" class='element-condition' placeholder="property name" value="<?php echo $condition['property-name'];?>">
								property to the value of
							</label>
							
							<select class='element-condition condition-select' name='element-conditions[<?php echo $conditionIndex;?>][property-value]'>
								<?php echo $this->inputDropdown($condition['property-value'], $elementId);?>
							</select>

							<?php
							if(!empty($condition['property-value'])){
								$type	= $this->getElementById($condition['property-value'], 'type');
							}else{
								$type	= '';
							}
							$hidden	= 'hidden';
							$hidden2= 'hidden';
							if(in_array($type, ['date', 'number', 'range', 'week', 'month']) ){
								$hidden	= '';

								if(in_array($type, ['date', 'week', 'month']) ){
									$hidden2	= '';
								}
							}
							?>
							<label class='addition <?php echo $hidden;?>'>
								+ <input type='number' name="element-conditions[<?php echo $conditionIndex;?>][addition]" class='element-condition' value="<?php echo $condition['addition'];?>" style='width: 60px;'>
								<span class='days <?php echo $hidden2;?>'> days</span>
							</label>
							<br>
						</div>
					</div>
				</div>
				<?php
			}
			?>
			<br>
			<div class="copyfieldswrapper">
				<label>
					<input type='checkbox' class="showcopyfields" <?php if(!empty($conditions['copyto'])){echo 'checked';}?>>
					Apply visibility conditions to other fields
				</label><br><br>
				
				<div class='copyfields <?php if(empty($conditions['copyto'])){echo 'hidden';}?>'>
					Check the fields these conditions should apply to as well,<br>
					This holds only for visibility conditions (show, hide or toggle).<br><br>
					<?php
					foreach($this->formElements as $element){
						//do not show the current element itself or wrapped labels
						if($element->id != $elementId && empty($element->wrap)){
							if(!empty($conditions['copyto'][$element->id])){
								$checked = 'checked';
							}else{
								$checked = '';
							}

							$name	= ucfirst(str_replace('_', ' ', $element->name));
							if(str_contains($name, '[]')){
								$name	.= " ($element->id)";
							}
							
							echo "<label>";
								echo "<input type='checkbox' name='element-conditions[copyto][{$element->id}]' value='{$element->id}' $checked>";
								echo $name;
							echo "</label><br>";
						}
					}
					?>
				</div>
			</div>
			<?php
			echo SIM\addSaveButton('submit-form-condition','Save conditions'); ?>
		</form>
		<?php
		return ob_get_clean();
	}
}
