import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { RadioControl, PanelBody, Button, Popover, TextControl, ToggleControl, CheckboxControl, SelectControl } from '@wordpress/components';
import { useState, useEffect } from 'react';
import apiFetch from "@wordpress/api-fetch";
import { RawHTML, Fragment } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { store as blockEditorStore } from '@wordpress/block-editor';
import './editor.scss';
import './innerblock_filter.js';


const MY_TEMPLATE = [
	[ 'tsjippy-forms/input', { type: 'number', name: 'user-id' } ],
	[ 'tsjippy-forms/input', { type: 'submit', name: 'submit', value: 'Submit the form'} ],
];

var formRemindersForm = '';
document.addEventListener("DOMContentLoaded", () => {
	apiFetch({
		path: tsjippy.restApiPrefix + `/forms/get_form_reminder_form`,
		method: "POST",
	}).then((res) => {
		formRemindersForm = res;
	});
});

var emailsForm = '';
document.addEventListener("DOMContentLoaded", () => {
	apiFetch({
		path: tsjippy.restApiPrefix + `/forms/get_emails_form`,
		method: "POST",
	}).then((res) => {
		emailsForm = res;
	});
});

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes, clientId, isSelected }) {
	/**
	 * Register the form if not done yet
	 */
	if(attributes.name != '' && attributes.id == -1){
		apiFetch({
			path: tsjippy.restApiPrefix + `/forms/register_form`,
			method: "POST",
			data: {
				slug: attributes.name
			},
		}).then((res) => {
			setAttributes({ id: res });
		});
	}

	const blockProps = useBlockProps();
    const { children, ...innerBlocksProps }  = useInnerBlocksProps( 
		blockProps, 
		{
			template: MY_TEMPLATE,
			templateInsertUpdatesSelection: true
		}
	);

	// Get roles
	const [availableRoles, setAvailableRoles] = useState([]);
	useEffect(() => {
		apiFetch({
			path: tsjippy.restApiPrefix + `/forms/get_roles`,
			method: "POST",
		}).then((res) => {
			setAvailableRoles(res);
		});
	}, []);

	/**
	 * Actions
	 */
	// Get available actions
	const [availableActions, setAvailableActions] = useState([]);
	useEffect(() => {
		apiFetch({
			path: tsjippy.restApiPrefix + `/forms/get_form_actions`,
			method: "POST",
		}).then((res) => {
			setAvailableActions(res);
		});
	}, []);

	// Build the checkboxes
	const getActionCheckboxes = () => {
		return [
			<b>Select available actions for form submission data</b>,
			availableActions.map((action) => {
				return (
					<CheckboxControl
						key      = {action}
						label    = {action}
						onChange = {(checked) => actionSelected( checked, action ) }
						checked  = {attributes.actions.indexOf(action) > -1}
					/>
				);
			}),
		];
	};

	// Store the settings
	const actionSelected = function (checked, action) {
      let actions = attributes.actions;

      // An action just got selected
      if (checked) {
        // Add to stored roles
        actions.push(action);
      } else {
        // remove from array
        actions = actions.filter((p) => {
          return p != action;
        });
      }

      // Store in Attributes
      // We need to set a new array to trigger a re-render
      setAttributes({ actions: [...actions] });
    };

	// Stores whetther to show the forms or the main form
	const [ isEmailsFormVisible, setEmailsFormVisibility ] = useState( false );
	const [ isRemindersFormVisible, setRemindersFormVisibility ] = useState( false );

	/**
	 * ROLES
	 */
	/**
     * Runs when a role gets (de)selected
     * @param {bool} checked true when selected, false otherwise
     */
    const onRoleSelected = function (checked, roleSlug) {
      let roles = attributes.roles;

      // A role just got selected
      if (checked) {
        // Add to stored roles
        roles.push(roleSlug);
      } else {
        // remove from array
        roles = roles.filter((p) => {
          return p != roleSlug;
        });
      }

      // Store in Attributes
      // Store as a new array to trigger a new render
      setAttributes({ roles: [...roles] });

    };

	/**
	 * Get form elements as select options
	 */
	const innerBlocks = useSelect((select) => 
		select('core/block-editor').getBlocks(clientId)
	, [clientId]);

	const getFormElements = () => {

		let blockNames	= [];

		innerBlocks.map((block) => {
			blockNames.push( { label: block.attributes.name, value: block.attributes.name });
		});

		return blockNames;
	}

	const getSplitElements = () => {
		let splittable	= [];

		innerBlocks.map((block) => {
			if( block.attributes.name != undefined && (block.attributes.name).search(/\[[\d*]*\]/) > -1 ){
				splittable.push( { label: block.attributes.name, value: block.attributes.name });
			}
		});

		if(splittable.length === 0){
			return;
		}

		return (
			<PanelBody title={__('Formdata Splitting', 'tsjippy')} initialOpen={false}>
				<SelectControl
					__next40pxDefaultSize = {true}
					multiple
					label    = { __("Split Form Submissions on these input values") }
					value    = { attributes.split_elements }
					options  = { splittable }
					onChange = { ( blockName ) => setAttributes({ split_elements: blockName })}
				/>
			</PanelBody>
		)
	}

	const resultingForm = () => {
		if(isEmailsFormVisible){
			return (<RawHTML> { emailsForm } </RawHTML>);
		}

		else if(isRemindersFormVisible){
			return (<RawHTML> { formRemindersForm } </RawHTML>);
		}

		return(
			<form {...innerBlocksProps} style={{border: 'solid' }} >
				{ children }
			</form>
		);
	}

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Form Settings', 'tsjippy')}>
				<RadioControl
					label    = "Form type"
					help     = "The type of the form, get adds all form values to the url, post is invisble"
					selected = { attributes.type }
					options  = { [
						{ label: 'Get', value: 'get' },
						{ label: 'Post', value: 'post' },
					] }
					onChange = { ( type ) => setAttributes({ type: type })}
				/>

				<TextControl
					label    = "Form Name"
					value    = { attributes.name }
					onChange = { ( value ) => setAttributes({ name: value })}
				/>

				<TextControl
					label    = "Submission Message"
					value    = { attributes.submission_message }
					onChange = { ( value ) => setAttributes({ submission_message: value })}
				/>

				<ToggleControl
					label    = {__("Include submission ID in message", "tsjippy")}
					checked  = {!!attributes.submission_id}
					onChange = {() => setAttributes({ submission_id: !attributes.submission_id }) }
				/>

				<ToggleControl
					label    = {__("Save submissions in usermeta table", "tsjippy")}
					checked  = {!!attributes.user_meta}
					onChange = {() => setAttributes({ user_meta: !attributes.user_meta }) }
				/>
				{ getActionCheckboxes() }
			</PanelBody>

			<PanelBody title={__('Form Permissions', 'tsjippy')} initialOpen={false}>
				<SelectControl
					__next40pxDefaultSize = {true}
					multiple
					label    = { __("Select roles or users with form edit rights") }
					value    = { attributes.edit_roles }
					options  = { availableRoles }
					onChange = { ( roles ) => setAttributes({ edit_roles: roles })}
				/>

				<SelectControl
					__next40pxDefaultSize = {true}
					multiple
					label    = { __("Select roles who can submit the form on behalve of somebody else") }
					value    = { attributes.submission_roles }
					options  = { availableRoles }
					onChange = { ( roles ) => setAttributes({ submission_roles: roles })}
				/>
			</PanelBody>

			<PanelBody title={__('Form Submission Archive Settings', 'tsjippy')} initialOpen={false}>
				
				<SelectControl
					__next40pxDefaultSize = {true}
					multiple
					label    = { __("Auto archive a (sub) entry when field") }
					value    = { attributes.auto_archive_element }
					options  = { getFormElements() }
					onChange = { ( blockName ) => setAttributes({ auto_archive_element: blockName })}
				/>
				
				<TextControl
					label    = "equals (A fixed value or you can use placeholders like ‘%today%+3days’ for a value)"
					value    = { attributes.auto_archive_value }
					onChange = { ( value ) => setAttributes({ auto_archive_value: value })}
				/>
			</PanelBody>

			{ getSplitElements() }

			<PanelBody title={__('Form E-mails', 'tsjippy')} initialOpen={false} onToggle={(value) => setEmailsFormVisibility(value)}>
				<p>Close this to hide the e-mails form again</p>
			</PanelBody>

			<PanelBody title={__('Form Reminders', 'tsjippy')} initialOpen={false} onToggle={(value) => setRemindersFormVisibility(value)}>
				<p>Close this to hide the reminders form again</p>
			</PanelBody>

		</InspectorControls>

		{ resultingForm() }
		</>
	);
}
