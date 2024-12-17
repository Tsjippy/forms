<?php
namespace SIM\FORMS;
use SIM;

add_action('sim_forms_module_update', __NAMESPACE__.'\pluginUpdate');
function pluginUpdate($oldVersion){
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    require_once ABSPATH . 'wp-admin/install-helper.php';

    $simForms = new SimForms();

    if($oldVersion < '8.1.1'){
        maybe_add_column($simForms->tableName, 'reminder_amount', "ALTER TABLE $simForms->tableName ADD COLUMN `reminder_amount` LONGTEXT");
    }
}