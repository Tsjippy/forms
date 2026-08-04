<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;
use WP_Error;

if (! defined('ABSPATH')) {
    exit;
}

class Forms
{

    public bool        $all;            // do not page submissions
    protected bool     $clonableFormStep;
    public bool        $editRights;
    public array       $elementMapping;
    public array       $emailSettings;
    public object      $formData;
    public array       $formElements;
    public string      $formEmailTable;
    public int         $formId;
    public object|null $formReminder;
    public string      $formReminderTable;
    public array       $forms;
    public int         $formStepCounter;
    public bool        $isFormStep;
    public bool        $isMultiStepForm;
    public string      $jsFileName;
    public array       $multiInputsHtml;
    public bool        $multiwrap;
    public array       $nonInputs;
    public array       $wpMetaKeys;
    public string      $objectName;
    public bool        $onlyOwn;
    public int         $pageSize;
    public string      $shortcodeColumnSettingsTable;
    public int         $shortcodeId;
    public string      $shortcodeTable;
    public bool        $showArchived;
    public array       $slugs;
    public object|null $submission;
    public array       $submissions;
    public string      $submissionTableName;
    public string      $submissionValuesTableName;
    public string      $blockConditionsTableName;
    public string      $blockRemindersTableName;
    public array       $submitRoles;
    protected array    $tableFormats;
    public \WP_User    $user;
    public int         $userId;
    protected string   $userIdElementName;
    public array       $userRoles;
    public array       $inputTags;
    public array       $checkboxTypes;

    /**
     * Constructor
     *
     * @param array    $atts        Shortcode attributes
     * @param bool     $all         Whether to retrieve all submissions or not
     * @param int      $pageSize    Number of submissions per page
     * @param int      $postId      Post ID to retrieve form for
     * @param string   $formUrl     Form URL to retrieve form for
     * @param int      $userId      User ID to retrieve form for
     */
    public function __construct($atts = [], $all = false, $pageSize = 50, $postId = '', $formUrl = '',  $userId = 0)
    {
        global $wpdb;

        $this->all                          = $all;
        $this->clonableFormStep             = false;
        $this->elementMapping               = [];
        $this->emailSettings                = [];
        $this->formData                     = new stdClass();
        $this->formEmailTable               = $wpdb->prefix . 'tsjippy_form_emails';
        $this->formElements                 = [];
        $this->formId                       = -1;
        $this->formReminder                 = null;
        $this->formReminderTable            = $wpdb->prefix . 'tsjippy_form_reminders';
        $this->forms                        = [];
        $this->formStepCounter              = 0;
        $this->isFormStep                   = false;
        $this->isMultiStepForm              = false;
        $this->jsFileName                   = '';
        $this->multiInputsHtml              = [];
        $this->multiwrap                    = false;

        $this->nonInputs                    = [
            'label'       => 1,
            'button'      => 1,
            'datalist'    => 1,
            'formstep'    => 1,
            'info'        => 1,
            'p'           => 1,
            'php'         => 1,
            'multi-start' => 1,
            'multi-end'   => 1,
            'div-start'   => 1,
            'div-end'     => 1
        ];

        $this->inputTags    = [
            'input' => 1, 
            'textarea' => 1, 
            'select' => 1
        ];

        $this->checkboxTypes    = [
            'checkbox'  => true,
            'radio'     => true
        ];

        $this->wpMetaKeys                   = [
            'nickname'                              => 1,
            'first_name'                            => 1,
            'last_name'                             => 1,
            'description'                           => 1,
            'rich_editing'                          => 1,
            'syntax_highlighting'                   => 1,
            'comment_shortcuts'                     => 1,
            'admin_color'                           => 1,
            'use_ssl'                               => 1,
            'show_admin_bar_front'                  => 1,
            'locale'                                => 1,
            'wp_capabilities'                       => 1,
            'wp_user_level'                         => 1,
            'dismissed_wp_pointers'                 => 1,
            'show_welcome_panel'                    => 1,
            'session_tokens'                        => 1,
            'wp_dashboard_quick_press_last_post_id' => 1,
            'wp_user-settings'                      => 1,
            'wp_user-settings-time'                 => 1,
            'wp_persisted_preferences'              => 1,
            '2fa_hash'                              => 1
        ];

        $this->objectName                   = '';
        $this->onlyOwn                      = false;
        $this->pageSize                     = $pageSize;
        $this->shortcodeColumnSettingsTable = $wpdb->prefix . 'tsjippy_form_shortcode_column_settings';
        $this->shortcodeId                  = -1;
        $this->shortcodeTable               = $wpdb->prefix . 'tsjippy_form_shortcodes';
        $this->showArchived                 = false;
        $this->slugs                        = [];
        $this->submission                   = null;
        $this->submissions                  = [];
        $this->submissionTableName          = $wpdb->prefix . 'tsjippy_form_submissions';
        $this->submissionValuesTableName    = $wpdb->prefix . 'tsjippy_form_submission_values';
        $this->blockConditionsTableName     = $wpdb->prefix . 'tsjippy_form_block_conditions';
        $this->blockRemindersTableName      = $wpdb->prefix . 'tsjippy_form_block_reminders';
        $this->submitRoles                  = [];
        $this->tableFormats                 = [];
        $this->user                         = wp_get_current_user();
        $this->userId                       = $this->user->ID;  // The user id for who we retrieve a form (results)
        $this->userIdElementName            = '';
        $this->userRoles                    = array_flip($this->user->roles);

        if ($all) {
            $this->pageSize                    = 99999;
        }

        //calculate full form rights
        $object        = get_queried_object();
        $postAuthor    = 0;
        if (!empty($object->post_author)) {
            $postAuthor    = $object->post_author;
        }

        // phpcs:ignore
        elseif (is_numeric($postId)) {
            $post        = get_post($postId);
            if (!empty($post)) {
                $postAuthor    = $post->post_author;
            }
        }
        // phpcs:ignore
        elseif (!empty($formUrl)) {
            $postId        = url_to_postid($formUrl);

            if ($postId) {
                $postAuthor    = get_post($postId)->post_author;
            }
        }

        if (array_intersect_key(['administrator' => 1, 'editor' => 1], $this->userRoles) || $postAuthor == $this->user->ID) {
            $this->editRights        = true;
        } else {
            $this->editRights        = false;
        }

        // $this->userId is the user id for whom the form is submitted
        $this->userId    = $userId === 0 ? $this->user->ID : $userId;

        if (!empty($atts)) {
            $this->processAtts($atts);
        }

        $this->tableFormats();
    }

    /**
     * Creates the tables for this plugin
     */
    public function createDbTables()
    {
        if (!function_exists('maybe_create_table')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        add_option("forms_db_version", "1.0");

        //only create db if it does not exist
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();

        // Form Reminders Table
        $sql = "CREATE TABLE {$this->formReminderTable} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id int,
            block_id tinytext,
            frequency int,
            period text,
            reminder_start_date date,
            reminder_amount int,
            reminder_period text,
            window_start date,
            window_end date,
            conditions LONGTEXT,

            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->formReminderTable, $sql);

        // form e-mails table
        $sql = "CREATE TABLE {$this->formEmailTable} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id int,
            block_id tinytext NOT NULL,
            `trigger` longtext,
            sender longtext,
            recipient longtext,
            `subject` longtext,
            `message` longtext,
            headers longtext,
            attachments longtext,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->formEmailTable, $sql);

        // shortcodeTableSettings table
        $sql = "CREATE TABLE {$this->shortcodeTable} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id int,
            block_id tinytext NOT NULL,
            title tinytext,
            default_sort tinytext,
            sort_direction tinytext,
            filter longtext,
            hide_row tinytext,
            result_type tinytext,
            split_table boolean,
            archived boolean,
            `view_right_roles` longtext,
            edit_right_roles longtext,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->shortcodeTable, $sql);

        // shortcode Column Settings table
        $sql = "CREATE TABLE {$this->shortcodeColumnSettingsTable} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            shortcode_id mediumint(9) NOT NULL,
            width mediumint(9),
            element_id tinytext,
            `show` boolean,
            slug tinytext,
            name tinytext,
            `priority` mediumint(9),
            view_right_roles longtext,
            edit_right_roles longtext,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->shortcodeColumnSettingsTable, $sql);

        // submission table
        $sql = "CREATE TABLE {$this->submissionTableName} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id int,
            block_id    tinytext NOT NULL,
            time_created datetime DEFAULT NULL,
            time_last_edited datetime DEFAULT NULL,
            user_id mediumint(9),
            submitter_id mediumint(9),
            archived BOOLEAN,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->submissionTableName, $sql);

        // Submission values table
        $sql = "CREATE TABLE {$this->submissionValuesTableName} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            submission_id    mediumint(9) NOT NULL,
            sub_id    mediumint(9),
            element_id mediumint(9) NOT NULL,
            `value` longtext NOT NULL,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->submissionValuesTableName, $sql);

        // Block conditions table
        $sql = "CREATE TABLE {$this->blockConditionsTableName} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id mediumint(9),
            `block_id` tinytext,
            `rules` longtext,
            `actions` longtext,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->blockConditionsTableName, $sql);

        // Block reminders table
        $sql = "CREATE TABLE {$this->blockRemindersTableName} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id mediumint(9),
            `block_id` tinytext,
            `rules` longtext,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->blockRemindersTableName, $sql);
    }

    /**
     * Defines the formats of each column in each table
     */
    private function tableFormats()
    {
        // From Reminder Settings
        $formats            = [
            'post_id'             => '%d',
            'block_id'            => '%s',
            'frequency'           => '%d',
            'period'              => '%s',
            'reminder_start_date' => '%s',
            'reminder_amount'     => '%d',
            'reminder_period'     => '%s',
            'window_start'        => '%s',
            'window_end'          => '%s',
            'conditions'          => '%s'
        ];

        $this->tableFormats[$this->formReminderTable] = apply_filters('tsjippy-forms-form-reminder-formats', $formats, $this);

        // Form Emails
        $formats    = [
            'post_id'     => '%d',
            'block_id'    => '%s',
            'trigger'     => '%s',
            'sender'      => '%s',
            'recipient'   => '%s',
            'subject'     => '%s',
            'message'     => '%s',
            'headers'     => '%s',
            'attachments' => '%s'
        ];

        $this->tableFormats[$this->formEmailTable]    = apply_filters('tsjippy-forms-email-table-formats', $formats, $this);

        // Form Submissions
        $formats    = [
            'post_id'          => '%d',
            'block_id'         => '%s',
            'time_created'     => '%s',
            'time_last_edited' => '%s',
            'user_id'          => '%d',
            'submitter_id'     => '%d',
            'archived'         => '%d'
        ];

        $this->tableFormats[$this->submissionTableName] = apply_filters('tsjippy-forms-submission-table-formats', $formats, $this);

        // Form Submission Data
        $formats    = [
            'submission_id' => '%d',
            'sub_id'        => '%d',
            'element_id'    => '%d',
            'value'         => '%s'
        ];

        $this->tableFormats[$this->submissionValuesTableName] = apply_filters('tsjippy-forms-submission-values-table-formats', $formats, $this);

        // Table Settings
        $formats    = [
            'post_id'          => '%d',
            'block_id'         => '%s',
            'title'            => '%s',
            'default_sort'     => '%s',
            'sort_direction'   => '%s',
            'filter'           => '%s',
            'hide_row'         => '%d',
            'result_type'      => '%s',
            'split_table'      => '%s',
            'archived'         => '%d',
            'view_right_roles' => '%s',
            'edit_right_roles' => '%s'
        ];

        $this->tableFormats[$this->shortcodeTable]    = apply_filters('tsjippy-forms-shortcode-table-formats', $formats, $this);

        // Column Settings
        $formats    = [
            'shortcode_id'     => '%d',
            'element_id'       => '%s',
            'width'            => '%d',
            'show'             => '%d',
            'slug'             => '%s',
            'name'             => '%s',
            'priority'         => '%d',
            'copy'             => '%d',
            'view_right_roles' => '%s',
            'edit_right_roles' => '%s'
        ];

        $this->tableFormats[$this->shortcodeColumnSettingsTable] = apply_filters('tsjippy-forms-shortcode-settings-table-formats', $formats, $this);

        // Condition Settings
        $formats    = [
            'rules'    => '%s',
            'actions'  => '%s',
            'block_id' => '%s',
            'post_id'  => '%d'     
        ];

        $this->tableFormats[$this->blockConditionsTableName] = apply_filters('tsjippy-forms-condition-formats', $formats, $this);

        // Block Reminder Settings
        $formats    = [
            'rules'    => '%s',
            'post_id'  => '%d',
            'block_id' => '%s', 
        ];

        $this->tableFormats[$this->blockConditionsTableName] = apply_filters('tsjippy-forms-block-reminder-formats', $formats, $this);

        foreach ($this->tableFormats as &$format) {
            ksort($format);
        }
    }

    /**
     * Get a form by submission id
     * @param    int            $submisisonId    The id of the submission for which to retrieve the form
     * @return    object                        The form data object or WP_Error on failure
     */
    public function getFormBySubmissionId($submisisonId)
    {
        $formId        = TSJIPPY\getFromDb(
            "get_form_by_submission_id_$submisisonId",
            "forms",
            "SELECT form_id FROM %i WHERE id = %d LIMIT 1",
            $this->submissionTableName,
            $submisisonId
        );

        if (empty($formId)) {
            return new WP_Error('forms', "No form found for submission id $submisisonId");
        }

        return $formId;
    }

    /**
     * Gets the form reminders from the db
     * @param    int    $blockId        the block id for which to get the reminders
     */
    public function getFormReminder($blockId = '')
    {
        $this->formReminder    = new stdClass();

        $results    =  TSJIPPY\getFromDb(
            "get_form_reminders_$blockId",
            "forms",
            "SELECT * FROM %i WHERE block_id = %d",
            $this->formReminderTable,
            $blockId
        );

        if (empty($results)) {
            return;
        }

        $this->formReminder    = map_deep($results[0], 'maybe_unserialize');
    }

    /**
     * Retrieves e-mail settings from the database
     */
    public function getEmailSettings()
    {
        global $wpdb;

        if (empty($this->formData)) {
            return new WP_Error('forms', "no form is loaded");
        }

        $this->emailSettings =  TSJIPPY\getFromDb(
            "get_email_settings_" . $this->formData->id,
            "forms",
            "select * from %i where form_id=%d",
            $this->formEmailTable,
            $this->formData->id
        );

        if (empty($this->emailSettings)) {
            $emails[0]["from"]            = "";
            $emails[0]["to"]            = "";
            $emails[0]["subject"]        = "";
            $emails[0]["message"]        = "";
            $emails[0]["headers"]        = "";
            $emails[0]["files"]            = "";
            $emails[0]["email_trigger"]    = "";

            $this->emailSettings = $emails;
        }
    }

    /**
     * Creates the element mappers to find elements based on id, name or type
     *
     * @param    bool    $force        Whether to requery, default false
     */
    public function elementMapper($force = false)
    {
        if (
            empty($this->formData) ||
            (
                isset($this->elementMapping) &&
                !empty($this->elementMapping['type']) &&
                !$force
            )
        ) {
            return;
        }

        //used to find the index of an element based on its unique id, type or name
        $this->elementMapping                                    = [];
        $this->elementMapping['type']                            = [];
        $this->elementMapping['slug']                            = [];

        $this->getAllFormElements('priority', $this->formData->id, true);

        foreach ($this->formElements as $index => $element) {
            $this->elementMapping['id'][$element->id]               = $index;
            $this->elementMapping['slug'][$element->slug][$index]   = $index;
            $this->elementMapping['type'][$element->type][$index]   = $index;
        }
    }

    /**
     * Get the elements of a form
     * 
     * @param string $blockId The forms block id
     * @param int    $postId  The forms post id    
     */
    public function getForm($postId, $blockId){
        $post   = get_post($postId);

        $blocks = parse_blocks($post->post_content);

        return $blocks;
    }

    /**
     * Finds all blocks and post ids who are of the formbuilder type
     */
    public function getForms(){
        global $wpdb;

        return TSJIPPY\getFromDb(
            'all_forms',
            'forms',
            "SELECT distinct block_id, post_id from %i",
            $this->submissionTableName
        );
    }

    /**
     * Creates a dropdown with all the forms
     *
     * @return    string    the select html
     */
    public function formSelect()
    {
        $this->getForms();

        foreach ($this->forms as $form) {
            $this->slugs[]            = $form->slug;
        }

        $html = "<select name='form-selector'>";
        $html .= "<option value=''>---</option>";
        foreach ($this->slugs as $name) {
            $html .= "<option value='$name'>$name</option>";
        }
        $html .= "</select>";

        return $html;
    }

    /**
     * Finds an element by its id
     *
     * @param    int        $id        the element id
     * @param    string    $key    A specific element attribute to return. Default empty
     *
     * @return    object|array|string|false            The element or element property
     */
    public function getElementById($id, $key = '')
    {
        global $post;

        if (empty($id)) {
            return false;
        }

        if (!is_numeric($id) && gettype($id) == 'string') {
            return $this->getElementBySlug($id, $key);
        }

        //load if needed
        if (empty($this->elementMapping)) {
            $this->getForm(2, 3);
        }

        if (!isset($this->elementMapping['id'][$id])) {
            $this->elementMapper(true);

            if (empty($post)) {
                // phpcs:ignore
                $url    = TSJIPPY\sanitize($_SERVER['REQUEST_URI'] ?? '');
            } else {
                $url    = get_page_link($post);
            }

            TSJIPPY\printArray("Element with id '$id' not found on form '{$this->formData->slug}' with id  '{$this->formData->id}' on page $url", false);
            return false;
        }
        $elementIndex    = $this->elementMapping['id'][$id];

        $element        = $this->formElements[$elementIndex];
        if (empty($element)) {
            return false;
        }

        $element->index    = $elementIndex;

        if (empty($key)) {
            return $element;
        } else {
            return $element->$key;
        }
    }

    /**
     * Finds an element by its slug
     *
     * @param    string    $slug               The element slug
     * @param    string    $key                A specific element attribute to return. Default empty
     * @param    bool      $single             Wheter to return a singel element, default true
     *
     * @return    object|array|string|false    The element or an array of elements or an element property of false if not found
     */
    public function getElementBySlug($slug, $key = '', $single = true)
    {
        if (empty($slug)) {
            return false;
        }

        //load if needed
        if (empty($this->elementMapping)) {
            $result    = $this->getForm(2,3);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        if (!isset($this->elementMapping['slug'][$slug])) {
            // first part of the name, remove anything after [
            $slugNew    = explode('[', $slug)[0];

            if (isset($this->elementMapping['slug'][$slugNew])) {
                // remove '[]'
                $slug    = $slugNew;
            } elseif (isset($this->elementMapping['slug'][$slug . '[]'])) {
                // add []
                $slug    = '[]';
            } elseif (!empty($this->formData->split)) {
                // only the last part of a splitted name is given
                $mainName    = explode('[', $this->getElementById($this->formData->split[0], 'name'))[0];

                // we already tried adding splits, did not work
                if (str_contains($slug, $mainName . '[1][')) {
                    return false;
                } elseif ($mainName == $slugNew) {
                    $exploded    = explode('[', $slug);
                    $orgName    = trim(end($exploded), ']');
                    $slug        = $mainName . "[1][$orgName]";
                } else {
                    $slug        = $mainName . "[0][$slug]";
                }

                return $this->getElementBySlug($slug, $key, $single);
            } else {
                //TSJIPPY\printArray("Element with slug $slug not found on form {$this->formData->slug} with id {$this->formData->id}");
                return false;
            }
        }
        $elementIndexes    = $this->elementMapping['slug'][$slug];

        $elements        = [];

        foreach ($elementIndexes as $index) {
            $element        = $this->formElements[$index];
            $element->index = $index;
            $elements[]     = $element;
        }

        if (!$single) {
            return $elements;
        }

        $element    = $elements[0];

        if (empty($key)) {
            return $element;
        } else {
            return $element->$key;
        }
    }

    /**
     * Finds an element by its type
     *
     * @param    string    $type    The element type
     * @param    bool    $load    Try to load the formdata if empty default true
     *
     * @return    object|array|string|false            An array of elements
     */
    public function getElementByType($type, $load = true)
    {
        if (empty($type)) {
            return false;
        }

        //load if needed
        if (empty($this->elementMapping['type']) && $load) {
            $result    = $this->getForm(2, 3);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        if (!isset($this->elementMapping['type'][$type])) {
            //TSJIPPY\printArray("Element with id $type not found");
            return false;
        }

        $elementIndexes    = $this->elementMapping['type'][$type];

        $elements        = [];

        foreach ($elementIndexes as $index) {
            $element        = $this->formElements[$index];
            $element->index = $index;
            $elements[]     = $element;
        }

        return $elements;
    }

    /**
     * Finds the user name element in a form
     *
     * @return    string    the element name or false if no name element is found
     */
    public function findUserNameElementName()
    {
        // find the user id element
        $userNameKey    = false;

        if ($this->getElementBySlug('name')) {
            $userNameKey    = 'name';
        } elseif ($this->getElementBySlug('fullname')) {
            $userNameKey    = 'fullname';
        } elseif ($this->getElementBySlug('firstname')) {
            $userNameKey    = 'firstname';
        } elseif ($this->getElementBySlug('lasttname')) {
            $userNameKey    = 'lasttname';
        }

        return $userNameKey;
    }

    /**
     * Finds the phonenumber element in a form
     *
     * @return    string    the element name or false if no phonenumber element is found
     */
    public function findPhoneNumberElementName()
    {
        // find the user id element
        $phonenumberKey    = false;

        if ($this->getElementBySlug('phone')) {
            $phonenumberKey    = 'phone';
        } elseif ($this->getElementBySlug('phonenumber')) {
            $phonenumberKey    = 'phonenumber';
        } elseif ($this->getElementBySlug('phone_number')) {
            $phonenumberKey    = 'phone_number';
        }

        return $phonenumberKey;
    }

    /**
     * Finds the e-mail element in a form
     *
     * @return    string    the element name or false if no e-mail element is found
     */
    public function findEmailElementName()
    {
        // find the user id element
        $emailKey    = false;

        if ($this->getElementBySlug('email')) {
            $emailKey    = 'email';
        } elseif ($this->getElementBySlug('e-mail')) {
            $emailKey    = 'e-mail';
        }

        return $emailKey;
    }

    /**
     * Get all elements belonging to the current form
     *
     * @param    string     $sortCol        the column to sort on. Default empty
     * @param    int        $formId         The id of the form to get elements for, default empty
     * @param    bool       $force          Whether to requery, default false
     */
    public function getAllFormElements($sortCol = '', $formId = '', $force = false)
    {
        if (isset($this->formElements) && !$force) {
            return '';
        }

        if (!is_numeric($formId) && $this->formData && is_numeric($this->formData->id)) {
            $formId    = $this->formData->id;
        }

        if (!is_numeric($formId) && isset($this->formData->id) && is_numeric($this->formData->id)) {
            $formId    = $this->formData->id;
        }

        if (!is_numeric($formId)) {
            return new \WP_Error('forms', 'No form id given');
        }

        // Get all form elements


        /**
         * Filters the elements of this form,
         * @param    array   $elements  The elements array
         * @param    object  $object    The form instance
         * @param    bool    $force     Wheter to force a requery
         */
        //$this->formElements         =  apply_filters('tsjippy-forms-elements', $elements, $this, false);
    }

    /**
     * Parses all WP Shortcode attributes
     *
     * @param    array    $atts    The shortcode attributes
     */
    public function processAtts($atts)
    {
        if (empty($this->formData)) {
            $this->formData    = new stdClass();
        }

        if (!isset($this->formData->slug)) {
            $atts    = shortcode_atts(
                array(
                    'name'         => '',
                    'user_id'      => 0,
                    'user-id'      => 0,
                    'search'       => '',
                    'shortcodeid'  => -1,
                    'shortcode-id' => -1,
                    'id'           => -1,
                    'only-own'     => false,
                    'onlyown'      => false,
                    'archived'     => false,
                    'all'          => false,
                ),
                $atts
            );

            if ($atts['user-id'] == 0 && $atts['user_id'] !== 0) {
                $atts['user-id']      = $atts['user_id'];
            }

            if ($atts['shortcode-id'] == -1 && $atts['shortcodeid'] !== -1) {
                $atts['shortcode-id'] = $atts['shortcodeid'];
            }

            if (empty($atts['only-own'])) {
                $atts['only-own']     = $atts['onlyown'];
            }

            $this->shortcodeId        = $atts['shortcode-id'];
            if ($this->shortcodeId == -1 && $atts['id'] !== -1) {
                $this->shortcodeId    = $atts['id'];
            }

            $this->onlyOwn            = $atts['only-own'];

            $this->all                = $atts['all'];
            $this->showArchived       = $atts['archived'];

            if ( is_numeric($atts['user-id'] ?? '') && $atts['user-id'] > 0) {
                $this->userId    = $atts['user-id'];
            }

            $this->getForm(2, 3);

            $this->getAllFormElements();
        }
    }

    /**
     * Get submission value
     *
     * @param    int        $submissionId    The id of a submission
     * @param    string    $elementId        The element_id of the submission value
     * @param    int        $subId            The sub id in case of multiple values for the same key
     * @param    bool    $returnArray    Wheter to return an array of values, default false
     */
    public function getSubmissionValue($submissionId, $elementId, $subId = '', $returnArray = false)
    {
        global $wpdb;

        /**
         * Check if the requested submission is already in the submissions property, if so return the value from there instead of querying the database
         */
        if (!empty($this->submissions)) {
            foreach ($this->submissions as $submission) {
                if ($submission->id == $submissionId && isset($submission->{$elementId})) {
                    return $submission->{$elementId};
                }
            }
        }

        $baseQuery    = "SELECT `value` FROM %i WHERE ";
        $where        = [
            'submission_id = %d',
            'element_id = %s'
        ];

        $values        = [
            $this->submissionValuesTableName,
            $submissionId,
            $elementId
        ];

        if (is_numeric($subId)) {
            $where[]    = "sub_id = %d";
            $values[]    = $subId;
        }

        /**
         * Add the metas to the submissions
         */
        /**
         * Apply filter to modify the query
         * @param array containing
         *    string $base        The base query
         *    array    $where       Array of where statements
         *    array    $values      Array of values for the where statements
         * @param   int     $userId   The user Id
         * @param   object  $object The current instance
         */
        $filtered    = apply_filters(
            'tsjippy-forms-formdata-retrieval-query',
            [
                'baseQuery' => $baseQuery,
                'where'     => $where,
                'values'    => $values,
            ],
            $this->userId,
            $this
        );

        extract($filtered);

        $query      = $baseQuery . implode(' AND ', $where);

        // phpcs:disable
        $results    = $wpdb->get_col(
            $wpdb->prepare($query, ...$values)
        );
        // phpcs:enable

        $results = array_map(function ($value) {
            return maybe_unserialize($value);
        }, $results);

        if ($returnArray) {
            return $results;
        }

        if (empty($results)) {
            return '';
        }

        if ($subId === '' || empty($results[$subId])) {
            return $results[0];
        }
        return $results[$subId];
    }

    /**
     * Add signal data to wp_mail args
     *
     * @param    array    $args    The wp_mail args
     */
    public function addFormData($args)
    {
        $args['submission'] = $this->submission;

        return $args;
    }

    /**
     * Replaces placeholder with the value
     *
     * @param    string   $string          The string to check for placeholders
     * @param    array    $replaceValues   An indexed array where the index is the keyword and the value the keyword should be replaced with. Default empty, in that case form results are used.
     *
     * @return   string                    The filtered string
     */
    public function processPlaceholders($string, $replaceValues = '')
    {
        if (empty($string)) {
            return $string;
        }

        if (empty($replaceValues) && empty($this->submission)) {
            return false;
        }

        if (!empty($this->submission)) {
            if (empty($replaceValues)) {
                $replaceValues = (array) $this->submission;
            }

            if (empty($this->submission->submissiondate)) {
                $this->submission->submissiondate = gmdate('d F y', strtotime($this->submission->time_created));
                $this->submission->editdate       = gmdate('d F y', strtotime($this->submission->time_last_edited));
            }
        }

        // Replace ids with names
        foreach ($replaceValues as $index => $value) {
            if (is_numeric($index)) {
                $replaceValues[$this->getElementById($index, 'name')]    = $value;
            }
        }

        $pattern = '/%([^%;]*)%/i';
        //Execute the regex
        preg_match_all($pattern, $string, $matches);

        //loop over the results
        foreach ($matches[1] as $match) {
            $replaceValue    = $replaceValues[$match] ?? '';

            // Empty
            if (empty($replaceValue)) {
                $replaceValue    = apply_filters('tsjippy-forms-transform-empty', $replaceValue, $match, $replaceValues, $this);

                if (empty($replaceValue)) {
                    //remove the placeholder, there is no value
                    $string = str_replace("%$match%", '', $string);

                    // mention it in the log
                    TSJIPPY\printArray("No value found for transform value '%$match%' on form '{$this->formData->slug}' with id {$this->formData->id}");

                    $replaceValue    = '';
                }
                $string         = str_replace("%$match%", $replaceValue, $string);
            }

            // Valid file(s)
            elseif (
                is_array($replaceValue)                                    &&    // the form results are an array
                file_exists(ABSPATH . array_values($replaceValue)[0])        // and the first entry is a valid file
            ) {
                // add the ABSPATH to the file paths
                $string = array_map(function ($value) {
                    return ABSPATH . $value;
                }, $replaceValue);
            } else {
                if (is_array($replaceValue) && count($replaceValue) == 1) {
                    $replaceValue    = array_values($replaceValue)[0];
                }

                if (is_array($replaceValue)) {
                    $replaceValue    = apply_filters('tsjippy-forms-transform-array', implode(',', $replaceValue), $replaceValue, $this, $match);
                } elseif (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $replaceValue, $matches)) {
                    $replaceValue    = gmdate(get_option('date_format'), strtotime((string)$matches[1]));
                }

                //replace the placeholder with the value
                if (!file_exists($replaceValue)) {
                    $replaceValue    = str_replace('_', ' ', $replaceValue);
                }

                // wordpress sometimes adds http:// automatically
                if ($match == 'formurl') {
                    $string         = str_replace("http://%$match%", $replaceValue, $string);
                }
                $string             = str_replace("%$match%", $replaceValue, $string);
            }
        }

        return $string;
    }

    /**
     * Get all conditions for a post
     * 
     * @param string  $postId  post id
     */
    public function getAllBlockConditions($postId){
        return TSJIPPY\getFromDb(
            "block-conditions-post-$postId", 
            'forms',
            "select * from %i where post_id=%s",
            $this->blockConditionsTableName,
            $postId
        );
    }

    /**
     * Gets the conditions for a block
     * 
     * @param string  $blockId  block id
     */
    public function getBlockConditions($blockId){
        return TSJIPPY\getFromDb(
            "block-conditions-block-$blockId", 
            'forms',
            "select * from %i where block_id=%s",
            $this->blockConditionsTableName,
            $blockId
        );
    }

    /**
     * Removes a block condition
     * 
     * @param   string  $conditionId
     */
    public function deleteBlockCondition($conditionId){
        TSJIPPY\removeFromDb(
            $this->blockConditionsTableName, 
            [
                'id'    => $conditionId
            ], 
            [
                '%d'
            ], 
            'forms'
        );
    }

    /**
     * Save block conditions
     * 
     * @param   array   $conditions     Array containing conditions
     * @param   string  $blockId
     * @param   int     $postId
     */
    public function saveBlockConditions($conditions, $blockId, $postId){
        $currentConditions  = $this->getBlockConditions($blockId);
    
        $conditions         = TSJIPPY\cleanUpNestedArray($conditions);

        /**
         * Remove any removed conditions
         */
        $toBeRemoved = array_diff(array_column($currentConditions, 'id'), array_column($conditions, 'id'));
        foreach($toBeRemoved as $conditionId){
            $this->deleteBlockCondition($conditionId);
        }

        /**
         * Add or update the remaining conditions
         */
        foreach($conditions as &$condition){
            $condition['post_id']   = $postId;

            ksort($condition);

            if(empty($condition['id'])){
                // Insert returns the new id
                $condition['id'] = TSJIPPY\insertInDb(
                    $this->blockConditionsTableName,
                    $condition,
                    $this->tableFormats[$this->blockConditionsTableName],
                    'forms'
                );

                if(is_wp_error($condition['id'])){
                    TSJIPPY\printArray($condition['id']);

                    unset($condition['id']);
                }
            }else{
                $result = TSJIPPY\updateDbValue(
                    $this->blockConditionsTableName,
                    $condition,
                    [
                        'id' => $condition['id']
                    ],
                    $this->tableFormats[$this->blockConditionsTableName],
                    [
                        '%s'
                    ],
                    'forms'
                );

                if(is_wp_error($result)){
                    TSJIPPY\printArray($result);
                }
            }
        }
        unset($condition);

        return $conditions;
    }

    /**
     * Gets the form e-mail settings for a specific form
     * 
     * @param   int     $blockId    The formbuilder block id
     * @return  array               The form e-mail settings
     */
    public function getFormEmailSettings($blockId){
        return TSJIPPY\getFromDb(
            "form-email-settings-$blockId", 
            'forms',
            "select * from %i where post_id=%s",
            $this->formEmailTable,
            $blockId
        );
    }
}
