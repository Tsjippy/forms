<?php
namespace TSJIPPY\FORMS;
use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

class AfterUpdate extends TSJIPPY\AfterPluginUpdate {

    public function afterPluginUpdate($oldVersion){
        global $wpdb;

        TSJIPPY\printArray('Running update actions');

        if(version_compare('11.0.6', $oldVersion)){
            /**
             * Rename tables to tsjippy_
             */
            $wpdb->query(
                "ALTER TABLE `{$wpdb->prefix}tsjippy_forms`
                RENAME COLUMN `name` to `slug`,
                RENAME COLUMN `formname` to `name`;"
            );

            $wpdb->query(
                "ALTER TABLE `{$wpdb->prefix}tsjippy_form_elements`
                RENAME COLUMN `functionname` to `function_name`,
                RENAME COLUMN `foldername` to `folder_name`,
                RENAME COLUMN `name` to `slu`,
                RENAME COLUMN `nicename` to `name`,
                RENAME COLUMN `editimage` to `edit_image`,
                RENAME COLUMN `valuelist` to `value_list`;"
            );

            $wpdb->query(
                "ALTER TABLE `{$wpdb->prefix}tsjippy_form_reminders`
                RENAME COLUMN `reminder_startdate` to `reminder_start_date`;"
            );

            $wpdb->query(
                "ALTER TABLE `{$wpdb->prefix}tsjippy_form_shortcode_column_settings`
                RENAME COLUMN `name` to `slug`,
                RENAME COLUMN `nice_name` to `name`;"
            );

            $wpdb->query(
                "ALTER TABLE `{$wpdb->prefix}tsjippy_form_shortcode_column_settings`
                RENAME COLUMN `timecreated` to `time_created`,
                RENAME COLUMN `timelastedited` to `time_last_edited`,
                RENAME COLUMN `userid` to `user_id`;"
            );
        }
    }
}
