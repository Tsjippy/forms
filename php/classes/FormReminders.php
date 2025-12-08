<?php
namespace SIM\FORMS;
use SIM;

class FormReminders extends SimForms{
    public $metaForms;
    public $defaultForms;
    public $reminders;
    public $html;
    private $userIds;
    public $mandatoryElements;
    public $userReminders;

    public function __construct(){
        parent::__construct();

        $this->metaForms         = [];
        $this->defaultForms      = [];
        $this->html              = '';

        $this->getFormsWithReminders();

        $this->getCache();

        $this->mapToUserId();
    }

    /**
     * Gets the cached results from the database
     */
    protected function getCache(){
        $this->reminders  = get_transient('form_reminders');

        if(empty($this->reminders)){
            $this->updateCache();
        }
    }

    /**
     * Store the result in the cache
     */
    public function updateCache(){
        delete_transient('form_reminders');

        $this->reminders            = [
            'metaforms'    => [],
            'defaultforms' => []  
        ];

        //Change the user to the admin account otherwise get_users will not work
        if(wp_doing_cron()){
            wp_set_current_user(1);
        }
        
        //Retrieve all users
        $this->userIds          			= SIM\getUserAccounts(false, false, 'ID');
        
        $this->getMandatoryElements();

        // Loop over the days
        foreach($this->metaForms as $day => $metaForm){
            if(empty($metaForm)){
                continue;
            }

            // Loop over the mandatory forms for this day
            foreach($metaForm as $formDetails){
                // Save the current formdata before loading the form to save a db query
                $this->formData = $formDetails['form'];

                $this->getForm($formDetails['form']->id);

                $this->checkElementNeedsInput();
            }
        }

        foreach($this->defaultForms as $day => $submissionForm){
            if(empty($submissionForm)){
                continue;
            }

            foreach($submissionForm as $formDetails){
                $formReminder   = $formDetails['reminder'];

                // do not process if not needed
                if($this->passedReminderCount($formReminder)){
                    continue;
                }

                $this->formData = $formDetails['form'];

                $this->processDefaultForm($formReminder);
            }
        }

        $this->mapToUserId();

        return set_transient('form_reminders', $this->reminders, WEEK_IN_SECONDS);
    }

    /**
     * Updates the cache for a specific form
     */
    public function updateFormCache($formId, $userId=''){
        $this->getForm($formId);


    }

    public function getUserFormReminders($userId){
        if(empty($this->userReminders[$userId])){
            return '';
        }

        return $this->userReminders[$userId];
    }

    /**
     * Gets all forms with reminder settings from the db
     */
    protected function getFormsWithReminders(){
        global $wpdb;

        // Prepare the weekdays
        $date = new \DateTime('Sunday');

        for ($i = 0; $i < 7; $i++) {
            $this->metaForms[$date->format('D')]        = []; // 'D' for short day name
            $this->defaultForms[$date->format('D')]     = []; 
            $date->modify('+1 day');
        }

        // Get the forms that have a stardate in the past
        $date				= date('Y-m-d');

        $query				= "SELECT * FROM {$this->formReminderTable} WHERE reminder_startdate <= '$date'";

        $results			= $wpdb->get_results($query);

        foreach($results as $formReminder){
            $formReminder->conditions	= maybe_unserialize($formReminder->conditions);

            $form	= $this->getForm($formReminder->form_id);

            $form	= $this->formData;

            // get the start day of the week
            $day = date('D', strtotime($formReminder->reminder_startdate));

            // This is a form that saves its data in the user meta, so we use different logic for that
            if($this->formData->save_in_meta){
                $varName   = 'metaForms';
            }else{
                $varName   = 'defaultForms';
            }

            $this->$varName[$day][]  = [
                'form'      => $form,
                'reminder'  => $formReminder
            ];
        }
    }

    /**
     * Checks if the maximimum form amount of form reminders has been sent
     * 
     * @param   object  $formReminder   The form reminder obejct
     * 
     * @return  bool                    true if past, false otherwise 
     */
    protected function passedReminderCount($formReminder){
        // Recurring submissions
        if(!empty($formReminder->frequency ) && !empty($formReminder->period)){
            // Get the re-submission intervals since the start
            $interval 		= \DateInterval::createFromDateString("$formReminder->frequency $formReminder->period"); 	// the interval between submissions
            $daterange 		= new \DatePeriod(																			// the date range between startdate and today with the specified interval 
                date_create($formReminder->reminder_startdate), 
                $interval , 
                new \DateTime('now'),
                false
            );

            // Get the current interval
            $currentReminderStart		= $daterange->getEndDate()->format('Y-m-d');
            foreach($daterange as $date1){
                $currentReminderStart = $date1->format('Y-m-d');
            }
        }else{
            $currentReminderStart = $formReminder->reminder_startdate;
        }

        // Do not continue if we already have notified the maximum amount
        if(
            !empty($formReminder->reminder_amount) &&																				// There is an max amount set in weeks
            strtotime("+ $formReminder->reminder_amount $formReminder->reminder_period", strtotime($currentReminderStart)) < time()	// we are passed the amount
        ){
            return true;
        }

        return false;
    }

    /**
     * Get the minimum form submission date
     */
    protected function getMinimumDate($formReminder){
        $since	= '';

        // We have definded a submission
        if(
            !empty($formReminder->frequency) && 
            !empty($formReminder->period) && 
            !empty($formReminder->window_start) && 
            $formReminder->window_start != '0000-00-00'
        ){
            $interval 		= \DateInterval::createFromDateString("$formReminder->frequency $formReminder->period");

            // calculate the start of the current window
            $daterange 		= new \DatePeriod(																			// the date range between startdate and today with the specified interval 
                date_create($formReminder->window_start), 
                $interval , 
                new \DateTime('now'),
                false
            );

            // Get the current interval
            $since		= $daterange->getEndDate()->format('Y-m-d');
            foreach($daterange as $date1){
                $since = $date1->format('Y-m-d');
            }

            $since = "AND timecreated >= '$since'";
        }

        return $since;
    }

    /**
     * Checks a given form for pending reminders
     */
    protected function processDefaultForm($formReminder){
        global $wpdb;

        $since  = $this->getMinimumDate($formReminder);

        // Get all submissions created inside the current submission window
        $query			= "SELECT * FROM {$this->submissionTableName} WHERE form_id=$formReminder->form_id $since";

        $submissions	= $wpdb->get_results($query);

        // get all the users who have submitted the form after the currentIntervalStart date
        $usersWithSubmission	= [];
        foreach($submissions as $submission){
            $usersWithSubmission[]	= $submission->userid;
        }

        $usersWithoutSubmission	= array_diff($this->userIds, $usersWithSubmission);		

        foreach($usersWithoutSubmission as $index => $userWithoutSubmission){
            if($this->checkIfConditionsAppliesToUser($formReminder->conditions, $userWithoutSubmission, $submissions)){
                unset($usersWithoutSubmission[$index]);
            }
        }

        $this->reminders['defaultforms'][$formReminder->form_id]	= array_values($usersWithoutSubmission);
    }

    /**
     * Get mandatory and recommended elements from the db
     */
    protected function getMandatoryElements(){
        global $wpdb;

        $query				        = "SELECT * FROM {$this->elTableName}";

        $this->mandatoryElements	= $wpdb->get_results($query." WHERE mandatory=1 OR recommended=1");
        $this->mandatoryElements	= apply_filters("sim_elements_filter", $this->mandatoryElements	, $this);
    }

    /**
     * Checks if a given set of conditions applies to the current user. Returns true if there is a match
     *
     * @param	object	$conditions		The element conditions
     * @param	int		$userId			The user id
     * @param	array	$submissions	The submissions to check
     *
     * @return	bool					true if no conditions or the condition apply, false if it does not apply
     */
    public function checkIfConditionsAppliesToUser($conditions, $userId, $submissions=''){
        if(!is_array($conditions)){
            return true;
        }

        $conditions	= SIM\cleanUpNestedArray($conditions);

        // Check if the the roles overlap
        if(isset($conditions['roles'])){
            // Check if user has one of the roles
            $user	= get_userdata($userId);
            if($user){
                $userRoles	= $user->roles;
                $intersect	= array_intersect($conditions['roles'], $userRoles);
                if(!empty($intersect)){
                    // There is at least one overlapping role
                    return true;
                }
            }
            unset($conditions['roles']);
        }

        foreach($conditions as $check){
            // get the user value
            $value		= get_user_meta($userId, $check['meta-key'], true);

            $metaIndex  = trim($check['meta-key-index']);
            if(!empty($metaIndex)){
                if(!empty($value[$metaIndex])){
                    $value		= $value[$metaIndex];
                }else{
                    $value		= '';
                }
            }

            if(is_array($value)){
                $value	= array_filter($value);

                if(empty($value)){
                    $value	= '';
                }
            }

            if(is_array($value) && $check['equation'] != 'submitted' && isset($value[0])){
                $value	= $value[0];
            }

            // Get the compare value
            $checkValue	= '';
            if(isset($check['conditional-value'])){
                $checkValue			= $check['conditional-value'];
                $conditionalValue	= strtotime($check['conditional-value']);
                if($conditionalValue && Date('Y', $conditionalValue) < 2200){
                    $checkValue	= Date('Y-m-d', $conditionalValue);
                }
            }

            // compare the values
            switch($check['equation']){
                case '==':
                    $result	= $value == $checkValue;
                    break;
                case '!=':
                    $result	= $value != $checkValue;
                    break;
                case '>':
                    $result	= $value > $checkValue;
                    break;
                case '<':
                    $result	= $value < $checkValue;
                    break;
                case 'submitted':
                    $result	= false;

                    // check if the given userid has submitted the form already 
                    foreach($submissions as $submission){
                        if(is_array($value)){
                            if(in_array($submission->userid, $value)){
                                $result	= true;
                                break;
                            }
                        }else{
                            if($submission->userid == $value){
                                $result	= true;
                                break;
                            }
                        }
                    }
                    break;
                default:
                    $result = false;
            }

            // Check the result
            if($result){
                $applies = true;

                //break this loop when we already know we should skip this field
                if(!empty($check['combinator']) && $check['combinator'] == 'or'){
                    break;
                }
            }else{
                $applies = false;
            }
        }
        
        return $applies;
    }

    /**
     * Checks if a given elements needs some input of a given user and returns html links for each element that needs input
     *
     * @param	array	$elements	The elements
     * @param	int		$userId		The id of the user
     *
     * @return	string				The html
     */
    public function checkElementNeedsInput(){
        //Loop over the users
        foreach($this->userIds as $userId){

            // Loop over all mandatory and required elements
            foreach($this->mandatoryElements as $element){
                //check if this element applies to this user
                $warningCondition	= maybe_unserialize($element->warning_conditions);

                if($this->checkIfConditionsAppliesToUser($warningCondition, $userId)){
                    continue;
                }

                $metakey 	= explode('[', $element->name)[0];
                $value		= get_user_meta($userId, $metakey, true);

                $name		= $element->name;
                if (str_contains($name, '[')){
                    $value = SIM\getMetaArrayValue($userId, $name, $value);
                }

                // Element has a value
                if(!empty($value)){
                    continue;
                }

                // Store the in the reminders array
                if(!isset($this->reminders['metaforms'][$this->formData->id])){
                    $this->reminders['metaforms'][$this->formData->id]   = [];
                }

                if(!isset($this->reminders['metaforms'][$this->formData->id][$element->id])){
                    $this->reminders['metaforms'][$this->formData->id][$element->id]   = [];
                }

                $this->reminders['metaforms'][$this->formData->id][$element->id][]   = $userId;
            }
        }
    }

    /**
     * Gets all mandatory forms for today as html links
     *
     * @param int    		$userId 	WP user id
     *
     * @return string|array 			Returns html links to forms who are due for submission if a userid is given, an array of form => [userids] otherwise
     */
    public function getAllFormRemindersForToday($includeMandatoryForms=true){
        $today      = date('D');
	    $family	    = new SIM\FAMILY\Family();
        $reminders  = [];

        // Form element reminders
        foreach($this->metaForms[$today] as $formDetails){
            $formId     = $formDetails['form']->id;

            // Do nothing if there are no reminders for this form
            if(!isset($this->reminders['metaforms'][$formId])){
                continue;
            }

            foreach($this->reminders['metaforms'][$formId] as $elementId => $userIds){

                $this->getForm($formId);

                foreach($userIds as $userId){
                    $child				= $family->isChild($userId);
                    $childName          = '';
                    if($child){
                        $childName		= get_userdata($userId)->first_name;
                    }

                    $result             = $this->getElementReminderHtml($elementId, 'mandatory', $childName);

                    if(!empty($result)){   
                        if(!isset($reminders[$userId])){
                            $reminders[$userId] = [];
                        }

                        if(!isset($reminders[$userId][$formId])){
                            $reminders[$userId][$formId] = '<ul>';
                        }

                        $reminders[$userId][$formId]   .= $result;
                    }
                }
            }
        }

        // Form reminders
        if($includeMandatoryForms){
            foreach($this->defaultForms[$today] as $formDetails){
                $formId     = $formDetails['form']->id;

                // Do nothing if there are no reminders for this form
                if(!isset($this->reminders['defaultforms'][$formId])){
                    continue;
                }

                foreach($this->reminders['defaultforms'] as $formId => $userIds){
                    foreach($userIds as $userId){
                        $child				= $family->isChild($userId);
                        $childName          = '';
                        if($child){
                            $childName		= get_userdata($userId)->first_name;
                        }

                        if(!isset($reminders[$userId])){
                            $reminders[$userId] = '<ul>';
                        }

                        $reminders[$userId] .= $this->getFormReminderHtml($formId, $childName);
                    }
                }
            }
        }

        foreach($reminders as $userId => &$forms){
            foreach($forms as $formId => &$reminder){
                $reminder .= '</ul>';
            }
        }

        return $reminders;
    }

    /**
     * Maps all reminders by user id
     */
    protected function mapToUserId(){
        foreach($this->reminders['metaforms'] as $formId => $elements){
            foreach($elements as $elementId => $userIds){
                foreach($userIds as $userId){
                    if(!isset($this->userReminders[$userId])){
                        $this->userReminders[$userId]   = [];
                    }

                    if(!isset($this->userReminders[$userId]['metaforms'])){
                        $this->userReminders[$userId]['metaforms']   = [];
                    }

                    if(!isset($this->userReminders[$userId]['metaforms'][$formId])){
                        $this->userReminders[$userId]['metaforms'][$formId]   = [];
                    }

                    $this->userReminders[$userId]['metaforms'][$formId][]    = $elementId; 
                }
            }
        }

        foreach($this->reminders['defaultforms'] as $formId => $userIds){
            foreach($userIds as $userId){
                $this->userReminders[$userId]['defaultforms'][]     = $formId;
            }
        }
    }

    /**
     * Get the html for a specific element
     */
    protected function getElementReminderHtml($elementId, $type='all', $childName=''){
        $element    = $this->getElementById($elementId);
        if(!$element){
            return '';
        }

        if($type != 'all' && !$element->$type){
            return '';
        }

        $formUrl    = $this->formData->form_url;

        parse_str(parse_url($formUrl, PHP_URL_QUERY), $params);

        //Show a nice name
        $name	= str_replace(['[]', '_'], ['', ' '], $element->nicename);
        $name	= ucfirst(str_replace(['[', ']'], [': ',''], $name));

        $baseUrl	= explode('main-tab=', $_SERVER['REQUEST_URI'])[0];
        $mainTab	= $params['main-tab'];
        if(!empty($childName)){
            $name 		.= " for $childName";
            $mainTab	 = strtolower($childName);
            $formUrl	 = str_replace($params['main-tab'], $mainTab, $formUrl);
        }
        
        // If the url has no hash or we are not on the same url
        if(
            !isset($_GET['userid']) && 
            !str_contains($baseUrl, 'wp-json') &&
            (
                empty($params['main-tab']) || 
                !str_contains($formUrl, $baseUrl)
            )
        ){
            return "<li><a href='$formUrl#{$element->name}'>$name</a></li>";
        }

        //We are on the same page, just change the hash
        $secondTab	= '';
        $names		= explode('[', $element->name);
        if(count($names) > 1){
            $secondTab	= $names[0];
        }

        return "<li><a onclick='Main.changeUrl(this, `$secondTab`)' data-param-val='$mainTab' data-hash={$element->name} style='cursor:pointer'>$name</a></li>";
    }

    /**
     * Gets the html for a form reminder
     */
    protected function getFormReminderHtml($formId, $childName){
        $this->getForm($formId);

        $formUrl    = $this->formData->form_url;

        $formName   = str_replace(['-', '_'], ' ', $this->formData->name);

        $text       = "Fill in the $formName form";
        if(!empty($childName)){
            $text   .= " for $childName";
        }

        return "<li><a href='$formUrl'>$text</a></li>";
    }

    /**
     * Gets the reminder html for a given user id
     * 
     * @param   int $userId     The user id to get the reminder html for
     */
    public function getReminderHtml($userId, $type){
        // Nothing to do
        if(empty($this->userReminders[$userId])){
            return '';
        }
        
	    $family	            = new SIM\FAMILY\Family();
        $child				= $family->isChild($userId);
        $childName          = '';
        if($child){
            $childName		= get_userdata($userId)->first_name;
        }
        
        $html				= '';

        // HTML for individual elements on a meta form
        if(!empty($this->userReminders[$userId]['metaforms'])){
            foreach($this->userReminders[$userId]['metaforms'] as $formId => $elements){
                // Load the form data
                $this->getForm($formId);

                foreach($elements as $elementId){
                    $result = $this->getElementReminderHtml($elementId, $type, $childName);

                    if(!empty($result)){
                        $html   .= $result;
                    }
                }
            }
        }

        // Forms to be submitted
        if(!empty($this->userReminders[$userId]['defaultforms'])){
            foreach($this->userReminders[$userId]['defaultforms'] as $formId){
                $html .= $this->getFormReminderHtml($formId, $childName);
            }
        }

        if(!empty($html)){
            $html	= "<ul>$html</ul>";
        }

        $html	= apply_filters("sim_manadatory_html_filter", $html, $userId, $this);

        return $html;
    }

    /**
     * Sends reminders by e-mail to submit or update a form
     */
    public function sendFormReminders(){
        $today  = date('D');

        // Send e-mails for forms to be submitted
        foreach($this->defaultForms[$today] as $formDetails){
            $this->formData = $formDetails['form'];

            $formId = $this->formData->id;

            // Do nothing if there are no reminders for this form
            if(!isset($this->reminders['defaultforms'][$formId])){
                continue;
            }

            // Load the e-mail settings
            $this->getEmailSettings();

            foreach($this->reminders['defaultforms'][$formId] as $userId){
                $this->sendEmail($userId);
            }
        }

        foreach($this->getAllFormRemindersForToday(false) as $userId => $forms){
            foreach($forms as $formId => $html){
                // Load the form data for this form
                foreach($this->metaForms[$today] as $formDetails){
                    if($formDetails['form']->id == $formId){
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

    protected function sendEmail($userId, $html=''){   
        $user   = get_userdata($userId);
        
        // Invalid user id given
        if(!$user){
            return;
        }

        foreach($this->emailSettings as $mail){
            $mail   = (object)$mail;

            if($mail->email_trigger != 'shouldsubmit'){
                continue;
            }

            $from       = $mail->from;

            $to         = $mail->to;

            $subject    = $mail->subject;

            $message    = $mail->message;

            $headers	= [];

            if(!empty(trim($mail->headers))){
                $headers	= explode("\n", trim($mail->headers));
            }

            if(!empty($from) && !str_contains($mail->headers, 'Reply-To:')){
                if(str_contains($from, '%')){
                    $headers[]	= "Reply-To: ". $user->user_email;
                }else{
                    $headers[]	= "Reply-To: $from";
                }
            }

            if(str_contains($to, '%')){
                $recipient  = $user->user_email; 
            }else{
                $recipient  = $to;
            }

            if(!empty($html) && !str_contains($message, '%reminders%')){
                $message .= '%reminders%';
            }

            $msg      = $this->processPlaceholders(
                $message,
                [
                    'formurl'   => $this->formData->form_url,
                    'name'      => $user->first_name,
                    'email'     => $user->user_email,
                    'reminders' => $html
                ]
            );

            wp_mail($recipient , $subject, $msg, $headers);
        }
    }
}