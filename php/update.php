<?php
namespace SIM\FORMS;
use SIM;

add_action('sim_forms_module_update', __NAMESPACE__.'\pluginUpdate');
function pluginUpdate($oldVersion){
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    require_once ABSPATH . 'wp-admin/install-helper.php';

    SIM\printArray($oldVersion);

    $simForms = new SimForms();

    if($oldVersion < '8.2.1'){
        maybe_add_column($simForms->tableName, 'reminder_amount', "ALTER TABLE $simForms->tableName ADD COLUMN `reminder_amount` LONGTEXT");

        SIM\printArray("Added column");
    }

    if($oldVersion < '8.5.9'){
        $simForms->getForms();
        foreach($simForms->forms as $formData){
            $fullRightRoles = maybe_unserialize($formData->full_right_roles);
            if(is_array($fullRightRoles) && !is_numeric(array_keys($fullRightRoles)[0])){
                $fullRightRoles = array_keys($fullRightRoles);
            }

            $submitOthersForm = maybe_unserialize($formData->submit_others_form);
            if(is_array($submitOthersForm) && !is_numeric(array_keys($submitOthersForm)[0])){    
                $submitOthersForm = array_keys($submitOthersForm);
            }

            $wpdb->update($simForms->tableName,
                [
                    'full_right_roles'		=> maybe_serialize($fullRightRoles),
                    'submit_others_form'	=> maybe_serialize($submitOthersForm)
                ],
                [
                    'id'		            => $formData->id,
                ],
            );
        }
    }

    if($oldVersion < '8.6.9'){
        maybe_add_column($simForms->elTableName, 'add', "ALTER TABLE $simForms->elTableName ADD COLUMN `add` LONGTEXT");
        maybe_add_column($simForms->elTableName, 'remove', "ALTER TABLE $simForms->elTableName ADD COLUMN `remove` LONGTEXT");
    }

    if($oldVersion < '8.7.0'){
        $elements   = $wpdb->get_results("SELECT * FROM $simForms->elTableName WHERE `conditions` IS NOT NULL");

        foreach($elements as $element){
            $conditions = maybe_unserialize($element->conditions);

            foreach($conditions as &$condition){
                foreach($condition as $index => $value){
                    if(is_array($value)){
                        foreach($value as &$rule){
                            foreach($rule as $i => $v){
                                $newIndex   = str_replace('_', '-', $i, $c);

                                if($c > 0){
                                    unset($rule[$i]);
                                    $rule[$newIndex]    = $v;
                                }
                            }


                        }
                    }

                    $newIndex   = str_replace('_', '-', $index, $count);

                    if($count > 0){
                        unset($condition[$index]);
                    }

                    $condition[$newIndex]   = $value;
                }
            }

            $element->conditions    = maybe_serialize($conditions);

            $el    = (array)$element;
            $wpdb->update(
                $simForms->elTableName,
                $el,
                array(
                    'id'		=> $element->id,
                ),
                $simForms->elementTableFormats,
                ['%d']
            );
        }
    }
}

add_action('init', function(){
    pluginUpdate('8.6.9');
});