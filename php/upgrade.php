<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

function insertNewForms(){
    global $wpdb;

    
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
        "button",
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
        'value' => 'add',
        'inputAttributes' => 'add',
        'ariaAttributes' => 'add',
        'selectable_options' => 'add',
        'add_button_content' => 'add',
        'remove_button_content' => 'remove',
        'multiple' => 'add',
        'required' => 'required',
        'hasLabelParent' => 'add',
        'hide' => 'add',
    ];

    $blocks = [
        "formstep"  => "tsjippy-forms/formstep",
        "input"     => "tsjippy-forms/input",
        "label"     => "tsjippy-forms/label",
        "datalist"  => "tsjippy-forms/datalist",
        "textarea"  => "tsjippy-forms/input",
        "select"    => "tsjippy-forms/select",
        "p"         => "paragraph",
        "div-start" => "group"

    ];

    $forms = $wpdb->get_results("SELECT * FROM `wp_tsjippy_forms`");

    foreach($forms as $form){

        // Insert the post into the database
        $postId = wp_insert_post( [
            'post_title'    => wp_strip_all_tags( $form->name ),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page'
        ] );

        /**
         * Build the form
         */

        $blockAttributes = [];

        foreach ($formAttributes as $blockKey => $oldKey) {
            if (isset($form->$oldKey)) {
                $blockAttributes[$blockKey] = $form->$oldKey;
            }
        }

        $content = sprintf(
            '<!-- wp:tsjippy-forms/formbuilder %s /-->',
            wp_json_encode($blockAttributes)
        );

        /**
         * Form Elements
         */
        $elements   = $wpdb->get_results("select * from wp_tsjippy_form_elements where form_id = $form->id ORDER BY `priority` ");

        $closeLabel     = false;
        $closeFormstep  = false;
        foreach($elements as $element){
            /* div-start
            div-end */

            $attributes = [];

            // Formstep
            if($element->type == 'formstep'){
                if($closeFormstep){
                    $content .= sprintf(
                        '<!-- /wp:%s /-->',
                        $blocks[$element->type]
                    );
                }

                $attributes = ['text' => $element->text];

                $closeFormstep  = true;
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
                    }
                }

                $element->type  = 'input';

                if($closeLabel){
                    $content .= sprintf(
                        '<!-- /wp:%s /-->',
                        $blocks['label']
                    );
                    
                    $closeLabel     = false;
                }
            }

            // Label
            elseif($element->type == 'label'){
                $attributes = ['text' => $element->text];

                $closeLabel     = true;
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
                $content .= '<!-- /wp:group /-->';

                continue;
            }

            $content .= sprintf(
                '<!-- wp:%s %s /-->',
                $blocks[$element->type],
                wp_json_encode($attributes)
            );
        }
    }
}



// Example output:
// <!-- wp:tsjippy-forms/formbuilder {"method":"post","name":"test"} /-->


/* <!-- wp:tsjippy-forms/formbuilder {"method":"post","name":"test"} -->
    <!-- wp:tsjippy-forms/input {"type":"radio","name":"gender","selectable_options":"male\nfemale","className":"wp-block-tsjippy-forms-input","blockId":"a1f3213b-f7c0-458c-9549-df181f8e9b22"} -->

    <!-- /wp:tsjippy-forms/input -->


    <!-- wp:tsjippy-forms/input {"type":"checkbox","name":"cartype","selectable_options":"toyota\ncitroen\nrenault\npegaut\ndaf","className":"wp-block-tsjippy-forms-input","blockId":"8734ab95-a3d0-4d18-9d15-4c88a81eb805"} -->

    <!-- /wp:tsjippy-forms/input -->


    <!-- wp:tsjippy-forms/label {"text":"Your Name","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text","hidden":""},"blockId":"66643432-c143-4277-bfa5-18e83ab08cd0"} -->

    <!-- wp:tsjippy-forms/input {"type":"text","name":"firstname","hasLabelParent":true,"blockId":"650a87a0-1f4b-4979-8328-98f37e27f057"} -->

    <!-- /wp:tsjippy-forms/input --></label>

    <!-- /wp:tsjippy-forms/label -->


    <!-- wp:tsjippy-forms/label {"text":"Your Last Name","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text","hidden":""},"blockId":"1bb8d061-1200-4c12-9bc3-791604385142"} -->

    <!-- wp:tsjippy-forms/input {"type":"text","name":"lastname","inputAttributes":{"dir":"ltr"},"hasLabelParent":true,"blockId":"bf115303-e843-41b7-b11d-427b49069d88"} -->

    <!-- /wp:tsjippy-forms/input --></label>

    <!-- /wp:tsjippy-forms/label -->


    <!-- wp:tsjippy-forms/label {"text":"Phone Number","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"tel","hidden":""},"blockId":"134be812-48b8-4e9a-86da-6d338e6cf476"} -->

    <!-- wp:tsjippy-forms/input {"type":"tel","name":"phone","hasLabelParent":true,"blockId":"5d58a748-9109-48cb-bc4c-ad5f55a31511"} -->

    <!-- /wp:tsjippy-forms/input -->

    <!-- /wp:tsjippy-forms/label -->

<!-- /wp:tsjippy-forms/formbuilder --> */