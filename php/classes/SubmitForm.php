<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use WP_Embed;
use WP_Error;

if (! defined('ABSPATH')) {
    exit;
}

class SubmitForm extends SaveFormSettings
{
    public function __construct($atts=[], $all=false, $pageSize=50, $postId='', $formUrl='', $userId=0)
    {
        parent::__construct(atts: $atts, all: $all, pageSize:$pageSize, postId:$postId, formUrl:$formUrl, userId:$userId);
    }

    /**
     * Returns conditional e-mails with a valid condition
     *
     * @param    array    $conditions        The conditions of a conditional e-mail
     *
     * @return    string|false            The e-mail adres or false if none found
     */
    public function findConditionalEmail($conditions)
    {
        //loop over all conditions
        foreach ($conditions as $condition) {

            $elementName    = $this->getElementById($condition['fieldid'], 'slug');

            //get the submitted form value
            $formValue = $this->submission->{$elementName};

            //if the value matches the conditional value
            if (strtolower($formValue) == strtolower($condition['value'])) {
                return $condition['email'];
            }
        }

        return false;
    }

    /**
     * Replaces the url with form url
     *
     * @param    array    $footer        The footer array
     *
     * @return    array                The filtered footer array
     */
    public function emailFooter($footer)
    {
        // phpcs:ignore
        $url    = TSJIPPY\sanitize($_POST['formurl'], 'url');
        $footer['url']  = $url;
        $footer['text'] = $url;

        return $footer;
    }

    /**
     * Check if an e-mail should be send
     *
     * @param    object    $email        The e-mail data
     * @param    string    $trigger    The trigger string
     *
     * @return    bool                False if no match ture otherwise
     */
    private function checkEmailConditions($email, $trigger)
    {
        if (
            $email->email_trigger    != $trigger &&                     // trigger of the e-mail does not match the trigger exactly
            (
                $email->email_trigger    != 'submittedcond' ||        // trigger of the e-mail is not submittedcond
                (
                    $email->email_trigger    == 'submittedcond' &&    // trigger of the e-mail is submittedcond
                    $trigger                != 'submitted'            // the trigger is not submitted
                )
            )
        ) {
            return false;
        }

        $changedElementId    = (int) $_POST['element-id'] ?? '';

        // check if a certain element is changed to a certain value
        if ($trigger == 'fieldchanged') {

            // the changed element is not the conditional element)
            if ($changedElementId != $email->conditional_field) {
                return false;
            }

            // get the element value
            $elementName    = str_replace('[]', '', $this->getElementById($changedElementId, 'name'));

            $formValue         = $this->submission->{$elementName};
            if (is_array($formValue)) {
                $formValue    = $formValue[0];
            }
            $formValue         = strtolower($formValue);

            // get the compare value
            $compareValue    = strtolower($email->conditional_value);

            //do not proceed if there is no match
            if ($formValue != $compareValue && $formValue != str_replace(' ', '_', $compareValue)) {
                return false;
            }
        } elseif (
            $trigger == 'fieldschanged'                                    &&        // an element has been changed
            !in_array($changedElementId, $email->conditional_fields)            // and the element is not in the conditional fields array
        ) {
            return false;
        } elseif ($trigger == 'submitted' && $email->email_trigger == 'submittedcond') {    // check if the submit condition is matched
            if (!is_array($email->submitted_trigger)) {
                return false;
            }

            // get element and the form result of that element
            $element    = $this->getElementById($email->submitted_trigger['element']);
            if (empty($this->submission->{$element->slug})) {
                $elValue    = '';
            } else {
                $elValue    = $this->submission->{$element->slug};
            }

            // get the value to compare with
            if (is_numeric($email->submitted_trigger['value-element'] ?? '')) {
                $compareElement    = $this->getElementById($email->submitted_trigger['value-element']);
                $compareElValue    = $this->submission->{$compareElement->slug};
            } else {
                $compareElValue    = $email->submitted_trigger['value'];
            }

            if (is_array($elValue)) {
                $elValue    = $elValue[0];
            }

            if (is_array($compareElValue)) {
                $compareElValue    = $compareElValue[0];
            }

            // Do the comparisson, do not proceed if no match
            if (version_compare($elValue, $compareElValue, $email->submitted_trigger['equation']) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send an e-mail
     *
     * @param    string    $trigger    One of 'submitted' or 'fieldchanged' . Default submitted
     */
    public function sendEmail($trigger = 'submitted', $replaceValues = [])
    {
        $this->getEmailSettings();

        foreach ($this->emailSettings as $key => $email) {
            $email    = (object)map_deep($email, 'trim');

            if (!$this->checkEmailConditions($email, $trigger)) {
                continue;
            }

            $from    = '';

            //Send e-mail from conditional e-mail adress
            if ($email->from_email == 'conditional') {
                $from     = $this->findConditionalEmail($email->conditional_from_email);

                if (!$from) {
                    $from    = $email->else_from;
                }
            } elseif ($email->from_email == 'fixed') {
                $from    = $this->processPlaceholders($email->from, $replaceValues);
            }

            if (empty($from)) {
                TSJIPPY\printArray("No from email found for email $key");
            }

            $to        = '';
            if ($email->email_to == 'conditional') {
                $to = $this->findConditionalEmail($email->conditional_email_to);

                if (!$to) {
                    $to    = $email->else_to;
                }
            } elseif ($email->email_to == 'fixed') {
                $to        = $this->processPlaceholders($email->to, $replaceValues);

                // if no e-mail found, find any numbers and assume they are user ids
                // than replace the id with the e-mail of that user
                if (!str_contains($to, '@')) {
                    $pattern     = '/[0-9\.]+/i';
                    $to            = preg_replace_callback(
                        $pattern,
                        function ($match) {
                            $user    = get_userdata($match[0]);

                            if ($user && !str_contains($user->user_email, 'empty')) {
                                return $user->user_email;
                            }
                            return $match[0];
                        },
                        $to
                    );
                }
            }

            $recipients    = [];
            foreach (explode(',', $to) as $t) {
                if (str_contains($t, '@')) {
                    $recipients[]    = $t;
                }
            }

            if (empty($recipients)) {
                TSJIPPY\printArray("No to email found for email $key on form {$this->formData->slug} with id {$this->formData->id}");
                continue;
            }

            $subject    = $this->processPlaceholders($email->subject, $replaceValues);
            $message    = $this->processPlaceholders($email->message, $replaceValues);

            $headers    = $email->headers;
            if (!is_array($headers)) {
                if (!empty(trim($headers))) {
                    $headers    = explode("\n", trim($email->headers));
                } else {
                    $headers    = [];
                }
            }

            if (!empty($from)) {
                $headers[]    = "Reply-To: $from";
            }

            $files        = [];
            if (!empty($email->files) && is_string($email->files)) {
                $files        = $this->processPlaceholders($email->files, $replaceValues);

                if (is_string($files)) {
                    $files        = explode(',', trim($files));
                }
            }

            // add the form specific footer filter
            add_filter('tsjippy-email-footer-url', [$this, 'emailFooter']);

            add_filter('wp_mail', [$this, 'addFormData'], 1);

            //Send the mail
            $result = wp_mail($to, $subject, $message, $headers, $files);

            remove_filter('wp_mail', [$this, 'addFormData'], 1);

            if ($result === false) {
                TSJIPPY\printArray("Sending the e-mail failed");
                TSJIPPY\printArray([
                    $to,
                    $subject,
                    $message,
                    $headers,
                    $files
                ]);
            }

            // remove the form specific footer filter
            remove_filter('tsjippy-email-footer-url', [$this, 'emailFooter']);
        }
    }

    /**
     * Rename any existing files to include the form id.
     *
     * @param    array    $uploadedFiles        The array of filepaths
     * @param    string    $inputName            The name for the file
     */
    public function processFiles(&$formResults)
    {
        // Get the target dir from the form
        if(!empty($formResults['file-upload-target-dir'])){
            $targetDir  = TSJIPPY\sanitize($formResults['file-upload-target-dir']);

            // Do not store in form results
            unset($formResults['file-upload-target-dir']);
        }else{
            // Store files in the private upload folder 
            $targetDir    = wp_upload_dir()['basedir']."/private/form_uploads/".$this->formData->slug;
        }

        $fileUploader = new TSJIPPY\FILEUPLOAD\FileUploader( userId: $this->userId );

        /**
         * Loop pver all file/image inputs
         */
        foreach($_FILES as $inputName => $fileData){
            $fileNames  = [];
            $inputName  = str_replace('-files', '', $inputName);

            /**
             * Loop over all sumitted files
             */
            foreach($fileData['name'] as $fileName){
                //add input name to filename
                $fileName    = "{$inputName}_$fileName";

                $fileName    = $this->submission->id . "_$fileName";

                $fileNames[] = $fileName;
            }

            $fileUploader->processFiles(files: $fileData, targetDir: $targetDir, targetFileNames: $fileNames);

            // Store potentially updated file names in the submission
            foreach($fileUploader->filesArr as $key => $data){
                $formResults[$inputName][$key] = $data['fileName'];
            }
        }
        
    }

    /**
     * remove empty splitted entries
     *
     * @param    array    $formresults    The form results array
     */
    public function parseSplittedData(&$formresults)
    {
        if (!isset($this->formData->split)) {
            return;
        }

        global $wpdb;

        // loop over all split elements
        foreach ($this->formData->split as $index => $id) {
            // get the name of the split element
            $slug    = $this->getElementById($id, 'slug');

            // Check if we are dealing with an split element with form name[X]name
            if (
                preg_match('/(.*?)\[[0-9]\](\[.*?\])/', $slug, $matches) &&
                isset($matches[1]) &&
                is_array($formresults[$matches[1]])
            ) {
                // remove empty entries
                $results = TSJIPPY\cleanUpNestedArray($formresults[$matches[1]]);

                // loop over all the sub entries of the split field to see if they are empty
                foreach ($results as $in => $subValues) {
                    foreach ($subValues as $subKey => $subValue) {
                        if (empty($subValue)) {
                            continue;
                        }

                        // Find the element id
                        $elementId    = $this->getElementBySlug($matches[1] . "[$in][$subKey]", 'id');

                        // insert the value
                        TSJIPPY\insertInDb(
                            $this->submissionValuesTableName,
                            [
                                'submission_id' => $this->submission->id,
                                'sub_id'        => $in,
                                'element_id'    => $elementId,
                                'value'         => maybe_serialize($subValue)
                            ],
                            [
                                '%d',
                                '%d',
                                '%d',
                                '%s'
                            ],
                            'forms'
                        );
                    }
                }

                // reindex
                unset($formresults[$matches[1]]);
            }

            // single value for the split entry
            else {
                // loop over all the sub entries of the split field to see if they are empty
                foreach ($formresults[$slug] as $key => $subValue) {
                    if (empty($subValue)) {
                        continue;
                    }

                    $elementId = $this->getElementBySlug($slug . '[' . $key . ']', 'id');

                    TSJIPPY\insertInDb(
                        $this->submissionValuesTableName,
                        [
                            'submission_id' => $this->submission->id,
                            'sub_id'        => $index,
                            'element_id'    => $elementId,
                            'value'         => maybe_serialize($subValue)
                        ],
                        [
                            '%d',
                            '%d',
                            '%s',
                            '%s'
                        ],
                        'forms'
                    );
                }

                unset($formresults[$slug]);
            }
        }
    }

    /**
     * Save a form submission to the submission table
     *
     * @param    array    $formresults    the form result from post
     * @param    string    $formUrl        The url of the form
     * @param    string    $message        The message passed by reference to adjust
     *
     * @return    WP_Error|true            The error if one exists or true
     */
    public function saveToSubmissionTable($formresults, $formUrl, &$message)
    {
        global $wpdb;

        if (!empty($this->formData->save_in_meta)) {
            return;
        }

        // Insert Submission
        $submission             = $this->submission;
        $this->submission->id    = $this->insertOrUpdateData($this->submissionTableName, $submission);

        if (is_wp_error($this->submission->id)) {
            return $this->submission->id;
        }

        //remove empty splitted entries
        $this->parseSplittedData($formresults);

        // Add a security hash for submissions from outside
        $formresults['viewhash']        = wp_hash($this->submission->id);

        /**
         * Handle File Uploads
         */
        if(!empty($_FILES)){
            $this->processFiles($formresults);
        }

        /**
         * Process Results
         */
        foreach ($formresults as $key => &$result) {
            if (is_array($result)) {
                //sort the array
                ksort($result);
            }

            $result    = TSJIPPY\cleanUpNestedArray($result);

            if (empty($result)) {
                continue;
            }

            if ($key == 'viewhash') {
                $elementId = -7;
            } else {
                $elementId    = $this->getElementBySlug($key, 'id');
                if (!$elementId) {
                    continue;
                }
            }

            //insert the data
            $data    = [
                'submission_id' => $this->submission->id,
                'element_id'    => $elementId,
                'value'         => $result
            ];

            $this->insertOrUpdateData(
                $this->submissionValuesTableName,
                $data
            );
        }

        $placeholders            = $formresults;

        $placeholders['id']      = $this->submission->id;

        $placeholders['formurl'] = $formUrl;

        $placeholders['formid']  = $this->submission->form_id;

        $this->sendEmail('submitted', $placeholders);

        if ($wpdb->last_error !== '') {
            $message    =  new \WP_Error('error', $wpdb->last_error);
        } elseif (empty($this->formData->include_id) || $this->formData->include_id) {
            $message    .= "<br>Your id is {$this->submission->id}";
        }

        return true;
    }

    /**
     * Saves a submission to the user meta table
     * @param    array    $formresults    The form results to be saved
     */
    public function saveToUserMetaTable($formresults)
    {
        $updateUserData    = false;

        //get user data as array
        $userData      = (array)get_userdata($this->userId)->data;
        foreach ($formresults as $key => &$result) {
            $subKey    = false;

            /** 
             * Determine if we are dealing with an indexed array ($test['test'])
             */
            if (is_array($result)) {
                $result = TSJIPPY\cleanUpNestedArray($result);

                if(empty($result)){
                    delete_user_meta($this->userId, $key);
                    continue;
                }

                //check if we should only update one entry of the array
                $index = array_keys($result)[0];

                if(is_string($index)){
                    $el     = $this->getElementBySlug($key . '[' . $index . ']');
                    if (count(array_keys($result)) == 1 && $el) {
                        $subKey    = array_keys($result)[0];
                    }
                }
            }

            // update in the users table
            if (isset($userData[$key])) {
                if ($subKey) {
                    $userData[$key][$subKey] = $result;
                    $updateUserData          = true;
                } elseif ($userData[$key]    != $result) {
                    $userData[$key]          = $result;
                    $updateUserData          = true;
                }
            } 
            
            //update user meta
            else {
                if(!str_contains($key, 'tsjippy_') && !in_array($key, $this->wpMetaKeys)){
                    $key    = 'tsjippy_' . $key;
                }

                // update an indexed value
                if ($subKey) {
                    $curValue    = get_user_meta($this->userId, $key, true);
                    if (empty($result)) {
                        // remove subkey
                        if (isset($curValue[$subKey])) {
                            unset($curValue[$subKey]);
                        }
                    } else {
                        if (!is_array($curValue)) {
                            $curValue    = [];
                        }

                        //update subkey
                        $curValue[$subKey]    = $result[$subKey];
                    }

                    update_user_meta($this->userId, $key, $curValue);
                } else {
                    if (empty($result)) {
                        delete_user_meta($this->userId, $key);
                    } elseif(is_array($result)){
                        $prevValues = get_user_meta($this->userId, $key);

                        $added      = array_diff($result, $prevValues);
                        $removed    = array_diff($prevValues, $result);

                        foreach($added as $value){
                            add_user_meta($this->userId, $key, $value);
                        }

                        foreach($removed as $value){
                            delete_user_meta($this->userId, $key, $value);
                        }
                    }else {
                        update_user_meta($this->userId, $key, $result);
                    }
                }
            }
        }

        if ($updateUserData) {
            wp_update_user($userData);
        }

        return true;
    }

    /**
     * Save a form submission to the db
     */
    public function formSubmit($userId, $formId, $formresults)
    {
        $this->submission                    = new \stdClass();

        $this->submission->form_id           = $formId;

        $this->getForm($this->submission->form_id);

        // The user id of the current user
        $this->userId                        = $this->user->ID;

        // Check if we are submitting for another user
        if (is_numeric($userId)) {
            //If we are submitting for someone else and we do not have the right to save the form for someone else
            if (
                array_intersect($this->userRoles, $this->submitRoles) === false &&
                $this->user->ID != $userId
            ) {
                return new \WP_Error('Error', "You do not have permission to save data for user with id $userId");
            } else {
                $this->userId = $userId;
            }
        }

        $this->submission->time_created      = gmdate("Y-m-d H:i:s");

        $this->submission->time_last_edited  = gmdate("Y-m-d H:i:s");

        $this->submission->user_id           = $userId;

        $this->submission->submitter_id      = $this->userId;

        $orgFormResults                      = $formresults;

        // check for required empty elements
        foreach ($this->formElements as $element) {
            // element is required but has no value
            if ($element->required && ($formresults[str_replace('[]', '', $element->slug)] ?? '') === '') {
                return new \WP_Error('Error', "$element->name is required!");
            }
        }

        $this->submission->archived         = false;

        $formUrl                            = $formresults['formurl'];

        // remove the action and other unnecary info
        unset($formresults['form-slug']);
        unset($formresults['fileupload']);
        unset($formresults['user_id']);
        unset($formresults['form-id']);
        unset($formresults['_wpnonce']);
        unset($formresults['formurl']);
        unset($formresults['form-id']);

        /**
         * Filters the form results
         *
         * @param array     $formresults    The form results
         * @param object    $object         The SubmitForm Instance
         */
        $formresults        = apply_filters('tsjippy-forms-before-inserting-formdata', (object)$formresults, $this);

        if (is_wp_error($formresults)) {
            return $formresults;
        }

        $this->submission   = (object)array_merge((array)$this->submission, (array)$formresults);

        $formresults        = (array) $formresults;

        $message = $this->formData->succes_message;
        if (empty($message)) {
            $message = 'succes';
        }

        // Save to submission table
        if (empty($this->formData->save_in_meta)) {
            $result    = $this->saveToSubmissionTable($formresults, $formUrl, $message);
        } 
        
        // Save to user meta
        else {
            $result    = $this->saveToUserMetaTable($formresults);
        }

        if (is_wp_error($result)) {
            return $result;
        }

        /**
         * Filters the message to be shown after form submission
         * 
         * @param string $message       The message to be shown
         * @param array  $formResults   The submitted form results
         * @param object $object        The SubmitForm object
         */
        $message    = apply_filters('tsjippy-forms-after-submission', $message, $orgFormResults, $this);

        do_action('tsjippy-forms-after-submit', $this);

        return $message;
    }
}
