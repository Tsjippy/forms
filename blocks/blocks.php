<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', __NAMESPACE__ . '\initBlocks');
function initBlocks()
{
    // Register all js blocks
    wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
    
    register_block_type(
        'tsjippy-forms/form-selector',
        array(
            'title'           => __( 'Forms Selector', '%TEXTDOMAIN%' ),
            'attributes'      => array(
                'hide_meta_forms'   => array(
                    'label'   => __( 'Hide forms that save to user meta', '%TEXTDOMAIN%' ),
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

    $forms      = new Forms();
    $forms->getForms();
    $formNames  = [];

    foreach($forms->forms as $form){
        if(empty($form->name)){
            continue;
        }

        $formNames[]    = trim($form->slug);
    }

    register_block_type(
        'tsjippy-forms/forms-results',
        array(
            'title'           => __( 'Form Results', '%TEXTDOMAIN%' ),
            'attributes'      => [
                'formname' => [
                    'label'   => __( 'Form name', '%TEXTDOMAIN%' ),
                    'type'    => 'string',
                    'enum'    => $formNames
                ],
                'only-own'  => [
                    'label'   => __( 'Show The Results of the Current User Only', '%TEXTDOMAIN%' ),
                    'type'    => 'boolean',
                    'default' => true,
                ],
                'archived'  => [
                    'label'   => __( 'Show Archived Results', '%TEXTDOMAIN%' ),
                    'type'    => 'boolean',
                    'default' => false,
                ],
                'id'  => [
                    'type'    => 'integer',
                    'default' => -1,
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
            'title'           => __( 'Missing Form Entries', '%TEXTDOMAIN%' ),
            'attributes'      => [
                'type' => [
                    'label'   => __( 'Which type', '%TEXTDOMAIN%' ),
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

/**
 * Displays form results based on the provided attributes
 *
 * @param   array   $atts    The shortcode attributes
 *
 * @return  string           The HTML for the form results
 */
function formResults($atts)
{
    if($atts['id'] == -1){
        return "<div class='error'>No valid shortcode id yet</div>";
    }
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


add_filter( 'block_categories_all', __NAMESPACE__.'\addFormsCategory' );

/**
 * Adds the form-elements block category
 * 
 * @param   array   $categories
 */
function addFormsCategory( $categories) {
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'form-elements', // Your unique category slug
                'title' => __( 'Elements for the formbuilder block', '%TEXTDOMAIN%' ), // Category display name
                'icon'  => 'forms', // Optional Dashicon
            ),
        )
    );
}

// Hook into the rendering of ALL blocks
add_filter( 'render_block', __NAMESPACE__.'\addBlockIdAttribute', 10, 3 );

/**
 * Adds the block id as data attribute on the frontend to be used in js
 * 
 * @param   string  $blockContent
 * @param   array   $block
 */
function addBlockIdAttribute( $blockContent, $block, $instance ) {
    /**
     * Fill with dynamic data
     */
    $forms  = new ElementHtmlBuilder();

    $forms->buildDefaultsArray();

    $multi  = $forms->defaultArrayValues;
    $single = $forms->defaultValues;

    /**
     * Set default value
     */
    $defaultValue = $single[$block['attrs']['dynamic_value']] ?? '';
    $blockContent = str_replace('%value-placeholder%', $defaultValue, $blockContent);
    
    /**
     * Add the options
     */
    $options    = [];
    $optionData = $multi[$block['attrs']['options_dynamic']] ?? [];

    if(!empty($optionData)){
        foreach($optionData as $key => $value){
            // Data list
            if($block['blockName'] == "tsjippy-forms/datalist"){
                $option = "<option dataset-value='$key' ";
                if(is_array($value)){
                    $option .= "value='{$value['value']}'>{$value['display']}</option>";
                }else{
                    $option .= "value='$value'></option>";
                }
            }else{
                $selected   = '';

                /**
                 * Determine the selected option
                 */
                if(
                    $defaultValue   == $key &&  
                    (
                        $block['blockName'] == "tsjippy-forms/select" ||
                        (
                            $block['blockName'] == "tsjippy-forms/input" &&
                            in_array($block['attrs']['type'] ?? '', ['radio', 'checkbox'])
                        )
                    ) 
                ){
                    $selected = 'selected="selected"';
                }
                $option = "<option value='$key' $selected>$value</option>";
            }

            $options[] = $option;
        }
    }
    $blockContent = str_replace('%options-placeholder%', implode("\n", $options), $blockContent);

    /**
     * Load dynamic forms script
     */
    if($block['blockName'] == "tsjippy-forms/formbuilder"){
        $formName   = $block['attrs']['name'];
        $jsPath     = plugin_dir_path(__DIR__) . "js/dynamic/{$formName}.js";
        
        if (file_exists($jsPath) && filesize($jsPath) > 0) {
            wp_enqueue_script("tsjippy_forms_dynamic_{$formName}_js", TSJIPPY\pathToUrl($jsPath), array('tsjippy_forms_script'), PLUGINVERSION, true);
        }
    }

    /**
     * Check if our filtered attribute exists in the block data
     */
    if ( ! empty( $block['attrs']['blockId'] ) && !str_contains($blockContent, 'data-blockid')) {
        $id = esc_attr( $block['attrs']['blockId'] );

        $blockContent = str_replace( '/>', " data-blockid='$id' />", $blockContent );
    }

    return $blockContent;
}