<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

function printJs($blockData, $postId){
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            sendBlockContent(<?php echo wp_json_encode($blockData); ?>, <?php echo $postId;?>);
        });
    </script>
    <?php
}

add_action('wp_ajax_save_generated_blocks', function () {
    $content = wp_unslash($_POST['content'] ?? '');

    wp_update_post(
        [
            'ID'    => (int)$_POST['postId'],
            'post_content'  => $content
        ]
    );

    wp_send_json_success();
});

function insertNewForms(){
    global $wpdb;

    ?>
<script>
function buildBlock(block) {
    return wp.blocks.createBlock(
        block.blockName,
        block.attrs || {},
        (block.innerBlocks || []).map(buildBlock)
    );
}

function sendBlockContent(block, postId){
    console.log(block);
    const content = wp.blocks.serialize(
        buildBlock(block)
    );

    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'save_generated_blocks',
            content,
            postId
        }),
    });
}
</script>
<?php   
    $formAttributes = [
        'submission_message' => 'succes_message',
        'submission_id'      => 'include_id',
        'name'               => 'name',
        'actions'            => 'actions',
        'user_meta'          => 'save_in_meta',
        'edit_roles'         => 'full_right_roles',
        'auto_archive_element' => 'autoarchive_el',
        'auto_archive_value' => 'autoarchive_value',
        'submission_roles'   => 'submit_others_form',
        'split_elements'     => 'split',
    ];

    $inputTypes = [
        "checkbox",
        "color",
        "date",
        "datetime-local",
        "email",
        "file",
        "hidden",
        "image",
        "month",
        "number",
        "password",
        "radio",
        "range",
        "reset",
        "search",
        "submit",
        "tel",
        "text",
        "time",
        "url",
        "week",
    ];
    
    $inputAttributes = [
        'type' => 'type',
        'name' => 'name',
        'selectable_options' => 'value_list',
        'add_button_content' => 'add',
        'remove_button_content' => 'remove',
        'multiple' => 'multiple',
        'required' => 'required',
        'hide' => 'hidden',
        'selectable_options_dynamic' => 'default_array_value',
        'dynamic_value' => 'default_value',
    ];

    $blocks = [
        "formstep"  => "tsjippy-forms/formstep",
        "input"     => "tsjippy-forms/input",
        "label"     => "tsjippy-forms/label",
        "datalist"  => "tsjippy-forms/datalist",
        "textarea"  => "tsjippy-forms/input",
        "select"    => "tsjippy-forms/select",
        "p"         => "paragraph",
        "div-start" => "group",
        "multi-start" => "tsjippy-forms/multiwrap",
        "info"      => "tsjippy-forms/info",
        "booking-selector" => "tsjippy-bookings/accomodation",
        "button"    => "buttons",
    ];

    $forms = new Forms();
    $forms->createDbTables();

    $forms->getForms();

    foreach($forms->forms as $form){

        if($form->id != 12){
            continue;
        }

        // Insert the post into the database
        $postId = wp_insert_post( [
            'post_title'    => wp_strip_all_tags( $form->name ),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page'
        ] );

        echo "Created post <a href='" . get_permalink($postId) ."'>$form->name</a><br>";
        
        /**
         * Build the form
         */

        $attributes = [];

        foreach ($formAttributes as $blockKey => $oldKey) {     
            if (isset($form->$oldKey)) {
                
                if(in_array($blockKey, ['submission_id', 'user_meta'])){
                    $attributes[$blockKey] = boolval($form->$oldKey);
                }else{
                    $attributes[$blockKey] = $form->$oldKey;
                }
            }
        }

        $formBlock  = [
            'blockName'    => 'tsjippy-forms/formbuilder',
            'attrs'        => $attributes,
            'innerBlocks'  => [],
            'innerHTML'    => '',
            'innerContent' => []
        ];

        $stack = [&$formBlock];

        /**
         * Form Elements
         */
        $elements   = $wpdb->get_results("select * from wp_tsjippy_form_elements where form_id = $form->id ORDER BY `priority` ");

        $shouldCLoseFormstep    = false;
        $shouldCloseLabel       = false;
        foreach($elements as $element){
            $attributes     = [];
            $innerBlocks    = [];

            // Formstep
            if($element->type == 'formstep'){
                $attributes = ['text' => $element->text];

                // Store innerblock index
                if($shouldCLoseFormstep){
                    array_pop($stack);
                }
                $shouldCLoseFormstep = true;
            }

            // Input
            elseif(in_array($element->type, $inputTypes)){
                /**
                 * Build the form
                 */

                $attributes = [];

                foreach ($inputAttributes as $blockKey => $oldKey) {
                    if (isset($element->$oldKey)) {
                        $attributes[$blockKey] = $element->$oldKey;

                        if(in_array($blockKey, ['multiple','required', 'hide'])){
                            $attributes[$blockKey] = boolval($element->$oldKey);
                        }
                    }
                }

                $attributes['inputAttributes'] = [];
                if(!empty($element->text)){
                    $attributes['inputAttributes']["value"] = $element->text;
                }

                 if(!empty($element->options)){
                    $options = explode("\n",  $element->options);

                    foreach($options as $option){
                        $exp    = explode("=", $option);
                        $attributes['inputAttributes'][$exp[0]] = $exp[1];
                    }
                 }

                $element->type  = 'input';
            }

            // Label
            elseif($element->type == 'label'){
                $attributes = ['text' => $element->text];

                $shouldCloseLabel = true;
            }

            // datalist
            elseif($element->type == 'datalist'){
                $attributes = ['id' => $element->slug];
                $attributes = ['options' => $element->options];
            }

            // select
            elseif($element->type == 'select'){
                $attributes = ['id' => $element->slug];
                $attributes = ['options' => $element->options];
            }

            // container
            elseif($element->type == 'div-start'){
                $attributes = [
                    'layout' => [
                        "type"        => "flex",
                        "orientation" => "vertical"
                    ]
                ];
            }

            // Container end
            elseif($element->type == 'div-end'){
                array_pop($stack);

                continue;
            }

            // multi container
            elseif($element->type == 'multi-start'){
                $attributes = [
                    'add_button_content' => $element->add,
                    "remove_button_content" => $element->remove
                ];
            }

            // multi container end
            elseif($element->type == 'multi-end'){
                array_pop($stack);

                continue;
            }

            // info
            elseif($element->type == 'info'){
                $attributes = [
                    'text' => $element->text
                ];
            }

            // booking selector
            elseif($element->type == 'booking-selector'){
                $attributes = [
                    'bookingSubjects' => [
                        24522,
                        24523,
                        24524,
                        24530
                    ],
                    "required" => true
                ];
            }

            // php
            elseif($element->type == 'php'){
                continue;
            }

            elseif($element->type == 'p'){
                $attributes = [
                    "text"  => $element->text
                ]; 
            }

            elseif($element->type == 'button'){
                $attributes = [
                    "text"  => $element->text
                ]; 

                $innerBlocks = [
                    [
                        'blockName'    => 'button',
                        'attrs'        => ["onlyOnInherited" => true],
                        'innerBlocks'  => [],
                        'innerHTML'    => '',
                        'innerContent' => []
                    ]
                ];
            }

            else{
                TSJIPPY\printArray("Unknown element type $element->type ");
            }

            /**
             * Add to the current parent
             */
            $attributes['blockId']  = $element->id;

            $current = &$stack[count($stack) - 1];

            $current['innerBlocks'][] = [
                'blockName'    => $blocks[$element->type],
                'attrs'        => $attributes,
                'innerBlocks'  => $innerBlocks,
                'innerHTML'    => '',
                'innerContent' => []
            ];

            if($shouldCloseLabel && $element->type != 'label'){
                array_pop($stack);
                $shouldCloseLabel   = false;
            }

            if(in_array($element->type, ['formstep', 'label', 'div-start', 'multi-start'])){
                $index = count($current['innerBlocks']) - 1;
                $stack[] = &$current['innerBlocks'][$index];
            }

            /**
             * Block Conditions
             */
            $rules   = [];
            $actions = [];
            if(!empty($element->conditions)){
                $element->conditions = maybe_unserialize($element->conditions);
                foreach($element->conditions as $condition){
                    foreach($condition['rules'] as $index => $rule){
                        if(empty($rule['conditional-field'])){
                            unset($condition['rules'][$index]);
                        }
                    }
                    $newRules   = TSJIPPY\cleanUpNestedArray($condition['rules']);

                    if(!empty($newRules)){
                        $rules[] = $newRules;
                    }

                    unset($condition['rules']);

                    if(!empty($condition['property-name1'])){
                        $condition['property-name']    = $condition['property-name1'];
                    }
                    unset($condition['property-name1']);
                }

                $actions[]  = TSJIPPY\cleanUpNestedArray($condition);
            }

            if(!empty($rules) && !empty($actions)){
                $wpdb->insert(
                    $wpdb->prefix."tsjippy_form_block_conditions",
                    [
                        "rules" => maybe_serialize($rules),
                        "actions" => maybe_serialize($actions),
                        "block_id" => $element->id,
                        "post_id" => $postId
                    ],
                    [
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                    ]
                );
            }

            /**
             * Warning conditons
             */
            if(!empty($element->warning_conditions)){
                $element->warning_conditions = maybe_unserialize($element->warning_conditions);

                if(!empty($element->warning_conditions)){
                    $wpdb->insert(
                        $wpdb->prefix."tsjippy_form_block_reminders",
                        [
                            "rules" => maybe_serialize(TSJIPPY\cleanUpNestedArray($element->warning_conditions)),
                            "block_id" => $element->id,
                            "post_id" => $postId
                        ],
                        [
                            '%s',
                            '%s',
                            '%d',
                        ]
                    );
                }
            }
        }

        printJs($formBlock, $postId);
    }
}

add_action('admin_footer-post.php', function(){
    //insertNewForms();
});
