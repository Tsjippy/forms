<?php

namespace TSJIPPY\FORMS;

use DOMElement;
use stdClass;
use TSJIPPY;
use WP_Error;

use function TSJIPPY\addElement as addElement;
use function TSJIPPY\addRawHtml as addRawHtml;

if (! defined('ABSPATH')) {
    exit;
}

class ElementHtmlBuilder extends SubmitForm
{
    public array $attributes;
    public object $currentElement;
    public array $defaultArrayValues;
    public array $defaultValues;
    public object $element;
    public object $elementHtmlBuilder;
    public DOMElement|null $formWrapper;
    public string $html;
    public int $minElForTabs;
    public int $multiWrapElementCount;
    public object|null $multiwrapperFirstClone;
    public int $multiWrapValueCount;
    public object|null $nextElement;
    public array $nonWrappable;
    public object|null $prevElement;
    public array $usermeta;
    public bool $wrap;
    private mixed $requestedValue;
    private mixed $selectedValue;
    private string $tagType;
    protected array $elementPossibleValues;
    protected array $metaDefaults;

    /**
     * ElementHtmlBuilder constructor.
     *
     * @param   array   $atts       The attributes for the form
     * @param   bool    $all        Whether to get all elements or only the ones for the current user
     * @param   int     $pageSize   The number of elements to get per page
     * @param   string  $postId     The post id to get the elements for
     * @param   string  $formUrl    The url of the form
     * @param   int     $userId     The user id to get the elements for
     */
    public function __construct($atts=[], $all=false, $pageSize=50, $postId='', $formUrl='', $userId=0)
    {
        parent::__construct(atts: $atts, all: $all, pageSize:$pageSize, postId:$postId, formUrl:$formUrl, userId:$userId);

        $this->currentElement         = new stdClass();
        $this->defaultArrayValues     = [];
        $this->defaultValues          = [];
        $this->element                = new stdClass();
        $this->elementHtmlBuilder     = new stdClass();
        $this->formWrapper            = null;
        $this->html                   = '';
        $this->minElForTabs           = -1;
        $this->multiWrapElementCount  = -1;
        $this->multiwrapperFirstClone = null;
        $this->multiWrapValueCount    = -1;
        $this->nextElement            = null;
        $this->nonWrappable           = [];
        $this->prevElement            = null;
        $this->wrap                   = false;
        $this->requestedValue         = null;
        $this->metaDefaults           = [];

        $this->reset();
    }

    /**
     * Resets the element html builder to its default state
     */
    public function reset()
    {
        $this->tagType                = '';
        $this->selectedValue          = '';
        $this->attributes             = ['class' => ''];
    }

    /**
     * Checks if this is a clonable formstep, meaning a multi_start - multi-end group wrapped inside a formstep
     */
    protected function isClonableFormStep()
    {
        $this->clonableFormStep    = false;

        if (
            (
                !empty($this->nextElement) &&
                $this->nextElement->type == 'multi-start' &&
                $this->currentElement->type == 'formstep'
            ) ||
            (
                $this->currentElement->type == 'multi-start' &&
                $this->prevElement->type == 'formstep'
            )
        ) {
            // loop until we find the multi-end
            $x    = $this->currentElement->priority; // this is the index of the next element, which is the multi-start
            while (true) {
                $x++;
                // This is the multi end
                if ($this->formElements[$x]->type == 'multi-end') {
                    // only if the next element is a formstep we have a clonable formstep
                    if (
                        empty($this->formElements[$x + 1]) ||                // this is the last element of the form
                        $this->formElements[$x + 1]->type == 'formstep'        // the next element is a formstep
                    ) {
                        $this->clonableFormStep    = true;
                    }
                    break;
                }
            }
        }

        return $this->clonableFormStep;
    }

    /**
     * Builds an array with all user(meta)data for the current user
     */
    public function buildDefaultsArray()
    {
        global $wpdb;

        //Only create one time, and only for logged in users
        if ($this->userId === 0) {
            return;
        }

        $value = wp_cache_get("default-meta-values-".$this->userId, 'tsjippy_forms', false, $found1);
        if($found1){
            $this->defaultValues    = $value;
        }

        $value = wp_cache_get("default-array-meta-values-".$this->userId, 'tsjippy_forms', false, $found2);
        if($found2){
            $this->defaultArrayValues    = $value;
        }

        if($found1 && $found2){
            return;
        }

        /**
         * User data
         */
        $this->defaultValues      = (array)$this->user->data;
        $this->defaultArrayValues = [];

        // We are getting the form results not for ourselves
        if ($this->userId != $this->user->ID) {
            $this->defaultValues = (array)get_userdata($this->userId)->data;
        }

        //Change ID to user_id because its a confusing name
        $this->defaultValues['user_id']    = $this->defaultValues['ID'] ?? 0;
        unset($this->defaultValues['ID']);
        
        // Do not use everything
        foreach (['user_pass', 'user_activation_key', 'user_status', 'user_level'] as $field) {
            unset($this->defaultValues[$field]);
        }

        /**
         * Check which meta keys can have multiple values for the same user
         */
        $multiKeys  = array_flip(TSJIPPY\getFromDb(
            'multiple-user-meta',
            'forms',
            "select distinct meta_key from (SELECT meta_key, user_id, COUNT(*) FROM %i GROUP BY meta_key, user_id HAVING (COUNT(*) > 1)) as multiple;",
            $wpdb->usermeta
        ));

        /**
         * Filters which user metas can have more than one value for the same key
         * 
         * @param   array   $multiKeys  Array containing the meta keys as array keys that can have more than one value
         */
        $multiKeys  = apply_filters('tsjippy-forms-user-meta-multi-keys', $multiKeys);

        /**
         * Add usermeta
         */
        $userMetas  = get_user_meta($this->userId);

        // add a value for every possible meta key even if the current user doesn't have it
        foreach($this->userMetaKeys() as $metaKey => $index){
            $noPrefixKey    = str_replace('tsjippy_', '', $metaKey);
            // Multi value
            if(isset($multiKeys[$metaKey])){
                //Current user has a value for this
                if(isset($userMetas[$metaKey])){
                    $this->defaultArrayValues[$noPrefixKey] = $userMetas[$metaKey];
                }elseif(!isset($this->defaultArrayValues[$noPrefixKey])){
                    $this->defaultArrayValues[$noPrefixKey] = [];
                }
            }

            // Single value
            else{
                //Current user has a value for this
                if(!empty($userMetas[$metaKey])){
                    if(count($userMetas[$metaKey]) == 1 && isset($userMetas[$metaKey][0])){
                        $this->defaultValues[$noPrefixKey] = $userMetas[$metaKey][0];
                    }else{
                        $this->defaultValues[$noPrefixKey] = $userMetas[$metaKey];
                    }
                }elseif(!isset($this->defaultValues[$noPrefixKey])){
                    $this->defaultValues[$noPrefixKey] = '';
                }
            }
        }

        $this->defaultValues      = array_filter(
            $this->defaultValues, 
            function($value, $key){
                return (
                    !str_contains($key, 'closedpostboxes_') && 
                    !str_contains($key, '_per_page') &&
                    !str_contains($key, 'meta') &&
                    !str_contains($key, 'polq') &&
                    !str_contains($key, '_position') &&
                    !str_contains($key, '_event_id') &&
                    !str_contains($key, 'hidden_columns_')
                );
            },
            ARRAY_FILTER_USE_BOTH 
        );

        // Filter the default values
        $this->defaultValues      = apply_filters('tsjippy-forms-add-form-defaults', $this->defaultValues, $this->userId, $this->formData->slug);

        // Sort on key
        ksort($this->defaultValues);

        // Make sure all data is unserialized
        $this->defaultValues      = map_deep($this->defaultValues, 'maybe_unserialize');

        foreach (TSJIPPY\getUserAccounts(false, false, [], [], [], true) as $user) {
            $this->defaultArrayValues['all_users'][$user->ID] = $user->display_name;
        }

        /**
         *  Add family member names
         */
        $family = new TSJIPPY\FAMILY\Family();
        // Our own details
        $familyNames              = [
            $this->user->ID => $this->user->display_name
        ];

        // Partner
        $partner    = $family->getPartner($this->userId, true);
        if ($partner) {
            $familyNames[$partner->ID]       = $partner->display_name;
        }

        // Siblings
        $siblings    = $family->getSiblings($this->user->ID);
        foreach ($siblings as $sibling) {
            $siblingData                     = get_userdata($sibling);

            if (!$siblingData) {
                continue;
            }

            $familyNames[$sibling]           = $siblingData->display_name;
        }

        $familyNamesWithChildAge             = $familyNames;

        // Children
        $children                            = $family->getChildren($this->user->ID);
        $childrenNames                       = [];
        $childrenAges                        = [];
        foreach ($children as $child) {
            $childData                       = get_userdata($child);
            if (!$childData) {
                continue;
            }

            $name                            = $childData->display_name;
            $birthDateString                 = get_user_meta($child, 'tsjippy_birthday', true);
            $birthDate                       = new \DateTime($birthDateString);
            $currentDate                     = new \DateTime('today');

            // Calculate the difference between the two dates
            $interval                        = $currentDate->diff($birthDate);

            // Extract the number of years from the interval
            $age                             = $interval->y;
            $childrenNames[$child]           = $name;
            $childrenAges[$child]            = $age;
            $familyNamesWithChildAge[$child] = "$name ($age)";
        }

        $familyNames                                             = $familyNames + $childrenNames;

        // Add everything to the defaults array
        $this->defaultArrayValues['children_names']              = $childrenNames;
        $this->defaultArrayValues['children_ages']               = $childrenAges;

        $this->defaultArrayValues['family_member_names']         = $familyNames;
        $this->defaultArrayValues['family_member_names_and_age'] = $familyNamesWithChildAge;

        /**
         * Filters the default array values array
         * 
         * @param   array   $defaultArrayValues Array defaults
         * @param   int     $userId             User Id
         */
        $this->defaultArrayValues    = apply_filters('tsjippy-forms-add-form-multi-defaults', $this->defaultArrayValues, $this->userId);

        ksort($this->defaultArrayValues);

        wp_cache_set("default-meta-values-".$this->userId, $this->defaultValues, 'tsjippy_forms');
        wp_cache_set("default-array-meta-values-".$this->userId, $this->defaultArrayValues, 'tsjippy_forms');
    }

    /**
     * Gets the meta value from for an element
     *
     * @param    string    $metaKey    The meta key
     * 
     * @return   array                 The meta values
     */
    protected function getMetaElementValues($metaKey)
    {
        $this->metaDefaults = [];
        
        if ($this->userId === 0) {
            return;
        }


        if(!empty($this->defaultValues[$metaKey])){
            $this->metaDefaults[]  = $this->defaultValues[$metaKey];

            return;
        }elseif(!empty($this->defaultArrayValues[$metaKey])){
            $this->metaDefaults  = $this->defaultArrayValues[$metaKey];

            return;
        }

        /**
         * Check if indexed
         */
        $indexes = explode('[', $metaKey);

        // Not an indexed metakey
        if(count($indexes) == 1){
            return;
        }

        $baseKey = $indexes[0];
        unset($indexes[0]);

        if(empty($this->defaultValues[$baseKey])){
            return;
        }

        $metaValue    = [];
        foreach($this->defaultValues[$baseKey] as $index => &$metaValue){
            if(!empty($indexes)){
                //loop over all the subkeys, and store the value until we have our final result
                $resultFound    = false;
                foreach ($indexes as $position => $i) {
                    $i  = trim($i, ']');

                    if ($index == $i) {
                        $resultFound    = true;
                        unset($indexes[$position]);
                        break 2;
                    }elseif (isset($metaValue[$i])) {
                        $metaValue      = (array)$metaValue[$i];
                        $resultFound    = true;
                        unset($indexes[$position]);
                        break 2;
                    }
                }

                // somehow it does not exist, return an empty value
                if (!$resultFound) {
                    $metaValue    = [];
                }
            }
        }
        
        $this->metaDefaults = (array)$metaValue;
    }

    /**
     * Gets the prefilled values of an element
     *
     * @param    object    $element        The element
     *
     * @return    array                    The array of values
     */
    protected function getElementValues($element)
    {
        // Do not return default values when requesting the html over rest api
        if (defined('REST_REQUEST')) {
            //return $values;
        }

        if (isset($this->nonInputs[$element->type]) && $element->type != 'datalist') {
            return [];
        }

        /**
         * Get user(meta)data
         */
        $this->buildDefaultsArray();

        /**
         * Gets values from the element settings
         */
        $this->elementPossibleValues  = [];
        if (!empty($element->value_list)) {
            $values    = explode("\n", $element->value_list);

            // split in value text pairs if needed
            foreach ($values as $value) {
                $value    = trim($value);

                // Check if a key value pair is given
                $exploded = explode('|', $value);

                // Key value pair
                if (count($exploded) > 1) {
                    $this->elementPossibleValues[$exploded[0]]       = $exploded[1];
                } 
                // use the value as key as well
                else {
                    $this->elementPossibleValues[strtolower($value)] = $value;
                }
            }
        }

        //retrieve meta values if needed
        $this->getMetaElementValues(trim($element->slug, '[]'));

        //add default values
        if (empty($element->multiple) || isset(['select' => 1, 'checkbox' => 1, 'radio' => 1][$element->type])) {
            $key = $element->default_value ?? '';

            // There is a selected value set
            if (!empty($key)) {
                if (isset($this->defaultValues[$key])) {
                    $this->elementPossibleValues  = array_merge($this->elementPossibleValues, (array)$this->defaultValues[$key]);
                } elseif (!isset($values['defaults'][$key])) {
                    /**
                     * Current user has no value for this key, check if it is a valid user meta key
                     */
                    $allMetaKeys   = $this->userMetaKeys();

                    // The key is also not a registered user meta key
                    if(isset($allMetaKeys[$key]) && !isset($allMetaKeys["tsjippy_$key"])){
                        $this->elementPossibleValues[$key] = $key;
                    }
                }
            }
        }

        if (!empty($element->default_array_value)) {
            $key = $element->default_array_value;
            if (is_array($this->defaultArrayValues[$key] ?? '')) {
                $this->elementPossibleValues = $this->defaultArrayValues[$key] + $this->elementPossibleValues ;
            }
        }
    }

    /**
     * Adds an index to the name and id and adds the value of the current index
     *
     * @param    int                $index            The current iteration index of the element
     * @param    string|array    $value            The value to add
     * @param    object            $node            The node to edit
     */
    protected function changeNodeAttributes($index, $value, $node)
    {
        // the node is already an input
        if (isset($this->inputTags[$node->tagName])) {
            $nodes    = [$node];
        } else {
            $nodes    = $node->getElementsByTagName('*');
        }

        foreach ($nodes as $curNode) {
            if (!isset($this->inputTags[$curNode->tagName])) {
                continue;
            }

            if ($value === null) {
                $value = '';
            }

            /**
             * Change the name
             */
            // make sure we add the [] after the index if there was [] originally
            $name                = str_replace('[]', '', $this->element->slug, $replaceCount);
            $indexString         = "[$index]";
            if ($replaceCount) {
                $indexString    .= "[]";
            }

            // Add the index to the name
            $curNode->setAttribute('name', $name . $indexString);

            /**
             * Change the id
             */
            if (!empty($curNode->attributes['id']->value)) {
                // Add the index to the id
                $curNode->setAttribute('id', $name . "[$index]");
            }

            /**
             * Change selected option
             */
            if ($this->element->type == 'select') {
                $options = $curNode->getElementsByTagName('option');

                foreach ($options as $option) {
                    if ($option->attributes['value']->value == $value) {
                        $option->setAttribute('selected', 'selected');
                    } else
                        $option->removeAttribute('selected');
                }
            }

            /**
             * Change selected checkbox
             */
            elseif (isset($this->checkboxTypes[$this->element->type])) {
                $nodes = $curNode->getElementsByTagName($this->element->type);

                foreach ($nodes as $nd) {
                    if ($nd->attributes['value']->value  == $value) {
                        $nd->setAttribute('checked', 'checked');
                    } else
                        $nd->removeAttribute('checked');
                }
            }

            /**
             *  Element value
             */
            elseif ($this->element->type == 'textarea') {
                $curNode->nodeValue = $value;
            } elseif (is_array($value)) {
                $curNode->setAttribute('value', $value[$index]);
            } elseif (!empty($value)) {
                $curNode->setAttribute('value', $value);
            }

            // Add the index to the label if we are not displaying it on seperate tabs
            if (
                $this->element->type == 'label' &&
                $this->multiWrapElementCount < $this->minElForTabs
            ) {
                $nr                     = $index + 1;
                $curNode->nodeValue    .= " $nr";
            }
        }
    }

    /**
     * Get the previous values of a element
     */
    protected function getPrevValues($returnArray = false)
    {
        if (empty($this->submissions)) {
            return;
        }

        // Check if we should include previous submitted values
        $prevValues        = '';

        if ($returnArray) {
            $prevValues        = [];
        }

        // we are not doing this via an api request
        // phpcs:ignore
        if (!str_contains($_SERVER['REDIRECT_URL'] ?? '', 'get_input_html') || !empty($this->requestedValue)) {
            return $prevValues;
        }

        $valueIndexes    = explode('[', str_replace('[]', '', $this->element->slug));

        foreach ($valueIndexes as $i => $index) {
            // just one possible value found
            if ($i == 0) {
                // there is no value in the form results
                if (empty($this->submissions[0]->{$index})) {

                    // check the submission meta data
                    if (empty($this->submissions[0]->$index)) {
                        break;
                    }

                    $prevValues    = $this->submissions[0]->$index;
                }

                // This is a splitted value, select all values
                // phpcs:ignore
                elseif (count($this->submissions) > 1 && !empty($_POST['subid'])) {
                    $prevValues    = [];

                    foreach ($this->submissions as $submission) {
                        $prevValues[]    = $submission->{$index};
                    }
                } else {
                    $prevValues    = $this->submissions[0]->{$index};
                }
            }

            // return the sub value
            else {
                if ($i == 1 && is_numeric($_POST['subid'] ?? '')) {
                    $index    = (int) $_POST['subid'];
                }

                $index    = trim($index, ']');

                if (!isset($prevValues[$index])) {
                    break;
                }

                $prevValues    = $prevValues[$index];
            }
        }

        if (is_string($prevValues)) {
            $result    = json_decode($prevValues);

            if (json_last_error() === JSON_ERROR_NONE) {
                $prevValues        = $result;
            }
        }

        return $prevValues;
    }

    /**
     * Determines the target dir for a file/image element
     * 
     * @return string   THe path for uploads
     */
    protected function uploadDir(){
        // Element setting
        if (!empty($this->element->folder_name)) {
            if (str_contains($this->element->folder_name, "private/")) {
                $targetDir    = $this->element->folder_name;
            } else {
                $targetDir    = "private/" . $this->element->folder_name;
            }
        }

        // Form setting
        if (empty($targetDir)) {
            $targetDir = $this->formData->upload_path;
        }

        // Default setting
        if (empty($targetDir)) {
            $targetDir = 'private/form_uploads/' . $this->formData->slug;
        }

        $baseDir    = wp_upload_dir()['basedir'];
        if(!str_contains($targetDir, $baseDir)){
            $targetDir   = $baseDir . '/' . $targetDir;
        }

        return wp_normalize_path($targetDir);
    }

    /**
     * Gets the element value
     */
    protected function getValue()
    {
        if (isset($this->nonInputs[$this->element->type]) || $this->requestedValue === false) {
            return '';
        }

        // The requested value is a value of a previous submission, find previous submitted values if not provided to the function
        if (empty($this->requestedValue)) {
            $this->requestedValue    = $this->getPrevValues();
        }

        // Do not continue
        if (
            $this->multiwrap ||
            !empty($this->element->multiple)
        ) {
            return;
        }

        $this->selectedValue    = $this->requestedValue;

        if (empty($this->selectedValue)) {
            //this is an input and there is a value for it
            if (
                !empty($this->elementPossibleValues) &&     // there is a default value
                (
                    empty($this->formData->save_in_meta) ||     // we are not saving to the user meta table
                    empty($this->metaDefaults)    // or the metavalue is empty
                )
            ) {
                $this->selectedValue        = $this->elementPossibleValues;
            } 
            
            elseif (!empty($this->metaDefaults)) {
                $elIndex    = 0;
                if (str_contains($this->element->slug, '[]')) {
                    // Check if there are multiple elements with the same name
                    $elements    = $this->getElementBySlug($this->element->slug, '', false);

                    foreach ($elements as $elIndex => $el) {
                        if ($el->id == $this->element->id) {
                            break;
                        }
                    }
                }

                $this->selectedValue        = array_values($this->metaDefaults)[$elIndex];
            }        
        }

        if (
            !empty($this->selectedValue) &&
            !isset($this->checkboxTypes[$this->element->type])
        ) {
            if (is_array($this->selectedValue)) {
                $this->selectedValue    = array_values($this->selectedValue)[0];
            }

            // if there is a datalist attached to this element we should use the corresponding name
            if (isset($this->attributes['list'])) {
                $listElement    = $this->getElementBySlug($this->attributes['list']);

                if ($listElement && !empty($this->defaultArrayValues[$listElement->default_array_value])) {
                    // Get the list values
                    $values    = $this->defaultArrayValues[$listElement->default_array_value];

                    if (!empty($values[$this->selectedValue])) {
                        $this->selectedValue    = $values[$this->selectedValue];
                    }
                }
            }

            $this->attributes["value"] = $this->selectedValue;
        }
    }
}
