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
            'render_callback' => function ( $attributes ) {
                return showFormSelector( $attributes );
            },
            'supports'        => array(
                'autoRegister' => true,
            ),
        )
    );

    register_block_type(
        'tsjippy-forms/form-builder',
        array(
            'title'           => __( 'Insert A Form', 'tsjippy' ),
            'attributes'      => array(
                'form_name'   => array(
                    'label'   => __( 'Form Name', 'tsjippy' ),
                    'type'    => 'string',
                    'default' => '',
                )
            ),
            'render_callback' => function ( $attributes ) {
                if(empty($attributes['form_name'])){
                    return "<p>Please enter a formname</p>";
                }

                $formName   = $attributes['form_name'];

                $formSlug   = checkFormExistence($formName, $found);

                $attributes['slug'] = $formSlug;

                if($found){
                    $forms  = new DisplayForm( $attributes );
                }else{
                    $forms  = new FormBuilderForm( $attributes );
                }
                return $forms->showForm();
            },
            'supports'        => array(
                'autoRegister' => true,
            ),
        )
    );

    register_block_type(
        'tsjippy-forms/forms-results',
        array(
            'title'           => __( 'Form Results', 'tsjippy' ),
            'attributes'      => [
                'formId' => [
                    'type' => 'string'
                ],
                'onlyOwn'  => [
                    'label'   => __( 'Show The Results of the Current User Only', 'tsjippy' ),
                    'type'    => 'boolean',
                    'default' => true,
                ],
                'archived'  => [
                    'label'   => __( 'Show Archived Results', 'tsjippy' ),
                    'type'  => 'boolean',
                    'default' => false,
                ],
                'tableId'  => [
                    'type'    => 'integer',
                    'default' => -1,
                ],
            ],
            'render_callback' => __NAMESPACE__ . '\showFormResults',
            'supports'        => array(
                'autoRegister' => true,
            ),
        )
    );

    register_block_type(
        'tsjippy-forms/missing-form-inputs',
        array(
            'title'           => __( 'Form Results', 'tsjippy' ),
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
        )
    );
}

add_action('enqueue_block_assets', __NAMESPACE__ . '\loadAssets');
function loadAssets()
{
    TSJIPPY\enqueueScripts();

    TSJIPPY\FILEUPLOAD\registerUploadScripts();

    registerScripts();

    wp_enqueue_script('tsjippy_formbuilderjs');

    wp_enqueue_script('tsjippy_forms_table_script');
}
