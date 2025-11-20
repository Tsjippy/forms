<?php
namespace SIM\FORMS;
use SIM;
use WP_Error;

class ElementHtmlBuilder extends SubmitForm{
    public $defaultArrayValues;
    public $prevElement;
	public $nextElement;
	public $currentElement;
    public $multiWrapValueCount;
    public $wrap;
    public $defaultValues;
    public $element;
	private $requestedValue;
	private $elementValues;
	private $tagType;
	private $selectedValue;
	public $html;
	public $formData;
	public $formElements;
	public $usermeta;
	public $submissions;
	public $attributes;
	public $multiwrapperFirstClone;
	public $multiWrapElementCount;
	public $minElForTabs;
	public $elementHtmlBuilder;
	public $nonWrappable;
	public $dom;
	public $formWrapper;

    public function __construct(){
		parent::__construct();
        
		$this->reset();
    }

	public function reset(){
		$this->elementValues			= [];
		$this->tagType					= '';
		$this->selectedValue			= '';
		$this->attributes				= ['class' => ''];
	}

	/**
	 * Checks if this is a clonable formstep, meaning a multi_start - multi-end group wrapped inside a formstep
	 */
	protected function isClonableFormStep(){
		$this->clonableFormStep	= false;

		if(
			(
				$this->nextElement->type == 'multi-start' && 
				$this->currentElement->type == 'formstep'
			) ||
			(
				$this->currentElement->type == 'multi-start' && 
				$this->prevElement->type == 'formstep'
			)
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
	 * Builds the array with default values for the current user
	 */
	function buildDefaultsArray(){
		//Only create one time
		if(!empty($this->defaultValues)){
			return;
		}

		if(empty($this->formName)){
			$this->formName			= $this->formData->name;
		}

		$this->defaultValues		= (array)$this->user->data;
		if($this->userId != $this->user->ID){
			$this->defaultValues		= (array)get_userdata($this->userId)->data;
		}
		
		//Change ID to userid because its a confusing name
		$this->defaultValues['user_id']	= $this->defaultValues['ID'];
		unset($this->defaultValues['ID']);
		
		foreach(['user_pass', 'user_activation_key', 'user_status', 'user_level'] as $field){
			unset($this->defaultValues[$field]);
		}

		// Add family meta
		$family		= new SIM\FAMILY\Family();
		$this->defaultValues['family_name']		= $family->getFamilyName($this->user->ID);
		$this->defaultValues['family_picture']	= $family->getFamilyMeta($this->user, 'family_picture');
		$this->defaultValues['family_partner']	= $family->getPartner($this->user);
		$this->defaultValues['weddingdate']		= $family->getWeddingDate($this->user);
		
		//get defaults from filters
		$this->defaultValues		= apply_filters('sim_add_form_defaults', $this->defaultValues, $this->userId, $this->formName);
		
		ksort($this->defaultValues);
				
		$this->defaultArrayValues	= [];

		foreach(SIM\getUserAccounts(false, false, [], [], [], true) as $user){
			$this->defaultArrayValues['all_users'][$user->ID] = $user->display_name;
		}

		/**
		 *  Add family member names
		 */ 
		// Our own details
		$familyNames				= [
			$this->user->ID => get_userdata($this->user->ID)->display_name
		];

		// Partner
		$partner	= $family->getPartner($this->user->ID, true);
		if($partner){
			$familyNames[$partner->ID]	= $partner->display_name;
		}

		// Siblings
		$siblings	= $family->getSiblings($this->user->ID);
		foreach($siblings as $sibling){
			$familyNames[$sibling]				= get_userdata($sibling)->display_name;
		}

		$familyNamesWithChildAge				= $familyNames;

		// Children
		$children		= $family->getChildren($this->user->ID);
		$childrenNames	= [];
		$childrenAges	= [];
		foreach($children as $child){
			$name								= get_userdata($child)->display_name;
			$birthDateString					= get_user_meta($child, 'birthday', true);

			$birthDate 							= new \DateTime($birthDateString);
			$currentDate 						= new \DateTime('today');

			// Calculate the difference between the two dates
			$interval 							= $currentDate->diff($birthDate);

			// Extract the number of years from the interval
			$age 								= $interval->y;

			$childrenNames[$child]				= $name;
			$childrenAges[$child]				= $age;
			$familyNamesWithChildAge[$child]	= "$name ($age)";
		}

		$familyNames												= $familyNames + $childrenNames;

		// Add everything to the defaults array
		$this->defaultArrayValues['children_names']					= $childrenNames;
		$this->defaultArrayValues['children_ages']					= $childrenAges;

		$this->defaultArrayValues['family_member_names']			= $familyNames;
		$this->defaultArrayValues['family_member_names_and_age']	= $familyNamesWithChildAge;

		$this->defaultArrayValues	= apply_filters('sim_add_form_multi_defaults', $this->defaultArrayValues, $this->userId, $this->formName);
		
		ksort($this->defaultArrayValues);
	}

	/**
	 * Gets the meta value from for an element
	 */
    function getMetaElementValue($elementNames){
		if(empty($this->formData->save_in_meta)){
			return '';
		}

		//only load usermeta once
		if(!is_array($this->usermeta)){
			//usermeta comes as arrays, only keep the first
			$this->usermeta	= [];
			foreach(get_user_meta($this->userId) as $key => $meta){
				$this->usermeta[$key]	= $meta[0];
			}
			$this->usermeta	= apply_filters('sim_forms_load_userdata', $this->usermeta, $this->userId);
		}

		$metaValue	= '';
	
		if(count($elementNames) == 1){
			//non array name
			$elementName			= $elementNames[0];

			if(isset($this->usermeta[$elementName])){
				$metaValue	= (array)maybe_unserialize($this->usermeta[$elementName]);
			}
		}elseif(!empty($this->usermeta[$elementNames[0]])){
			//an array of values, we only want a specific one
			$metaValue	= (array)maybe_unserialize($this->usermeta[$elementNames[0]]);
			
			unset($elementNames[0]);

			//loop over all the subkeys, and store the value until we have our final result
			$resultFound	= false;
			foreach($elementNames as $v){
				if(isset($metaValue[$v])){
					$metaValue 		= (array)$metaValue[$v];
					$resultFound	= true;
				}
			}

			// somehow it does not exist, return an empty value
			if(!$resultFound){
				$metaValue	= '';
			}
		}

		return $metaValue;
	}

	/**
	 * Gets the prefilled values of an element
	 *
	 * @param	object	$element		The element
	 *
	 * @return	array					The array of values
	 */
	function getElementValues($element){
		// Do not return default values when requesting the html over rest api
		if(defined('REST_REQUEST')){
			//return $values;
		}
		
		if(in_array($element->type, $this->nonInputs) && $element->type != 'datalist'){
			return [];
		}
		
		$values	= [
			'defaults'	=> [],
			'metavalue'	=> []
		];

		$this->buildDefaultsArray();

		//get the elementName, remove [] and split on remaining [
		$elementNames		= explode('[', trim($element->name, '[]'));

		/**
		 * Gets values from the element settings
		 */
		if(!empty($element->valuelist)){
			$elementValues	= explode("\n", $element->valuelist);

			// split in value text pairs if needed
			foreach($elementValues as $elementValue){
				$elementValue	= trim($elementValue);

				$exploded		= explode('|', $elementValue);

				if(count($exploded) > 1){
					$values['defaults'][$exploded[0]]				= $exploded[1];
				}else{
					$values['defaults'][strtolower($elementValue)]	= $elementValue;
				}
			}
		}
		
		//retrieve meta values if needed
		$values['metavalue']	= $this->getMetaElementValue($elementNames);
		
		//add default values
		if(empty($element->multiple) || in_array($element->type, ['select', 'checkbox', 'radio'])){
			$key							= $element->default_value;

			if(!empty($key)){
				if(isset($this->defaultValues[$key])){
					$values['defaults']		= array_merge($values['defaults'], (array)$this->defaultValues[$key]);
				}elseif(!in_array($key, $values['defaults'])) {
					$values['defaults'][]	= $key;
				}
			}
		}
		
		if(!empty($element->default_array_value)){
			$key						= $element->default_array_value;
			if(!empty($this->defaultArrayValues[$key]) && is_array($this->defaultArrayValues[$key])){
				$values['defaults']		= $this->defaultArrayValues[$key] + $values['defaults'];
			}
		}

		return $values;
	}

	/**
	 * Returns the html for an info element
	 */
	public function infoBoxHtml($text, $parent=''){
		$returnHtml	 	= false;
		$dom			= '';

		if(empty($parent)){
			$returnHtml	= true;
			$dom 	= new \DOMDocument();
			$parent	= $dom;
		}

		//remove any paragraphs
		$content 	= str_replace(['<p>', '</p>'], '', $text);
		$content 	= SIM\deslash($content);
		
		$node		= $this->addElement('div', $parent, ['class' => 'info-box'], '', $dom);
		$wrapper	= $this->addElement('div', $node, ['style' => "float:right"], '', $dom);
		$paragraph	= $this->addElement('p', $wrapper, ['class' => "info-icon"], '', $dom);

		$this->addElement(
			'img', 
			$paragraph, 
			[
				'draggable' => "false",
				'role'		=> "img",
				'class'		=> "emoji",
				'alt'		=> "ℹ",
				'src'		=> SIM\PICTURESURL.'/info.png',
				'loading'	=> "lazy"
			], 
			'', 
			$dom
		);

		$this->addElement('span', $node, ['class' => "info-text"], $content, $dom);

		if($returnHtml){
			return $dom->saveHtml($parent);
		}

		return $node;
	}

	/**
	 * Transforms a given string to hyperlinks or other formats
	 *
	 * @param 	string	$string			the string to convert
	 * @param	string	$elementName	The name of the element the string value belongs to
	 * @param	object	$submission		The submission this string belongs to
	 *
	 * @return	string					The transformed string
	 */
	public function transformInputData($string, $elementName, $submission){
		if(empty($string)){
			return $string;
		}
		
		//convert arrays to strings
		if(is_array($string)){
			$output = '';

			foreach($string as $sub){
				if(!empty($output)){
					$output .= "<br>";
				}
				$output .= $this->transformInputData($sub, $elementName, $submission);
			}
			return $output;
		}
		
		$output		= $string;
		//open mail programm on click on email
		if (str_contains($string, '@')) {
			$name		= '';
			if(isset($submission->name)){
				$name	= "Hi $submission->name,";
			}elseif(isset($submission->your_name)){
				$name	= "Hi $submission->your_name,";
			}elseif(isset($submission->first_name)){
				$name	= "Hi $submission->first_name,";
			}
			$output 	= "<a href='mailto:$string?subject=Regarding your {$this->formData->name} with id $submission->id&body={$name}'>$string</a>";
		//Convert link to clickable link if not already
		}elseif(
			(
				str_contains($string, 'https://')	||
				str_contains($string, 'http://')	||
				str_contains($string, '/form_uploads/')
			) &&
			!str_contains($string, 'href') &&
			!str_contains($string, '<img')
		) {
			$url	= str_replace(['https://', 'http://'], '', SITEURL);
			$string	= str_replace(str_replace('\\', '/', ABSPATH), '', $string);

			if(!str_contains($string, $url)){
				$string		= SITEURL."/$string";
			}

			$text	= "Link";

			if(getimagesize(SIM\urlToPath($string)) !== false) {
				$text	= "<img src='$string' alt='form_upload' style='width:150px;' loading='lazy'>";
			}
			$output		= "<a href='$string'>$text</a>";
		// Convert phonenumber to signal link
		}elseif(gettype($string) == 'string' && $string[0] == '+'){
			$numbers		= explode(" ", $string);
			$output			= '';
			$signalNumber	= '';

			$userIdKey	= false;
			if(isset($submission->user_id)){
				$userIdKey	= 'user_id';
			}elseif(isset($submission->userid)){
				$userIdKey	= 'userid';
			}

			if($userIdKey){
				$signalNumber	= get_user_meta($submission->$userIdKey, 'signal_number', true);
			}

			foreach($numbers as $number){
				if($userIdKey && $number == $signalNumber){
					$output	.= "<a href='https://signal.me/#p/$number'>$number</a><br>";
				}else{
					$output	.= "<a href='https://api.whatsapp.com/send?phone=$number&text=Regarding%20your%20submission%20of%20{$this->formData->form_name}%20with%20id%20$submission->id'>$number</a><br>";
				}
			}
		//display dates in a nice way
		}elseif(strtotime($string) && Date('Y', strtotime($string)) < 2200 && Date('Y', strtotime($string)) > 1900){
			$date		= date_parse($string);

			//Only transform if everything is there
			if($date['year'] && $date['month'] && $date['day']){
				$format		= get_option('date_format');

				//include time if needed
				if($date['hour'] && $date['minute']){
					$format	.= ' '.get_option('time_format');
				}

				$output		= date($format, strtotime($string));
			}
		}
	
		$output = apply_filters('sim_transform_formtable_data', $output, $elementName);
		return $output;
	}

	/**
	 * Adds an element and its attributes to a parent element
	 * 
	 * @param	string	$type			The element tagname
	 * @param	object	$parent			The parent node
	 * @param	array	$attributes		An array of attribute names and values
	 * @param	string	$textContent	The text content of the element
	 * @param	object	$dom			Domdocument to use, default empty for this->dom
	 * 
	 * @return	object					The created node
	 */
	public function addElement($type, $parent, $attributes=[], $textContent='', $dom=''){
		if(empty($dom)){
			$dom	= $this->dom;
		}

		try {
			$node = $dom->createElement($type, $textContent );
		} catch (\DOMException $e) {
			// Catch the specific DOMException
			SIM\printArray("Caught DOMException: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
		} catch (\Exception $e) {
			// Catch any other general exceptions if needed
			SIM\printArray( "Caught general Exception: " . $e->getMessage());
		}

		foreach($attributes as $attribute => $value){
			try{
				$node->setAttribute($attribute, $value);
			} catch (\DOMException $e) {
				// Catch the specific DOMException
				SIM\printArray("Caught DOMException for attribute '$attribute' " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
			} catch (\Exception $e) {
				// Catch any other general exceptions if needed
				SIM\printArray( "Caught general Exception: " . $e->getMessage());
			}
		}
		
		try{
			$parent->appendChild($node);
		} catch (\DOMException $e) {
			// Catch the specific DOMException
			SIM\printArray("Caught DOMException: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
		} catch (\Exception $e) {
			// Catch any other general exceptions if needed
			SIM\printArray( "Caught general Exception: " . $e->getMessage());
		}

		return $node;
	}
	
	/**
	 * Creates nodes from raw html and adds it to the parent
	 * 
	 * @param	string	$html		The html
	 * @param	object	$parent		The parent Node
	 * 
	 * @return	object				The created node
	 */
	public function addRawHtml($html, $parent){
		if(empty($html)){
			return false;
		}
		
		$html		= force_balance_tags($html);

		$dom 		= new \DOMDocument();
		$dom->loadHTML($html);

		// Import the node
		$node 		= $this->dom->importNode($dom->getElementsByTagName('body')->item(0)->childNodes->item(0), true);
		$node		= $parent->appendChild($node);

		return $node;
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
		if(get_class($this) == 'SIM\FORMS\DisplayFormResults'){
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
		if(!empty($this->element->add)){
			$addText	= $this->element->add;
		}

		$removeText	= '-';
		if(!empty($this->element->remove)){
			$removeText	= $this->element->remove;
		}
		
		$wrapper	= $this->addElement(
			'div',
			$parent,
			[
				'class'	=> 'button-wrapper',
				'style'	=> 'margin: auto; display:flex;'
			]
		);

		$this->addElement(
			'button',
			$wrapper,
			[
				'type'	=> 'button',
				'class'	=> 'add button',
				'style'	=> 'flex: 1;max-width: max-content;'
			],
			$addText
		);

		$this->addElement(
			'button',
			$wrapper,
			[
				'type'	=> 'button',
				'class'	=> 'remove button',
				'style'	=> 'flex: 1;max-width: max-content;'
			],
			$removeText
		);

		return $wrapper;
	}

	/**
	 * Adds an index to the name and id and adds the value of the current index
	 * 
	 * @param	int				$index			The current iteration index of the element
	 * @param	string|array	$value			The value to add
	 * @param	object			$node			The node to edit
	 */
	function changeNodeAttributes($index, $value, $node){
		// the node is already an input
		if(in_array($node->tagName, ['input', 'textarea', 'select'])){
			$nodes	= [$node];
		}else{
			$nodes	= $node->getElementsByTagName('*');
		}

		foreach($nodes as $node){
			if(!in_array($node->tagName, ['input', 'textarea', 'select'])){
				continue;
			}

			if($value === null ){
				$value = '';
			}
			
			/**
			 * Change the name
			 */
			// make sure we add the [] after the index if there was [] originally
			$name				= str_replace('[]', '', $this->element->name, $replaceCount);
			$indexString 		= "[$index]";
			if($replaceCount){
				$indexString	.= "[]";
			}

			// Add the index to the name
			$node->setAttribute('name', $name.$indexString);

			/**
			 * Change the id
			 */
			if(!empty($node->attributes['id']->value)){
				// Add the index to the id
				$node->setAttribute('id', $name."[$index]");
			}
						
			/**
			 * Change selected option
			 */
			if($this->element->type == 'select'){
				$options = $node->getElementsByTagName('option');
				
				foreach($options as $option){
					if($option->attributes['value'] == $value){
						$option->setAttribute('selected', 'selected');
					}else
						$option->removeAttribute('selected');
					}
			}
			
			/**
			 * Change selected checkbox
			 */
			elseif(in_array($this->element->type, ['radio', 'checkbox'])){
				$nodes = $node->getElementsByTagName($this->element->type);
				
				foreach($nodes as $node){
					if($node->attributes['value'] == $value){
						$node->setAttribute('checked', 'checked');
					}else
						$node->removeAttribute('checked');
					}
			}

			/**
			 *  Element value
			 */ 
			elseif($this->element->type == 'textarea'){
				$node->nodeValue = $value;
			}
			elseif(is_array($value)){
				$node->setAttribute('value', $value[$index]);
			}elseif(!empty($value)){
				$node->setAttribute('value', $value);
			}

			// Add the index to the label if we are not displaying it on seperate tabs
			if(
				$this->element->type == 'label' && 
				$this->multiWrapElementCount < $this->minElForTabs
			){
				$nr					 = $index + 1;
				$node->nodeValue	.= " $nr";
			}
		}
	}

	/**
	 * Get the previous values of a element
	 */
	function getPrevValues($returnArray=false){
		if(empty($this->submissions)){
			return;
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
		
		$options	= $this->attributes;
		unset($options['name']);
		unset($options['class']);

		$optionHtml	= '';
		foreach($options as $attribute => $value){
			$optionHtml .= " $attribute=$value";
		}
			
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
			$library	= $this->element->library;
			$metakey	= $name;
			$userId		= $this->userId;
		}
		//Load js
		$uploader 		= new SIM\FILEUPLOAD\FileUpload($userId, $metakey, $library, '', false, $this->usermeta[$metakey]);
		
		return $uploader->getUploadHtml($name, $targetDir, $this->element->multiple, $optionHtml, $this->element->editimage);
	}

	/**
	 * Determines the tag type of an element
	 */
	protected function getTagType(){
		if(in_array($this->element->type, ['formstep', 'info', 'div-start'])){
			$this->tagType		= "div";
		}elseif(in_array($this->element->type, array_merge($this->nonInputs, ['select', 'textarea']))){
			$this->tagType		= $this->element->type;
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
				$this->attributes['class']	.= " form-label";
				break;
			case 'button':
				$this->attributes['class']	.= " button";
				break;
			case 'formstep':
				$this->attributes['class']	.= " formstep step-hidden";
				break;
			default:
				$this->attributes['class']	.= " formfield-input";
		}
	}

	/**
	 * Gets the element value
	 */
	protected function getValue(){
		if(in_array($this->element->type, $this->nonInputs) || $this->requestedValue === false){
			return '';
		}

		// The requested value is a value of a previous submission, find previous submitted values if not provided to the function
		if(empty($this->requestedValue)){
			$this->requestedValue	= $this->getPrevValues();
		}

		// Do not continue
		if(
			$this->multiwrap || 
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
				!empty($this->elementValues['defaults']) && 	// there is a default value
				(
					empty($this->formData->save_in_meta) || 	// we are not saving to the user meta table
					empty($this->elementValues['metavalue'])	// or the metavalue is empty
				)
			){
				$this->selectedValue		= $this->elementValues['defaults'];
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

			// if there is a datalist attached to this element we should use the corresponding name
			if(in_array('list', array_keys($this->attributes))){
				$listElement	= $this->getElementByName($this->attributes['list']);

				if($listElement && !empty($this->defaultArrayValues[$listElement->default_array_value])){
					// Get the list values
					$values	= $this->defaultArrayValues[$listElement->default_array_value];

					if(!empty($values[$this->selectedValue])){
						$this->selectedValue	= $values[$this->selectedValue];
					}
				}
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

	/**
	 * Add the nodes needed for a multi text
	 */
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
					$this->submissions	= $this->submissions;
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
		$inputWrapper			= $this->addElement(
			'div', 
			$wrapper, 
			[
				'class' => 'multi-text-input-wrapper'
			]
		);

		$attributes				= $this->attributes;
		$attributes['type']		= 'text';
		$attributes['name']		= $elName;
		$attributes['class']	.= " datalistinput multiple";
		
		$this->addElement('input', $inputWrapper, $attributes);

		$this->addElement('button', $inputWrapper, ['type' => "button", 'class' => "small add-list-selection hidden"], 'add');

		return $wrapper;
	}

	/**
	 * Gets the html for elements with multiple instances
	 */
	protected function getMultiElementHtml($node){
		if(
			empty($this->element->multiple) ||
			in_array($this->element->type, ['file', 'image', 'text'])
		){
			return false;
		}
        
		$parent = $node->parentNode;
		
        if(
			empty($this->formData->save_in_meta) && 
			!empty($this->elementValues['defaults'])
		){
			$values		= array_values($this->elementValues['defaults']);
		}elseif(!empty($this->elementValues['metavalue'])){
			$values		= array_values($this->elementValues['metavalue']);
		}

		// check how many elements we should render
		$this->multiWrapValueCount	= max(1, count((array)$values));

		// Check if the previous node is wrapping this one
		if(
			!empty($this->prevElement)	&&
			!empty($this->prevElement->wrap) && 
			$this->prevElement != $this->element
		){	
			// we should clone the wrapping node
			$node 		= $node->parentNode;

			// The parent should also be updated
			$parent		= $node->parentNode;
		}

  		$multiWrapper 	= $this->addElement('div', $parent, ['class' => 'clone-divs-wrapper']);
		
		//create as many inputs as the maximum value found
		for ($index = 0; $index < $this->multiWrapValueCount; $index++) {
			$val	= '';
			if(!empty($values[$index])){
				$val	= $values[$index];
			}
			
			// Add the clone div
			$cloneDiv	= $this->addElement("div", $multiWrapper, ["class" => 'clone-div', "data-div-id" => $index]);
            
            // Add label to each entry if prev element is a label and wrapped with this one
            $parentNode = $cloneDiv;

			// Create the add and remove buttons
			$buttonWrapper	= $this->renderButtons($parentNode);
			
			// Clone the original input but not for index 0, then we use the original
			if($index === 0){
				$copy 	= $node;
			}else{
            	$copy	= $node->cloneNode(true);
			}

			// Update node values
            $this->changeNodeAttributes($index, $val, $copy);

			// Add the copy to the button wrapper before the buttons
			$buttonWrapper->prepend($copy);
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
                    'data-value' 	=> $key,
                    'value' 		=> $value
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
			$attributes['type'] 	= $this->element->type;
			$attributes['name'] 	= $this->element->name;
			$attributes['value']	= $key;

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

		return $checkboxWrapper;
	}

	/**
	 * Calculates the amount of clones needed
	 * and creates the wrapper for each clone
	 */
	public function multiwrapStart($parent){
		/**
		 * find the max number of values
		 */
		$i								= $this->element->priority;
		$this->multiWrapValueCount		= 1;
		$this->multiWrapElementCount	= 0;

		// Loop till we reach a multi-end element or the end of the form and count the values of each input
		while(!empty($this->formElements[$i]) && $this->formElements[$i]->type != 'multi-end'){
			$i++;

			$type	= $this->formElements[$i]->type;

			$this->multiWrapElementCount++;

			if(in_array($type, $this->nonInputs)){
				continue;
			}
			
			//Get the field values and count
			$values			= $this->getElementValues($this->formElements[$i]);

			if(empty($values) || !is_array(array_values($values)[0])){
				$valueCount	= 0;
			}else{
				$valueCount	= count(array_values($values)[0]);

				// Do not count the valuelist values
				if(!empty($this->formElements[$i]->valuelist)){
					$elementValues	= explode("\n", $this->formElements[$i]->valuelist);
					$valueCount		= $valueCount - count($elementValues);
				}
			}
			
			if($valueCount > $this->multiWrapValueCount){
				$this->multiWrapValueCount = $valueCount;
			}
		}
		
		// Get the name
		$name		= $this->element->name;

		$attributes	=  [
			'class' => 'clone-divs-wrapper', 
			'name' => $name
		];

		$multiWrapper = $this->addElement("div", $parent, $attributes);
		
		// We do not need to continue if this is a formstep
		if($this->clonableFormStep && $this->element->type == 'formstep'){
			return;
		}

		// Add the clone divs
		for ($index = 1; $index <= $this->multiWrapValueCount; $index++) {

			$attributes	= [
				'class'			=> "clone-div",
			];

			if($this->isClonableFormStep()){
				$attributes['class']	.= ' formstep step-hidden';
			}

			 /**
			 * Instead of showing the whole in a single view,
			 * we show each group of inputs in a seperate tab
			 * if the number of inputs in each group is bigger than
			 * the minimum
			 */
			elseif($this->multiWrapElementCount >= $this->minElForTabs ){
				$active = '';

				// First button is the active button
				if($index === 1){
					$active = 'active';
				}

				// Add the button to switch to a certain tab
				$this->addElement(
					'button',
					$multiWrapper,
					[
						'class' 		=> "button tablink $active",
						'type'			=> 'button',
						'id' 			=> "show-{$name}-$index",
						'data-target'	=> "$name-$index",
						'style' 		=> 'margin-right:4px;'
					],
					"{$this->element->nicename} $index"
				);

				// Extra class for each clone-div 
				$attributes['class']	.= ' tabcontent';
				$attributes['id']		 = "$name-$index";
			}

			// Add the clone-div
			$cloneDiv	= $this->addElement(
				'div', 
				$multiWrapper, 
				$attributes
			);

			if(empty($this->multiwrapperFirstClone)){
				$this->multiwrapperFirstClone	= $cloneDiv;
			}

			// add the add and remove buttons
			$this->renderButtons($cloneDiv);
		}

		return $multiWrapper;
	}

	/**
	 * Gets the html of form element
	 *
	 * @param	object			$element		The element data
	 * @param	object			$parent			The parent node to appeend to
	 * @param	string|false	$requestedValue	The value the element should have, false for no value, default empty
	 *
	 * @return	string| WP error		The html
	 */
	public function getElementHtml($element, $parent='', $requestedValue =''){
		$this->reset();

		$this->element				= $element;
		$this->requestedValue		= $requestedValue;
		$returnHtml 				= false;

		if(empty($parent)){
			// Create a new DOMDocument object
			$this->dom 	= new \DOMDocument();
			
			$parent 	= $this->dom;
			
   			$returnHtml = true;
		}

		/**
		 * Override filter, return a node to bypass this function
		 */
		$node 					= apply_filters('sim-form-element-html-short-circuit', null, $parent, $this);
		if(!empty($node)){
			if($returnHtml){
				return $this->dom->saveHtml();
			}
		
			return $node;
		}

		$this->elementValues		= $this->getElementValues($element);

		$this->getAttributes();

		$this->getNameAttribute();			

		$this->getElementId();
		
		$this->getClasses();

		$this->getValue();
		
		switch($this->element->type){
			case 'p':
				$content 	= wp_kses_post($this->element->text);
				$content	= SIM\deslash($content);

				$node		= $this->addElement('div', $parent, ['name'=>$this->element->name]);

				$this->addRawHtml($content, $node);
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
				$node = $this->multiwrapStart($parent);
				break;
			case 'div-end':
				break;
			case 'multi-end':
				$this->multiwrapperFirstClone = '';

				// nothing to do
				if($this->multiWrapElementCount < $this->minElForTabs){
					$this->renderButtons($parent);
				}
				
				$this->multiWrapElementCount = -1;
				break;
			case 'info':
				$node		= $this->infoBoxHtml($this->element->text, $parent);

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
				if($this->element->type == 'text' && $this->element->multiple){
					$node	= $this->getMultiTextInputHtml($parent);
				}else{

					$this->getTagType();
					
					$node 	= $this->addElement($this->tagType, $parent, $this->attributes);

					// do this after the creation of the element
					$this->getTagContent($node);
				}	
		}
		
		// Duplicate inputs with multiple values
		$this->getMultiElementHtml($node);

		/**
		 *  Process elements in a multi-wrap
		 */ 
		if(	
			$this->multiwrapperFirstClone != '' && 					// We have something to clone
			get_class($this) != "SIM\FORMS\FormBuilderForm" && 		// Do not clone on formbuilder pages
			$element->type != 'multi-start' &&						// skip this one
			!$element->wrap											// only clone when the wrapping is finished
		){
			$cloneDivs	= $this->multiwrapperFirstClone->parentNode->childNodes;

			// loop over all clone-divs
			$index	= -1;
			foreach ($cloneDivs as $cloneDiv) {
				$index++;

				$value	= '';
				if(!empty($this->elementValues)){
					if(!empty($this->elementValues['defaults']) && !empty(array_values($this->elementValues['defaults'])[$index])){
						$value	= array_values($this->elementValues['defaults'])[$index];
					}

					if(!empty($this->elementValues['metavalue']) && !empty(array_values($this->elementValues['metavalue'])[$index])){
						$value	= array_values($this->elementValues['metavalue'])[$index];
					}
				}

				// We do not have to proceed with the first clone-div, it already has all the elements, but we need to update its name, id and value
				if($cloneDiv->isSameNode($this->multiwrapperFirstClone)){
					// Update all attributes of the original
					$this->changeNodeAttributes($index, $value, $node);

					continue;
				}

				// If this is a clone-div
				if (str_contains($cloneDiv->attributes['class']->value, 'clone-div')){
					// Check which node to clone should be a direct child of the clone-div
					$base	= $node;
					while(!str_contains($base->parentNode->attributes['class']->value, 'clone-div')){
						$base	= $base->parentNode;
					}
					// Clone the just created node
					$copy	= $base->cloneNode(true);

					// Update all attributes
					$this->changeNodeAttributes($index, $value, $copy);

					$cloneDiv->appendChild($copy);
				}
			}
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

		$node = apply_filters('sim-form-element-html', $node, $this);
		
		if($returnHtml){
			return $this->dom->saveHtml();
		}
	
		return $node;
	}
}