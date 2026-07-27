import { __ } from '@wordpress/i18n';
import {
	InnerBlocks,
	useBlockProps,
	InspectorControls,
	Inserter,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RadioControl,
	CheckboxControl,
	Button,
	ToggleControl,
	TextControl,
	Placeholder
} from '@wordpress/components';
import { useState, useEffect, useCallback, RawHTML  } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { useSelect } from '@wordpress/data';
import { plus } from '@wordpress/icons';
import { blockDefault } from '@wordpress/icons';

import './editor.scss';
import './filters/addButtonToInnerBlocks.js';
import './filters/storeClientIdInAttributes.js';
import { FormSubmitter } from './components/Submitter.js';
import * as forms from './../../../js/forms.js';

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
 * Gutenberg block edit component.
 * This is the editor-side UI for the form block.
 */
export default function Edit({ attributes, setAttributes, clientId }) {
	const {
		name = '',
		id = -1,
		actions = [],
		roles = [],
		method = 'post',
	} = attributes;

	/* Local state for available roles and actions fetched from the API. */
	const [availableRoles, setAvailableRoles] = useState([]);
	const [availableActions, setAvailableActions] = useState([]);
	const [isEmailsFormVisible, setEmailsFormVisibility] = useState(false);
	const [isRemindersFormVisible, setRemindersFormVisibility] = useState(false);

	/* Load available roles from the server for the inspector panel. */
	useEffect(() => {
		apiFetch({
			path: `${tsjippy.restApiPrefix}/forms/get_roles`,
			method: 'POST',
		}).then((res) => {
			setAvailableRoles(Array.isArray(res) ? res : []);
		});
	}, []);

	/* Load available actions from the server for the inspector panel. */
	useEffect(() => {
		apiFetch({
			path: `${tsjippy.restApiPrefix}/forms/get_form_actions`,
			method: 'POST',
		}).then((res) => {
			setAvailableActions(Array.isArray(res) ? res : []);
		});
	}, []);

	/* Read inner blocks so the editor can inspect nested form elements if needed. */
	const innerBlocks = useSelect(
		(select) => {
			const block = select('core/block-editor').getBlock(clientId);
			return block?.innerBlocks || [];
		},
		[clientId]
	);

    const stepAmount	= innerBlocks?.filter(
        block => block.name === 'tsjippy-forms/formstep'
    ).length || 0;

	useEffect(() => {
		if (attributes.step_amount !== stepAmount) {
			setAttributes({ step_amount: stepAmount });
		}
	}, [stepAmount, attributes.step_amount, setAttributes]);


	/* Block wrapper props. */
	const blockProps = useBlockProps();

	/* Add or remove a role from the stored attributes. */
	const onRoleSelected = useCallback(
		(checked, roleSlug) => {
			let nextRoles = Array.isArray(roles) ? [...roles] : [];

			if (checked) {
				if (!nextRoles.includes(roleSlug)) {
					nextRoles.push(roleSlug);
				}
			} else {
				nextRoles = nextRoles.filter((role) => role !== roleSlug);
			}

			setAttributes({ roles: nextRoles });
		},
		[roles, setAttributes]
	);

	/* Add or remove an action from the stored attributes. */
	const actionSelected = useCallback(
		(checked, action) => {
			let nextActions = Array.isArray(actions) ? [...actions] : [];

			if (checked) {
				if (!nextActions.includes(action)) {
					nextActions.push(action);
				}
			} else {
				nextActions = nextActions.filter((item) => item !== action);
			}

			setAttributes({ actions: nextActions });
		},
		[actions, setAttributes]
	);

	/* Build role checkboxes for the inspector panel. */
	const RoleCheckboxes = () => {
		if (!availableRoles.length) {
			return <p>{__('No roles available.', 'tsjippy')}</p>;
		}

		return availableRoles.map((role) => {
			const roleSlug = role.slug || role.value || role;
			const roleLabel = role.label || role.name || roleSlug;

			return (
				<CheckboxControl
					key={roleSlug}
					label={roleLabel}
					checked={(roles || []).includes(roleSlug)}
					onChange={(checked) => onRoleSelected(checked, roleSlug)}
				/>
			);
		});
	};

	/* Build action checkboxes for the inspector panel. */
	const ActionCheckboxes = () => {
		if (!availableActions.length) {
			return <p>{__('No actions available.', 'tsjippy')}</p>;
		}

		return availableActions.map((action) => {
			const actionSlug = action.slug || action.value || action;
			const actionLabel = action.label || action.name || actionSlug;

			return (
				<CheckboxControl
					key={actionSlug}
					label={actionLabel}
					checked={(actions || []).includes(actionSlug)}
					onChange={(checked) => actionSelected(checked, actionSlug)}
				/>
			);
		});
	};

	const FormMethodComponent = (props) => {
		return (
			<RadioControl
				label={__('Form Method', 'tsjippy')}
				help={__(
					'The type of the form. Get adds values to the URL. Post submits invisibly.',
					'tsjippy'
				)}
				selected = { method }
				options={[
					{ label: __('Get', 'tsjippy'), value: 'get' },
					{ label: __('Post', 'tsjippy'), value: 'post' },
				]}
				onChange={(nextMethod) => setAttributes({ method: nextMethod })}
			/>
		)
	}

	/**
	 * Set a debounce for the formname input so it disappears when we stop typing, not straight after the first character
	 */
	const [formName, setFormName] = useState(attributes.name);

	useEffect(() => {
		const timeoutId = setTimeout(() => {
			setAttributes({ name: formName })
		}, 800);

		return () => clearTimeout(timeoutId);
	}, [formName, 800]);

	/**
	 * Return HTML
	 */
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Form Settings', 'tsjippy')} initialOpen={true}>
					<FormMethodComponent />

					<TextControl
						label    = "Form Name"
						value    = { formName }
						onChange = { ( value ) => setFormName(value) }
					/>

					<RadioControl
						label    = "Form Target"
						help     = "Target location for the form response"
						selected = { attributes.target }
						options  = { [
							{ label: 'New Tab', value: '_blank' },
							{ label: 'Current page', value: '_self' },
							{ label: 'Parent Frame', value: '_parent' },
							{ label: 'In the body', value: '_top' },
							{ label: 'iframe', value: 'iframe' }
						] }
						onChange = { ( target ) => setAttributes({ target: target })}
					/>

					<ToggleControl
						label    = {__("Enable autocomplete", "tsjippy")}
						checked  = {!!attributes.autocomplete}
						onChange = {() => setAttributes({ autocomplete: !attributes.autocomplete }) }
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
				</PanelBody>

				<PanelBody title={__('Roles', 'tsjippy')} initialOpen={false}>
					<RoleCheckboxes />
				</PanelBody>

				<PanelBody title={__('Actions', 'tsjippy')} initialOpen={false}>
					<ActionCheckboxes />
				</PanelBody>

				<PanelBody title={__('E-mail Settings', 'tsjippy')} initialOpen={false} onToggle={() => setEmailsFormVisibility((prev) => !prev)}>
					{isEmailsFormVisible
						? __('Hide Emails Form', 'tsjippy')
						: __('Show Emails Form', 'tsjippy')}
				</PanelBody>

				<PanelBody title={__('Form Reminders', 'tsjippy')} initialOpen={false} onToggle={ () => setRemindersFormVisibility((prev) => !prev)}>
					{isRemindersFormVisible
						? __('Hide Reminders Form', 'tsjippy')
						: __('Show Reminders Form', 'tsjippy')}
				</PanelBody>
			</InspectorControls>

			<fieldset { ...blockProps }  key="main_form_fieldset">
				<legend>{ attributes.name } Form</legend>

				{ 
					method == '' ? 
					<>
					<FormMethodComponent /> 
					<br></br>
					</>:

						attributes.name == '' ?
							<TextControl
								label    = "Form Name"
								value    = { formName }
								onChange = { ( value ) => setFormName(value) }
							/>
						: 
						
						isEmailsFormVisible ? 
							<div { ...blockProps }><RawHTML> { emailsForm } </RawHTML></div> 
						:
							isRemindersFormVisible ? 
								<div { ...blockProps }><RawHTML> { formRemindersForm } </RawHTML></div> 
							:
								<>
									<InnerBlocks
										renderAppender={false}
									/>

									<FormSubmitter
										attributes={attributes}
									/>
									
									<Inserter
										rootClientId={clientId}
										isAppender
										renderToggle={({ onToggle }) => (
											<Button
												variant="primary"
												onClick={onToggle}
												icon={plus}
											>
												{__('Add Form Blocks', 'tsjippy')}
											</Button>
										)}
									/>
								</>
				}
			</fieldset>
		</>
	);
}