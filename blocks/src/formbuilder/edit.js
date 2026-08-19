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
	Placeholder,
	SelectControl,
	FormTokenField,
	BaseControl
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
import { EmailSettings } from './emails/EmailSettings.js';

import * as forms from './../../../js/forms.js';

const TEMPLATE = [
    [
        'tsjippy-forms/input',
        {
            name: 'user-id',
			type: 'hidden',
            dynamicValue: 'user_id',
        },
    ],
];

/**
 * Gutenberg block edit component.
 * This is the editor-side UI for the form block.
 */
export default function Edit({ attributes, setAttributes, clientId }) {
	/* Local state for available roles and actions fetched from the API. */
	const [availableRoles, setAvailableRoles] = useState([]);
	const [availableActions, setAvailableActions] = useState([]);
	const [isEmailsFormVisible, setEmailsFormVisibility] = useState(false);
	const [isRemindersFormVisible, setRemindersFormVisibility] = useState(false);

	/**
	 * Store post id
	 */
	// get the id
    const currentPostId = useSelect( ( select ) => 
        select( 'core/editor' ).getCurrentPostId()
    , [] );

    // store in attributes
    useEffect( () => {
        if ( currentPostId && currentPostId !== attributes.postId ) {
            setAttributes( { postId: currentPostId } );
        }
    }, [ currentPostId, attributes.postId, setAttributes ] );

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
			const { getClientIdsOfDescendants } = select('core/block-editor');
			const ids = getClientIdsOfDescendants([clientId]);

			return ids.map((id) =>
				select('core/block-editor').getBlock(id)
			);
		},
		[clientId]
	);

	// Load all conditions once
	useSelect(
		(select) => select('tsjippy-forms/conditions-store').getFormConditions(attributes.postId),
		[attributes.postId]
	);

	/**
	 * The amount of formsteps in the form
	 */
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
			let nextRoles = Array.isArray(attributes.roles) ? [...attributes.roles] : [];

			if (checked) {
				if (!nextRoles.includes(roleSlug)) {
					nextRoles.push(roleSlug);
				}
			} else {
				nextRoles = nextRoles.filter((role) => role !== roleSlug);
			}

			setAttributes({ roles: nextRoles });
		},
		[attributes.roles, setAttributes]
	);

	/* Add or remove an action from the stored attributes. */
	const actionSelected = useCallback(
		(checked, action) => {
			let nextActions = Array.isArray(attributes.actions) ? [...attributes.actions] : [];

			if (checked) {
				if (!nextActions.includes(action)) {
					nextActions.push(action);
				}
			} else {
				nextActions = nextActions.filter((item) => item !== action);
			}

			setAttributes({ actions: nextActions });
		},
		[attributes.actions, setAttributes]
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
					checked={(attributes.roles || []).includes(roleSlug)}
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
					checked={(attributes.actions || []).includes(actionSlug)}
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
				selected = { attributes.method }
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
	}, [ formName, setAttributes, attributes.name ]);

	const inputBlocks = innerBlocks.filter((block) =>
		[
			'core/text-control',
			'core/select-control',
			'core/textarea-control',
			'core/radio',
			'core/checkbox',
			'core/toggle-control',
			'tsjippy-forms/input',
			'tsjippy-forms/select',
		].includes(block.name)
	);

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

				<PanelBody
					title={__('Auto Archive', 'tsjippy')}
					initialOpen={false}
				>
					<SelectControl
						label={__('Auto Archive Block', 'tsjippy')}
						value={attributes.auto_archive_element}
						options={[
							{
								label: __('Select a block', 'tsjippy'),
								value: '',
							},
							...inputBlocks.map((block) => ({
								label: block.attributes?.name ||
									block.name,
								value: block.attributes.blockId,
							})),
						]}
						onChange={(value) => setAttributes({ auto_archive_element: value })}
					/>

					<TextControl
						label    = {__('Auto Archive Value', 'tsjippy')}
						help	 = "You can use placeholders like %today%-2days"
						value    = { attributes.auto_archive_value }
						onChange = { ( value ) => setAttributes({ auto_archive_value: value })}
					/>
				</PanelBody>

				<PanelBody
					title={__('Split Entries', 'tsjippy')}
					initialOpen={false}
				>
					<SelectControl
						multiple
						label={__('Split Blocks', 'tsjippy')}
						help={__(
							'Form submission data will be split on the values of these inputs, while sharing the values of the other inputs.',
							'tsjippy'
						)}
						value={attributes.split_elements || []}
						options={inputBlocks.map((block) => ({
							label: block.attributes?.name || block.name,
							value: block.attributes?.blockId,
						}))}
						onChange={(values) =>
							setAttributes({
								split_elements: Array.isArray(values)
									? values
									: [values],
							})
						}
					/>
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

			<div {...blockProps}>
				{ 
					attributes.method == '' ? 
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
							<EmailSettings
								blockId={attributes.blockId}
								formElements={[]}
							/>
						:
							isRemindersFormVisible ? 
								<FormReminderPanel
									blockId= { attributes.blockId }
									saveInMeta = { attributes.user_meta }
								/>			
								:
								<>
									<InnerBlocks
										template={TEMPLATE}
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
			</div>
		</>
	);
}