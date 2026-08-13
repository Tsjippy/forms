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
    // Get form e-mails
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_form_emails',
        array(
            'methods'             => 'POST',
            'callback'            => function($wpRestRequest){
                $forms  = new Forms();

                return $forms->getFormEmailSettings((int) $wpRestRequest->get_param('blockId'));
            },
            'permission_callback' => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'blockId'        => array(
                    'required'    => true
                ),

            )
        )
    );

    // save_form_emails
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_form_emails',
        array(
            'methods'                 => 'POST',
            'callback'                =>     __NAMESPACE__ . '\saveFormEmails',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'blockId'        => array(
                    'required'    => true
                ),
                'emails'        => array(
                    'required'    => true
                ),

            )
        )
    );

    // Get form reminders
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_form_reminders',
        array(
            'methods'             => 'POST',
            'callback'            => function($wpRestRequest){
                $forms  = new Forms();

                return $forms->getFormReminder((int) $wpRestRequest->get_param('blockId'));
            },
            'permission_callback' => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'blockId'        => array(
                    'required'    => true
                ),

            )
        )
    );

    // save form reminders
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_form_reminders',
        array(
            'methods'                 => 'POST',
            'callback'                => __NAMESPACE__ . '\saveFormReminders',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'blockId'        => array(
                    'required'    => true
                ),
                'emails'        => array(
                    'required'    => true
                ),

            )
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

    // Get dynamic form data
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_prefill',
        array(
            'methods'  => 'POST',
            'callback' => function ($wpRestRequest) {
                $forms  = new Forms();

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

    /**
     * Get Element Conditions
     */
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_element_conditions',
        array(
            'methods'                 => 'POST',
            'callback'                => function($wpRest){
                $postId   = TSJIPPY\sanitize($wpRest->get_param('postId') ?? '');
                
                $forms = new Forms(postId:$postId);
                return $forms->getBlockConditions();
            },
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
        )
    );

    // Save Element Conditions
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_element_conditions',
        array(
            'methods'                 => 'POST',
            'callback'                => function($wpRest){
                $forms = new Forms();

                $blockId    = TSJIPPY\sanitize($wpRest->get_param('blockId') ?? '');
                $conditions = TSJIPPY\sanitize($wpRest->get_param('conditions') ?? []);

                /**
                 * Makes sure we do not store unnecesary data
                 */
                foreach($conditions as &$condition){
                    foreach($condition['rules'] as &$rule){
                        // conditional-field-2
                        if(
                            !isset(['== value' => 1, '!= value' => 1, '> value' => 1, '< value' => 1, '+' => 1, '-' => 1][$rule['equation']]) && // Not one of these 
                            !empty($rule['conditional-field-2'])    // should be empty but is not
                        ){
                            unset($rule['conditional-field-2']);
                        }

                        // equation-2
                        if(
                            !isset(['+' => 1, '-' => 1,][$rule['equation']]) && // Not one of these 
                            !empty($rule['equation-2'])    // should be empty but is not
                        ){
                            unset($rule['equation-2']);
                        }

                        // conditional-value
                        if(
                            !isset(['==' => 1, '!=' => 1, '>' => 1, '<' => 1, '+' => 1, '-' => 1,][$rule['equation']]) && // Not one of these 
                            !empty($rule['conditional-value'])    // should be empty but is not
                        ){
                            unset($rule['conditional-value']);
                        }

                    }
                    unset($rule);
                }
                unset($condition);

                $postId     = $wpRest->get_param('postId');

                // Save conditions
                $newConditions  = $forms->saveBlockConditions($conditions, $blockId, $postId);

                // Build new dynamic js
                processFormBlocks($postId);

                return $newConditions;
            },
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
        )
    );
}

/**
 * Save the email settings for a form
 *
 * @param \WP_REST_Request $wpRestRequest The REST API request object
 * 
 * @return string    A success message indicating that the email settings were saved successfully
 */
function saveFormEmails($wpRestRequest)
{
    $saver    = new SaveFormSettings();

    $formEmails     = TSJIPPY\sanitize($wpRestRequest->get_param('emails') ?? []);

    $result         = $saver->saveFormEmails($formEmails, (int) $wpRestRequest->get_param('blockId'));

    if (is_wp_error($result)) {
        return $result;
    }

    return "Succesfully saved your form e-mail configuration";
}

/**
 * Save the email settings for a form
 *
 * @param \WP_REST_Request $wpRestRequest The REST API request object
 * 
 * @return string    A success message indicating that the email settings were saved successfully
 */
function saveFormReminders($wpRestRequest)
{
    $saver    = new SaveFormSettings();

    $reminder = TSJIPPY\sanitize($wpRestRequest->get_param('reminder') ?? []);

    $result   = $saver->updateFormReminder((int) $wpRestRequest->get_param('blockId'), $reminder);

    if (is_wp_error($result)) {
        return $result;
    }

    return "Succesfully saved the reminder";
}