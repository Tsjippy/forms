<?php
namespace SIM\FORMS;
use SIM;
use WP_Error;

class ElementHtmlBuilder extends SimForms{
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
	private $nameHtml;
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
		$this->nameHtml				= '';
		$this->idHtml				= '';
		$this->classHtml			= '';
		$this->valueHtml			= '';
		$this->selectedValue		= '';
		$this->tagContent			= '';
		$this->tagCloseHtml			= '';
		$this->selectOptionsHtml	= '';
		$this->html					= '';
	}

    /**
	 * Gets the options html string
	 */
	protected function getOptionHtml(){
		/*
			BUTTON TYPE
		*/
		if($this->element->type == 'button'){
			$this->optionsHtml	.= " type='button'";
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
				$this->classHtml .= " $optionValue";
			}else{
				//remove any leading "
				$optionValue	= trim($optionValue, '\'"');

				if($this->element->type == 'date' && in_array($optionType, ['min', 'max', 'value']) && strtotime($optionValue)){
					$optionValue	= Date('Y-m-d', strtotime($optionValue));
				}

				//Write the corrected option as html
				$this->optionsHtml	.= " $optionType=\"$optionValue\"";
			}
		}
	}

	/**
	 * Changes the element html to accomodate multiple values
	 * Adds an index to the name and id and adds the value of the current index
	 * 
	 * @param	int				$index			The current iteration index of the element
	 * @param	string|array	$value			The value to add
	 * 
	 * @return	string							The updated HTML
	 */
	function prepareElementHtml($index, $value, $baseHtml=''){
		if($value === null){
			$value = '';
		}

		if(empty($baseHtml)){
			$baseHtml	= $this->html;
		}

		// make sure we add the [] after the index
		$elementHtml		= str_replace('[]', '', $baseHtml, $replaceCount);
		$indexString 		= "[$index]";
		if($replaceCount){
			$indexString	.= "[]";
		}

		//Add the key to the fields name
		$elementHtml	= preg_replace("/(name='.*?)'/i", "\${1}$indexString'", $elementHtml);

		// Add the key to the id
		$elementHtml	= preg_replace("/(id='.*?)'/i", "\${1}[$index]'", $elementHtml);
					
		//we are dealing with a select, add options
		if($this->element->type == 'select'){
			$elementHtml .= "<option value=''>---</option>";

			// Loop over the select options to see which option should be selected
			foreach($this->elementValues['defaults'] as $optionKey => $option){
				if(strtolower($value) == strtolower($optionKey) || strtolower($value) == strtolower($option)){
					$selected	= 'selected="selected"';
				}else{
					$selected	= '';
				}
				$elementHtml .= "<option value='$optionKey' $selected>$option</option>";
			}

			$elementHtml  .= "</select>";
		}
		
		// Add the options for checkboxes and radios
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
					//remove the % to leave the selected
					$elementHtml	= str_replace('%', '', $elementHtml);
				}
				
				// This option is not selected
				else{
					//remove the %checked% keyword
					$elementHtml	= str_replace('%checked%', '', $elementHtml);
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
	 * Renders the html for element who can have multiple inputs
     * 
	 */
	function multiInput($initialHtml){
		$this->multiInputsHtml	= [];
		
		//add label to each entry if prev element is a label and wrapped with this one
		if(
			!empty($this->prevElement)	&&
			$this->prevElement->type	== 'label' && 
			!empty($this->prevElement->wrap) && 
			$this->prevElement != $this->element
		){
			$this->prevElement->text = $this->prevElement->text.' %key%';
			$prevLabel = $this->getElementHtml($this->prevElement).'</label>';
		}else{
			$prevLabel	= '';
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

		//create as many inputs as the maximum value found
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}

			$elementItemHtml	= $this->prepareElementHtml($index, $val, $initialHtml);
			
			//open the clone div
			$html	= "<div class='clone-div' data-div-id='$index'>";
				//add flex to single multi items, re-add the label for each value
				$html .= str_replace('%key%', $index + 1, $prevLabel);
				
				//wrap input AND buttons in a flex div
				$html .= "<div class='button-wrapper' style='width:100%; display: flex;'>";
			
					//write the element
					$html .= $elementItemHtml;
			
					//close any label first before adding the buttons
					if($this->wrap == 'label'){
						$html .= "</label>";
					}
			
					$html .= "<button type='button' class='add button' style='flex: 1;'>+</button>";
					$html .= "<button type='button' class='remove button' style='flex: 1;'>-</button>";
				$html .= "</div>";
			$html .= "</div>";//close clone-div

			$this->multiInputsHtml[$index]	= $html;
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
		
		$this->html		= $uploader->getUploadHtml($name, $targetDir, $this->element->multiple, $this->optionsHtml, $this->element->editimage);
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
	protected function getNameHtml(){
		$this->element->name	= trim($this->element->name, " \n\r\t\v\0_");

		// [] not yet added to name
		if(in_array($this->element->type, ['radio','checkbox']) && !str_contains($this->element->name, '[]')) {
			$this->element->name .= '[]';
		}

		$this->nameHtml		= "name='{$this->element->name}'" ;

		$nameNeeded			= false;

		// No need for a name if not an input, only needed for js code
		/* if(in_array($this->element->type, $this->parentInstance->nonInputs)){
			$this->nameHtml	= '';
		} */
	}

	/**
	 * Get element Id
	 */
	protected function getElementId(){
		$this->idHtml	= "";

		//datalist needs an id to work as well as mandatory elements for use in anchor links
		if($this->element->type == 'datalist' || $this->element->mandatory || $this->element->recommended){
			$this->idHtml	= "id='{$this->element->name}'";
		}

		if(str_contains($this->element->name, '[]')){
			$this->idHtml	= "id='E{$this->element->id}'";
		}
	}

	/**
	 * Gets the class string for an element
	 */
	protected function getClassHtml(){
		$this->classHtml = " formfield";

		switch($this->element->type){
			case 'label':
				$this->classHtml .= " form-label";
				break;
			case 'button':
				$this->classHtml .= " button";
				break;
			case 'formstep':
				$this->classHtml .= " formstep step-hidden";
				break;
			default:
				$this->classHtml .= " formfield-input";
		}
	}

	/**
	 * Gets the element value
	 */
	protected function getValueHtml(){
		if(in_array($this->element->type, $this->nonInputs)){
			return '';
		}

		// The requested value is a value of a previous submission, find previous submitted values if not provided to the function
		if(empty($this->requestedValue)){
			$this->requestedValue	= $this->getPrevValues();
		}

		// Write a placeholder value for multi elements
		if($this->parentInstance->multiwrap || !empty($this->element->multiple)){
			if(str_contains($this->tagType, 'input')){
				$this->valueHtml	= "value='%value%'";
			}

			return;
		}
		

		if(empty($this->elementValues) && empty($this->requestedValue)){
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

			$this->valueHtml	= "value='$this->selectedValue'";
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

	/**
	 * Determines if we should have a tag closure
	 */
	protected function getTagClose(){
		//close the html
		if(in_array($this->element->type, ['select', 'textarea', 'button', 'datalist'])){
			$this->tagCloseHtml	= "</{$this->element->type}>";
		}
		
		//only close a label field if it is not wrapped
		elseif($this->element->type == 'label' && empty($this->element->wrap)){
			$this->tagCloseHtml	= "</label>";
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
	protected function getMultiElementHtml(){
		if(empty($this->element->multiple)){
			return;
		}

		if($this->element->type == 'select'){
			$html	= "<select name='{$this->element->name}' $this->idHtml class='$this->classHtml' $this->optionsHtml>";
		}elseif($this->element->type == 'text'){
			$this->getMultiTextInputHtml();
			return;
		}else{
			$html	= "<$this->tagType $this->nameHtml $this->idHtml class='$this->classHtml' $this->optionsHtml value='%value%'>$this->tagContent$this->tagCloseHtml";
		}
		
		$this->multiInput($html);

		$html	= "<div class='clone-divs-wrapper'>";
			foreach($this->multiInputsHtml as $h){
				$html	.= $h;
			}
		$html	.= '</div>';

		$this->html	= $html; 
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
				if($this->multiwrap){
					$checked	= '%checked%';
				}elseif(
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
	public function getElementHtml($element, $requestedValue =''){
		$this->reset();
		$this->element				= $element;
		$this->requestedValue		= $requestedValue;

		$this->elementValues		= $this->getElementValues($element);

		$this->getOptionHtml();
		
		if($this->element->type == 'p'){
			$content 	= wp_kses_post($this->element->text);
			$content	= SIM\deslash($content);

			$this->html = "<div name='{$this->element->name}'>$content</div>";
		}elseif($this->element->type == 'php'){
			//we store the functionname in the html variable replace any double \ with a single \
			$functionName 	= str_replace('\\\\', '\\', $this->element->functionname);

			//only continue if the function exists
			if (function_exists( $functionName ) ){
				$this->html		= $functionName($this->userId);
			}else{
				$this->html		= "php function '$functionName' not found";
			}
		}elseif($this->element->type == 'div-start'){
			$class		= '';
			if($this->element->hidden){
				$class	= "class='hidden'";
			}
			$this->html 		= "<div name='{$this->element->name}' $class>";
		}elseif($this->element->type == 'div-end'){
			$this->html 		= "</div>";
		}elseif(in_array($this->element->type, ['multi-start', 'multi-end'])){
			$this->html 		= "";
		}elseif(in_array($this->element->type, ['info'])){
			$this->html	.= $this->infoBoxHtml($this->element->text);
		}elseif(in_array($this->element->type, ['file', 'image'])){
			$this->uploaderHtml();
		}else{
			$this->getTagType();

			$this->getNameHtml();			

			$this->getElementId();
			
			$this->getClassHtml();

			$this->getValueHtml();			
			
			$this->getTagContent();			
			
			$this->getTagClose();	
			
			$this->getMultiElementHtml();

			if(empty($this->html)){
				$this->html	= "<$this->tagType $this->nameHtml $this->idHtml class='$this->classHtml' $this->optionsHtml $this->valueHtml>$this->tagContent$this->tagCloseHtml";
			}
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