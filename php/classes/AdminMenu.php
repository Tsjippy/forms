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

    /**
     * Add the settings page to the admin menu
     *
     * @param \DOMElement $parent The parent menu slug
     * 
     * @return bool True if the settings page was added, false otherwise
     */
    public function settings($parent)
    {
        return false;
    }

    /**
     * Get the menu slug for the admin menu
     *
     * @return string The menu slug for the admin menu
     */
    public function emails($parent)
    {
        return false;
    }

    /**
     * Get the menu slug for the admin menu
     *
     * @return string The menu slug for the admin menu
     */
    public function data($parent = '')
    {
        $forms    = new SaveFormSettings();
        $forms->getForms();

        if (empty($forms->forms)) {
            return false;
        }

        // sort the forms on name
        usort($forms->forms, function ($a, $b) {
            return strcasecmp($a['formData']->name, $b['formData']->name);
        });

        $table  = addElement('table', $parent, ['class' => 'tsjippy table formoverview']);
        $thead  = addElement('thead', $table);
        $tr     = addElement('tr', $thead);

        foreach (['Post ID', 'Name', 'Url', 'Actions'] as $th) {
            $th     = addElement('th', $tr, [], $th);
        }

        $tbody  = addElement('tbody', $table);

        foreach ($forms->forms as $form) {
            if($form['formData']->post->post_status != 'publish'){
                continue;
            }

            $tr     = addElement('tr', $tbody);
            addElement('td', $tr, [], $form['formData']->postId);
            addElement('td', $tr, [], $form['formData']->name ?? 'Not Set');
            $td     = addElement('td', $tr, []);

            $formUrl  = get_permalink($form['formData']->postId) . '#tsjippy-form-' . $form['formData']->blockId;
            addElement('a', $td, ['href' => $formUrl, 'target' => '_blank'], 'Link');

            $td     = addElement('td', $tr, []);
            $formEl = addElement('form', $td, ['method' => 'post', 'style' => 'display: inline-block; margin-right:10px;']);
            addElement('input', $formEl, ['type' => 'hidden', 'name' => 'post-id', 'value' => $form['formData']->postId]);
            addElement('input', $formEl, ['type' => 'hidden', 'name' => 'block-id', 'value' => $form['formData']->blockId]);
            addElement('button', $formEl, ['class' => 'small', 'name' => 'action', 'value' => 'export'], 'Export');

            $formEl = addElement('form', $td, ['method' => 'post', 'style' => 'display: inline-block;']);
            addElement('input', $formEl, ['type' => 'hidden', 'name' => 'post-id', 'value' => $form['formData']->postId]);
            addElement('input', $formEl, ['type' => 'hidden', 'name' => 'block-id', 'value' => $form['formData']->blockId]);
            addElement('button', $formEl, ['class' => 'small', 'name' => 'action', 'value' => 'delete'], 'Delete');
        }

        return true;
    }

    /**
     * Get the menu slug for the admin menu
     *
     * @return string The menu slug for the admin menu
     */
    public function functions($parent)
    {
        ob_start();
        ?>
        <h4>
            Form import
        </h4>
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

    /**
     * Handle post actions for the admin menu
     *
     * @param array $request The request data
     *
     * @return string The response to the post action
     */
    public function postActions($request)
    {
        if (isset($request['import-form'])) {
            $formBuilder    = new FormExport();

            $path   = wp_normalize_path($_FILES['formfile']['tmp_name'] ?? '');

            return $formBuilder->importForm($path);
        }

        if ($request['action'] ?? '' === 'delete') {
            $forms    = new SaveFormSettings();

            return $forms->deleteForm($request['post-id'], $request['block-id']);
        }

        // phpcs:ignore
        if (isset($_GET['deleteall'])) {
            $forms    = new SaveFormSettings();

            global $wpdb;

            $emptyForms    = TSJIPPY\getFromDb(
                "empty_forms",
                "forms",
                "SELECT * FROM %i WHERE `version` = 1 and `button_text` IS NULL",
                "{$wpdb->prefix}tsjippy_forms"
            );

            foreach ($emptyForms as $form) {
                $forms->deleteForm($form->postId, $form->blockId);
            }

            $count  = count($emptyForms);

            return "<div class='success'>Succesfully deleted $count empty forms</div>";
        }

        return '';
    }
}
