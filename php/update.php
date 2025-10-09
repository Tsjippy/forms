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
        $simForms->createDbTables();

        // Shortcode data
        $shortcodes   = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sim_form_shortcodes");
        foreach($shortcodes as &$shortcode){
            $tableSettings  = maybe_unserialize($shortcode->table_settings);

            if(!empty($tableSettings)){
                $data = [
                    'shortcode_id'			=> $shortcode->id,
                    'form_id'				=> $shortcode->form_id,
                    'title' 				=> $tableSettings['title'],
                    'default_sort'			=> $tableSettings['default_sort'],	
                    'sort_direction'		=> $tableSettings['sort-direction'],
                    'filter'				=> maybe_serialize($tableSettings['filter']),	
                    'hide_row'				=> $tableSettings['hiderow'],
                    'result_type'			=> $tableSettings['result_type'],
                    'split_table'			=> $tableSettings['split-table'] == 'yes',
                    'archived'				=> $tableSettings['archived'] == 'true',
                    'view_right_roles'		=> maybe_serialize($tableSettings['view_right_roles']),
                    'edit_right_roles'		=> maybe_serialize($tableSettings['edit_right_roles'])
                ];
                $wpdb->insert($simForms->shortcodeTableSettingsTable, $data, $formBuilder->shortcodeTableSettingsFormats);
            }

            $columnSettings  = maybe_unserialize($shortcode->column_settings);

            if(!empty($columnSettings)){
                foreach($columnSettings as $elId => $columnSetting){
                    if(empty($columnSetting)){
                        continue;
                    }

                    $data = [
                        'shortcode_id'		=> $shortcode->id,
                        'form_id'			=> $shortcode->form_id,
                        'element_id'		=> $elId,
                        'show'				=> $columnSetting['show'] != 'hide',
                        'name'				=> $columnSetting['name'],
                        'nice_name'			=> $columnSetting['nice_name'],
                        'view_right_roles'	=> maybe_serialize($columnSetting['view_right_roles']),
                        'edit_right_roles'	=> maybe_serialize($columnSetting['edit_right_roles']),
                    ];
                    $wpdb->insert($simForms->shortcodeColumnSettingsTable, $data, $formBuilder->shortcodeTableColumnFormats);
                }
            }
        }
        
        // remimder conditions
        $forms   = $wpdb->get_results("SELECT * FROM $simForms->tableName WHERE `reminder_conditions` IS NOT NULL");
        foreach($forms as &$form){
            $reminders  = maybe_unserialize($form->reminder_conditions);

            foreach($reminders as &$reminder){
                foreach($reminder as $index => $value){
                    if(is_array($value)){
                        foreach($value as $i => $v){
                            $newIndex   = str_replace('_', '-', $i);

                            unset($value[$i]);
                            
                            $value[$newIndex]   = $v;
                        }
                    }

                    $newIndex   = str_replace('_', '-', $index);

                    unset($reminder[$index]);
                            
                    $reminder[$newIndex]   = $value;
                }
            }

            $wpdb->update($simForms->tableName,
                [
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
                    "submitted_trigger"     => $email['submittedtrigger'],
                    "conditional_field"     => $email['conditionalfield'],
                    "conditional_value"     => $email['conditionalvalue'],
                    "from_email"            => $email['fromemail'],
                    "from"                  => $email['from'],
                    "conditional_from_email"=> $email['conditionalfromemail'],
                    "else_from"             => $email['elsefrom'],
                    "email_to"              => $email['emailto'],
                    "to"                    => $email['to'],
                    "else_to"               => $email['elseto'],
                    "conditional_email_to"  => $email['conditionalemailto'],
                    "subject"               => $email['subject'],
                    "message"               => $email['message'],
                    "headers"               => $email['headers'],
                    "files"                 => $email['files'],
                ];

                $wpdb->insert($simForms->formEmailTable, $data, $formBuilder->formEmailTableFormats);
            }
        }
        
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