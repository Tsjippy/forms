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
                'el-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($elementId) {
                        return is_numeric($elementId);
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

                return $formBuilder->formSubmit($userId, (int) $_POST['form-id'], TSJIPPY\sanitize($_POST));
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
    global $wpdb;


    $forms    = new SaveFormSettings(formUrl: TSJIPPY\sanitize($_REQUEST['form-url'] ?? ''));
    $forms->getForm((int) $_POST['form-id']);

    $index        = 0;
    $oldElement    = new stdClass();

    //copy an existing element
    if ($copy === true) {
        $element            = $forms->getElementById((int) $_POST['element-id']);

        $element->slug        = $element->name;

        $element->infotext    = $element->text;
    }

    // Get element from $_POST
    else {
        $element            = TSJIPPY\sanitize($_POST["formfield"]);
        $element            = (object)$element;

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
    // phpcs:ignore
    if (is_numeric($_POST['element-id'])) {
        if ($copy !== true) {
            $update        = true;
        }

        $element->id    = (int) $_POST['element-id'];

        $oldElement     = $forms->getElementById($element->id);

        //$index        = $oldElement->index;
    }

    if ($element->type == 'php') {
        //we store the function_name in the html variable replace any double \ with a single \
        $element->slug            = $element->function_name;

        //only continue if the function exists
        if (! function_exists($element->function_name)) {
            return new WP_Error('forms', "A function with name $element->function_name does not exist!");
        }
    }

    if (in_array($element->type, ['label', 'button', 'formstep'])) {
        $element->name    = $element->text;
    } elseif (empty($element->name)) {
        return new \WP_Error('Error', "Please enter a name");
    }

    $element->name    = ucfirst(trim($element->name, '[]'));

    $element->slug    = str_replace(' ', '-', strtolower($element->name));

    if (
        in_array($element->type, $forms->nonInputs)         &&     // this is a non-input
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
    if (in_array($element->type, ['info', 'p'])) {
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
        if (!is_numeric($_POST['insert-after'])) {
            $element->priority    = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(`id`) FROM %i WHERE `form_id`=%d",
                    $forms->elTableName,
                    $element->form_id
                )
            ) + 1;
        } else {
            $element->priority    = (int) $_POST['insert-after'] + 1;
        }

        $element->id    = $forms->insertElement($element);

        // phpcs:ignore
        if (!empty($_POST['extra'])) {
            // The current indexes without the new element
            $newIndexes     = (array)json_decode(TSJIPPY\sanitize($_POST['extra']));

            // The new element has an unknow id of -1, replace it with the real id.
            $newIndexes[$element->id]    = $newIndexes[-1];
            unset($newIndexes[-1]);

            $forms->reorderElements($newIndexes, $element);
        }
    }

    $formBuilderForm    = new FormBuilderForm();
    $formBuilderForm->getForm((int) $_POST['form-id']);

    $html                 = $formBuilderForm->buildHtml($element, '', $index);

    return [
        'message'        => $message,
        'html'            => $html
    ];
}

// DONE
function removeElement()
{
    global $wpdb;

    $formBuilder    = new SaveFormSettings();

    $elementId        = (int) $_POST['elementindex'];

    $wpdb->delete(
        $formBuilder->elTableName,
        ['id' => $elementId],
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

    $formId                 = (int) $_POST['form-id'];
    $elementId              = (int) $_POST['element-id'];

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
    $formBuilder            = new SaveFormSettings();

    $formBuilder->formId    = (int) $_POST['form-id'];

    $newIndexes             = (array)json_decode(TSJIPPY\sanitize($_POST['indexes']));

    $element                = $formBuilder->getElementById((int) $_POST['el-id']);

    $formBuilder->reorderElements($newIndexes, $element);

    return "Succesfully saved new form order";
}

// DONE
function editFormfieldWidth()
{
    $formBuilder    = new SaveFormSettings();

    $elementId      = (int) $_POST['elementid'];
    $element        = $formBuilder->getElementById($elementId);

    $newwidth       = (int) $_POST['new-width'];
    $element->width = min($newwidth, 100);

    if ($formBuilder->formData == null) {
        $formBuilder->getForm((int) $_POST['form-id']);
    }

    $formBuilder->updateFormElement($element);

    return "Succesfully updated formelement width to $newwidth%";
}

// DONE
function requestFormConditionsHtml()
{
    $formBuilder = new FormBuilderForm();

    $elementID   = (int) $_POST['elementid'];

    $formBuilder->getForm((int) $_POST['form-id']);

    return $formBuilder->elementConditionsForm($elementID);
}

// DONE
function saveElementConditions()
{
    $formBuilder          = new SaveFormSettings();

    $elementId            = (int) $_POST['elementid'];
    if (!$elementId) {
        return new \WP_Error('forms', "First save the element before adding conditions to it");
    }
    $formId                = (int) $_POST['form-id'];

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
    $formBuilder   = new SaveFormSettings();

    $formSettings  = TSJIPPY\sanitize($_POST);
    unset($formSettings['_wpnonce']);
    unset($formSettings['form-id']);

    if (empty($formBuilder->formData)) {
        $formBuilder->formData    = new stdClass();
    }

    $formBuilder->formData->name    = TSJIPPY\sanitize($formSettings['form-name']);
    $formBuilder->formData->slug    = str_replace(' ', '-', strtolower($formBuilder->formData->name));

    //remove double slashes
    $formSettings['upload_path']    = wp_normalize_path($formSettings['upload_path'] ?? '');

    $formBuilder->maybeInsertForm((int) $_POST['form-id']);

    $result    = $formBuilder->updateFormSettings((int) $_POST['form-id'], $formSettings);

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

    $result    = $formBuilder->updateFormReminder((int) $_POST['form-id'], $formReminder);

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
