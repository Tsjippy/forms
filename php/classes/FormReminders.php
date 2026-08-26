<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

class FormReminders extends Forms
{
    public array        $metaForms;
    public array        $defaultForms;
    public array|bool   $reminders;
    public string       $html;
    private array       $userIds;
    public array        $mandatoryElements;
    public array        $userReminders;

    /**
     * Constructor
     *
     * @param   int $userId    The user id to get the reminders for
     */
    public function __construct($userId=0)
    {
        parent::__construct(userId: $userId);

        $this->metaForms         = [];
        $this->defaultForms      = [];
        $this->html              = '';
        $this->mandatoryElements = [];
        $this->userReminders     = [];

        $this->getFormsWithReminders();

        $this->reminders  = wp_cache_get("get-form-reminders", 'tsjippy_forms');
        if (empty($this->reminders)) {
            $this->updateCache();
        }

        $this->mapToUserId();
    }

    /**
     * Store the result in the cache
     */
    public function updateCache()
    {
        $this->reminders  = [
            'metaforms'    => [],
            'defaultforms' => []
        ];

        //Retrieve all users
        $this->userIds    = TSJIPPY\getUserAccounts(false, false, 'ID');

        // Get all blocks which should be submitted
        $this->getMandatoryElements();

        $this->getElementReminders();

        foreach ($this->defaultForms as $day => $submissionForm) {
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

                $this->processDefaultForm($formReminder);
            }
        }

        $this->mapToUserId();

        return wp_cache_set("get-form-reminders", $this->reminders, 'tsjippy_forms');
    }

    /**
     * Retrieves the reminder conditions for a specific block from the db
     * 
     * @param   int $postId
     * @param   int $blockId
     */
    public function getReminderConditions($postId, $blockId){
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
    public function getUserFormReminders($userId)
    {
        if (empty($this->userReminders[$userId])) {
            return '';
        }

        return $this->userReminders[$userId];
    }

    /**
     * Gets all forms with reminder settings from the db
     */
    protected function getFormsWithReminders()
    {
        // Prepare the weekdays
        $date = new \DateTime('Sunday');

        for ($i = 0; $i < 7; $i++) {
            $this->metaForms[$date->format('D')]        = []; // 'D' for short day name
            $this->defaultForms[$date->format('D')]     = [];
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
                $varName   = 'metaForms';
            } else {
                $varName   = 'defaultForms';
            }

            $this->$varName[$day][]  = [
                'form'      => $this->formData,
                'reminder'  => $formReminder
            ];

            $requiredElements = array_values(array_filter($this->formElements, function($element) {
                return $element->required;
            }));

            foreach($requiredElements as $element){
                $element->postId = $this->formData->postId;
            }

            $this->mandatoryElements = array_merge($this->mandatoryElements, $requiredElements);
        }
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
    protected function processDefaultForm($formReminder)
    {
        // Get all submissions created inside the current submission window
        $query            = "SELECT * FROM %i WHERE post_id=%d and block_id=%d";
        $values            = [
            $this->submissionTableName,
            $formReminder->post_id,
            $formReminder->block_id
        ];

        $this->getMinimumDate($formReminder, $query, $values);

        $submissions    = TSJIPPY\getFromDb(
            "get_submissions_".$formReminder->block_id,
            'forms',
            $query,
            $values
        );

        // get all the users who have submitted the form after the currentIntervalStart date
        $usersWithSubmission    = [];
        foreach ($submissions as $submission) {
            $usersWithSubmission[]    = $submission->user_id;
        }

        $usersWithoutSubmission    = array_diff($this->userIds, $usersWithSubmission);

        foreach ($usersWithoutSubmission as $index => $userWithoutSubmission) {
            if ($this->checkIfConditionsAppliesToUser($formReminder->conditions, $userWithoutSubmission, $submissions)) {
                unset($usersWithoutSubmission[$index]);
            }
        }

        $this->reminders['defaultforms'][$formReminder->block_id]    = array_values($usersWithoutSubmission);
    }

    /**
     * Get mandatory and recommended elements from the db
     */
    protected function getMandatoryElements()
    {
        $this->mandatoryElements    = apply_filters("tsjippy-forms-elements-filter", $this->mandatoryElements, $this);

        // Sort on form
        usort($this->mandatoryElements, function ($a, $b) {
            return $a->postId <=> $b->postId; // The spaceship operator (<=>) simplifies comparisons in PHP 7+
        });
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

        foreach ($conditions as $check) {
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

        return $applies;
    }

    /**
     * Builds the reminders array
     *
     * @return    string                The html
     */
    public function getElementReminders()
    {
        // Loop over all mandatory and required elements
        foreach ($this->mandatoryElements as $element) {
            if($element->postId != $this->formData->postId){
                $this->reset();

                // Load the form data for this element to save db queries in the getElementById function
                $this->getForm($element->postId);
            }

            // Get the warning conditions
            $warningCondition = $this->getReminderConditions($element->postId, $element->blockId);

            // Loop over the users
            foreach ($this->userIds as $userId) {
                //check if this element applies to this user
                if (!$this->checkIfConditionsAppliesToUser($warningCondition, $userId)) {
                    continue;
                }

                $name       = $element->slug;
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

                // Store the user id in the reminders array
                if (!isset($this->reminders['metaforms'][$this->formData->blockId])) {
                    $this->reminders['metaforms'][$this->formData->blockId]   = [];
                }

                if (!isset($this->reminders['metaforms'][$this->formData->blockId][$element->blockId])) {
                    $this->reminders['metaforms'][$this->formData->blockId][$element->blockId]   = [];
                }

                $this->reminders['metaforms'][$this->formData->blockId][$element->blockId][]   = $userId;
            }
        }
    }

    /**
     * Gets all mandatory forms for today as html links
     *
     * @param   bool    $includeMandatoryForms    Whether to include mandatory forms without element reminders as well
     *
     * @return string|array             Returns html links to forms who are due for submission if a user_id is given, an array of form => [user_ids] otherwise
     */
    public function getAllFormRemindersForToday($includeMandatoryForms = true)
    {
        $today      = gmdate('D');
        $family        = new TSJIPPY\FAMILY\Family();
        $reminders  = [];

        // Form element reminders
        foreach ($this->metaForms[$today] as $formDetails) {
            $formId     = $formDetails['form']->id;

            // Do nothing if there are no reminders for this form
            if (!isset($this->reminders['metaforms'][$formId])) {
                continue;
            }

            foreach ($this->reminders['metaforms'][$formId] as $elementId => $userIds) {

                //$this->getForm($formId);

                foreach ($userIds as $userId) {
                    $child                = $family->isChild($userId);
                    $childName          = '';
                    if ($child) {
                        $childName        = get_userdata($userId)->first_name;
                    }

                    $result             = $this->getElementReminderHtml($elementId, 'mandatory', $childName);

                    if (!empty($result)) {
                        if (!isset($reminders[$userId])) {
                            $reminders[$userId] = [];
                        }

                        if (!isset($reminders[$userId][$formId])) {
                            $reminders[$userId][$formId] = '<ul>';
                        }

                        $reminders[$userId][$formId]   .= $result;
                    }
                }
            }
        }

        // Form reminders
        if ($includeMandatoryForms) {
            foreach ($this->defaultForms[$today] as $formDetails) {
                $formId     = $formDetails['form']->id;

                // Do nothing if there are no reminders for this form
                if (!isset($this->reminders['defaultforms'][$formId])) {
                    continue;
                }

                $formId = $formDetails['form']->id;

                if (!isset($this->reminders['defaultforms'][$formId])) {
                    continue;
                }

                foreach ($this->reminders['defaultforms'][$formId] as $userIds) {
                    foreach ($userIds as $userId) {
                        $child                = $family->isChild($userId);
                        $childName          = '';
                        if ($child) {
                            $childName        = get_userdata($userId)->first_name;
                        }

                        if (!isset($reminders[$userId])) {
                            $reminders[$userId] = '<ul>';
                        }

                        $reminders[$userId] .= $this->getFormReminderHtml($formId, $childName);
                    }
                }
            }
        }

        foreach ($reminders as $userId => &$forms) {
            foreach ($forms as $formId => &$reminder) {
                $reminder .= '</ul>';
            }
        }

        return $reminders;
    }

    /**
     * Maps all reminders by user id
     */
    protected function mapToUserId()
    {
        foreach ($this->reminders['metaforms'] as $blockId => $elements) {
            foreach ($elements as $elementId => $userIds) {
                foreach ($userIds as $userId) {
                    if (!isset($this->userReminders[$userId])) {
                        $this->userReminders[$userId]   = [];
                    }

                    if (!isset($this->userReminders[$userId]['metaforms'])) {
                        $this->userReminders[$userId]['metaforms']   = [];
                    }

                    if (!isset($this->userReminders[$userId]['metaforms'][$blockId])) {
                        $this->userReminders[$userId]['metaforms'][$blockId]   = [];
                    }

                    if (!in_array($elementId, $this->userReminders[$userId]['metaforms'][$blockId])) {
                        $this->userReminders[$userId]['metaforms'][$blockId][]    = $elementId;
                    }
                }
            }
        }

        foreach ($this->reminders['defaultforms'] as $blockId => $userIds) {
            foreach ($userIds as $userId) {
                $this->userReminders[$userId]['defaultforms'][]     = $blockId;
            }
        }
    }

    /**
     * Get the html for a specific element
     *
     * @param   int     $elementId     The element id to get the html for
     * @param   string  $type          The type of reminder to get the html for
     * @param   string  $childName     The name of the child to include in the reminder text if applicable
     * @return  string                 The html for the element reminder
     *
     */
    protected function getElementReminderHtml($elementId, $type = 'all', $childName = '')
    {
        $element    = $this->getElementById($elementId);
        if (!$element) {
            return '';
        }

        if ($type != 'all' && !$element->$type) {
            return '';
        }

        $formUrl    = get_permalink($this->formData->postId);

        parse_str(wp_parse_url($formUrl, PHP_URL_QUERY), $params);

        //Show a nice name
        $name       = ucfirst( $element->name);

        // phpcs:ignore
        $baseUrl    = explode('main-tab=', TSJIPPY\sanitize($_SERVER['REQUEST_URI'] ?? ''))[0];
        $mainTab    = $params['main-tab'] ?? '';
        if (!empty($childName)) {
            $name        .= " for $childName";
            $mainTab     = strtolower($childName);
            $formUrl     = str_replace($params['main-tab'], $mainTab, $formUrl);
        }

        /**
         * Return a hyperlink to another page
         */
        if (
            !str_contains($baseUrl, 'wp-json') &&
            (
                empty($params['main-tab']) ||
                !str_contains($formUrl, $baseUrl)
            )
        ) {
            return "<li><a href='$formUrl#{$element->slug}'>$name</a></li>";
        }

        /**
         * We are on the same page, just change the hash
         */
        $secondTab    = '';
        $names        = explode('[', $element->slug);
        if (count($names) > 1) {
            $secondTab    = $names[0];
        }

        return "<li><a onclick='Main.changeUrl(this, `$secondTab`)' data-target='$mainTab' data-hash={$element->slug} style='cursor:pointer'>$name</a></li>";
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
        // Nothing to do
        if (empty($this->userReminders[$userId])) {
            return '';
        }

        $family        = new TSJIPPY\FAMILY\Family();
        $child         = $family->isChild($userId);
        $childName     = '';
        if ($child) {
            $childName = get_userdata($userId)->first_name;
        }

        $html          = '';

        // HTML for individual elements on a meta form
        if (!empty($this->userReminders[$userId]['metaforms'])) {
            foreach ($this->userReminders[$userId]['metaforms'] as $blockId => $elements) {
                $this->reset();

                // Load the form data
                $this->getForm(blockId: $blockId);

                foreach ($elements as $elementId) {
                    $result = $this->getElementReminderHtml($elementId, $type, $childName);

                    if (!empty($result)) {
                        $html   .= $result;
                    }
                }
            }
        }

        // Forms to be submitted
        if (!empty($this->userReminders[$userId]['defaultforms'])) {
            foreach ($this->userReminders[$userId]['defaultforms'] as $formId) {
                $html .= $this->getFormReminderHtml($formId, $childName);
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
        foreach ($this->defaultForms[$today] as $formDetails) {
            $this->formData = $formDetails['form'];

            $formId = $this->formData->blockId;

            // Do nothing if there are no reminders for this form
            if (!isset($this->reminders['defaultforms'][$formId])) {
                continue;
            }

            // Load the e-mail settings
            $this->getEmailSettings();

            foreach ($this->reminders['defaultforms'][$formId] as $userId) {
                $this->sendEmail($userId);
            }
        }

        foreach ($this->getAllFormRemindersForToday(false) as $userId => $forms) {
            foreach ($forms as $formId => $html) {
                // Load the form data for this form
                foreach ($this->metaForms[$today] as $formDetails) {
                    if ($formDetails['form']->id == $formId) {
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
