<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;

use function TSJIPPY\addElement as addElement;
use function TSJIPPY\addRawHtml as addRawHtml;

if (! defined('ABSPATH')) {
    exit;
}

class FormBuilderForm extends SubmitForm
{
    public bool $inMultiAnswer;
    public bool $isInDiv;
    public bool $showId;
    public bool $showName;

    /**
     * Constructor for the FormBuilderForm class.
     *
     * @param array $atts        Optional. An array of attributes for the form. Default empty array.
     * @param bool $showId       Optional. Whether to show the element IDs. Default false.
     * @param bool $showName     Optional. Whether to show the element names. Default false.
     * @param bool $all          Optional. Whether to show all elements. Default false.
     * @param int $pageSize      Optional. The number of elements per page. Default 50.
     * @param string $postId     Optional. The ID of the post associated with the form. Default empty string.
     * @param string $formUrl    Optional. The URL of the form. Default empty string.
     * @param int $userId        Optional. The ID of the user associated with the form. Default 0.
     */
    public function __construct($atts = [], $showId = false, $showName = false, $all = false, $pageSize = 50, $postId = '', $formUrl = '', $userId = 0)
    {
        parent::__construct(atts: $atts, all: $all, pageSize: $pageSize, postId: $postId, formUrl: $formUrl, userId: $userId);

        $this->inMultiAnswer = false;
        $this->isInDiv       = false;
        $this->showName      = $showName;
        $this->showId        = $showId;
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

        
        $users  = TSJIPPY\getUserAccounts(returnFamily: false, adults: true, uniqueDisplayName: true);
        foreach($users as $key => $user){
            unset($users[$key]);

            $users[$user->ID] = $user->display_name;
        }

        //Sort the users
        asort($users);

    ?>
        <div class="element-settings-wrapper">
            <form action='' method='post' class='tsjippy-form builder'>
                <div class='form-elements'>
                    <input type='hidden' class='no-reset' class='formbuilder' name='form-id' value='<?php echo esc_attr($this->formData->id); ?>'>

                    <label class="block">
                        <h4>
                            Submit button text
                        </h4>
                        <input type='text' class='formbuilder form-element-setting' name='button-text' value="<?php echo esc_attr($this->formData->button_text); ?>">
                    </label>

                    <label class="block">
                        <h4>
                            Succes message
                        </h4>
                        <input type='text' class='formbuilder form-element-setting' name='succes-message' value="<?php echo esc_attr($this->formData->succes_message); ?>">
                    </label>

                    <label class="block">
                        <h4>
                            Include submission ID in message
                        </h4>
                        <label>
                            <input
                                type='radio'
                                class='formbuilder form-element-setting'
                                name='include-id'
                                value="1"
                                <?php if (!isset($this->formData->include_id) || $this->formData->include_id) echo 'checked';  ?>>
                            Yes
                        </label>
                        <label>
                            <input
                                type='radio'
                                class='formbuilder form-element-setting'
                                name='include-id'
                                value="0"
                                <?php if (!($this->formData->include_id ?? false)) echo 'checked';  ?>>
                            No
                        </label>
                    </label>

                    <label class="block">
                        <h4>
                            Form name
                        </h4>
                        <input type='text' class='formbuilder form-element-setting' name='name' value="<?php echo esc_attr($this->formData->name) ?>">
                    </label>
                    <br>

                    <label class='block'>
                        <input type='checkbox' class='formbuilder form-element-setting' name='save-in-meta' value='1' <?php if ($this->formData->save_in_meta) echo 'checked';  ?>>
                        Save submissions in usermeta table
                    </label>
                    <br>

                    <label class="block">
                        <h4>
                            Form url
                        </h4>
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

                    <h4>
                        Available actions
                    </h4>
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
                                <?php if (!empty($this->formData->actions[$action])) echo  'checked';  ?>>
                            <?php echo esc_html(ucfirst($action)); ?>
                        </label><br>
                    <?php
                    }
                    ?>

                    <div class="formsettings-wrapper">
                        <label class="block">
                            <h4>
                                Auto archive results
                            </h4>
                            <br>
                            <label>
                                <input
                                    type="radio"
                                    name="autoarchive"
                                    value="1"
                                    <?php if ($this->formData->autoarchive) echo 'checked'; ?>>
                                Yes
                            </label>
                            <label>
                                <input
                                    type="radio"
                                    name="autoarchive"
                                    value="0"
                                    <?php if (!$this->formData->autoarchive) echo 'checked'; ?>>
                                No
                            </label>
                        </label>
                        <br>
                        <div
                            class='auto-archive-logic 
                            <?php if (!$this->formData->autoarchive) echo 'hidden';  ?>'
                            style="display: flex;width: 100%;">
                            Auto archive a (sub) entry when field
                            <select name="autoarchive-el" style="margin-right:10px;">
                                <option
                                    value=''
                                    <?php if (empty($this->formData->autoarchive_el)) echo 'selected'; ?>>
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
                                        value='<?php echo esc_attr($element->id); ?>'
                                        <?php if (($this->formData->autoarchive_el ?? -1) == $element->id) echo 'selected="selected"';  ?>>
                                        <?php echo esc_html($slug); ?>
                                    </option>
                                <?php
                                }
                                ?>
                            </select>
                            <label style="margin:0 10px;">
                                equals
                            </label>
                            <input type='text' name="autoarchive-value" value="<?php echo esc_attr($this->formData->autoarchive_value ?? ''); ?>">
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
                                <h4>
                                    Select fields where you want to create seperate rows for
                                </h4>
                                <?php

                                foreach ($foundElements as $slug => $id) {
                                    $name    = ucfirst(strtolower(str_replace('_', ' ', $slug)));

                                    //Check which option is the selected one
                                ?>
                                    <label>
                                        <input
                                            type='checkbox'
                                            name='split[]'
                                            value='<?php echo esc_attr($id); ?>'
                                            <?php if (in_array($id, $this->formData->split)) echo 'checked';  ?>>
                                        <?php echo esc_html($name); ?>
                                    </label>
                                    <br>
                            <?php
                                }
                            }
                            ?>

                            <h4>
                                Select roles or users with form edit rights
                            </h4>
                            <select name='full_right_roles[]' multiple>
                                <option value=''>
                                    ---
                                </option>

                                <optgroup label="Roles">
                                    <?php
                                    foreach ($userRoles as $key => $name) {
                                    ?>
                                        <option
                                            value='<?php echo esc_attr($key); ?>'
                                            <?php if (isset($this->formData->full_right_roles[$key])) echo 'selected'; ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                                <optgroup label="Users">
                                    <?php
                                    foreach ($users as $key => $name) {
                                    ?>
                                        <option
                                            value='<?php echo esc_attr($key); ?>'
                                            <?php if (isset($this->formData->full_right_roles[$key])) echo 'selected'; ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </optgroup> 
                            </select>
                            <br>
                            <h4>
                                Select roles who can submit the form on behalve of somebody else
                            </h4>
                            <select name='submit_others_form[]' multiple>
                                <option value=''>
                                    ---
                                </option>

                                <optgroup label="Roles">
                                    <?php
                                    foreach ($userRoles as $key => $name) {
                                        ?>
                                        <option
                                            value='<?php echo esc_attr($key); ?>'
                                            <?php if (isset($this->formData->submit_others_form[$key])) echo 'selected'; ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                                <optgroup label="Users">
                                    <?php
                                    foreach ($users as $key => $name) {
                                    ?>
                                        <option
                                            value='<?php echo esc_attr($key); ?>'
                                            <?php if (isset($this->formData->submit_others_form[$key])) echo 'selected'; ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </optgroup> 
                            </select>
                        </div>
                    </div>
                </div>
                <?php
                TSJIPPY\addSaveButton('submit-form-setting',  'Save form settings');
                ?>
            </form>
            <form method="POST" style='display: inline-block;'>
                <input type="hidden" name='nonce' value='<?php echo esc_attr(wp_create_nonce('form-export-' . $this->formData->id)); ?>'>
                <button type='submit' class='button' name="export-form" value='<?php echo esc_attr($this->formData->id); ?>'>
                    Export this form
                </button>
            </form>
            <form method="POST" style='display: inline-block;'>
                <input type="hidden" class="no-reset" name="page-id" value='<?php echo esc_attr(get_the_ID()); ?>'>
                <input type="hidden" name='nonce' value='<?php echo esc_attr(wp_create_nonce('form-delete-' . $this->formData->id)); ?>'>
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
                        <?php if (!empty($this->formReminder->frequency)) echo 'checked';  ?>>
                    <span class="slider round"></span>
                </label>
                <br>
                <br>

                <div
                    class='recurring-submissions 
                    <?php if (empty($this->formReminder->frequency)) echo 'hidden';  ?>'>
                    <label>
                        <h4>
                            Recurring Submissions
                        </h4>
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
                                <?php if (($this->formReminder->period ?? '') == $period) echo 'checked'; ?>>
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
                            <?php if (($this->formReminder->reminder_period ?? '') == $period) echo 'checked'; ?>>
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

        if (!isset($conditions[0])) {
            $conditions[0] = [];
        }

        if (!isset($conditions[0]["meta-key"])) {
            $conditions[0]["meta-key"] = '';
        }

        if (!isset($conditions[0]["equation"])) {
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
                <option value='<?php echo esc_attr($key) ?>' <?php echo esc_attr($data); ?>>
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
                <option value='<?php echo esc_attr($key); ?>' <?php if (isset($conditions['roles'][$key])) echo 'selected'; ?>>
                    <?php echo esc_html($roleName); ?>
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
                                    <option value='<?php echo esc_attr($key); ?>'>
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
                                value='<?php echo esc_attr($option); ?>'
                                <?php if ($condition['equation'] == $option) echo 'selected'; ?>>
                                <?php echo esc_html($optionLabel); ?>
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
                        <?php if ($condition['equation'] == 'submitted') echo 'visibility:hidden;';  ?>">

                    <button
                        type='button'
                        class='warn-cond button 
                        <?php if (($condition['combinator'] ?? '') == 'and') echo 'active';  ?>'
                        title='Add a new "AND" rule'
                        value="and">
                        AND
                    </button>

                    <button
                        type='button'
                        class='warn-cond button 
                        <?php if (($condition['combinator'] ?? '') == 'or') echo 'active';  ?>'
                        title='Add a new "OR"  rule'
                        value="or">
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
                                    %<?php echo esc_attr($element->slug); ?>%
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
                                id='show-email-<?php echo esc_attr($key); ?>'
                                data-target='email-<?php echo esc_attr($key); ?>'
                                style='margin-right:4px;'>
                                E-mail <?php echo esc_attr($nr); ?>
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
                                        <h4>
                                            Trigger
                                        </h4>
                                        Send e-mail when:<br>
                                        <label>
                                            <input
                                                type='radio'
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]'
                                                class='email-trigger'
                                                value='submitted'
                                                <?php if (($email->email_trigger ?? '') == 'submitted') echo 'checked';  ?>>
                                            The form is submitted
                                        </label>
                                        <br>

                                        <label>
                                            <input
                                                type='radio'
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]'
                                                class='email-trigger'
                                                value='shouldsubmit'
                                                <?php if (($email->email_trigger ?? '') == 'shouldsubmit') echo 'checked';  ?>>
                                            The form is due for submission
                                        </label><br>

                                        <label>
                                            <input
                                                type='radio'
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]'
                                                class='email-trigger'
                                                value='submittedcond'
                                                <?php if (($email->email_trigger ?? '') == 'submittedcond') echo 'checked';  ?>>
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
                                                            value='<?php echo esc_attr($option); ?>'
                                                            <?php if ($triggerEquation == $option) echo 'selected'; ?>>
                                                            <?php echo esc_html($optionLabel); ?>
                                                        </option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>

                                                <label
                                                    class='staticvalue 
                                                    <?php if (empty($triggerEquation) || !isset(['==' => 1, '!=' => 1, '>' => 1, '<' => 1][$triggerEquation])) echo 'hidden';  ?>'>
                                                    <input type='text' name='emails[<?php echo esc_attr($key); ?>][submitted-trigger][value]' value="<?php echo esc_attr($triggerValue); ?>" style='width: auto;'>
                                                </label>

                                                <select
                                                    class='dynamicvalue 
                                                    <?php if (empty($triggerEquation) || isset(['==' => 1, '!=' => 1, '>' => 1, '<' => 1, 'checked' => 1, '!checked' => 1][$triggerEquation])) echo 'hidden';  ?>'
                                                    name='emails[<?php echo esc_attr($key); ?>][submitted-trigger][value-element]'>
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
                                                <?php if (($email->email_trigger ?? '') == 'fieldchanged') echo 'checked';  ?>>
                                            A field has changed to a value
                                        </label>
                                        <div
                                            class='conditional-field-wrapper 
                                            <?php if (($email->email_trigger ?? '') != 'fieldchanged') echo 'hidden';  ?>'>
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
                                                <?php if (($email->email_trigger ?? '') == 'fieldschanged') echo 'checked';  ?>>
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
                                                <?php if (($email->email_trigger ?? '') == 'removed') echo 'checked';  ?>>
                                            The submission is archived or deleted
                                        </label>
                                        <br>
                                        <?php do_action('tsjippy-forms-after-email-triggers', $key, $email); ?>
                                        <label>
                                            <input
                                                type='radio'
                                                name='emails[<?php echo esc_attr($key); ?>][email-trigger]'
                                                class='email-trigger' value='disabled'
                                                <?php if (($email->email_trigger ?? '') == 'disabled') echo 'checked';  ?>>
                                            Do not send this e-mail
                                        </label>
                                        <br>
                                    </div>

                                    <br>
                                    <div class="formfield form-label">
                                        <h4>
                                            Sender address
                                        </h4>
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
                                                value="<?php if (empty($email->from))   esc_attr($defaultFrom);
                                                        else echo esc_attr($email->from); ?>">
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
                                            foreach (array_values(($email->conditional_from_email ?? [])) as $fromKey => $fromEmail) {
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
                                    <h4>
                                        Recipient address
                                    </h4>
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
                                                value="<?php if (empty($email->to)) echo '%email%';
                                                        else echo esc_attr($email->to); ?>">
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
                                        <h4>
                                            Subject
                                        </h4>
                                        <input type='text' class='formbuilder form-element-setting' name='emails[<?php echo esc_attr($key); ?>][subject]' value="<?php echo esc_attr($email->subject ?? '') ?>">
                                    </div>

                                    <br>
                                    <div class="formfield form-label">
                                        <h4>
                                            Content
                                        </h4>
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
}
