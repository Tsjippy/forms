<?php
namespace SIM\FORMS;
use SIM;

add_action('sim_forms_module_update', __NAMESPACE__.'\moduleUpdate');
function moduleUpdate($oldVersion){
    global $wpdb;

    require_once ABSPATH . 'wp-admin/install-helper.php';
    
    SIM\printArray($oldVersion);

    $simForms = new SimForms();

    $simForms->createDbTables();

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
            $conditions = $element->conditions;

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

    if($oldVersion < '8.7.8'){
        maybe_add_column("{$wpdb->prefix}sim_form_shortcode_column_settings", 'priority', "ALTER TABLE {$wpdb->prefix}sim_form_shortcode_column_settings ADD COLUMN `priority` int");
    }

    if($oldVersion < '8.8.1'){
        $simForms->getForms();
        foreach($simForms->forms as $formData){

            $formData->reminder_conditions  = maybe_unserialize($formData->reminder_conditions);
            $formData->reminder_conditions  = SIM\cleanUpNestedArray( $formData->reminder_conditions );
            
            $data = array_filter([
                'frequency'		        => $formData->reminder_frequency,
                'period'		        => $formData->reminder_period,
                'window_start'	        => '',
                'window_end'	        => '',
                'reminder_amount'		=> $formData->reminder_amount,
                'reminder_startdate'	=> $formData->reminder_startdate,
                'conditions'	        => empty($formData->reminder_conditions) ? null : maybe_serialize($formData->reminder_conditions),
            ]);

            if(!empty($data)){
                $data['form_id']            = $formData->id;
                $data['reminder_period']    = 'weeks';
                $wpdb->insert($simForms->formReminderTable, $data);
            }
        }
    }

    if($oldVersion < '8.8.6'){
        foreach(['form_reset', 'reminder_frequency', 'reminder_period', 'reminder_conditions', 'reminder_amount', 'reminder_startdate'] as $columnName){
            maybe_drop_column( $simForms->tableName, $columnName, "ALTER TABLE $simForms->tableName DROP COLUMN $columnName");
        }
    }

    if($oldVersion < '8.9.0'){
        $wpdb->query("UPDATE `{$wpdb->prefix}sim_form_shortcode_column_settings` SET `name`='submitteruserid' WHERE `element_id` = -2");
    }

    if($oldVersion < '8.9.3'){
        maybe_add_column("{$wpdb->prefix}sim_form_shortcode_column_settings", 'copy', "ALTER TABLE {$wpdb->prefix}sim_form_shortcode_column_settings ADD COLUMN `copy` bool");
    }

    if($oldVersion < '8.9.7'){
        maybe_add_column($simForms->submissionTableName, 'submitter_id', "ALTER TABLE {$simForms->submissionTableName} ADD COLUMN `submitter_id` int");

        $splitters    = [];
        $results     = $wpdb->get_results("SELECT id, split FROM $simForms->tableName WHERE split IS NOT NULL AND split <> ''");
        foreach($results as $result){
            $splitters[$result->id] = [];

            $elementIds    = maybe_unserialize($result->split);
            foreach($elementIds as $elementId){
                $splitters[$result->id][]  = explode('[', $wpdb->get_var("SELECT name FROM $simForms->elTableName WHERE id = $elementId"))[0];
            }
        }

        $results        = $wpdb->get_results("SELECT * FROM $simForms->submissionTableName");
        $elsChangend    = [];
        foreach($results as &$result){
            // change archivedsubs
            $archivedsubs    = maybe_unserialize(maybe_unserialize($result->archivedsubs));
            if(!empty($archivedsubs)){
                foreach($archivedsubs as $i => $archivedsub){
                    $wpdb->insert(
                        $simForms->submissionValuesTableName,
                        array(
                            'submission_id'	=> $result->id,
                            'key'			=> 'archived_indexes',
                            'value'			=> $archivedsub
                        ),
                        array(
                            '%d',
                            '%s',
                            '%s'
                        )
                    );
                }
            }

            $formresults    = maybe_unserialize(maybe_unserialize($result->formresults));
            if(empty($formresults)){
                continue;
            }

            $userId         = $result->userid;
            if(isset($formresults['userid'])){
                $userId = $formresults['userid'];
                unset($formresults['userid']);
            }elseif(isset($formresults['user-id'])){
                $userId = $formresults['user-id'];
                unset($formresults['user-id']);
            }elseif(isset($formresults['user_id'])){
                $userId = $formresults['user_id'];
                unset($formresults['user_id']);
            }

            $result->submitter_id   = $result->userid;

            $result->userid         = $userId;

            //Update the submission
			$wpdb->update(
                $simForms->submissionTableName,
				[
                    'submitter_id'  => $result->userid,
                    'userid'        => $userId
                ],
				array(
					'id'		=> $result->id
				),
                [
                    '%d',
                    '%d'
                ]
			);

            /**
             * Insert all into the new submission values table
             */

            // unset unneeded values
            unset($formresults['submissiontime']);
            unset($formresults['edittime']);
            unset($formresults['formurl']);
            unset($formresults['id']);
            unset($formresults['_wpnonce']);
            unset($formresults['booking-startdate']);
            unset($formresults['booking-enddate']);
            unset($formresults['booking-room']);
            unset($formresults['user-id']);
            unset($formresults['formid']);

            foreach($formresults as $key => $value){
                if(empty($value) || str_contains($key, '[') || str_contains($key, ']')){
                    continue;
                }

                // the current form has splitters and the current key is one of them
                if(
                    in_array($result->form_id, array_keys($splitters)) &&
                    in_array($key, $splitters[$result->form_id])
                ){
                    // Split the data and insert each entry
                    foreach($value as $index => $subValues){
                        if(empty($subValues)){
                            continue;
                        }

                        // Subvalues is an array itself
                        if(is_array($subValues)){
                            foreach($subValues as $subKey => $subValue){
                                if(empty($subValue)){
                                    continue;
                                }

                                // insert the value
                                $wpdb->insert(
                                    $simForms->submissionValuesTableName,
                                    [
                                        'submission_id' => $result->id,
                                        'sub_id'        => $index,
                                        'key'           => $subKey,
                                        'value'         => maybe_serialize($subValue)
                                    ],
                                    [
                                        '%d',
                                        '%d',
                                        '%s',
                                        '%s'
                                    ]
                                );
                            }
                            continue;
                        }

                        // single value for the split entry
                        // insert the value
                        $wpdb->insert(
                            $simForms->submissionValuesTableName,
                            [
                                'submission_id' => $result->id,
                                'sub_id'        => $index,
                                'key'           => $key,
                                'value'         => maybe_serialize($subValues)
                            ],
                            [
                                '%d',
                                '%d',
                                '%s',
                                '%s'
                            ]
                        );
                    }

                    // skip to the next entry
                    continue;
                }

                $oldKey    = $key;

                // make sure the key is valid
                // Make sure we only are working on the name
                $key	= end(explode('\\', $key));

                // Replace spaces with _
                $key	= str_replace(" ", "_", $key);

                // Make lowercase
                $key	= strtolower($key);

                // Keep only valid chars
                $key = preg_replace('/[^a-zA-Z0-9_\[\]]/', '_', $key);

                // Remove ending _
                $key	= trim($key, " \n\r\t\v\0_");

                // Make sure the first char is a letter or _
                $key[0] = preg_replace('/[^a-zA-Z_]/', '_', $key[0]);    
                
                if($oldKey !== $key && !in_array($key, $elsChangend)){
                    // Update form element name
                    $wpdb->query("UPDATE `$simForms->elTableName` SET `name`='$key' WHERE `name`='$oldKey'");

                    $elsChangend[$oldKey] = $key;
                }

                $value  = maybe_serialize($value);

                // insert the value
                $wpdb->insert(
                    $simForms->submissionValuesTableName,
                    [
                        'submission_id' => $result->id,
                        'key'           => $key,
                        'value'         => $value
                    ],
                    [
                        '%d',
                        '%s',
                        '%s'
                    ]
                );
            }
        }
        SIM\printArray($elsChangend);

        // rename user id input elements to userid
        $wpdb->query("UPDATE `$simForms->elTableName` SET `name`='userid' WHERE `name`='user-id' OR `name`='user_id'");

        // Remove empty forms
        $wpdb->query("DELETE FROM `$simForms->tableName` WHERE not exists
            (
            select 1
            from
                $simForms->elTableName
            where
                $simForms->elTableName.form_id = $simForms->tableName.id
            )"
        );

        // Insert userid element if needed
        $formIds = $wpdb->get_col("select distinct id
            from
            $simForms->tableName as forms
            where save_in_meta <> 1 AND not exists
            (
            select 1
            from
                $simForms->elTableName as elements
            where
                elements.form_id = forms.id and
                elements.name = 'userid'
            )"
        );

        foreach($formIds as $formId){

            // Insert the userid element
            $wpdb->insert(
                $simForms->elTableName,
                array(
                    'form_id'				=> $formId,
                    'type' 					=> 'number',
                    'name'					=> 'userid',
                    'default_value' 		=> 'user_id',
                    'hidden'				=> true,
                    'priority'				=> 1
                )
            );
        }

        //remove dynamic js files
        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        WP_Filesystem();
        global $wp_filesystem;

        $path	= MODULE_PATH."/js/dynamic";

        $wp_filesystem->delete( $path, true );

        foreach(['archivedsubs', 'formresults'] as $columnName){
            maybe_drop_column( $simForms->submissionTableName, $columnName, "ALTER TABLE $simForms->submissionTableName DROP COLUMN $columnName");
        }
    }
}

add_action('init', function(){
    //moduleUpdate('8.9.6');
});