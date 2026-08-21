<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use WP_Error;

use function TSJIPPY\removeFromDb;

if (! defined('ABSPATH')) {
    exit;
}

class SaveFormSettings extends Forms
{
    /**
     * Constructor
     *
     * @param    string  $blockId  The blockId
     * @param    bool    $all      Whether to show all elements or only the visible ones
     * @param    int     $pageSize The number of elements to show per page
     * @param    string  $postId   The post id to get the form for
     * @param    string  $formUrl  The url of the form
     * @param    int     $userId   The user id to get the form for
     */
    public function __construct($blockId='', $all=false, $pageSize=50, $postId='', $formUrl='', $userId=0)
    {
        parent::__construct(blockId: $blockId, all: $all, pageSize:$pageSize, postId:$postId, userId:$userId);
    }

    /**
     * Prepares an data for storages in db
     *
     * @param    string            $table        The table to insert/update the data into
     * @param     object|array    $data        The data to be stored
     * @param    array            $where        The where clause for updates
     * @param    array            $whereFormat The format of the where clause
     *
     * @return    array                        The data ready for db injection
     */
    public function insertOrUpdateData($table, &$data, $where = [], $whereFormat = ['%d'])
    {
        if (empty($table) || empty($data)) {
            return new WP_Error('forms', 'Please supply a table and data to insert/update');
        }

        global $wpdb;

        $shouldObject    = false;
        if (is_object($data)) {
            $data            = (array)$data;
            $shouldObject    = true;
        }

        // fix possible where indexes
        foreach ($where as $index => &$val) {
            if (!is_string($val)) {
                continue;
            }

            if (!is_numeric($val) && !empty($data[$val])) {
                $val    = $data[$val];
                continue;
            }

            $newVal    = str_replace('_', '-', $val);
            if (!is_numeric($val) && !empty($data[$newVal])) {
                $val    = $data[$newVal];
            }

            if (empty($val)) {
                unset($where[$index]);
            }
        }
        unset($val);

        $formats    = $this->tableFormats[$table];

        // Fix indexes
        foreach ($data as $index => $value) {
            unset($data[$index]);

            $value    = TSJIPPY\cleanUpNestedArray($value);

            if (!empty($value)) {
                $value    = maybe_serialize($value);
            }

            $data[str_replace('-', '_', $index)] = $value;
        }

        // Remove data without a column in the db
        foreach (array_diff_key($data, $formats) as $key => $val) {
            unset($data[$key]);
        }

        // Remove unnecesary formats
        foreach (array_diff_key($formats, $data) as $key => $val) {
            unset($formats[$key]);
        }

        ksort($data);

        if (empty($where)) {
            $result = TSJIPPY\insertInDb(
                $table,
                $data,
                $formats,
                'forms'
            );
        } else {
            //Update element
            $result = TSJIPPY\updateDbValue(
                $table,
                $data,
                $where,
                $formats,
                $whereFormat,
                'forms'
            );

            // Nothing got updated, maybe we should create instead of update
            if ($result == false) {
                // check if this already exists
                // phpcs:ignore
                $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE " . implode('=%s AND ', array_keys($where)) . "=%s", array_values($where)));

                if ($wpdb->num_rows === 0) {
                    // Insert instead
                    $result = TSJIPPY\insertInDb(
                        $table,
                        $data,
                        $formats,
                        'forms'
                    );
                }
            }
        }

        // unserialize again
        foreach ($data as $index => &$value) {
            if (!empty($value)) {
                $value    = maybe_unserialize($value);
            }
        }

        if ($shouldObject) {
            $data    = (object)$data;
        }

        if ($wpdb->last_error !== '') {
            return new WP_Error('forms', $wpdb->last_error);
        }

        return $result;
    }

    /**
     * Stores the form reminder settings in the db
     *
     * @param    int|string   $blockId    The id of the form to update the reminder settings for
     * @param    array        $settings    The reminder settings to update, this should be an
     *
     * return    true|WP_Error                The result or error on failure
     */
    public function updateFormReminder($blockId, $settings)
    {
        if (empty($blockId)) {
            return new \WP_Error('Error', 'Please supply a formbuilder block id');
        }

        if (empty($settings)) {
            return new \WP_Error('Error', 'Please supply the form settings');
        }

        // Store roles inversed for use in isset
        $settings['conditions']['roles']    = array_flip($settings['conditions']['roles'] ?? []);

        $result    = $this->insertOrUpdateData($this->formReminderTable, $settings, ['block_id' => $blockId]);
        if (is_wp_error($result)) {
            return $result;
        }

        do_action('tsjippy-forms-after-form-reminder-save', $settings, $this);

        return true;
    }

    /**
     * Saves the column settings for a table shortcode
     * @param    array        $settings        The column settings to be saved
     * @param    int|string    $shortcodeId    The id of the shortcode these settings belong
     * @return    true|WP_Error                The result or error on failure
     */
    public function saveColumnSettings($settings = [], $shortcodeId = '')
    {
        $priority    = 0;

        foreach ($settings as $elementId => $column) {
            if (!is_array($column)) {
                continue;
            }

            $priority++;
            $column['priority']     = $priority;

            $column['element_id']   = $elementId;

            $column['shortcode_id'] = $shortcodeId;

            //if there are edit rights defined
            if (!empty($column['edit-right-roles'])) {
                //create view array if it does not exist
                if (empty($column['view-right-roles']) || !is_array($column['view-right-roles'])) {
                    $column['view-right-roles'] = [];
                }

                //merge and save
                $column['view-right-roles'] = array_flip(array_merge($column['view-right-roles'], $column['edit-right-roles']));
            }
            $column['edit-right-roles']     = array_flip($column['edit-right-roles']);

            $where    = [];

            if (!empty($column['column-id'])) {
                $where    = [
                    'id'    => $column['column-id']
                ];
            }

            $result    = $this->insertOrUpdateData($this->shortcodeColumnSettingsTable, $column, $where);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Updates the form e-mails in the db
     *
     * @param    array        $formEmails    The form e-mails to be saved, this should be an array of associative arrays where the keys are the column names in the db and the values are the values to update
     * @param    int|string    $formId        The id of the form to update the e-mails for
     *
     * @return    true|WP_Error                    The result or error on failure
     */
    public function saveFormEmails($formEmails, $formId)
    {
        global $wpdb;

        // Remove deleted emails
        $existingEmails    = TSJIPPY\getFromDb(
            "get_email_ids_for_form_$formId",
            "forms",
            "SELECT id FROM %i WHERE form_id = %d",
            $this->formEmailTable,
            $formId
        );

        $emailsToKeep    = array_column($formEmails, 'email-id');

        $emailsToDelete  = array_diff($existingEmails, $emailsToKeep);

        // Remove any deleted e-mails
        if (!empty($emailsToDelete)) {

            $placeholders   = implode(', ', array_fill(0, count($emailsToDelete), '%d'));

            removeFromDb(
                $this->formEmailTable,
                [
                    "DELETE FROM %i WHERE id IN ($placeholders)",
                    $this->formEmailTable,
                    ...$emailsToDelete
                ],
                [],
                'forms'
            );
        }

        $result    = true;

        // Update each email
        foreach ($formEmails as $email) {
            $email['form_id']    = $formId;
            $email['message']    = trim(wp_unslash($email['message']));

            $where               = [];

            // Its an update to an existing one
            if (!empty($email['email-id'])) {
                $where            = [
                    'id' => $email['email-id']
                ];
            }

            $email    = TSJIPPY\cleanUpNestedArray($email);

            $result    = $this->insertOrUpdateData($this->formEmailTable, $email, $where);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        return $result;
    }
}
