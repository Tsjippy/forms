<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;
use WP_Error;

if (! defined('ABSPATH')) {
    exit;
}

// Allow rest api urls for non-logged in users
add_filter('tsjippy-allowed-rest-api-urls', __NAMESPACE__ . '\addRestUrls');
/**
 * Add REST API URLs for forms
 *
 * @param array $urls The array of allowed REST API URLs
 * 
 * @return array The updated array of allowed REST API URLs
 */
function addRestUrls($urls)
{
    $urls[] = TSJIPPY\RESTAPIPREFIX . '/forms/save_form_input';

    return $urls;
}

/**
 * Check permissions for form editing
 *
 * @return bool Whether the user has permission to edit forms
 */
function checkPermissions()
{
    $forms    = new Forms();

    return $forms->editRights;
}

add_action('rest_api_init', __NAMESPACE__ . '\restApiInitForms');
/**
 * Register REST API routes for forms
 */
function restApiInitForms()
{
    // load form results table
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/load_form_results',
        array(
            'methods'             => 'POST',
            'callback'            =>     __NAMESPACE__ . '\loadFormResults',
            'permission_callback' => '__return_true',
            'args'                => array(
                'shortcode-id'    => array(
                    'required'    => true,
                    'validate_callback' => function ($id) {
                        return is_numeric($id);
                    }
                )
            )
        )
    );

    // request form element
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/request_form_element',
        array(
            'methods'             => 'POST',
            'callback'            => __NAMESPACE__ . '\requestFormElement',
            'permission_callback' => __NAMESPACE__ . '\checkPermissions',
            'args'                => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                ),
                'element-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($elementId) {
                        return is_numeric($elementId);
                    }
                )
            )
        )
    );

    // save_form_input
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_form_input',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     function () {
                // phpcs:ignore
                $settings       = TSJIPPY\sanitize($_POST);

                $formBuilder    = new SubmitForm(blockId:$settings['block-id'], postId:$settings['post-id'], userId:get_current_user_id());

                // The user id for whom the form is submitted
                $userId = 0;
                if (isset($settings['user-id'])) {
                    $userId    = (int) $settings['user-id'];
                }
                
                return $formBuilder->formSubmit($userId, $settings);
            },
            'permission_callback'     => '__return_true',
            'args'                    => array(
                'block-id'        => array(
                    'required'    => true
                )
            )
        )
    );
}

/**
 * Load the results of a form by its shortcode ID
 *
 * @return string    The HTML of the form results table, or an error message if no results are found
 */
function loadFormResults()
{
    $displayFormResults = new DisplayFormResults(['shortcode-id' => (int) $_POST['shortcode-id']]);

    return $displayFormResults->showFormresultsTable();
}
