<?php
namespace SIM\FORMS;
use SIM;

class DisplayForm extends SubmitForm{
	use ElementHtml;
	use CreateJs;

	public $wrap;
	public $multiWrapValueCount;
	public $multiWrapElementCount;
	public $minElForTabs;
	public $nextElement;
	public $prevElement;
	public $currentElement;
	public $usermeta;
	private $tabId;

	public function __construct($atts=[]){
		parent::__construct();

		$this->isFormStep				= false;
		$this->wrap						= false;
		$this->isMultiStepForm			= '';
		$this->formStepCounter			= 0;
		$this->minElForTabs				= 6;
		if(!empty($atts)){
			$this->processAtts($atts);
			$this->getForm();
			$this->getAllFormElements();

			$this->getUserId($atts);
		}
	}

	/**
	 * Check if we are editing on behalf of someone else, and we have permission for that
	 *
	 */
	protected function getUserId($atts=[]){
		if(
			array_intersect($this->userRoles, $this->submitRoles) 	&&	// we have the permission to submit on behalf on someone else
			!empty(($_GET['userid']))								&&
			is_numeric($_GET['userid'])								&& // and the userid parameter is set in the url
			empty($atts['userid'])										// and the user id is not given in the shortcode
		){
			$this->userId	= $_GET['userid'];
		}
	}

	/**
	 * Renders the add and remove buttons for a multi-answer group
	 */
	protected function renderButtons(){
		ob_start();
		
		?>
		<div class='button-wrapper' style='margin: auto;'>
			<button type='button' class='add button' style='flex: 1;max-width: max-content;'><?php echo $this->prevElement->add;?></button>
			<button type='button' class='remove button' style='flex: 1;max-width: max-content;'><?php echo $this->prevElement->remove;?></button>
		</div><?php

		return ob_get_clean();
	}

	/**
	 * Renders the start of a multi wrap group
	 *
	 * @param	int		$index		The index index of the copies
	 */
	protected function multiWrapStart($index, $element){
		$class	= '';

		// Wrap in a tab if it is a big one
		if($this->multiWrapElementCount >= $this->minElForTabs){
			$hidden	= 'hidden';

			if($index === 0){
				$hidden = '';
			}

			$this->tabId	= str_replace(' ', '-', $element->nicename);
			$class	= "tabcontent $hidden' id='$this->tabId-$index";
		}

		$style	= '';
		if($this->multiWrapElementCount < $this->minElForTabs){
			$style	= 'style="display:flex;"';
		}

		$this->multiInputsHtml[$index]   = "<div class='clone-div $class' data-divid='$index' $style>";
		$this->multiInputsHtml[$index]	.= $this->renderButtons();
		$this->multiInputsHtml[$index]	.= "<div class='multi-input-wrapper'>";
	}

	/**
	 * All html for closing a multi wrap
	 */
	protected function multiWrapEnd($index, $element){
		//close any label first before adding the buttons
		if($this->wrap == 'label'){
			$this->multiInputsHtml[$index] .= "</label>";
		}
		
		//close select
		if($element->type == 'select'){
			$this->multiInputsHtml[$index] .= "</select>";
		}

		$this->multiInputsHtml[$index] .= "</div>"; //close multi-input-wrapper div

		if($this->multiWrapElementCount < $this->minElForTabs){
			$this->multiInputsHtml[$index] .= $this->renderButtons();
		}
		
		$this->multiInputsHtml[$index] .= "</div>"; //close clone-div
	}
	
	/**
	 * Renders the html for element who can have multiple inputs
	 *
	 * @param	object	$element		The element
	 * @param	int		$width			The width of the elements
	 */
	protected function processMultiFields($element, $width){
		$class	= 'input-wrapper';

		//Check if element needs to be hidden
		if(!empty($element->hidden)){
			$class .= ' hidden';
		}
		
		//if the current element is required or this is a label and the next element is required
		if(
			!empty($element->required)		||
			!empty($element->mandatory)		||
			$element->type == 'label'		&&
			(
				$this->nextElement->required	||
				$this->nextElement->mandatory
			)
		){
			$class .= ' required';
		}

		$elementHtml = $this->getElementHtml($element);
		
		//close the label element after the field element
		if($this->wrap && !$element->wrap){
			$elementHtml .= "</div>";
			if($this->wrap == 'label'){
				$elementHtml .= "</label>";
			}
			$this->wrap = false;
		}elseif(!$this->wrap){
			if($element->type == 'info'){
				$elementHtml .= "<div class='$class info'>";
			}else{
				if(!empty($element->wrap)){
					$class	.= ' flex';
				}
				$elementHtml = "<div class='$class' style='width:$width%;'>$elementHtml</div>";
			}
		}
		
		if(in_array($element->type, $this->nonInputs)){
			//Get the field values of the next element as this does not have any
			$values		= $this->getElementValues($this->nextElement);
		}else{
			$values		= $this->getElementValues($element);
		}

		// Get the values we need
		if(empty($this->formData->save_in_meta)){
			if(!empty($values['defaults'])){
				$values		= array_values((array)$values['defaults']);
			}
		}else{
			if(!empty($values['metavalue'])){
				$values		= array_values((array)$values['metavalue']);
			}
		}
		
		// Determine if we should wrap
		if(
			!$this->wrap											&&			// We are currently not wrapping
			$element->wrap											&& 			// we should wrap around next element
			is_object($this->nextElement)							&& 			// and there is a next element
			!in_array($this->nextElement->type, ['select','php','formstep'])	// and the next element type is not a select or php element
		){
			$this->wrap = $element->type;
		}

		// Create as many clones as the maximum value of one of the elements 
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}

			// prepare the base html for duplicating
			$elementHtml	= $this->prepareElementHtml($element, $index, $elementHtml, $val);

			// First element in a multi answer wrapper
			if($this->prevElement->type == 'multi_start'){
				$this->multiWrapStart($index, $element);
			}
			
			// elements between start and end
			$this->multiInputsHtml[$index] .= force_balance_tags($elementHtml);
			
			// Last element in the multi wrap, write the buttons and closing div
			if($this->nextElement->type == 'multi_end'){
				$this->multiWrapEnd($index, $element);
			}
		}
	}

	/**
	 * Build all html for a particular element including edit controls.
	 *
	 * @param	object	$element		The element
	 * @param	int		$key			The key in case of a multi element. Default 0
	 *
	 * @return	string					The html
	 */
	public function buildHtml($element, $key=0){

		if($element->type == 'div_start'){
			$class		= 'input-wrapper';
			if($element->hidden){
				$class	.= " hidden";
			}
			return "<div name='$element->name' class='$class'>";
		}elseif($element->type == 'div_end'){
			return "</div>";
		}

		if(isset($this->formElements[$key-1])){
			$this->prevElement		= $this->formElements[$key-1];
		}else{
			$this->prevElement		= '';
		}

		if(isset($this->formElements[$key+1])){
			$this->nextElement		= $this->formElements[$key+1];
		}else{
			$this->nextElement		= '';
		}

		if(
			!empty($this->nextElement->multiple) 	&& 	// if next element is a multiple
			$this->nextElement->type != 'file' 		&& 	// next element is not a file
			$this->nextElement->type != 'text' 		&& 	// next element is not a text
			$element->type == 'label'				&& 	// and this is a label
			!empty($element->wrap)						// and the label is wrapped around the next element
		){
			return;
		}

		//store the prev rendered element before updating the current element
		$prevRenderedElement	= $this->currentElement;
		$this->currentElement	= $element;
				
		//Set the element width to 85 percent so that the info icon floats next to it
		if($key != 0 && $prevRenderedElement->type == 'info'){
			$width = 85;
		//We are dealing with a label which is wrapped around the next element
		}elseif($element->type == 'label' && !isset($element->wrap) && is_numeric($this->nextElement->width)){
			$width = $this->nextElement->width;
		}elseif(is_numeric($element->width)){
			$width = $element->width;
		}else{
			$width = 100;
		}

		//Load default values for this element
		$elementHtml 	= $this->getElementHtml($element);

		if(is_wp_error($elementHtml)){
			return $elementHtml;
		}

		$html			= '';

		//write a formstep div
		if($element->type == 'formstep'){
			$html	= '';
			//if there is already a formstep written close that one first
			if($this->isFormStep){
				$html .= "</div>";
			//first part of the form, don't hide
			}else{
				$this->isFormStep	= true;
				$html .= SIM\LOADERIMAGE;
			}
			
			$this->formStepCounter	+= 1;
			$html .= $elementHtml;

			return $html;
		}

		if($element->type == 'multi_start'){
			$this->multiwrap				= true;
			
			//we are wrapping so we need to find the max amount of filled in fields
			$i								= $key + 1;
			$this->multiWrapValueCount		= 1;
			$this->multiWrapElementCount	= 0;
			$this->multiInputsHtml 			= [];

			//loop over all consequent wrapped elements
			while(true){
				$type	= $this->formElements[$i]->type;
				if($type != 'multi_end' && !empty($this->formElements[$i])){
					$this->multiWrapElementCount++;

					if(!in_array($type, $this->nonInputs)){
						//Get the field values and count
						$valueCount		= count($this->getElementValues($this->formElements[$i]));
						
						if($valueCount > $this->multiWrapValueCount){
							$this->multiWrapValueCount = $valueCount;
						}
					}
					$i++;
				}else{
					break;
				}
			}

			return $html;
		}
		
		if($this->multiwrap && $element->type != 'multi_start' && $element->type != 'multi_end'){
			$this->processMultiFields($element, $width);

			return $html;
		}

		if($element->type == 'multi_end'){
			$this->multiwrap	= false;

			//write down all the multi html
			$name	= str_replace('end', 'start', $element->name);
			$elementHtml	= "<div class='clone-divs-wrapper' name='$name'>";
				// Tablink buttons
				if($this->multiWrapElementCount >= $this->minElForTabs){
					for ($index = 1; $index <= $this->multiWrapValueCount; $index++) {
						$active = '';

						if($index === 1){
							$active = 'active';
						}

						$elementHtml	.= "<button class='button tablink $active' type='button' id='show_{$element->name}-$index' data-target='{$this->tabId}-$index' style='margin-right:4px;'>
							{$element->nicename} $index
						</button>";
					}
				}

				foreach($this->multiInputsHtml as $multiHtml){
					$elementHtml .= $multiHtml;
				}
				
				if($this->wrap){
					if($this->wrap	== 'label'){
						$elementHtml .= '</label>';
					}
					$this->wrap	= false;
				}
			$elementHtml	.= '</div>';
		}
		
		// wrap an element and a folowing field in the same wrapper
		// so only write a div if the wrap property is not set
		if(!$this->wrap){
			$class	= 'input-wrapper';

			//Check if element needs to be hidden
			if(!empty($element->hidden)){
				$class .= ' hidden';
			}
			
			//if the current element is required or this is a label and the next element is required
			if(
				!empty($element->required)		||
				!empty($element->mandatory)		||
				$element->type == 'label'		&&
				(
					$this->nextElement->required	||
					$this->nextElement->mandatory
				)
			){
				$class .= ' required';
			}

			if($element->type == 'info'){
				$html = "<div class='$class info'>";
			}else{
				if(!empty($element->wrap)){
					$class	.= ' flex';
				}
				$html = "<div class='$class' style='width:$width%;'>";
			}
		}
		
		$html .= $elementHtml;

		//do not close the div if wrap is turned on
		if(
			!$this->wrap									&&
			$element->wrap									&&				//we should wrap around next element
			is_object($this->nextElement)					&& 				// and there is a next element
			!in_array($this->nextElement->type, ['php','formstep'])			// and the next element type is not a select or php element
		){
			//end a label if the next element is a select
			if($element->type == 'label' && in_array($this->nextElement->type, ['php','select'])){
				$html .= "</label></div>";
			}else{
				$this->wrap = $element->type;
			}

			return $html;
		}
		
		//we have not started a wrap
		//only close wrap if the current element is not wrapped or it is the last
		if(!$element->wrap || !is_object($this->nextElement)){
			//close the label element after the field element or if this the last element of the form and a label
			if(
				$this->wrap == 'label' ||
				($element->type == 'label' && !is_object($this->nextElement))
			){
 				$html .= "</label>";
			}
			$this->wrap = false;
			
			$html .= "</div>";
		}

		return $html;
	}
	
	/**
	 * Show the form
	 */
	public function showForm(){
		//Load conditional js if available and needed
		if($_SERVER['HTTP_HOST'] == 'localhost'){
			$jsPath		= $this->jsFileName.'.js';
		}else{
			$jsPath		= $this->jsFileName.'.min.js';
		}

		if(!file_exists($jsPath)){
			//SIM\printArray("$jsPath does not exist!\nBuilding it now");
			
			$path	= plugin_dir_path(__DIR__)."../js/dynamic";
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			
			//build initial js if it does not exist
			$this->createJs();
		}

		wp_enqueue_script('sim_forms_script');
		//Only enqueue if there is content in the file
		if(file_exists($jsPath) && filesize($jsPath) > 0){
			wp_enqueue_script( "dynamic_{$this->formName}forms", SIM\pathToUrl($jsPath), array('sim_forms_script'), $this->formData->version, true);
		}

		$html		= apply_filters('sim-forms-before-showing-form', '', $this);

		$formName	= $this->formData->form_name;

		$buttonText	= 'Submit the form';
		if(!empty($this->formData->button_text)){
			$buttonText	= $this->formData->button_text;
		}

		$dataset	= "data-formid='{$this->formData->id}'";
		if(!empty($this->formData->form_reset)){
			$dataset .= " data-reset='true'";
		}
		if(!empty($this->formData->save_in_meta)){
			$dataset .= " data-addempty='true'";
		}

		$html	.= '<div class="sim-form-wrapper">';
			// Formbuilder button
			if($this->editRights){
				$html	.= "<button type='button' class='button small formbuilder-switch'>Switch to formbuilder</button>";
			}
		
			$html	.= "<h3>$formName</h3>";

			if(array_intersect($this->userRoles, $this->submitRoles) && !empty($this->formData->save_in_meta)){
				$html	.= SIM\userSelect("Select an user to show the data of:");
			}
			$html	.=  apply_filters('sim_before_form', '', $this->formName);

			$html	.= "<form action='' method='post' class='sim-form-wrapper' $dataset>";
				$html	.= "<div class='form-elements'>";
					$html	.= "<input type='hidden' name='formid' value='{$this->formData->id}'>";
					$html	.= "<input type='hidden' name='formurl' value='".SIM\currentUrl(true)."'>";
					$html	.= "<input type='hidden' name='userid' value='$this->userId'>";
					foreach($this->formElements as $key=>$element){
						$html	.= $this->buildHtml($element, $key);
					}
				
					//close the last formstep if needed
					if($this->isFormStep){
						$html	.= "</div>";
						$html	.= "<div class='multistepcontrols hidden'>";
							$html	.= "<div class='multi-step-controls-wrapper'>";
								$html	.= "<div style='flex:1;'>";
									$html	.= "<button type='button' class='button' name='prevBtn'>Previous</button>";
								$html	.= "</div>";
								
								//Circles which indicates the steps of the form:
								$html	.= "<div style='flex:1;text-align:center;margin:auto;'>";
									for ($x = 1; $x <= $this->formStepCounter; $x++) {
										$html	.= "<span class='step'></span>";
									}
								$html	.= "</div>";
							
								$html	.= "<div style='flex:1;'>";
									$html	.= "<button type='button' class='button nextBtn' name='nextBtn'>Next</button>";
									$html	.= SIM\addSaveButton('submit_form', $buttonText, 'hidden');
								$html	.= "</div>";
							$html	.= "</div>";
						$html	.= "</div>";
					}

					if(!$this->isFormStep && !empty($this->formElements)){
						$html	.= SIM\addSaveButton('submit_form', $buttonText);
					}
				$html	.= "</div>";
			$html	.= "</form>";
		$html	.= "</div>";

		return $html;
	}
}
