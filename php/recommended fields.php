<?php
namespace TSJIPPY\FORMS;
use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('tsjippy_mandatory_html_filter', __NAMESPACE__.'\addChildFields', 10, 3);
function addChildFields($html, $userId, $object){
	// Add warnings for child fields
	$family = new TSJIPPY\FAMILY\Family();

	// Loop over children
	foreach($family->getChildren($userId) as $child){
		$userData = get_userdata($child);
		// Valid user account
		if ($userData){
			// Add html for each field as well
			$html	.= $object->getReminderHtml($child, 'mandatory');
		}
	}

	return $html;
}

add_action('tsjippy_dashboard_warnings', __NAMESPACE__.'\dashboardWarnings');
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