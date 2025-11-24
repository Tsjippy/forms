<?php
namespace SIM\FORMS;
use SIM;

class DisplayForm extends ElementHtmlBuilder{
	use CreateJs;

	public function __construct($atts=[]){
		parent::__construct();

		$this->isFormStep				= false;
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
		}elseif(in_array($element->type, ['div-end'])){
			return;
		}
		
		/**
		 * Wrap elements that are not wrapped in anoter element
		 * in a div container, except for formsteps
		 */
		if(
			!$this->isClonableFormStep() && 	// this is a clonable formstep and a multi-start element
			!$this->prevElement->wrap &&		// this element is not wrapped in a previous element
			$element->type != 'formstep'		// this is not a formstep
		){
			//Set the element width to 85 percent so that the info icon floats next to it
			if($elementIndex != 0 && $this->prevElement->type == 'info'){
				$width = 85;
			//We are dealing with a label which is wrapped around the next element
			}elseif($element->type == 'label' && !isset($element->wrap) && is_numeric($this->nextElement->width)){
				$width = $this->nextElement->width;
			}elseif(is_numeric($element->width)){
				$width = $element->width;
			}else{
				$width = 100;
			}

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
				$style = "width:$width%;";
			}
			
			$parent = $this->addElement('div', $parent, ['class' => $class, 'style' => $style]);
		}

		// Only add element if this is not a clonable formstep
		$node = '';
		if(
			!$this->clonableFormStep ||			// this is not a clonable formstep
			$element->type == 'multi-start'		// or it is but this is the multi-start element
		){
			$node 	= $this->getElementHtml($element, $parent);

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

		return $node;
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
		if($_SERVER['HTTP_HOST'] == 'localhost' || str_contains($_SERVER['HTTP_HOST'], '.local')){
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

		/**
		 * Form container
		 */
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

		/**
		 * Hidden input for form id
		 */
		$attributes = [
			'type'		=> 'hidden',
			'class'		=> 'no-reset',
			'name'		=> 'form-id',
			'value'		=> $this->formData->id
		];
		$this->addElement('input', $form, $attributes);

		/**
		 * Hidden input for form url
		 */
		$attributes = [
			'type'		=> 'hidden',
			'class'		=> 'no-reset',
			'name'		=> 'formurl',
			'value'		=> SIM\currentUrl(true)
		];
		$this->addElement('input', $form, $attributes);

		/**
		 * Loop over all form elements and add the nodes
		 */
		$parents 			= ['root' => $form];
		$this->prevElement	= '';
		foreach($this->formElements as $index => $element){
			/**
			 * Store the current and the next elements
			 */
			if(isset($this->formElements[$index + 1])){
				$this->nextElement		= $this->formElements[$index + 1];
			}else{
				$this->nextElement		= '';
			}

			$this->currentElement	= $element;

			// Reset the parents if this is a formstep
			if ($element->type == 'formstep'){
				$parents = ['root' => $form];
			}

			// Insert the main node
			$node = $this->buildHtml($element, end($parents));
			
			/**
			 * Check if we should change the parent node
			 */
			if(
				!empty($node) &&															// the node is set
				(
					(
						$element->wrap &&													// this is the first wraping element
						!$this->formElements[$index - 1]->wrap
					) || 
					in_array($element->type, ['formstep', 'div-start', 'multi-start']) ||	// this is a wrapping element type
					$this->clonableFormStep													// this is a clonable forstep multi-start
				)
			){
				// Make the first child-div the parent of the concuring elements
				if($element->type == 'multi-start'){
					$parents[$element->type] = $this->multiwrapperFirstClone;
				}else{
					$parents[$element->type] = $node;
				}
			}
			// we finished wrapping remove last parent
			elseif(
				(
					!$element->wrap && 
					$this->formElements[$index - 1]->wrap
				) ||
				in_array($element->type, ['div-end', 'multi-end'])
			){
				array_pop($parents);
			}

			/**
			 * Store the current element as the previous element before next iteration
			 */
			$this->prevElement		= $element;
		}

		/**
		 * Form end
		 */
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

	/**
	 * Finds all elements that should be splitted in case of a BASENAME[index]SUBNAME name
	 */
	public function findSplitElementIds(){
		$baseNames	= [];
		// Check if this is an splitted element
		if(empty($this->formData->split)){
			return [];
		}

		$this->formData->split	= maybe_unserialize($this->formData->split);
		
		// loop over all element ids that data should be splitted on
		foreach($this->formData->split as $splitElementId){

			// Get the element name
			$name	= $this->getElementById($splitElementId, 'name');

			// Find the base name keyword followed by one or more numbers between [] followed by a keyword between []
			$pattern	= "/(.*?)\[[0-9]+\]\[([^\]]+)\]/i";

			// This is name matches the pattern
			if( preg_match($pattern, $name, $matches)){
				$baseNames[]	= $matches[1];
			}
		}

		if(empty($baseNames)){
			return [];
		}

		$elementIds	= [];
		//loop over all elements to find splitted ones
		foreach ($this->formElements as $element){
			// Check if this is an indexed splitted element basename[index][keyname]
			if(str_contains($element->name, '[')){
				// loop over all base names that data should be splitted on
				foreach($baseNames as $baseName){
					// Check if this name belongs to this splitted element
					$pattern		= "/$baseName\[[0-9]+\]\[([^\]]+)\]/i";

					if( preg_match($pattern, $element->name, $matches)){
						$name			= $matches[1];
						
						// store found element ids by basename
						if(empty($elementIds[$baseName])){
							$elementIds[$baseName]	= [];
						}

						if(empty($elementIds[$baseName][$name])){
							$elementIds[$baseName][$name]	= [];
						}

						// Add the current element id
						$elementIds[$baseName][$name][$element->name]	= $element->id;
						break;
					}
				}
			}
		}

		return $elementIds;
	}
}
