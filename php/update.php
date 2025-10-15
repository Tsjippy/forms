<?php
namespace SIM\FORMS;
use SIM;

add_action('sim_forms_module_update', __NAMESPACE__.'\moduleUpdate');
function moduleUpdate($oldVersion){
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
        $simForms->createDbTables();

        // Shortcode data
        $shortcodes   = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sim_form_shortcodes");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'title', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `title` tinytext");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'default_sort', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `default_sort` tinytext");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'sort_direction', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `sort_direction` tinytext");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'filter', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `filter` longtext");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'hide_row', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `hide_row` tinytext");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'result_type', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `result_type` tinytext");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'split_table', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `split_table` boolean");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'archived', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `archived` boolean");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'view_right_roles', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `view_right_roles` longtext");
        maybe_add_column("{$wpdb->prefix}sim_form_shortcodes", 'edit_right_roles', "ALTER TABLE {$wpdb->prefix}sim_form_shortcodes ADD COLUMN `edit_right_roles` longtext");
        
        foreach($shortcodes as &$shortcode){
            $tableSettings  = maybe_unserialize($shortcode->table_settings);

            if(!empty($tableSettings)){
                $data = [
                    'form_id'				=> $shortcode->form_id,
                    'title' 				=> $tableSettings['title'],
                    'default_sort'			=> $tableSettings['default_sort'],	
                    'sort_direction'		=> $tableSettings['sort-direction'],
                    'filter'				=> maybe_serialize($tableSettings['filter']),	
                    'hide_row'				=> $tableSettings['hiderow'],
                    'result_type'			=> $tableSettings['result_type'],
                    'split_table'			=> $tableSettings['split-table'] == 'yes',
                    'archived'				=> $tableSettings['archived'] == 'true',
                    'view_right_roles'		=> maybe_serialize(array_keys($tableSettings['view_right_roles'])),
                    'edit_right_roles'		=> maybe_serialize(array_keys($tableSettings['edit_right_roles']))
                ];

                ksort($data);
                $wpdb->update(
                    $simForms->shortcodeTable, 
                    $data, 
                    ['id' => $shortcode->id]
                );
            }

            $columnSettings  = maybe_unserialize($shortcode->column_settings);

            if(!empty($columnSettings)){
                foreach($columnSettings as $elId => $columnSetting){
                    if(empty($columnSetting)){
                        continue;
                    }

                    $data = [
                        'shortcode_id'		=> $shortcode->id,
                        'element_id'		=> $elId,
                        'show'				=> $columnSetting['show'] != 'hide',
                        'name'				=> $columnSetting['name'],
                        'nice_name'			=> $columnSetting['nice_name'],
                        'view_right_roles'	=> maybe_serialize($columnSetting['view_right_roles']),
                        'edit_right_roles'	=> maybe_serialize($columnSetting['edit_right_roles']),
                    ];
                    $wpdb->insert($simForms->shortcodeColumnSettingsTable, $data);
                }
            }
        }
        
        // remimder conditions
        $forms   = $wpdb->get_results("SELECT * FROM $simForms->tableName");
        foreach($forms as $form){
            $reminders  = maybe_unserialize($form->reminder_conditions);

            foreach($reminders as &$reminder){
                foreach($reminder as $i => $value){
                    if(is_array($value)){
                        foreach($value as $key => $v){
                            $newIndex   = str_replace('_', '-', $key);

                            unset($reminder[$i][$key]);
                            
                            $reminder[$i][$newIndex]   = $v;
                        }

                        $value = $reminder[$i];
                    }

                    $newIndex   = str_replace('_', '-', $i);

                    unset($reminder[$i]);
                            
                    $reminder[$newIndex]   = $value;
                }
            }

            $version    = $form->version + 1;

            $wpdb->update($simForms->tableName,
                [
                    'version'                   => $version,
                    'reminder_conditions'		=> maybe_serialize($reminders)
                ],
                [
                    'id'		            => $form->id,
                ],
            );
        }

        $forms   = $wpdb->get_results("SELECT * FROM $simForms->tableName WHERE `emails` IS NOT NULL");
        foreach($forms as $form){
            $emails  = maybe_unserialize($form->emails);

            foreach($emails as &$email){
                if(!isset($email['emailtrigger'])){
                    continue;
                }

                $data = [
                    "form_id"               => $form->id,
                    "email_trigger"         => $email['emailtrigger'],
                    "submitted_trigger"     => maybe_serialize($email['submittedtrigger']),
                    "conditional_field"     => maybe_serialize($email['conditionalfield']),
                    "conditional_fields"    => maybe_serialize($email['conditionalfields']),
                    "conditional_value"     => $email['conditionalvalue'],
                    "from_email"            => $email['fromemail'],
                    "from"                  => $email['from'],
                    "conditional_from_email"=> maybe_serialize($email['conditionalfromemail']),
                    "else_from"             => $email['elsefrom'],
                    "email_to"              => $email['emailto'],
                    "to"                    => $email['to'],
                    "else_to"               => $email['elseto'],
                    "conditional_email_to"  => maybe_serialize($email['conditionalemailto']),
                    "subject"               => $email['subject'],
                    "message"               => $email['message'],
                    "headers"               => maybe_serialize($email['headers']),
                    "files"                 => maybe_serialize($email['files']),
                ];

                $wpdb->insert($simForms->formEmailTable, $data, $formBuilder->formEmailTableFormats);
            }
        }
        
        $elements   = $wpdb->get_results("SELECT * FROM $simForms->elTableName");

        foreach($elements as $element){

            $element->type  = str_replace('_', '-', $element->type);

            // conditions
            $conditions = maybe_unserialize($element->conditions);

            foreach($conditions as &$condition){
                foreach($condition as $index => $value){
                    if(is_array($value)){
                        foreach($value as $i => $rule){
                            foreach($rule as $key => $v){
                                $newIndex   = str_replace('_', '-', $key);

                                unset($condition[$index][$i][$key]);
                                $condition[$index][$i][$newIndex] = $v;
                            }
                        }

                        $value  = $condition[$index];
                    }
                    
                    $newIndex   = str_replace('_', '-', $index);

                    $newIndex   = str_replace('propertyname', 'property-name', $newIndex);

                    unset($condition[$index]);

                    $condition[$newIndex]   = $value;
                }
            }

            $element->conditions    = maybe_serialize($conditions);

            // warning conditions
            $conditions = maybe_unserialize($element->warning_conditions);

            foreach($conditions as &$condition){
                foreach($condition as $index => $value){
                    if(is_array($value)){
                        foreach($value as $i => $rule){
                            foreach($rule as $key => $v){
                                $newIndex   = str_replace('_', '-', $i);

                                unset($condition[$index][$i][$key]);
                                $condition[$index][$i][$newIndex] = $v;
                            }
                        }

                        $value  = $condition[$index];
                    }
                    
                    $newIndex   = str_replace('_', '-', $index);
                    unset($condition[$index]);

                    $condition[$newIndex]   = $value;
                }
            }

            $element->warning_conditions    = maybe_serialize($conditions);

            $element    = (array)$element;

            $wpdb->update(
                $simForms->elTableName,
                $element,
                array(
                    'id'		=> $element['id'],
                )
            );
        }
     
        $path	= plugin_dir_path(__DIR__)."js/dynamic";
        if (is_dir($path)) {
            $files = glob($path . '/*'); // Get all files and directories in the path

            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    if($oldVersion < '8.7.4'){
        $query						= "SELECT * FROM {$simForms->shortcodeTable}";
		$shortcodes 		        = $wpdb->get_results($query);

        foreach($shortcodes as $tableSettings){
            foreach($tableSettings as $key => &$value){
                $value	= maybe_unserialize($value);
            }

            if(is_array($tableSettings->view_right_roles) && !is_numeric(array_keys($tableSettings->view_right_roles)[0])){
                $tableSettings->view_right_roles    = array_keys($tableSettings->view_right_roles);
            }

            if(is_array($tableSettings->edit_right_roles) && !is_numeric(array_keys($tableSettings->edit_right_roles)[0])){
                $tableSettings->edit_right_roles    = array_keys($tableSettings->edit_right_roles);
            }


            foreach($tableSettings as $key => &$value){
                $value	= maybe_serialize($value);
            }

            $tableSettings  = (array)$tableSettings;
            $wpdb->update(
                $simForms->shortcodeTable,
                $tableSettings,
                array(
                    'id'		=> $tableSettings['id'],
                )
            );

            $wpdb->rows_affected;
        }
    }
}

add_action('init', function(){
    moduleUpdate('8.7.3');
});