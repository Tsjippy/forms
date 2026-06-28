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
    public string      $elTableName;
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
    public array       $submitRoles;
    protected array    $tableFormats;
    public string      $tableName;
    public \WP_User    $user;
    public int         $userId;
    protected string   $userIdElementName;
    public array       $userRoles;
    public array       $inputTags;
    public array       $checkboxTypes;

    public function __construct($atts = [], $all = false, $pageSize = 50, $postId = '', $formUrl = '',  $userId = 0)
    {
        global $wpdb;

        $this->all                          = $all;
        $this->clonableFormStep             = false;
        $this->elementMapping               = [];
        $this->elTableName                  = $wpdb->prefix . 'tsjippy_form_elements';
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
        $this->submitRoles                  = [];
        $this->tableFormats                 = [];
        $this->tableName                    = $wpdb->prefix . 'tsjippy_forms';
        $this->user                         = wp_get_current_user();
        $this->userId                       = $this->user->ID;  // The user id for who we retrieve a form (results)
        $this->userIdElementName            = '';
        $this->userRoles                    = $this->user->roles;

        if ($all) {
            $this->pageSize                    = 99999;
        }

        // $this->userId is the user id for whom the form is submitted
        if (
            array_intersect($this->userRoles, $this->submitRoles)     &&    // we have the permission to submit on behalf on someone else
            $userId != 0
        ) {
            $this->userId    = $userId;
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

        if (array_intersect(['administrator', 'editor'], $this->userRoles) || $postAuthor == $this->user->ID) {
            $this->editRights        = true;
        } else {
            $this->editRights        = false;
        }

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

        //Main table
        $sql = "CREATE TABLE {$this->tableName} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            slug tinytext NOT NULL,
            name text,
            version text NOT NULL,
            button_text text,
            succes_message text,
            include_id boolean,
            save_in_meta boolean,
            url text,
            actions text,
            autoarchive boolean,
            autoarchive_el integer,
            autoarchive_value text,
            split text,
            full_right_roles LONGTEXT,
            submit_others_form LONGTEXT,
            upload_path LONGTEXT,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->tableName, $sql);

        // Form Reminders Table
        $sql = "CREATE TABLE {$this->formReminderTable} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id int,
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

        // Form element table
        $sql = "CREATE TABLE {$this->elTableName} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id int NOT NULL,
            type text NOT NULL,
            priority int,
            width int default 100,
            function_name text,
            folder_name text,
            slug text NOT NULL,
            nametext,
            text text,
            html text,
            value_list text,
            default_value text,
            default_array_value text,
            options text,
            required boolean default False,
            mandatory boolean default False,
            recommended boolean default False,
            wrap boolean default False,
            hidden boolean default False,
            multiple boolean default False,
            library boolean default False,
            edit_image boolean default False,
              conditions longtext,
            warning_conditions longtext,
            `add` longtext,
            `remove` longtext,
            PRIMARY KEY  (id)
         ) $charsetCollate;";

        maybe_create_table($this->elTableName, $sql);

        // form e-mails table
        $sql = "CREATE TABLE {$this->formEmailTable} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id int NOT NULL,
            email_trigger tinytext,
            submitted_trigger tinytext,
            conditional_field tinytext,
            conditional_fields longtext,
            conditional_value tinytext,
            from_email tinytext,
            `from` tinytext,
            conditional_from_email longtext,
            else_from tinytext,
            email_to tinytext,
            `to` tinytext,
            conditional_email_to longtext,
            else_to tinytext,
            subject longtext,
            message longtext,
            headers longtext,
            files longtext,
            PRIMARY KEY  (id)
       ) $charsetCollate;";

        maybe_create_table($this->formEmailTable, $sql);

        // shortcodeTableSettings table
        $sql = "CREATE TABLE {$this->shortcodeTable} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id int NOT NULL,
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
            form_id    int NOT NULL,
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
    }

    /**
     * Defines the formats of each column in each table
     */
    private function tableFormats()
    {
        // Form Settings
        $formats            = [
            'slug'               => '%s',
            'version'            => '%s',
            'button_text'        => '%s',
            'succes_message'     => '%s',
            'include_id'         => '%d',
            'name'               => '%s',
            'save_in_meta'       => '%d',
            'url'                => '%s',
            'actions'            => '%s',
            'autoarchive'        => '%d',
            'autoarchive_el'     => '%d',
            'autoarchive_value'  => '%s',
            'split'              => '%s',
            'full_right_roles'   => '%s',
            'submit_others_form' => '%s',
            'upload_path'        => '%s'
        ];

        $this->tableFormats[$this->tableName]         = apply_filters('tsjippy-forms-form-table-formats', $formats, $this);

        // From Reminder Settings
        $formats            = [
            'form_id'             => '%d',
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

        // Form Elements
        $formats        = [
            'form_id'             => '%d',
            'type'                => '%s',
            'priority'            => '%d',
            'width'               => '%d',
            'function_name'       => '%s',
            'folder_name'         => '%s',
            'slug'                => '%s',
            'name'                => '%s',
            'text'                => '%s',
            'html'                => '%s',
            'value_list'          => '%s',
            'default_value'       => '%s',
            'default_array_value' => '%s',
            'options'             => '%s',
            'required'            => '%d',
            'mandatory'           => '%d',
            'recommended'         => '%d',
            'wrap'                => '%d',
            'hidden'              => '%d',
            'multiple'            => '%d',
            'library'             => '%d',
            'edit_image'          => '%d',
            'conditions'          => '%s',
            'remove'              => '%s',
            'add'                 => '%s',
        ];

        $this->tableFormats[$this->elTableName]       = apply_filters('tsjippy-forms-element-table-formats', $formats, $this);

        // Form Emails
        $formats    = [
            'form_id'                => '%d',
            'email_trigger'          => '%s',
            'submitted_trigger'      => '%s',
            'conditional_field'      => '%s',
            'conditional_fields'     => '%s',
            'conditional_value'      => '%s',
            'from_email'             => '%s',
            'from'                   => '%s',
            'conditional_from_email' => '%s',
            'else_from'              => '%s',
            'email_to'               => '%s',
            'to'                     => '%s',
            'conditional_email_to'   => '%s',
            'else_to'                => '%s',
            'subject'                => '%s',
            'message'                => '%s',
            'headers'                => '%s',
            'files'                  => '%s'
        ];

        $this->tableFormats[$this->formEmailTable]    = apply_filters('tsjippy-forms-email-table-formats', $formats, $this);

        // Form Submissions
        $formats    = [
            'form_id'          => '%d',
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
            'form_id'          => '%d',
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

        // Sort formats by key to make sure they are in the same order as the data
        foreach ($this->tableFormats as &$format) {
            ksort($format);
        }
    }

    /**
     * Inserts a new form in the db
     *
     * @param    string    $slug    The form slug
     *
     * @return    int|WP_Error    The form id or error ion failure
     */
    public function insertForm($slug = '')
    {
        global $wpdb;

        if (empty($this->formData)) {
            $this->formData     =  new \stdClass();
        }

        if (empty($slug) && !empty($this->formData->slug)) {
            $slug = $this->formData->slug;
        } else {
            return new WP_Error('forms', 'No form slug given');
        }

        $slug    = str_replace([' ', '/'], '-', strtolower($slug));

        // Check if name already exists
        $newName = $slug;
        $i       = 1;
        while (true) {
            $result    = TSJIPPY\getFromDb(
                "get_form_with_slug_$newName",
                "forms",
                "SELECT * FROM %i WHERE slug = %s",
                $this->tableName,
                $newName
            );

            if (empty($result)) {
                break;
            }

            $newName    = "$slug$i";
            $i++;
        }

        $this->formData->slug    = $newName;

        $result = TSJIPPY\insertInDb(
            $this->tableName,
            array(
                'slug'            => $this->formData->slug,
                'version'         => 1
            ),
            [
                '%s',
                '%d'
            ],
            'forms'
        );

        if (is_wp_error($result)) {
            return $result;
        }

        $formId = $result;

        /**
         * insert default elements
         */

        // First create the name element as we need its id for the user id element conditions
        $result = TSJIPPY\insertInDb(
            $this->elTableName,
            array(
                'form_id'       => $formId,
                'type'          => 'text',
                'slug'          => 'name',
                'options'       => 'list=users',
                'default_value' => 'display_name',
                'priority'      => 3
            ),
            [
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d'
            ],
            'forms'
        );

        if (is_wp_error($result)) {
            return $result;
        }

        $elementId    = $result;

        $elements = [
            array(
                'type'                     => 'number',
                'slug'                    => 'user_id',
                'default_value'         => 'user_id',
                'hidden'                => true,
                'conditions'            => serialize([
                    [
                        'rules'                => [
                            [
                                'conditional-field'    => $elementId,
                                'equation'            => 'changed',
                            ]
                        ],
                        'action'            => 'property',
                        'property-name'        => 'value',
                        'property-value'    => $elementId
                    ]
                ]),
                'priority'                => 1
            ),
            array(
                'type'                     => 'label',
                'slug'                    => 'name-label',
                'text'                     => 'Your Name',
                'wrap'                    => true,
                'priority'                => 2
            ),
            array(
                'type'                     => 'datalist',
                'slug'                    => 'users',
                'default_array_value'     => 'all_users',
                'priority'                => 4
            )
        ];

        foreach ($elements as $element) {
            $element['form_id'] = $formId;

            TSJIPPY\insertInDb(
                $this->elTableName,
                $element,
                [],
                'forms'
            );
        }
    }

    /**
     * Checks if the current form exists in the db. If not, inserts it
     */
    public function maybeInsertForm($formId = '')
    {
        global $wpdb;

        if (!isset($this->formData->slug)) {
            return new WP_ERROR('forms', 'No form slug given');
        }

        $query   = "SELECT slug FROM %i WHERE `slug` = %s";
        $values  = [
            $this->tableName,
            $this->formData->slug
        ];

        if (is_numeric($formId)) {
            $query    .= " OR id=%d";
            $values[]   = $formId;
        }
        //check if form row already exists
        // phpcs:disable
        if (!$wpdb->get_var(
            $wpdb->prepare($query, $values)
        )) {
            //Create a new form row
            $this->insertForm();
        }
        // phpcs:enable
    }

    /**
     * Deletes a form
     *
     * @param    int        $formId    The id of the form to be deleted
     *
     * @return    string            The deletion result
     */
    public  function deleteForm($formId)
    {
        global $wpdb;

        // Remove the form
        TSJIPPY\removeFromDb(
            $this->tableName,
            ['id' => $formId],
            ['%d'],
            'forms'
        );

        // remove the form elements
        TSJIPPY\removeFromDb(
            $this->elTableName,
            ['form_id' => $formId],
            ['%d'],
            'forms'
        );

        // emails
        TSJIPPY\removeFromDb(
            $this->formEmailTable,
            ['form_id' => $formId],
            ['%d'],
            'forms'
        );

        // reminders
        TSJIPPY\removeFromDb(
            $this->formReminderTable,
            ['form_id' => $formId],
            ['%d'],
            'forms'
        );

        //shortcodes
        TSJIPPY\removeFromDb(
            $this->shortcodeTable,
            ['form_id' => $formId],
            ['%d'],
            'forms'
        );

        // shortcode setttings
        TSJIPPY\removeFromDb(
            $this->shortcodeColumnSettingsTable,
            ['form_id' => $formId],
            ['%d'],
            'forms'
        );

        // submission values
        TSJIPPY\removeFromDb(
            $this->submissionValuesTableName,
            [
                "DELETE sv FROM %i sv JOIN %i s ON sv.submission_id = s.id WHERE s.form_id = %d",
                $this->submissionValuesTableName,
                $this->submissionTableName,
                $formId
            ],
            [],
            'forms'
        );

        // remove the form submissions
        TSJIPPY\removeFromDb(
            $this->submissionTableName,
            ['form_id' => $formId],
            ['%d'],
            'forms'
        );

        // update or delete posts with this form
        $results    = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID FROM %i WHERE post_content LIKE %s",
                $wpdb->posts,
                "%" . $wpdb->esc_like('[tsjippy_formbuilder slug={$this->formData->slug}]') . "%"
            )
        );

        // remove the shortcode from the page
        foreach ($results as $postId) {
            $post    = get_post($postId);

            $post->post_content    = str_replace('[tsjippy_formbuilder slug={$this->formData->slug}]', '', $post->post_content);

            // delete post
            if (empty($post->post_content)) {
                wp_delete_post($post->ID);
            } else {
                wp_update_post($post);
            }
        }

        /**
         * Flush db cache
         */
        if (wp_cache_supports('flush_group')) {
            wp_cache_flush_group('forms');
        } else {
            wp_cache_flush();
        }

        return "<div class='success'>Deletion of the form with id '$formId' finished successfully.</div>";
    }

    /**
     * Gets all forms from the db
     */
    public function getForms()
    {
        $this->forms =  TSJIPPY\getFromDb(
            "get_all_forms",
            "forms",
            "SELECT * FROM %i",
            $this->tableName
        );
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

        return $this->getForm($formId);
    }

    /**
     * Load a specific form or creates it if it does not exist
     *
     * @param    int        $formId    the form id to load. Default empty
     */
    public function getForm($formId = '')
    {
        global $wpdb;

        // first check if needed
        if (
            !isset($this->formData->version) ||
            (
                !empty($this->formData->id)        &&
                !empty($formId) &&
                $this->formData->id != $formId
            )
        ) {
            // Get the form data
            $query  = "SELECT * FROM %i WHERE ";
            $values = [$this->tableName];
            if (is_numeric($formId)) {
                $query    .= "id= %d";
                $values[] = $formId;
            } elseif (is_numeric($this->formData->id ?? '') && $this->formData->id > -1) {
                $query    .= "id= %d";
                $values[] = $this->formData->id;
            } elseif (!empty($this->formData->slug)) {
                $query    .= "slug= %s";
                $values[] = $this->formData->slug;
            } else {
                return new \WP_Error('forms', 'No form name or id given');
            }

            // phpcs:ignore
            $result = $wpdb->get_row($wpdb->prepare($query, $values));

            // Form does not exist yet
            if (empty($result)) {
                global $post;

                $url = get_page_link($post);

                TSJIPPY\printArray("Form requested on {$post->post_type} on $url does not exist. Query used is '$query'");
                $this->insertForm();
            } else {
                $result                      = map_deep($result, 'maybe_unserialize');

                foreach( [ 'full_right_roles', 'submit_others_form', 'split'] as $key){
                    if(!is_array($result->$key)){
                        $result->$key   = [];
                    }
                }

                // Use the values as keys as well to allow for faster searching with isset
                $result->full_right_roles    = array_combine($result->full_right_roles, $result->full_right_roles);
                $result->submit_others_form  = array_combine($result->submit_others_form, $result->submit_others_form);

                $this->formData              = $result;

                /**
                 * Filters the elements the submission data should be splitted on
                 *
                 * @param    array    $splitElements    The current element id's
                 * @param    object    $object            Form instance
                 */
                $this->formData->split              = apply_filters('tsjippy-forms-split-elements', $this->formData->split, $this);
            }
        }

        $this->elementMapper(true);

        if (!$this->editRights) {
            $editRoles    = ['administrator', 'editor'];
            if (!empty($this->formData->full_right_roles)) {
                $editRoles    = (array)$this->formData->full_right_roles;
            }

            //calculate full form rights
            $object    = get_queried_object();

            if (array_intersect($editRoles, (array)$this->userRoles) || (!empty($object) && $object->post_author == $this->user->ID)) {
                $this->editRights        = true;
            } else {
                $this->editRights        = false;
            }
        }

        if (isset($this->formData->submit_others_form)) {
            $this->submitRoles    = (array)$this->formData->submit_others_form;
        }

        if ($wpdb->last_error !== '') {
            TSJIPPY\printArray($wpdb->print_error());
        }

        $this->jsFileName    = plugin_dir_path(__DIR__) . "../js/dynamic/{$this->formData->slug}forms";

        return true;
    }

    /**
     * Gets the form reminders from the db
     * @param    int    $formId        the form id for which to get the reminders
     */
    public function getFormReminder($formId = '')
    {

        if (empty($formId)) {
            $formId    = $this->formData->id;
        }
        $this->formReminder    = new stdClass();

        $results    =  TSJIPPY\getFromDb(
            "get_form_reminders_$formId",
            "forms",
            "SELECT * FROM %i WHERE form_id = %d",
            $this->formReminderTable,
            $formId
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
            $this->getForm();
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
            $result    = $this->getForm();

            if (is_wp_error($result)) {
                return $result;
            }
        }

        if (!isset($this->elementMapping['slug'][$slug])) {
            // first part of the name, remove anything after [
            $slugNew    = explode('[', $slug)[0];

            if (isset($this->elementMapping['slug'][$slugNew])) {
                // remove '[]'
                $slug    .= $slugNew;
            } elseif (isset($this->elementMapping['slug'][$slug . '[]'])) {
                // add []
                $slug    .= '[]';
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
            $element->index    = $index;
            $elements[]        = $element;
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
            $result    = $this->getForm();

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
            $element->index    = $index;
            $elements[]        = $element;
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
        $query                        = "SELECT * FROM %i WHERE form_id= %d";
        $values                        = [
            $this->elTableName,
            $formId
        ];
        $cacheKey   = "form_elements_$formId";

        if (!empty($sortCol)) {
            $query      .= " ORDER BY %s ASC";
            $values[]    = $sortCol;
            $cacheKey   .= "_sorted_$sortCol";
        }

        // phpcs:ignore
        $elements    =  TSJIPPY\getFromDb($cacheKey, "forms", $query, $values);

        /**
         * Filters the elements of this form,
         * @param    array   $elements  The elements array
         * @param    object  $object    The form instance
         * @param    bool    $force     Wheter to force a requery
         */
        $this->formElements         =  apply_filters('tsjippy-forms-elements', $elements, $this, false);
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
                    'slug'         => '',
                    'formname'     => '',
                    'form-name'    => '',
                    'name'         => '',
                    'user_id'      => 0,
                    'user-id'      => 0,
                    'search'       => '',
                    'shortcodeid'  => -1,
                    'shortcode-id' => -1,
                    'id'           => -1,
                    'formid'       => -1,
                    'form-id'      => -1,
                    'only-own'     => false,
                    'onlyown'      => false,
                    'archived'     => false,
                    'all'          => false,
                ),
                $atts
            );

            if (empty($atts['form-name'])) {
                if (!empty($atts['formname'])) {
                    $atts['form-name'] = $atts['formname'];
                } elseif (!empty($atts['name'])) {
                    $atts['form-name'] = $atts['name'];
                } elseif (!empty($atts['slug'])) {
                    $atts['form-name'] = ucfirst(str_replace('-', ' ', $atts['slug']));
                }
            }

            if (empty($atts['slug'])) {
                $atts['slug']         = str_replace(' ', '-', strtolower($atts['form-name']));
            }

            if ($atts['user-id'] == 0 && $atts['user_id'] !== 0) {
                $atts['user-id']      = $atts['user_id'];
            }

            if ($atts['shortcode-id'] == -1 && $atts['shortcodeid'] !== -1) {
                $atts['shortcode-id'] = $atts['shortcodeid'];
            }

            if ($atts['form-id'] == -1 && $atts['formid'] !== -1) {
                $atts['form-id']      = $atts['formid'];
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

            if (!empty($atts['user-id']) && is_numeric($atts['user-id'])) {
                $this->userId    = $atts['user-id'];
            }

            $this->formData->name     = $atts['form-name'];
            $this->formData->slug     = $atts['slug'];
            $this->formData->id       = $atts['form-id'];

            $this->getForm();

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
}
