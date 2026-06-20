<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

add_shortcode('tsjippy_formselector', __NAMESPACE__ . '\showFormSelector');
function showFormSelector($atts = [])
{
    global $wpdb;

    wp_enqueue_script('tsjippy_forms_script');

    wp_enqueue_script('tsjippy_forms_table_script');

    wp_enqueue_style('tsjippy_forms_style');

    ob_start();

    $a = shortcode_atts(array(
        'exclude'   => [],
        'no_meta'   => true
    ), $atts);

    $formTable    = new DisplayFormResults($atts);
    $formTable->getForms();

    $forms          = $formTable->forms;

    // Remove any unwanted forms
    if (!empty($a['exclude']) || $a['no_meta']) {
        if (is_array($a['exclude'])) {
            $exclusions = $a['exclude'];
        } else {
            $exclusions = explode(',', $a['exclude']);
        }

        foreach ($forms as $key => $form) {
            if (in_array($form->slug, $exclusions) || empty($form->slug)) {
                unset($forms[$key]);
            }

            // Remove any form that saves its data in the usermeta
            if ($a['no_meta'] && $form->save_in_meta) {
                unset($forms[$key]);
            }
        }
    }

    //Sort form names by alphabeth
    usort($forms, function ($a, $b) {
        return strcasecmp($a->slug, $b->slug);
    });

    ?>
    <div id="forms-wrapper">
        <?php
        //only show selector if not queried
        if (!isset($_REQUEST['form'])) {
        ?>
            <div id="form-selector-wrapper">
                <label>Select the form you want to submit or view the results of</label>
                <br>
                <select id="tsjippy-forms-selector">
                    <?php
                    foreach ($forms as $form) {
                        $name   = ucfirst(str_replace('_', ' ', $form->slug));

                    ?>
                        <option
                            value='<?php echo esc_url($form->slug); ?>'
                            <?php
                            // phpcs:ignore
                            if (($_REQUEST['form'] ?? '') == $form->slug || ($_REQUEST['form'] ?? '') == $form->id) {
                                echo 'selected=selected';
                            }
                            ?>>
                            <?php echo esc_html($name); ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        <?php
        }

        // phpcs:ignore
        if (($_REQUEST['display'] ?? '') == 'results') {
            $formVis       = ' hidden';
            $resultVis     = '';
            $formActive    = ' active';
            $resultActive  = '';
        } else {
            $formVis       = '';
            $resultVis     = ' hidden';
            $formActive    = ' active';
            $resultActive  = '';
        }

        /**
         * Loop over the forms to add both the form and the submission data
         */
        foreach ($forms as $form) {
            $shortcodeData     = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM %i WHERE form_id= %d",
                    $formTable->shortcodeTable,
                    $form->id
                )
            );

            //Create shortcode data if not existing
            if (empty($shortcodeData)) {
                $shortcodeId   = $formTable->insertInDb($form->id);
            } else {
                $shortcodeId   = $shortcodeData[0]->id;
            }

            //Check if this form should be displayed
            // phpcs:ignore
            if (isset($_REQUEST['form']) && ($_REQUEST['form'] == $form->slug || $_REQUEST['form'] == $form->id)) {
                $hidden = '';
            } else {
                $hidden = ' hidden';
            }

            $id = strtolower(str_replace([' ', '_'], '-', $form->slug));

            ?>
            <div id='<?php echo esc_attr($id);?>' class='main-form-wrapper<?php echo esc_attr($hidden);?>'>
                <?php
                //only show button if not queried
                // phpcs:ignore
                if (!isset($_REQUEST['display'])) {
                    ?>
                    <button class='button tablink<?php echo esc_attr($formActive);?>' id='show-<?php echo esc_attr($id);?>-form' data-target='<?php echo esc_attr($id);?>-form'>
                        Show form
                    </button>
                    <button class='button formresults tablink<?php echo esc_attr($resultActive);?>' id='show-<?php echo esc_attr($id);?>_results' data-target='<?php echo esc_attr($id);?>-results'>
                        Show form results
                    </button>
                    <?php
                }

                ?>
                <div id='<?php echo esc_attr($id);?>-form' class='form-wrapper <?php echo esc_attr($formVis);?> form-load-trigger' data-form-id=<?php echo esc_attr($form->id);?>>
                </div>


                <div id='<?php echo esc_attr($id);?>-results' class='form-results-wrapper <?php echo esc_attr($resultVis);?> form-load-trigger' data-shortcode-id=<?php echo esc_attr($shortcodeId);?>>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php

    return ob_get_clean();
}

//shortcode to make forms
add_shortcode('tsjippy_formbuilder', __NAMESPACE__ . '\showForm');
/**
 * Displays a form based on the provided attributes
 *
 * @param   array   $atts    The shortcode attributes
 *
 * @return  string            The HTML for the form
 */
function showForm($atts)
{
    if(!empty($_POST['export-form'])){
        $forms   = new FormExport($atts);

        $formId = (int) $_POST['export-form'];

        if(!TSJIPPY\verifyNonce('nonce', 'form-export-'.$formId)){
            return "<div class='error'>Invalid nonce</div>";
        }

        return $forms->exportForm($formId);
    }

    if(!empty($_POST['delete-form'])){
        $forms   = new Forms($atts);

        $formId = (int) $_POST['export-form'];

        if(!TSJIPPY\verifyNonce('nonce', 'form-delete-'.$formId)){
            return "<div class='error'>Invalid nonce</div>";
        }
        
        return $forms->deleteForm($formId);
    }

    if(!empty($_GET['formbuilder'])){
        $forms   = new FormBuilderForm($atts);
        return $forms->showForm();
    }
    
    $forms   = new DisplayForm($atts); 
    
    $html    = $forms->determineForm();
    if (is_wp_error($html)) {
        return "<div class='error'>" . $html->get_error_message() . "</div>";
    }

    return $html;
}

add_shortcode('tsjippy_formresults', __NAMESPACE__ . '\formResults');
/**
 * Displays form results based on the provided attributes
 *
 * @param   array   $atts    The shortcode attributes
 *
 * @return  string            The HTML for the form results
 */
function formResults($atts)
{
    $object = new DisplayFormResults($atts);
    $object->showArchived   = isset($_GET['archived']);
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
        return "<div class='error'>" . $html->get_error_message() . "</div>";
    }

    return $html;
}

//Shortcode for recommended fields
add_shortcode("tsjippy_missing_form_fields", __NAMESPACE__ . '\missingFormFields');

/**
 * Displays recommended form fields based on the provided attributes
 *
 * @param   array   $atts    The shortcode attributes
 *
 * @return  string            The HTML for the recommended form fields
 */
function missingFormFields($atts)
{
    $a = shortcode_atts(array(
        'type'   => 'mandatory'
    ), $atts);

    $html    = '';

    $forms      = new FormReminders();
    $fieldHtml  = $forms->getReminderHtml(get_current_user_id(), $a['type']);

    if (!empty($fieldHtml)) {
        $html .=  '<div id=recommendations style="margin-top:20px;">';
        $html .=  '<h3 class="frontpage">Recommendations</h3>';
        $html .=  '<p>It would be very helpfull if you could fill in the following:</p>';
        $html .=  $fieldHtml;
        $html .=  '</div>';
    }

    return $html;
}

add_filter('wp_insert_post_data', __NAMESPACE__ . '\insertPostData', 10, 2);
/**
 * Checks if the content contains a form shortcode and if so, creates the form and replaces the shortcode with the form
 *
 * @param   array   $data       The post data to be inserted into the database
 * @param   array   $postarr    The original post data before it was modified by this filter
 *
 * @return  array   The modified post data to be inserted into the database
 */
function insertPostData($data, $postarr)
{
    if (function_exists('wp_get_current_user')) {
        $formtable  = new DisplayFormResults([]);
        return $formtable->checkForFormShortcode($data);
    }

    return $data;
}
