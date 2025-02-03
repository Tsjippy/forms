<?php
namespace SIM\forms;
use SIM;

const MODULE_VERSION		= '8.2.5';
//module slug is the same as grandparent folder name
DEFINE(__NAMESPACE__.'\MODULE_SLUG', strtolower(basename(dirname(__DIR__))));

DEFINE(__NAMESPACE__.'\MODULE_PATH', plugin_dir_path(__DIR__));

add_filter('sim_submenu_description', __NAMESPACE__.'\moduleDescription', 10, 2);
function moduleDescription($description, $moduleSlug){
	//module slug should be the same as the constant
	if($moduleSlug != MODULE_SLUG)	{
		return $description;
	}

	ob_start();
	$url		= SIM\ADMIN\getDefaultPageLink($moduleSlug, 'forms_pages');
	if(!empty($url)){
		?>
		<p>
			<strong>Auto created page:</strong><br>
			<a href='<?php echo $url;?>'>Form selector page</a>
		</p>
		<?php
	}
	?>
	<?php
	return $description.ob_get_clean();
}

add_filter('sim_submenu_options', __NAMESPACE__.'\moduleOptions', 10, 3);
function moduleOptions($optionsHtml, $moduleSlug, $settings){
	//module slug should be the same as grandparent folder name
	if($moduleSlug != MODULE_SLUG){
		return $optionsHtml;
	}

	ob_start();
	?>
	Do you want to use this module also for userdata?
	<label class="switch">
		<input type="checkbox" name="userdata" <?php if(isset($settings['userdata'])){echo 'checked';}?>>
		<span class="slider round"></span>
	</label>

	<?php
	if(isset($settings['userdata'])){
		?>
		<br>
		<br>
		<label for="reminder_freq">
			How often should people be reminded of remaining form fields to fill?
		</label>
		<br>
		<select name="reminder_freq">
			<?php
			SIM\ADMIN\recurrenceSelector($settings['reminder_freq']);
			?>
		</select>

		<?php
	}

	return ob_get_clean();
}

add_filter('sim_email_settings', __NAMESPACE__.'\emailSettings', 10, 3);
function emailSettings($optionsHtml, $moduleSlug, $settings){
	//module slug should be the same as grandparent folder name
	if($moduleSlug != MODULE_SLUG || !isset($settings['userdata'])){
		return $optionsHtml;
	}

	ob_start();

	?>
	<label>
		Define the e-mail people get when they need to fill in some mandatory form information.<br>
		There is one e-mail to adults, and one to parents of children with missing info.<br>
	</label>
	<br>

	<?php
	$formAdultEmails    = new AdultEmail(wp_get_current_user());
	$formAdultEmails->printPlaceholders();
	?>

	<h4>E-mail to adults</h4>
	<?php

	$formAdultEmails->printInputs($settings);
	
	?>
	<br>
	<br>
	<h4>E-mail to parents about their child</h4>
	<?php

	$formAdultEmails    = new ChildEmail(wp_get_current_user());

	$formAdultEmails->printInputs($settings);

	return ob_get_clean();
}

add_filter('sim_module_data', __NAMESPACE__.'\moduleData', 10, 2);
function moduleData($dataHtml, $moduleSlug){
	//module slug should be the same as grandparent folder name
	if($moduleSlug != MODULE_SLUG){
		return $dataHtml;
	}
	$html	= '';

	if(isset($_POST['delete'])){
		$html	.= "<div class='success' style='margin-top:20px;'>Form succesfully deleted</div>";
	}

	$simForms	= new SaveFormSettings();
	$simForms->getForms();

	// sort the forms on name
	usort($simForms->forms, function($a, $b){
			return strcasecmp($a->name, $b->name);
	});

	$html	.= "<table class='formoverview'>";
		$html	.= "<thead>";
			$html	.= "<tr>";
				$html	.= "<th>Id</th>";
				$html	.= "<th>Name</th>";
				$html	.= "<th>Url</th>";
				$html	.= "<th>Actions</th>";
			$html	.= "</tr>";
		$html	.= "</thead>";
		$html	.= "<tbody>";
			foreach($simForms->forms as $form){
				$formName	= $form->name;
				$formUrl	= '';

				$formName	= $form->form_name;
				$formUrl	= $form->form_url;

				$formName	= str_replace('_', ' ', ucfirst($formName));

				$html	.= "<tr>";
					$html	.= "<td>$form->id</td>";
					$html	.= "<td>$formName</td>";
					if(empty($formUrl)){
						$html	.= "<td>Not set</td>";
					}else{
						$html	.= "<td><a href='$formUrl'>Link</a></td>";
					}
					$html	.= "<td>";
						$html	.= "<form action='' method='post' style='display: inline-block; margin-right:10px;'>";
							$html	.= "<button class='small' name='export' value='{$form->id}'>Export</button>";
						$html	.= "</form>";
						$html	.= "<form action='' method='post' style='display: inline-block;'>";
							$html	.= "<button class='small' name='delete' value='{$form->id}'>Delete</button>";
						$html	.= "</form>";
					$html	.= "</td>";
				$html	.= "</tr>";
			}
		$html	.= "</tbody>";
	$html	.= "</table>";

	return $dataHtml.$html;
}

add_filter('sim_module_functions', __NAMESPACE__.'\moduleFunctions', 10, 2);
function moduleFunctions($functionHtml, $moduleSlug){
	//module slug should be the same as grandparent folder name
	if($moduleSlug != MODULE_SLUG){
		return $functionHtml;
	}
	
	ob_start();
	?>
	<h4>Form import</h4>
	<p>
		It is possible to import forms exported from this plugin previously.<br>
		Use the button below to do so.
	</p>
	<form method='POST' enctype="multipart/form-data">
		<label>
			Select a form export file
			<input type='file' name='formfile'>
		</label>
		<br>
		<button type='submit' name='import-form'>Import the form</button>
	</form>

	<?php
	return ob_get_clean();
}

add_filter('sim_module_updated', __NAMESPACE__.'\moduleUpdated', 10, 3);
function moduleUpdated($options, $moduleSlug, $oldOptions){
	//module slug should be the same as grandparent folder name
	if($moduleSlug != MODULE_SLUG){
		return $options;
	}

	$simForms = new SimForms();
	$simForms->createDbTable();

	// Create frontend posting page
	$options	= SIM\ADMIN\createDefaultPage($options, 'forms_pages', 'Form selector', '[formselector]', $oldOptions);

	scheduleTasks();

	return $options;
}

add_action('sim_module_actions',__NAMESPACE__.'\moduleActions' );
function moduleActions(){
	if(isset($_POST['import-form'])){
		$formBuilder	= new FormBuilderForm();
		$formBuilder->importForm($_FILES['formfile']['tmp_name']);
	}

	if(isset($_POST['export']) && is_numeric($_POST['export'])){
		$simForms	= new FormBuilderForm();
		$simForms->exportForm($_POST['export']);
	}

	if(isset($_POST['delete']) && is_numeric($_POST['delete'])){
		$simForms	= new SaveFormSettings();
		$simForms->deleteForm($_POST['delete']);
	}
}

add_filter('display_post_states', __NAMESPACE__.'\postStates', 10, 2);
function postStates( $states, $post ) {
    
    if (is_array(SIM\getModuleOption(MODULE_SLUG, 'forms_pages')) && in_array($post->ID, SIM\getModuleOption(MODULE_SLUG, 'forms_pages', false))) {
        $states[] = __('Form selector page');
    }

    return $states;
}

add_action('sim_module_activated', __NAMESPACE__.'\moduleActivated');
function moduleActivated($moduleSlug){
	//module slug should be the same as grandparent folder name
	if($moduleSlug != MODULE_SLUG)	{return;}
	
	$simForms = new SimForms();
	$simForms->createDbTable();

	$formTable = new DisplayFormResults();
	$formTable->createDbShortcodeTable();

	scheduleTasks();
}

add_action('sim_module_deactivated', __NAMESPACE__.'\moduleDeActivated', 10, 2);
function moduleDeActivated($moduleSlug, $options){
	//module slug should be the same as grandparent folder name
	if($moduleSlug != MODULE_SLUG)	{
		return;
	}

	foreach($options['forms_pages'] as $page){
		// Remove the auto created page
		wp_delete_post($page, true);
	}

	wp_clear_scheduled_hook( 'auto_archive_action' );
	wp_clear_scheduled_hook( 'mandatory_fields_reminder_action' );
}
