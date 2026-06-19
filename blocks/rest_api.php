<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

add_action('rest_api_init', __NAMESPACE__ . '\restApiInit');
function restApiInit()
{
    // add element to form
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/form-selector',
        array(
            'methods'             => 'GET',
            'callback'            => __NAMESPACE__ . '\showFormSelector',
            'permission_callback' => '__return_true',                            // Allow public access
        )
    );

    // form builder
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/form_builder',
        array(
            'methods'             => 'POST,GET',
            'callback'            => __NAMESPACE__ . '\showFormBuilder',
            'permission_callback' => '__return_true',                        // Allow public access to be able access public available forms
        )
    );

    // Get all forms
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_forms',
        array(
            'methods'             => 'POST,GET',
            'callback'            => __NAMESPACE__ . '\getAllForms',
            'permission_callback' => '__return_true',                // Allow public access to be able access public available forms
        )
    );

    // Show form results
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/show_form_results',
        array(
            'methods'             => 'POST,GET',
            'callback'            => __NAMESPACE__ . '\showFormResults',
            'permission_callback' => '__return_true',            // Allow public access to be able access public available forms results, the function itself will check if the form is public or not and if the user has access to the results or not
        )
    );

    // Add new form table
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/missing_form_fields',
        array(
            'methods'             => 'POST,GET',
            'callback'            => function ($attributes) {
                if ($attributes instanceof \WP_REST_Request) {
                    $attributes    = $_REQUEST;
                }
                $result    = missingFormFields($attributes);
                if (empty($result)) {
                    return "No {$attributes['type']} fields found";
                }
                return $result;
            },
            'permission_callback' => function ($rest) {
                return current_user_can('read');        // Allow access to logged in users, the function itself will check if the user has access to the form and the fields or not
            },
        )
    );
}

/**
 * Show the form builder block
 *
 * @param \WP_REST_Request|array $attributes    The parameters for the block, either as an array or as a WP_REST_Request object
 */
function showFormBuilder($attributes)
{
    $isRest    = false;
    if ($attributes instanceof \WP_REST_Request) {
        $isRest    = true;
        if (!empty($_REQUEST['formname'])) {
            $attributes = ['formname' => TSJIPPY\sanitize($_REQUEST['formname'])];
        } elseif (!empty($_REQUEST['slug'])) {
            $attributes = ['slug' => TSJIPPY\sanitize($_REQUEST['slug'])];
        } elseif (!empty($_REQUEST['form-id'])) {
            $attributes = ['form-id' => (int) $_REQUEST['form-id']];
        } else {
            return false;
        }
    }

    if(!empty($_REQUEST['formbuilder'])){
        $forms = new FormBuilderForm($attributes);
    }else{
        $forms = new DisplayForm($attributes);
    }

    $html    = $forms->determineForm();
    if (is_wp_error($html)) {
        $html = $html->get_error_message();
    }

    if (!$isRest) {
        return [
            'html'    => $html,
        ];
    }

    do_action('wp_enqueue_scripts');

    wp_enqueue_style('tsjippy_forms_style');
    wp_enqueue_style('tsjippy_formtable_style');
    wp_enqueue_script('tsjippy_formbuilderjs');

    wp_enqueue_editor();

    \_WP_Editors::enqueue_scripts();
    wp_enqueue_editor();
    ob_start();
    \_WP_Editors::editor_js();
    wp_print_scripts(["tsjippy_formbuilderjs"]);
    print_footer_scripts();
    $js    = ob_get_clean();

    do_action('wp_enqueue_style');
    ob_start();
    wp_print_styles();
    $css    = ob_get_clean();

    return [
        'html'    => $html,
        'js'    => $js,
        'css'    => $css
    ];
}

/**
 * Show form results
 * @param \WP_REST_Request|array $attributes    The parameters for the block, either as an array or as a WP_REST_Request object
 */
function showFormResults($attributes)
{

    if ($attributes instanceof \WP_REST_Request) {
        if (!empty($_REQUEST['form-id'])) {
            $attributes = [
                'form-id'         => TSJIPPY\sanitize($_REQUEST['form-id']),
                'shortcode-id'    => TSJIPPY\sanitize($_REQUEST['shortcode-id'])
            ];

            if (isset($_REQUEST['archived'])) {
                $attributes['archived'] = TSJIPPY\sanitize($_REQUEST['archived']);
            }

            if (isset($_REQUEST['only-own'])) {
                $attributes['onlyOwn']  = TSJIPPY\sanitize($_REQUEST['only-own']);
            }

            if (isset($_REQUEST['all'])) {
                $attributes['all']      = TSJIPPY\sanitize($_REQUEST['all']);
            }
        } else {
            return false;
        }
    } elseif (!isset($attributes['form-id'])) {
        return false;
    }

    if (isset($attributes['tableid'])) {
        $attributes['id']    = $attributes['tableid'];
    }

    if (isset($attributes['tableid'])) {
        $attributes['id']    = $attributes['tableid'];
    }

    $object = new DisplayFormResults($attributes); 

    $html   = $object->showFormresultsTable(all: isset($_POST['export-xls']) || isset($_POST['export-pdf']));

    //now we have rendered all the content we can export the excel if requested
    // phpcs:ignore
    if (isset($_POST['export-xls'])) {
        $object->exportExcel();
    }

    //now we have rendered all the content we can export the pdf if requested
    // phpcs:ignore
    if (isset($_POST['export-pdf'])) {
        // phpcs:ignore
        echo $object->exportPdf();
    }

    if (is_wp_error($html)) {
        $html = $html->get_error_message();
    }

    return $html;
}

function getAllForms()
{
    $forms    = new Forms();
    $forms->getForms();

    return $forms->forms;
}
