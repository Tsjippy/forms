<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use TSJIPPY\ADMIN;

use function TSJIPPY\addElement;
use function TSJIPPY\addRawHtml;

if (! defined('ABSPATH')) {
    exit;
}

class AdminMenu extends ADMIN\SubAdminMenu
{

    /**
     * AdminMenu constructor.
     *
     * @param array $settings The settings for the plugin
     * @param string $name The name of the plugin
     */
    public function __construct($settings, $name)
    {
        parent::__construct($settings, $name);
    }

    public function settings($parent)
    {
        return false;
    }

    public function emails($parent)
    {
        return false;
    }

    public function data($parent = '')
    {
        $forms    = new SaveFormSettings();
        $forms->getForms();

        if(empty($forms->forms)){
            return false;
        }

        // sort the forms on name
        usort($forms->forms, function ($a, $b) {
            return strcasecmp($a->slug, $b->slug);
        });

        $table  = addElement('table', $parent, ['class' => 'tsjippy table formoverview']);
        $thead  = addElement('thead', $table);
        $tr     = addElement('tr', $thead);

        foreach (['Id', 'Name', 'Url', 'Actions'] as $th) {
            $th     = addElement('th', $tr, [], $th);
        }

        $tbody  = addElement('tbody', $table);

        foreach ($forms->forms as $form) {
            $formSlug    = $form->slug;
            $formUrl    = $form->url;
            if (empty($formUrl)) {
                $formUrl    .= "Not set";
            } else {
                $formUrl    .= "<a href='$formUrl' target='_blank'>Link</a>";
            }

            $tr     = addElement('tr', $tbody);
            addElement('td', $tr, [], $form->id);
            addElement('td', $tr, [], $formSlug);
            addElement('td', $tr, [], $formUrl);


            $td     = addElement('td', $tr, [], $formUrl);
            $formEl   = addElement('form', $td, ['method' => 'post', 'style' => 'display: inline-block; margin-right:10px;']);
            addElement('button', $formEl, ['class' => 'small', 'name' => 'export', 'value' => $form->id], 'Export');

            $formEl   = addElement('form', $td, ['method' => 'post', 'style' => 'display: inline-block;']);
            addElement('button', $formEl, ['class' => 'small', 'name' => 'delete', 'value' => $form->id], 'Delete');
        }

        return true;
    }

    public function functions($parent)
    {
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

    public function postActions()
    {
        // phpcs:ignore
        if (isset($_POST['import-form'])) {
            $formBuilder    = new FormExport();
            return $formBuilder->importForm(TSJIPPY\sanitize($_FILES['formfile']['tmp_name']));
        }

        // phpcs:ignore
        if (is_numeric($_POST['export'] ?? '')) {
            $forms    = new FormExport();
            $forms->exportForm(TSJIPPY\sanitize($_POST['export']));

            return;
        }

        // phpcs:ignore
        if (is_numeric($_POST['delete'] ?? '')) {
            $forms    = new SaveFormSettings();

            return $forms->deleteForm(TSJIPPY\sanitize($_POST['delete']));
        }

        // phpcs:ignore
        if (isset($_GET['deleteall'])) {
            $forms    = new SaveFormSettings();

            global $wpdb;

            $emptyForms    = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM %i WHERE `version` = 1 and `button_text` IS NULL",
                    "{$wpdb->prefix}tsjippy_forms"
                )
            );

            foreach ($emptyForms as $form) {
                $forms->deleteForm($form->id);
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
    public function postSettingsSave()
    {
        TSJIPPY\scheduleTask('anniversary-check', 'daily');
        TSJIPPY\scheduleTask('remove-old-schedules', 'daily');
        TSJIPPY\scheduleTask('add-repeated-events', 'yearly');

        $freq   = SETTINGS['freq'] ?? false;

        if ($freq) {
            TSJIPPY\scheduleTask('remove-old-events', $freq);
        }
    }
}
