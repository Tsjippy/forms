<?php
// This file is generated. Do not modify it manually.
return array(
	'formbuilder' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/formbuilder',
		'version' => '0.1.0',
		'title' => 'Form Builder Test',
		'category' => 'widgets',
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
			'type' => array(
				'type' => 'string',
				'default' => 'post'
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
	'input' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-forms/input',
		'version' => '0.1.0',
		'title' => 'Form Input Element',
		'category' => 'widgets',
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
			)
		)
	)
);
