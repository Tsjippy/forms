<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', __NAMESPACE__ . '\initBlocks');
function initBlocks()
{
    register_block_type(
        'tsjippy-forms/form-selector',
        array(
            'title'           => __( 'Forms Selector', 'tsjippy' ),
            'attributes'      => array(
                'hide_meta_forms'   => array(
                    'label'   => __( 'Hide forms that save to user meta', 'tsjippy' ),
                    'type'    => 'boolean',
                    'default' => false,
                )
            ),
            'render_callback' => __NAMESPACE__.'\showFormSelector',
            'supports'        => array(
                'autoRegister' => true,
            ),
            'icon'  => 'forms'
        )
    );

    register_block_type(
        'tsjippy-forms/form-builder',
        array(
            'title'           => __( 'Insert A Form', 'tsjippy' ),
            'attributes'      => array(
                'formname'   => array(
                    'label'   => __( 'Form Name', 'tsjippy' ),
                    'type'    => 'string',
                    'default' => '',
                )
            ),
            'render_callback' => __NAMESPACE__.'\showFormBuilder',
            'supports'        => array(
                'autoRegister' => true,
            ),
            'icon'  => 'forms'
        )
    );

    $forms  = new Forms();
    $forms->getForms();
    $formNames = [];

    foreach($forms->forms as $form){
        if(empty($form->name)){
            continue;
        }

        $formNames[]    = trim($form->name);
    }

    register_block_type(
        'tsjippy-forms/forms-results',
        array(
            'title'           => __( 'Form Results', 'tsjippy' ),
            'attributes'      => [
                'form-name' => [
                    'label'   => __( 'Form name', 'tsjippy' ),
                    'type'    => 'string',
                    'enum'    => $formNames
                ],
                'only-own'  => [
                    'label'   => __( 'Show The Results of the Current User Only', 'tsjippy' ),
                    'type'    => 'boolean',
                    'default' => true,
                ],
                'archived'  => [
                    'label'   => __( 'Show Archived Results', 'tsjippy' ),
                    'type'    => 'boolean',
                    'default' => false,
                ]
            ],
            'render_callback' => __NAMESPACE__ . '\formResults',
            'supports'         => array(
                'autoRegister' => true,
            ),
            'icon'  => 'table'
        )
    );

    register_block_type(
        'tsjippy-forms/missing-form-inputs',
        array(
            'title'           => __( 'Missing Form Entries', 'tsjippy' ),
            'attributes'      => [
                'type' => [
                    'label'   => __( 'Which type', 'tsjippy' ),
                    'type'    => 'string',
                    'enum'    => ['mandatory', 'recommended', 'all'],
                    'default' => 'all',
                ]
            ],
            'render_callback' => __NAMESPACE__ . '\missingFormFields',
            'supports'        => array(
                'autoRegister' => true,
            ),
            'icon'  => 'ellipsis'
        )
    );
}

add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\loadAssets');
add_action('enqueue_block_assets', __NAMESPACE__ . '\loadAssets');
function loadAssets()
{
    TSJIPPY\enqueueScripts();

    TSJIPPY\FILEUPLOAD\registerUploadScripts();

    registerScripts();

    wp_enqueue_script('tsjippy_formbuilderjs');

    wp_enqueue_script('tsjippy_forms_table_script');

    wp_enqueue_style('tsjippy_forms_style');
    wp_enqueue_style('tsjippy_formtable_style');
}

function showFormBuilder($attributes){
    if(empty($attributes['formname'])){
        if(!empty($attributes['slug'])){
            $attributes['formname'] = $attributes['slug'];
        }else{
            return "<p>Please enter a formname</p>";
        }
    }

    $formName   = $attributes['formname'];

    if(!empty($_POST['export-form'])){
        $forms   = new FormExport($attributes);

        $formId = (int) $_POST['export-form'];

        if(!TSJIPPY\verifyNonce('nonce', 'form-export-'.$formId)){
            return "<div class='error'>Invalid nonce</div>";
        }

        return $forms->exportForm($formId);
    }

    if(!empty($_POST['delete-form'])){
        $forms   = new Forms($attributes);

        $formId = (int) $_POST['export-form'];

        if(!TSJIPPY\verifyNonce('nonce', 'form-delete-'.$formId)){
            return "<div class='error'>Invalid nonce</div>";
        }
        
        return $forms->deleteForm($formId);
    }

        // If requesting for another user
    if(is_numeric($_REQUEST['user-id'] ?? '')){
        $attributes['user-id'] = $_REQUEST['user-id'];
    }

    $formSlug   = checkFormExistence($formName, $found);

    $attributes['slug'] = $formSlug;

    if($found && !isset($_REQUEST['formbuilder'])){
        $forms  = new DisplayForm( $attributes );
    }else{
        $forms  = new FormBuilderForm( $attributes );
    }
    return $forms->showForm();
}

/**
 * Displays form results based on the provided attributes
 *
 * @param   array   $atts    The shortcode attributes
 *
 * @return  string           The HTML for the form results
 */
function formResults($atts)
{
    $object                 = new DisplayFormResults($atts);
    $object->showArchived   = isset($_GET['archived']);
    $html                   = $object->showFormresultsTable(all: isset($_POST['export-xls']) || isset($_POST['export-pdf']));

    //now we have rendered all the content, we can export the excel if requested
    // phpcs:ignore
    if (isset($_POST['export-xls'])) {
        $object->exportExcel();
    }

    //now we have rendered all the content we can export the pdf if requested
    // phpcs:ignore
    if (isset($_POST['export-pdf'])) {
        $object->exportPdf();
    }

    if (is_wp_error($html)) {
        return "<div class='error'>" . $html->get_error_message() . "</div>";
    }

    return $html;
}
