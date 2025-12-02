<?php
namespace SIM\forms;
use SIM;

const MODULE_VERSION		= '9.0.4';
//module slug is the same as grandparent folder name
DEFINE(__NAMESPACE__.'\MODULE_SLUG', strtolower(basename(dirname(__DIR__))));

DEFINE(__NAMESPACE__.'\MODULE_PATH', plugin_dir_path(__DIR__));

add_filter('sim_submenu_forms_description', __NAMESPACE__.'\moduleDescription', 10, 2);
function moduleDescription($description, $moduleSlug){
	ob_start();
	$url		= SIM\ADMIN\getDefaultPageLink($moduleSlug, 'forms-pages');
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

add_filter('sim_module_forms_data', __NAMESPACE__.'\moduleData');
function moduleData($dataHtml){
	$html	= '';

	if(isset($_POST['delete'])){
		$html	.= "<div class='success' style='margin-top:20px;'>Form succesfully deleted</div>";
	}

	if(isset($_GET['deleteall'])){
		$html	.= "<div class='success' style='margin-top:20px;'>Empty forms succesfully deleted</div>";
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

add_filter('sim_module_forms_functions', __NAMESPACE__.'\moduleFunctions');
function moduleFunctions($functionHtml){
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
	return $functionHtml.ob_get_clean();
}

add_filter('sim_module_forms_after_save', __NAMESPACE__.'\moduleUpdated', 10, 2);
function moduleUpdated($options, $oldOptions){
	$simForms = new SimForms();
	$simForms->createDbTables();

	// Create frontend posting page
	$options	= SIM\ADMIN\createDefaultPage($options, 'forms-pages', 'Form selector', '[formselector]', $oldOptions);

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

	if(isset($_GET['deleteall'])){
		$simForms	= new SaveFormSettings();

		global $wpdb;

		$emptyForms	= $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sim_forms WHERE `version` = 1 and `button_text` IS NULL");

		foreach($emptyForms as $form){
			$simForms->deleteForm($form->id);
		}
	}
}

add_filter('display_post_states', __NAMESPACE__.'\postStates', 10, 2);
function postStates( $states, $post ) {
    
    if (is_array(SIM\getModuleOption(MODULE_SLUG, 'forms-pages')) && in_array($post->ID, SIM\getModuleOption(MODULE_SLUG, 'forms-pages', false))) {
        $states[] = __('Form selector page');
    }

    return $states;
}

add_action('sim_module_forms_activated', __NAMESPACE__.'\moduleActivated');
function moduleActivated(){
	$simForms = new SimForms();
	$simForms->createDbTables();

	scheduleTasks();
}

add_action('sim_module_forms_deactivated', __NAMESPACE__.'\moduleDeActivated');
function moduleDeActivated($options){
	foreach($options['forms-pages'] as $page){
		// Remove the auto created page
		wp_delete_post($page, true);
	}

	wp_clear_scheduled_hook( 'auto_archive_action' );
	wp_clear_scheduled_hook( 'mandatory_fields_reminder_action' );
}
