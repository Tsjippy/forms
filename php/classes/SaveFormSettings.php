<?php

namespace TSJIPPY\FORMS;

use ParseSplittedFormResults;
use TSJIPPY;
use WP_Embed;
use WP_Error;

use function TSJIPPY\removeFromDb;

if (! defined('ABSPATH')) {
    exit;
}

class SaveFormSettings extends Forms
{
    use CreateJs;

    public function __construct($atts=[], $all=false, $pageSize=50, $postId='', $formUrl='', $userId=0)
    {
        parent::__construct(atts: $atts, all: $all, pageSize:$pageSize, postId:$postId, formUrl:$formUrl, userId:$userId);
    }

    /**
     * Gets a unique name for a form element
     *
     * @param object $element The form element
     * @param bool $update Whether this is an update operation
     * @param object $oldElement The old form element
     * @return string The unique name
     */
    public function getUniqueName($element, $update, $oldElement)
    {
        global $wpdb;

        // Make sure we only are working on the name
        $exploded         = explode('\\', $element->slug);
        $element->slug    = end($exploded);

        // Replace spaces with _
        $element->slug    = str_replace(" ", "_", $element->slug);

        // Make lowercase
        $element->slug    = strtolower($element->slug);

        // Keep only valid chars
        $element->slug = preg_replace('/[^a-zA-Z0-9_\[\]]/', '', $element->slug);

        // Remove ending _
        $element->slug    = trim($element->slug, " \n\r\t\v\0_");

        // Make sure the first char is a letter or _
        $element->slug[0] = preg_replace('/[^a-zA-Z_]/', '_', $element->slug[0]);

        // Check if name is unique
        // Get all elements with this name
        $elements        = $this->getElementBySlug($element->slug, '', false);
        if (
            str_contains($element->slug, '[]')     ||      // Doesn't need to be unique
            (
                $update &&
                $oldElement->slug == $element->slug &&     // Name didn't change
                $elements &&
                count($elements) == 1
            )
        ) {

            return $element->slug;
        }

        $elementName = $element->slug;

        $i = '';

        // getElementBySlug returns false when no match found
        while (true) {
            $existingElement    = $this->getElementBySlug($elementName);

            if (
                !$existingElement ||                             // No existing element found
                $existingElement->slug != $element->slug ||        // Different name found
                (
                    $update &&                                     // Updating existing element
                    $existingElement->id == $element->id         // Same element
                )
            ) {
                break;
            }

            $i++;

            $elementName = "{$element->slug}_$i";
        }

        //update the name
        if ($i != '') {
            $element->slug .= "_$i";
        }

        // only update previous submissions when an update of the name of existing element took place
        if (!$update) {
            return $element->slug;
        }

        // Update the name in the form elements array
        foreach ($this->formElements as &$el) {
            if ($el->id == $element->id) {
                $el->slug    = $element->slug;
                break;
            }
        }

        // update js
        $this->createJs();

        return $element->slug;
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
     * Change an existing form element in the db
     *
     * @param    object|array    $element    The new element data
     *
     * @return    true|WP_Error                The result or error on failure
     */
    public function updateFormElement($element)
    {
        $elId          = $element->id;
        $oldElement    = $this->getElementById($elId);

        $this->insertOrUpdateData($this->elTableName, $element, ['id' => $elId]);

        $element->id    = $elId;

        // Update the element in the formElements array
        $this->formElements[$oldElement->index]    = (object)$element;

        $formVersion    = 1;
        if (is_numeric($this->formData->version)) {
            $formVersion    = $this->formData->version + 1;
        }

        //Update form version
        $result = TSJIPPY\updateDbValue(
            $this->tableName,
            ['version' => $formVersion],
            ['id'      => $this->formData->id],
            ['%d'],
            ['%d'],
            'forms'
        );

        if (is_wp_error($result)) {
            return $result;
        }

        do_action('tsjippy-forms-after-formelement-updated', $element, $this, $oldElement);

        return $result;
    }

    /**
     * Inserts a new element in the db
     *
     * @param    object|array    $element    The new element to insert
     *
     * @return    int                            The new element id
     */
    public function insertElement($element)
    {
        $id    = $this->insertOrUpdateData($this->elTableName, $element);

        return $id;
    }

    /**
     * Change the priority of an element
     *
     * @param    object|array    $element    The element to change the priority of
     *
     * @return    array|WP_Error                The result or error on failure
     */
    public function updatePriority($element)
    {
        //Update the database
        $result = TSJIPPY\updateDbValue(
            $this->elTableName,
            array(
                'priority' => $element->priority
            ),
            array(
                'id'       => $element->id
            ),
            ['%d'],
            ['%d'],
            'forms'
        );

        if (is_wp_error($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * Change the order of form elements
     *
     * @param    array  $elementIds     Array of element ids in the order they should be shown
     */
    public function reorderElements($elementIds)
    {
        if (!isset($this->formData->id)) {
            return new WP_Error('tsjippy-forms', 'No form data loaded');
        }

        // Get all elements of this form
        $this->getAllFormElements('priority', $this->formData->id, true);

        foreach ($this->formElements as &$el) {
            $priority   = array_search($el->id, $elementIds) + 1;
            if ($el->priority != $priority) {
                $el->priority    = $priority;

                $this->updatePriority($el);
            }
        }
    }

    /**
     * Update form settings
     *
     * @param    int|string   $formId   The id of the form to update the settings for
     * @param    array        $request  The sanitized request data
     *
     * @return    true|WP_Error          The result or error on failure
     */
    public function updateFormSettings($formId = '', $request = '')
    {
        if (empty($formId)) {
            if (!empty($this->formData->id)) {
                $formId    = $this->formData->id;
            } else {
                return new \WP_Error('Error', 'Please supply a form id');
            }
        }

        $request    = apply_filters('tsjippy-forms-before-saving-settings', $request, $this, $formId);

        $result    = $this->insertOrUpdateData($this->tableName, $request, ['id' => $formId]);
        if (is_wp_error($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Stores the form reminder settings in the db
     *
     * @param    int|string    $formId    The id of the form to update the reminder settings for
     * @param    array        $settings    The reminder settings to update, this should be an
     *
     * return    true|WP_Error                The result or error on failure
     */
    public function updateFormReminder($formId, $settings)
    {
        if (empty($formId)) {
            if (!empty($this->formData->id)) {
                $formId    = $this->formData->id;
            } else {
                return new \WP_Error('Error', 'Please supply a form id');
            }
        }

        if (empty($settings)) {
            return new \WP_Error('Error', 'Please supply the form settings');
        }

        $result    = $this->insertOrUpdateData($this->formReminderTable, $settings, ['form_id' => $formId]);
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
            $column['priority']        = $priority;

            $column['element_id']    = $elementId;

            $column['shortcode_id']    = $shortcodeId;

            //if there are edit rights defined
            if (!empty($column['edit-right-roles'])) {
                //create view array if it does not exist
                if (empty($column['view-right-roles']) || !is_array($column['view-right-roles'])) {
                    $column['view-right-roles'] = [];
                }

                //merge and save
                $column['view-right-roles'] = array_merge($column['view-right-roles'], $column['edit-right-roles']);
            }

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
            $email['message']    = trim(TSJIPPY\deslash($email['message']));

            $where                = [];

            // Its an update to an existing one
            if (!empty($email['email-id'])) {
                $where            = [
                    'id' => $email['email-id']
                ];
            }

            $result    = $this->insertOrUpdateData($this->formEmailTable, $email, $where);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        return $result;
    }
}
