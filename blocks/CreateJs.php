<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Gets all the inner blocks of a form block
 * 
 * @param   array   $block
 * 
 * @return  array   blockids
 */
function getBlocks($block){
    $blocks   = [];

    $blocks[$block['attrs']['blockId'] ?? ''] = $block;

    foreach($block['innerBlocks'] as $innerBlock){
        $blocks   = array_merge($blocks, getBlocks($innerBlock));
    }

    return $blocks;
}

/**
 * Creates dynamic js when a post has been saved.
 *
 * @param int     $postId Post ID.
 */
function processFormBlocks($postId){
    $post   = get_post($postId);
    /**
     * Get all conditions who build the js with
     */

    // get all blocks of the post
    $blocks     = parse_blocks($post->post_content);

    foreach($blocks as $block){
        if($block['blockName'] == "tsjippy-forms/formbuilder"){
            buildJs($block, $post);
        }
    }
}

function getInputType($block){
    if($block['name'] == "tsjippy-forms/input"){
        return ($block['attr']['type'] ?? '');
    }else{
        return explode('/', $block['name'])[1];
    }
}

function getBlockId($block){
    return ($block['attr']['blockId'] ?? '');
}


/**
 * Builds the js code for the show, hide and toggle actions based on the given query strings. It also makes sure that we only apply the action to the most outer wrapper element to prevent conflicts with nested conditions. The function returns the js code as a string.
 * @param array $queryStrings The query strings for the show, hide and toggle actions
 * @param string $prefix The prefix to add to each line of the js code, for example to add extra tabs for nested if statements
 */
function buildQuerySelector($queryStrings, $prefix, $objectName)
{
    $actionCode    = '';
    foreach ($queryStrings as $action => $elements) {
        //multiple
        if (count($elements) > 1) {
            $actionCode    .= "{$prefix}form.querySelectorAll(`";
            $last           = array_key_last($elements);
            foreach ($elements as $key => $element) {
                $actionCode     .= getSelector($element);

                if ($key != $last) {
                    $actionCode    .= ', ';
                }
            }
            $actionCode    .= "`).forEach (el=>{\n";
            //$actionCode    .= "{$prefix}\ttry{\n";
            $actionCode    .= "{$prefix}\t\t//Make sure we only do each wrapper once by adding a temp class\n";
            $actionCode    .= "{$prefix}\t\tif (!el.closest('.input-wrapper').matches('.action-processed')) {\n";
            $actionCode    .= "{$prefix}\t\t\tel.closest('.input-wrapper').classList.add('action-processed');\n";
            //$actionCode    .= "{$prefix}\t\t\tel.closest('.input-wrapper').classList.$action('hidden');\n";
            $actionCode    .= "{$prefix}\t\t\tthis.change_visibility('$action', el, {$objectName}.processFields);\n";
            $actionCode    .= "{$prefix}\t\t}\n";
            //$actionCode    .= "{$prefix}\t}catch(e) {\n";
            //$actionCode    .= "{$prefix}\t\tel.classList.$action('hidden');\n";
            //$actionCode    .= "{$prefix}\t}\n";
            $actionCode    .= "{$prefix}});\n";
            $actionCode    .= "{$prefix}document.querySelectorAll('.action-processed').forEach (el=>{el.classList.remove('action-processed')});\n";
            //just one
        } elseif (count($elements) == 1) {
            $selector       = getSelector($elements[0]);
            //$actionCode    .= "{$prefix}form.querySelector('$selector').closest('.input-wrapper').classList.$action('hidden');\n";
            $actionCode    .= "{$prefix}this.change_visibility('$action', form.querySelector('$selector').closest('.input-wrapper'), {$objectName}.processFields);\n";
        }
    }

    return $actionCode;
}

/**
 * Returns the css selector for the given element based on the element type and slug. For example, if the element is a radio button with the slug "gender", the function will return "[name='gender[]']" to select all radio buttons with
 * the name "* gender[]" . If the element is a file input with the slug "resume", the function will return "[name='resume_files[]']" to select the file input. If the element is a checkbox with the slug "interests[]", the function will return "[id^='E123']" (assuming the element id is 123) to select all checkboxes with an id that starts with "E123" . The function takes into account different input types and naming conventions to generate the correct selector for each element.
 *
 * @param array  $block The block for which to generate the selector
 * @param string $value Specific value in case there are multiple for the same block
 *
 * @return string The css selector for the given element
 */
function getSelector($block, $value='')
{
    $selector   = "[data-blockId='"  . getBlockId($block) . "']";

    if(isset(['radio', 'checkbox'][getInputType($block)])){
        if(empty($value)){
            $selector   .= ":checked";
        }else{
            $selector   .= "[value='$value']"; 
        }
    }
}

/**
 * Checks if the form has form steps
 * 
 * @param   array   $innerBlocks
 */
function hasFormStep($innerBlocks){
    foreach($innerBlocks as $block){
        if(getInputType($block) == 'formstep'){
            return true;
        }
    }

    return false;
}

function buildJs($block, $post){
    $formName   = $block['attr']['name'] ?? '';
    if(empty( $formName )){
        return;
    }

    // Get all conditions for this post
    $forms       = new Forms();
    $conditions  = $forms->getAllBlockConditions($post->ID);
    $innerBlocks = getBlocks($block);

    // Object name
    $objectName  = strtolower(str_replace('-', '', $formName));

    $checks      = [];
    $errors      = [];
    $propCompare = 'dataset.blockId'; 

    /**
     * Loop over the conditions
     */
    foreach ($conditions as $conditionIndex => $condition) {
        //if there are rules build some javascript
        if (empty($condition['rules'] ?? []) ) {
            continue;
        }

        //Open the if statemenet
        $lastRuleKey        = array_key_last($condition['rules']);
        $fieldCheckIf       = "";
        $conditionVariables = [];
        $conditionIf        = '';
        $checkForChange     = false;

        /**
         * Loop over the rules and build the conditionIf string
         */
        foreach ($condition['rules'] as $ruleIndex => $rule) {
            $fieldNumber1             = $ruleIndex * 2  + 1;
            $fieldNumber2             = $fieldNumber1 + 1;
            $equation                 = str_replace(' value', '', $rule['equation'] ?? '');
            $conditionalBlockId       = $rule['conditional-field'] ?? '';
            $conditionalTwoBlockId    = $rule['conditional-field-2'] ?? '';

            if (!$conditionalBlockId) {
                $errors[]   = "Block {$rule['target']} has no conditional block";

                continue;
            }

            // true for checkboxes and other blocks with multiple inputs with the same blockId
            $shouldIncludeValue     = false;

            // checkboxes have all the same block id, so to select a specofic one we should query the id
            if (in_array($innerBlocks[$conditionalBlockId]['attr']['type'], ['radio', 'checkbox'])) {
                $shouldIncludeValue = true;
            }

            if (empty($conditionalTwoBlockId)) {
                $errors[]   = "Block {$rule['target']} has no conditional block";
                continue;
            }

            //Check if we are calculating a value based on two field values
            if (($equation == '+' || $equation == '-') && !empty($conditionalTwoBlockId) && !empty($rule['equation-2'])) {
                $calc = true;
            } else {
                $calc = false;
            }

            //make sure we do not include other fields in changed or click rules
            if (isset(['changed' => 1, 'clicked' => 1][$equation])) {
                // do not add the same element name twice
                if (!str_contains($fieldCheckIf, $conditionalBlockId)) {
                    if (!empty($fieldCheckIf)) {
                        $fieldCheckIf   .= " || ";
                    }
                    $fieldCheckIf   .= "$propCompare == '$conditionalBlockId'";
                }
                $checkForChange = true;
            }

            //Only allow or statements
            if (!$checkForChange || (isset($condition['rules'][$ruleIndex - 1]) && $condition['rules'][$ruleIndex - 1]['combinator'] == 'OR')) {
                // do not add the same element name twice
                if (!str_contains($fieldCheckIf, "$propCompare == '$conditionalBlockId'")) {
                    //Write the if statement to check if the current clicked field belongs to this condition
                    if (!empty($fieldCheckIf)) {
                        $fieldCheckIf .= " || ";
                    }
                    $fieldCheckIf .= "$propCompare == '$conditionalBlockId'";
                }

                // do not add the same element name twice
                if (!str_contains($fieldCheckIf, "$propCompare == '$conditionalTwoBlockId'")) {
                    //If there is an extra field to check
                    if (!empty($rule['conditional-field-2'] ?? '')) {
                        $fieldCheckIf .= " || $propCompare == '$conditionalTwoBlockId'";
                    }
                }
            }

            //We calculate the sum or difference of two field values if needed.
            if ($calc) {
                if (getInputType($innerBlocks[$conditionalBlockId]) == 'date') {
                    //Convert date strings to date values then miliseconds to days
                    $conditionVariables[]  = "var calculated_value_$ruleIndex = (Date.parse(value_$fieldNumber1) $equation Date.parse(value_$fieldNumber2))/ (1000 * 60 * 60 * 24);";
                } else {
                    $conditionVariables[]  = "var calculated_value_$ruleIndex = value_$fieldNumber1 $equation value_$fieldNumber2;";
                }
                $equation = $rule['equation-2'];

                //compare with calculated value
                $compareValue1 = "calculated_value_$ruleIndex";
            } else {
                //compare with a field value
                $compareValue1 = "value_$fieldNumber1";
            }

            //compare with the value of another field
            if (str_contains($rule['equation'], 'value')) {
                $compareValue2 = "value_$fieldNumber2";
                //compare with a number
            } elseif (is_numeric($rule['conditional-value'] ?? '')) {
                $compareValue2 = trim($rule['conditional-value'] ?? '');
                //compare with text
            } else {
                $compareValue2 = "'" . strtolower(trim($rule['conditional-value'] ?? '')) . "'";
            }

            /*
                NOW WE KNOW THAT THE CHANGED FIELD BELONGS TO THIS CONDITION
                LETS CHECK IF ALL THE VALUES ARE MET AS WELL
            */
            if (!isset(['changed' => 1, 'clicked' => 1, 'checked' => 1, '!checked' => 1, 'visible' => 1, 'invisible' => 1][$equation])) {
                $conditionVariables[]      = "var value_$fieldNumber1 = this.get_field_value('$conditionalBlockId', form, true, $compareValue2, true);";

                if (is_numeric($rule['conditional-field-2'] ?? '')) {
                    $conditionVariables[]  = "var value_$fieldNumber2 = this.get_field_value('$conditionalTwoBlockId', form, true, $compareValue2, true);";
                }
            }

            if (empty($equation)) {
                return new \WP_Error('forms', "$condition->target has a rule without equation set. Please check");
            } elseif ($equation == 'checked') {
                if (count($condition['rules']) == 1) {
                    $conditionIf .= "el.checked";
                } else {
                    $conditionIf .= "form.querySelector('[name=\"$conditionalBlockId\"]').checked";
                }
            } elseif ($equation == '!checked') {
                if (count($condition['rules']) == 1) {
                    $conditionIf .= "!el.checked";
                } else {
                    $conditionIf .= "!form.querySelector('[name=\"$conditionalBlockId\"]').checked";
                }
            } elseif ($equation == 'visible') {
                $conditionIf .= "form.querySelector(\"[name='$conditionalBlockId']\").closest('.hidden') == null";
            } elseif ($equation == 'invisible') {
                $conditionIf .= "form.querySelector(\"[name='$conditionalBlockId']\").closest('.hidden') != null";
            } elseif ($equation != 'changed' && $equation != 'clicked') {
                $conditionIf .= "$compareValue1 $equation $compareValue2";
            } elseif ($equation == 'changed' || $equation == 'clicked') {
                $conditionIf .= "$propCompare == '$conditionalBlockId'";
            }

            //If there is another rule, add || or &&
            if (
                $lastRuleKey != $ruleIndex                                                      &&  // there is a next rule
                !empty($conditionIf)                                                                 //there is already preceding code
            ) {
                if (empty($rule['combinator'])) {
                    $rule['combinator'] = 'AND';
                    TSJIPPY\printArray("Condition index $conditionIndex of $condition->target is missing a combinator. I have set it to 'AND' for now");
                }
                if ($rule['combinator'] == 'AND') {
                    $conditionIf .= " && ";
                } else {
                    $conditionIf .= " || ";
                }
            }
        }

        /**
         * Loop over all actions to fill the action array
         */
        foreach($condition['actions'] as $actionIndex => $action){
            //store if statement
            $fieldCheckIf = "if ($fieldCheckIf) {";
            if (!isset($checks[$fieldCheckIf])) {
                $checks[$fieldCheckIf]                  = [];
                $checks[$fieldCheckIf]['variables']     = [];
                $checks[$fieldCheckIf]['actions']       = ['querystrings' => [$action => []]];
                $checks[$fieldCheckIf]['condition_ifs'] = [];
            }

            //no need for variable in case of a 'changed' condition
            if (empty($conditionIf)) {
                $actionArray = &$checks[$fieldCheckIf]['actions'];
            } else {
                $conditionIf = "if ($conditionIf) {";
                if (empty($checks[$fieldCheckIf]['condition_ifs'][$conditionIf])) {
                    $array              = [
                        'actions'       => ['querystrings' => [$action => []]],
                        'variables'     => [],
                    ];
                    $checks[$fieldCheckIf]['condition_ifs'][$conditionIf]    = $array;
                }

                foreach ($conditionVariables as $variable) {
                    if (!in_array($variable, $checks[$fieldCheckIf]['condition_ifs'][$conditionIf]['variables'])) {
                        $checks[$fieldCheckIf]['condition_ifs'][$conditionIf]['variables'][]    = $variable;
                    }
                }

                $actionArray   = &$checks[$fieldCheckIf]['condition_ifs'][$conditionIf]['actions'];
            }

            /**
             * show, toggle or hide action for this field
             */
            if (isset(['show' => 1, 'hide' => 1, 'toggle' => 1][$action])) {
                if ($action == 'show') {
                    $action = 'remove';
                } elseif ($action == 'hide') {
                    $action = 'add';
                }

                if (empty($actionArray['querystrings'][$action]) || !is_array($actionArray['querystrings'][$action])) {
                    $actionArray['querystrings'][$action] = [];
                }

                /**
                 * Hide, show or toggle a formstep
                 */
                if (getInputType($innerBlocks[$condition->target]) == 'formstep') {
                    $actionCode    = "form.querySelector(`[data-blockId='$condition->target']`).classList.$action('hidden');";
                    if (!isset($actionArray[$actionCode])) {
                        $actionArray[$actionCode] = $actionCode;
                    }
                } 
                
                /**
                 * Hide, show or toggle other blocks
                 */
                else {
                    $actionArray['querystrings'][$action][]    = $condition->target;
                }

                /** 
                 * Apply the same rule to other blocks
                 */
                foreach ($conditions['copyto'] ?? [] as $fieldIndex) {
                    //find the element with the right id
                    $copyToBlock    = $innerBlocks($fieldIndex);
                    if (!$copyToBlock) {
                        $errors[]   = "Element $condition->target has an invalid rule, apply to other block not found";
                        continue;
                    }

                    // Apply to a formstep
                    if (getInputType($innerBlocks[$copyToBlock]) == 'formstep') {
                        $blockId    = getBlockId($copyToBlock);
                        $actionCode = "form.querySelector(`[data-blockId='$blockId']`).classList.$action('hidden');";
                        if (!isset($actionArray[$actionCode])) {
                            $actionArray[$actionCode] = $actionCode;
                        }
                    }
                    
                    // Apply to other blocks
                    else {
                        $actionArray['querystrings'][$action][] = $copyToBlock;
                    }
                }
            } 
            
            //set property value
            elseif ($action == 'set-property') {
                $propertyName     = $condition['property-name'];

                $newPropertyValue = $condition['property-value'];

                $addition   = '';
                $selector   = getSelector($block);

                /**
                 * Check if the new value is a blockId
                 */
                if(str_contains($newPropertyValue, "the-value-of-")){
                    $targetBlockId  = str_replace("the-value-of-", '', $newPropertyValue);

                    $valueSelector   = getSelector($innerBlocks[$targetBlockId]);

                    // We should not hardcode the value but make it a function
                    $newPropertyValue   = "this.get_field_value($valueSelector, form)";

                    // We should add a number to the value
                    if (!empty($action['addition'])) {
                        $addition       = $action['addition'];
                    }
                }

                /* 
                // NOt sure what this does
                $varCode = "let $varName = this.get_field_value('$copyFieldName', form);";
                if (!in_array($varCode, $checks[$fieldCheckIf]['variables'])) {
                    $checks[$fieldCheckIf]['variables'][] = $varCode;
                } */

                if($propertyName == 'value'){
                    $actionCode    = "this.change_field_value('$selector', $newPropertyValue, {$objectName}.processFields, form, $addition);";
                    if (!isset($actionArray[$actionCode])) {
                        $actionArray[$actionCode] = $actionCode;
                    }
                } else {
                    $actionCode    = "this.change_field_property('$selector', '$propertyName', $newPropertyValue, {$objectName}.processFields, form, $addition);";
                    if (!isset($actionArray[$actionCode])) {
                        $actionArray[$actionCode] = $actionCode;
                    }
                }
            } 
            
            else {
                TSJIPPY\printArray("formbuilder.php writing js: missing action: '$action' for condition $conditionIndex of field {$condition->target}");
            }
        }
    }
    
    /**
     * BUILD THE JS
     */
    $js         = "";
    $minifiedJs = "";

    /*
    ** EVENT LISTENER JS
    */
    $newJs   = '';

    // Store all forms with this form-id in a variable
    $newJs  .= "\n\tforms =                 document.querySelectorAll(`form[data-formName=\"{$formName}\"]`);";

    // Shorter variable for the form functions
    $newJs  .= "\n\tget_field_value =       FormFunctions.getFieldValue;";
    $newJs  .= "\n\tchange_field_value =    FormFunctions.changeFieldValue;";
    $newJs  .= "\n\tchange_visibility =     FormFunctions.changeVisibility;";
    $newJs  .= "\n\tchange_field_property = FormFunctions.changeFieldProperty;";

    $newJs  .= "\n\tprevEl =               '';";
    $newJs  .= "\n\n\tlistener = (event) => {";
    $newJs  .= "\n\t\tlet el            = event.target;";
    $newJs  .= "\n\t\tlet form            = el.closest('form');";
    $newJs  .= "\n\t\tlet elName        = el.getAttribute('name');";
    $newJs  .= "\n\n\t\tif (elName == '' || elName == undefined) {";
    $newJs  .= "\n\t\t\t//el is a nice select";
    $newJs  .= "\n\t\t\tif (el.closest('.nice-select-dropdown') != null && el.closest('.input-wrapper') != null) {";
    $newJs  .= "\n\t\t\t\t//find the select element connected to the nice-select";
    $newJs  .= "\n\t\t\t\tel.closest('.input-wrapper').querySelectorAll('select').forEach (select=>{";
    $newJs  .= "\n\t\t\t\t\tif (el.dataset.value == select.value) {";
    $newJs  .= "\n\t\t\t\t\t\tel    = select;";
    $newJs  .= "\n\t\t\t\t\t\telName = select.name;";
    $newJs  .= "\n\t\t\t\t\t}";
    $newJs  .= "\n\t\t\t\t});";
    $newJs  .= "\n\t\t\t}else{";
    $newJs  .= "\n\t\t\t\treturn;";
    $newJs  .= "\n\t\t\t}";
    $newJs  .= "\n\t\t}";

    $newJs  .= "\n\n\t\t//prevent duplicate event handling";
    $newJs  .= "\n\t\tif (el == this.prevEl) {";
    $newJs  .= "\n\t\t\treturn;";
    $newJs  .= "\n\t\t}";

    $newJs  .= "\n\t\tthis.prevEl = el;";
    $newJs  .= "\n\n\t\t//clear event prevenion after 100 ms";
    $newJs  .= "\n\t\tsetTimeout(() => { this.prevEl = ''; }, 50);";

    $newJs  .= "\n\n\t\tif (elName == 'next-button') {";
    $newJs  .= "\n\t\t\tFormFunctions.nextPrev(1, form);";
    $newJs  .= "\n\t\t}else if (elName == 'previous-button') {";
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
    if (hasFormStep($innerBlocks)) {
        $tabJs .= "\n\t\t\t//show first tab";
        $tabJs .= "\n\t\t\t// Display the current tab// Current tab is set to be the first tab (0)";
        $tabJs .= "\n\t\t\tlet currentTab = 0; ";
        $tabJs .= "\n\t\t\t// Display the current tab";
        $tabJs .= "\n\t\t\tFormFunctions.showFormStep(currentTab, form); ";
    }

    // Prefill form with meta data
    $tabJs .= "\n\t\t\tform.querySelectorAll(`select, input, textarea`).forEach (";
    $tabJs .= "\n\t\t\t\tel=>this.processFields(el)";
    $tabJs .= "\n\t\t\t);";

    if (!empty($tabJs)) {
        $tabJs  = "\n\n\t\tthis.forms.forEach (form => {\n\t\t\t$tabJs\n\t\t});";
    }

    // Process get variables in the url
    $newJs    = "\n
init = () => {
    console.log('Dynamic {$objectName} forms js loaded');

    document.addEventListener('click', this.listener);
    document.addEventListener('input', this.listener);

    FormFunctions.tidyMultiInputs();
    $tabJs
    // Loop over the elements who's value is given in the url and set the value;
    if (typeof(urlSearchParams) == 'undefined') {
        window.urlSearchParams = new URLSearchParams(window.location.search.replaceAll('&amp;', '&'));
    }

    // Prefill the form with values from the url
    Array.from(urlSearchParams).forEach (array => {
        document.querySelectorAll(`[name^='\${array[0]}' i]`).forEach (el => this.change_field_value(el, array[1], $objectName.processFields, el.closest('form')));
    });

    // Loop over the elements who have a default value and apply the logic;
    this.forms.forEach (form => {Array.from(form.elements).filter(element => {
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
    }).forEach (el => this.processFields(el))});
};";

    $js         .= $newJs;
    $minifiedJs .= \Garfix\JsMinify\Minifier::minify($newJs, array('flaggedComments' => false));

    /*
    ** MAIN JS
    */
    $newJs   = '';
    $newJs  .= "\n\n\tprocessFields = (el) => {";
    $newJs  .= "\n\t\tvar elName    = el.getAttribute('name');\n";
    $newJs  .= "\n\t\tvar form      = el.closest('form');\n";
    foreach ($checks as $if => $check) {
        // empty if is not allowed
        if (str_contains($if, 'if ()')) {
            continue;
        }

        $prevVar   = [];
        $newJs  .= "\t\t$if\n";
        foreach ($check['variables'] as $variable) {
            //Only write same var definition once
            $varParts  = explode(' = ', $variable);
            if (!isset($prevVar[$varParts[0]]) || $prevVar[$varParts[0]] != $varParts[1]) {
                $newJs  .= "\t\t\t$variable\n";
                $prevVar[$varParts[0]] = $varParts[1];
            }
        }

        foreach ($check['actions'] as $index => $action) {
            if ($index === 'querystrings') {
                $newJs  .= buildQuerySelector($action, "\t\t\t", $objectName);
            } else {
                $newJs  .= "\t\t\t$action\n";
            }
        }

        $prevVar   = [];
        foreach ($check['condition_ifs'] as $i => $prop) {
            foreach ($prop['variables'] as $variable) {
                //Only write same var definition once
                $varParts  = explode(' = ', $variable);
                if (!isset($prevVar[$varParts[0]]) || $prevVar[$varParts[0]] != $varParts[1]) {
                    $newJs  .= "\t\t\t$variable\n";
                    $prevVar[$varParts[0]] = $varParts[1];
                }
            }

            if (!empty($prop['actions'])) {
                $newJs  .= "\n\t\t\t$i\n";
                foreach ($prop['actions'] as $index => $action) {
                    if ($index === 'querystrings') {
                        $newJs  .= buildQuerySelector($action, "\t\t\t\t", $objectName);
                    } else {
                        $newJs  .= "\t\t\t\t$action\n";
                        if (str_contains((string) $action, 'formstep')) {
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
    $className  = ucfirst($objectName);

    $js         = "class $className {" . $js . "\n};\n\nlet $objectName = new $className();\n\n$objectName.init();\n";
    $minifiedJs = "class $className {" . $minifiedJs . "\n};\n\nlet $objectName = new $className();\n\n$objectName.init();\n";

    /**
     * EXTERNAL JS Filter
     * Allows to add extra js to the form, for example for custom conditions or actions that are not supported by the form builder yet. The js will be added to both the normal and minified version of the js file. The filter passes the current form object as a parameter, so you can check for the form slug or id to only add the js to specific forms.
     * @param string $extraJs The extra js code to add to the form
     * @param string $formName The current form name
     * @param bool   $minified Whether the js code will be added to the minified version of the js file or the normal version
     **/
    $extraJs   = apply_filters('tsjippy-forms-extra-js', '', $formName, false);
    if (!empty($extraJs)) {
        if (empty($checks)) {
            $js = $extraJs;
        } else {
            $js .= "\n\n";
            $js .= $extraJs;
        }
    }

    //Create js file
    $jsFileName = plugin_dir_path(__DIR__) . "../js/dynamic/{$formName}";
    file_put_contents($jsFileName . '.js', $js);

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

    $extraJs   = apply_filters('tsjippy-forms-extra-js', '', $formName, true);
    if (!empty($extraJs)) {
        $minifiedJs .= "\n\n";
        $minifiedJs .= $extraJs;
    }

    // Create minified version
    file_put_contents($jsFileName . '.min.js', $minifiedJs);

    if (!empty($errors)) {
        TSJIPPY\printArray($errors);
    }

    return $errors;
}

