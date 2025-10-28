<?php
namespace SIM\FORMS;
use SIM;

require( MODULE_PATH  . 'lib/vendor/autoload.php');

trait CreateJs{
    	/**
	 * Checks if the current form is a multi step form
	 *
	 * @return	bool	True if multistep false otherwise
	 */
	function isMultiStep(){
		if(empty($this->isMultiStepForm)){
			foreach($this->formElements as $el){
				if($el->type == 'formstep'){
					$this->isMultiStepForm	= true;
					return true;
				}
			}

			$this->isMultiStepForm	= false;
			return false;
		}else{
			return $this->isMultiStepForm;
		}
	}
    
    /**
     * Builds the js files for the current form
    */
    function createJs(){
        $this->formName     = $this->formData->name;
        $this->objectName   = strtolower(str_replace('-', '', $this->formName));

        $checks = [];
        $errors = [];

        //Loop over all elements to find any conditions
        foreach($this->formElements as $elementIndex => $element){
            $conditions	= maybe_unserialize($element->conditions);

            //if there are conditions
            if(!is_array($conditions)){
                continue;
            }

            //Loop over the conditions
            foreach($conditions as $conditionIndex => $condition){
                //if there are rules build some javascript
                if(is_array($condition['rules'])){
                    //Open the if statemenet
                    $lastRuleKey			= array_key_last($condition['rules']);
                    $fieldCheckIf 		    = "";
                    $conditionVariables	    = [];
                    $conditionIf			= '';
                    $checkForChange         = false;
                    
                    //Loop over the rules
                    foreach($condition['rules'] as $ruleIndex => $rule){
                        $fieldNumber1	= $ruleIndex * 2  + 1;
                        $fieldNumber2	= $fieldNumber1 + 1;
                        $equation		= str_replace(' value', '', $rule['equation']);
                        
                        //Get field names of the fields who's value we are checking
                        if(is_numeric($rule['conditional-field'])){
                            $conditionalElement		= $this->getElementById($rule['conditional-field']);
                        }else{
                            $conditionalElement     = false;
                        }
                        
                        if(!$conditionalElement){
                            $errors[]   = "Element $element->name has an invalid rule";

                            continue;
                        }

                        $conditionalFieldName		= $conditionalElement->name;
                        $propCompare                = 'elName';

                        if(str_contains($conditionalFieldName, '[]')){
                            $propCompare            = 'el.id';
                            $conditionalFieldName	= 'E'.$conditionalElement->id;
                        }elseif(in_array($conditionalElement->type,['radio','checkbox']) && !str_contains($conditionalFieldName, '[]')) {
                            $conditionalFieldName .= '[]';
                        }

                        $conditionalFieldType		= $conditionalElement->type;

                        if(is_numeric($rule['conditional-field-2'])){
                            $conditionalElement2		= $this->getElementById($rule['conditional-field-2']);
                            if(!$conditionalElement2){
                                $errors[]   = "Element $element->name has an invalid rule";
                                continue;
                            }

                            $conditionalField2Name	= $conditionalElement2->name;
                            
                            if(str_contains($conditionalField2Name, '[]')){
                                $propCompare            = 'el.id';
                                $conditionalField2Name	= 'E'.$conditionalElement2->id;
                            }elseif(in_array($conditionalElement2->type, ['radio','checkbox']) && !str_contains($conditionalField2Name, '[]')) {
                                $conditionalField2Name .= '[]';
                            }
                        }
                        
                        //Check if we are calculating a value based on two field values
                        if(($equation == '+' || $equation == '-') && is_numeric($rule['conditional-field-2']) && !empty($rule['equation-2'])){
                            $calc = true;
                        }else{
                            $calc = false;
                        }
                        
                        //make sure we do not include other fields in changed or click rules
                        if(in_array($equation, ['changed', 'clicked'])){
                            // do not add the same element name twice
                            if(!str_contains($fieldCheckIf, $conditionalFieldName)){
                                if(!empty($fieldCheckIf)){
                                    $fieldCheckIf   .= " || ";
                                }
                                $fieldCheckIf   .= "$propCompare == '$conditionalFieldName'";
                            }
                            $checkForChange = true;
                        }
                        
                        //Only allow or statements
                        if(!$checkForChange || (isset($condition['rules'][$ruleIndex-1]) && $condition['rules'][$ruleIndex-1]['combinator'] == 'OR')){
                            // do not add the same element name twice
                            if(!str_contains($fieldCheckIf, "$propCompare == '$conditionalFieldName'")){
                                //Write the if statement to check if the current clicked field belongs to this condition
                                if(!empty($fieldCheckIf)){
                                    $fieldCheckIf .= " || ";
                                }
                                $fieldCheckIf .= "$propCompare == '$conditionalFieldName'";
                            }
                            
                            // do not add the same element name twice
                            if(!str_contains($fieldCheckIf, "$propCompare == '$conditionalField2Name'")){
                                //If there is an extra field to check
                                if(is_numeric($rule['conditional-field-2'])){
                                    $fieldCheckIf .= " || $propCompare == '$conditionalField2Name'";
                                }
                            }
                        }
        
                        //We calculate the sum or difference of two field values if needed.
                        if($calc){
                            if($conditionalFieldType == 'date'){
                                //Convert date strings to date values then miliseconds to days
                                $conditionVariables[]  = "var calculated_value_$ruleIndex = (Date.parse(value_$fieldNumber1) $equation Date.parse(value_$fieldNumber2))/ (1000 * 60 * 60 * 24);";
                            }else{
                                $conditionVariables[]  = "var calculated_value_$ruleIndex = value_$fieldNumber1 $equation value_$fieldNumber2;";
                            }
                            $equation = $rule['equation-2'];

                            //compare with calculated value
                            $compareValue1 = "calculated_value_$ruleIndex";
                        }else{
                            //compare with a field value
                            $compareValue1 = "value_$fieldNumber1";
                        }
                            
                        //compare with the value of another field
                        if(str_contains($rule['equation'], 'value')){
                            $compareValue2 = "value_$fieldNumber2";
                        //compare with a number
                        }elseif(is_numeric($rule['conditional-value'])){
                            $compareValue2 = trim($rule['conditional-value']);
                        //compare with text
                        }else{
                            $compareValue2 = "'".strtolower(trim($rule['conditional-value']))."'";
                        }
                        
                        /*
                            NOW WE KNOW THAT THE CHANGED FIELD BELONGS TO THIS CONDITION
                            LETS CHECK IF ALL THE VALUES ARE MET AS WELL
                        */
                        if(!in_array($equation, ['changed', 'clicked', 'checked', '!checked', 'visible', 'invisible'])){
                            $conditionVariables[]      = "var value_$fieldNumber1 = this.get_field_value('$conditionalFieldName', form, true, $compareValue2, true);";
                            
                            if(is_numeric($rule['conditional-field-2'])){
                                $conditionVariables[]  = "var value_$fieldNumber2 = this.get_field_value('$conditionalField2Name', form, true, $compareValue2, true);";
                            }
                        }
                        
                        if(empty($equation)){
                            return new \WP_Error('forms', "$element->name has a rule without equation set. Please check");
                        }elseif($equation == 'checked'){
                            if(count($condition['rules'])==1){
                                $conditionIf .= "el.checked";
                            }else{
                                $conditionIf .= "form.querySelector('[name=\"$conditionalFieldName\"]').checked";
                            }
                        }elseif($equation == '!checked'){
                            if(count($condition['rules'])==1){
                                $conditionIf .= "!el.checked";
                            }else{
                                $conditionIf .= "!form.querySelector('[name=\"$conditionalFieldName\"]').checked";
                            }
                        }elseif($equation == 'visible'){
                            $conditionIf .= "form.querySelector(\"[name='$conditionalFieldName']\").closest('.hidden') == null";
                        }elseif($equation == 'invisible'){
                            $conditionIf .= "form.querySelector(\"[name='$conditionalFieldName']\").closest('.hidden') != null";
                        }elseif($equation != 'changed' && $equation != 'clicked'){
                            $conditionIf .= "$compareValue1 $equation $compareValue2";
                        }elseif($equation == 'changed' || $equation == 'clicked'){
                            $conditionIf .= "$propCompare == '$conditionalFieldName'";
                        }
                        
                        //If there is another rule, add || or &&
                        if(
                            $lastRuleKey != $ruleIndex                                                      &&  // there is a next rule
                            !empty($conditionIf) 																//there is already preceding code
                        ){
                            if(empty($rule['combinator'])){
                                $rule['combinator'] = 'AND';
                                SIM\printArray("Condition index $conditionIndex of $element->name is missing a combinator. I have set it to 'AND' for now");
                            }
                            if($rule['combinator'] == 'AND'){
                                $conditionIf .= " && ";
                            }else{
                                $conditionIf .= " || ";
                            }
                        }
                    }

                    $action                             = $condition['action'];

                    //store if statment
                    $fieldCheckIf = "if($fieldCheckIf){";
                    if(!isset($checks[$fieldCheckIf])){
                        $checks[$fieldCheckIf]                                            = [];
                        $checks[$fieldCheckIf]['variables']                               = [];
                        $checks[$fieldCheckIf]['actions']                                 = ['querystrings'=>[$action=>[]]];
                        $checks[$fieldCheckIf]['condition_ifs']                           = [];
                    }
                        
                    //no need for variable in case of a 'changed' condition
                    if(empty($conditionIf)){
                        $actionArray   =&  $checks[$fieldCheckIf]['actions'];
                    }else{
                        $conditionIf       = "if($conditionIf){";
                        if(empty($checks[$fieldCheckIf]['condition_ifs'][$conditionIf])){
                            $array              = [
                                'actions'       => ['querystrings'=>[$action=>[]]],
                                'variables'     => [],
                            ];
                            $checks[$fieldCheckIf]['condition_ifs'][$conditionIf]    = $array;
                        }

                        foreach($conditionVariables as $variable){
                            if(!in_array($variable, $checks[$fieldCheckIf]['condition_ifs'][$conditionIf]['variables'])){
                                $checks[$fieldCheckIf]['condition_ifs'][$conditionIf]['variables'][]    = $variable;
                            }
                        }
                        
                        $actionArray   =&  $checks[$fieldCheckIf]['condition_ifs'][$conditionIf]['actions'];
                    }
                    
                    //show, toggle or hide action for this field
                    if($action == 'show' || $action == 'hide' || $action == 'toggle'){
                        if($action == 'show'){
                            $action = 'remove';
                        }elseif($action == 'hide'){
                            $action = 'add';
                        }

                        if(!is_array($actionArray['querystrings'][$action])){
                            $actionArray['querystrings'][$action] = [];
                        }
                        
                        $name	= $element->name;

                        //formstep do not have an input-wrapper
                        if($element->type == 'formstep'){
                            $actionCode    = "form.querySelector('[name=\"$name\"]').classList.$action('hidden');";
                            if(!in_array($actionCode, $actionArray)){
                                $actionArray[] = $actionCode;
                            }
                        }else{
                            //only add if there is no wrapping element with the same condition.
                            $prevElement = $this->formElements[$elementIndex];
                            if(
                                !$prevElement->wrap ||                                           // this element is not wrapped in the previous one
                                !in_array($prevElement, $actionArray['querystrings'][$action])   // or the previous element is not in the action array
                            ){
                                $actionArray['querystrings'][$action][]    = $element;
                            }
                        }

                        foreach($conditions['copyto'] as $fieldIndex){
                            if(!is_numeric($fieldIndex)){
                                continue;
                            }

                            //find the element with the right id
                            $copyToElement	= $this->getElementById($fieldIndex);
                            if(!$copyToElement){
                                $errors[]   = "Element $element->name has an invalid rule";
                                continue;
                            }
                            
                            //formstep do not have an input-wrapper
                            if($copyToElement->type == 'formstep'){
                                $actionCode    = "form.querySelector('[name=\"$copyToElement->name\"]').classList.$action('hidden');";
                                if(!in_array($actionCode, $actionArray)){
                                    $actionArray[] = $actionCode;
                                }
                            }else{
                                $actionArray['querystrings'][$action][]    = $copyToElement;
                            }
                        }
                    //set property value
                    }elseif($action == 'property' || $action == 'value'){
                        //set the attribute value of one field to the value of another field
                        $selector		= $this->getSelector($element);
                        
                        //fixed prop value
                        if($action == 'value'){
                            $propertyName	                        = $condition['property-name1'];
                            if(isset($condition['action-value'])){
                                $varName   = '"'.do_shortcode($condition['action-value']).'"';
                            }
                        //retrieve value from another field
                        }else{
                            $propertyName	= $condition['property-name'];
                        
                            $copyfieldid	= $condition['property-value'];
                            
                            //find the element with the right id
                            $copyElement = $this->getElementById($copyfieldid);
                            if(!$copyElement){
                                $errors[]   = "Element $element->name has an invalid rule";
                                continue;
                            }

                            $copyFieldName	= $copyElement->name;
                            if(str_contains($copyFieldName, '[]')){
                                $propCompare            = 'el.id';
                                $copyFieldName	= 'E'.$copyElement->id;
                            }elseif(in_array($copyElement->type,['radio','checkbox']) && !str_contains($copyFieldName, '[]')) {
                                $copyFieldName .= '[]';
                            }
                            
                            $varName = str_replace(['[]', '[', ']'], ['', '_', ''], $copyFieldName);

                            $varCode = "let $varName = this.get_field_value('$copyFieldName', form);";
                            if(!in_array($varCode, $checks[$fieldCheckIf]['variables'])){
                                $checks[$fieldCheckIf]['variables'][] = $varCode;
                            }
                        }
                        
                        $addition       = '';
                        if(!empty($condition['addition'])){
                            $addition       = $condition['addition'];
                        }
                        if($propertyName == 'value'){
                            $actionCode    = "this.change_field_value('$selector', $varName, {$this->objectName}.processFields, form, $addition);";
                            if(!in_array($actionCode, $actionArray)){
                                $actionArray[] = $actionCode;
                            }
                        }else{
                            $actionCode    = "this.change_field_property('$selector', '$propertyName', $varName, {$this->objectName}.processFields, form, $addition);";
                            if(!in_array($actionCode, $actionArray)){
                                $actionArray[] = $actionCode;
                            }
                        }
                    }else{
                        SIM\printArray("formbuilder.php writing js: missing action: '$action' for condition $conditionIndex of field {$element->name}");
                    }
                }
            }
        }
        $js         = "";
        $minifiedJs = "";

        /*
        ** EVENT LISTENER JS
        */
        $newJs   = '';

        // Store all forms with this form-id in a variable
        $newJs  .= "\n\tforms =               document.querySelectorAll(`form[data-form-id=\"{$this->formData->id}\"]`);";

        // Shorter variable for the form functions
        $newJs  .= "\n\tget_field_value =       FormFunctions.getFieldValue;";
        $newJs  .= "\n\tchange_field_value =    FormFunctions.changeFieldValue;";
        $newJs  .= "\n\tchange_visibility =    FormFunctions.changeVisibility;";
        $newJs  .= "\n\tchange_field_property = FormFunctions.changeFieldProperty;";

        $newJs  .= "\n\tprevEl =               '';";
        $newJs  .= "\n\n\tlistener = (event) => {";
            $newJs  .= "\n\t\tlet el			= event.target;";
            $newJs  .= "\n\t\tlet form			= el.closest('form');";
            $newJs  .= "\n\t\tlet elName		= el.getAttribute('name');";
            $newJs  .= "\n\n\t\tif(elName == '' || elName == undefined){";
                $newJs  .= "\n\t\t\t//el is a nice select";
                $newJs  .= "\n\t\t\tif(el.closest('.nice-select-dropdown') != null && el.closest('.input-wrapper') != null){";
                    $newJs  .= "\n\t\t\t\t//find the select element connected to the nice-select";
                    $newJs  .= "\n\t\t\t\tel.closest('.input-wrapper').querySelectorAll('select').forEach(select=>{";
                        $newJs  .= "\n\t\t\t\t\tif(el.dataset.value == select.value){";
                            $newJs  .= "\n\t\t\t\t\t\tel	= select;";
                            $newJs  .= "\n\t\t\t\t\t\telName = select.name;";
                        $newJs  .= "\n\t\t\t\t\t}";
                    $newJs  .= "\n\t\t\t\t});";
                $newJs  .= "\n\t\t\t}else{";
                    $newJs  .= "\n\t\t\t\treturn;";
                $newJs  .= "\n\t\t\t}";
            $newJs  .= "\n\t\t}";

            $newJs  .= "\n\n\t\t//prevent duplicate event handling";
            $newJs  .= "\n\t\tif(el == this.prevEl){";
                $newJs  .= "\n\t\t\treturn;";
            $newJs  .= "\n\t\t}";
            
            $newJs  .= "\n\t\tthis.prevEl = el;";
            $newJs  .= "\n\n\t\t//clear event prevenion after 100 ms";
            $newJs  .= "\n\t\tsetTimeout(() => { this.prevEl = ''; }, 50);";

            $newJs  .= "\n\n\t\tif(elName == 'next-button'){";
                $newJs  .= "\n\t\t\tFormFunctions.nextPrev(1, form);";
            $newJs  .= "\n\t\t}else if(elName == 'previous-button'){";
                $newJs  .= "\n\t\t\tFormFunctions.nextPrev(-1, form);";
            $newJs  .= "\n\t\t}";

            $newJs  .= "\n\n\t\tthis.processFields(el);";
        $newJs  .= "\n\t};";

        $js         .= $newJs;
        $minifiedJs .= \Garfix\JsMinify\Minifier::minify($newJs, array('flaggedComments' => false));

        /*
        ** Initial actions JS
        */
        $tabJs  = '';

        // Show the first tab
        if($this->isMultiStep()){
            $tabJs.= "\n\t\t\t//show first tab";
            $tabJs.= "\n\t\t\t// Display the current tab// Current tab is set to be the first tab (0)";
            $tabJs.= "\n\t\t\tlet currentTab = 0; ";
            $tabJs.= "\n\t\t\t// Display the current tab";
            $tabJs.= "\n\t\t\tFormFunctions.showFormStep(currentTab, form); ";
        }

        // Prefill form with meta data
        if(!empty($this->formData->save_in_meta)){
            $tabJs.= "\n\t\t\tform.querySelectorAll(`select, input, textarea`).forEach(";
                $tabJs.= "\n\t\t\t\tel=>this.processFields(el)";
            $tabJs.= "\n\t\t\t);";
        }

        if(!empty($tabJs)){
            $tabJs  = "\n\n\t\tthis.forms.forEach(form => {
                $tabJs
            });";
        }

        // Process get variables in the url
        $newJs    = "\n
    init = () => {
        console.log('Dynamic $this->formName forms js loaded');

        window.addEventListener('click', this.listener);
        window.addEventListener('input', this.listener);
        
        FormFunctions.tidyMultiInputs();
        $tabJs
        // Loop over the elements who's value is given in the url and set the value;
        if(typeof(urlSearchParams) == 'undefined'){
            window.urlSearchParams = new URLSearchParams(window.location.search.replaceAll('&amp;', '&'));
        }
        Array.from(urlSearchParams).forEach(array => {
            document.querySelectorAll(`[name^='\${array[0]}' i]`).forEach(el => this.change_field_value(el, array[1], $this->objectName.processFields, el.closest('form')));
        });

        // Loop over the elements who have a default value and apply the logic;
        this.forms.forEach(form => {Array.from(form.elements).filter(element => {
            // Exclude elements without a name, as they are typically not submitted
            if (!element.name) {
                return false;
            }

            // Handle specific input types
            if (element.type === 'checkbox' || element.type === 'radio') {
                return element.checked;
            }

            // For other input types, check if the value is not empty
            return element.value !== '';
        }).forEach(el => this.processFields(el))});
    };";

        $js         .= $newJs;
        $minifiedJs .= \Garfix\JsMinify\Minifier::minify($newJs, array('flaggedComments' => false));

        /*
        ** MAIN JS
        */
        $newJs   = '';
        $newJs  .= "\n\n\tprocessFields = (el) => {";
            $newJs  .= "\n\t\tvar elName = el.getAttribute('name');\n";
            $newJs  .= "\n\t\tvar form	= el.closest('form');\n";
            foreach($checks as $if => $check){
                // empty if is not allowed
                if(str_contains($if, 'if()')){
                    continue;
                }
                
                $prevVar   = [];
                $newJs  .= "\t\t$if\n";
                foreach($check['variables'] as $variable){
                    //Only write same var definition once
                    $varParts  = explode(' = ', $variable);
                    if($prevVar[$varParts[0]] != $varParts[1]){
                        $newJs  .= "\t\t\t$variable\n";
                        $prevVar[$varParts[0]] = $varParts[1];
                    }
                }

                foreach($check['actions'] as $index=>$action){
                    if($index === 'querystrings'){
                        $newJs  .= $this->buildQuerySelector($action, "\t\t\t");
                    }else{
                        $newJs  .= "\t\t\t$action\n";
                    }
                }

                $prevVar   = [];
                foreach($check['condition_ifs'] as $if=>$prop){
                    foreach($prop['variables'] as $variable){
                        //Only write same var definition once
                        $varParts  = explode(' = ', $variable);
                        if(!isset($prevVar[$varParts[0]]) || $prevVar[$varParts[0]] != $varParts[1]){
                            $newJs  .= "\t\t\t$variable\n";
                            $prevVar[$varParts[0]] = $varParts[1];
                        }
                    }

                    if(!empty($prop['actions'])){
                        $newJs  .= "\n\t\t\t$if\n";
                        foreach($prop['actions'] as $index=>$action){
                            if($index === 'querystrings'){
                                $newJs  .= $this->buildQuerySelector($action, "\t\t\t\t");
                            }else{
                                $newJs  .= "\t\t\t\t$action\n";
                                if(str_contains($action, 'formstep')){
                                    $newJs  .= "\t\t\t\tFormFunctions.updateMultiStepControls(form);\n";
                                }
                            }
                        }
                        $newJs  .= "\t\t\t}\n";
                    }
                }
                $newJs  .= "\t\t}\n\n";
            }
        $newJs  .= "\t};";

        $js         .= $newJs;
        $minifiedJs .= \Garfix\JsMinify\Minifier::minify($newJs, array('flaggedComments' => false));  

        // Put is all in a namespace variable
        $className  = ucfirst($this->objectName);

        $js         = "class $className {".$js."\n};\n\nlet $this->objectName = new $className();\n\n$this->objectName.init();\n";
        $minifiedJs = "class $className {".$minifiedJs."\n};\n\nlet $this->objectName = new $className();\n\n$this->objectName.init();\n";

        /*
        ** EXTERNAL JS
        */
        $extraJs   = apply_filters('sim_form_extra_js', '', $this, false);
        if(!empty($extraJs)){
            if(empty($checks)){
                $js = $extraJs;
            }else{
                $js.= "\n\n";
                $js.= $extraJs;
            }
        }

        //Create js file
        file_put_contents($this->jsFileName.'.js', $js);

        //replace long strings for shorter ones
        $minifiedJs = str_replace(
            [
                "listener",
                "processFields",
                'value_',
                'elName',
                "\n",
                "get_field_value",
                "change_field_value",
                "change_visibility",
                "change_field_property",
                "init"
            ],
            [
                'q',
                'p',
                'v_',
                'n',
                '',
                'gF',
                'cF',
                'cV',
                'cP',
                'i'
            ],
            $minifiedJs
        );

        $extraJs   = apply_filters('sim_form_extra_js', '', $this, true);
        if(!empty($extraJs)){
            $minifiedJs .= "\n\n";
            $minifiedJs .= $extraJs;
        }

        // Create minified version
        file_put_contents($this->jsFileName.'.min.js', $minifiedJs);
        
        if(!empty($errors)){
            SIM\printArray($errors);
        }

        return $errors;
    }

    function buildQuerySelector($queryStrings, $prefix){
        $actionCode    = '';
        foreach($queryStrings as $action=>$elements){
            //multiple
            if(count($elements) > 1){
                $actionCode    .= "{$prefix}form.querySelectorAll(`";
                $last           = array_key_last($elements);
                foreach($elements as $key=>$element){
                    $actionCode     .= $this->getSelector($element);

                    if($key != $last){
                        $actionCode    .= ', ';
                    }
                }
                $actionCode    .= "`).forEach(el=>{\n";
                    //$actionCode    .= "{$prefix}\ttry{\n";
                        $actionCode    .= "{$prefix}\t\t//Make sure we only do each wrapper once by adding a temp class\n";
                        $actionCode    .= "{$prefix}\t\tif(!el.closest('.input-wrapper').matches('.action-processed')){\n";
                            $actionCode    .= "{$prefix}\t\t\tel.closest('.input-wrapper').classList.add('action-processed');\n";
                            //$actionCode    .= "{$prefix}\t\t\tel.closest('.input-wrapper').classList.$action('hidden');\n";
                            $actionCode    .= "{$prefix}\t\t\tthis.change_visibility('$action', el, {$this->objectName}.processFields);\n";
                        $actionCode    .= "{$prefix}\t\t}\n";
                    //$actionCode    .= "{$prefix}\t}catch(e){\n";
                        //$actionCode    .= "{$prefix}\t\tel.classList.$action('hidden');\n";
                    //$actionCode    .= "{$prefix}\t}\n";
                $actionCode    .= "{$prefix}});\n";
                $actionCode    .= "{$prefix}document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});\n";
            //just one
            }elseif(count($elements) == 1){
                $selector       = $this->getSelector($elements[0]);
                //$actionCode    .= "{$prefix}form.querySelector('$selector').closest('.input-wrapper').classList.$action('hidden');\n";
                $actionCode    .= "{$prefix}this.change_visibility('$action', form.querySelector('$selector').closest('.input-wrapper'), {$this->objectName}.processFields);\n";
            }
        }

        return $actionCode;
    }

    function getSelector($element){
        $queryById          = false;
        $name				= $element->name;

        if(str_contains($name, '[]')){
            $queryById          = true;
        }elseif(in_array($element->type, ['radio', 'checkbox']) && !str_contains($name, '[]')) {
            $name .= '[]';
        }

        if(in_array($element->type, ['file', 'image'])){
            $name .= '_files[]';
        }

        if($queryById){
            return "[id^='E$element->id']";
        }elseif(empty($element->multiple)){
            return "[name=\"$name\"]";
        }else{
            // name is followed by an index [0]
            return "[name^=\"{$name}[\"]";
        }
    }
}