<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;

use function TSJIPPY\addElement as addElement;
use function TSJIPPY\addRawHtml as addRawHtml;

if (! defined('ABSPATH')) {
    exit;
}

class FormBuilderForm extends DisplayForm
{
    public bool $inMultiAnswer;
    public bool $isInDiv;
    public bool $showId;
    public bool $showName;


    public function __construct($atts = [], $showId=false, $showName=false, $all=false, $pageSize=50, $postId='', $formUrl='', $userId=0)
    {
        parent::__construct(atts: $atts, all: $all, pageSize:$pageSize, postId:$postId, formUrl:$formUrl, userId:$userId);

        $this->inMultiAnswer = false;
        $this->isInDiv       = false;
        $this->showName      = $showName;
        $this->showId        = $showId;
    }

    /**
     * Prints a dropdown with all form elements
     *
     * @param    int|array        $selectedId    The id of the current selected element in the dropdown. Default empty
     * @param    int        $elementId    the id of the element
     *
     */
    protected function inputDropdown($selectedId, $elementId = '')
    {
        ?>
        <option value='' <?php if (empty($selectedId)){echo 'selected';}?>>
            ---
        </option>
        <?php

        // Add booking date elements
        /**
         * Filters the elements of this form,
         * @param    array   $elements The elements array
         * @param    object  $object   The form instance
         * @param    bool    $force    Wheter to force a requery
         */
        $elements    = apply_filters('tsjippy-forms-elements', $this->formElements, $this, true);

        foreach ($elements as $element) {
            //do not include the element itself do not include non-input types
            if ($element->id != $elementId && !isset(['label' => 1, 'info' => 1, 'datalist' => 1, 'formstep' => 1, 'div-end' => 1][$element->type])) {
                $slug = ucfirst(str_replace('_', ' ', $element->slug));

                // add the id if non-unique name
                if (str_contains($slug, '[]')) {
                    $slug    .= " ($element->id)";
                }

                //Check which option is the selected one
                if (
                    !empty($selectedId) &&                      // there is an option selected
                    (
                        $selectedId == $element->id    ||       // its the current element
                        (
                            is_array($selectedId)        &&     // multiple elements are selected
                            in_array($element->id, $selectedId) // current element is one of the selected   
                        )

                    )
                ) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                
                ?>
                <option value='<?php echo esc_attr($element->id);?>' <?php echo esc_attr($selected);?>>
                    <?php echo esc_html($element->name);?>
                </option>
                <?php
            }
        }
    }

    /**
     * Adds meta symbols to an element based on its properties
     *
     * @param    object    $parent    The parent node
     * @param    object    $element    The element to add symbols to
     */
    protected function addMetaSymbols($parent, $element)
    {
        //Add a symbol if this field has conditions or is required
        if (empty($element->conditions) && !$element->required && !$element->mandatory) {
            return;
        }

        $icons        = [];
        if (!empty($element->conditions)) {
            $icons[]        = [
                'content'     => '*',
                'explainer'    => 'This element has conditions',
                'class'        => '',
                'right'        => 20
            ];
        }

        if (!empty($element->required)) {
            $right            = 20;
            if (count($icons) > 0) {
                $right    = 50;
            }
            $icons[]        = [
                'content'     => '!',
                'explainer'    => 'This element is required',
                'class'        => '',
                'right'        => $right
            ];
        }

        if ($element->mandatory) {
            $right            = 20;
            if (count($icons) == 1) {
                $right    = 50;
            } elseif (count($icons) == 2) {
                $right    = 80;
            }

            $icons[]        = [
                'content'     => '!',
                'explainer'    => 'This element is conditionally required',
                'class'        => 'conditional',
                'right'        => $right
            ];
        }

        $right    = $icons[array_key_last($icons)]['right'] + 30;

        foreach ($icons as $icon) {
            $div    = addElement(
                'div',
                $parent,
                [
                    'class'    => 'info-box',
                    'style'    => 'position: absolute;top: 0;width: 100%;'
                ]
            );

            addElement(
                'span',
                $div,
                [
                    'class' => "conditions-info formfield-button {$icon['class']}",
                    'style' => "right:{$icon['right']}px"
                ],
                $icon['content']
            );

            addElement(
                'span',
                $div,
                [
                    'class' => 'info-text conditions',
                    'style' => "position: absolute;margin: 0;right: {$right}px;top: 5px;height: 30px;"
                ],
                $icon['explainer']
            );
        }
    }

    /**
     * Build all html for a particular element including edit controls.
     *
     * @param    object    $element        The element
     * @param    object    $parent         The parent node
     * @param    int       $key            The key in case of a multi element. Default 0
     *
     * @return    string                   The html
     */
    public function buildHtml($element, $parent = '', $key = 0)
    {
        $class        = 'form-element-wrapper';
        if ($this->inMultiAnswer) {
            $class    .= 'multi-answer-element';
        }

        $style    = 'display: flex;';

        // Visualy show that an element is wrapped in a div container
        if ($this->isInDiv && $element->type != 'div-end') {
            $style    .= 'margin-left: 30px;';
        }

        //Add form edit controls if needed
        $controlsWrapper    = addElement(
            'div',
            $parent,
            [
                'class'             => $class,
                'data-element-id'   => $element->id,
                'data-form-id'      => $this->formData->id,
                'data-priority'     => $element->priority,
                'data-type'         => $element->type,
                'style'             => $style
            ]
        );

        $span    = addElement(
            'span',
            $controlsWrapper,
            [
                'class'         => 'movecontrol formfield-button',
                'aria-hidden'     => 1
            ],
            ':::'
        );

        addElement('br', $span);

        $class    = 'element-id';
        // phpcs:ignore
        if (!$this->showId) {
            $class    .= ' hidden';
        }
        addElement(
            'span',
            $span,
            [
                'class' => $class,
                'style' => 'font-size:xx-small'
            ],
            $element->id
        );

        $resizerWrapper    = addElement('div', $controlsWrapper, ['class' => 'resizer-wrapper']);

        //Check if element needs to be hidden
        $hidden = '';
        if (!empty($element->hidden) && $element->hidden == true) {
            $hidden = ' hidden';
        }

        //if the current element is required or this is a label and the next element is required
        if (
            $element->required == true           ||
            $element->mandatory == true          ||
            (
                $element->type == 'label'        &&
                !empty($this->nextElement)       &&
                (
                    $this->nextElement->required ||
                    $this->nextElement->mandatory
                )
            )
        ) {
            $hidden .= ' required';
        }

        if ($element->type == 'info') {
            $attributes    = ["class" => "show input-wrapper$hidden"];
        } else {
            //Set the element width to 85 percent so that the info icon floats next to it
            if ($key != 0 && $this->prevElement->type == 'info') {
                $width = 85;
                //We are dealing with a label which is wrapped around the next element
            } elseif ($element->type == 'label'    && !isset($element->wrap) && is_numeric($this->nextElement->width)) {
                $width = $this->nextElement->width;
            } elseif (is_numeric($element->width)) {
                $width = $element->width;
            } else {
                $width = 100;
            }

            $attributes    = [
                "class"                 => "resizer show input-wrapper$hidden",
                "data-width-percentage" => "$width",
                "style"                 => "width:$width%;"
            ];
        }

        $text    = '';
        $name    = ucfirst(str_replace('_', ' ', $element->slug));
        if ($element->type == 'formstep') {
            $text = ' ***Formstep element***';
        } elseif ($element->type == 'datalist') {
            $text = " ***Datalist element $element->slug***";
        } elseif ($element->type == 'multi-start') {
            $text = ' ***Multi answer start***';
            $this->inMultiAnswer    = true;
        } elseif ($element->type == 'multi-end') {
            $text = ' ***Multi answer end***';
            $this->inMultiAnswer    = false;
        } elseif ($element->type == 'div-start') {
            $text             = " ***$name div container start***";
            $this->isInDiv    = true;
        } elseif ($element->type == 'div-end') {
            $name            = ucfirst(str_replace('_', ' ', $element->slug));
            $text             = " ***$name div container end***";
            $this->isInDiv    = false;
        }

        $resizer    = addElement('div', $resizerWrapper, $attributes, $text);

        if (!isset(['multi-start' => 1, 'multi-end' => 1, 'div-start' => 1, 'div-end' => 1][$element->type])) {
            //Load default values for this element
            $this->getElementHtml($element, $resizer);
        }

        $hidden    = ' hidden';
        if ($this->showName) {
            $hidden    = '';
        }

        addElement(
            'span',
            $resizer,
            [
                'class' => "element-name $hidden",
                'style' => 'font-size:xx-small;'
            ],
            $element->slug
        );

        $this->addMetaSymbols($resizer, $element);

        addElement('span', $resizer, ['class' => 'width-percentage formfield-button']);

        addElement(
            'button',
            $controlsWrapper,
            [
                'type'    => 'button',
                'class'    => 'add-form-element button formfield-button',
                'title'    => 'Add an element after this one'
            ],
            '+'
        );

        addElement(
            'button',
            $controlsWrapper,
            [
                'type'    => 'button',
                'class'    => 'remove-form-element button formfield-button',
                'title'    => 'Remove this element'
            ],
            '-'
        );

        addElement(
            'button',
            $controlsWrapper,
            [
                'type'    => 'button',
                'class'    => 'edit-form-element button formfield-button',
                'title'    => 'Change this element'
            ],
            'Edit'
        );

        $copyButton    = addElement(
            'button',
            $controlsWrapper,
            [
                'type'    => 'button',
                'class'    => 'copy-form-element button formfield-button',
                'title'    => 'Duplicate this element'
            ]
        );

        addElement(
            'img',
            $copyButton,
            [
                'class'     => 'copy copy-form-element',
                'src'       => TSJIPPY\pathToUrl(PLUGINPATH . 'pictures/copy_white.png'),
                'loading'    => 'lazy'
            ]
        );

        if (empty($parent)) {
            return $controlsWrapper->ownerDocument->saveHtml();
        }

        return $controlsWrapper;
    }

    /**
     * Main function to show all
     */
    public function showForm()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        // Load js
        wp_enqueue_script('tsjippy_forms_script');

        //Formbuilder js
        wp_enqueue_script('tsjippy_formbuilderjs');

        // make sure we use unique priorities
        ob_start();

        ?>
        <div class="tsjippy-form-wrapper">
            <?php
            $this->addElementModal();

            ?>
            <button 
                class="button tablink formbuilder-form
                <?php if (!empty($this->formElements)) echo ' active';  ?>" 
                id="show-element-form" 
                data-target="element-form"
                >
                Elements
            </button>
            <button 
                class="button tablink formbuilder-form
                <?php if (empty($this->formElements)) echo ' active';  ?>" id="show-form-settings" data-target="form-settings">
                Settings
            </button>
            <button class="button tablink formbuilder-form" id="show-form-reminders" data-target="form-reminders">
                Reminders
            </button>
            <button class="button tablink formbuilder-form" id="show-form-emails" data-target="form-emails">
                E-mails
            </button>

            <div class="tabcontent
                <?php if (empty($this->formElements)) echo ' hidden';  ?>" id="element-form">
                <?php $this->formElementsForm(); ?>
            </div>

            <div class="tabcontent
            <?php if (!empty($this->formElements)) echo ' hidden';  ?>" id="form-settings">
                <?php $this->formSettingsForm(); ?>
            </div>

            <div class="tabcontent
                <?php if (!empty($this->formElements)) echo ' hidden';  ?>" 
                id="form-reminders">
                <?php $this->formReminderForm(); ?>
            </div>

            <div class="tabcontent hidden" id="form-emails">
                <?php $this->formEmailsForm(); ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Outputs the form builder
     */
    public function formElementsForm()
    {
        if (empty($this->formElements)) {
            ?>
            <div name="formbuildbutton">
                <p>No formfield defined yet.</p>
                <button name='createform' class='button' data-slug='<?php echo esc_attr($this->formData->slug); ?>'>
                    Add fields to this form
                </button>
            </div>
            <?php
        } else {
        ?>
            <div class="form-edit-buttons-wrapper">
                <button name='show-id' class='button' data-action='show' style='padding-top:0px;padding-bottom:0px;'>
                    Show element id's
                </button>
                <button name='show-name' class='button' data-action='show' style='padding-top:0px;padding-bottom:0px;'>
                    Show element name's
                </button>
                <button class='button formbuilder-switch-back small'>
                    Show enduser form
                </button>
            </div>
            <?php
        }

        $form        = addElement(
            'form',
            '',
            [
                'action'    => '',
                'method'    => 'post',
                'class'         => 'tsjippy-form builder'
            ]
        );

        $wrapper    = addElement('div', $form, ['class'    => 'form-elements']);

        addElement(
            'input',
            $wrapper,
            [
                'type'    => 'hidden',
                'class'    => 'no-reset',
                'name'    => 'form-id',
                'value'    => $this->formData->id
            ]
        );

        $this->nextElement        = null;

        // Sort on priority
        usort($this->formElements, function ($a, $b){
            return $a->priority <=> $b->priority;
        });

        foreach ($this->formElements as $key => $element) {
            if (isset($this->formElements[$key + 1])) {
                $this->nextElement = $this->formElements[$key + 1];
            } else {
                $this->nextElement = null;
            }

            $this->currentElement  = $element;

            $this->buildHtml($element, $wrapper, $key);

            $this->prevElement     = $element;
        }

        // Print the html
        // phpcs:ignore
        echo $form->ownerDocument->saveHtml();
    }

    /**
     * The modal to add an element to the form
     */
    public function addElementModal()
    {
        ?>
        <div class="modal add-form-element-modal hidden">
            <!-- Modal content -->
            <div class="modal-content" style='max-width:90%; width:max-content;'>
                <?php TSJIPPY\addCloseButtton();?>

                <button class="button tablink formbuilder-form active" id="show-element-builder" data-target="element-builder">
                    Form element
                </button>
                <button class="button tablink formbuilder-form" id="show-element-conditions" data-target="element-conditions">
                    Element conditions
                </button>

                <div class="tabcontent" id="element-builder">
                </div>

                <div class="tabcontent hidden" id="element-conditions">
                    <div class="element-conditions-wrapper"></div>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Form to change form settings
     */
    public function formSettingsForm()
    {
        global $wp_roles;

        //Get all available roles
        $userRoles = $wp_roles->role_names;

        //Sort the roles
        asort($userRoles);

    ?>
        <div class="element-settings-wrapper">
            <form action='' method='post' class='tsjippy-form builder'>
                <div class='form-elements'>
                    <input type='hidden' class='no-reset' class='formbuilder' name='form-id' value='<?php echo esc_attr($this->formData->id); ?>'>

                    <label class="block">
                        <h4>Submit button text</h4>
                        <input type='text' class='formbuilder form-element-setting' name='button-text' value="<?php echo esc_attr($this->formData->button_text); ?>">
                    </label>

                    <label class="block">
                        <h4>Succes message</h4>
                        <input type='text' class='formbuilder form-element-setting' name='succes-message' value="<?php echo esc_attr($this->formData->succes_message); ?>">
                    </label>

                    <label class="block">
                        <h4>Include submission ID in message</h4>
                        <label>
                            <input 
                                type='radio' 
                                class='formbuilder form-element-setting' 
                                name='include-id' 
                                value="1" 
                                <?php if (!isset($this->formData->include_id) || $this->formData->include_id) echo 'checked';  ?>
                            >
                            Yes
                        </label>
                        <label>
                            <input 
                                type='radio' 
                                class='formbuilder form-element-setting' 
                                name='include-id' 
                                value="0" 
                                <?php if (!($this->formData->include_id ?? false)) echo 'checked';  ?>
                            >
                            No
                        </label>
                    </label>

                    <label class="block">
                        <h4>Form name</h4>
                        <input type='text' class='formbuilder form-element-setting' name='name' value="<?php echo esc_attr($this->formData->name) ?>">
                    </label>
                    <br>

                    <label class='block'>
                        <input type='checkbox' class='formbuilder form-element-setting' name='save-in-meta' value='1' <?php if ($this->formData->save_in_meta) echo 'checked';  ?>>
                        Save submissions in usermeta table
                    </label>
                    <br>

                    <label class="block">
                        <h4>Form url</h4>
                        <?php
                        if (!empty($this->formData->url)) {
                            $url    = $this->formData->url;
                        } else {
                            $url    = str_replace(['?formbuilder=yes', '&formbuilder=yes'], '', TSJIPPY\currentUrl(true));
                        }

                        ?>
                        <input type='url' class='formbuilder form-element-setting' name='url' value="<?php echo esc_url($url) ?>">
                    </label>
                    <br>

                    <?php
                    //check if we have any upload fields in this form
                    $hideUploadEl    = true;
                    foreach ($this->formElements as $el) {
                        if ($el->type == 'file' || $el->type == 'image') {
                            $hideUploadEl    = false;
                            break;
                        }
                    }
                    ?>
                    <label 
                        class='block 
                        <?php if ($hideUploadEl) echo 'hidden';  ?>'>
                        <h4>Save form uploads in this subfolder of the uploads folder:<br>
                            If you leave it empty the default form_uploads will be used</h4>
                        <input type='text' class='formbuilder form-element-setting' name='upload-path' value='<?php echo esc_url($this->formData->upload_path); ?>'>
                    </label>
                    <br>

                    <h4>Available actions</h4>
                    <?php
                    $actions = ['archive', 'delete'];
                    foreach ($actions as $action) {
                        ?>
                        <label class='option-label'>
                            <input 
                                type='checkbox' 
                                class='formbuilder form-element-setting' 
                                name='actions[<?php echo esc_attr($action); ?>]' 
                                value='<?php echo esc_attr($action); ?>' 
                                <?php if (!empty($this->formData->actions[$action])) echo  'checked';  ?>
                            >
                            <?php echo esc_html(ucfirst($action)); ?>
                        </label><br>
                        <?php
                    }
                    ?>

                    <div class="formsettings-wrapper">
                        <label class="block">
                            <h4>Auto archive results</h4>
                            <br>
                            <label>
                                <input 
                                    type="radio" 
                                    name="autoarchive" 
                                    value="1" 
                                    <?php if ($this->formData->autoarchive) echo 'checked'; ?>
                                >
                                Yes
                            </label>
                            <label>
                                <input 
                                    type="radio" 
                                    name="autoarchive" 
                                    value="0" 
                                    <?php if (!$this->formData->autoarchive) echo 'checked'; ?>
                                >
                                No
                            </label>
                        </label>
                        <br>
                        <div 
                            class='auto-archive-logic 
                            <?php if (!$this->formData->autoarchive) echo 'hidden';  ?>' 
                            style="display: flex;width: 100%;"
                        >
                            Auto archive a (sub) entry when field
                            <select name="autoarchive-el" style="margin-right:10px;">
                                <option 
                                    value='' 
                                    <?php if (empty($this->formData->autoarchive_el)) echo 'selected';?>
                                >
                                    ---
                                </option>
                                <?php

                                $processed = [];
                                foreach ($this->formElements as $key => $element) {
                                    if (isset($this->nonInputs[$element->type])) {
                                        continue;
                                    }

                                    $pattern            = "/\[[0-9]+\]\[([^\]]+)\]/i";

                                    $slug = $element->slug;
                                    if (preg_match($pattern, $element->slug, $matches)) {
                                        //We found a keyword, check if we already got the same one
                                        if (!isset($processed[$matches[1]])) {
                                            //Add to the processed array
                                            $processed[$matches[1]] = 1;

                                            //replace the slug
                                            $slug        = $matches[1];
                                        } else {
                                            //do not show this element
                                            continue;
                                        }
                                    }

                                    //Check which option is the selected one                                    
                                    ?>
                                    <option 
                                        value='<?php echo esc_attr($element->id);?>' 
                                        <?php if (($this->formData->autoarchive_el ?? -1) == $element->id) echo 'selected="selected"';  ?>
                                    >
                                        <?php echo esc_html($slug);?>
                                    </option>
                                    <?php
                                }
                            ?>
                            </select>
                            <label style="margin:0 10px;">
                                equals
                            </label>
                            <input type='text' name="autoarchive-value" value="<?php echo esc_attr($this->formData->autoarchive_value ?? ''); ?>">

                            <?php
                            $this->infoBoxHtml("You can use placeholders like '%today%+3days' for a value");
                            ?>
                        </div>
                    </div>

                    <?php do_action('tsjippy-forms-extra-form-settings', $this); ?>

                    <div style='margin-top:10px;'>
                        <button class='button builder-permissions-rights-form' type='button'>
                            Advanced
                        </button>

                        <div class='permission-wrapper hidden'>
                            <?php
                            // Splitted fields
                            $foundElements = [];
                            foreach ($this->formElements as $key => $element) {
                                if ($element->type == 'multi-start') {
                                    $nextKey    = $key;
                                    while (true) {
                                        $nextKey++;
                                        $nextElement    = $this->formElements[$nextKey];

                                        if (!isset($this->nonInputs[$nextElement->type])) {
                                            $foundElements[$nextElement->slug] = $nextElement->id;
                                        }

                                        if ($nextElement->type == 'multi-end') {
                                            break;
                                        }
                                    }
                                }

                                $pattern = "/([^\[]+)\[[0-9]*\]/i";

                                if (preg_match($pattern, $element->slug, $matches)) {
                                    //Only add if not found before
                                    if (!isset($foundElements[$matches[1]])) {
                                        $foundElements[$matches[1]] = $element->id;
                                    }
                                }
                            }

                            if (!empty($foundElements)) {
                                ?>
                                    <h4>Select fields where you want to create seperate rows for</h4>
                                <?php

                                foreach ($foundElements as $slug => $id) {
                                    $name    = ucfirst(strtolower(str_replace('_', ' ', $slug)));

                                    //Check which option is the selected one
                                    ?>
                                    <label>
                                        <input 
                                            type='checkbox' 
                                            name='split[]' 
                                            value='<?php echo esc_attr($id);?>'
                                            <?php if (in_array($id, $this->formData->split)) echo 'checked';  ?>
                                        >
                                        <?php echo esc_html($name);?>
                                    </label>
                                    <br>
                                    <?php
                                }
                            }
                            ?>

                            <h4>
                                Select roles with form edit rights
                            </h4>
                            <select name='full_right_roles[]' multiple>
                                <option value=''>
                                    ---
                                </option>
                                <?php
                                foreach ($userRoles as $key => $roleName) {
                                    ?>
                                    <option 
                                        value='<?php echo esc_attr($key);?>' 
                                        <?php if (isset($this->formData->full_right_roles[$key])) echo 'selected'; ?>
                                    >
                                        <?php echo esc_html($roleName);?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <br>
                            <h4>Select users with form edit rights</h4>
                            <?php
                            TSJIPPY\userSelect(onlyAdults: true, id: 'full_right_roles', userId: $this->formData->full_right_roles, excludeIds: [1], multiple: true, echo: true);
                            ?>

                            <h4>Select roles who can submit the form on behalve of somebody else</h4>
                            <select name='submit_others_form[]' multiple>
                                <option value=''>---</option>
                                <?php
                                foreach ($userRoles as $key => $roleName) {
                                    ?>
                                    <option 
                                        value='<?php echo esc_attr($key);?>' 
                                        <?php if (isset($this->formData->submit_others_form[$key])) echo 'selected'; ?>
                                    >
                                        <?php echo esc_html($roleName);?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>

                            <h4>Select users who can submit the form on behalve of somebody else</h4>
                            <?php
                            TSJIPPY\userSelect(onlyAdults: true, id: 'submit_others_form', userId: $this->formData->submit_others_form, excludeIds: [1], multiple: true, echo: true);
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                TSJIPPY\addSaveButton('submit-form-setting',  'Save form settings');
                ?>
            </form>
            <form method="POST" style='display: inline-block;'>
                <input type="hidden" name='nonce' value='<?php echo esc_attr(wp_create_nonce('form-export-'.$this->formData->id)); ?>'>
                <button type='submit' class='button' name="export-form" value='<?php echo esc_attr($this->formData->id); ?>'>
                    Export this form
                </button>
            </form>
            <form method="POST" style='display: inline-block;'>
                <input type="hidden" class="no-reset" name="page-id" value='<?php echo esc_attr(get_the_ID()); ?>'>
                <input type="hidden" name='nonce' value='<?php echo esc_attr(wp_create_nonce('form-delete-'.$this->formData->id)); ?>'>
                <button type='submit' class='button' name="delete-form" value='<?php echo esc_attr($this->formData->id); ?>'>
                    Delete this form
                </button>
            </form>
        </div>
        <?php
    }

    /**
     * Form to specify form reminders
     */
    public function formReminderForm()
    {
        $this->getFormReminder();

        /**
         * Show a warning when no e-mail is set
         */
        $this->getEmailSettings();

        $triggerFound = false;
        foreach ($this->emailSettings as $setting) {
            $setting = (object)$setting;

            if ($setting->email_trigger == 'shouldsubmit') {
                $triggerFound    = true;
                break;
            }
        }

        $min = '';
        $max = '';

        if (!$triggerFound) {
        ?>
            <div class='warning'>
                If you define form reminders you should also define an e-mail with the 'The form is due for submission' trigger
            </div>
        <?php
        }

        ?>
        <form action='' method='post' class='tsjippy-form builder' style='margin-top:10px;'>
            <input type='hidden' name='form-id' value='<?php echo esc_attr($this->formData->id); ?>'>

            <?php
            // recurring submission can only happen with forms that are not saved in meta
            if (empty($this->formData->save_in_meta)) {

            ?>
                Enable Recurring Form Submissions
                <label class="switch">
                    <input 
                        type="checkbox" 
                        name="enable" 
                        <?php if (!empty($this->formReminder->frequency)) echo 'checked';  ?>
                    >
                    <span class="slider round"></span>
                </label>
                <br>
                <br>

                <div 
                    class='recurring-submissions 
                    <?php if (empty($this->formReminder->frequency)) echo 'hidden';  ?>'>
                    <label>
                        <h4>Recurring Submissions</h4>
                        Request new form submissions every
                        <input type='number' name='frequency' value='<?php echo esc_attr($this->formReminder->frequency ?? ''); ?>' style='max-width: 70px;'>
                    </label>

                    <?php
                    foreach (['years', 'months', 'days'] as $period) {
                        ?>
                        <label>
                            <input 
                                type='radio' 
                                name='period' 
                                id='period' 
                                value='<?php echo esc_attr($period); ?>' 
                                <?php if ( ($this->formReminder->period ?? '') == $period) echo 'checked'; ?>
                            >
                            <?php echo esc_html($period); ?>
                        </label>
                    <?php
                    }

                    if (!empty($this->formReminder->frequency) && !empty($this->formReminder->period)) {
                        // Selected data can not be in a previous window
                        $min = 'min="' . gmdate("Y-m-d", strtotime("-{$this->formReminder->frequency} {$this->formReminder->period} + 1 day")) . '"';

                        // Selected date cannot be in the newxt window
                        $max = 'max="' . gmdate("Y-m-d", strtotime("+{$this->formReminder->frequency} {$this->formReminder->period} - 1 day")) . '"';
                    }
                    ?>

                    <br>
                    <label>
                        <h4>
                            Date Window
                        </h4>
                        Allow Submissions Within This Date Window<br>
                        From <input type="date" name='window-start' value='<?php echo esc_attr($this->formReminder->window_start ?? ''); ?>' <?php echo esc_attr($min); ?>>
                        To <input type="date" name='window-end' value='<?php echo esc_attr($this->formReminder->window_end ?? ''); ?>' <?php echo esc_attr($max); ?>>
                    </label>
                </div>
            <?php
            }
            ?>

            <label>
                <h4>
                    Reminder Amount
                </h4>
                How many times should people be reminded?<br>
                Leave empty for unlimited.<br>
                Once every
                <?php
                foreach (['week', 'day'] as $period) {
                    ?>
                    <label>
                        <input 
                            type='radio' 
                            name='reminder-period' 
                            id='reminder-period' 
                            value='<?php echo esc_attr($period); ?>' 
                            <?php if (($this->formReminder->reminder_period ?? '') == $period) echo 'checked'; ?>
                        >
                        <?php echo esc_html($period); ?>
                    </label>
                <?php
                }
                ?>
                for <input type="number" name='reminder-amount' value='<?php echo esc_attr($this->formReminder->reminder_amount ?? ''); ?>' style='width: 70px;'>
            </label>
            times.
            <br>
            <label>
                <h4>
                    Start reminding from 
                </h4>
                <input type='date' name='reminder-start_date' value='<?php echo esc_attr($this->formReminder->reminder_start_date ?? ''); ?>' <?php echo esc_attr("$min $max"); ?>>
            </label>

            <h4>
                Warning Exclusions
            </h4>
            <?php $this->warningConditionsForm('conditions', $this->formReminder->conditions ?? []); ?>

            <?php
            TSJIPPY\addSaveButton('submit-form-reminder',  'Save form reminder');
            ?>
        </form>
    <?php
    }

    /**
     * Form to add warning conditions to an element
     *
     * @param    string    $name        The basename for the form conditions inputs.
     * @param    array      $conditions The existing conditions
     */
    public function warningConditionsForm($name, $conditions = [])
    {
        global $wpdb;
        global $wp_roles;

        if(!isset($conditions[0])){
            $conditions[0] = [];
        }

        if(!isset($conditions[0]["meta-key"])){
            $conditions[0]["meta-key"] = '';
        }

        if(!isset($conditions[0]["equation"])){
            $conditions[0]["equation"] = '';
        }

        if (!isset($conditions['roles'])) {
            $conditions['roles']    = [];
        }

        // get all possible user meta keys, not just the one the current user has
        $userMetaKeys = $this->userMetaKeys();

        $userMetas    = get_user_meta($this->user->ID);

        //Get all available roles
        $userRoles    = $wp_roles->role_names;

        //Sort the roles
        asort($userRoles);
        ?>
        <datalist id="meta-key">
            <?php
            foreach ($userMetaKeys as $key => $value) {
                // Value for the current user
                if (isset($userMetas[$key])) {
                    $value    = $userMetas[$key][0];
                } 
                // Value for a random user
                else {
                    $value    = TSJIPPY\getFromDb(
                        "get_meta_values_for_$key",
                        "forms",
                        "SELECT `meta_value` FROM %i WHERE meta_key = %s LIMIT 1",
                        $wpdb->usermeta,
                        $key
                    );
                }

                // Check if array, store array keys
                $data    = '';
                if (is_array($value)) {
                    $keys    = implode(',', array_keys($value));
                    $data    = "data-keys=$keys";
                }
                ?>
                <option value='<?php echo esc_attr($key)?>' <?php echo esc_attr($data);?>>
                <?php
            }

            ?>
        </datalist>
        <label>
            Do not warn if user has role
        </label>
        <select name='<?php echo esc_attr($name); ?>[roles][]' multiple>
            <option value=''>
                ---
            </option>
            <?php
            foreach ($userRoles as $key => $roleName) {
                ?>
                <option  value='<?php echo esc_attr($key);?>' <?php if (isset($conditions['roles'][$key])) echo 'selected'; ?> >
                    <?php echo esc_html($roleName);?>
                </option>
                <?php
            }
            ?>
        </select>
        <br>
        <label>
            Or this user meta evaluation is true
        </label>
        <div class="conditions-wrapper" style='width: 90vw;z-index: 9999;position: relative;'>
            <?php
            foreach ($conditions as $conditionIndex => $condition) {
                if (!is_numeric($conditionIndex)) {
                    continue;
                }

                $arrayKeys    = [];
                if (!empty($condition['meta-key']) && !empty($userMetaKeys[$condition['meta-key']])) {
                    $arrayKeys    = $userMetaKeys[$condition['meta-key']][0];
                }
            ?>
                <div class='warning-conditions element-conditions' data-index='<?php echo esc_attr($conditionIndex); ?>'>
                    <input type="hidden" class="no-reset warning-condition combinator" name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($conditionIndex); ?>][combinator]" value="<?php echo esc_attr($condition['combinator'] ?? ''); ?>">

                    <input type="text" class="warning-condition meta-key" name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($conditionIndex); ?>][meta-key]" value="<?php echo esc_attr($condition['meta-key'] ?? ''); ?>" list="meta-key" style="width: fit-content;">

                    <span 
                        class="index-wrapper 
                        <?php if (empty($condition['meta-key-index'])) echo 'hidden';  ?>">
                        <span>and index</span>
                        <input type="text" class="warning-condition meta-key-index" name='<?php echo esc_attr($name); ?>[<?php echo esc_attr($conditionIndex); ?>][meta-key-index]' value="<?php echo esc_attr($condition['meta-key-index'] ?? ''); ?>" list="meta-key-index[<?php echo esc_attr($conditionIndex); ?>]" style="width: fit-content;">
                        <datalist class="meta-key-index-list warning-condition" id="meta-key-index[<?php echo esc_attr($conditionIndex); ?>]">
                            <?php
                            if (is_array($arrayKeys)) {
                                foreach (array_keys($arrayKeys) as $key) {
                                    ?>
                                    <option value='<?php echo esc_attr($key);?>'>
                                    <?php
                                }
                            }
                            ?>
                        </datalist>
                    </span>

                    <select class="warning-condition inline" name='<?php echo esc_attr($name); ?>[<?php echo esc_attr($conditionIndex); ?>][equation]'>
                        <?php
                        $optionArray    = [
                            ''          => '---',
                            '=='        => 'equals',
                            '!='        => 'is not',
                            '>'         => 'greather than',
                            '<'         => 'smaller than',
                            'submitted' => 'has submitted',
                        ];
                        foreach ($optionArray as $option => $optionLabel) {
                            ?>
                            <option 
                                value='<?php echo esc_attr($option);?>' 
                                <?php if ($condition['equation'] == $option) echo 'selected'; ?>
                            >
                                <?php echo esc_html($optionLabel);?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                    <input 
                        type='text' 
                        class='warning-condition' 
                        name='<?php echo esc_attr($name); ?>[<?php echo esc_attr($conditionIndex); ?>][conditional-value]' 
                        value="<?php echo esc_attr($condition['conditional-value'] ?? ''); ?>" 
                        style="width: fit-content; 
                        <?php if ($condition['equation'] == 'submitted') echo 'visibility:hidden;';  ?>"
                    >

                    <button 
                        type='button' 
                        class='warn-cond button 
                        <?php if (($condition['combinator'] ?? '') == 'and') echo 'active';  ?>' 
                        title='Add a new "AND" rule' 
                        value="and"
                    >
                        AND
                    </button>

                    <button 
                        type='button' 
                        class='warn-cond button 
                        <?php if (($condition['combinator'] ?? '')== 'or') echo 'active';  ?>' 
                        title='Add a new "OR"  rule' 
                        value="or"
                    >
                        OR
                    </button>
                    <button type='button' class='remove-warn-cond  button' title='Remove rule'>
                        -
                    </button>

                    <br>
                </div>
            <?php
            }
            ?>
        </div>
        <?php
    }

    /**
     * Form to setup form e-mails
     */
    public function formEmailsForm()
    {
        $this->getEmailSettings();
        $emails         = $this->emailSettings;
        $defaultFrom    = get_option('admin_email');

        ?>
        <div class="emails-wrapper">
            <form action='' method='post' class='tsjippy-form builder'>
                <div class='form-elements'>
                    <input type='hidden' class='no-reset' class='formbuilder' name='form-id' value='<?php echo esc_attr($this->formData->id); ?>'>

                    <label class="formfield form-label">
                        Define any e-mails you want to send.<br>
                        You can use placeholders in your inputs.<br>
                        These default ones are available:<br><br>
                    </label>
                    <span class='placeholders' title="Click to copy">%id%</span>
                    <?php
                    if (!empty($this->formData->split)) {
                    ?>
                        <span class='placeholders' title="Click to copy">%subid%</span>
                    <?php
                    }
                    ?>
                    <span class='placeholders' title="Click to copy">%formurl%</span>
                    <span class='placeholders' title="Click to copy">%submissiondate%</span>
                    <span class='placeholders' title="Click to copy">%editdate%</span>
                    <span class='placeholders' title="Click to copy">%time_created%</span>
                    <span class='placeholders' title="Click to copy">%time_last_edited%</span>
                    <span class='placeholders' title="Click to copy">%viewhash%</span>(include this in any url send to non-logged in users)
                    <br>
                    All your fieldvalues are available as well:
                    <select class='nonice placeholderselect'>
                        <option value=''>
                            Select to copy to clipboard
                        </option>
                        <?php
                        foreach ($this->formElements as $element) {
                            $element->slug    = str_replace('[]', '', $element->slug);
                            if (!isset(['label' => 1, 'info' => 1, 'button' => 1, 'datalist' => 1, 'formstep' => 1][$element->type])) {
                                ?>
                                <option>
                                    %<?php echo esc_attr($element->slug);?>%
                                </option>
                                <?php
                            }
                        }
                        do_action('tsjippy-forms-add-email-placeholder-option', $this);
                        ?>
                    </select>

                    <br>
                    <div class='clone-divs-wrapper'>
                        <?php
                        // Render tab buttons
                        foreach ($emails as $key => $email) {
                            $nr        = $key + 1;

                            

                            ?>
                            <button 
                                class='button tablink formbuilder-form 
                                <?php if ($key === 0) echo  'active';  ?>' 
                                type='button' 
                                id='show-email-<?php echo esc_attr($key);?>' 
                                data-target='email-<?php echo esc_attr($key);?>' 
                                style='margin-right:4px;'
                            >
                                E-mail <?php echo esc_attr($nr);?>
                            </button>
                            <?php
                        }

                        // Render tab contents
                        foreach ($emails as $key => $email) {
                            $email     = (object) $email;

                            $hidden    = 'hidden';
                            if ($key === 0) {
                                $hidden = '';
                            }

                            $triggerElementId       = $email->submitted_trigger['element'] ?? '';
                            $triggerEquation        = $email->submitted_trigger['equation'] ?? '';
                            $triggerValue           = $email->submitted_trigger['value'] ?? '';
                            $triggerValueElementId  = $email->submitted_trigger['value-element'] ?? '';

                        ?>
                            <div class='clone-div tabcontent <?php echo esc_attr($hidden); ?>' id="email-<?php echo esc_attr($key); ?>" data-div-id='<?php echo esc_attr($key); ?>'>
                                <h4 class="formfield" style="margin-top:50px; display:inline-block;">
                                    E-mail <?php echo esc_attr($key + 1); ?>
                                </h4>
                                <button type='button' class='add button' style='flex: 1;'>
                                    +
                                </button>
                                <button type='button' class='remove button' style='flex: 1;'>
                                    -
                                </button>
                                <div style='width:100%;'>
                                    <input type='hidden' class='no-reset' name='emails[<?php echo esc_attr($key); ?>][email-id]' value='<?php echo esc_attr($email->id ?? ''); ?>'>
                                    <input type='hidden' class='no-reset' name='emails[<?php echo esc_attr($key); ?>][form-id]' value='<?php echo esc_attr($email->form_id ?? ''); ?>'>

                                    <div class="formfield form-label" style="margin-top:10px;">
                                        <h4>Trigger</h4>
                                        Send e-mail when:<br>
                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]' 
                                                class='email-trigger' 
                                                value='submitted' 
                                                <?php if (($email->email_trigger ?? '') == 'submitted') echo 'checked';  ?>
                                            >
                                            The form is submitted
                                        </label>
                                        <br>

                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]' 
                                                class='email-trigger' 
                                                value='shouldsubmit' 
                                                <?php if (($email->email_trigger ?? '') == 'shouldsubmit') echo 'checked';  ?>
                                            >
                                            The form is due for submission
                                        </label><br>

                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]' 
                                                class='email-trigger' 
                                                value='submittedcond' 
                                                <?php if (($email->email_trigger ?? '') == 'submittedcond') echo 'checked';  ?>
                                            >
                                            The form is submitted and meets a condition
                                        </label><br>

                                        <div 
                                            class='submitted-type 
                                            <?php if (($email->email_trigger ?? '') != 'submittedcond') echo 'hidden';  ?>'>
                                            <div class='submitted-trigger-type'>
                                                Element
                                                <select class='' name='emails[<?php echo esc_attr($key); ?>][submitted-trigger][element]'>
                                                    <?php
                                                    $this->inputDropdown($triggerElementId, "emails[$key][submitted-trigger']['element']");
                                                    ?>
                                                </select>

                                                <select class='' name='emails[<?php echo esc_attr($key); ?>][submitted-trigger][equation]'>
                                                    <?php
                                                    $optionArray    = [
                                                        ''         => '---',
                                                        '=='       => 'equals',
                                                        '!='       => 'is not',
                                                        '>'        => 'greather than',
                                                        '<'        => 'smaller than',
                                                        'checked'  => 'is checked',
                                                        '!checked' => 'is not checked',
                                                        '== value' => 'equals the value of',
                                                        '!= value' => 'does not equal the value of',
                                                        '> value'  => 'greather than the value of',
                                                        '< value'  => 'smaller than the value of'
                                                    ];

                                                    foreach ($optionArray as $option => $optionLabel) {
                                                        ?>
                                                        <option 
                                                            value='<?php echo esc_attr($option);?>' 
                                                            <?php if ($triggerEquation == $option) echo 'selected'; ?>
                                                        >
                                                            <?php echo esc_html($optionLabel);?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>

                                                <label 
                                                    class='staticvalue 
                                                    <?php if (empty($triggerEquation) || !isset(['==' => 1, '!=' => 1, '>' => 1, '<' => 1][$triggerEquation])) echo 'hidden';  ?>'
                                                >
                                                    <input type='text' name='emails[<?php echo esc_attr($key); ?>][submitted-trigger][value]' value="<?php echo esc_attr($triggerValue); ?>" style='width: auto;'>
                                                </label>

                                                <select 
                                                    class='dynamicvalue 
                                                    <?php if (empty($triggerEquation) || isset(['==' => 1, '!=' => 1, '>' => 1, '<' => 1, 'checked' => 1, '!checked' => 1][$triggerEquation])) echo 'hidden';  ?>' 
                                                    name='emails[<?php echo esc_attr($key); ?>][submitted-trigger][value-element]'
                                                >
                                                    <?php
                                                    $this->inputDropdown($triggerValueElementId, "emails[$key][submitted-trigger][value-element]");
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]' 
                                                class='email-trigger' 
                                                value='fieldchanged' 
                                                <?php if (($email->email_trigger ?? '') == 'fieldchanged') echo 'checked';  ?>
                                            >
                                            A field has changed to a value
                                        </label>
                                        <div 
                                            class='conditional-field-wrapper 
                                            <?php if (($email->email_trigger ?? '') != 'fieldchanged') echo 'hidden';  ?>'
                                        >
                                            <label class="formfield form-label">Field</label>
                                            <select name='emails[<?php echo esc_attr($key); ?>][conditional-field]'>
                                                <?php
                                                $this->inputDropdown($email->conditional_field ?? '');
                                                ?>
                                            </select>

                                            <label class="formfield form-label">
                                                Value
                                                <input type='text' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][conditional-value]' value="<?php echo esc_attr($email->conditional_value ?? ''); ?>" style='width:fit-content;'>
                                            </label>
                                        </div>

                                        <br>
                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]' 
                                                class='email-trigger' 
                                                value='fieldschanged' 
                                                <?php if (($email->email_trigger ?? '') == 'fieldschanged') echo 'checked';  ?>
                                            >
                                            One or more fields have changed
                                        </label>
                                        <div 
                                            class='conditional-fields-wrapper 
                                            <?php if (($email->email_trigger ?? '') != 'fieldschanged') echo 'hidden';   ?>'>
                                            <label class="formfield form-label">Field(s)</label>
                                            <select name='emails[<?php echo esc_attr($key); ?>][conditional-fields][]' multiple='multiple'>
                                                <?php
                                                $this->inputDropdown($email->conditional_fields ?? []);
                                                ?>
                                            </select>
                                        </div>

                                        <br>
                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]' 
                                                class='email-trigger' 
                                                value='removed' 
                                                <?php if (($email->email_trigger ?? '') == 'removed') echo 'checked';  ?>
                                            >
                                            The submission is archived or deleted
                                        </label>
                                        <br>
                                        <?php do_action('tsjippy-forms-after-email-triggers', $key, $email); ?>
                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]' 
                                                class='email-trigger' value='disabled' 
                                                <?php if (($email->email_trigger ?? '') == 'disabled') echo 'checked';  ?>
                                            >
                                            Do not send this e-mail
                                        </label>
                                        <br>
                                    </div>

                                    <br>
                                    <div class="formfield form-label">
                                        <h4>Sender address</h4>
                                        Sender e-mail should be:<br>
                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][from-email]' 
                                                class='from-email' value='fixed' 
                                                <?php if (empty($email->from_email) || $email->from_email == 'fixed') echo 'checked';  ?>>
                                            Fixed e-mail adress
                                        </label><br>
                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][from-email]' 
                                                class='from-email' value='conditional' 
                                                <?php if (($email->from_email ?? '') == 'conditional') echo 'checked';  ?>>
                                            Conditional e-mail adress
                                        </label><br>
                                    </div>

                                    <div class='emailfromfixed <?php if (!empty($email->from_email) && $email->from_email != 'fixed') echo 'hidden'; ?>'>
                                        <label class="formfield form-label">
                                            From e-mail
                                            <input 
                                                type='text' 
                                                class='formbuilder form-element-setting' 
                                                name='emails[<?php echo esc_attr($key); ?>][from]' 
                                                value="<?php if (empty($email->from))   esc_attr($defaultFrom);  else echo esc_attr($email->from); ?>">
                                        </label>
                                    </div>

                                    <div 
                                        class='emailfromconditional 
                                        <?php if (($email->from_email ?? '') != 'conditional') echo 'hidden';  ?>'>
                                        <div class='clone-divs-wrapper'>
                                            <?php
                                            if (!is_array($email->conditional_from_email ?? '')) {
                                                $email->conditional_from_email = [
                                                    [
                                                        'fieldid'    => '',
                                                        'value'        => '',
                                                        'email'        => ''
                                                    ]
                                                ];
                                            }
                                            foreach (array_values(($email->conditional_from_email ?? [] )) as $fromKey => $fromEmail) {
                                            ?>
                                                <div class='clone-div' data-div-id='<?php echo esc_attr($fromKey); ?>'>
                                                    <fieldset class='form-email-fieldset'>
                                                        <legend class="formfield button-wrapper">
                                                            <span class='text'>Condition <?php echo esc_attr($fromKey + 1); ?></span>
                                                            <button type='button' class='add button' style='flex: 1;'>+</button>
                                                            <button type='button' class='remove button' style='flex: 1;'>-</button>
                                                        </legend>
                                                        If
                                                        <select name='emails[<?php echo esc_attr($key); ?>][conditional-from-email][<?php echo esc_attr($fromKey); ?>][fieldid]'>
                                                            <?php
                                                            $this->inputDropdown($fromEmail['fieldid']);
                                                            ?>
                                                        </select>
                                                        <label class="formfield form-label">
                                                            equals
                                                            <input type='text' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][conditional-from-email][<?php echo esc_attr($fromKey); ?>][value]' value="<?php echo esc_attr($fromEmail['value']); ?>">
                                                        </label>
                                                        <label class="formfield form-label">
                                                            then from e-mail address should be:<br>
                                                            <input type='email' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][conditional-from-email][<?php echo esc_attr($fromKey); ?>][email]' value="<?php echo esc_attr($fromEmail['email']); ?>">
                                                        </label>
                                                    </fieldset>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                            <br>
                                            <label class="formfield form-label">
                                                Else the e-mail will be
                                                <input type='text' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][else-from]' value="<?php echo esc_attr($email->else_from ?? ''); ?>">
                                            </label>
                                        </div>
                                    </div>

                                    <br>
                                    <h4>Recipient address</h4>
                                    <div class="formfield tofieldlabel">
                                        Recipient e-mail should be:<br>
                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-to]' 
                                                class='email-to' 
                                                value='fixed' 
                                                <?php if (empty($email->email_to) || $email->email_to == 'fixed') echo 'checked';  ?>>
                                            Fixed e-mail adress
                                        </label><br>
                                        <label>
                                            <input 
                                                type='radio' 
                                                name='emails[<?php echo esc_attr($key); ?>][email-to]' 
                                                class='email-to' 
                                                value='conditional' 
                                                <?php if ($email->email_to == 'conditional') echo 'checked';   ?>>
                                            Conditional e-mail adress
                                        </label><br>
                                    </div>
                                    <br>
                                    <div 
                                        class='email-tofixed 
                                        <?php if (!empty($email->email_to) && $email->email_to != 'fixed') echo 'hidden';   ?>'>
                                        <label class="formfield form-label">
                                            To e-mail
                                            <input 
                                                type='text' 
                                                class='formbuilder form-element-setting' 
                                                name='emails[<?php echo esc_attr($key); ?>][to]' 
                                                value="<?php if (empty($email->to)) echo '%email%';   else echo esc_attr($email->to); ?>">
                                        </label>
                                    </div>

                                    <div 
                                        class='email-toconditional 
                                        <?php if (($email->email_to ?? '') != 'conditional') echo 'hidden';  ?>'>
                                        <div class='clone-divs-wrapper'>
                                            <?php
                                            if (!is_array($email->conditional_email_to ?? '')) {
                                                $email->conditional_email_to = [
                                                    [
                                                        'fieldid'    => '',
                                                        'value'        => '',
                                                        'email'        => ''
                                                    ]
                                                ];
                                            }

                                            foreach (($email->conditional_email_to ?? []) as $toKey => $toEmail) {
                                            ?>
                                                <div class='clone-div' data-div-id='<?php echo esc_attr($toKey); ?>'>
                                                    <fieldset class='form-email-fieldset button-wrapper'>
                                                        <legend class="formfield">
                                                            <span class='text'>Condition <?php echo esc_attr($toKey + 1); ?></span>
                                                            <button type='button' class='add button' style='flex: 1;'>
                                                                +
                                                            </button>
                                                            <button type='button' class='remove button' style='flex: 1;'>
                                                                -
                                                            </button>
                                                        </legend>
                                                        If
                                                        <select name='emails[<?php echo esc_attr($key); ?>][conditional-email-to][<?php echo esc_attr($toKey); ?>][fieldid]'>
                                                            <?php
                                                            $this->inputDropdown($toEmail['fieldid']);
                                                            ?>
                                                        </select>
                                                        <label class="formfield form-label">
                                                            equals
                                                            <input type='text' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][conditional-email-to][<?php echo esc_attr($toKey); ?>][value]' value="<?php echo esc_attr($toEmail['value']); ?>">
                                                        </label>
                                                        <label class="formfield form-label">
                                                            then from e-mail address should be:<br>
                                                            <input type='email' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][conditional-email-to][<?php echo esc_attr($toKey); ?>][email]' value="<?php echo esc_attr($toEmail['email']); ?>">
                                                        </label>
                                                    </fieldset>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                            <br>
                                            <label class="formfield form-label">
                                                Else the e-mail will be
                                                <input type='text' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][else-to]' value="<?php echo esc_attr($email->else_to ?? ''); ?>">
                                            </label>
                                        </div>
                                    </div>

                                    <br>
                                    <div class="formfield form-label">
                                        <h4>Subject</h4>
                                        <input type='text' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][subject]' value="<?php echo esc_attr($email->subject ?? '') ?>">
                                    </div>

                                    <br>
                                    <div class="formfield form-label">
                                        <h4>Content</h4>
                                        <?php
                                        $settings = array(
                                            'wpautop' => false,
                                            'media_buttons' => false,
                                            'forced_root_block' => true,
                                            'convert_newlines_to_brs' => true,
                                            'textarea_name' => "emails[$key][message]",
                                            'textarea_rows' => 10
                                        );

                                        wp_editor(
                                            $email->message ?? '',
                                            "{$this->formData->slug}_email_message_$key",
                                            $settings
                                        );
                                        ?>
                                    </div>

                                    <br>
                                    <div class="formfield form-label">
                                        <h4>
                                            Additional headers like 'Reply-To'
                                        </h4>
                                        <textarea class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][headers]'><?php
                                             echo trim(wp_kses_post($email->headers ?? '')); 
                                        ?></textarea>
                                    </div>

                                    <br>
                                    <div class="formfield form-label">
                                        <h4>
                                            Attachments
                                        </h4>
                                        Form values that should be attached to the e-mail
                                        <textarea class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][files]'><?php
                                             echo wp_kses_post($email->files ?? ''); 
                                        ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
                TSJIPPY\addSaveButton('submit-form-emails', 'Save form email configuration');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Form to add or edit a new form element
     */
    public function elementBuilderForm($element = null)
    {
        $heading    = "Please fill in the form to add a new form element";

        if (is_numeric($element)) {
            $element = $this->getElementById($element);

            if ($element) {
                $heading    = "Change this element";
            } else {
                $element    = null;
            }
        }

        $nonInputClasses    = 'non-' . implode(' non-', $this->nonInputs);

        ob_start();
        ?>
        <div class="form-wrapper">
            <h4><?php echo wp_kses_post($heading); ?></h4><br>

            <input type="hidden" class="no-reset" name="form-id" value="<?php echo esc_attr($this->formData->id); ?>">

            <input type="hidden" class="no-reset" name="formfield[form-id]" value="<?php echo esc_attr($this->formData->id); ?>">

            <input 
                type="hidden" 
                class="no-reset" 
                name="element-id" 
                value="<?php if ($element != null) echo esc_attr($element->id);   ?>">

            <input type="hidden" class="no-reset" name="insert-after">

            <input type="hidden" class="no-reset" name="formfield[width]" value="100">

            <label>Element type</label><br>
            <select class="formbuilder element-type" name="formfield[type]" required>
                <optgroup label="Normal elements">
                    <?php
                    $options = [
                        "button"   => "Button",
                        "checkbox" => "Checkbox",
                        "color"    => "Color",
                        "date"     => "Date",
                        "select"   => "Dropdown",
                        "email"    => "E-mail",
                        "file"     => "File upload",
                        "image"    => "Image upload",
                        "label"    => "Label",
                        "month"    => "Month",
                        "number"   => "Number",
                        "password" => "Password",
                        "tel"      => "Phonenumber",
                        "radio"    => "Radio",
                        "range"    => "Range",
                        "text"     => "Text",
                        "textarea" => "Text (multiline)",
                        "time"     => "Time",
                        "url"      => "Url",
                        "week"     => "Week"
                    ];

                    foreach ($options as $key => $option) {
                        ?>
                        <option 
                            value='<?php echo esc_attr($key); ?>' 
                            <?php if ($element != null && $element->type == $key) echo 'selected="selected"';   ?>
                        >
                            <?php echo wp_kses_post($option); ?>
                        </option>
                        <?php
                    }
                    ?>
                </optgroup>
                <optgroup label="Special elements">
                    <?php
                    $options    = [
                        "hcaptcha"        => "hCaptcha",
                        "recaptcha"       => "reCaptcha",
                        "turnstile"       => "Cloudflare Turnstile",
                        "datalist"        => "Datalist",
                        "div-start"       => "Div Container - start",
                        "div-end"         => "Div Container - end",
                        "formstep"        => "Multistep",
                        "info"            => "Infobox",
                        "multi-start"     => "Multi-answer - start",
                        "multi-end"       => "Multi-answer - end",
                        "p"               => "Paragraph",
                        "php"             => "Custom code"
                    ];

                    $options    = apply_filters('tsjippy-forms-special-form-elements', $options);

                    foreach ($options as $key => $option) {
                        ?>
                        <option 
                            value='<?php echo esc_attr($key); ?>' 
                            <?php if ($element != null && $element->type == $key) echo 'selected="selected"';   ?>>
                            <?php echo wp_kses_post($option); ?>
                        </option>
                        <?php
                    }
                    ?>
                </optgroup>
            </select>
            <br>

            <div name='elementname' class='element-option wide reverse not-label not-php not-formstep button shouldhide' style='background-color: unset;'>
                <label>
                    <div style='text-align: left;'>Specify a name for the element</div>
                    <input type="text" class="formbuilder wide" name="formfield[name]" value="<?php echo esc_attr($element->slug ?? ''); ?>">
                </label>
                <br><br>
            </div>

            <div name='add-text' class='element-option multi-start shouldhide'>
                <label>
                    <div style='text-align: left;'>Specify the text for the 'add' button</div>
                    <input type="text" class="formbuilder wide" name="formfield[add]" value="<?php echo esc_attr($element->add ?? '+'); ?>">
                </label>
                <br><br>
            </div>

            <div name='remove-text' class='element-option multi-start shouldhide'>
                <label>
                    <div style='text-align: left;'>Specify the text for the 'remove' button</div>
                    <input type="text" class="formbuilder wide" name="formfield[remove]" value="<?php echo esc_attr($element->remove ?? '-'); ?>">
                </label>
                <br><br>
            </div>

            <div name='function_name' class='element-option wide hidden php'>
                <label>
                    Specify the function_name
                    <input type="text" class="formbuilder wide" name="formfield[function_name]" value="<?php echo esc_attr($element->function_name ?? ''); ?>">
                </label>
                <br><br>
            </div>

            <div name='label-text' class='element-option label button formstep hidden wide' style='background-color: unset;'>
                <label>
                    <div style='text-align: left;'>
                        Specify the 
                        <span class='element-type'>
                            label
                        </span>
                         text
                    </div>
                    <input type="text" class="formbuilder wide" name="formfield[text]" value="<?php echo esc_attr($element->text ?? ''); ?>">
                </label>
                <br><br>
            </div>

            <div name='upload-options' class='element-option hidden file image'>
                <label>
                    <input 
                        type="checkbox" 
                        class="formbuilder" 
                        name="formfield[library]" 
                        value="1" 
                        <?php if ($element != null && $element->library) echo 'checked';  ?>>
                    Add the <span class='filetype'>file</span> to the library
                </label>
                <br><br>

                <label>
                    <input 
                        type="checkbox" 
                        class="formbuilder" 
                        name="formfield[edit_image]" 
                        value="1" 
                        <?php if ($element != null && $element->edit_image) echo 'checked';  ?>>
                    Allow people to edit an image before uploading it
                </label>
                <br>
                <br>

                <label>
                    Name of the folder the <span class='filetype'>file</span> should be uploaded to.<br>
                    <input type="text" class="formbuilder" name="formfield[folder_name]" value="<?php echo esc_attr($element->folder_name ?? ''); ?>">
                </label>
            </div>

            <div name='wrap' class='element-option reverse not-p not-php not-file not-image not-multi-start not-multi-end shouldhide'>
                <label>
                    <input 
                        type="checkbox" 
                        class="formbuilder" 
                        name="formfield[wrap]" 
                        value="1" 
                        <?php if ($element != null && $element->wrap) echo 'checked';  ?>>
                    Group together with next element
                </label>
                <br><br>
            </div>

            <div name='infotext' class='element-option info p hidden'>
                <label>
                    Specify the text for the <span class='type'>info-box</span>
                    <?php
                    $settings = array(
                        'wpautop'                 => false,
                        'media_buttons'           => false,
                        'forced_root_block'       => true,
                        'convert_newlines_to_brs' => true,
                        'textarea_name'           => "formfield[infotext]",
                        'textarea_rows'           => 10,
                        'editor_class'            => 'formbuilder'
                    );

                    if (empty($element->text)) {
                        $content    = '';
                    } else {
                        $content    = $element->text;
                    }
                    wp_editor(
                        $content,
                        $this->formData->slug . "_infotext",    //editor should always have an unique id
                        $settings
                    );
                    ?>

                </label>
                <br>
            </div>

            <div name='multiple' class='element-option reverse <?php echo esc_attr($nonInputClasses); ?> shouldhide'>
                <label>
                    <input 
                        type="checkbox" 
                        class="formbuilder" 
                        name="formfield[multiple]" 
                        value="1" 
                        <?php if ($element != null && $element->multiple) echo 'checked';  ?>>
                    Allow multiple answers
                </label>
                <br>
                <br>
            </div>

            <div name='value_list' class='element-option datalist radio select checkbox hidden'>
                <label>
                    Specify the values, one per line
                    <textarea class="formbuilder" name="formfield[value_list]"><?php echo esc_attr(trim($element->value_list ?? '')); ?></textarea>
                </label>
                <br>
            </div>

            <div name='select-options' class='element-option datalist radio select checkbox multi-answer-element hidden'>
                <label class='block'>Specify an options group if desired</label>
                <select class="formbuilder" name="formfield[default-array-value]">
                    /*<option value="">---</option>*/
                    <?php
                    $this->buildDefaultsArray();
                    foreach ($this->defaultArrayValues as $key => $field) {
                    ?>
                        <option 
                            value='<?php echo esc_attr($key); ?>' 
                            <?php if (($element->default_array_value ?? '') == $key) echo 'selected="selected"';  ?>>
                            <?php echo esc_attr(ucfirst(str_replace('_', ' ', $key))); ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>

            <div name='defaults' class='element-option reverse not-php not-file <?php echo esc_attr($nonInputClasses); ?> shouldhide'>
                <label class='block'>
                    Specify a default value if desired
                </label>

                <input type='text' class="formbuilder" name="formfield[default-value]" list='defaults' value='<?php echo esc_attr(trim($element->default_value ?? '')); ?>'>

                <datalist id='defaults'>
                    <?php
                    foreach ($this->defaultValues as $key => $field) {
                    ?>
                        <option value='<?php echo esc_attr($key); ?>'>
                            <?php echo esc_attr(ucfirst(str_replace('_', ' ', $key))); ?>
                        </option>
                    <?php
                    }
                    ?>
                </datalist>
            </div>

            <?php
            do_action('tsjippy-forms-after-formbuilder-element-options', $element);
            ?>
            <br>
            <div name='element-options' class='element-option reverse not-php <?php echo esc_attr($nonInputClasses); ?> shouldhide'>
                <label>
                    Specify any options like styling
                    <textarea class="formbuilder" name="formfield[options]"><?php echo esc_attr(trim($element->options ?? '')); ?></textarea>
                </label><br>
                <br>

                <?php
                if (!empty($this->formData->save_in_meta)) {
                ?>
                    <h3>Warning conditions</h3>
                    <label class="option-label">
                        <input 
                            type="checkbox" 
                            class="formbuilder" 
                            name="formfield[mandatory]" value="1" 
                            <?php if ($element != null && $element->mandatory) echo 'checked';   ?>>
                        People should be warned by e-mail/signal if they have not filled in this field.
                    </label><br>
                    <br>

                    <label class="option-label">
                        <input 
                            type="checkbox" 
                            class="formbuilder" 
                            name="formfield[recommended]" 
                            value="1" 
                            <?php if ($element != null && $element->recommended) echo 'checked';  ?>>
                        People should be notified on their homepage if they have not filled in this field.
                    </label><br>
                    <br>

                    <div <?php if ($element == null || (!$element->mandatory && !$element->recommended)) echo "class='hidden'"; ?>>
                        <?php
                        if ($element == null) {
                            $conditions    = [];
                        } else {
                            $conditions = $element->warning_conditions;
                        }
                        $this->warningConditionsForm('formfield[warning-conditions]', $conditions);
                        ?>
                    </div>
                <?php
                } else {
                ?>
                    <label class="option-label">
                        <input 
                            type="checkbox"
                            class="formbuilder" 
                            name="formfield[required]" 
                            value="1" 
                            <?php if ($element != null && $element->required) echo 'checked';  ?>>
                        This should be a required field
                    </label><br>
                    <label class="option-label">
                        <input 
                            type="checkbox" 
                            class="formbuilder" 
                            name="formfield[mandatory]" 
                            value="1" 
                            <?php if ($element != null && $element->mandatory) echo 'checked';  ?>>
                        This should be a conditional required field: its only required when visible
                    </label><br>
                    <br>
                <?php
                }
                ?>
            </div>
            <label class="option-label element-option not-multi-start not-multi-end reverse shouldhide">
                <input 
                    type="checkbox" 
                    class="formbuilder" 
                    name="formfield[hidden]" 
                    value="1" 
                    <?php if ($element != null && $element->hidden) echo 'checked';  ?>>
                Hidden field
            </label><br>

            <?php
            if ($element == null) {
                $text    = "Add";
            } else {
                $text    = "Change";
            }
            ?>
        </div>
        <?php

        /**
         * Filters the form contents of adding or editing an element
         *
         * @param    string    $html        The form html
         * @param    object    $object        The FormBuilderForm Instance
         * @param    object    $element    The current element which is edited
         */
        $formContents    = apply_filters('tsjippy-forms-element-form-content', ob_get_clean(), $this, $element);

        ob_start();
        ?>
        <form action="" method="post" name="add-form-element-form" class="form-element-form tsjippy-form" data-add-empty=1>
            <div style="display: none;" class="error"></div>
            <?php

            // phpcs:ignore
            echo $formContents;

            TSJIPPY\addSaveButton('submit-form-element', "$text form element"); ?>
        </form>
        <?php

        return ob_get_clean();
    }

    /**
     * Form to add conditions to an element
     *
     * A field can have one or more conditions applied to it like:
     *        1) hide when field X is Y
     *        2) Show when field X is Z
     *    Each condition can have multiple rules like:
     *        Hide when field X is Y and field A is B
     *
     *    The array structure is therefore:
     *        [
     *            [0][
     *                [rules]
     *                        [0]
     *                        [1]
     *                [action]
     *            [1]
     *                [rules]
     *                        [0]
     *                [action]
     *        ]
     *
     *    It is also stored at the conditional fields to be able to create efficient JavaScript
     *
     * @param int    $elementId    The id of the element. Default -1 for empty
     */
    public function elementConditionsForm($elementId = -1)
    {
        $element    = null;
        if ($elementId != -1) {
            $element    = $this->getElementById($elementId);
        }

        if ($elementId == -1 || empty($element->conditions)) {
            if ($elementId == -1 || gettype($element) != 'object') {
                $element    = new stdClass();
            }

            $dummyFieldCondition['rules'][0]["conditional-field"]   = "";
            $dummyFieldCondition['rules'][0]["equation"]            = "";
            $dummyFieldCondition['rules'][0]["conditional-field-2"] = "";
            $dummyFieldCondition['rules'][0]["equation-2"]          = "";
            $dummyFieldCondition['rules'][0]["conditional-value"]   = "";
            $dummyFieldCondition["action"]                          = "";
            $dummyFieldCondition["target_field"]                    = "";

            if ($elementId == -1) {
                $elementId            = 0;
            }
            $element->conditions = [$dummyFieldCondition];
        }

        $conditions = $element->conditions;

        ob_start();
        $counter = 0;
        foreach ($this->formElements as $el) {
            $copyTo    = $el->conditions;
            if (in_array($elementId, $copyTo['copyto'] ?? [])) {
                $counter++;
                ?>
                <div class="form-element-wrapper" data-element-id="<?php echo esc_attr($el->id); ?>" data-form-id="<?php echo esc_attr($this->formData->id); ?>">
                    <button type="button" class="edit-form-element button" title="Jump to conditions element">
                        View conditions of '<?php echo esc_attr($el->slug); ?>'
                    </button>
                </div>
            <?php
            }
        }

        if ($counter > 0) {
            $jumbButtonHtml =  ob_get_clean();
            if ($counter == 1) {
                $counter    = 'another element';
                $any         = 'the';
            } else {
                $counter    = "$counter other elements";
                $any         = 'any';
            }

            ob_start();
            ?>
            <div>
                This element has some conditions defined by <?php echo esc_attr($counter); ?>.<br>
                Click on <?php echo esc_attr($any); ?> button below to view.
                <?php echo wp_kses_post($jumbButtonHtml); ?>
            </div><br><br>
        <?php
        }
        ?>

        <form action='' method='post' name='add-form-element-conditions-form'>
            <h3>Form element conditions</h3>
            <input type='hidden' class='no-reset' class='element-condition' name='form-id' value='<?php echo esc_attr($this->formData->id); ?>'>

            <input type='hidden' class='no-reset' class='element-condition' name='elementid' value='<?php echo esc_attr($elementId); ?>'>

            <?php
            // get the last numeric array key
            $numericKeys    = array_filter(array_keys($conditions), 'is_int');
            $lastCondtionKey = end($numericKeys);

            foreach ($conditions as $conditionIndex => $condition) {
                if (!is_numeric($conditionIndex)) {
                    continue;
                }
            ?>
                <div class='condition-row' data-condition-index='<?php echo esc_attr($conditionIndex); ?>'>
                    <span style='font-weight: 600;'>If</span>
                    <br>
                    <?php
                    $lastRuleKey = array_key_last($condition['rules']);
                    foreach ($condition['rules'] as $ruleIndex => $rule) {
                    ?>
                        <div class='rule-row' data-rule-index='<?php echo esc_attr($ruleIndex); ?>'>
                            <input type='hidden' class='no-reset element-condition combinator' name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][rules][<?php echo esc_attr($ruleIndex); ?>][combinator]' value='<?php echo esc_attr($rule['combinator'] ?? ''); ?>'>

                            <select class='element-condition condition-select conditional-field' name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][rules][<?php echo esc_attr($ruleIndex); ?>][conditional-field]' required>
                                <?php
                                $this->inputDropdown($rule['conditional-field'], $elementId);
                                ?>
                            </select>

                            <select class='element-condition condition-select equation' name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][rules][<?php echo esc_attr($ruleIndex); ?>][equation]' required>
                                <?php
                                $optionArray    = [
                                    ''          => '---',
                                    'changed'   => 'has changed',
                                    'clicked'   => 'is clicked',
                                    '=='        => 'equals',
                                    '!='        => 'is not',
                                    '>'         => 'greather than',
                                    '<'         => 'smaller than',
                                    'checked'   => 'is checked',
                                    '!checked'  => 'is not checked',
                                    '== value'  => 'equals the value of',
                                    '!= value'  => 'does not equal the value of',
                                    '> value'   => 'greather than the value of',
                                    '< value'   => 'smaller than the value of',
                                    '-'         => 'minus the value of',
                                    '+'         => 'plus the value of',
                                    'visible'   => 'is visible',
                                    'invisible' => 'is not visible',
                                ];

                                foreach ($optionArray as $option => $optionLabel) {
                                    ?>
                                    <option 
                                        value='<?php echo esc_attr($option); ?>' 
                                        <?php if ($rule['equation'] == $option) echo 'selected="selected"';  ?>>
                                        <?php echo esc_attr($optionLabel); ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>

                            <?php
                            //show if -, + or value field is target value
                            if ($rule['equation'] == '-' || $rule['equation'] == '+' || str_contains($rule['equation'], 'value')) {
                                $hidden = '';
                            } else {
                                $hidden = 'hidden';
                            }
                            ?>

                            <span class='<?php echo esc_attr($hidden); ?> condition-form conditional-field-2'>
                                <select class='element-condition condition-select' name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][rules][<?php echo esc_attr($ruleIndex); ?>][conditional-field-2]'>
                                    <?php
                                    $this->inputDropdown($rule['conditional-field-2'], $elementId);
                                    ?>
                                </select>
                            </span>

                            <?php
                            if ($rule['equation'] == '-' || $rule['equation'] == '+') {
                                $hidden = '';
                            } else {
                                $hidden = 'hidden';
                            }
                            ?>

                            <span class='<?php echo esc_attr($hidden); ?> condition-form equation-2'>
                                <select class='element-condition condition-select' name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][rules][<?php echo esc_attr($ruleIndex); ?>][equation-2]'>
                                    <?php
                                    $optionArray    = [
                                        ''            => '---',
                                        '=='        => 'equals',
                                        '!='        => 'is not',
                                        '>'            => 'greather than',
                                        '<'            => 'smaller than',
                                    ];
                                    foreach ($optionArray as $option => $optionLabel) {
                                        ?>
                                        <option 
                                            value='<?php echo esc_attr($option); ?>' 
                                            <?php if ($rule['equation-2'] == $option) echo 'selected="selected"';  ?>>
                                            <?php echo esc_attr($optionLabel); ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </span>
                            <?php
                            if (str_contains($rule['equation'], 'value') || isset(['changed' => 1, 'checked' => 1, '!checked' => 1, 'visible' => 1, 'invisible' => 1][$rule['equation']])) {
                                $hidden = 'hidden';
                            } else {
                                $hidden = '';
                            }
                            ?>
                            <input type='text' class='<?php echo esc_attr($hidden); ?> element-condition condition-form' name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][rules][<?php echo esc_attr($ruleIndex); ?>][conditional-value]' value="<?php echo esc_attr($rule['conditional-value']); ?>">

                            <button 
                                type='button' 
                                class='element-condition and-rule condition-form button 
                                <?php if (!empty($rule['combinator']) && $rule['combinator'] == 'AND') echo 'active';  ?>' 
                                title='Add a new "AND" rule to this condition'>
                                AND
                            </button>

                            <button 
                                type='button' 
                                class='element-condition or-rule condition-form button  
                                <?php if (!empty($rule['combinator']) && $rule['combinator'] == 'OR') echo 'active';  ?>' 
                                title='Add a new "OR"  rule to this condition'>
                                OR
                            </button>

                            <button type='button' class='remove-condition condition-form button' title='Remove rule or condition'>
                                -
                            </button>
                            <?php
                            if ($conditionIndex == $lastCondtionKey && $ruleIndex == $lastRuleKey) {
                            ?>
                                <button type='button' class='add-condition condition-form button' title='Add a new condition'>
                                    +
                                </button>
                                <button type='button' class='add-condition opposite condition-form button' title='Add a new condition, opposite to to the previous one'>
                                    Add opposite
                                </button>
                            <?php
                            }
                            ?>
                        </div>
                    <?php
                    }
                    ?>
                    <br>
                    <span style='font-weight: 600;'>then</span><br>

                    <div class='action-row'>
                        <div class='radio-wrapper condition-form'>
                            <label>
                                <input 
                                    type='radio' 
                                    name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][action]' 
                                    class='element-condition' 
                                    value='show'
                                    <?php if ($condition['action'] == 'show') echo 'checked';  ?> required>
                                Show this element
                            </label><br>

                            <label>
                                <input 
                                    type='radio' 
                                    name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][action]' 
                                    class='element-condition' 
                                    value='hide' 
                                    <?php if ($condition['action'] == 'hide') echo 'checked';  ?> required>
                                Hide this element
                            </label><br>

                            <label>
                                <input 
                                    type='radio' 
                                    name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][action]' 
                                    class='element-condition' 
                                    value='toggle' <?php if ($condition['action'] == 'toggle') echo 'checked';   ?> required>
                                Toggle this element
                            </label><br>

                            <label>
                                <input 
                                    type='radio' 
                                    name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][action]' 
                                    class='element-condition' 
                                    value='value' <?php if ($condition['action'] == 'value') echo 'checked';  ?> required>
                                Set property
                            </label>
                            <input type="text" list="propertylist" name="element-conditions[<?php echo esc_attr($conditionIndex); ?>][property-name1]" class='element-condition' placeholder="property name" value="<?php echo esc_attr($condition['property-name1'] ?? ''); ?>">
                            <label> to:</label>
                            <textarea class='element-condition' name="element-conditions[<?php echo esc_attr($conditionIndex); ?>][action-value]" rows='1'>
                                <?php echo esc_textarea($condition['action-value'] ?? ''); ?>
                            </textarea>
                            <br>
                            <label>
                                <input 
                                    type='radio' 
                                    name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][action]' 
                                    class='element-condition' 
                                    value='property' <?php if ($condition['action'] == 'property') echo 'checked';  ?> required>
                                Set the
                            </label>

                            <datalist id="propertylist">
                                <option value="value">
                                <option value="min">
                                <option value="max">
                            </datalist>
                            <label>
                                <input type="text" list="propertylist" name="element-conditions[<?php echo esc_attr($conditionIndex); ?>][property-name]" class='element-condition' placeholder="property name" value="<?php echo esc_attr($condition['property-name'] ?? ''); ?>">
                                property to the value of
                            </label>

                            <select class='element-condition condition-select' name='element-conditions[<?php echo esc_attr($conditionIndex); ?>][property-value]'>
                                <?php $this->inputDropdown($condition['property-value'] ?? '', $elementId); ?>
                            </select>

                            <?php
                            if (!empty($condition['property-value'])) {
                                $type    = $this->getElementById($condition['property-value'], 'type');
                            } else {
                                $type    = '';
                            }
                            $hidden  = 'hidden';
                            $hidden2 = 'hidden';
                            if (isset(['date' => 1, 'number' => 1, 'range' => 1, 'week' => 1, 'month' => 1][$type])) {
                                $hidden    = '';

                                if (isset(['date' => 1, 'week' => 1, 'month' => 1][$type])) {
                                    $hidden2    = '';
                                }
                            }
                            ?>
                            <label class='addition <?php echo esc_attr($hidden); ?>'>
                                + <input type='number' name="element-conditions[<?php echo esc_attr($conditionIndex); ?>][addition]" class='element-condition' value="<?php echo esc_attr($condition['addition'] ?? ''); ?>" style='width: 60px;'>
                                <span class='days <?php echo esc_attr($hidden2); ?>'> days</span>
                            </label>
                            <br>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <br>
            <div class="copyfieldswrapper">
                <label>
                    <input 
                        type='checkbox' 
                        class="showcopyfields" 
                        <?php if (!empty($conditions['copyto'])) echo 'checked'; ?>>
                    Apply visibility conditions to other fields
                </label><br><br>

                <div 
                    class='copyfields 
                    <?php if (empty($conditions['copyto'])) echo 'hidden';  ?>'>
                    Check the fields these conditions should apply to as well,<br>
                    This holds only for visibility conditions (show, hide or toggle).<br><br>
                    <?php
                    foreach ($this->formElements as $element) {
                        //do not show the current element itself or wrapped labels
                        if ($element->id != $elementId && empty($element->wrap)) {
                            if (!empty($conditions['copyto'][$element->id])) {
                                $checked = 'checked';
                            } else {
                                $checked = '';
                            }

                            $name    = ucfirst(str_replace('_', ' ', $element->slug));
                            if (str_contains($name, '[]')) {
                                $name    .= " ($element->id)";
                            }

                            ?>
                            <label>
                                <input 
                                    type='checkbox' 
                                    name='element-conditions[copyto][<?php echo esc_attr($element->id);?>]' 
                                    value='<?php echo esc_attr($element->id);?>' 
                                    <?php if (!empty($conditions['copyto'][$element->id])) echo 'checked';?>
                                >
                                <?php echo esc_html($name);?>
                            </label>
                            <br>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
            TSJIPPY\addSaveButton('submit-form-condition', 'Save conditions'); ?>
        </form>
        <?php
        return ob_get_clean();
    }
}
