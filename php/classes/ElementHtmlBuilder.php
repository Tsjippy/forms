<?php
namespace SIM\FORMS;
use SIM;
use WP_Error;

class ElementHtmlBuilder extends DisplayForm{
    use ElementHtml;

    public $defaultArrayValues;
    public $prevElement;
    public $multiWrapValueCount;
    public $wrap;
    public $defaultValues;
    public $element;
	private $parentInstance;
	private $requestedValue;
	private $elementValues;
	private $tagType;
	private $selectedValue;
	private $tagContent;
	private $tagCloseHtml;
	public $html;
	public $formData;
	public $formElements;
	public $usermeta;
	public $submissions;
	public $attributes;

    public function __construct($parentInstance){
		//parent::__construct();

		$this->parentInstance		= $parentInstance;

		$this->formData				= $parentInstance->formData;
		$this->formElements			= $parentInstance->formElements;

		$this->nonInputs			= $parentInstance->nonInputs;
		$this->user					= $parentInstance->user;
		$this->userId				= $parentInstance->userId;
        
		$this->reset();
    }

	public function reset(){
		$this->elementValues		= [];
		$this->tagType				= '';
		$this->selectedValue		= '';
		$this->attributes			= ['class' => ''];
	}

    /**
	 * Gets the elment attributes
	 */
	protected function getAttributes(){
		/*
			BUTTON TYPE
		*/
		if($this->element->type == 'button'){
			$this->attributes["type"]	='button';
		}

		if(empty($this->element->options)){
			return;
		}

		$removeMin	= false;

		// do not have min values in a form table to allow to edit values for the past
		if(get_class($this->parentInstance) == 'SIM\FORMS\DisplayFormResults'){
			$removeMin	= true;
		}

		//Store options in an array
		$options	= explode("\n", trim($this->element->options));
		
		//Loop over the options array
		foreach($options as $option){
			//Remove starting or ending spaces and make it lowercase
			$option 		= explode('=', trim($option));

			$optionType		= $option[0];
			$optionValue	= str_replace('\\\\', '\\', $option[1]);
			
			if($removeMin && in_array($optionType, ['min', 'max'])){
				continue;
			}

			if($optionType == 'class'){
				$this->attributes['class']	.= $optionValue;
			}else{
				//remove any leading "
				$optionValue	= trim($optionValue, '\'"');

				// Parse a date value
				if(
					$this->element->type == 'date' && 
					in_array($optionType, ['min', 'max', 'value']) && 
					strtotime($optionValue)
				){
					$optionValue	= Date('Y-m-d', strtotime($optionValue));
				}

				// Store in the attributes array
				$this->attributes[$optionType] = "$optionValue";
			}
		}
	}

	/**
	 * Renders the add and remove buttons for a multi-answer group
	 */
	protected function renderButtons($parent){
		ob_start();

		$addText	= '+';
		if(!empty($this->prevElement) && !empty($this->prevElement->add)){
			$addText	= $this->prevElement->add;
		}

		$removeText	= '-';
		if(!empty($this->prevElement) && !empty($this->prevElement->remove)){
			$removeText	= $this->prevElement->remove;
		}
		
		$wrapper	= $this->addElement(
			'div',
			$parent,
			[
				'class'	=> 'button-wrapper',
				'style'	=> 'margin: auto;'
			]
		);

		$this->addElement(
			'div',
			$wrapper,
			[
				'type'	=> 'button',
				'class'	=> 'add button',
				'style'	=> 'flex: 1;max-width: max-content;'
			],
			$addText
		);

		$this->addElement(
			'div',
			$wrapper,
			[
				'type'	=> 'button',
				'class'	=> 'remove button',
				'style'	=> 'flex: 1;max-width: max-content;'
			],
			$removeText
		);
	}

	/**
	 * Adds an index to the name and id and adds the value of the current index
	 * 
	 * @param	int				$index			The current iteration index of the element
	 * @param	string|array	$value			The value to add
	 * @param	object			$node			The node to edit
	 */
	function changeNodeAttributes($index, $value, &$node){
		if($value === null){
			$value = '';
		}

		// make sure we add the [] after the index if there was [] originally
		$node->attributes['name']	= str_replace('[]', '', $node->attributes['name'], $replaceCount);
		$indexString 				= "[$index]";
		if($replaceCount){
			$indexString			.= "[]";
		}

		// Add the index to the name
		$node->attributes['name']	= $node->attributes['name'].$indexString;

		// Add the index to the id
		$node->attributes['id']		= $node->attributes['id']."[$index]";
					
		/**
		 * Add Select options
		 */
		if($this->element->type == 'select'){
			// default empty first option
			$this->addElement("option", $node, ["value" => ''], "---");

			// Loop over the select options to see which option should be selected
			foreach($this->elementValues['defaults'] as $optionKey => $option){
				$attributes	= [
					"value" => $optionKey
				];

				if(strtolower($value) == strtolower($optionKey) || strtolower($value) == strtolower($option)){
					$attributes['selected']	= 'selected';
				}

				$this->addElement("option", $node, $attributes, $option);
			}
		}
		
		/**
		 * checkbox checked value
		 */
		elseif(in_array($this->element->type, ['radio', 'checkbox'])){
			$options	= explode("\n", trim($this->element->options));

			//make key and value lowercase
			array_walk_recursive($options, function(&$item, &$key){
				$item 	= strtolower($item);
				$key 	= strtolower($key);
			});

			foreach($options as $optionKey => $option){
				$found = false;

				if(is_array($value)){
					foreach($value as $v){
						if(strtolower($v) == $optionKey || strtolower($v) == $option){
							$found 	= true;
						}
					}
				}elseif(strtolower($value) == $optionKey || strtolower($value) == $option){
					$found 	= true;
				}
				
				// This is the selected radio or checkbox value
				if($found){
					$node->attributes['checked']	= 'checked';
				}
			}
		}

		/**
		 *  Element value
		 */ 
		elseif(is_array($value)){
			$node->attributes['value'] = $value[$index];
		}else{
			$node->attributes['value'] = $value;
		}

		// Add the index to the label if we are not displaying it on seperate tabs
		if(
			$this->element->type == 'label' && 
			$this->parentInstance->multiWrapElementCount < $this->parentInstance->minElForTabs
		){
			$nr					 = $index + 1;
			$node->nodeValue	.= " $nr";
		}
	}

	/**
	 * Renders the html for element who can have multiple inputs
	 */
	function multiInput($parent){		
		if(
			empty($this->parentInstance->formData->save_in_meta) && 
			!empty($this->elementValues['defaults'])
		){
			$values		= array_values($this->elementValues['defaults']);
		}elseif(!empty($this->elementValues['metavalue'])){
			$values		= array_values($this->elementValues['metavalue']);
		}

		//check how many elements we should render
		$this->multiWrapValueCount	= max(1, count((array)$values));

		//create as many inputs as the maximum value found
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}
			
			// Open the clone div
			$wrapper	= $this->addElement(
				'div', 
				$parent, 
				[
					'class'			=> 'clone-div',
					'data-div-id'	=> $index
				]
			);

			$parentNode		= $wrapper;
			//add wrapping element to each entry if prev element is wrapped with this one
			if(
				!empty($this->prevElement)	&&
				!empty($this->prevElement->wrap) && 
				$this->prevElement != $this->element
			){
				$wrappingNode = $this->getElementHtml($this->prevElement, $wrapper);
				$wrappingNode->attributes->nodeValue	.= $index + 1;

				$parentNode		= $wrappingNode;
			}

			//wrap input AND buttons in a flex div
			$flexWrapper	= $this->addElement(
				'div',
				$parentNode,
				[
					'class'	=> 'button-wrapper',
					'style' => 'width:100%; display: flex;' 
				]
			);
				
			// Add the element itself
			$node	= $this->addElement($this->tagType, $flexWrapper, $this->attributes, $this->tagContent);
			$this->changeNodeAttributes($index, $val, $node);				

			$this->renderButtons($flexWrapper);
		}
	}

	/**
	 * Get the previous values of a element
	 */
	function getPrevValues($returnArray=false){
		if(empty($this->submissions)){
			$this->submissions	= $this->parentInstance->submissions;
		}

		// Check if we should include previous submitted values
		$prevValues		= '';

		if($returnArray){
			$prevValues		= [];
		}
		
		// we are not doing this via an api request
		if(!str_contains($_SERVER['REDIRECT_URL'], 'get_input_html') || !empty($this->requestedValue)){
			return $prevValues;
		}

		$valueIndexes	= explode('[', str_replace('[]', '', $this->element->name));

		foreach($valueIndexes as $i => $index){
			// just one possible value found
			if($i == 0){
				// there is no value in the form results
				if(empty($this->submissions[0]->{$index})){

					// check the submission meta data
					if(empty($this->submissions[0]->$index)){
						break;
					}

					$prevValues	= $this->submissions[0]->$index;
				}
				
				// This is a splitted value, select all values
				elseif(count($this->submissions) > 1 && !empty($_POST['subid'])){
					$prevValues	= [];

					foreach($this->submissions as $submission){
						$prevValues[]	= $submission->{$index};
					}
				}else{
					$prevValues	= $this->submissions[0]->{$index};
				}
			}
			
			// return the sub value
			else{
				if($i == 1 && is_numeric($_POST['subid'])){
					$index	= $_POST['subid'];
				}

				$index	= trim($index, ']');

				if(!isset($prevValues[$index])){
					break;
				}

				$prevValues	= $prevValues[$index];
			}					
		}

		if(is_string($prevValues)){
			$result	= json_decode($prevValues);

			if (json_last_error() === JSON_ERROR_NONE) {
				$prevValues		= $result;
			}
		}

		return $prevValues;
	}

	/**
	 * Gets the html for a file or image element
	 */
	protected function uploaderHtml(){
		$name		= $this->element->name;
			
		// Element setting
		if(!empty($this->element->foldername)){
			if(str_contains($this->element->foldername, "private/")){
				$targetDir	= $this->element->foldername;
			}else{
				$targetDir	= "private/".$this->element->foldername;
			}
		}

		// Form setting
		if(empty($targetDir)){
			$targetDir = $this->parentInstance->formData->upload_path;
		}

		// Default setting
		if(empty($targetDir)){
			$targetDir = 'form_uploads/'.$this->parentInstance->formData->form_name;
		}

		if(empty($this->parentInstance->formData->save_in_meta)){
			$library	= false;
			$metakey	= '';
			$userId		= '';
		}else{
			$library	= $this->element->library;
			$metakey	= $name;
			$userId		= $this->userId;
		}
		//Load js
		$uploader 		= new SIM\FILEUPLOAD\FileUpload($userId, $metakey, $library, '', false, $this->usermeta[$metakey]);
		
		return $uploader->getUploadHtml($name, $targetDir, $this->element->multiple, $this->attributes, $this->element->editimage);
	}

	/**
	 * Determines the tag type of an element
	 */
	protected function getTagType(){
		if(in_array($this->element->type, ['formstep', 'info', 'div-start'])){
			$this->tagType	= "div";
		}elseif(in_array($this->element->type, array_merge($this->parentInstance->nonInputs, ['select', 'textarea']))){
			$this->tagType	= $this->element->type;
		}else{
            $this->attributes['type'] = $this->element->type;
            
            $this->tagType		= "input";
        }
	}

	/**
	 * Determines the element name
	 */
	protected function getNameAttribute(){
		$this->element->name	= trim($this->element->name, " \n\r\t\v\0_");

		// [] not yet added to name
		if(in_array($this->element->type, ['radio','checkbox']) && !str_contains($this->element->name, '[]')) {
			$this->element->name .= '[]';
		}

		$this->attributes["name"] = $this->element->name;
	}

	/**
	 * Get element Id
	 */
	protected function getElementId(){
		//datalist needs an id to work as well as mandatory elements for use in anchor links
		if($this->element->type == 'datalist' || $this->element->mandatory || $this->element->recommended){
			$this->attributes["id"] = $this->element->name;
		}

		if(str_contains($this->element->name, '[]')){
			$this->attributes["id"] = "E{$this->element->id}";
		}
	}

	/**
	 * Gets the class string for an element
	 */
	protected function getClasses(){
		$this->attributes['class']	.= "formfield";

		switch($this->element->type){
			case 'label':
				$this->attributes['class']	.= "form-label";
				break;
			case 'button':
				$this->attributes['class']	.= "button";
				break;
			case 'formstep':
				$this->attributes['class']	.= "formstep step-hidden";
				break;
			default:
				$this->attributes['class']	.= "formfield-input";
		}
	}

	/**
	 * Gets the element value
	 */
	protected function getValue(){
		if(in_array($this->element->type, $this->nonInputs)){
			return '';
		}

		// The requested value is a value of a previous submission, find previous submitted values if not provided to the function
		if(empty($this->requestedValue)){
			$this->requestedValue	= $this->getPrevValues();
		}

		// Do not continue
		if(
			$this->parentInstance->multiwrap || 
			!empty($this->element->multiple) ||
			(
				empty($this->elementValues) && empty($this->requestedValue)
			)
		){
			return;
		}

		$this->selectedValue	= $this->requestedValue;

		if(empty($this->requestedValue)){
			//this is an input and there is a value for it
			if(
				!empty($this->elementValues['defaults']) && 					// there is a default value
				(
					empty($this->parentInstance->formData->save_in_meta) || 	// we are not saving to the user meta table
					empty($this->elementValues['metavalue'])					// or the metavalue is empty
				)
			){
				$this->selectedValue		= array_values($this->elementValues['defaults'])[0];
			}elseif(!empty($this->elementValues['metavalue'])){
				$elIndex	= 0;
				if(str_contains($this->element->name, '[]')){
					// Check if there are multiple elements with the same name
					$elements	= $this->getElementByName($this->element->name, '', false);

					foreach($elements as $elIndex=>$el){
						if($el->id == $this->element->id){
							break;
						}
					}
				}

				$this->selectedValue		= array_values($this->elementValues['metavalue'])[$elIndex];
			}
		}

		if(
			!empty($this->selectedValue) && 
			!in_array($this->element->type, ['radio', 'checkbox'])
		){
			if(is_array($this->selectedValue)){
				$this->selectedValue	= array_values($this->selectedValue)[0];
			}

			$this->attributes["value"] = $this->selectedValue;
		}
	}

	/**
	 * Get the tag content of an element, i.e. the conten between the openening and closing tag
	 */
	protected function getTagContent($node){
		switch($this->element->type){
			case 'textarea':
				$value	= $this->requestedValue;
				if(empty($value)){
					$value	= $this->selectedValue;
				}

				if(!empty($value)){
					if(is_array($value)){
						$value	= array_values($value)[0];
					}

					$node->nodeValue = $value;
				}
				break;
			case 'formstep':
				$this->addElement("h3", $node, [], $this->element->text);
				break;
			case 'label':
				$this->addElement("h4", $node, ['class' => 'label-text'], $this->element->text);
				break;
			case 'button':
				$node->nodeValue = $this->element->text;
				break;
			case 'select':
				$this->addSelectOptions($node);
				break;
			case 'datalist':
				$this->addDatalistOptions($node);
				break;
			default:
				$this->addElement( "label", $node, ['class' => 'label-text'], $this->element->text);
        }
	}

	protected function getMultiTextInputHtml($parent){
		if(empty($this->requestedValue) && !empty($this->defaultArrayValues[$this->element->default_value])){
			$this->requestedValue	= $this->defaultArrayValues[$this->element->default_value];

			if(!is_array($this->requestedValue)){
				$this->requestedValue	= [$this->requestedValue];
			}
		}

		$elName	= $this->element->name;
		if(!str_contains($elName, '[]')){
			$elName	.= '[]';
		}

		$wrapper = $this->addElement("div", $parent, ['class' => 'option-wrapper']);
			
		/**
		 * The list of prefileld values
		 */

		// The unoredered list for choices made
		$selectionList	= $this->addElement("ul", $wrapper, ['class' => 'list-selection-list']);

		// Add all the list items
		foreach($this->requestedValue as $v){
			if(method_exists($this, 'transformInputData')){
				if(empty($this->submissions)){
					$this->submissions	= $this->parentInstance->submissions;
				}
				$transValue		= $this->transformInputData($v, $this->element->name, $this->submissions[0]);
			}else{
				$transValue		= $v;
			}

			$listItem	= $this->addElement('li', $selectionList, ['class' => 'list-selection']);

			$button		= $this->addElement(
				'button', 
				$listItem, 
				[
					'type'	=> 'button',
					'class'	=> 'small remove-list-selection'
				]
			);

			$this->addElement('span', $button, ['class' => 'remove-list-selection'], '×');

			$this->addElement(
				'input',
				$listItem,
				[
					'type'	=> 'hidden',
					'class'	=> 'no-reset',
					'name'	=> $elName,
					'value'	=> $v
				]
			);

			$this->addElement('span', $listItem, ['class' => 'selected-name'], $transValue);
		}

		/**
		 * Add the actual text input
		 */
		$inputWrapper			= $this->addElement('div', $wrapper, ['class' => 'multi-text-input-wrapper']);

		$attributes				= $this->attributes;
		$attributes['type']		= 'text';
		$attributes['name']		= $elName;
		$attributes['class']	.= "datalistinput multiple";
		
		$this->addElement('input', $inputWrapper, $attributes);

		$this->addElement('button', $inputWrapper, ['type' => "button", 'class' => "small add-list-selection hidden"], 'add');
	}

	/**
	 * Gets the html for elements with multiple instances
	 */
	protected function getMultiElementHtml($parent){
		if(empty($this->element->multiple)){
			return false;
		}
        
        if($this->element->type == 'text'){
			$this->getMultiTextInputHtml($parent);
			return;
		}
        
        if(
			empty($this->parentInstance->formData->save_in_meta) && 
			!empty($this->elementValues['defaults'])
		){
			$values		= array_values($this->elementValues['defaults']);
		}elseif(!empty($this->elementValues['metavalue'])){
			$values		= array_values($this->elementValues['metavalue']);
		}

		//check how many elements we should render
		$this->multiWrapValueCount	= max(1, count((array)$values));

        $multiWrapper = $this->addElement('div', $parent, ['class' => 'clone-divs-wrapper']);
		
		//create as many inputs as the maximum value found
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}
			
			//open the clone div
			$cloneDiv = $this->addElement(	"div", $multiWrapper, ["class" => 'clone-div', "data-div-id" => '$index']);
            
            //add label to each entry if prev element is a label and wrapped with this one
            $parentNode = $cloneDiv;
    		if(
    			!empty($this->prevElement)	&&
    			!empty($this->prevElement->wrap) && 
    			$this->prevElement != $this->element
    		){
    			$parentNode = $this->getElementHtml($this->prevElement, $cloneDiv);
    		}
            
            //wrap input AND buttons in a flex div
            $buttonWrapper = $this->addElement(
                "div", 
                $parentNode,
                [
                    'class' => 'button-wrapper',
                    'style' => 'width:100%; display: flex;'
                ]
            );
			
            // get base element
            if($this->element->type == 'select'){
    			$attributes	= $this->attributes;
    			$attributes['name']	= $this->element->name;
    			$node = $this->addElement(
    				'select',
    				$buttonWrapper,
    				$attributes
    			);
    		}else{
    			$node	= $this->addElement($this->tagType, $buttonWrapper, $this->attributes, $this->tagContent);
    		}
            
            $this->changeNodeAttributes($index, $val, $node);
            
            // add the buttons
            $this->addElement(
                'button', 
                $buttonWrapper,
                [
                    'type'	=> 'button',
                    'class' => 'add button',
                    'style' => 'flex: 1'
                ],
                '+'
            );
            
            $this->addElement(
                'button', 
                $buttonWrapper,
                [
                    'type' 	=> 'button',
                    'class' => 'remove button',
                    'style' => 'flex: 1'
                ],
                '-'
            );
        }
	}

	/**
	 * Options for a select element
	 * Adds all the options of a select element
	 */
	public function addSelectOptions($node){
		// Empty option on the top
		$this->addElement( "option", $node, ['value' => ''], '---');

		$selValues	= [];
		if(!empty($this->elementValues['metavalue'])){
			$selValues	= array_map('strtolower', $this->elementValues['metavalue']);
		}

		if(!empty($this->requestedValue)){
			if(is_array($this->requestedValue)){
				foreach($this->requestedValue as $v){
					$selValues[] = strtolower($v);
				}
			}else{
				$selValues[] = strtolower($this->requestedValue);
			}
		}

		foreach($this->elementValues['defaults'] as $key => $option){
			$attributes = [
                'value' => $key
            ];
            
            if(
				in_array(strtolower($option), $selValues) || 
				in_array(strtolower($key), $selValues) || 
				in_array($this->element->default_value, [$key, $option])
			){
				$attributes['selected'] ="selected";
			}
			$this->addElement( "option", $node, $attributes, $option);
		}
	}

	/**
	 * Adds the options to a datalist element
	 */
	public function addDatalistOptions($node){
		foreach($this->elementValues['defaults'] as $key => $option){
			if(is_array($option)){
				$value	= $option['value'];
			}else{
				$value	= $option;
			}
            
            $elContent = '';
            if(is_array($option)){
				$elContent = $option['display'];
			}

			$this->addElement(
                "option",
                $node, 
                [
                    'data-value' => $key,
                    'value' => $value
                ],
                $elContent
            );
		}
	}

	/**
	 * Adds all the checkboaxes or radios
	 *
	 */
	public function addCheckboxes($parent){
		/**
		 * Get all the checked options and make them lowercase
		 */
		$selected	= [];
		
		$defaultKey				= $this->element->default_value;
		if(!empty($defaultKey) && !empty($this->elementValues['defaults'][$defaultKey])){
			$selected[]		= strtolower($this->elementValues['defaults'][$defaultKey]);
		}

		if(!empty($this->elementValues['metavalue'] )){
			foreach($this->elementValues['metavalue'] as $key => $val){
				if(is_array($val)){
					foreach($val as $v){
						if(is_array($v)){
							foreach($v as $v2){
								$selected[] = strtolower($v2);
							}
						}else{
							$selected[] = strtolower($v);
						}
					}
				}else{
					$selected[] = strtolower($val);
				}
			}
		}

		if(!empty($this->requestedValue)){
			if(is_array($this->requestedValue)){
				foreach($this->requestedValue as $v){
					if(is_array($v)){
						foreach($v as $av){
							if(is_array($av)){
								foreach($av as $aav){
									$selected[] = strtolower($aav);
								}
							}else{
								$selected[] = strtolower($av);
							}
						}
					}else{
						$selected[] = strtolower($v);
					}
				}
			}else{
				$selected[] = strtolower($this->requestedValue);
			}
		}

		/**
		 * Filters the options to build to show a checkbox or radio for
		 * 
		 * @param 	array	$options	the options in array where the key is the value of the element and the value is the text to be displayed
		 * @param	object	$object		this instance
		 */
		$options		= apply_filters('sim-forms-checkbox-options', $this->elementValues['defaults'], $this);

		// check the length of each option
		$maxLength		= 0;
		$totalLength	= 0;

		// Check how much space we need
		foreach($options as $option){
			$maxLength		 = max($maxLength, strlen($option));
			$totalLength	+= strlen($option);
		}

		$checkboxWrapper = $this->addElement('div', $parent, ['class' => 'checkbox-options-group formfield']);
		
        // build the options
		foreach($options as $key => $option){
			// Add a wrapping label
			$label = $this->addElement('label', $checkboxWrapper, ['class' => 'checkbox-label']);

			// Default attributes
            $attributes = $this->attributes;

			// Checked attribute
			if(
				in_array(strtolower($option), $selected) || 
				in_array(strtolower($key), $selected) || 
				in_array($this->element->default_value, [$key, $option])
			){
				$attributes['checked']	= 'checked';
			}

			// Element id
			if(!empty($attributes['id'])){
				$attributes['id']	.= "-$key";
			}
			
			// Other attributes
			$attributes['type'] = $this->element->type;
			$attributes['name'] = $this->element->name;
			$attributes['value'] = $key;

			// Add the input
			$this->addElement(
				"input",
				$label,
				$attributes
			);
			
			// Text for the checkbox or radio
			$this->addElement(
				"span",
				$label,
				['class' => 'optionlabel'],
				$option
			);

			// one of the options is longer than 8 or the total is more than 30, add each checkbox on a seperate line
			if($maxLength > 8 || $totalLength > 30){
				$this->addElement("br", $checkboxWrapper);
			}
		}
	}

	public function multiwrapStart(){
		// We are wrapping so we need to find the max amount of filled in fields
			$i								= $elementIndex + 1;
			$this->multiWrapValueCount		= 1;
			$this->multiWrapElementCount	= 0;

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
			
			//write down all the multi html
			$name	= str_replace('_multi-end', '_multi-start', $element->name);

			$this->multiWrapper = $this->addElement("div", $parent, ['class' => 'clone-divs-wrapper', 'name' => $name]);
			if(!$this->clonableFormStep){
				$this->addRawHtml( $this->renderButtons(), $this->multiWrapper);

				// Tablink buttons
				if($this->multiWrapElementCount >= $this->minElForTabs ){
					for ($index = 1; $index <= $this->multiWrapValueCount; $index++) {
						$active = '';

						if($index === 1){
							$active = 'active';
						}

						$this->addElement(
							'button',
							$this->multiWrapper,
							[
								 'class' => "button tablink $active",
									'type' => 'button',
									'id' => "show-{$element->name}-$index",
									'data-target' => "$this->tabId}-$index",
									'style' => 'margin-right:4px;'
								],
								"{$element->nicename} $index"
							);
					}
				}
			}
	}

	/**
	 * Gets the html of form element
	 *
	 * @param	mixed	$this->requestedValue		The value the element should have
	 *
	 * @return	string| WP error		The html
	 */
	public function getElementHtml($element, $parent, $requestedValue =''){
		$this->reset();
		$this->element				= $element;
		$this->requestedValue		= $requestedValue;

		$this->elementValues		= $this->getElementValues($element);

		$this->getAttributes();

		$this->getNameAttribute();			

		$this->getElementId();
		
		$this->getClasses();
		
		switch($this->element->type){
			case 'p':
				$content 	= wp_kses_post($this->element->text);
				$content	= SIM\deslash($content);

				$node		= $this->addElement('div', $parent, ['name'=>$this->element->name], $content);
				break;
			case 'php':
				//we store the functionname in the html variable replace any double \ with a single \
				$functionName 	= str_replace('\\\\', '\\', $this->element->functionname);

				//only continue if the function exists
				if (function_exists( $functionName ) ){
					$node		= $this->addRawHtml($functionName($this->userId), $parent);
				}else{
					$node		= $this->addElement('text', $parent, [], "php function '$functionName' not found");
				}

				break;
			case 'div-start':
				$attributes	= ['name'=> $this->element->name];
				if($this->element->hidden){
					$attributes["class"] = 'hidden';
				}

				$node		= $this->addElement('div', $parent, $attributes);
				break;
			case 'multi-start':
				$this->multiwrapStart();
				break;
			case 'div-end':
				break;
			case 'multi-end':
				// nothing to do
				if($this->multiWrapElementCount < $this->minElForTabs){
					$this->renderButtons();
				}
				break;
			case 'info':
				$node		= $this->addRawHtml($this->infoBoxHtml($this->element->text), $parent);

				break;
			case 'file':
			case 'image':
				$node		= $this->addRawHtml($this->uploaderHtml(), $parent);
				break;
			case 'radio':
			case 'checkbox':
				$node		= $this->addCheckboxes($parent);
				break;
			default:
				$this->getTagType();

				$this->getValue();
				
				$this->getMultiElementHtml($parent);

				$node = $this->addElement($this->tagType, $parent, $this->attributes);

				// do this after the creation of the element
				$this->getTagContent($node);	
			}
		
		//check if we need to transform a keyword to date
		preg_match_all('/%([^%;]*)%/i', $this->html, $matches);
		foreach($matches[1] as $key => $keyword){
			$keyword = str_replace('_',' ', $keyword);
			
			//If the keyword is a valid date keyword
			if(!empty(strtotime($keyword))){
				//convert to date
				$dateString = date("Y-m-d", strtotime($keyword));
				
				//update form element
				$this->html = str_replace($matches[0][$key], $dateString, $this->html);
			}
		}

		return apply_filters('sim-form-element-html', $node, $this);
	}
}