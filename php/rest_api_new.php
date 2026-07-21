<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;
use WP_Error;

if (! defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', __NAMESPACE__ . '\restApiInitFormsNew');
/**
 * Register REST API routes for forms
 */
function restApiInitFormsNew()
{
    // Retrieve the form reminder form
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_form_reminder_form',
        array(
            'methods'             => 'POST',
            'callback'            => function(){
                $forms  = new FormBuilderForm();
                ob_start();
                $forms->formReminderForm();
                return ob_get_clean();
            },
            'permission_callback' => __NAMESPACE__ . '\checkPermissions'
        )
    );

    // Retrieve the form emails form
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_emails_form',
        array(
            'methods'             => 'POST',
            'callback'            => function(){
                $forms  = new FormBuilderForm();
                ob_start();
                $forms->formEmailsForm();
                return ob_get_clean();
            },
            'permission_callback' => __NAMESPACE__ . '\checkPermissions'
        )
    );

    // Get all roles 
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_roles',
        array(
            'methods'     => 'POST',
            'callback'     => function ($wpRestRequest) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
                
                $array  = [];
                foreach( get_editable_roles() as $key => $data){
                    $array[] = [
                        'value' => $key,
                        'label' => $data['name']
                    ];
                }

                return $array;
            },
            'permission_callback' => function(){
                return current_user_can('edit_users');
            }
        )
    );

    // Get all form actions 
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_form_actions',
        array(
            'methods'     => 'POST',
            'callback'     => function ($wpRestRequest) {
                /**
                 * Filters the forms actions
                 * 
                 * @param   array   $actions The form table actions
                 */
                return apply_filters('tsjippy-forms-actions', ['archive', 'delete']);
            },
            'permission_callback' => function(){
                return current_user_can('edit_users');
            }
        )
    );

    // Register a new form
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/register_form',
        array(
            'methods'  => 'POST',
            'callback' => function ($wpRestRequest) {
                $forms  = new Forms();
                $forms->insertForm($wpRestRequest->get_param('slug'));

                return $forms->formData->id;
            },
            'permission_callback' => function(){
                return current_user_can('edit_users');
            }
        )
    );

    // Register a new form
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_prefil',
        array(
            'methods'  => 'POST',
            'callback' => function ($wpRestRequest) {
                $forms  = new ElementHtmlBuilder();

                $forms->buildDefaultsArray();

                return [
                    'multi'  => $forms->defaultArrayValues,
                    'single' => $forms->defaultValues
                ];
            },
            'permission_callback' => function(){
                return current_user_can('edit_users');
            }
        )
    );

    // form conditions html
    /**
     * [
     *   [
     *       {
     *           "conditional-field": "abc123",
     *           "equation": "==",
     *           "conditional-value": "yes",
     *           "combinator": "and"
     *       }
     *   ]
     *]
     */
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_element_conditions',
        array(
            'methods'                 => 'POST',
            'callback'                => function($wpRest){
                $formBuilder = new FormBuilderForm();

                $elementId   = (int) $wpRest->get_param('elementId') ?? '';
                $elementId  = 1940;

                $formBuilder->getForm(182);

                $element    = $formBuilder->getElementById($elementId);

                if(!$element || empty($element->conditions)){
                    return [];
                }

                return [
                    "rules" => [
                        [
                            "conditional-field" => "abc123",
                            "equation" => "==",
                            "conditional-value" => "yes"
                        ]
                    ],
                    "actions" => []
                ];

                return $element->conditions;
            },
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
        )
    );

    // form conditions html
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_element_conditions',
        array(
            'methods'                 => 'POST',
            'callback'                => function($wpRest){
                $formBuilder = new FormBuilderForm();

                $elementId   = (int) $wpRest->get_param('elementId') ?? '';
                $elementId  = 1940;

                $formBuilder->getForm(182);

                $element    = $formBuilder->getElementById($elementId);

                if(!$element || empty($element->conditions)){
                    return [];
                }

                return $element->conditions;
            },
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
        )
    );
}