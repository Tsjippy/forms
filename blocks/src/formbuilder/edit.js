import { __ } from '@wordpress/i18n';
import {
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
	Inserter,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RadioControl,
	CheckboxControl,
	Button,
} from '@wordpress/components';
import { useState, useEffect, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { useSelect } from '@wordpress/data';
import { plus } from '@wordpress/icons';

import './editor.scss';
import './filters/addButtonToInnerBlocks.js';

/* Default inner block template for the form. */
const MY_TEMPLATE = [
	[
		'tsjippy-forms/input',
		{ type: 'submit', name: 'submit', value: 'Submit the form' },
	],
];

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

	/* Register the form if it has a name but has not been saved yet. */
	useEffect(() => {
		if (!name || id !== -1) {
			return;
		}

		apiFetch({
			path: `${tsjippy.restApiPrefix}/forms/register_form`,
			method: 'POST',
			data: {
				name,
			},
		}).then((res) => {
			if (res?.id) {
				setAttributes({
					id: res.id,
				});
			}
		});
	}, [name, id, setAttributes]);

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
	useSelect(
		(select) => select('core/block-editor').getBlocks(clientId),
		[clientId]
	);

	/* Block wrapper props. */
	const blockProps = useBlockProps();

	/* Configure inner blocks and custom appender. */
	const { children, ...innerBlocksProps } = useInnerBlocksProps(blockProps, {
		orientation: 'vertical',
		template: MY_TEMPLATE,
		renderAppender: () => (
			<Inserter
				rootClientId={clientId}
				isAppender
				renderToggle={({ onToggle }) => (
					<Button
						variant="primary"
						onClick={onToggle}
						icon={plus}
					>
						{__('Add More Form Blocks', 'tsjippy')}
					</Button>
				)}
			/>
		),
	});

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
	const getRoleCheckboxes = () => {
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
	const getActionCheckboxes = () => {
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

	/* Toggleable placeholder panels for additional form-related UI. */
	const resultingForm = () => {
		if (isEmailsFormVisible) {
			return (
				<div className="tsjippy-form-secondary-panel">
					<p>{__('Emails form is visible.', 'tsjippy')}</p>
				</div>
			);
		}

		if (isRemindersFormVisible) {
			return (
				<div className="tsjippy-form-secondary-panel">
					<p>{__('Reminders form is visible.', 'tsjippy')}</p>
				</div>
			);
		}

		return null;
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Form Settings', 'tsjippy')} initialOpen={true}>
					<RadioControl
						label={__('Form Method', 'tsjippy')}
						help={__(
							'The type of the form. Get adds values to the URL. Post submits invisibly.',
							'tsjippy'
						)}
						selected={method}
						options={[
							{ label: __('Get', 'tsjippy'), value: 'get' },
							{ label: __('Post', 'tsjippy'), value: 'post' },
						]}
						onChange={(nextMethod) => setAttributes({ method: nextMethod })}
					/>
				</PanelBody>

				<PanelBody title={__('Roles', 'tsjippy')} initialOpen={false}>
					{getRoleCheckboxes()}
				</PanelBody>

				<PanelBody title={__('Actions', 'tsjippy')} initialOpen={false}>
					{getActionCheckboxes()}
				</PanelBody>

				<PanelBody title={__('Extra Forms', 'tsjippy')} initialOpen={false}>
					<Button
						variant="secondary"
						onClick={() => setEmailsFormVisibility((prev) => !prev)}
					>
						{isEmailsFormVisible
							? __('Hide Emails Form', 'tsjippy')
							: __('Show Emails Form', 'tsjippy')}
					</Button>

					<Button
						variant="secondary"
						onClick={() => setRemindersFormVisibility((prev) => !prev)}
						style={{ marginLeft: '8px' }}
					>
						{isRemindersFormVisible
							? __('Hide Reminders Form', 'tsjippy')
							: __('Show Reminders Form', 'tsjippy')}
					</Button>
				</PanelBody>
			</InspectorControls>

			<div {...innerBlocksProps}>
				{resultingForm()}

				<InnerBlocks
					allowedBlocks={['tsjippy-forms/input', 'tsjippy-forms/label']}
					template={MY_TEMPLATE}
					renderAppender={InnerBlocks.ButtonBlockAppender}
				/>
			</div>
		</>
	);
}