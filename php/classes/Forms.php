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
    public bool        $editRights;
    public array       $blockMapping;
    public array       $emailSettings;
    public object      $formData;
    public array       $formBlocks;
    public string      $formEmailTable;
    public object|null $formReminder;
    public string      $formReminderTable;
    public array|WP_Error       $forms;
    public array       $formBlock;
    public string      $jsFileName;
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
    public array       $submitRoles;
    protected array    $tableFormats;
    public \WP_User    $user;
    public int         $userId;
    protected string   $userIdBlockName;
    public array       $userRoles;
    public array        $defaultArrayValues;
    public array        $defaultValues;
    public array        $usermeta;
    public array        $nonInputs;

    /**
     * Constructor
     *
     * @param string   $blockId     THe block id of the form block
     * @param int      $postId      The post id the form is in
     * @param bool     $all         Whether to retrieve all submissions or not
     * @param int      $pageSize    Number of submissions per page
     * @param int      $userId      User ID to retrieve form for
     */
    public function __construct($blockId='', $postId = -1, $all = false, $pageSize = 50, $userId = 0)
    {
        global $wpdb;

        $this->all                          = $all;
        $this->blockMapping               = [];
        $this->emailSettings                = [];
        $this->formData                     = new stdClass();
        $this->formEmailTable               = $wpdb->prefix . 'tsjippy_form_emails';
        $this->formBlocks                 = [];
        $this->formReminder                 = null;
        $this->formReminderTable            = $wpdb->prefix . 'tsjippy_form_reminders';
        $this->forms                        = [];
        $this->formBlock                    = [];
        $this->jsFileName                   = '';
        $this->objectName                   = '';
        $this->onlyOwn                      = false;
        $this->pageSize                     = $pageSize;
        $this->shortcodeColumnSettingsTable = $wpdb->prefix . 'tsjippy_form_shortcode_column_settings';
        if(empty($this->shortcodeId)){
            $this->shortcodeId                  = -1;
        }
        $this->shortcodeTable               = $wpdb->prefix . 'tsjippy_form_shortcodes';
        $this->showArchived                 = false;
        $this->slugs                        = [];
        $this->submission                   = null;
        $this->submissions                  = [];
        $this->submissionTableName          = $wpdb->prefix . 'tsjippy_form_submissions';
        $this->submissionValuesTableName    = $wpdb->prefix . 'tsjippy_form_submission_values';
        $this->blockConditionsTableName     = $wpdb->prefix . 'tsjippy_form_block_conditions';
        $this->submitRoles                  = [];
        $this->tableFormats                 = [];
        $this->user                         = wp_get_current_user();
        $this->userId                       = $this->user->ID;  // The user id for who we retrieve a form (results)
        $this->userIdBlockName            = '';
        $this->userRoles                    = array_flip($this->user->roles);
        $this->formData->blockId            = $blockId;
        $this->formData->postId             = $postId;
        $this->formData->post               = null;
        $this->formData->split_blocks     = [];

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

        $this->nonInputs                    = [
            'label'       => 1,
            'button'      => 1,
            'datalist'    => 1,
            'formstep'    => 1,
            'info'        => 1,
            'heading'     => 1,
            'group'       => 1,
            'paragraph'   => 1,
        ];

        if ($all) {
            $this->pageSize                 = 99999;
        }

        //calculate full form rights
        $postAuthor    = 0;
        // phpcs:ignore
        if ($postId != -1) {
            $this->formData->post        = get_post($postId);
            if (!empty($this->post)) {
                $postAuthor    = $this->formData->post->post_author;
            }
        }

        if (array_intersect_key(['administrator' => 1, 'editor' => 1], $this->userRoles) || $postAuthor == $this->user->ID) {
            $this->editRights        = true;
        } else {
            $this->editRights        = false;
        }

        // $this->userId is the user id for whom the form is submitted
        $this->userId    = $userId === 0 ? $this->user->ID : $userId;

        if(!empty($blockId) || $postId != -1){
            $this->getForm();

            $this->getAllformBlocks();
        }

        $this->tableFormats();

        $this->defaultArrayValues     = [];
        $this->defaultValues          = [];
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
            block_id tinytext,
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
            block_id mediumint(9) NOT NULL,
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
            'block_id'    => '%d',
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
            'block_id'       => '%s',
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
        $postId        = TSJIPPY\getFromDb(
            "get_form_by_submission_id_$submisisonId",
            "forms",
            "SELECT block_id, post_id FROM %i WHERE id = %d LIMIT 1",
            $this->submissionTableName,
            $submisisonId
        );

        if (empty($postId)) {
            return new WP_Error('forms', "No form found for submission id $submisisonId");
        }

        return $postId;
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

        $this->formReminder    = $results[0];

        return $this->formReminder;
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
            "get_email_settings_" . $this->formData->blockId,
            "forms",
            "select * from %i where block_id=%d",
            $this->formEmailTable,
            $this->formData->blockId
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
     * Resets the instance form data
     *
     */
    public function reset(){
        // Reset form data
        $this->formData->blockId = null;
        $this->formData->postId = null;
        $this->formData->post   = null;

        $this->blockMapping   = [];

        $this->formBlocks     = [];

        $this->formBlock        = [];
    }

    /**
     * Creates the block mappers to find blocks based on id, name or type
     *
     * @param    bool    $force        Whether to requery, default false
     */
    public function blockMapper($force = false)
    {
        if (
            empty($this->formData) ||
            (
                isset($this->blockMapping) &&
                !empty($this->blockMapping['type']) &&
                !$force
            )
        ) {
            return;
        }

        //used to find the index of an block based on its unique id, type or name
        $this->blockMapping                                    = [];
        $this->blockMapping['type']                            = [];
        $this->blockMapping['slug']                            = [];

        $this->getAllformBlocks(true);

        foreach ($this->formBlocks as $index => $block) {
            $this->blockMapping['id'][$block->blockId]          = $index;
            $this->blockMapping['slug'][$block->slug][$index]   = $index;
            $this->blockMapping['type'][$block->type][$index]   = $index;
        }
    }

    /**
     * Finds all blocks and post ids who are of the formbuilder type
     */
    public function getForms($type=null){
        if(!empty($this->forms)){
            return;
        }

        global $wpdb;

        $likeString = '%'.$wpdb->esc_like('<!-- wp:tsjippy-forms/formbuilder').'%';

        if($type == 'meta'){
            $likeString .= $wpdb->esc_like('"user_meta":true').'%';
        }

        $posts = TSJIPPY\getFromDb(
            'all_forms',
            'forms',
            "SELECT * FROM %i where post_content like %s",
            $wpdb->posts,
            $likeString
        );

        foreach($posts as $post){
            $this->getForm($post);

            $this->forms[]   = [
                'formData'  => clone($this->formData),
                'blocks'  => $this->formBlocks,
                'formBlock' => $this->formBlock,
            ];
        }
    }

    /**
     * Finds a formbuilder block on a post and parses it to html
     * 
     * @param   object|int  $post   The post or post id to search for the block. Default empty to use the object post
     * 
     * @return  string|false   The form html or false if not found
     */
    public function getForm($post='', $blockId=''){
        if(empty($post) && $this->formData->post == null){
            $post  = false;

            if(!empty($this->formData->postId)){
                $post   = get_post($this->formData->postId);
            }

            if(empty($post)){
                if(empty($blockId)){
                    return $this->formBlock = [];
                }

                $this->getForms();

                foreach($this->forms as $form){
                    if($form->blockId == $blockId){
                        $post   = $form->postId;
                        break;
                    }
                }
            }
        }

        if(empty($post)){
            $post   = $this->formData->post;
        }elseif(is_numeric($post)){
            if(!empty($this->formData->post) && $this->formData->post->ID == $post){
                $post = $this->formData->post;
            }else{
                $post   = get_post($post);
            }
        }
        
        if(
            
            $post != $this->formData->post || 
            (
                is_numeric($post) && 
                $post != $this->formData->postId
            )
        ){
            $this->reset();
        }

        $this->formData->post = $post;

        if(empty($blockId)){
            $blockId    = $this->formData->blockId;
        }

        $blocks = parse_blocks($post->post_content);

        foreach($blocks as $block){
            if(
                $block['attrs']['blockId'] == $blockId ||               // This is the block we need
                (
                    empty($blockId) &&
                    $block['blockName'] == "tsjippy-forms/formbuilder"  // Just take the first form on the page
                )
            ){
                $this->formBlock = $block;

                foreach($block['attrs'] as $key => $attribute){
                    $this->formData->$key   = maybe_unserialize($attribute);
                }

                $this->blockMapper(true);

                return true;
            }
        }

        return false;
    }

    /**
     * Parses a formbuilder block to html
     * 
     * @param   array   $block
     * 
     * @return string   The html
     */
    public function showForm(){
        return render_block($this->formBlock);
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
            $this->slugs[] = $form->slug;
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
     * Finds an block by its id
     *
     * @param    int       $id        the block id
     * @param    string    $key    A specific block attribute to return. Default empty
     *
     * @return    object|array|string|false            The block or block property
     */
    public function getBlockById($id, $key = '')
    {
        global $post;

        if (empty($id)) {
            return false;
        }

        if (!is_numeric($id) && gettype($id) == 'string') {
            return $this->getBlockBySlug($id, $key);
        }

        //load if needed
        if (empty($this->formData)) {
            $this->getForm();
        }

        if (!isset($this->blockMapping['id'][$id])) {
            $this->blockMapper(true);

            if (!isset($this->blockMapping['id'][$id])) {
                $url    = get_page_link($post);

                TSJIPPY\printArray("Block with id '$id' not found on form '{$this->formData->slug}' with id '{$this->formData->blockId}' on page $url", false);
                return false;
            }
        }
        $blockIndex    = $this->blockMapping['id'][$id];

        $block        = $this->formBlocks[$blockIndex];
        if (empty($block)) {
            return false;
        }

        $block->index    = $blockIndex;

        if (empty($key)) {
            return $block;
        } else {
            return $block->$key;
        }
    }

    /**
     * Finds an block by its slug
     *
     * @param    string    $slug               The block slug
     * @param    string    $key                A specific block attribute to return. Default empty
     * @param    bool      $single             Wheter to return a singel block, default true
     *
     * @return    object|array|string|false    The block or an array of blocks or an block property of false if not found
     */
    public function getBlockBySlug($slug, $key = '', $single = true)
    {
        if (empty($slug)) {
            return false;
        }

        //load if needed
        if (empty($this->blockMapping)) {
            $result    = $this->getForm();

            if (is_wp_error($result)) {
                return $result;
            }
        }

        if (!isset($this->blockMapping['slug'][$slug])) {
            // first part of the name, remove anything after [
            $slugNew    = explode('[', $slug)[0];

            if (isset($this->blockMapping['slug'][$slugNew])) {
                // remove '[]'
                $slug    = $slugNew;
            } elseif (isset($this->blockMapping['slug'][$slug . '[]'])) {
                // add []
                $slug    = '[]';
            } elseif (!empty($this->formData->split_blocks)) {
                // only the last part of a splitted name is given
                $mainName    = explode('[', $this->getBlockById($this->formData->split_blocks[0], 'slug'))[0];

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

                return $this->getBlockBySlug($slug, $key, $single);
            } else {
                //TSJIPPY\printArray("Block with slug $slug not found on form {$this->formData->slug} with id {$this->formData->blockId}");
                return false;
            }
        }
        $blockIndexes    = $this->blockMapping['slug'][$slug];

        $blocks        = [];

        foreach ($blockIndexes as $index) {
            $block        = $this->formBlocks[$index];
            $block->index = $index;
            $blocks[]     = $block;
        }

        if (!$single) {
            return $blocks;
        }

        $block    = $blocks[0];

        if (empty($key)) {
            return $block;
        } else {
            return $block->$key;
        }
    }

    /**
     * Finds an block by its type
     *
     * @param    string    $type    The block type
     * @param    bool    $load    Try to load the formdata if empty default true
     *
     * @return    object|array|string|false            An array of blocks
     */
    public function getBlockByType($type, $load = true)
    {
        if (empty($type)) {
            return false;
        }

        //load if needed
        if (empty($this->blockMapping['type']) && $load) {
            $result    = $this->getForm();

            if (is_wp_error($result)) {
                return $result;
            }
        }

        if (!isset($this->blockMapping['type'][$type])) {
            //TSJIPPY\printArray("Block with id $type not found");
            return false;
        }

        $blockIndexes    = $this->blockMapping['type'][$type];

        $blocks        = [];

        foreach ($blockIndexes as $index) {
            $block        = $this->formBlocks[$index];
            $block->index = $index;
            $blocks[]     = $block;
        }

        return $blocks;
    }

    /**
     * Finds the user name block in a form
     *
     * @return    string    the block name or false if no name block is found
     */
    public function findUserNameBlockName()
    {
        // find the user id block
        $userNameKey    = false;

        if ($this->getBlockBySlug('name')) {
            $userNameKey    = 'name';
        } elseif ($this->getBlockBySlug('fullname')) {
            $userNameKey    = 'fullname';
        } elseif ($this->getBlockBySlug('firstname')) {
            $userNameKey    = 'firstname';
        } elseif ($this->getBlockBySlug('lasttname')) {
            $userNameKey    = 'lasttname';
        }

        return $userNameKey;
    }

    /**
     * Finds the phonenumber block in a form
     *
     * @return    string    the block name or false if no phonenumber block is found
     */
    public function findPhoneNumberBlockName()
    {
        // find the user id block
        $phonenumberKey    = false;

        if ($this->getBlockBySlug('phone')) {
            $phonenumberKey    = 'phone';
        } elseif ($this->getBlockBySlug('phonenumber')) {
            $phonenumberKey    = 'phonenumber';
        } elseif ($this->getBlockBySlug('phone_number')) {
            $phonenumberKey    = 'phone_number';
        }

        return $phonenumberKey;
    }

    /**
     * Finds the e-mail block in a form
     *
     * @return    string    the block name or false if no e-mail block is found
     */
    public function findEmailBlockName()
    {
        // find the user id block
        $emailKey    = false;

        if ($this->getBlockBySlug('email')) {
            $emailKey    = 'email';
        } elseif ($this->getBlockBySlug('e-mail')) {
            $emailKey    = 'e-mail';
        }

        return $emailKey;
    }

    /**
     * Parses the innerblocks of a block
     * 
     * @param   array   $block
     */
    private function parseBlocks($block){
        $newBlock = new stdClass();

        $newBlock->block         = $block;

        $newBlock->type          = explode('/', $block['blockName'])[1];

        $newBlock->blockId       = $block['attrs']['blockId'] ?? '';

        $newBlock->required      = !empty($block['attrs']['required']);

        $newBlock->slug          = $block['attrs']['name'] ?? '';

        $newBlock->name          = ucfirst(str_replace('_', ' ', $newBlock->slug));

        $this->formBlocks[]   = $newBlock;

        // Get all form blocks
        foreach($block['innerBlocks'] as $innerBlock){
            $this->parseBlocks($innerBlock);
        }
    }

    /**
     * Get all blocks belonging to the current form
     *
     * @param    string     $sortCol        the column to sort on. Default empty
     * @param    int        $formId         The id of the form to get blocks for, default empty
     * @param    bool       $force          Whether to requery, default false
     */
    public function getAllformBlocks($force = false)
    {
        if (!empty($this->formBlocks) && !$force) {
            return '';
        }

        if (empty($this->formBlock)) {
            $this->getForm();

            return new \WP_Error('forms', 'No formBlock given');
        }

        $this->formBlocks   = [];

        foreach($this->formBlock['innerBlocks'] as $innerBlock){
            $this->parseBlocks($innerBlock);
        }

        /**
         * Filters the blocks of this form,
         * @param    array   $blocks  The blocks array
         * @param    object  $object    The form instance
         * @param    bool    $force     Wheter to force a requery
         */
        $this->formBlocks         =  apply_filters('tsjippy-forms-blocks', $this->formBlocks, $this, false);
    }

    /**
     * Get submission value
     *
     * @param    int        $submissionId    The id of a submission
     * @param    string    $blockId        The block_id of the submission value
     * @param    int        $subId            The sub id in case of multiple values for the same key
     * @param    bool    $returnArray    Wheter to return an array of values, default false
     */
    public function getSubmissionValue($submissionId, $blockId, $subId = '', $returnArray = false)
    {
        global $wpdb;

        /**
         * Check if the requested submission is already in the submissions property, if so return the value from there instead of querying the database
         */
        if (!empty($this->submissions)) {
            foreach ($this->submissions as $submission) {
                if ($submission->id == $submissionId && isset($submission->{$blockId})) {
                    return $submission->{$blockId};
                }
            }
        }

        $baseQuery    = "SELECT `value` FROM %i WHERE ";
        $where        = [
            'submission_id = %d',
            'block_id = %s'
        ];

        $values        = [
            $this->submissionValuesTableName,
            $submissionId,
            $blockId
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
                $replaceValues[$this->getBlockById($index, 'name')]    = $value;
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
                    TSJIPPY\printArray("No value found for transform value '%$match%' on form '{$this->formData->slug}' with id {$this->formData->blockId}");

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
     * Gets the conditions for a post
     * 
     * @param int  $postId  post id
     */
    public function getBlockConditions($postId=''){
        if(empty($postId)){
            $postId = $this->formData->postId;
        }
        
        return TSJIPPY\getFromDb(
            "block-conditions-block-$postId", 
            'forms',
            "select * from %i where post_id=%d",
            $this->blockConditionsTableName,
            $postId
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

    /**
     * Gets all unique user meta data keys
     * 
     * @return array $allMetaKeys     Array containing all user meta keys
     */
    public function userMetaKeys(){
        $value = wp_cache_get('user-meta-keys', 'tsjippy_forms', false, $found);

        if ($found) {
            return $value;
        }

        global $wpdb;

        $allMetaKeys    = array_flip(TSJIPPY\getFromDb(
            'all-meta-keys',
            'forms',
            "SELECT distinct meta_key FROM %i where meta_key not like %s and meta_key not like %s ORDER BY `meta_key` ASC",
            $wpdb->usermeta,
            $wpdb->esc_like('_').'%',
            $wpdb->esc_like("tsjippy_hidden_columns_").'%'
        ));

        $familyMetaKeys = TSJIPPY\FAMILY\getFamilyMetaKeys();

        $metaKeys       = array_merge($allMetaKeys, $familyMetaKeys);

        ksort($metaKeys, SORT_STRING | SORT_FLAG_CASE);

        wp_cache_set('user-meta-keys', $metaKeys, 'tsjippy_forms');

        return $metaKeys;
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
}
