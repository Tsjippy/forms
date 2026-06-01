

== Description ==
<p>
    This module adds 4 shortcodes:<br>
    - formbuilder
    - formresults
    - formselector
    - missing_form_fields
</p>
<h4>Formbuilder</h4>
<p>
    This shortcode allows you to build a form with a unique name.<br>
    Use like this: <code>[formbuilder formname=SOMENAME]</code>.<br>
    The formname must supply a valid formname.<br>
</p>
<h4>Formresults</h4>
<p>
    This shortcode allows you to display a form's results.<br>
    Use like this: <code>[formresults formname=SOMENAME]</code>
</p>
<h4>Formselector</h4>
<p>
    This shortcode will display a dropdown with all forms.<br>
    Upon selection of a form, the form will be displayed as well as the results of the form.
    You can exclude certain forms by using the 'exclude' key word.<br>
    Forms that save their submission to the usermeta table are exclude by default.<br>
    You can include them by using the 'no_meta' key word.<br>
    Use like this: <code>[formresults exclude="SOMENAME, SOMEOTHERNAME" no_meta="false"]</code>
</p>
<h4>Missing form fields</h4>
<p>
    This shortcode allows you to display a list of all mandatory or recommended fields still to be filled in.<br>
    The type should be 'all', 'mandatory' or 'recommended'.<br>
    Use like this: <code>[missing_form_fields type="recommended"]</code>
</p>

== Hooks ==
# FILTERS
- apply_filters("sim_{$type}_elements_filter", $elements);
- apply_filters("sim_{$type}_html_filter", $html, $userId);
- apply_filters('sim-forms-submission-updated', $message, $formTable, $elementName, $oldValue, $newValue);
- apply_filters('sim-forms-before-showing-form', '', $this);
- apply_filters('sim_before_form', '', $this->formName);
- apply_filters('sim_formdata_retrieval_query', $query, $userId, $this);
- apply_filters('sim_retrieved_formdata', $results, $userId, $this);
- apply_filters('sim_formdata_retrieval_query', $query, $userId, $this);
- apply_filters('sim_retrieved_formdata', $result, $userId, $this);
- apply_filters('sim_remove_formdata', true, $userId, $submission);
- apply_filters('sim_transform_formtable_data', $output, $elementName);
- apply_filters('sim_form_actions', $actions);
- apply_filters('sim-formresult-cell-opening-tag', $cellOpeningTag, $this, $columnSetting, $values);
- apply_filters('sim_form_actions_html', $buttonsHtml, $values, $subId, $this);
- apply_filters('sim-formstable-should-show', true, $this, $type);
- apply_filters('sim-forms-elements', $this->formElements, $this, true);
- apply_filters('sim-special-form-elements', $options);
- apply_filters('sim-forms-before-saving-settings', $newSettings, $this, $formId);
- apply_filters('sim-forms-elements', $wpdb->get_results($query), $this, false);
- apply_filters('sim-forms-transform-empty', $replaceValue, $this, $match);
- apply_filters('sim-forms-transform-array', implode(',', $replaceValue), $replaceValue, $this, $match);
- apply_filters('sim_before_saving_formdata', $this->submission->formresults, $this);
- apply_filters('sim_after_saving_formdata', $message, $this);
- apply_filters('sim_form_extra_js', '', $this->formName, false);
- apply_filters('sim_form_extra_js', '', $this->formName, true);
- apply_filters('sim_add_form_defaults', $this->defaultValues, $this->userId, $this->formName);
- apply_filters('sim_add_form_multi_defaults', $this->defaultArrayValues, $this->userId, $this->formName);
- apply_filters('sim_forms_load_userdata', $this->usermeta, $this->userId);
- apply_filters('sim-forms-element-html', $html, $element, $this);
- apply_filters('sim-form-element-html', $html, $element, $this);
- sim_form_extra_js
- sim-table-edit-permissions
- sim-table-view-permissions

# Actions
- do_action('sim-forms-after-email-triggers', $key, $email)