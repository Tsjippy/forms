<?php
namespace SIM\FORMS;
use SIM;

// update reminder cache after each form submission and reminder settings update
add_action('sim-after-form-submit', __NAMESPACE__.'\updateReminders');
add_action('sim-after-form-reminder-save', __NAMESPACE__.'\updateReminders');
function updateReminders(){
    $forms  = new FormReminders();
    
    $forms->updateCache();
}