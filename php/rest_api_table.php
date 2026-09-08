<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;
use WP_Error;

if (! defined('ABSPATH')) {
    exit;
}

// Allow rest api urls for non-logged in users
add_filter('tsjippy-allowed-rest-api-urls', __NAMESPACE__ . '\addFormResultUrls');

/**
 * Add form result URLs to the list of allowed REST API URLs
 *
 * @param array $urls The list of allowed REST API URLs
 * @return array The updated list of allowed REST API URLs
 */
function addFormResultUrls($urls)
{
    $urls[] = TSJIPPY\RESTAPIPREFIX . '/forms/edit_value';
    $urls[] = TSJIPPY\RESTAPIPREFIX . '/forms/get_input_html';

    return $urls;
}

/**
 * Check if the current user has the permission needed for the requested action
 */
function submissionPermission(){
    $settings          = TSJIPPY\sanitize($_POST);

    // Check if table edit permissions
    $formsTable        = new DisplayFormResults($settings['shortcode-id']);
    if( $formsTable->tableEditPermissions){
        return true;
    }

    // Check if this is our own submission
    $submissions    = $formsTable->getSubmissions(submissionId:$settings['submission-id']);

    return ($submissions[0]->user_id ?? false) == get_current_user_id();
}

add_action('rest_api_init', __NAMESPACE__ . '\restApiInitTable');
/**
 * Initializes the REST API routes for form table actions
 */
function restApiInitTable()
{
    //save_table_prefs
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_table_prefs',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\saveTablePrefs',
            'permission_callback' => function ($request) {
                return current_user_can('read');        // Allow access to logged in users, tto be able to save theire column visibility preferences
            },
            'args'                => array(
                'block-id'         => array(
                    'required'    => true,
                    'validate_callback' => function ($blockId) {
                        return is_numeric($blockId);
                    }
                ),
                'column-name'     => array('required'    => true),
            )
        )
    );

    //delete_table_prefs
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/delete_table_prefs',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\deleteTablePrefs',
            'permission_callback' => function ($request) {
                return current_user_can('read');        // Allow access to logged in users, to be able to reset theire column visibility preferences
            },
            'args'                => array(
                'block-id'         => array(
                    'required'    => true,
                    'validate_callback' => function ($blockId) {
                        return is_numeric($blockId);
                    }
                ),
            )
        )
    );

    //save_column_settings
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_column_settings',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\saveColumnSettings',
            'permission_callback' => function () {
                $settings          = TSJIPPY\sanitize($_POST);

                $formsTable        = new DisplayFormResults($settings['shortcode-id']);
                return $formsTable->tableEditPermissions;
            },
            'args'                => array(
                'shortcode-id'    => array(
                    'required'    => true,
                    'validate_callback' => function ($shortcodeId) {
                        return is_numeric($shortcodeId);
                    }
                ),
                'column-settings' => array(
                    'required'    => true,
                ),
            )
        )
    );

    // save_table_prefs
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_table_settings',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\saveTableSettings',
            'permission_callback' => function () {
                $settings          = TSJIPPY\sanitize($_POST);
                $formsTable        = new DisplayFormResults($settings['shortcode-id']);
                return $formsTable->tableEditPermissions;
            },
            'args'                => array(
                'shortcode-id'    => array(
                    'required'    => true,
                    'validate_callback' => function ($shortcodeId) {
                        return is_numeric($shortcodeId);
                    }
                ),
                'table-settings'  => array(
                    'required'    => true,
                ),
            )
        )
    );

    //remove submission
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/remove_submission',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\removeSubmission',
            'permission_callback' => __NAMESPACE__ . '\submissionPermission',
            'args'                => array(
                'submission-id'   => array(
                    'required'    => true,
                    'validate_callback' => function ($submissionId) {
                        return is_numeric($submissionId);
                    }
                )
            )
        )
    );

    //archive submission
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/archive_submission',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\archiveSubmission',
            'permission_callback' => __NAMESPACE__ . '\submissionPermission',
            'args'                => array(
                'submission-id'   => array(
                    'required'    => true,
                    'validate_callback' => function ($submissionId) {
                        return is_numeric($submissionId);
                    }
                )
            )
        )
    );

    // edit value
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/edit_value',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\editValue',
            'permission_callback' => __NAMESPACE__ . '\submissionPermission',     // Allow public access, the function itself will check if the user has permissions to edit the value or not
            'args'                => array(
                'submission-id'   => array(
                    'required'    => true,
                    'validate_callback' => function ($submissionId) {
                        return is_numeric($submissionId);
                    }
                ),
                'block-id'      => array(
                    'required'    => true,
                    'validate_callback' => function ($submissionId) {
                        return is_numeric($submissionId);
                    }
                ),
                'new-value'       => array(
                    'required'    => true,
                ),
            )
        )
    );

    //get_input_html
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_input_html',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\getInputHtml',
            'permission_callback' => __NAMESPACE__ . '\submissionPermission',                      // Allow public access, the function itself will check if the user has permissions to view the input or not
            'args'                => array(
                'block-id'      => array(
                    'required'    => true,
                ),
                'submission-id'   => array(
                    'required'    => true,
                    'validate_callback' => function ($submissionId) {
                        return is_numeric($submissionId);
                    }
                ),
            )
        )
    );

    // get next or prev page
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/get_page',
        array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => __NAMESPACE__ . '\getPage',
            'permission_callback' => '__return_true',                        // Allow public access
            'args'                => array(
                'shortcode-id'         => array(
                    'required'    => true,
                    'validate_callback' => function ($shortcodeId) {
                        return is_numeric($shortcodeId);
                    }
                )
            )
        )
    );
}

/**
 * Retrieves the next or previous page of the form results table
 *
 * @return array The table html for the next or previous page
 */
function getPage()
{
    // phpcs:ignore
    $settings    = TSJIPPY\sanitize($_POST);

    $displayFormResults = new DisplayFormResults(shortcodeId: $settings['shortcode-id'], pageSize: $settings['pagesize'] ?? 50); 

    $displayFormResults->loadShortcodeData();

    $tables             = [];

    $types              = [$settings['type']];

    if ($settings['type'] == 'all' && $displayFormResults->tableSettings->split_table) {
        $types          = ['own', 'others'];
    }

    // phpcs:ignore
    if(!empty($_REQUEST['only-own'])){
        $displayFormResults->onlyOwn      = true;
    }

    // phpcs:ignore
    if(!empty($_REQUEST['archived'])){
        $displayFormResults->showArchived = true;
    }

    foreach ($types as $type) {
        $tableWrapper   = $displayFormResults->renderTable($type);

        $tables[$type]  = $tableWrapper->ownerDocument->saveHTML();
    }

    return $tables;
}

/**
 * Saves the user's table preferences for column visibility
 *
 * @param \WP_REST_Request $request The REST API request object
 * @return string A success message indicating that the column settings were updated
 */
function saveTablePrefs(\WP_REST_Request $request)
{
    $columnName                 = $request->get_param('column-name');

    $userId                     = get_current_user_id();
    $hiddenColumns              = (array)get_user_meta($userId, 'tsjippy_hidden_columns_' . $request->get_param('block-id'), true);

    $hiddenColumns[$columnName] = 'hidden';

    update_user_meta($userId, 'tsjippy_hidden_columns_' . $request['block-id'], $hiddenColumns);

    return 'Succesfully updated column settings';
}

/**
 * Deletes the user's table preferences for column visibility
 *
 * @param \WP_REST_Request $request The REST API request object
 * @return string A success message indicating that the column settings were reset
 */
function deleteTablePrefs(\WP_REST_Request $request)
{
    $userId        = get_current_user_id();
    delete_user_meta($userId, 'tsjippy_hidden_columns_' . $request->get_param('block-id'));

    return 'Succesfully reset column visibility';
}

/**
 * Saves the column settings for a specific shortcode
 *
 * @param \WP_REST_Request $wpRest
 * 
 * @return string|WP_Error A success message or a WP_Error object if an error occurred
 */
function saveColumnSettings($wpRest = [])
{
    $forms    = new SaveFormSettings();

    $params   = $wpRest->get_params();

    $settings = $params['column-settings'];

    $result = $forms->saveColumnSettings($settings, $params['shortcode-id']);

    if (is_wp_error($result)) {
        return $result;
    }

    return "Succesfully saved your column settings";
}

/**
 * Saves the table settings for a specific shortcode
 *
 * @return string|WP_Error A success message or a WP_Error object if an error occurred
 */
function saveTableSettings()
{
    $tableSettings     = TSJIPPY\sanitize($_POST['table-settings']);
    $shortcodeId       =  (int) $_POST['shortcode-id'];

    // Check invalid filter names
    if (isset($tableSettings->filter)) {
        foreach ($tableSettings->filter as $filter) {
            if (isset(['accept-charset' => 1, 'action' => 1, 'autocomplete' => 1, 'enctype' => 1, 'method' => 1, 'name' => 1, 'novalidate' => 1, 'rel' => 1, 'target' => 1][$filter['name']])) {
                return new WP_Error('forms', "Invalid filter name '{$filter['name']}', use a different one");
            }
        }
    }

    $tableSettings['view_right_roles']   = array_flip($tableSettings['view-right-roles'] ?? []);
    $tableSettings['edit_right_roles']   = array_flip($tableSettings['edit-right-roles'] ?? []);

    //update table settings
    $forms    = new SaveFormSettings();

    $result = $forms->insertOrUpdateData($forms->shortcodeTable, $tableSettings, ['id' => $shortcodeId]);

    if (is_wp_error($result)) {
        return $result;
    }

    return "Succesfully saved your table settings";
}

/**
 * Removes a submission from the form results table
 */
function removeSubmission()
{
    $settings     = TSJIPPY\sanitize($_POST);
    $formTable    = new EditFormResults($settings['shortcode-id']);

    $result        = $formTable->deleteSubmission($settings['submission-id']);

    if (is_wp_error($result)) {
        return $result;
    }

    do_action('tsjippy-forms-entry-removed', $formTable, $settings['submission-id']);

    return "Entry with id {$settings['submission-id']} succesfully removed";
}

/**
 * Archive or unarchive a subsubmission
 */
function archiveSubmission()
{
    $requestData                = TSJIPPY\sanitize($_POST);
    $formTable                  = new EditFormResults();
    $formTable->submissionId    = $requestData['submission-id'];
    $action                     = $requestData['action'];

    if ($action    == 'archive') {
        $archive = true;
    } else {
        $archive = false;
    }

    $message    = $formTable->archiveSubmission($archive, $requestData['subid'] ?? null);

    return $message;
}

/**
 * Retrieves the block html needed to be able to update a form result entry
 */
function getInputHtml()
{
    $requestData = TSJIPPY\sanitize($_POST);
    $formTable   = new DisplayFormResults();

    $formTable->parseSubmissions('', $requestData['submission-id']);

    // Get the form id from the submission and load the form
    $formTable->getForm($formTable->submission->post_id, $formTable->submission->block_id);

    $userId             = $formTable->submission->user_id;

    $formTable->userId  = $userId;

    $blockId          = $requestData['block-id'];

    $block            = $formTable->getBlockById($blockId);

    if (!$block) {
        return new \WP_Error('No block found', "No block found with id '$blockId'");
    }

    $value        = $formTable->getSubmissionValue($requestData['submission-id'], $blockId, $requestData['subid'] ?? null);

    // Get block html
    $html         = render_block($block);

    /**
     * Check if this block needs a datalist
     */
    $listBlockId    = $block['attrs']['list'] ?? '';

    if(!empty($listBlockId)){
        $listBlock  = $formTable->getBlockById($listBlockId);
        $html      .= render_block($listBlock);
    }

    return $html;
}

/**
 * Updates a value in the submission results table with a new value
 */
function editValue()
{
    $settings                = TSJIPPY\sanitize($_POST);
    $formTable               = new EditFormResults();

    $formTable->submissionId = $settings['submission-id'];

    $blockId                 = $settings['block-id'];

    $subId                   = $settings['subid'];
    if ($subId == '') {
        $subId    = null;
    }

    $newValue                = json_decode(TSJIPPY\sanitize($settings['new-value'], 'textarea_field'));

    $oldValue                = $formTable->getSubmissionValue($formTable->submissionId, $blockId, $subId);

    if ($oldValue == $newValue) {
        if (is_array($oldValue)) {
            $oldValue    = implode(' ', $oldValue);
        }
        return new WP_Error('tsjippy-forms', "Old value '$oldValue' is the same as the new value!");
    }

    // update the submissiom
    $result        = $formTable->updateSubmission($blockId, $newValue, $subId);
    if (is_wp_error($result)) {
        return $result;
    }

    //get transformed value
    $block       = $formTable->getBlockById($blockId);
    $submissions = $formTable->getSubmissions('', $formTable->submissionId);
    $transValue  = $formTable->transformInputData($newValue, $block, $submissions[0]);

    //send message back to js
    return [
        'message'   => "Succesfully updated the value to $transValue",
        'new-value' => $transValue,
    ];
}
