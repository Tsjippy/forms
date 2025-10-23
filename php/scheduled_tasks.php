<?php
namespace SIM\FORMS;
use SIM;

add_action('init', __NAMESPACE__.'\initTasks');
function initTasks(){
	//add action for use in scheduled task
	add_action( 'auto_archive_action', __NAMESPACE__.'\autoArchiveFormEntries' );
    
	add_action( 'form_reminder_action', __NAMESPACE__.'\formReminder' );
}

function scheduleTasks(){
    SIM\scheduleTask('auto_archive_action', 'daily');
    
    SIM\scheduleTask('form_reminder_action', 'daily');
}

function autoArchiveFormEntries(){
	$editFormResults = new EditFormResults();
	$editFormResults->autoArchive();
}

/**
 * Sends reminders by e-mail and Signal to fill in a form
 */
function formReminder(){
    // Also send a reminder for any mandatory forms
    $simForms   = new FormReminders();

    $simForms->sendFormReminders();
}