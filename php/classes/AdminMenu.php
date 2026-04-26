<?php
namespace TSJIPPY\FORMS;
use TSJIPPY;
use TSJIPPY\ADMIN;

use function TSJIPPY\addElement;
use function TSJIPPY\addRawHtml;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AdminMenu extends ADMIN\SubAdminMenu{

    public function __construct($settings, $name){
        parent::__construct($settings, $name);
    }

    public function settings($parent){
        return false;
    }

    public function emails($parent){
        return false;
    }

    public function data($parent=''){
        $simForms	= new SaveFormSettings();
        $simForms->getForms();

        // sort the forms on name
        usort($simForms->forms, function($a, $b){
            return strcasecmp($a->name, $b->name);
        });

        $table  = addElement('table', $parent, ['class' => 'sim table formoverview']);
        $thead  = addElement('thead', $table);
        $tr     = addElement('tr', $thead);

        foreach(['Id', 'Name', 'Url', 'Actions'] as $th){
            $th     = addElement('th', $tr, [], $th);
        }

        $tbody  = addElement('tbody', $table);

        foreach($simForms->forms as $form){
            $formName	= $form->form_name;
            $formUrl	= $form->form_url;
            if(empty($formUrl)){
                $formUrl	.= "Not set";
            }else{
                $formUrl	.= "<a href='$formUrl'>Link</a>";
            }

            $formName	= str_replace('_', ' ', ucfirst($formName));

            $tr     = addElement('tr', $tbody);
            addElement('td', $tr, [], $form->id);
            addElement('td', $tr, [], $formName);
            addElement('td', $tr, [], $formUrl);

            
            $td     = addElement('td', $tr, [], $formUrl);
            $form   = addElement('form', $td, ['method' => 'post', 'style' => 'display: inline-block; margin-right:10px;']);
            addElement('button', $form, ['class' => 'small', 'name' => 'export', 'value' => $form->id], 'Export');

            $form   = addElement('form', $td, ['method' => 'post', 'style' => 'display: inline-block;']);
            addElement('button', $form, ['class' => 'small', 'name' => 'delete', 'value' => $form->id], 'Delete');

        }

        return true;
    }

    public function functions($parent){
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

        addRawHtml(ob_get_clean(), $parent);

        return true;
    }

    public function postActions(){
        if(isset($_POST['import-form'])){
            $formBuilder	= new FormExport();
            return $formBuilder->importForm($_FILES['formfile']['tmp_name']);
        }

        if(isset($_POST['export']) && is_numeric($_POST['export'])){
            $simForms	= new FormExport();
            $simForms->exportForm($_POST['export']);

            return;
        }

        if(isset($_POST['delete']) && is_numeric($_POST['delete'])){
            $simForms	= new SaveFormSettings();
            
            return $simForms->deleteForm($_POST['delete']);
        }

        if(isset($_GET['deleteall'])){
            $simForms	= new SaveFormSettings();

            global $wpdb;

            $emptyForms	= $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tsjippy_forms WHERE `version` = 1 and `button_text` IS NULL");

            foreach($emptyForms as $form){
                $simForms->deleteForm($form->id);
            }

            $count  = count($emptyForms);

            return "<div class='success'>Succesfully deleted $count empty forms</div>";
        }

        return '';
    }

    /**
     * Schedules the tasks for this plugin
     *
    */
    public function postSettingsSave(){
        TSJIPPY\scheduleTask('anniversary_check_action', 'daily');
        TSJIPPY\scheduleTask('remove_old_schedules_action', 'daily');
        TSJIPPY\scheduleTask('add_repeated_events_action', 'yearly');

        $freq   = SETTINGS['freq'] ?? false;

        if($freq){
            TSJIPPY\scheduleTask('remove_old_events_action', $freq);
        }
    }
}