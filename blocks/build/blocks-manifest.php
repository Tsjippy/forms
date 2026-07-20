<?php
// This file is generated. Do not modify it manually.
return array(
	'formbuilder' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/formbuilder',
		'version' => '0.1.0',
		'title' => 'Form Builder Test',
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
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'id' => array(
				'type' => 'integer',
				'default' => -1
			),
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
				'default' => true
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
	'formstep-controls' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/formstep-controls',
		'version' => '0.1.0',
		'title' => 'Formstep Controls Element',
		'category' => 'form-elements',
		'icon' => 'forms',
		'description' => 'Shows the formstep controls and a previous and next button',
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
			'amount' => array(
				'type' => 'integer',
				'default' => 0
			)
		)
	),
	'input' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/input',
		'version' => '0.1.0',
		'title' => 'Form Input Element',
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
				'default' => 'text'
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
			)
		)
	),
	'label' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/label',
		'version' => '0.1.0',
		'title' => 'Form Input Element Label',
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
			)
		)
	)
);
