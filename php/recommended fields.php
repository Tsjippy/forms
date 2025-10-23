<?php
namespace SIM\FORMS;
use SIM;

add_filter('sim_mandatory_html_filter', __NAMESPACE__.'\addChildFields', 10, 3);
function addChildFields($html, $userId, $object){
	// Add warnings for child fields
	$family = get_user_meta($userId, "family", true);
	
	//User has children
	if (isset($family["children"])){

		// Loop over children
		foreach($family["children"] as $child){
			$userData = get_userdata($child);
			// Valid user account
			if ($userData){
				// Add html for each field as well
				$html	.= $object->getReminderHtml($child, 'mandatory');
			}
		}
	}

	return $html;
}

add_action('sim_dashboard_warnings', __NAMESPACE__.'\dashboardWarnings');
function dashboardWarnings($userId){
	$forms	= new FormReminders();

	$html	 = $forms->getReminderHtml($userId, 'recommended');
	
	if (empty($html)){
		echo "<p>All your data is up to date, well done.</p>";
	}else{
		echo "<h3>Please finish your account:</h3>";
	}
		
	if (!empty($html)){
		echo "<p>Please complete the following:<br></p>$html";
	}
}