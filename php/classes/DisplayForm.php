<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

use function TSJIPPY\addElement as addElement;
use function TSJIPPY\addRawHtml as addRawHtml;

if (! defined('ABSPATH')) {
    exit;
}

class DisplayForm extends ElementHtmlBuilder
{
    use CreateJs;

    /**
     * Constructor
     *
     * @param    array    $atts        The attributes for the form
     * @param    bool    $all        Whether to show all elements or only the visible ones
     * @param    int        $pageSize    The number of elements to show per page
     * @param    string    $postId        The post id to get the form for
     * @param    string    $formUrl        The url of the form
     * @param    int        $userId        The user id to get the form for
     */
    public function __construct($atts = [], $all=false, $pageSize=50, $postId='', $formUrl='', $userId=0)
    {
        parent::__construct(atts: $atts, all: $all, pageSize:$pageSize, postId:$postId, formUrl:$formUrl, userId:$userId);

        $this->isFormStep      = false;
        $this->nonWrappable    = [
            'select',
            'file',
            'image',
            'php'
        ];
        $this->isMultiStepForm = '';
        $this->formStepCounter = 0;
        $this->minElForTabs    = 6;
    }

    /**
     * Build all html for a particular element including edit controls.
     *
     * @param    object        $element        The element
     * @param    \DOMElement    $parent            The parent node to which the element should be added
     *
     * @return    string                    The html
     */
    public function buildHtml($element, $parent)
    {

        $elementIndex    = $element->priority - 1;

        if ($element->type == 'div-start') {
            $class        = 'input-wrapper';
            if ($element->hidden) {
                $class    .= " hidden";
            }
            return addElement(
                'div',
                $parent,
                [
                    'name'    => $element->slug,
                    'class'    => $class
                ]
            );
        } elseif ($element->type == 'div-end') {
            return;
        }

        /**
         * Wrap elements that are not wrapped in anoter element
         * in a div container, except for formsteps
         */
        if (
            !$this->isClonableFormStep() &&
            (
                empty($this->prevElement) ||    // this is a clonable formstep and a multi-start element
                !$this->prevElement->wrap
            ) &&        // this element is not wrapped in a previous element
            $element->type != 'formstep'        // this is not a formstep
        ) {
            //Set the element width to 85 percent so that the info icon floats next to it
            if ($elementIndex != 0  && !empty($this->prevElement) && $this->prevElement->type == 'info') {
                $width = 85;
                //We are dealing with a label which is wrapped around the next element
            } elseif ($element->type == 'label' && !isset($element->wrap) && is_numeric($this->nextElement->width)) {
                $width = $this->nextElement->width;
            } elseif (is_numeric($element->width)) {
                $width = $element->width;
            } else {
                $width = 100;
            }

            $class    = 'input-wrapper';

            //Check if element needs to be hidden
            if (!empty($element->hidden)) {
                $class .= ' hidden';
            }

            //if the current element is required or this is a label and the next element is required
            if (
                !empty($element->required)        ||
                !empty($element->mandatory)        ||
                $element->type == 'label'        &&
                (
                    !empty($this->nextElement) &&
                    (
                        $this->nextElement->required    ||
                        $this->nextElement->mandatory
                    )
                )
            ) {
                $class .= ' required';
            }

            if ($element->type == 'info') {
                $class .= ' info';
                $style = '';
            } else {
                if (!empty($element->wrap)) {
                    $class    .= ' flex';
                }
                $style = "width:$width%;";
            }

            $parent = addElement('div', $parent, ['class' => $class, 'style' => $style]);
        }

        // Only add element if this is not a clonable formstep
        $node = '';
        if (
            !$this->clonableFormStep ||            // this is not a clonable formstep
            $element->type == 'multi-start'        // or it is but this is the multi-start element
        ) {
            $node     = $this->getElementHtml($element, $parent);

            if (is_wp_error($node)) {
                return $node;
            }
        }

        //write a formstep div
        if ($element->type == 'formstep') {
            // First step of the form
            if (!$this->isFormStep && !empty($node)) {
                addElement('div', $node, ['class' => "loader-image-trigger"]);
            }

            $this->isFormStep        = true;

            $this->formStepCounter    += 1;
        }

        return $node;
    }

    /**
     * Adds form step controls to the form
     *
     * @param    \DOMElement    $parent        The parent node to which the controls should be added
     *
     * @return    \DOMELEMENT                The next button node, needed for formstep logic in the js
     */
    public function formStepControls($parent)
    {
        // formstep buttons
        if (!$this->isFormStep) {
            return $parent;
        }

        $formstepButtonWrapper     = addElement("div", $parent, ['class' => 'multi-step-controls hidden']);
        $wrapper                 = addElement("div", $formstepButtonWrapper, ['class' => 'multi-step-controls-wrapper']);
        $prevWrapper             = addElement('div', $wrapper,    ['style' => 'flex:1;']);

        /**
         * Previous button
         */
        addElement(
            "button",
            $prevWrapper,
            [
                'type' => 'button',
                'class' => 'button',
                'name' => 'previous-button'
            ],
            'Previous'
        );

        //Circles which indicates the steps of the form:
        $indicatorWrapper = addElement('div', $wrapper,    ['class' => 'step-wrapper', 'style' => 'flex:1;text-align:center;margin:auto;']);
        for ($x = 1; $x <= $this->formStepCounter; $x++) {
            addElement('span', $indicatorWrapper, ['class' => 'step']);
        }

        /**
         * Next button
         */
        $nextWrapper = addElement("div", $wrapper, ['style' => 'flex:1;']);
        addElement(
            'button',
            $nextWrapper,
            [
                'type'    => 'button',
                'class' => 'button next-button',
                'name'    => 'next-button'
            ],
            'Next'
        );

        return $nextWrapper;
    }

    /**
     * Check if we should show the formbuilder or the form itself
     */
    public function determineForm()
    {
        wp_enqueue_style('tsjippy_forms_style');

        $query        = "SELECT * FROM %i WHERE `form_id`=";
        $values       = [$this->elTableName];

        $cacheKey     = "get_form_elments_for_form_";
        if (is_numeric($this->formData->id) && $this->formData->id > -1) {
            $query    .= '%d';
            $values[]  = $this->formData->id;
            $cacheKey .= $this->formData->id;
        } elseif (!empty($this->formData->slug)) {
            $query    .= "(SELECT `id` FROM %i WHERE slug=%s LIMIT 1)";
            $values[]  = $this->tableName;
            $values[]  = $this->formData->slug;
            $cacheKey .= $this->formData->slug;
        } else {
            return new \WP_Error('forms', 'Which form do you have?');
        }

        // phpcs:ignore
        $formElements  =  TSJIPPY\getFromDb($cacheKey, "forms", $query, $values);

        // phpcs:ignore
        if (empty($formElements)) {
            $html    = "<div class='warning'>This form has no elements yet.<br>";
            if ($this->editRights) {
                $url     = add_query_arg('formbuilder', 1, TSJIPPY\getCurrentUrl());
                $html    .= "<br><a href='$url' class='button small tsjippy'>Start Building the form</a>";
            } else {
                $html    .= "Ask an user with the editor role to start working on it";
            }
            return $html . "</div>";
        } else {
            return $this->showForm();
        }
    }

    /**
     * Show the form
     */
    public function showForm()
    {
        //Load conditional js if available and needed
        if (wp_get_environment_type() === 'local') {
            $jsPath        = $this->jsFileName . '.js';
        } else {
            $jsPath        = $this->jsFileName . '.min.js';
        }

        if (!file_exists($jsPath)) {
            //TSJIPPY\printArray("$jsPath does not exist!\nBuilding it now");

            $path    = PLUGINPATH . "js/dynamic";
            if (!is_dir($path)) {
                wp_mkdir_p($path);
            }

            //build initial js if it does not exist
            $this->createJs();
        }

        wp_enqueue_script('tsjippy_forms_script');
        //Only enqueue if there is content in the file
        if (file_exists($jsPath) && filesize($jsPath) > 0) {
            wp_enqueue_script("dynamic_{$this->formData->slug}forms", TSJIPPY\pathToUrl($jsPath), array('tsjippy_forms_script'), $this->formData->version, true);
        }

        $this->formWrapper = addElement('div', '', ['class' => 'tsjippy-form-wrapper']);

        $initialHtml    = apply_filters('tsjippy-forms-before-showing-form', '', $this);
        if (!empty($initialHtml)) {
            addRawHtml($initialHtml, $this->formWrapper);
        }

        // Formbuilder button
        if ($this->editRights) {
            $attributes = [
                'type'     => 'button',
                'class' => 'button small formbuilder-switch'
            ];
            addElement('button', $this->formWrapper, $attributes, 'Switch to formbuilder');
        }

        $formName    = $this->formData->name;
        if (!empty($formName)) {
            addElement("h3", $this->formWrapper, [], $formName);
        }

        if (array_intersect_key($this->userRoles, $this->submitRoles) && !empty($this->formData->save_in_meta)) {
            addRawHtml(TSJIPPY\userSelect("Select an user to show the data of:"), $this->formWrapper);
        }
        addRawHtml(apply_filters('tsjippy-forms-before-form', '', $this->formData->slug), $this->formWrapper);

        /**
         * Form container
         */
        $attributes = [
            'method'       => 'post',
            'class'        => 'tsjippy-form-wrapper',
            'data-form-id' => $this->formData->id
        ];

        // Reset a form when not saving to meta
        if (empty($this->formData->save_in_meta)) {
            $attributes["data-reset"]        = 1;
        } else {
            // make sure empty checkboxes show up in form results
            $attributes["data-add-empty"]    = 1;
        }
        $form = addElement("form", $this->formWrapper, $attributes);

        addElement('div', $form, ['class' => 'form-elements']);

        /**
         * Hidden input for form id
         */
        $attributes = [
            'type'        => 'hidden',
            'class'        => 'no-reset',
            'name'        => 'form-id',
            'value'        => $this->formData->id
        ];
        addElement('input', $form, $attributes);

        /**
         * Hidden input for form url
         */
        $attributes = [
            'type'        => 'hidden',
            'class'        => 'no-reset',
            'name'        => 'formurl',
            'value'        => TSJIPPY\currentUrl(true)
        ];
        addElement('input', $form, $attributes);

        /**
         * Loop over all form elements and add the nodes
         */
        $parents             = ['root' => $form];
        $this->prevElement    = null;
        
        // Sort on priority
        usort($this->formElements, function ($a, $b){
            return $a->priority <=> $b->priority;
        });

        foreach ($this->formElements as $index => $element) {
            /**
             * Store the current and the next elements
             */
            if (isset($this->formElements[$index + 1])) {
                $this->nextElement        = $this->formElements[$index + 1];
            } else {
                $this->nextElement        = null;
            }

            $this->currentElement    = $element;

            // Reset the parents if this is a formstep
            if ($element->type == 'formstep') {
                $parents = ['root' => $form];
            }

            // Insert the main node
            $node = $this->buildHtml($element, end($parents));

            /**
             * Check if we should change the parent node
             */
            if (
                !empty($node) &&                                                             // the node is set
                (
                    (
                        $element->wrap &&                                                    // this is the first wrapping element
                        (
                            empty($this->formElements[$index - 1]) ||
                            !$this->formElements[$index - 1]->wrap
                        )
                    ) ||
                    isset(['formstep' => 1, 'div-start' => 1, 'multi-start' => 1][$element->type]) ||    // this is a wrapping element type
                    $this->clonableFormStep                                                  // this is a clonable forstep multi-start
                )
            ) {
                // Make the first child-div the parent of the concuring elements
                if ($element->type == 'multi-start') {
                    $parents[$element->type] = $this->multiwrapperFirstClone;
                } else {
                    $parents[$element->type] = $node;
                }
            }
            // we finished wrapping remove last parent
            elseif (
                (
                    !$element->wrap &&
                    !empty($this->formElements[$index - 1]) &&
                    $this->formElements[$index - 1]->wrap
                ) ||
                isset(['div-end' => 1, 'multi-end' => 1][$element->type])
            ) {
                array_pop($parents);
            }

            /**
             * Store the current element as the previous element before next iteration
             */
            $this->prevElement        = $element;
        }

        /**
         * Form end
         */
        $inputElementsCount = 0;
        $inputType          = '';
        foreach ($this->formElements as $element) {
            if (!isset($this->nonInputs[$element->type])) {
                $inputElementsCount++;
                $inputType  = $element->type;

                if($inputElementsCount> 1){
                    break;
                }
            }
        }

        if (
            $inputElementsCount > 1 || // We have more than one element
            $inputElementsCount == 1 && !isset(['file' => 1, 'image' => 1][$inputType]) // We only have one input element and its not a file element
        ) {
            $hidden = '';
            $parent = $form;

            $buttonText    = 'Submit the form';
            if (!empty($this->formData->button_text)) {
                $buttonText    = $this->formData->button_text;
            }

            if ($this->isFormStep) {
                $hidden = 'hidden';
                $parent = $this->formStepControls($parent);
            }

            addRawHtml(TSJIPPY\addSaveButton('submit-form', $buttonText, $hidden, false), $parent);
        }

        return $this->formWrapper->ownerDocument->saveHTML();
    }

    /**
     * Finds all elements that should be splitted
     * Two options:
     *    1 - case of a BASENAME[index]SUBNAME name
     *    2 - case of a BASENAME[index] name
     */
    public function findSplitElementIds()
    {
        $baseNames    = $elementIds = [];

        // Check if this is an splitted element
        if (empty($this->formData->split)) {
            return apply_filters('tsjippy-forms-split-element-ids', $elementIds, $this);
        }

        /**
         * loop over all element ids that data should be splitted on
         */
        foreach ($this->formData->split as $splitElementId) {
            // Get the element slug
            $slug    = $this->getElementById($splitElementId, 'slug');

            // Find the base slug keyword followed by one or more numbers between [] followed by a keyword between []
            $pattern    = "/(.*?)\[[0-9]+\]\[([^\]]+)\]/i";

            // This slug matches the pattern
            if (preg_match($pattern, $slug, $matches)) {
                $baseNames[$matches[1]]      = $matches[1];
            } else {
                // Splitted element with just normal multiple values slug[index]
                $elementIds[$splitElementId] = $splitElementId;
            }
        }

        if (empty($baseNames)) {
            return apply_filters('tsjippy-forms-split-element-ids', $elementIds, $this);
        }

        /**
         * Loop over all elements to find splitted ones
         */
        foreach ($this->formElements as $element) {
            // Check if this is an indexed splitted element basename[index][keyname]
            if (str_contains($element->slug, '[')) {
                // loop over all base names that data should be splitted on
                foreach ($baseNames as $baseName) {
                    // Check if this name belongs to this splitted element
                    $pattern        = "/$baseName\[[0-9]+\]\[([^\]]+)\]/i";

                    if (preg_match($pattern, $element->slug, $matches)) {
                        $name            = $matches[1];

                        // store found element ids by basename
                        if (empty($elementIds[$baseName])) {
                            $elementIds[$baseName]    = [];
                        }

                        if (empty($elementIds[$baseName][$name])) {
                            $elementIds[$baseName][$name]    = [];
                        }

                        // Add the current element id
                        $elementIds[$baseName][$name][$element->slug]    = $element->id;
                        break;
                    }
                }
            }
        }

        return apply_filters('tsjippy-forms-split-element-ids', $elementIds, $this);
    }
}
