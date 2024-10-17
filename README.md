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