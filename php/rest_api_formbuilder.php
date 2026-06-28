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
function addRestUrls($urls)
{
    $urls[] = TSJIPPY\RESTAPIPREFIX . '/forms/save_form_input';

    return $urls;
}

function checkPermissions()
{
    $forms    = new Forms();

    return $forms->editRights;
}

add_action('rest_api_init', __NAMESPACE__ . '\restApiInitForms');
function restApiInitForms()
{
    // load form
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/load_form',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\loadForm',
            'permission_callback'     => '__return_true',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                )
            )
        )
    );

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

    // copy element to form
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/copy_form_element',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\copyFormElement',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'element-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($elementIndex) {
                        return is_numeric($elementIndex);
                    }
                ),
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                ),
                'order'        => array(
                    'required'    => true
                ),
            )
        )
    );

    // add element to form
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/add_form_element',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\addFormElement',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'formfield'        => array(
                    'required'    => true
                ),
                'element-id'        => array(
                    'required'    => true
                )
            )
        )
    );

    // delete element
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/remove_element',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\removeElement',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'elementindex'        => array(
                    'required'    => true,
                    'validate_callback' => function ($elementIndex) {
                        return is_numeric($elementIndex);
                    }
                ),
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
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
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\requestFormElement',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
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

    // reorder form elements
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/reorder-form-elements',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\reorderFormElements',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                ),
                'indexes'        => array(
                    'required'    => true
                )
            )
        )
    );

    // edit formfield width
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/edit_formfield_width',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\editFormfieldWidth',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                ),
                'elementid'        => array(
                    'required'    => true,
                    'validate_callback' => function ($elementId) {
                        return is_numeric($elementId);
                    }
                ),
                'new-width'        => array(
                    'required'    => true,
                    'validate_callback' => function ($width) {
                        return is_numeric($width);
                    }
                )
            )
        )
    );

    // form conditions html
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/request_form_conditions_html',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\requestFormConditionsHtml',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                ),
                'elementid'        => array(
                    'required'    => true,
                    'validate_callback' => function ($elementId) {
                        return is_numeric($elementId);
                    }
                )
            )
        )
    );

    // save_element-conditions
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_element-conditions',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\saveElementConditions',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                ),
                'elementid'        => array(
                    'required'    => true,
                    'validate_callback' => function ($elementId) {
                        return is_numeric($elementId);
                    }
                )
            )
        )
    );

    // save_form_settings
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_form_settings',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\saveFormSettings',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                )
            )
        )
    );

    // save_form_reminder
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_form_reminder',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\saveFormReminder',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
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
                $formBuilder    = new SubmitForm();

                // The user id of the current user
                $formBuilder->userId = get_current_user_id();

                // The user id for whom the form is submitted
                $userId    = '';
                foreach (['user-id', 'userid', 'user_id'] as $key) {
                    // phpcs:ignore
                    if (isset($_POST[$key])) {
                        $userId    = (int) $_POST[$key];
                        break;
                    }
                }

                // phpcs:ignore
                return $formBuilder->formSubmit($userId, TSJIPPY\sanitize($_POST));
            },
            'permission_callback'     => '__return_true',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                )
            )
        )
    );

    // save_form_emails
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/forms',
        '/save_form_emails',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\saveFormEmails',
            'permission_callback'     => __NAMESPACE__ . '\checkPermissions',
            'args'                    => array(
                'form-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($formId) {
                        return is_numeric($formId);
                    }
                ),
                'emails'        => array(
                    'required'    => true
                ),

            )
        )
    );
}

function prepareProperties($prop)
{
    if (is_array($prop)) {
        foreach ($prop as &$p) {
            prepareProperties($p);
        }
    } else {
        $prop     = wp_kses_post(wp_kses_stripslashes($prop));
        $prop    = str_replace('\\\\', '\\', $prop);
        $prop    = str_replace("\\'", "'", $prop);
    }

    return $prop;
}

function copyFormElement()
{
    return addFormElement(true);
}

// DONE
function addFormElement($copy = false)
{
    $request    = TSJIPPY\sanitize($_REQUEST);

    $forms      = new SaveFormSettings(atts: ['formid' => $request['form-id']]);

    $index      = 0;
    $oldElement = new stdClass();

    //copy an existing element
    if ($copy === true) {
        $element           = $forms->getElementById((int) $request['element-id']);

        $element->slug     = $element->name;

        $element->infotext = $element->text;
    }

    // Get element from $request
    else {
        $element            = (object)$request["formfield"];

        // make sure all data is clean
        foreach ($element as $prop => $val) {
            if (empty($val)) {
                continue;
            }

            $element->$prop = prepareProperties($val);

            if ($val == "true") {
                $val     = true;
            }

            if (is_array($val)) {
                $val    = serialize($val);
            } else {
                $val    = TSJIPPY\deslash($val);
            }
        }
    }

    $update    = false;
    if (is_numeric($request['element-id'])) {
        if ($copy !== true) {
            $update        = true;
        }

        $element->id    = (int) $request['element-id'];

        $oldElement     = $forms->getElementById($element->id);
    }

    if ($element->type == 'php') {
        //we store the function_name in the html variable replace any double \ with a single \
        $element->slug = $element->function_name;

        //only continue if the function exists
        if (! function_exists($element->function_name)) {
            return new WP_Error('forms', "A function with name $element->function_name does not exist!");
        }
    }

    if (isset(['label' => 1, 'button' => 1, 'formstep' => 1][$element->type])) {
        $element->name    = $element->text;
    } elseif (empty($element->name)) {
        return new \WP_Error('Error', "Please enter a name");
    }

    $element->name    = ucfirst(trim($element->name, '[]'));

    $element->slug    = str_replace(' ', '-', strtolower($element->name));

    if (
        isset($forms->nonInputs[$element->type])         &&     // this is a non-input
        $element->type != 'datalist'                        &&     // but not a datalist
        !str_contains($element->slug, $element->type)            // and the type is not yet added to the name
    ) {
        $element->slug    .= '_' . $element->type;
    }

    //Get an unique name if needed
    if (!$update || $element->slug != $oldElement->slug) {
        $element->slug        = $forms->getUniqueName($element, $update, $oldElement, $forms);
        if (is_wp_error($element->slug)) {
            return $element->slug;
        }
    }

    //Store info text in text column
    if (isset(['info' => 1, 'p' => 1][$element->type])) {
        $element->text     = wp_kses_post($element->infotext);
    }

    if ($update) {
        $message = "Succesfully updated '{$element->slug}'";
        $result  = $forms->updateFormElement($element);
        if (is_wp_error($result)) {
            return $result;
        }
    } else {
        $message = "Succesfully added '{$element->slug}' to this form";

        // phpcs:ignore
        if (!is_numeric($request['insert-after'])) {
            $element->priority    = TSJIPPY\getFromDb(
                "get_form_elements_count_for_$element->form_id",
                "forms",
                "SELECT COUNT(`id`) FROM %i WHERE `form_id`=%d LIMIT 1",
                $forms->elTableName,
                $element->form_id
            ) + 1;
        } else {
            // phpcs:ignore
            $element->priority    = (int) $request['insert-after'] + 1;
        }

        $element->id    = $forms->insertElement($element);
    }

    $formBuilderForm    = new FormBuilderForm(atts: ['formid' => $request['form-id']]);

    $html               = $formBuilderForm->buildHtml($element, '', $index);

    return [
        'message' => $message,
        'html'    => $html
    ];
}

// DONE
function removeElement()
{
    global $wpdb;

    $formBuilder    = new SaveFormSettings();

    $elementId        = (int) $_POST['elementindex'];

    TSJIPPY\removeFromDb(
        $formBuilder->elTableName,
        ['id' => $elementId],
        ['%d'],
        'forms'
    );

    // Fix priorities
    // Get all elements of this form
    $formBuilder->getAllFormElements('priority', (int) $_POST['form-id']);

    //Loop over all elements and give them the new priority
    foreach ($formBuilder->formElements as $key => $el) {
        if ($el->priority != $key + 1) {
            $el->priority = $key + 1;
            //Update the database

            $formBuilder->updatePriority($el);
        }
    }

    return "Succesfully removed the element";
}

// DONE
function requestFormElement()
{
    $formBuilderForm        = new FormBuilderForm();

    $formId                 = (int) $_POST['form-id'] ?? '';
    $elementId              = (int) $_POST['element-id'] ?? '';

    $formBuilderForm->getForm($formId);

    $conditionForm          = $formBuilderForm->elementConditionsForm($elementId);

    $elementForm            = $formBuilderForm->elementBuilderForm($elementId);

    return [
        'elementForm'       => $elementForm,
        'conditionsHtml'    => $conditionForm
    ];
}

// DONE
function reorderFormElements()
{
    $formBuilder            = new SaveFormSettings(['formid' => (int) $_POST['form-id'] ?? '']);

    // phpcs:ignore
    $elementIds             = (array)json_decode(TSJIPPY\sanitize($_POST['indexes']));

    $formBuilder->reorderElements($elementIds);

    return "Succesfully saved new form order";
}

// DONE
function editFormfieldWidth()
{
    $formBuilder    = new SaveFormSettings();

    $elementId      = (int) $_POST['elementid'] ?? '';
    $element        = $formBuilder->getElementById($elementId);

    $newwidth       = (int) $_POST['new-width'] ?? '';
    $element->width = min($newwidth, 100);

    if ($formBuilder->formData == null) {
        $formBuilder->getForm((int) $_POST['form-id'] ?? '');
    }

    $formBuilder->updateFormElement($element);

    return "Succesfully updated formelement width to $newwidth%";
}

// DONE
function requestFormConditionsHtml()
{
    $formBuilder = new FormBuilderForm();

    $elementID   = (int) $_POST['elementid'] ?? '';

    $formBuilder->getForm((int) $_POST['form-id'] ?? '');

    return $formBuilder->elementConditionsForm($elementID);
}

// DONE
function saveElementConditions()
{
    $formBuilder          = new SaveFormSettings();

    $elementId            = (int) $_POST['elementid'] ?? '';
    if (!$elementId) {
        return new \WP_Error('forms', "First save the element before adding conditions to it");
    }
    $formId                = (int) $_POST['form-id'] ?? '';

    $formBuilder->getForm($formId);

    $element              = $formBuilder->getElementById($elementId);

    $elementConditions    = TSJIPPY\sanitize($_POST['element-conditions'] ?? '');
    if (empty($elementConditions)) {
        $element->conditions    = '';

        $message = "Succesfully removed all conditions for {$element->slug}";
    } else {
        $element->conditions     = $elementConditions;

        $message                 = "Succesfully updated conditions for {$element->slug}";
    }

    $formBuilder->updateFormElement($element);

    //Create new js
    $errors         = $formBuilder->createJs();

    if (is_wp_error($errors)) {
        return $errors;
    } elseif (!empty($errors)) {
        $message    .= "\n\nThere were some errors:\n";
        $message    .= implode("\n", $errors);
    }

    return $message;
}

// DONE
function saveFormSettings()
{
    $formBuilder = new SaveFormSettings();
    $request     = TSJIPPY\sanitize($_POST);

    $formId        = $request['form-id'];
    unset($request['_wpnonce']);
    unset($request['form-id']);

    if (empty($formBuilder->formData)) {
        $formBuilder->formData    = new stdClass();
    }

    $formBuilder->formData->name    = $request['form-name'];
    $formBuilder->formData->slug    = str_replace(' ', '-', strtolower($formBuilder->formData->name));

    //remove double slashes
    $request['upload_path']    = wp_normalize_path($request['upload_path'] ?? '');

    $formBuilder->maybeInsertForm($formId);

    $result    = $formBuilder->updateFormSettings($formId, $request);

    if (is_wp_error($result)) {
        return $result;
    }
    return "Succesfully saved your form settings";
}

function saveFormReminder()
{
    $formBuilder   = new SaveFormSettings();

    $formReminder  = TSJIPPY\sanitize($_POST);
    unset($formReminder['_wpnonce']);

    $result    = $formBuilder->updateFormReminder($formReminder['form-id'], $formReminder);

    if (is_wp_error($result)) {
        return $result;
    }
    return "Succesfully saved your form reminder settings";
}

// DONE
function saveFormEmails()
{
    $formBuilder    = new SaveFormSettings();
    $formBuilder->getForm((int) $_POST['form-id']);

    $formEmails     = TSJIPPY\sanitize($_POST['emails']);

    $result         = $formBuilder->saveFormEmails($formEmails, (int) $_POST['form-id']);

    if (is_wp_error($result)) {
        return $result;
    }

    return "Succesfully saved your form e-mail configuration";
}

function loadForm()
{
    $displayForm    = new DisplayForm(['form-id' => (int) $_POST['form-id']]);

    return $displayForm->showForm();
}

function loadFormResults()
{
    $displayFormResults = new DisplayFormResults(['shortcode-id' => (int) $_POST['shortcode-id']]);

    return $displayFormResults->showFormresultsTable();
}
