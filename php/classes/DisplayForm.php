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
		
		return $this->clonableFormStep;
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
		
		// element wrapper
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
				$class .= ' info';
				$style = '';
			}else{
				if(!empty($element->wrap)){
					$class	.= ' flex';
				}
				$style = "width:$width";
			}
			
			$parent = $this->addElement('div', $parent, ['class', $class, 'style' => $style]);
		}
		
		// do not write any html if this is a clonable formstep
		$node = '';
		if(!$this->isClonableFormStep()){
			//Load default values for this element
			$node 	= $this->elementHtmlBuilder->getElementHtml($element, $parent);
	
			if(is_wp_error($node)){
				return $node;
			}
		}

		//write a formstep div
		if($element->type == 'formstep'){
			// First step of the form
			if(!$this->isFormStep && !empty($node)){
				$this->addElement('div', $node, ['class' => "loader-image-trigger"]);
			}

			$this->isFormStep		= true;
			
			$this->formStepCounter	+= 1;
		}

		if($element->type == 'multi-start'){
			$this->multiwrap				= true;
		}

		if($element->type == 'multi-end'){
			$this->multiwrap	= false;
		}
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
	
	public function formStepControls($parent){
		// formstep buttons
		if($this->isFormStep){
			$formstepButtonWrapper 	= $this->addElement("div", $parent, ['class' => 'multi-step-controls hidden']);
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
 
		return $nextWrapper;
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
			
			// we should wrap the next element in this one
			if($element->wrap){
				$parent = $node;
			}elseif(empty($formstep)){
				$parent = $form;
			}else{
				$parent = $formstep;
			}
		}

		if(!empty($this->formElements)){
			$hidden = '';
			$parent = $form;
			
			$buttonText	= 'Submit the form';
			if(!empty($this->formData->button_text)){
				$buttonText	= $this->formData->button_text;
			}
			
			if($this->isFormStep){
				$hidden = 'hidden';
				$parent = $this->formStepControls($parent);
			}
			 
			$this->addRawHtml(SIM\addSaveButton('submit-form', $buttonText, $hidden), $parent);
		}

		$html =  $this->dom->saveHTML();

		return $html;
	}
}
