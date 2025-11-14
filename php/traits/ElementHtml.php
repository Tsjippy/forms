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
		
		//get defaults from filters
		$this->defaultValues		= apply_filters('sim_add_form_defaults', $this->defaultValues, $this->userId, $this->formName);
		
		ksort($this->defaultValues);
				
		$this->defaultArrayValues	= [];

		foreach(SIM\getUserAccounts(false, false, [], [], [], true) as $user){
			$this->defaultArrayValues['all_users'][$user->ID] = $user->display_name;
		}

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
}

