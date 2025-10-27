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
	public $elementHtmlBuilder;
	public $nonWrappable;

	public function __construct($atts=[]){
		parent::__construct();

		$this->isFormStep				= false;
		$this->wrap						= false;
		$this->nonWrappable				= [
			'select',
			'file',
			'image',
			'php'
		];
		$this->isMultiStepForm			= '';
		$this->formStepCounter			= 0;
		$this->minElForTabs				= 6;
		if(!empty($atts)){
			$this->processAtts($atts);
			$this->getForm();
			$this->getAllFormElements();

			$this->getUserId($atts);
		}

		$this->elementHtmlBuilder		= new ElementHtmlBuilder($this);
	}

	/**
	 * Check if we are editing on behalf of someone else, and we have permission for that
	 *
	 */
	protected function getUserId($atts=[]){
		if(
			array_intersect($this->userRoles, $this->submitRoles) 	&&	// we have the permission to submit on behalf on someone else
			!empty(($_GET['user-id']))								&&
			is_numeric($_GET['user-id'])								&& // and the user-id parameter is set in the url
			empty($atts['user-id'])										// and the user id is not given in the shortcode
		){
			$this->userId	= $_GET['user-id'];
		}
	}

	/**
	 * Renders the add and remove buttons for a multi-answer group
	 */
	protected function renderButtons(){
		ob_start();

		$addText	= '+';
		if(!empty($this->prevElement) && !empty($this->prevElement->add)){
			$addText	= $this->prevElement->add;
		}

		$removeText	= '-';
		if(!empty($this->prevElement) && !empty($this->prevElement->remove)){
			$removeText	= $this->prevElement->remove;
		}
		
		?>
		<div class='button-wrapper' style='margin: auto;'>
			<button type='button' class='add button' style='flex: 1;max-width: max-content;'><?php echo $addText;?></button>
			<button type='button' class='remove button' style='flex: 1;max-width: max-content;'><?php echo $removeText;?></button>
		</div><?php

		return ob_get_clean();
	}

	/**
	 * Renders the start of a multi wrap group
	 *
	 * @param	int		$index		The index of the copies
	 */
	protected function multiWrapStart($index, $element){
		$class	= '';

		// this clone-div should also be a formstep
		if($this->clonableFormStep){
			$class .= ' formstep';	

			// make sure we increase the formstep counter
			$this->formStepCounter	+= 1;
		}

		// Wrap in a tab if it is a big one but not if it is a clonable formstep
		elseif($this->multiWrapElementCount >= $this->minElForTabs){
			$hidden	= 'hidden';

			if($index === 0){
				$hidden = '';
			}

			$this->tabId	= str_replace(' ', '-', $element->nicename);
			$id		= $index + 1;
			$class	= "tabcontent $hidden' id='$this->tabId-$id";
		}

		

		$style	= '';
		if($this->multiWrapElementCount < $this->minElForTabs){
			$style	= 'style="display:flex;"';
		}

		$this->multiInputsHtml[$index]   = "<div class='clone-div $class' data-div-id='$index' $style>";

		// Get the the element of two elements before this one, which should be the multi-start element
		// and use the text as a title for this group
		$this->multiInputsHtml[$index]   .= "<h3>{$this->formElements[$this->currentElement->priority - 3]->text}</h3>";
		
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
		$class	= '';

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

		$elementHtml = $this->elementHtmlBuilder->getElementHtml($element);
		
		// close the wrapping element after the last wrapped element
		if($this->wrap && !$element->wrap){
			$elementHtml .= "</$this->wrap>";
			$this->wrap = false;
		}elseif(!$this->wrap){
			if($element->type == 'info'){
				$elementHtml .= "<div class='$class info'>";
			}else{
				if(!empty($element->wrap)){
					$class	.= ' flex';
				}

				// input wrapper
				$elementHtml = "<div class='input-wrapper$class' style='width:$width%;'>$elementHtml";
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

		// Close the input wrapper div if we are not wrapping
		if(!$this->wrap){
			$elementHtml .= "</div>";
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

		$this->elementHtmlBuilder->html	= $elementHtml;

		// Create as many clones as the maximum value of one of the elements 
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}

			// prepare the base html for duplicating
			$newElementHtml	= $this->elementHtmlBuilder->prepareElementHtml($index, $val);

			// First element in a multi answer wrapper
			if($this->prevElement->type == 'multi-start'){
				$this->multiWrapStart($index, $element);
			}
			
			// elements between start and end
			$this->multiInputsHtml[$index] .= $newElementHtml;
			
			// Last element in the multi wrap, write the buttons and closing div
			if($this->nextElement->type == 'multi-end'){
				$this->multiWrapEnd($index, $element);
			}
		}
	}

	/**
	 * Checks if this is a clonable formstep, meaning a multi_start - multi-end group wrapped inside a formstep
	 */
	protected function isClonableFormStep(){
		$this->clonableFormStep	= false;

		if(
			$this->nextElement->type == 'multi-start' && 
			$this->currentElement->type == 'formstep'
		){
			// loop until we find the multi-end
			$x	= $this->currentElement->priority; // this is the index of the next element, which is the multi-start
			while(true){
				$x++;
				// This is the multi end
				if($this->formElements[$x]->type == 'multi-end'){
					// only if the next element is a formstep we have a clonable formstep
					if(
						empty($this->formElements[$x + 1]) ||				// this is the last element of the form
						$this->formElements[$x + 1]->type == 'formstep'		// the next element is a formstep
					){
						$this->clonableFormStep	= true;
					}
					break;
				}
			}
		}
	}

	/**
	 * Build all html for a particular element including edit controls.
	 *
	 * @param	object	$element		The element
	 *
	 * @return	string					The html
	 */
	public function buildHtml($element){

		$elementIndex	= $element->priority - 1;

		if($element->type == 'div-start'){
			$class		= 'input-wrapper';
			if($element->hidden){
				$class	.= " hidden";
			}
			return "<div name='$element->name' class='$class'>";
		}elseif($element->type == 'div-end'){
			return "</div>";
		}

		if(isset($this->formElements[$elementIndex -1])){
			$this->prevElement		= $this->formElements[$elementIndex - 1];
		}else{
			$this->prevElement		= '';
		}

		if(isset($this->formElements[$elementIndex + 1])){
			$this->nextElement		= $this->formElements[$elementIndex + 1];
		}else{
			$this->nextElement		= '';
		}

		if(
			!empty($this->nextElement->multiple) 							&& 	// if next element is a multiple
			!in_array($this->nextElement->type, $this->nonWrappable) 		&& 	// next element is wrappable
			$this->nextElement->type != 'text' 								&& 	// next element is not a text
			$element->type == 'label'										&& 	// and this is a label
			!empty($element->wrap)												// and the label is wrapped around the next element
		){
			return;
		}

		//store the prev rendered element before updating the current element
		$prevRenderedElement	= $this->currentElement;
		$this->currentElement	= $element;
				
		//Set the element width to 85 percent so that the info icon floats next to it
		if($elementIndex != 0 && $prevRenderedElement->type == 'info'){
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
		$elementHtml 	= $this->elementHtmlBuilder->getElementHtml($element);

		if(is_wp_error($elementHtml)){
			return $elementHtml;
		}

		$html			= '';

		//write a formstep div
		if($element->type == 'formstep'){
			$html	= '';

			//if there is already a formstep written close that one first
			if($this->isFormStep){
				// but not if this was a clonable formstep
				// as that one is already closed
				if(!$this->clonableFormStep){
					$html .= "</div>";
				}
			}else{
				$this->isFormStep		= true;
			}

			// do not write any html if this is a clonable formstep
			$this->isClonableFormStep();
			if($this->clonableFormStep){
				return $html;
			}

			// First step of the form
			if(!$this->isFormStep){
				$html .= SIM\LOADERIMAGE;
			}
			
			$this->formStepCounter	+= 1;
			$html .= $elementHtml;

			return $html;
		}

		if($element->type == 'multi-start'){
			$this->multiwrap				= true;
			
			// We are wrapping so we need to find the max amount of filled in fields
			$i								= $elementIndex + 1;
			$this->multiWrapValueCount		= 1;
			$this->multiWrapElementCount	= 0;
			$this->multiInputsHtml 			= [];

			//loop over all consequent wrapped elements
			while(true){
				$type	= $this->formElements[$i]->type;

				// Loop till we reach a multi-end element or the end of the form
				if($type == 'multi-end' || empty($this->formElements[$i])){
					break;
				}

				$this->multiWrapElementCount++;

				if(!in_array($type, $this->nonInputs)){
					//Get the field values and count
					$values			= $this->getElementValues($this->formElements[$i]);

					if(empty($values) || !is_array(array_values($values)[0])){
						$valueCount	= 0;
					}else{
						$valueCount		= count(array_values($values)[0]);

						// Do not count the valuelist values
						if(!empty($this->formElements[$i]->valuelist)){
							$elementValues	= explode("\n", $this->formElements[$i]->valuelist);
							$valueCount	= $valueCount - count($elementValues);
						}
					}
					
					if($valueCount > $this->multiWrapValueCount){
						$this->multiWrapValueCount = $valueCount;
					}
				}
				$i++;
			}

			return $html;
		}
		
		if($this->multiwrap && $element->type != 'multi-start' && $element->type != 'multi-end'){
			$this->processMultiFields($element, $width);

			return $html;
		}

		if($element->type == 'multi-end'){
			$this->multiwrap	= false;

			//write down all the multi html
			$name	= str_replace('_multi-end', '_multi-start', $element->name);

			$elementHtml	= "<div class='clone-divs-wrapper' name='$name'>";
			if(!$this->clonableFormStep){
				$elementHtml .= $this->renderButtons();

				// Tablink buttons
				if($this->multiWrapElementCount >= $this->minElForTabs ){
					for ($index = 1; $index <= $this->multiWrapValueCount; $index++) {
						$active = '';

						if($index === 1){
							$active = 'active';
						}

						$elementHtml	.= "<button class='button tablink $active' type='button' id='show-{$element->name}-$index' data-target='{$this->tabId}-$index' style='margin-right:4px;'>
							{$element->nicename} $index
						</button>";
					}
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
			if($element->type == 'label' && in_array($this->nextElement->type, $this->nonWrappable)){
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

		$dataset	= "data-form-id='{$this->formData->id}'";

		// Reset a form when not saving to meta
		if(empty($this->formData->save_in_meta)){
			$dataset .= " data-reset=1";
		}else{
			// make sure empty checkboxes show up in form results
			$dataset .= " data-add-empty=1";
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
					$html	.= "<input type='hidden' class='no-reset' name='form-id' value='{$this->formData->id}'>";
					$html	.= "<input type='hidden' class='no-reset' name='formurl' value='".SIM\currentUrl(true)."'>";
					$html	.= "<input type='hidden' class='no-reset' name='user-id' value='$this->userId'>";
					foreach($this->formElements as $element){
						$html	.= $this->buildHtml($element);
					}
				
					//close the last formstep if needed
					if($this->isFormStep){
						$html	.= "</div>";
						$html	.= "<div class='multi-step-controls hidden'>";
							$html	.= "<div class='multi-step-controls-wrapper'>";
								$html	.= "<div style='flex:1;'>";
									$html	.= "<button type='button' class='button' name='previous-button'>Previous</button>";
								$html	.= "</div>";
								
								//Circles which indicates the steps of the form:
								$html	.= "<div class='step-wrapper' style='flex:1;text-align:center;margin:auto;'>";
									for ($x = 1; $x <= $this->formStepCounter; $x++) {
										$html	.= "<span class='step'></span>";
									}
								$html	.= "</div>";
							
								$html	.= "<div style='flex:1;'>";
									$html	.= "<button type='button' class='button next-button' name='next-button'>Next</button>";
									$html	.= SIM\addSaveButton('submit-form', $buttonText, 'hidden');
								$html	.= "</div>";
							$html	.= "</div>";
						$html	.= "</div>";
					}

					if(!$this->isFormStep && !empty($this->formElements)){
						$html	.= SIM\addSaveButton('submit-form', $buttonText);
					}
				$html	.= "</div>";
			$html	.= "</form>";
		$html	.= "</div>";

		return force_balance_tags($html);
	}
}
