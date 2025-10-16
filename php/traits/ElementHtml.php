<?php
namespace SIM\FORMS;
use SIM;
use WP_Error;

trait ElementHtml{
	 /**
	 * Builds the array with default values for the current user
	 */
	function buildDefaultsArray(){
		//Only create one time
		if(empty($this->defaultValues)){
			if(empty($this->formName)){
				$this->formName	= $this->formData->name;
			}

			$this->defaultValues		= (array)$this->user->data;
			
			//Change ID to userid because its a confusing name
			$this->defaultValues['user_id']	= $this->defaultValues['ID'];
			unset($this->defaultValues['ID']);
			
			$this->defaultArrayValues	= [];
			
			foreach(['user_pass', 'user_activation_key', 'user_status', 'user_level'] as $field){
				unset($this->defaultValues[$field]);
			}
			
			//get defaults from filters
			$this->defaultValues		= apply_filters('sim_add_form_defaults', $this->defaultValues, $this->userId, $this->formName);
			$this->defaultArrayValues	= apply_filters('sim_add_form_multi_defaults', $this->defaultArrayValues, $this->userId, $this->formName);
			
			ksort($this->defaultValues);
			ksort($this->defaultArrayValues);
		}
	}
    
	/**
	 * Gets the prefilled values of an element
	 *
	 * @param	object	$element		The element
	 *
	 * @return	array					The array of values
	 */
	function getElementValues($element){
		$values	= [];

		// Do not return default values when requesting the html over rest api
		if(defined('REST_REQUEST')){
			//return $values;
		}
		
		if(in_array($element->type, $this->nonInputs)){
			return [];
		}

		$this->buildDefaultsArray();

		//get the elementName, remove [] and split on remaining [
		$elementNames	= explode('[', trim($element->name, '[]'));
		
		//retrieve meta values if needed
		if(!empty($this->formData->save_in_meta)){
			//only load usermeta once
			if(!is_array($this->usermeta)){
				//usermeta comes as arrays, only keep the first
				$this->usermeta	= [];
				foreach(get_user_meta($this->userId) as $key=>$meta){
					$this->usermeta[$key]	= $meta[0];
				}
				$this->usermeta	= apply_filters('sim_forms_load_userdata', $this->usermeta, $this->userId);
			}
		
			if(count($elementNames) == 1){
				//non array name
				$elementName			= $elementNames[0];
				$values['metavalue']	= [];
				if(isset($this->usermeta[$elementName])){
					$values['metavalue']	= (array)maybe_unserialize($this->usermeta[$elementName]);
				}
			}elseif(!empty($this->usermeta[$elementNames[0]])){
				//an array of values, we only want a specific one
				$values['metavalue']	= (array)maybe_unserialize($this->usermeta[$elementNames[0]]);
				

				unset($elementNames[0]);
				//loop over all the subkeys, and store the value until we have our final result
				$resultFound	= false;
				foreach($elementNames as $v){
					if(isset($values['metavalue'][$v])){
						$values['metavalue'] = (array)$values['metavalue'][$v];
						$resultFound	= true;
					}
				}

				// somehow it does not exist, return an empty value
				if(!$resultFound){
					$values['metavalue']	= '';
				}
			}
		}
		
		//add default values
		if(empty($element->multiple) || in_array($element->type, ['select','checkbox'])){
			$key					= $element->default_value;
			if(!empty($key)){
				if(isset($this->defaultValues[$key])){
					$values['defaults']		= $this->defaultValues[$key];
				}else{
					$values['defaults']		= $key;
				}
			}
		}
		
		if(!empty($element->default_array_value)){
			$key					= $element->default_array_value;
			if(!empty($this->defaultArrayValues[$key]) && is_array($this->defaultArrayValues[$key])){
				if(!empty($values['defaults']) && !is_array($values['defaults'])){
					$values['defaults']	= [$values['defaults']];
				}else{
					$values['defaults']	= [];
				}
				$values['defaults']		= array_merge($values['defaults'], $this->defaultArrayValues[$key]);
			}
		}

		return $values;
	}

	/**
	 * Gets the options of an element like styling etc.
	 *
	 * @param	object	$element		The element
	 *
	 * @return	array					Array of options
	 */
	function getElementOptions($element){
		$options	= [];
		//add element values
		$elValues	= explode("\n", trim($element->valuelist));
		
		if(!empty($elValues[0])){
			foreach($elValues as $value){
				$split = explode('|', $value);
				
				//Remove starting or ending spaces and make it lowercase
				$value 			= trim($split[0]);
				$escapedValue 	= str_replace([',', '(', ')'], ['', '', ''], $value);
				
				if(!empty($split[1])){
					$displayName	= $split[1];
				}else{
					$displayName	= $value;
				}
				
				$options[$escapedValue]	= $displayName;
			}
		}
		
		$defaultArrayKey				= $element->default_array_value;
		if(!empty($defaultArrayKey)){
			$this->buildDefaultsArray();
			$options	= (array)$this->defaultArrayValues[$defaultArrayKey]+$options;
		}
		
		return $options;
	}

	/**
	 * Changes the element html to accomodate multiple values
	 * Adds an index to the name and id and adds the value of the current index
	 * 
	 * @param	object			$element		The current element
	 * @param	int				$index			The current iteration index of the element
	 * @param	string			$elementHtml	The rendered element html so far
	 * @param	string|array	$value			The value to add
	 * 
	 * @return	string							The updated HTML
	 */
	function prepareElementHtml($element, $index, $elementHtml, $value){
		if($value === null){
			$value = '';
		}

		// make sure we add the [] after the index
		$elementHtml		= str_replace('[]', '', $elementHtml, $replaced);
		$indexString 		= "[$index]";
		if($replaced){
			$indexString	.= "[]";
		}

		//Add the key to the fields name
		$elementHtml	= preg_replace("/(name='.*?)'/i", "\${1}$indexString'", $elementHtml);

		// Add the key to the id
		$elementHtml	= preg_replace("/(id='.*?)'/i", "\${1}[$index]'", $elementHtml);
					
		//we are dealing with a select, add options
		if($element->type == 'select'){
			$elementHtml .= "<option value=''>---</option>";
			$options	= $this->getElementOptions($element);
			foreach($options as $optionKey => $option){
				if(strtolower($value) == strtolower($optionKey) || strtolower($value) == strtolower($option)){
					$selected	= 'selected="selected"';
				}else{
					$selected	= '';
				}
				$elementHtml .= "<option value='$optionKey' $selected>$option</option>";
			}

			$elementHtml  .= "</select>";
		}elseif(in_array($element->type, ['radio', 'checkbox'])){
			$options	= $this->getElementOptions($element);

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
				
				if($found){
					//remove the % to leave the selected
					$elementHtml	= str_replace('%', '', $elementHtml);
				}else{
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
		if($element->type == 'label' && $this->multiWrapElementCount < $this->minElForTabs){
			$nr				= $index + 1;
			$elementHtml	= str_replace('</h4>'," $nr</h4>", $elementHtml);
		}

		return  $elementHtml;
	}

	/**
	 * Renders the html for element who can have multiple inputs
	 *
	 * @param	object	$element		The element
	 * @param	array	$values			The values for this element
	 * @param	string	$elementHtml	The html of a single element
	 */
	function multiInput($element, $values=null, $elementHtml=''){
		$this->multiInputsHtml	= [];
		
		//add label to each entry if prev element is a label and wrapped with this one
		if($this->prevElement->type	== 'label' && !empty($this->prevElement->wrap) && $this->prevElement != $element){
			$this->prevElement->text = $this->prevElement->text.' %key%';
			$prevLabel = $this->getElementHtml($this->prevElement).'</label>';
		}else{
			$prevLabel	= '';
		}

		if(empty($this->formData->save_in_meta) && !empty($values['defaults'])){
			$values		= array_values((array)$values['defaults']);
		}elseif(!empty($values['metavalue'])){
			$values		= array_values((array)$values['metavalue']);
		}

		//check how many elements we should render
		$this->multiWrapValueCount	= max(1, count((array)$values));

		//create as many inputs as the maximum value found
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}

			$elementItemHtml	= $this->prepareElementHtml($element, $index, $elementHtml, $val);
			
			//open the clone div
			$html	= "<div class='clone-div' data-div-id='$index'>";
				//add flex to single multi items, re-add the label for each value
				$html .= str_replace('%key%', $index+1, $prevLabel);
				
				//wrap input AND buttons in a flex div
				$html .= "<div class='button-wrapper' style='width:100%; display: flex;'>";
			
					//write the element
					$html .= $elementItemHtml;
			
					//close any label first before adding the buttons
					if($this->wrap == 'label'){
						$html .= "</label>";
					}
			
					//close select
					if($element->type == 'select'){
						$html .= "</select>";
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
	function getPrevValues($element, $returnArray=false){
		// Check if we should inlcude previous submitted values
		$prevValues		= '';

		if($returnArray){
			$prevValues		= [];
		}
		
		if(str_contains($_SERVER['REDIRECT_URL'], 'get_input_html')){
			$valueIndexes	= explode('[', $element->name);

			foreach($valueIndexes as $i=>$index){
				if($i == 0){
					if(!isset($this->submission->formresults[$index])){
						break;
					}

					$prevValues	= $this->submission->formresults[$index];
				}else{
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
		}

		return $prevValues;
	}

	/**
	 * Processes the options for an element
	 */
	protected function processElementOptions($element, &$elClass, &$elOptions, &$extraHtml){
		/*
			BUTTON TYPE
		*/
		if($element->type == 'button'){
			$elOptions	.= " type='button'";
		}

		if(empty($element->options)){
			return;
		}

		$removeMin	= false;

		// do not have min values in a form table to allow to edit values for the past
		if(get_class() == 'SIM\FORMS\DisplayFormResults'){
			$removeMin	= true;
		}

		//Store options in an array
		$options	= explode("\n", trim($element->options));
		
		//Loop over the options array
		foreach($options as $option){
			//Remove starting or ending spaces and make it lowercase
			$option 		= trim($option);

			$optionType		= explode('=', $option)[0];
			$optionValue	= str_replace('\\\\', '\\', explode('=', $option)[1]);
			
			if($removeMin && in_array($optionType, ['min', 'max'])){
				continue;
			}

			if($optionType == 'class'){
				$elClass .= " $optionValue";
			}else{
				//remove any leading "
				$optionValue	= trim($optionValue, '\'"');

				if($element->type == 'date' && in_array($optionType, ['min', 'max', 'value']) && strtotime($optionValue)){
					$optionValue	= Date('Y-m-d', strtotime($optionValue));
				}

				//Write the corrected option as html
				$elOptions	.= " $optionType=\"$optionValue\"";
			}
			
			// we are getting the html for an input and that input depends on a datalist
			if($optionType == 'list'){
				$datalist	= $this->getElementByName($optionValue);

				if($datalist == $element){
					$datalist	= $this->getElementByName($optionValue.'-list');
					SIM\printArray("Datalist '$optionValue' cannot have the same name as the element depending on it");
				}

				if($datalist){
					$extraHtml	.= $this->getElementHtml($datalist);
				}
			}
		}
	}

	/**
	 * Returns the html for an info element
	 */
	public function infoBoxHtml($text){
		//remove any paragraphs
		$content = str_replace(['<p>', '</p>'], '', $text);
		$content = SIM\deslash($content);
		
		ob_start();
		?>
		<div class='info-box'>
			<div style="float:right">
				<p class="info-icon">
					<img draggable="false" role="img" class="emoji" alt="ℹ" src="<?php echo SIM\PICTURESURL.'/info.png';?>" loading="lazy" >
				</p>
			</div>
			<span class='info-text'>
				<?php echo $content;?>
			</span>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Gets the html for a file or image element
	 */
	protected function uploaderHtml($element, $elOptions){
		$name		= $element->name;
			
		// Element setting
		if(!empty($element->foldername)){
			if(str_contains($element->foldername, "private/")){
				$targetDir	= $element->foldername;
			}else{
				$targetDir	= "private/".$element->foldername;
			}
		}

		// Form setting
		if(empty($targetDir)){
			$targetDir = $this->formData->upload_path;
		}

		// Default setting
		if(empty($targetDir)){
			$targetDir = 'form_uploads/'.$this->formData->form_name;
		}

		if(empty($this->formData->save_in_meta)){
			$library	= false;
			$metakey	= '';
			$userId		= '';
		}else{
			$library	= $element->library;
			$metakey	= $name;
			$userId		= $this->userId;
		}
		//Load js
		$uploader = new SIM\FILEUPLOAD\FileUpload($userId, $metakey, $library, '', false);
		
		return $uploader->getUploadHtml($name, $targetDir, $element->multiple, $elOptions, $element->editimage);
	}

	/**
	 * Determines the tag type of an element
	 */
	protected function getTagType($element){
		$tagType	= "input type='{$element->type}'";

		if(in_array($element->type, ['formstep', 'info', 'div-start'])){
			$tagType	= "div";
		}elseif(in_array($element->type, array_merge($this->nonInputs, ['select', 'textarea']))){
			$tagType	= $element->type;
		}

		return $tagType;
	}

	/**
	 * Determines the element name
	 */
	protected function getElementNameForHtml($element){
		// [] not yet added to name
		if(in_array($element->type, ['radio','checkbox']) && !str_contains($element->name, '[]')) {
			$element->name .= '[]';
		}

		$nameString	= "name='$element->name'" ;

		// No need for a name if not an input
		if(in_array($element->type, $this->nonInputs)){
			$nameString	= '';
		}

		return $nameString;
	}

	/**
	 * Get element Id
	 */
	protected function getElementId($element){
		$elId	= "";

		//datalist needs an id to work as well as mandatory elements for use in anchor links
		if($element->type == 'datalist' || $element->mandatory || $element->recommended){
			$elId	= "id='$element->name'";
		}

		if(str_contains($element->name, '[]')){
			$elId	= "id='E$element->id'";
		}

		return $elId;
	}

	/**
	 * Gets the class string for an element
	 */
	protected function getElementClass($element){
		$elClass = " formfield";

		switch($element->type){
			case 'label':
				$elClass .= " form-label";
				break;
			case 'button':
				$elClass .= " button";
				break;
			case 'formstep':
				$elClass .= " formstep step-hidden";
				break;
			default:
				$elClass .= " formfield-input";
		}

		return $elClass;
	}

	/**
	 * Gets the element value
	 */
	protected function getElementValueForHtml($element, &$requestedValue, &$defaultValues, &$val){
		if(in_array($element->type, $this->nonInputs)){
			return '';
		}

		$valueString	= '';

		// The requested value is a value of a previous submission, find previous submitted values if not provided to the function
		if(empty($requestedValue)){
			$requestedValue	= $this->getPrevValues($element);
		}

		$defaultValues	= $this->getElementValues($element);

		// Write a placeholder value for multi elements
		if($this->multiwrap || !empty($element->multiple)){
			if(str_contains($element->type, 'input')){
				$valueString	= "value='%value%'";
			}

			return $valueString;
		}
		

		if(!empty($defaultValues) || !empty($requestedValue)){
			$val	= $requestedValue;

			if(empty($requestedValue)){
				//this is an input and there is a value for it
				if(!empty($defaultValues['defaults']) && (empty($this->formData->save_in_meta) || empty($defaultValues['metavalue']))){
					$val		= array_values((array)$defaultValues['defaults'])[0];
				}elseif(!empty($defaultValues['metavalue'])){
					$elIndex	= 0;
					if(str_contains($element->name, '[]')){
						// Check if there are multiple elements with the same name
						$elements	= $this->getElementByName($element->name, '', false);

						foreach($elements as $elIndex=>$el){
							if($el->id == $element->id){
								break;
							}
						}
					}

					$val		= array_values((array)$defaultValues['metavalue'])[$elIndex];
				}
			}

			if(
				str_contains($element->type, 'input') && 
				!empty($val) && 
				!in_array($element->type, ['radio', 'checkbox'])
			){
				if(is_array($val)){
					$val	= array_values($val)[0];
				}

				$valueString	= "value='$val'";
			}
		}

		return $valueString;
	}

	/**
	 * Get the tag content of an element, i.e. the conten between the openening and closing tag
	 */
	protected function getTagContent($element, $requestedValue, $val, $values, $elClass, $elOptions, &$html){
		$tagContent	= '';

		if($element->type == 'textarea'){
			if(!empty($requestedValue)){
				if(is_array($requestedValue)){
					$requestedValue	= array_values($requestedValue)[0];
				}

				$tagContent = $requestedValue;
			}elseif(!empty($val)){
				if(is_array($val)){
					$val	= array_values($val)[0];
				}
				$tagContent = $val;
			}
		}elseif(!empty($element->text)){
			switch($element->type){
				case 'formstep':
					$tagContent = "<h3>{$element->text}</h3>";
					break;
				case 'label':
					$tagContent = "<h4 class='label-text'>{$element->text}</h4>";
					break;
				case 'button':
					$tagContent = $element->text;
					break;
				default:
					$tagContent = "<label class='label-text'>{$element->text}</label>";
			}
		}
		
		switch($element->type){
			case 'select':
				$tagContent .= $this->selectOptionsHtml($element, $requestedValue, $values);
				break;
			case 'datalist':
				$tagContent .= $this->datalistOptionsHtml($element);
				break;
			case 'radio':
			case 'checkbox':
				$html .= $this->checkboxesHtml($element, $requestedValue, $values, $element->type, $element->name, $elClass, $elOptions);
				break;
			default:
				$tagContent .= "";
		}

		return $tagContent;
	}

	protected function getMultiTextInputHtml($element, $elId, $elClass, $elOptions){
		// add previous made inputs
		$preValues	= $this->getPrevValues($element, true);

		if(empty($preValues) && !empty($this->defaultArrayValues[$element->default_value])){
			$preValues	= $this->defaultArrayValues[$element->default_value];

			if(!is_array($preValues)){
				$preValues	= [$preValues];
			}
		}

		$elName	= $element->name;
		if(!str_contains($elName, '[]')){
			$elName	.= '[]';
		}

		$html	= "<div class='option-wrapper'>";
			// container for choices made
			$html	.= "<ul class='list-selection-list'>";
				foreach($preValues as $v){
					if(method_exists($this, 'transformInputData')){
						$transValue		= $this->transformInputData($v, $element->name, $this->submission->formresults);
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
				$html	.= "<input type='text' $elId name='$element->name[]' class='$elClass datalistinput multiple' $elOptions>";
				$html	.= '<button type="button" class="small add-list-selection hidden">Add</button>';
			$html	.= "</div>";
		$html	.= "</div>";

		return $html;
	}

	/**
	 * Gets the html for elements with multiple instances
	 */
	protected function getMultiElementHtml($element, $elId, $elClass, $elOptions, $elType, $nameString, $elContent, $elClose, $values){
		if(empty($element->multiple)){
			return '';
		}

		if($element->type == 'select'){
			$html	= "<$element->type name='$element->name' $elId class='$elClass' $elOptions>";
		}elseif($element->type == 'text'){
			return $this->getMultiTextInputHtml($element, $elId, $elClass, $elOptions);
		}else{
			$html	= "<$elType $nameString $elId class='$elClass' $elOptions value='%value%'>$elContent$elClose";
		}
		
		$this->multiInput($element, $values, $html);

		$html	= "<div class='clone-divs-wrapper'>";
			foreach($this->multiInputsHtml as $h){
				$html	.= $h;
			}
		$html	.= '</div>';

		return $html;
	}

	/**
	 * Options html for a select element
	 * Returns all the options of a select element
	 *
	 * @param	object	$element		The element to get the options for
	 * @param	mixed	$requestedValue			The value of the element
	 * @param	array	$values			The default values for the element
	 * 
	 * @return	string			The html
	 */
	public function selectOptionsHtml($element, $requestedValue, $values){
		$elContent	= "<option value=''>---</option>";
		$options	= $this->getElementOptions($element);

		$selValues	= [];
		if(!empty($values['metavalue'])){
			$selValues	= array_map('strtolower', (array)$values['metavalue']);
		}

		if(!empty($requestedValue)){
			if(is_array($requestedValue)){
				foreach($requestedValue as $v){
					$selValues[] = strtolower($v);
				}
			}else{
				$selValues[] = strtolower($requestedValue);
			}
		}

		foreach($options as $key=>$option){
			if(
				in_array(strtolower($option), $selValues) || 
				in_array(strtolower($key), $selValues) || 
				in_array($element->default_value, [$key, $option])
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
	 *
	 * @param	object	$element		The element to get the options for
	 * 
	 * @return	string			The html
	 */
	public function datalistOptionsHtml($element){
		$options	= $this->getElementOptions($element);
		$elContent	= '';

		foreach($options as $key=>$option){
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
	 * @param	object	$element		The element to get the options for
	 * @param	mixed	$value			The value of the element
	 * @param	array	$values			The default values for the element
	 * @param	string	$elType			The element type either checkbox or radio
	 * @param	string	$elName			The element name
	 * @param	string	$elClass		Classes for the elements
	 * @param	string	$elOptions		The options for the elements
	 * 
	 * @return	string			The html
	 */
	public function checkboxesHtml($element, $requestedValue, $values, $elType, $elName, $elClass, $elOptions){
		$options	= $this->getElementOptions($element);
		$html		= "<div class='checkbox-options-group formfield'>";
		
		// get all the default options and make them lowercase
		$lowValues	= [];
		
		$defaultKey				= $element->default_value;
		if(!empty($defaultKey) && !empty($this->defaultValues[$defaultKey])){
			$lowValues[]	= strtolower($this->defaultValues[$defaultKey]);
		}

		if(isset($values['metavalue'] )){
			foreach((array)$values['metavalue'] as $key=>$val){
				if(is_array($val)){
					foreach($val as $v){
						if(is_array($v)){
							foreach($v as $v2){
								$lowValues[] = strtolower($v2);
							}
						}else{
							$lowValues[] = strtolower($v);
						}
					}
				}else{
					$lowValues[] = strtolower($val);
				}
			}
		}

		if(!empty($requestedValue)){
			if(is_array($requestedValue)){
				foreach($requestedValue as $v){
					if(is_array($v)){
						foreach($v as $av){
							if(is_array($av)){
								foreach($av as $aav){
									$lowValues[] = strtolower($aav);
								}
							}else{
								$lowValues[] = strtolower($av);
							}
						}
					}else{
						$lowValues[] = strtolower($v);
					}
				}
			}else{
				$lowValues[] = strtolower($requestedValue);
			}
		}

		// check the length of each option
		$maxLength		= 0;
		$totalLength	= 0;
		foreach($options as $option){
			$maxLength		= max($maxLength, strlen($option));
			$totalLength	+= strlen($option);
		}

		// build the options
		foreach($options as $key=>$option){
			if($this->multiwrap){
				$checked	= '%checked%';
			}elseif(
				in_array(strtolower($option), $lowValues) || 
				in_array(strtolower($key), $lowValues) || 
				in_array($element->default_value, [$key, $option])
			){
				$checked	= 'checked';
			}else{
				$checked	= '';
			}

			$id	= '';
			if(!empty($elId)){
				$id	= trim($elId, "'");
				$id	= "$id-$key'";
			}
			
			$html .= "<label class='checkbox-label'>";
				$html .= "<$elType name='$elName' $id class='$elClass' $elOptions value='$key' $checked>";
				$html .= "<span class='optionlabel'>$option</span>";
			$html .= "</label>";
			if($maxLength > 8 || $totalLength > 30){
				$html .= "</br>";
			}
		}

		$html .= "</div>";

		return $html;
	}

	/**
	 * Gets the html of form element
	 *
	 * @param	object	$element			The element
	 * @param	mixed	$requestedValue		The value the element should have
	 *
	 * @return	string| WP error		The html
	 */
	public function getElementHtml($element, $requestedValue =''){
		$html			= '';
		$extraHtml		= '';
		$elClass		= '';
		$elOptions		= '';
		$defaultValues	= [];

		$this->processElementOptions($element, $elClass, $elOptions, $extraHtml);
		
		if($element->type == 'p'){
			$html = wp_kses_post($element->text);
			$html = "<div name='{$element->name}'>".SIM\deslash($html)."</div>";
		}elseif($element->type == 'php'){
			//we store the functionname in the html variable replace any double \ with a single \
			$functionName 	= str_replace('\\\\', '\\', $element->functionname);
			//only continue if the function exists
			if (function_exists( $functionName ) ){
				$html		= $functionName($this->userId);
			}else{
				$html		= "php function '$functionName' not found";
			}
		}elseif($element->type == 'div-start'){
			$class		= '';
			if($element->hidden){
				$class	= "class='hidden'";
			}
			$html 		= "<div name='$element->name' $class>";
		}elseif($element->type == 'div-end'){
			$html 		= "</div>";
		}elseif(in_array($element->type, ['multi-start','multi-end'])){
			$html 		= "";
		}elseif(in_array($element->type, ['info'])){
			$html	.= $this->infoBoxHtml($element->text);
		}elseif(in_array($element->type, ['file','image'])){
			$html	.= $this->uploaderHtml;
		}else{
			$elType			= $this->getTagType($element);

			$nameString 	= $this->getElementNameForHtml($element);			

			$elId			= $this->getElementId($element);
			
			$elClass		= $this->getElementClass($element);

			$valueString	= $this->getElementValueForHtml($element, $requestedValue, $defaultValues, $val);			
			
			$elContent 		= $this->getTagContent($element, $requestedValue, $val, $defaultValues, $elClass, $elOptions, $html);			
			
			//close the html
			if(in_array($element->type, ['select', 'textarea', 'button', 'datalist'])){
				$elClose	= "</{$element->type}>";
			}
			
			//only close a label field if it is not wrapped
			elseif($element->type == 'label' && empty($element->wrap)){
				$elClose	= "</label>";
			}
			else{
				$elClose	= "";
			}		
			
			$html			.= $this->getMultiElementHtml($element, $elId, $elClass, $elOptions, $elType, $nameString, $elContent, $elClose, $defaultValues);

			if(empty($html)){
				$html	= "<$elType $nameString $elId class='$elClass' $elOptions $valueString>$elContent$elClose";
			}
		}

		$html	= $extraHtml.$html;

		// filter the element html
		$html	= apply_filters('sim-forms-element-html', $html, $element, $this);
		
		//remove unnessary whitespaces
		$html = preg_replace('/\h+/', ' ', $html);
		
		//check if we need to transform a keyword to date
		preg_match_all('/%([^%;]*)%/i', $html, $matches);
		foreach($matches[1] as $key => $keyword){
			$keyword = str_replace('_',' ', $keyword);
			
			//If the keyword is a valid date keyword
			if(!empty(strtotime($keyword))){
				//convert to date
				$dateString = date("Y-m-d", strtotime($keyword));
				
				//update form element
				$html = str_replace($matches[0][$key], $dateString, $html);
			}
		}

		return apply_filters('sim-form-element-html', $html, $element, $this);
	}
}

