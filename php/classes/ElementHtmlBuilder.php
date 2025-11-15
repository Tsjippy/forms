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
    private $optionsHtml;
    private $classHtml;
	private $tagType;
	private $idHtml;
	private $valueHtml;
	private $selectedValue;
	private $tagContent;
	private $tagCloseHtml;
	private $selectOptionsHtml;
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
		$this->optionsHtml			= '';
		$this->tagType				= '';
		$this->selectedValue		= '';
		$this->tagContent			= '';
		$this->tagCloseHtml			= '';
		$this->selectOptionsHtml	= '';
		$this->html					= '';
		$this->attributes			= ['class' => ''];
	}

    /**
	 * Gets the options html string
	 */
	protected function getOptionHtml(){
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

				if($this->element->type == 'date' && in_array($optionType, ['min', 'max', 'value']) && strtotime($optionValue)){
					$optionValue	= Date('Y-m-d', strtotime($optionValue));
				}

				//Write the corrected option as html
				$this->attributes[$optionType] = "$optionValue";
			}
		}
	}

	/**
	 * Changes the element html to accomodate multiple values
	 * Adds an index to the name and id and adds the value of the current index
	 * 
	 * @param	int				$index			The current iteration index of the element
	 * @param	string|array	$value			The value to add
	 * @param	object			$node			The node to edit
	 * 
	 * @return	string							The updated HTML
	 */
	function multiElementHtml($index, $value, $node){
		if($value === null){
			$value = '';
		}

		// make sure we add the [] after the index
		$attribute 					= $node->attributes['name'];
		$node->attributes['name']	= str_replace('[]', '', $node->attributes['name'], $replaceCount);
		$indexString 				= "[$index]";
		if($replaceCount){
			$indexString			.= "[]";
		}

		// Add the index to the name
		$node->attributes['name']	= $node->attributes['name'].$indexString;

		// Add the index to the id
		$node->attributes['id']		= $node->attributes['id']."[$index]";
					
		//we are dealing with a select, add options
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
		
		// Make add the selected value if any
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
		}elseif(is_array($value)){
			$elementHtml	= str_replace('%value%', $value[$index], $elementHtml);
		}else{
			$elementHtml	= str_replace('%value%', $value, $elementHtml);
		}

		// Add the index to the label if we are not displaying it on seperate tabs
		if($this->element->type == 'label' && $this->parentInstance->multiWrapElementCount < $this->parentInstance->minElForTabs){
			$nr				= $index + 1;
			$elementHtml	= str_replace('</h4>'," $nr</h4>", $elementHtml);
		}

		return $elementHtml;
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
		
		return $uploader->getUploadHtml($name, $targetDir, $this->element->multiple, $this->optionsHtml, $this->element->editimage);
	}

	/**
	 * Determines the tag type of an element
	 */
	protected function getTagType(){
		$this->tagType		= "input type='{$this->element->type}'";

		if(in_array($this->element->type, ['formstep', 'info', 'div-start'])){
			$this->tagType	= "div";
		}elseif(in_array($this->element->type, array_merge($this->parentInstance->nonInputs, ['select', 'textarea']))){
			$this->tagType	= $this->element->type;
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

		// Write a placeholder value for multi elements
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
	protected function getTagContent(){
		if($this->element->type == 'textarea'){
			$value	= $this->requestedValue;
			if(empty($value)){
				$value	= $this->selectedValue;
			}

			if(!empty($value)){
				if(is_array($value)){
					$value	= array_values($value)[0];
				}

				$this->tagContent = $value;
			}
		}elseif(!empty($this->element->text)){
			switch($this->element->type){
				case 'formstep':
					$this->tagContent = "<h3>{$this->element->text}</h3>";
					break;
				case 'label':
					$this->tagContent = "<h4 class='label-text'>{$this->element->text}</h4>";
					break;
				case 'button':
					$this->tagContent = $this->element->text;
					break;
				default:
					$this->tagContent = "<label class='label-text'>{$this->element->text}</label>";
			}
		}
		
		switch($this->element->type){
			case 'select':
				$this->tagContent .= $this->selectOptionsHtml();
				break;
			case 'datalist':
				$this->tagContent .= $this->datalistOptionsHtml();
				break;
			case 'radio':
			case 'checkbox':
				$this->html .= $this->checkboxesHtml();
				break;
			default:
				$this->tagContent .= "";
		}
	}

	protected function getMultiTextInputHtml(){
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

		$html	= "<div class='option-wrapper'>";
			// container for choices made
			$html	.= "<ul class='list-selection-list'>";
				foreach($this->requestedValue as $v){
					if(method_exists($this, 'transformInputData')){
						if(empty($this->submissions)){
							$this->submissions	= $this->parentInstance->submissions;
						}
						$transValue		= $this->transformInputData($v, $this->element->name, $this->submissions[0]);
					}else{
						$transValue		= $v;
					}

					$html	.= "<li class='list-selection'>";
						$html	.= "<button type='button' class='small remove-list-selection'>";
							$html	.= "<span class='remove-list-selection'>×</span>";
						$html	.= "</button>";
						$html	.= "<input type='hidden' class='no-reset' name='$elName' value='$v'>";
						$html	.= "<span class='selected-name'>$transValue</span>";
					$html	.= "</li>";
				}
			$html	.= "</ul>";

			// add the text input
			$html	.= "<div class='multi-text-input-wrapper'>";
				$html	.= "<input type='text' $this->idHtml name='$elName' class='$this->classHtml datalistinput multiple' $this->optionsHtml>";
				$html	.= '<button type="button" class="small add-list-selection hidden">Add</button>';
			$html	.= "</div>";
		$html	.= "</div>";

		$this->html	= $html;
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

        $multiWrapper = $this->addElement('div', $parent, ['class' => 'clone-divs-wrapper');
		
		//create as many inputs as the maximum value found
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}
			
			//open the clone div
			$cloneDiv = $this->addElement(	"div", $multiWrapper, ["class" => 'clone-div', "data-div-id" => '$index']);
            
            //add label to each entry if prev element is a label and wrapped with this one
            $parentNode = cloneDiv;
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
            
            // add the buttons
            $this->addElement(
                'button', 
                $buttonWrapper,
                [
                    'type' => 'button',
                    'class' => 'add button',
                    'style' => 'flex: 1'
                ],
                '+'
            );
            
            $this->addElement(
                'button', 
                $buttonWrapper,
                [
                    'type' => 'button',
                    'class' => 'remove button',
                    'style' => 'flex: 1'
                ],
                '-'
            );
        }

	/**
	 * Options html for a select element
	 * Gets all the options of a select element
	 * 
	 * @return	string			The html
	 */
	public function selectOptionsHtml(){
		$elContent	= "<option value=''>---</option>";

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
			if(
				in_array(strtolower($option), $selValues) || 
				in_array(strtolower($key), $selValues) || 
				in_array($this->element->default_value, [$key, $option])
			){
				$selected	= 'selected="selected"';
			}else{
				$selected	= '';
			}
			$elContent .= "<option value='$key' $selected>$option</option>";
		}

		return $elContent;
	}

	/**
	 * Options html for a datalist element
	 * Returns all the options of a datalist
	 */
	public function datalistOptionsHtml(){
		$elContent	= '';

		foreach($this->elementValues['defaults'] as $key => $option){
			if(is_array($option)){
				$value	= $option['value'];
			}else{
				$value	= $option;
			}

			$elContent .= "<option data-value='$key' value='$value'>";

			if(is_array($option)){
				$elContent .= $option['display']."</option>";
			}
		}

		return $elContent;
	}

	/**
	 * Returns all the element of a radio or checkbox element
	 *
	 */
	public function checkboxesHtml(){
		// Get all the checked options and make them lowercase
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
		// Check how much space wee need
		foreach($options as $option){
			$maxLength		 = max($maxLength, strlen($option));
			$totalLength	+= strlen($option);
		}

		$html		= "<div class='checkbox-options-group formfield'>";
			// build the options
			foreach($options as $key => $option){
				if(
					in_array(strtolower($option), $selected) || 
					in_array(strtolower($key), $selected) || 
					in_array($this->element->default_value, [$key, $option])
				){
					$checked	= 'checked';
				}else{
					$checked	= '';
				}

				$id	= '';
				if(!empty($this->idHtml)){
					$id	= trim($this->idHtml, "'");
					$id	= "$id-$key'";
				}
				
				$html .= "<label class='checkbox-label'>";
					$html .= "<input type='{$this->element->type}' name='{$this->element->name}' $id class='$this->classHtml' $this->optionsHtml value='$key' $checked>";
					$html .= "<span class='optionlabel'>$option</span>";
				$html .= "</label>";


				if($maxLength > 8 || $totalLength > 30){
					$html .= "<br>";
				}
			}

		$html .= "</div>";

		return $html;
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

		$this->getOptionHtml();
		
		if($this->element->type == 'p'){
			$content 	= wp_kses_post($this->element->text);
			$content	= SIM\deslash($content);

			$this->addElement('div', $parent, ['name'=>$this->element->name], $content);
		}elseif($this->element->type == 'php'){
			//we store the functionname in the html variable replace any double \ with a single \
			$functionName 	= str_replace('\\\\', '\\', $this->element->functionname);

			//only continue if the function exists
			if (function_exists( $functionName ) ){
				$this->addRawHtml($functionName($this->userId), $parent);
			}else{
				$this->addElement('text', $parent, [], "php function '$functionName' not found");
			}
		}elseif($this->element->type == 'div-start'){
			$attributes	= ['name'=> $this->element->name];
			if($this->element->hidden){
				$attributes["class"] = 'hidden';
			}
			$this->addElement('div', $parent, $attributes);
		}elseif($this->element->type == 'div-end'){
			// not sure what to do here
			$this->html 		= "</div>";
		}elseif(in_array($this->element->type, ['multi-start', 'multi-end'])){
			$this->html 		= "";
		}elseif(in_array($this->element->type, ['info'])){
			$this->addRawHtml($this->infoBoxHtml($this->element->text), $parent);
		}elseif(in_array($this->element->type, ['file', 'image'])){
			$this->addRawHtml($this->uploaderHtml(), $parent);
		}else{
			$this->getTagType();

			$this->getNameAttribute();			

			$this->getElementId();
			
			$this->getClasses();

			$this->getValue();
			
			$this->getMultiElementHtml($parent);

			if(empty($this->html)){
				$this->html	= "<$this->tagType $this->nameHtml $this->idHtml class='$this->classHtml' $this->optionsHtml $this->valueHtml>$this->tagContent$this->tagCloseHtml";
			}

			// do this after the creation of the element
			$this->getTagContent();	
		}
		
		//remove unnessary whitespaces
		$this->html = preg_replace('/\h+/', ' ', $this->html);
		
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

		return apply_filters('sim-form-element-html', $this->html, $this);
	}
}