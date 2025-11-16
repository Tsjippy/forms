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
	public $dom;
	public $formWrapper;

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
		
		$this->dom 						= new \DOMDocument();

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
			is_numeric($_GET['user-id'])							&& 	// and the user-id parameter is set in the url
			empty($atts['user-id'])										// and the user id is not given in the shortcode
		){
			$this->userId	= $_GET['user-id'];
		}
	}
	
	/**
	 * Renders the html for element who can have multiple inputs
	 *
	 * @param	object	$element		The element
	 * @param	int		$width			The width of the elements
	 */
	protected function processMultiFields($element, $parent, $width){
		$node = $this->elementHtmlBuilder->getElementHtml($element, $parent);
		
		// close the wrapping element after the last wrapped element
		if($this->wrap && !$element->wrap){
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

		if(in_array($element->type, $this->nonInputs)){
			//Get the field values of the next element as this does not have any
			$values		= $this->getElementValues($this->nextElement);
		}else{
			$values		= $this->getElementValues($element);
		}

		// Get the values we need
		if(empty($this->formData->save_in_meta)){
			if(!empty($values['defaults'])){
				$values		= array_values($values['defaults']);
			}
		}else{
			if(!empty($values['metavalue'])){
				$values		= array_values($values['metavalue']);
			}
		}

		// Create as many clones as the maximum value of one of the elements 
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}

			// prepare the base html for duplicating
			$newElementHtml	= $this->elementHtmlBuilder->prepareElementHtml($index, $val, $parent);

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
	public function buildHtml($element, $parent){

		$elementIndex	= $element->priority - 1;

		if($element->type == 'div-start'){
			$class		= 'input-wrapper';
			if($element->hidden){
				$class	.= " hidden";
			}
			return $this->addElement(
				'div', 
				$parent, 
				[
					'name'	=> $element->name,
					'class'	=> $class
				]
			);
		}elseif($element->type == 'div-end'){
			return;
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
		$elementHtml 	= $this->elementHtmlBuilder->getElementHtml($element, $parent);

		if(is_wp_error($elementHtml)){
			return $elementHtml;
		}

		$html			= '';

		//write a formstep div
		if($element->type == 'formstep'){
			$html	= '';

			// First step of the form
			if(!$this->isFormStep){
				$this->addElement('div', $parent, ['class' => "loader-image-trigger"]);
			}

			$this->isFormStep		= true;

			// do not write any html if this is a clonable formstep
			$this->isClonableFormStep();
			if($this->clonableFormStep){
				return $html;
			}
			
			$this->formStepCounter	+= 1;
			$html .= $elementHtml;

			return $html;
		}

		if($element->type == 'multi-start'){
			$this->multiwrap				= true;

			return;
		}
		
		if($this->multiwrap && $element->type != 'multi-start' && $element->type != 'multi-end'){
			$this->processMultiFields($element, $parent, $width);

			return $html;
		}

		if($element->type == 'multi-end'){
			$this->multiwrap	= false;
			
				$this->wrap	= false;
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
	 * Adds an element and its attributes to a parent element
	 * 
	 * @param	string	$type			The element tagname
	 * @param	object	$parent			The parent node
	 * @param	array	$attributes		An array of attribute names and values
	 * @param	string	$textContent	The text content of the element
	 * 
	 * @return	object					The created node
	 */
	public function addElement($type, $parent, $attributes=[], $textContent=''){
		$element = $this->dom->createElement($type, $textContent );

		foreach($attributes as $attribute => $value){
			$element->setAttribute($attribute, $value);
		}
		
		$parent->appendChild($element);

		return $element;
	}
	
	/**
	 * Creates nodes from raw html and adds it to the parent
	 * 
	 * @param	string	$html		The html
	 * @param	object	$parent		The parent Node
	 * 
	 * @return	object				The created node
	 */
	public function addRawHtml($html, &$parent){
		if(!empty($html)){
			$fragment = $this->dom->createDocumentFragment();
			$fragment->appendXML($html);
			$parent->appendChild($fragment);

			return $fragment;
		}
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
			
			$path	= MODULE_PATH."/js/dynamic";
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

		$initialHtml	= apply_filters('sim-forms-before-showing-form', '', $this);
		if(!empty($initialHtml)){
			$this->dom->loadHTML( $initialHtml);
		}

		$this->formWrapper = $this->addElement('div', $this->dom, ['class' => 'sim-form-wrapper']);
			 
		// Formbuilder button
		if($this->editRights){
			$attributes = [
				'type' 	=> 'button',
				'class' => 'button small formbuilder-switch'
			];
			$this->addElement('button', $this->formWrapper, $attributes, 'Switch to formbuilder');
		}
	
		$formName	= $this->formData->form_name;
		if(!empty($formName)){
			$this->addElement("h3", $this->formWrapper, [], $formName);
		}

		if(array_intersect($this->userRoles, $this->submitRoles) && !empty($this->formData->save_in_meta)){
			$this->addRawHtml(SIM\userSelect("Select an user to show the data of:"), $this->formWrapper);
		}
		$this->addRawHtml(apply_filters('sim_before_form', '', $this->formName), $this->formWrapper);

		$attributes = [
			'method'		=> 'post',
			'class'			=> 'sim-form-wrapper',
			'data-form-id'	=> $this->formData->id
		];

		// Reset a form when not saving to meta
		if(empty($this->formData->save_in_meta)){
			$attributes["data-reset"]		= 1;
		}else{
			// make sure empty checkboxes show up in form results
			$attributes["data-add-empty"]	= 1;
		}
		$form = $this->addElement("form", $this->formWrapper, $attributes);
		
		$this->addElement('div', $form, ['class'=>'form-elements']);

		$attributes = [
			'type'		=> 'hidden',
			'class'		=> 'no-reset',
			'name'		=> 'form-id',
			'value'		=> $this->formData->id
		];
		$this->addElement('input', $form, $attributes);

		$attributes = [
			'type'		=> 'hidden',
			'class'		=> 'no-reset',
			'name'		=> 'formurl',
			'value'		=> SIM\currentUrl(true)
		];
		$this->addElement('input', $form, $attributes);

		$parent = $form;
		$formstep = '';
		foreach($this->formElements as $element){
			$node = $this->buildHtml($element, $parent);
			
			// we should wrap the next element in thus one
			if($element->wrap){
				$parent = $node;
			}elseif(!empty($formstep)){
				$parent = $formstep;
			}else{
				$parent = $form;
			}
		}
		
		$buttonText	= 'Submit the form';
		if(!empty($this->formData->button_text)){
			$buttonText	= $this->formData->button_text;
		}

		// Add formstep buttons
		if($this->isFormStep){
			$formstepButtonWrapper 	= $this->addElement("div", $form, ['class' => 'multi-step-controls hidden']);
			$wrapper 				= $this->addElement("div", $formstepButtonWrapper, ['class' => 'multi-step-controls-wrapper']);
			$prevWrapper 			= $this->addElement('div', $wrapper,	['style' => 'flex:1;']);
			
			/**
			 * Previous button
			 */
			$this->addElement(	
				"button", 
				$prevWrapper, 
				[
					'type' => 'button', 
					'class' =>'button',
					'name' => 'previous-button'
				],
				'Previous'
			);
			
			//Circles which indicates the steps of the form:
			$indicatorWrapper = $this->addElement('div', $wrapper,	['class' => 'step-wrapper', 'style' => 'flex:1;text-align:center;margin:auto;']);
			for ($x = 1; $x <= $this->formStepCounter; $x++) {
				$this->addElement('span', $indicatorWrapper, [ 'class' => 'step']);
			}
		
			/**
			 * Next button
			 */
			$nextWrapper = $this->addElement("div", $wrapper, ['style' => 'flex:1;']);
			$this->addElement(
				'button', 
				$nextWrapper,
				[
					'type'	=> 'button', 
					'class' => 'button next-button', 
					'name'	=> 'next-button'
				],
				'Next'
			);

			// Submit button
			$this->addRawHtml(SIM\addSaveButton('submit-form', $buttonText, 'hidden'), $nextWrapper);
		}

		if(!$this->isFormStep && !empty($this->formElements)){
			$this->addRawHtml(SIM\addSaveButton('submit-form', $buttonText), $form);
		}

		$html =  $this->dom->saveHTML();

		return $html;
	}
}
