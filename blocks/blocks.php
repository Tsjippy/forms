<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', __NAMESPACE__ . '\initBlocks');
/**
 * Register all blocks
 */
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
            'icon'  => 'forms',
            "category" => "form-blocks",
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
            'icon'  => 'table',
            "category" => "form-blocks",
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
            'icon'  => 'ellipsis',
            "category" => "form-blocks",
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

    $object                 = new DisplayFormResults($atts['id']);
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
 * Adds the form-blocks block category
 * 
 * @param   array   $categories
 */
function addFormsCategory( $categories) {
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'form-blocks', // Your unique category slug
                'title' => __( 'Elements for the formbuilder block', '%TEXTDOMAIN%' ), // Category display name
                'icon'  => 'forms', // Optional Dashicon
            ),
        )
    );
}

add_filter( 'pre_render_block', function($skip, $parsedBlock, $parentBlock ){
    if(
        ($parentBlock->name ?? '') == 'tsjippy-forms/label' &&  // THis block has a label parent
        ($parsedBlock['attrs']['multiple'] ?? false) &&   // and it can have multiple values &&
        (!in_array($parsedBlock['attrs']['type'] ?? '', ['text', 'email', 'tel', 'url']))
    ){
        return '';
    }
    return $skip;
}, 10, 3);

// Hook into the rendering of ALL blocks
add_filter( 'render_block', __NAMESPACE__.'\addBlockIdAttribute', 10, 3 );

/**
 * Function to render multi-inputs
 * 
 * @param   array   $values         The values to render
 * @param   string  $blockContent
 * @param   array   $block
 */
function renderMultiInput($values, $blockContent, $block, $label = null){
    /**
     * text or similar multi-input
     */
    if(in_array($block['attrs']['type'] ?? 'text' , ['text', "email", "tel", "text", "url"])){
        $listItems   = '';
        foreach($values as $value){
            $listItems   .= "<li class='list-selection'>";
                $listItems   .= "<button type='button' class='small remove-list-selection'>";
                    $listItems   .= "<span class='remove-list-selection'>×</span>";
                $listItems   .= "</button>";
                $listItems   .= "<input type='hidden' class='no-reset' name='{$block['attrs']['name']}' value='$value'>";
                $listItems   .= "<span class='selected-name'>$value</span>";
            $listItems   .= "</li>";
        }

        $blockContent = str_replace(['value="%value-placeholder%"', '%value-placeholder%'], ['', $listItems], $blockContent);
    }
    
    /**
     * Other multi-inputs
     */
    else{
        ob_start();
        ?>
        <div class="required flex" style="width: '85%';">
            <div class="clone-divs-wrapper">
                <?php
                $name  = $block['attrs']['name'] ?? '';
                foreach($values as $index => $value){
                    ?>
                    <div class="clone-div" data-div-id="<?php echo esc_attr($index);?>">
                        <?php echo wp_kses_post($label ?? '');?>
                        <div
                            class="button-wrapper"
                            style="margin: 'auto'; display: 'flex'"
                        >
                            <?php
                            echo str_replace([$name, '%value-placeholder%'], ["{$name}[$index]", $value], $blockContent);
                            ?>

                            <button
                                type="button"
                                class="remove button hidden"
                                style="flex: 1; max-width: max-content;"
                            >
                                <?php echo esc_html($block['attrs']['removeText'] ?? 'Remove');?>
                            </button>

                            <button
                                type="button"
                                class="add button"
                                style="flex: 1; max-width: max-content;"
                            >
                                <?php echo esc_html($block['attrs']['addText'] ?? 'Add');?>
                            </button>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        $blockContent = ob_get_clean();
    }

    return $blockContent;
}

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
    $forms  = new Forms();

    $forms->buildDefaultsArray();

    $defaultValues  = array_merge($forms->defaultArrayValues, $forms->defaultValues);

    /**
     * Set default value
     */
    if(empty($block['attrs']['dynamic_value'])){
        $defaultValue = $defaultValues[$block['attrs']['name'] ?? ''] ?? '';
    }else{
        $defaultValue = $defaultValues[$block['attrs']['dynamic_value']] ?? '';
    }

    /**
     * Render nested blocks of labels
     */
    if(
        $block['blockName'] == 'tsjippy-forms/label' &&  // This block has a label parent
        ($block['innerBlocks'][0]['attrs']['multiple'] ?? false) &&   // and it can have multiple values &&
        (!in_array($block['innerBlocks'][0]['attrs']['type'] ?? '', ['text', 'email', 'tel', 'url']))
    ){
        $blockContent = renderMultiInput($defaultValue, $block['innerBlocks'][0]['innerHTML'], $block['innerBlocks'][0], $blockContent);
    }
    /**
     * Set checked option
     */
    elseif(is_array($defaultValue) && in_array($block['attrs']['type'] ?? '', ['radio', 'checkbox'])){
        foreach($defaultValue as $value){
            $blockContent = str_replace("value=\"$value\"", "value=\"$value\" checked=\"checked\"", $blockContent);
        }
    }
    
    /**
     * Select option for select block
     */
    elseif(($block['blockName'] ?? '') == "tsjippy-forms/select"){
        if(!is_array($defaultValue)){
            $defaultValue    = [$defaultValue];
        }

        foreach($defaultValue as $value){
            $blockContent = str_replace("value=\"$value\"", "value=\"$value\" selected=\"selected\"", $blockContent);
        }
    }
    
    /**
     * Multiple values for multi-inputs
     */
    elseif(!empty($defaultValue) && ($block['attrs']['multiple'] ?? false)){
        if(!is_array($defaultValue)){
            $defaultValue    = [$defaultValue];
        }

        $blockContent = renderMultiInput($defaultValue, $blockContent, $block);
    }
    
    /**
     * Other inputs with a single value, like text, email, tel, url, etc.
     */
    else{
        if(!is_string($defaultValue)){
            $defaultValue    = '';
        }
        $blockContent = str_replace('%value-placeholder%', $defaultValue, $blockContent);
    }
    
    /**
     * Add the options
     */
    $options    = [];
    if(empty($block['attrs']['options_dynamic'])){
        $optionData = '';
    }else{
        $optionData = $multi[$block['attrs']['options_dynamic']] ?? [];
    }

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
    $id = esc_attr( $block['attrs']['blockId'] );
    if ( !empty( $block['attrs']['blockId'] ) && !str_contains($blockContent, "data-blockid='$id'")) {
        $processor = new \WP_HTML_Tag_Processor( $blockContent );

        if ( $processor->next_tag() ) {
            $processor->set_attribute( 'data-blockid', $id );

            $blockContent = $processor->get_updated_html();
        }
    }

    /**
     * Add hidden class if needed
     */
    if ( 
        !empty( $block['attrs']['hidden'] ) &&  // Hidden is enabled.
        empty( $block['attrs']['labelChild'])   // And not in a label block
    ) {
        $processor = new \WP_HTML_Tag_Processor( $blockContent );

        if ( $processor->next_tag() ) {
            $processor->add_class( 'hidden' );

            $blockContent = $processor->get_updated_html();
        }
    }

    return $blockContent;
}

/**
 * Determine whether a block has a formbuilder ancestor.
 * 
 * @param   array   $block
 */
function isFormbuilderChild( $block ) {
	if ( empty( $block['parent'] ) ) {
		return false;
	}

	foreach ( $block['parent'] as $parent ) {
		if ( 'tsjippy-forms/formbuilder' === $parent ) {
			return true;
		}
	}

	return false;
}

/**
 * Replaces the first occurence of a string
 * 
 * @param string    $search
 * @param string    $replace
 * @param string    $subject
 * 
 * @return string               Updated subject
 */
function strReplaceFirst($search, $replace, $subject) {
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

/**
 * Displays recommended form fields based on the provided attributes
 *
 * @param   array   $atts    The block attributes
 *
 * @return  string           The HTML for the recommended form fields
 */
function missingFormFields($atts)
{
    if(!is_user_logged_in()){
        return '';
    }

    $html    = '';

    $forms      = new FormReminders();
    $fieldHtml  = $forms->getReminderHtml($forms->userId, $atts['type'] ?? 'all');

    $family     = new TSJIPPY\FAMILY\Family();

    foreach($family->getChildren($forms->userId) as $child){
        $fieldHtml  .= $forms->getReminderHtml($child, $atts['type'] ?? 'all');
    }

    if (!empty($fieldHtml)) {
        $html .=  '<div id=recommendations style="margin-top:20px;">';
        $html .=  '<h3 class="frontpage">Recommendations</h3>';
        $html .=  '<p>It would be very helpfull if you could fill in the following:</p>';
        $html .=  $fieldHtml;
        $html .=  '</div>';
    }elseif(($_REQUEST['action'] ?? $_REQUEST['context'] ?? '') == 'edit'){
        $html   = "<div>No actions needed.</div>";
    }

    return $html;
}

/**
 * Displays a form selector based on the provided attributes
 *
 * @param   array   $atts    The shortcode attributes
 *
 * @return  string           The HTML for the form selector
 */
function showFormSelector($atts = [])
{
    wp_enqueue_script('tsjippy_forms_script');

    wp_enqueue_script('tsjippy_forms_table_script');

    wp_enqueue_style('tsjippy_forms_style');

    ob_start();

    $a = shortcode_atts(array(
        'exclude'   => [],
        'no_meta'   => true
    ), $atts);

    $formTable    = new DisplayFormResults();
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
                <label>
                    Select the form you want to submit or view the results of
                </label>
                <br>
                <select id="tsjippy-forms-selector">
                    <?php
                    foreach ($forms as $form) {
                        $name   = $form->slug;

                        if(empty($name)){
                            $name   = ucfirst(str_replace('_', ' ', $form->slug));
                        }

                        ?>
                        <option
                            value='<?php echo esc_attr($form->slug); ?>'
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
            $shortcodeData     = TSJIPPY\getFromDb(
                "get_shortcodes_for_form_$form->id",
                "forms",
                "SELECT * FROM %i WHERE block_id = %d",
                $formTable->shortcodeTable,
                $form->id
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


                <div id='<?php echo esc_attr($id);?>-results' class='form-results-wrapper <?php echo esc_attr($resultVis);?> formdata-load-trigger' data-shortcode-id=<?php echo esc_attr($shortcodeId);?>>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php

    return ob_get_clean();
}


add_filter( 'register_block_type_args', __NAMESPACE__.'\addGlobalAttributes' );
/**
 * Add filter attributes to all blocks contained in a formbuilder block
 *
 * @param array $args Arguments for registering a block type.
 * 
 * @return array
 */
function addGlobalAttributes( $args ) {
    if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
        $args['attributes'] = array();
    }

    $args['attributes'] = array_merge(
        $args['attributes'],
        [
            'blockId' => [
                'type'    => 'string',
                'default' => '',
            ],
            'hidden' => [
                'type'    => 'boolean',
                'default' => false,
            ],
            'formbuilderChild' => [
                'type'    => 'boolean',
                'default' => false,
            ],
            'notChild' => [
                'type'    => 'boolean',
                'default' => false,
            ],
            'remindByEmail' => [
                'type'    => 'boolean',
                'default' => false,
            ],
            'conditionMode' => [
                'type'    => 'string',
                'default' => 'and',
            ],
            'conditions' => [
                'type'    => 'array',
                'default' => [],
            ],
            'roles' => [
                'type'    => 'array',
                'default' => [],
            ],
            'inverseRoles' => [
                'type'    => 'boolean',
                'default' => false,
            ],
        ]
    );

    return $args;
}

function my_custom_post_view_config( $view_config ) {
    // Modify view configuration, fields, or filters here
    return $view_config;
}
add_filter( 'get_entity_view_config_posttype_page', __NAMESPACE__.'\my_custom_post_view_config' );
