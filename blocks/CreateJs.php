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
        return ($block['attrs']['type'] ?? '');
    }else{
        return explode('/', $block['name'])[1];
    }
}

function getBlockId($block){
    return ($block['attrs']['blockId'] ?? '');
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

    if(isset(['radio' => 1, 'checkbox' => 1][getInputType($block)])){
        if(empty($value)){
            $selector   .= ":checked";
        }else{
            $selector   .= "[value='$value']"; 
        }
    }
}

/**
 * Builds the js based on block conditions
 * 
 * @param   array   $conditions     All conditions for a certain post
 * @param   array   $innerBlocks
 */
function dynamicJs($conditions, $innerBlocks){
    /**
     * Loop over the conditions to build an array indexed by the blockids that should trigger the logic
     */
    $triggers   = [];
    foreach ($conditions as $condition) {
        //if there are rules build some javascript
        if (empty($condition->rules ?? []) ) {
            continue;
        }

        $triggerBlocks  = [];

        /**
         * Add a trigger for each rule
         */
        foreach($condition->rules as $rule){
            if(empty($rule['conditional-field'])){
                continue;
            }

            $triggerBlocks[] = $rule['conditional-field'];

            if(!empty($rule['conditional-field-2'])){
                $triggerBlocks[] = $rule['conditional-field-2']; 
            }
        }

        if(count($triggerBlocks) > 1){
            $triggerString  = "if([" . implode(", ", $triggerBlocks) . "].includes(blockId)){";
        }else{
            $triggerString  = "if (blockId == '" . $triggerBlocks[0] . "'){";
        }

        // One trigger can have multiple conditions
        if(!isset($triggers[$triggerString])){
            $triggers[$triggerString] = [];
        }

        $triggers[$triggerString][]  = $condition;
    }

    foreach($triggers as $triggerString => $triggeredConditions){
        // Open if statement
        echo "\n\n\t\t" . wp_kses_post($triggerString);

        $comparing    = true;
        
        // Check if this trigger is only invoked for a changed or clicked action
        if(
            count($triggeredConditions) == 1 &&
            count($triggeredConditions[0]->rules) == 1 &&
            isset(['changed' => 1, 'clicked' => 1][$triggeredConditions[0]->rules[0]['equation']])
        ){
            $comparing = false;
        }

        /**
         * Write the variables holding the input values
         */
        $vars        = [];
        $comparators = [];
        $actions     = [];

        // Loop over all conditions of this trigger
        foreach($triggeredConditions as $conditionIndex => $condition){
            if($comparing){
                $comparators[$conditionIndex] = [];

                // Loop over all the rules of this condition 
                foreach($condition->rules as $ruleIndex => $rule){
                    /**
                     * Determine variables
                     */
                    // Clicked and changed do not need variables
                    if(isset(['changed' => 1, 'clicked' => 1][$rule['equation']])){
                        // Clicked or changed the element
                        $comparators[$conditionIndex][] = "el.dataset.blockid == '{$rule['conditional-field']}'";
                    }
                    
                    // Check if visible
                    elseif(isset(['visible' => 1, 'invisible' => 1][$rule['equation']])){
                        $compare =  "el.dataset.blockid == '{$rule['conditional-field']}' &&";
                        if($rule['equation'] == 'visible'){
                            $compare .= "!";
                        }

                        $compare .= "el.classList.contains('step-hidden', 'hidden')";

                        $comparators[$conditionIndex][] = $compare;
                    }
                    
                    else{
                        $varName    = "value_{$conditionIndex}_$ruleIndex";

                        // Add the var
                        $vars[]     = "let $varName = this.getValue('{$rule['conditional-field']}', form);";

                        $compareFrom  = $varName;

                        $compareValue = $rule['conditional-value'];

                        // Wrap in " if not a number
                        if(!is_numeric($compareValue)){
                            $compareValue   = "'" . $compareValue . "'";
                        }

                        if(!empty($rule['conditional-field-2'])){
                            $vars[] = "let {$varName}_2 = this.getValue('{$rule['conditional-field-2']}', form);";

                            $compareValue   = "{$varName}_2";
                        }

                        $comparator = $rule['equation'];

                        // When adding or subsrtracting we first need to calculate the compare value
                        if(isset(['+' => 1, '-' => 1][$comparator])){
                            $compareFrom  = esc_attr($varName) . ' ' . esc_attr($comparator) . ' ' . $varName . "_2";

                            $comparator   = $rule['equation2'];
                        }

                        
                        /**
                         * Actual comparison
                         */
                        $comparators[$conditionIndex][] = $compareFrom . ' ' . $comparator . ' ' . $compareValue . ($rule['combinator'] ?? '');
                    }
                }
            }

            /**
             * Create the action js
             */
            $actions[$conditionIndex] = [
                'target'    => $condition->target
            ];

            foreach($condition->actions as $action){
                if($action['action'] == 'set-property'){
                    $addition   = $action['addition'] ?? '';

                    $newValue   = $action['property-value'];

                    // We should replace with a dynamic value
                    if(str_contains($newValue, 'the-value-of-')){
                        $blockId    = str_replace('the-value-of-', '', $newValue);
                        $newValue   = "this.getValue('$blockId', form)";
                    }else{
                        $newValue   = "'$newValue'";
                    }

                    $actions[$conditionIndex]['action'] = "
                this.change_field_property(
                    target,
                    '{$action['property-name']}',
                    $newValue,
                    form,
                    '{$addition}'
                );
                    ";   
                }else{
                    // Add, remove or toggle the hidden class
                    if($action['action'] == 'show'){
                        $action = 'remove';
                    }elseif($action['action'] == 'hide'){
                        $action = 'add';
                    }else{
                        $action = $action['action'];
                    }

                    $actions[$conditionIndex]['action'] = "target.classList.$action('hidden');";
                }
            }
        }

        /**
         * Print vars and comparisons
         */
        foreach($vars as $var){
            echo "\n\t\t\t" . wp_kses_post($var) ."\n";
        }

        $actionStrings   = [];

        foreach($actions as $conditionIndex => $actionData){
            // The element to perform the action on
            $targetQuery = "let target  = form.querySelector(`[data-blockid='{$actionData['target']}']`)";
                    
            // Show/hide/toggle the label in stead of the element
            if(str_contains($actionData['action'], 'target.classList') && $innerBlocks[$actionData['target']]['attrs']['hasLabelParent'] ?? false){
                $targetQuery .= ".closest('label')";
            }
                    
            $actionStrings[$conditionIndex] = "\n\t\t\t\t" . wp_kses_post($targetQuery) . ";\n";
            $actionStrings[$conditionIndex] .= "\n\t\t\t\t" . wp_kses_post($actionData['action']);
        }

        if(empty($comparators )){
            foreach($actionStrings as $actionString){
                echo wp_kses_post($actionString);
            }
        }

        foreach($comparators as $conditionIndex => $ifs){
            echo "\n\t\t\tif(";

                foreach($ifs as $if){
                    echo "\n\t\t\t\t" . wp_kses_post($if);
                }
            
            echo "\n\t\t\t){";
                echo wp_kses_post($actionStrings[$conditionIndex]);
            echo "\n\t\t\t}";
        }

        // CLose if triggerString
        echo "\n\t\t}";
    }
}

/**
 * Retrive the static js independent of conditions
 * 
 * @param   string  $formName
 * @param   array   $conditions
 * @param   array   $innerBlocks
 * 
 * @return  string              The js code
 */
function defaultJs($formName, $conditions, $innerBlocks){
    // Object name
    $objectName  = strtolower(str_replace('-', '', $formName));

    $className   = ucfirst($objectName);

    ob_start(); 

    ?>
class <?php echo esc_attr($className);?> {
    // We could have multiple instances of the same form on one page
    forms = document.querySelectorAll(`form[data-formname="<?php echo esc_attr($formName);?>"]`);

    change_field_property   = FormFunctions.changeFieldProperty;

    // Callback function to execute when mutations are observed
    onMutation = (mutationList, observer) => {
        for (const mutation of mutationList) {
            if (mutation.type === "childList") {
            console.log("A child node has been added or removed.");
            } else if (mutation.type === "attributes") {
            console.log(`The ${mutation.attributeName} attribute was modified.`);
            }else{
                console.log(mutation);
            }

            this.handleConditions(mutation.target);
        }
    };

    prevEl = '';

    // Create an observer instance linked to the onMutation function
    observer = new MutationObserver(this.onMutation);

    init = () => {
        console.log('Dynamic <?php echo esc_attr($formName);?> form js loaded');

        // Start observing the target node for configured mutations
        this.forms.forEach(form => {
            this.observer.observe(form, { attributes: true, childList: true, subtree: true });

            form.addEventListener('click', (event) => this.handleConditions(event.target));
            form.addEventListener('input', (event) => this.handleConditions(event.target));
        });

        FormFunctions.tidyMultiInputs();
        
        // Loop over the elements who's value is given in the url and set the value;
        if (typeof(urlSearchParams) == 'undefined') {
            window.urlSearchParams = new URLSearchParams(window.location.search.replaceAll('&amp;', '&'));
        }

        Array.from(urlSearchParams).forEach (array => {
            this.forms.forEach(form => {
                form.querySelectorAll(`[name^='${array[0]}' i]`).forEach (el => this.change_field_value(el, array[1], processFields, form));
            });
        });

        /**
        * Loop over the elements who have a default value and apply the logic;
        */
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
    };

    getValue    = (blockid, form) => {
        return FormFunctions.getFieldValue(form.querySelector(`[data-blockid='${blockid}']`), form);
    };

    handleConditions = (node) => {
        let el            = event.target;
        let form          = el.closest('form');
        let elName        = el.getAttribute('name');

        if (elName == '' || elName == undefined) {
            //el is a nice select
            if (el.closest('.nice-select-dropdown') != null) {
                //find the select element connected to the nice-select
                el.closest('.input-wrapper').querySelectorAll('select').forEach (select=>{
                    if (el.dataset.value == select.value) {
                        el    = select;
                        elName = select.name;
                    }
                });
            }else{
                return;
            }
        }

        //prevent duplicate event handling
        if (el == this.prevEl) {
            return;
        }

        this.prevEl = el;

        //clear event prevenion after 100 ms
        setTimeout(() => { this.prevEl = ''; }, 50);

        this.processFields(el);
    };

    processFields = (el) => {
        // Ge the name of the input that just got changed
        let blockId = el.dataset.blockid;

        // Get the form this input belongs to
        let form    = el.closest('form');
        <?php 
        dynamicJs($conditions, $innerBlocks);
        ?>
    };
}

let <?php echo esc_attr($objectName);?> = new <?php echo esc_attr($className);?>();

<?php echo esc_attr($objectName);?>.init();
    <?php

    return ob_get_clean();
}

/**
 * Builds dynamic js
 * 
 * @param   array       $block formbuilder block
 * @param   \WP_Post    $post   Wp Post object
 */
function buildJs($block, $post){
    $formName   = $block['attrs']['name'] ?? '';
    if(empty( $formName )){
        return;
    }

    // Get all conditions for this post
    $forms       = new Forms();
    $conditions  = $forms->getAllBlockConditions($post->ID);
    $innerBlocks = getBlocks($block);

    $checks      = [];
    $errors      = [];

    /**
     * BUILD THE JS
     */
    $js         = defaultJs($formName, $conditions, $innerBlocks);
    $minifiedJs = "";
    
    $minifiedJs .= \Garfix\JsMinify\Minifier::minify($js, array('flaggedComments' => false));

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
    $jsFileName = plugin_dir_path(__DIR__) . "js/dynamic/{$formName}";
    file_put_contents($jsFileName . '.js', $js);

    //replace long strings for shorter ones
    $minifiedJs = str_replace(
        [
            "listener",
            "processFields",
            'value_',
            'elName',
            "\n",
            "change_field_property",
            "init"
        ],
        [
            'q',
            'p',
            'v_',
            'n',
            '',
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

