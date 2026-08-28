<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

class FormReminders extends Forms
{
    public array|bool   $reminders;
    public string       $html;
    public array|bool   $mandatoryElements;
    public array|bool   $formReminders;

    /**
     * Constructor
     *
     * @param   int $userId    The user id to get the reminders for
     */
    public function __construct()
    {
        parent::__construct();

        $this->formReminders     = [];
        $this->mandatoryElements = [];
        $this->html              = '';

        $this->getFormsWithReminders();
        
        // Get all blocks which should be submitted
        $this->getMandatoryElements();

        $this->reminders  = [];
    }

    /**
     * Gets all blocks with reminder settings from the db
     */
    protected function getBlocksWithReminders()
    {
        return  TSJIPPY\getFromDb(
            "get_form_block_reminders",
            "forms",
            "SELECT * FROM %i",
            $this->blockRemindersTableName
        );
    }

    /**
     * Gets all forms with reminder settings from the db
     */
    protected function getFormsWithReminders()
    {
        // Prepare the weekdays
        $date = new \DateTime('Sunday');

        for ($i = 0; $i < 7; $i++) {
            $this->formReminders[$date->format('D')]        = []; // 'D' for short day name
            $date->modify('+1 day');
        }

        // Get the forms that have a stardate in the past
        $date    = gmdate('Y-m-d');

        $results = TSJIPPY\getFromDb(
            "get_form_reminders_before_$date",
            "forms",
            "SELECT * FROM %i WHERE reminder_start_date <= %s",
            $this->formReminderTable,
            $date
        );

        foreach ($results as $formReminder) {
            $this->getForm($formReminder->post_id, $formReminder->block_id);

            // get the start day of the week
            $day = gmdate('D', strtotime($formReminder->reminder_start_date));

            // This is a form that saves its data in the user meta, so we use different logic for that
            if (!empty($this->formData->save_in_meta)) {
                continue;
            }

            $this->formReminders[$day][]  = [
                'form'      => $this->formData,
                'reminder'  => $formReminder
            ];
        }
    }

    /**
     * Get mandatory and recommended elements from the db
     */
    protected function getMandatoryElements()
    {
        $this->mandatoryElements    = apply_filters("tsjippy-forms-elements-filter", $this->getBlocksWithReminders(), $this);

        // Sort on form
        usort($this->mandatoryElements, function ($a, $b) {
            return $a->post_id <=> $b->post_id; // The spaceship operator (<=>) simplifies comparisons in PHP 7+
        });
    }

    /**
     * Retrieves the reminder conditions for a specific block from the db
     * 
     * @param   int $postId
     * @param   int $blockId
     */
    public function getElementReminderConditions($postId, $blockId){
        $conditions    = TSJIPPY\getFromDb(
            "get_element_reminder_conditions_".$blockId,
            'forms',
            "select * from %i where post_id = %d and block_id = %d",
            $this->blockRemindersTableName,
            $postId,
            $blockId
        );

        return $conditions;   
    }

    /**
     * Gets the reminders for a given user id
     *
     * @param   int $userId    The user id to get the reminders for
     *
     * @return  array           The reminders for the given user id
     */
    public function getUserReminders($userId)
    {
        /**
         * Get all the elements on meta forms
         */
        $elements   = $this->getElementReminders($userId);

        /**
         * Get forms
         */
        $forms  = [];
        foreach ($this->formReminders as $day => $submissionForm) {
            if (empty($submissionForm)) {
                continue;
            }

            foreach ($submissionForm as $formDetails) {
                $formReminder   = $formDetails['reminder'];

                // do not process if not needed
                if ($this->passedReminderCount($formReminder)) {
                    continue;
                }

                $this->formData = $formDetails['form'];

                if($this->processDefaultForm($formReminder, $userId)){
                    $forms[]    = $formDetails['form'];
                }
            }
        }

        return [
            'elements'  => $elements,
            'forms'     => $forms
        ];
    }

    /**
     * Checks if the maximimum form amount of form reminders has been sent
     *
     * @param   object  $formReminder   The form reminder obejct
     *
     * @return  bool                    true if past, false otherwise
     */
    protected function passedReminderCount($formReminder)
    {
        // Recurring submissions
        if (!empty($formReminder->frequency) && !empty($formReminder->period)) {
            // Get the re-submission intervals since the start
            $interval         = \DateInterval::createFromDateString("$formReminder->frequency $formReminder->period");     // the interval between submissions
            $daterange         = new \DatePeriod(                                                                           // the date range between start_date and today with the specified interval
                date_create($formReminder->reminder_start_date),
                $interval,
                new \DateTime('now'),
                false
            );

            // Get the current interval
            $currentReminderStart        = $daterange->getEndDate()->format('Y-m-d');
            foreach ($daterange as $date1) {
                $currentReminderStart = $date1->format('Y-m-d');
            }
        } else {
            $currentReminderStart = $formReminder->reminder_start_date;
        }

        // Do not continue if we already have notified the maximum amount
        if (
            !empty($formReminder->reminder_amount) &&                                                                                // There is an max amount set in weeks
            strtotime("+ $formReminder->reminder_amount $formReminder->reminder_period", strtotime($currentReminderStart)) < time()    // we are passed the amount
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get the minimum form submission date
     *
     * @param   object  $formReminder   The form reminder obejct
     * @param   string  $query          The query to add the minimum date to
     * @param   array   $values         The values to add the minimum date to
     *
     * @return  string                  The minimum date for form submissions to be included in the reminders
     */
    protected function getMinimumDate($formReminder, &$query, &$values)
    {
        $since    = '';

        // We have definded a submission
        if (
            !empty($formReminder->frequency) &&
            !empty($formReminder->period) &&
            !empty($formReminder->window_start) &&
            $formReminder->window_start != '0000-00-00'
        ) {
            $interval         = \DateInterval::createFromDateString("$formReminder->frequency $formReminder->period");

            // calculate the start of the current window
            $daterange         = new \DatePeriod(                                                                           // the date range between start_date and today with the specified interval
                date_create($formReminder->window_start),
                $interval,
                new \DateTime('now'),
                false
            );

            // Get the current interval
            $since        = $daterange->getEndDate()->format('Y-m-d');
            foreach ($daterange as $date1) {
                $since = $date1->format('Y-m-d');
            }

            $query .= " AND time_created >= %s";
            $values[] = $since;
        }

        return $since;
    }

    /**
     * Checks a given form for pending reminders
     *
     * @param   object  $formReminder   The form reminder object
     *
     * @return  void
     */
    protected function processDefaultForm($formReminder, $userId)
    {
        // Get all submissions created inside the current submission window
        $query            = "SELECT * FROM %i WHERE post_id=%d and block_id=%d and user_id=%d";
        $values            = [
            $this->submissionTableName,
            $formReminder->post_id,
            $formReminder->block_id,
            $userId
        ];

        $this->getMinimumDate($formReminder, $query, $values);

        $submissions    = TSJIPPY\getFromDb(
            "get_submissions_".$formReminder->block_id,
            'forms',
            $query,
            $values
        );

        return $this->checkIfConditionsAppliesToUser($formReminder->conditions, $userId, $submissions);
    }

    /**
     * Checks if a given set of conditions applies to the current user. Returns true if there is a match
     *
     * @param    object   $conditions        The element conditions
     * @param    int      $userId            The user id
     * @param    array    $submissions    The submissions to check
     *
     * @return    bool                    true if no conditions or the condition apply, false if it does not apply
     */
    public function checkIfConditionsAppliesToUser($conditions, $userId, $submissions = '')
    {
        if (!is_array($conditions) || empty($conditions)) {
            return true;
        }

        $conditions    = TSJIPPY\cleanUpNestedArray($conditions);

        // Check if the the roles overlap
        if (isset($conditions['roles'])) {
            // Check if user has one of the roles
            $user    = get_userdata($userId);
            if ($user) {
                $intersect    = array_intersect($conditions['roles'], $user->roles);
                if (!empty($intersect)) {
                    // There is at least one overlapping role
                    return true;
                }
            }
            unset($conditions['roles']);
        }

        $applies = null;

        foreach ($conditions as $condition) {
            foreach($condition->rules as $check){
                // get the user value
                $metaKey = $check['meta-key'];
                if(!str_starts_with($metaKey, 'tsjippy_')){
                    $metaKey    = "tsjippy_$metaKey";
                }
                $value        = get_user_meta($userId, $metaKey);

                $metaIndex  = trim($check['meta-key-index'] ?? '');
                if (!empty($metaIndex)) {
                    if (!empty($value[$metaIndex])) {
                        $value        = $value[$metaIndex];
                    } else {
                        $value        = '';
                    }
                }

                if (is_array($value)) {
                    $value    = array_filter($value);

                    if (empty($value)) {
                        $value    = '';
                    }
                }

                if (is_array($value) && $check['equation'] != 'submitted' && isset($value[0])) {
                    $value    = $value[0];
                }

                // Get the compare value
                $checkValue    = '';
                if (isset($check['conditional-value'])) {
                    $checkValue        = $check['conditional-value'];
                    $conditionalValue  = strtotime($check['conditional-value']);
                    if ($conditionalValue && gmdate('Y', $conditionalValue) < 2200) {
                        $checkValue    = gmdate('Y-m-d', $conditionalValue);
                    }
                }

                // compare the values
                switch ($check['equation']) {
                    case '==':
                        $result    = $value == $checkValue;
                        break;
                    case '!=':
                        $result    = $value != $checkValue;
                        break;
                    case '>':
                        $result    = $value > $checkValue;
                        break;
                    case '<':
                        $result    = $value < $checkValue;
                        break;
                    case 'submitted':
                        $result    = false;

                        // check if the given user_id has submitted the form already
                        foreach ($submissions as $submission) {
                            if (is_array($value)) {
                                if (in_array($submission->user_id, $value)) {
                                    $result    = true;
                                    break;
                                }
                            } else {
                                if ($submission->user_id == $value) {
                                    $result    = true;
                                    break;
                                }
                            }
                        }
                        break;
                    default:
                        $result = false;
                }

                // Check the result
                if ($result) {
                    //break this loop when we already know we should skip this field
                    if (!empty($check['combinator'])) {
                        // One of the conditions applies so return true
                        if( $check['combinator'] == 'or'){
                            $applies = true;
                            break;
                        }
                        
                        // the combinator is AND and we have not seen a false yet
                        elseif(empty($applies)){
                            $applies = true;
                        }   
                    }
                }
            }
        }

        return $applies;
    }

    /**
     * Builds the reminders array
     *
     * @return    string                The html
     */
    public function getElementReminders($userId)
    {
        $elements   = [];

        // Loop over all mandatory and required elements
        foreach ($this->mandatoryElements as $element) {
            // Load the form data for this element to save db queries in the getElementById function
            $this->getForm($element->post_id);

            // Get the warning conditions
            $warningCondition = $this->getElementReminderConditions($element->post_id, $element->block_id);

            //check if this element applies to this user
            if (!$this->checkIfConditionsAppliesToUser($warningCondition, $userId)) {
                continue;
            }

            $name       = $this->getElementById($element->block_id, 'name');
            if (str_contains($name, '[')) {
                $value  = TSJIPPY\getMetaArrayValue($userId, $name, $value);
            } else {
                $metaKey          = explode('[', $element->slug)[0];
                
                if(!str_contains($metaKey, 'tsjippy_') && !isset($this->wpMetaKeys[$metaKey])){
                    $metaKey    = 'tsjippy_' . $metaKey;
                }

                $value  = get_user_meta($userId, $metaKey, true);
            }

            // Element has a value
            if (!empty($value)) {
                continue;
            }

            $elements[] = $element;
        }

        return $elements;
    }

    /**
     * Gets all mandatory forms for today as html links
     *
     * @param   bool    $includeMandatoryForms    Whether to include mandatory forms without element reminders as well
     *
     * @return string|array             Returns html links to forms who are due for submission if a user_id is given, an array of form => [user_ids] otherwise
     */
    public function getAllFormRemindersForToday($userId, $includeMandatoryForms = true)
    {
        $today      = gmdate('D');
        $family     = new TSJIPPY\FAMILY\Family();
        $reminders  = [];

        /**
         * Form reminders
         */
        if ($includeMandatoryForms) {
            foreach ($this->formReminders[$today] as $formDetails) {
                $formId     = $formDetails['form']->block_id;

                foreach ($this->reminders[$formId] as $blockId) {
                    $child                = $family->isChild($userId);
                    $childName          = '';
                    if ($child) {
                        $childName        = get_userdata($userId)->first_name;
                    }

                    $reminders .= $this->getFormReminderHtml($formId, $childName);
                }
            }
        }

        foreach ($reminders as &$forms) {
            foreach ($forms as $formId => &$reminder) {
                $reminder .= '</ul>';
            }
        }

        return $reminders;
    }

    /**
     * Get the html for a specific block
     *
     * @param   int     $blockId     The block id to get the html for
     * @param   string  $type        The type of reminder to get the html for
     * @param   int     $childId     The user id of the child to include in the reminder text if applicable
     * @return  string               The html for the element reminder
     *
     */
    protected function getElementReminderHtml($blockId, $type = 'all', $childId = false)
    {
        $element    = $this->getElementById($blockId);
        if (!$element) {
            return '';
        }

        if ($type != 'all' && !$element->$type) {
            return '';
        }

        $formUrl    = get_permalink($this->formData->postId);       

        //Show a nice name
        $name       = ucfirst( $element->name);

        if (!empty($childId)) {
            $childName    = get_user($childId)->first_name;
            $name        .= " for $childName";
            $formUrl     .= add_query_arg('child', $childId, $formUrl); 
        }

        /**
         * Filters the link to an form or form element that needs to be submitted
         * 
         * @param   string  $link       The hyperlink html
         * @param   object  $object     The current object
         * @param   object  $element    The form element
         * @param   string  $formUrl    The url
         * @param   string  $type        The type of reminder to get the html for
         * @param   int     $childId     The user id of the child to include in the reminder text if applicable
         * @return  string               The html for the element reminder
         */
        return apply_filters('tsjippy-forms-reminder-link', "<a href='$formUrl#{$element->slug}'>$name</a>", $this, $element, $formUrl, $type, $childId);
    }

    /**
     * Gets the html for a form reminder
     * @param   int     $blockId        The block id to get the html for
     * @param   string  $childName     The name of the child to include in the reminder text if applicable
     * @return  string                  The html for the form reminder
     */
    protected function getFormReminderHtml($blockId, $childName)
    {
        $this->getForm(blockId: $blockId);
        
        $formUrl    = get_permalink($this->formData->post);

        $formName   = $this->formData->name;

        $text       = "Fill in the $formName form";
        if (!empty($childName)) {
            $text   .= " for $childName";
        }

        return "<li><a href='$formUrl'>$text</a></li>";
    }

    /**
     * Gets the reminder html for a given user id
     *
     * @param   int $userId     The user id to get the reminder html for
     * @param   string $type    The type of reminders to include in the html, either 'mandatory', 'recommended' or 'all'
     *
     * @return  string          The reminder html for the given user id
     */
    public function getReminderHtml($userId, $type)
    {
        $family        = new TSJIPPY\FAMILY\Family();
        $child         = false;
        if ($family->isChild($userId)) {
            $child  = $userId;
        }

        $html          = '';

        $reminders     = $this->getUserReminders($userId);

        // HTML for individual elements on a meta form
        if (!empty($reminders['elements'])) {
            foreach ($reminders['elements'] as $element) {
                // Load the form data
                $this->getForm(post: $element->post_id, blockId: $element->block_id);
                $result = $this->getElementReminderHtml($element->block_id, $type, $child);

                if (!empty($result)) {
                    $html   .= "<li>$result</li>";
                }
            }
        }

        // Forms to be submitted
        if (!empty($reminders['forms'])) {
            foreach ($reminders['forms'] as $formId) {
                $html .= $this->getFormReminderHtml($formId, $child);
            }
        }

        if (!empty($html)) {
            $html    = "<ul>$html</ul>";
        }

        $html    = apply_filters("tsjippy-forms-manadatory-html-filter", $html, $userId, $this);

        return $html;
    }

    /**
     * Sends reminders by e-mail to submit or update a form
     */
    public function sendFormReminders()
    {
        $today  = gmdate('D');

        // Send e-mails for forms to be submitted
        foreach ($this->formReminders[$today] as $formDetails) {
            $this->formData = $formDetails['form'];

            $formId = $this->formData->blockId;

            // Do nothing if there are no reminders for this form
            if (!isset($this->reminders[$formId])) {
                continue;
            }

            // Load the e-mail settings
            $this->getEmailSettings();

            foreach ($this->reminders[$formId] as $userId) {
                $this->sendEmail($userId);
            }
        }

        foreach ($this->getAllFormRemindersForToday(false) as $userId => $forms) {
            foreach ($forms as $formId => $html) {
                // Load the form data for this form
                foreach ($this->formReminders[$today] as $formDetails) {
                    if ($formDetails['form']->blockId == $formId) {
                        $this->formData = $formDetails['form'];
                        break;
                    }
                }

                // Load the e-mail settings for this form
                $this->getEmailSettings();

                $this->sendEmail($userId, $html);
            }
        }
    }

    /**
     * Sends an e-mail reminder to a user
     *
     * @param   int     $userId        The user id to send the e-mail to
     * @param   string  $html          The html content for the e-mail
     */
    protected function sendEmail($userId, $html = '')
    {
        $user   = get_userdata($userId);

        // Invalid user id given
        if (!$user) {
            return;
        }

        foreach ($this->emailSettings as $mail) {
            $mail   = (object)$mail;

            if ($mail->email_trigger != 'shouldsubmit') {
                continue;
            }

            $from       = $mail->from;

            $to         = $mail->to;

            $subject    = $mail->subject;

            $message    = $mail->message;

            $headers    = [];

            if (!empty(trim($mail->headers))) {
                $headers    = explode("\n", trim($mail->headers));
            }

            if (!empty($from) && !str_contains($mail->headers, 'Reply-To:')) {
                if (str_contains($from, '%')) {
                    $headers[]    = "Reply-To: " . $user->user_email;
                } else {
                    $headers[]    = "Reply-To: $from";
                }
            }

            if (str_contains($to, '%')) {
                $recipient  = $user->user_email;
            } else {
                $recipient  = $to;
            }

            if (!empty($html) && !str_contains($message, '%reminders%')) {
                $message .= '%reminders%';
            }

            $msg      = $this->processPlaceholders(
                $message,
                [
                    'formurl'   => $this->formData->url,
                    'name'      => $user->first_name,
                    'email'     => $user->user_email,
                    'reminders' => $html
                ]
            );

            wp_mail($recipient, $subject, $msg, $headers);
        }
    }
}
