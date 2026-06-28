<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Checks if a form exists
 * @param   string  $slug   The form slug or form name
 */
function checkFormExistence($slug)
{
    $slug   = strtolower(str_replace(' ', '-', $slug));
    $forms  = new Forms();
    $forms->getForms();

    // check if a form with this slug already exists
    $found  = false;
    foreach ($forms->forms as $form) {
        if ($form->slug == $slug || $form->id == $slug) {
            $found  = true;
            break;
        }
    }

    // Only add a new form if it does not exist yet
    if (!$found) {
        $forms->insertForm($slug);

        return $forms->formData->slug;
    }

    return $slug;
}

add_action('wp_after_insert_post', __NAMESPACE__ . '\afterInsertPost', 10, 2);
function afterInsertPost($postId, $post)
{
    if (has_block('tsjippy/formbuilder', $post)) {
        $hasFormbuilderShortcode    = true;

        $blocks                    = parse_blocks($post->post_content);

        foreach ($blocks as $block) {
            if ($block['blockName'] == 'tsjippy/formbuilder' && !empty($block['attrs']['formname'])) {
                checkFormExistence($block['attrs']['formname']);
            }
        }
    } else {
        $hasFormbuilderShortcode    = has_shortcode($post->post_content, 'formbuilder');

        // Add the form if it does not exist yet
        if ($hasFormbuilderShortcode) {
            preg_match_all(
                '/' . get_shortcode_regex() . '/',
                $post->post_content,
                $shortcodes,
                PREG_SET_ORDER
            );

            // loop over all the found shortcodes
            foreach ($shortcodes as $shortcode) {
                // Only continue if the current shortcode is a formbuilder shortcode
                if ($shortcode[2] == 'formbuilder') {
                    $atts   = shortcode_parse_atts($shortcode[3]);
                    // Get the formbuilder name from the shortcode
                    if (!empty($atts['formname'])) {
                        $formName       = $atts['formname'];
                    } elseif (!empty($atts['name'])) {
                        $formName       = $atts['name'];
                    } elseif (!empty($atts['slug'])) {
                        $formName       = $atts['slug'];
                    } elseif (!empty($atts['id'])) {
                        $formName       = $atts['id'];
                    }

                    $newFormName    = checkFormExistence($formName);

                    // Check if we should adjust the name
                    if ($formName != $newFormName) {
                        $newShortcode   = str_replace($formName, $newFormName, $shortcode[0]);

                        $content        = str_replace($shortcode[0], $newShortcode, $post->post_content);
                        wp_update_post(
                            array(
                                'ID'           => $postId,
                                'post_content' => $content,
                            ),
                            false,
                            false
                        );
                    }
                }
            }
        }
    }

    if ($hasFormbuilderShortcode || has_shortcode($post->post_content, 'formselector') || has_block('tsjippy/formbuilder', $post)) {
        $pages  = SETTINGS['formbuilder-pages'] ?? [];

        $pages[ $postId] = $postId;

        $settings   = SETTINGS;
        $settings['formbuilder-pages'] = $pages;

        update_option('tsjippy_forms_settings', $settings);
    }

    if (has_shortcode($post->post_content, 'formresults') || has_shortcode($post->post_content, 'formselector')) {
        $pages  = SETTINGS['formtable-pages'] ?? [];

        $pages[]  = $postId;

        $settings   = SETTINGS;
        $settings['formtable-pages'] = $pages;

        update_option('tsjippy_forms_settings', $settings);
    }
}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\registerScripts');
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\registerScripts');

function registerScripts()
{
    wp_register_style('tsjippy_forms_style', TSJIPPY\pathToUrl(PLUGINPATH . 'css/forms.min.css'), array(), PLUGINVERSION);
    wp_register_style('tsjippy_formtable_style', TSJIPPY\pathToUrl(PLUGINPATH . 'css/formtable.min.css'), array(), PLUGINVERSION);

    wp_register_script('tsjippy_forms_script', TSJIPPY\pathToUrl(PLUGINPATH . 'js/forms.min.js'), array('tsjippy_formsubmit_script', 'tsjippy_fileupload_script'), PLUGINVERSION, true);

    wp_register_script('tsjippy_formbuilderjs', TSJIPPY\pathToUrl(PLUGINPATH . 'js/formbuilder.min.js'), array('tsjippy_forms_script'), PLUGINVERSION, true);

    wp_register_script('tsjippy_forms_table_script', TSJIPPY\pathToUrl(PLUGINPATH . 'js/forms_table.min.js'), array('tsjippy_forms_script', 'tsjippy_table_script'), PLUGINVERSION, true);

    if (is_numeric(get_the_ID())) {
        $pages  = SETTINGS['formbuilder-pages'] ?? [];
        if (isset($pages[get_the_ID()])) {
            wp_enqueue_style('tsjippy_forms_style');
        }
    }
}
