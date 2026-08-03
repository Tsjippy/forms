<?php
// This file is generated. Do not modify it manually.
return array(
	'datalist' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/datalist',
		'version' => '0.1.0',
		'title' => 'Datalist',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Datalist to be added to an form input',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'options' => array(
				'type' => 'string',
				'default' => ''
			),
			'options_dynamic' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	),
	'file' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/file',
		'version' => '0.1.0',
		'title' => 'File Upload Input',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'File upload input. Supports single and multiple file uploads. Can also be used to upload images.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'tsjippy_fileupload_script',
		'attributes' => array(
			'name' => array(
				'type' => 'string',
				'default' => ''
			),
			'disabled' => array(
				'type' => 'boolean',
				'default' => false
			),
			'multiple' => array(
				'type' => 'boolean',
				'default' => false
			),
			'required' => array(
				'type' => 'boolean',
				'default' => false
			),
			'targetDir' => array(
				'type' => 'string',
				'default' => ''
			),
			'userId' => array(
				'type' => 'boolean',
				'default' => false
			),
			'library' => array(
				'type' => 'boolean',
				'default' => false
			),
			'options' => array(
				'type' => 'object',
				'default' => array(
					
				)
			),
			'edit' => array(
				'type' => 'boolean',
				'default' => false
			),
			'metaKey' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	),
	'formbuilder' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/formbuilder',
		'version' => '0.1.0',
		'title' => 'Form Builder',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Form builder using blocks',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'tsjippy_forms_script',
		'attributes' => array(
			'method' => array(
				'type' => 'string',
				'default' => 'post'
			),
			'target' => array(
				'type' => 'string',
				'default' => '_self'
			),
			'autocomplete' => array(
				'type' => 'boolean',
				'default' => true
			),
			'submission_message' => array(
				'type' => 'string',
				'default' => 'Succesfully received your request'
			),
			'submission_id' => array(
				'type' => 'boolean',
				'default' => true
			),
			'name' => array(
				'type' => 'string',
				'default' => ''
			),
			'actions' => array(
				'type' => 'array',
				'default' => array(
					'archive',
					'delete'
				)
			),
			'user_meta' => array(
				'type' => 'boolean',
				'default' => false
			),
			'edit_roles' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'auto_archive_element' => array(
				'type' => 'string',
				'default' => ''
			),
			'auto_archive_value' => array(
				'type' => 'string',
				'default' => ''
			),
			'submission_roles' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'split_elements' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'step_amount' => array(
				'type' => 'integer',
				'default' => 0
			)
		)
	),
	'formstep' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/formstep',
		'version' => '0.1.0',
		'title' => 'Formstep element',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Allows splitting the form in steps',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'text' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	),
	'info' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/info',
		'version' => '0.1.0',
		'title' => 'Info element',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Shows an info icon and a message on hover',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'text' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	),
	'input' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/input',
		'version' => '0.1.0',
		'title' => 'Form Input',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Input element for a form',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'type' => array(
				'type' => 'string',
				'default' => ''
			),
			'name' => array(
				'type' => 'string',
				'default' => ''
			),
			'value' => array(
				'type' => 'string',
				'default' => ''
			),
			'inputAttributes' => array(
				'type' => 'object',
				'default' => array(
					
				)
			),
			'ariaAttributes' => array(
				'type' => 'boolean',
				'default' => false
			),
			'selectable_options' => array(
				'type' => 'string',
				'default' => ''
			),
			'selectable_options_dynamic' => array(
				'type' => 'string',
				'default' => ''
			),
			'dynamic_value' => array(
				'type' => 'string',
				'default' => ''
			),
			'add_button_content' => array(
				'type' => 'string',
				'default' => '+'
			),
			'remove_button_content' => array(
				'type' => 'string',
				'default' => '-'
			),
			'multiple' => array(
				'type' => 'boolean',
				'default' => false
			),
			'required' => array(
				'type' => 'boolean',
				'default' => false
			),
			'hasLabelParent' => array(
				'type' => 'boolean',
				'default' => false
			),
			'hide' => array(
				'type' => 'boolean',
				'default' => false
			),
			'reminders' => array(
				'type' => 'array',
				'default' => array(
					
				)
			)
		)
	),
	'label' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/label',
		'version' => '0.1.0',
		'title' => 'Form Input Label Wrapper',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Input element label for a form',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'text' => array(
				'type' => 'string',
				'default' => ''
			),
			'childAttr' => array(
				'type' => 'object',
				'default' => array(
					
				)
			)
		)
	),
	'multiwrap' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/multi-wrap',
		'version' => '0.1.0',
		'title' => 'Form Multi Input Wrapper',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Allows the submission of multiple elements which are grouped together. Children details for example where you can add multiple children.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'add_button_content' => array(
				'type' => 'string',
				'default' => '+'
			),
			'remove_button_content' => array(
				'type' => 'string',
				'default' => '-'
			)
		)
	),
	'select' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/select',
		'version' => '0.1.0',
		'title' => 'Select',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Selector dropdown',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'name' => array(
				'type' => 'string',
				'default' => ''
			),
			'options' => array(
				'type' => 'string',
				'default' => ''
			),
			'options_dynamic' => array(
				'type' => 'string',
				'default' => ''
			),
			'autofocus' => array(
				'type' => 'boolean',
				'default' => false
			),
			'disabled' => array(
				'type' => 'boolean',
				'default' => false
			),
			'multiple' => array(
				'type' => 'boolean',
				'default' => false
			),
			'required' => array(
				'type' => 'boolean',
				'default' => false
			),
			'dynamic_selected_value' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	)
);
