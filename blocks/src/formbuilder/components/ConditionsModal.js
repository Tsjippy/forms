import { __, sprintf } from '@wordpress/i18n';
import { Button, Spinner, Notice, SelectControl, TextControl, __experimentalNumberControl as NumberControl, ComboboxControl } from '@wordpress/components';
import {
	useEffect,
	useMemo,
	useRef,
	useState,
	useCallback,
	createPortal,
} from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { useBlocksAsSelectOptions } from '../hooks/useBlocksAsSelectOptions.js';
import {
	plus,
	trash,
	undo
} from '@wordpress/icons';
import apiFetch from '@wordpress/api-fetch';

import RuleRow from './RuleRow';
import { inputSchema } from './../../input/components/block_attributes.js';

/**
 * Create a blank condition object.
 */
function createEmptyRule() {
	return {
		'conditional-field': '',
		'equation': '',
		'conditional-value': '',
		'combinator': '',
		'conditional-field-2': '',
		'equation-2': '',
	};
}

/**
 * Create a blank action object.
 */
function createEmptyAction() {
	return {
		'targets': [],
		'action': '',
		'property-name': '',
		'property-value': '',
		'property-name1': '',
		'action-value': '',
		'addition': '',
	};
}

/**
 * Deep clone a plain object/array.
 */
function deepClone(value) {
	return JSON.parse(JSON.stringify(value || {}));
}

/**
 * Check whether an equation requires a value.
 */
function isEquationRequiringValue(equation) {
	return [
		'>',
		'<',
		'+',
		'-',
	].includes(equation);
}

/**
 * Validate the current conditions object.
 */
function validateConditions(conditions, setFieldErrors) {
	const errors = [];
	const fieldErrors = [{
		rules: [{}],
		actions: [{}],
	}];

	const firstErrorTarget = {
		section: null,
		conditionIndex: null,
		ruleIndex: null,
		actionIndex: null,
		fieldKey: null,
	};

	if (!Array.isArray(conditions) || conditions.length === 0) {
		return {
			errors,
			fieldErrors,
			firstErrorTarget,
		};
	}

	conditions = Array.isArray(conditions) ? conditions : [];

	/**
	 * Loop over all conditions
	 */
	conditions.forEach((condition, conditionIndex) => {
		if (!Array.isArray(condition.rules)) {
			errors.push(
				sprintf(
					__('Condition %d must contain at least one rule.', 'tsjippy'),
					conditionIndex + 1
				)
			);

			if (firstErrorTarget.section === null) {
				firstErrorTarget.section = 'rules';
				firstErrorTarget.conditionIndex = conditionIndex;
				firstErrorTarget.ruleIndex = 0;
				firstErrorTarget.fieldKey = 'conditionalField';
			}

			return;
		}

		if (condition.rules.length > 0) {
			if (!Array.isArray(condition.actions) || condition.actions.length === 0) {
				errors.push(
					sprintf(
						__('Condition %d must contain at least one action.', 'tsjippy'),
						conditionIndex + 1
					)
				);

				if (firstErrorTarget.section === null) {
					firstErrorTarget.section = 'actions';
					firstErrorTarget.conditionIndex = conditionIndex;
					firstErrorTarget.ruleIndex = 0;
					firstErrorTarget.fieldKey = 'conditionalField';
				}
			}
		}

		/**
		 * Loop over all rules of this condition
		 * And check validity
		 */
		condition.rules.forEach((rule, ruleIndex) => {

			((fieldErrors[conditionIndex] ||= {}).rules ||= [])[ruleIndex] ||= {};

			const ruleErrors = {};

			if (!rule?.['conditional-field']) {
				ruleErrors.conditionalField = __('Select an block.', 'tsjippy');

				if (firstErrorTarget.section === null) {
					firstErrorTarget.section = 'rules';
					firstErrorTarget.conditionIndex = conditionIndex;
					firstErrorTarget.ruleIndex = ruleIndex;
					firstErrorTarget.fieldKey = 'conditionalField';
				}
			}

			if (!rule?.equation) {
				ruleErrors.equation = __('Select an equation.', 'tsjippy');

				if (firstErrorTarget.section === null) {
					firstErrorTarget.section = 'rules';
					firstErrorTarget.conditionIndex = conditionIndex;
					firstErrorTarget.ruleIndex = ruleIndex;
					firstErrorTarget.fieldKey = 'equation';
				}
			}

			if (
				isEquationRequiringValue(rule?.equation) &&
				(
					rule?.['conditional-value'] === undefined ||
					rule?.['conditional-value'] === null ||
					rule?.['conditional-value'].trim() === ''
				)
			) {
				ruleErrors.conditionalValue = __('Enter a value.', 'tsjippy');

				if (firstErrorTarget.section === null) {
					firstErrorTarget.section = 'rules';
					firstErrorTarget.conditionIndex = conditionIndex;
					firstErrorTarget.ruleIndex = ruleIndex;
					firstErrorTarget.fieldKey = 'conditionalValue';
				}
			}

			if (rule?.equation === '+' || rule?.equation === '-') {
				if (!rule?.['conditional-field-2']) {
					ruleErrors.conditionalField2 = __(
						'Select a second block.',
						'tsjippy'
					);

					if (firstErrorTarget.section === null) {
						firstErrorTarget.section = 'rules';
						firstErrorTarget.conditionIndex = conditionIndex;
						firstErrorTarget.ruleIndex = ruleIndex;
						firstErrorTarget.fieldKey = 'conditionalField2';
					}
				}

				if (!rule?.['equation-2']) {
					ruleErrors.equation2 = __(
						'Select a second equation.',
						'tsjippy'
					);

					if (firstErrorTarget.section === null) {
						firstErrorTarget.section = 'rules';
						firstErrorTarget.conditionIndex = conditionIndex;
						firstErrorTarget.ruleIndex = ruleIndex;
						firstErrorTarget.fieldKey = 'equation2';
					}
				}
			}

			if (Object.keys(ruleErrors).length > 0) {
				fieldErrors[conditionIndex].rules[ruleIndex] = ruleErrors;
				errors.push(
					sprintf(
						__('Condition %1$d, rule \%2$d has validation errors.', 'tsjippy'),
						conditionIndex + 1,
						ruleIndex + 1
					)
				);
			}
		});

		/**
		 * Loop over all actions of this condition
		 * And check validity
		 */
		(condition.actions || []).forEach((actionItem, actionIndex) => {
			const actionErrors = {};

			if (!actionItem?.action) {
				actionErrors.action = __('Select an action.', 'tsjippy');

				if (firstErrorTarget.section === null) {
					firstErrorTarget.conditionIndex = conditionIndex;
					firstErrorTarget.section = 'actions';
					firstErrorTarget.actionIndex = actionIndex;
					firstErrorTarget.fieldKey = 'action';
				}
			}

			if (actionItem?.action === 'set-property') {
				if (!actionItem?.['property-name']) {
					actionErrors.propertyName = __('Enter a property name.', 'tsjippy');

					if (firstErrorTarget.section === null) {
						firstErrorTarget.conditionIndex = conditionIndex;
						firstErrorTarget.section = 'actions';
						firstErrorTarget.actionIndex = actionIndex;
						firstErrorTarget.fieldKey = 'propertyName';
					}
				}
			}

			if (Object.keys(actionErrors).length > 0) {
				((fieldErrors[conditionIndex] ||= {}).actions ||= [])[actionIndex] ||= {};
				fieldErrors[conditionIndex].actions[actionIndex] = actionErrors;
				errors.push(
					sprintf(
						__('Condition %1$d, action %d has validation errors.', 'tsjippy'),
						conditionIndex + 1,
						actionIndex + 1
					)
				);
			}
		});
	});

	setFieldErrors(fieldErrors);

	return {
		errors,
		fieldErrors,
		firstErrorTarget,
	};
}

/**
 * Conditions modal UI.
 */
export default function ConditionsModal({
	isVisible,
	onClose,
	blockId,
	allNestedBlocks,
	blockProps = {}
}) {
	const { setCondition } = useDispatch(
		'tsjippy-forms/conditions-store'
	);

	const { updateBlockAttributes } = useDispatch('core/block-editor');
	const { createSuccessNotice, createErrorNotice } = useDispatch('core/notices');

	const conditions = useSelect(
		(select) => select('tsjippy-forms/conditions-store')?.getConditions(blockId),
		[blockId]
	);

	const [draftConditions, setDraftConditions] = useState([]);
	const [successMessage, setSuccessMessage] = useState('');
	const [isSaving, setIsSaving] = useState(false);
	const [fieldErrors, setFieldErrors] = useState({});
	const [focusTarget, setFocusTarget] = useState(null);
	const [pulseTarget, setPulseTarget] = useState(null);
	const [filterValue, setFilterValue] = useState('');

	const formBlockOptions = useBlocksAsSelectOptions(allNestedBlocks, blockId);
	const modalRef = useRef(null);
	const previousBodyOverflow = useRef('');

	useEffect(() => {
		if (isVisible && Array.isArray(conditions)) {
			setDraftConditions(deepClone(conditions));
		}
	}, [isVisible, conditions]);

	useEffect(() => {
		if (!successMessage) {
			return;
		}

		const timer = window.setTimeout(() => {
			setSuccessMessage('');
		}, 3000);

		return () => window.clearTimeout(timer);
	}, [successMessage]);

	useEffect(() => {
		if (!isVisible || typeof document === 'undefined') {
			return;
		}

		previousBodyOverflow.current = document.body.style.overflow;
		document.body.style.overflow = 'hidden';

		return () => {
			document.body.style.overflow = previousBodyOverflow.current || '';
		};
	}, [isVisible]);

	const handleClose = useCallback(() => {
		const isDirty =
			JSON.stringify(draftConditions) !== JSON.stringify(conditions);

		if (isDirty) {
			const ok = window.confirm(
				__('You have unsaved changes. Close without saving?', 'tsjippy')
			);

			if (!ok) {
				return;
			}
		}

		onClose();
	}, [draftConditions, conditions, onClose]);

	const handleOverlayClick = useCallback(() => {
		handleClose();
	}, [handleClose]);

	const stopPropagation = useCallback((event) => {
		event.stopPropagation();
	}, []);

	useEffect(() => {
		if (!isVisible) {
			return;
		}

		const handleKeyDown = (event) => {
			if (event.key === 'Escape') {
				handleClose();
			}
		};

		window.addEventListener('keydown', handleKeyDown);

		return () => {
			window.removeEventListener('keydown', handleKeyDown);
		};
	}, [isVisible, handleClose]);

	useEffect(() => {
		if (!focusTarget || !modalRef.current || !focusTarget.section) {
			return;
		}

		const { section, conditionIndex, ruleIndex, actionIndex, fieldKey } = focusTarget;

		let selector = '';

		if (section === 'rules') {
			selector = `[data-rule-index="${ruleIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] input,
				[data-rule-index="${ruleIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] select,
				[data-rule-index="${ruleIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] textarea`;
		}

		if (section === 'actions') {
			selector = `[data-action-index="${actionIndex}"] [data-field-key="${fieldKey}"] input,
				[data-action-index="${actionIndex}"] [data-field-key="${fieldKey}"] select,
				[data-action-index="${actionIndex}"] [data-field-key="${fieldKey}"] textarea`;
		}

		const field = modalRef.current.querySelector(selector);

		if (field && typeof field.focus === 'function') {
			window.requestAnimationFrame(() => {
				field.focus();
				field.scrollIntoView({
					behavior: 'smooth',
					block: 'center',
				});

				setPulseTarget(focusTarget);

				window.setTimeout(() => {
					setPulseTarget(null);
				}, 1600);
			});
		}

		setFocusTarget(null);
	}, [focusTarget]);

	const validation = useMemo(() => {
		return validateConditions(draftConditions, setFieldErrors);
	}, [draftConditions]);

	const isValid = validation.errors.length === 0;

	const isDirty = useMemo(() => {
		return JSON.stringify(draftConditions) !== JSON.stringify(conditions);
	}, [draftConditions, conditions]);

	const clearSuccessMessage = useCallback(() => {
		setSuccessMessage('');
	}, []);

	const resetErrors = useCallback(() => {
		clearSuccessMessage();
		setFieldErrors({});
	}, [clearSuccessMessage]);

	const showToastSuccess = useCallback(
		(message) => {
			createSuccessNotice(message, {
				type: 'snackbar',
				isDismissible: true,
			});
		},
		[createSuccessNotice]
	);

	const showToastError = useCallback(
		(message) => {
			createErrorNotice(message, {
				type: 'snackbar',
				isDismissible: true,
			});
		},
		[createErrorNotice]
	);

	const addCondition = useCallback(() => {
		resetErrors();

		setDraftConditions((prev) => {
			const next = deepClone(prev);

			const newCondition = next[0]
				? deepClone(next[0])
				: {
					rules: [createEmptyRule()],
					actions: [createEmptyAction()],
				};
			newCondition.rules = [createEmptyRule()];
			newCondition.actions = [createEmptyAction()];
			newCondition.id = undefined;

			next.push(newCondition);

			return next;
		});
	}, [resetErrors]);

	const updateRuleCondition = useCallback(
		(conditionIndex, ruleIndex, key, value) => {
			setDraftConditions((prev) => {
				const next = deepClone(prev);

				if (!next[conditionIndex]) {
					next[conditionIndex] = [];
				}

				if (!next[conditionIndex].rules) {
					next[conditionIndex].rules = [];
				}

				if (!next[conditionIndex].actions) {
					next[conditionIndex].actions = [];
				}

				if (!next[conditionIndex].rules[ruleIndex]) {
					next[conditionIndex].rules[ruleIndex] = createEmptyRule();
				}

				next[conditionIndex].rules[ruleIndex][key] = value;

				if (
					key === 'combinator' &&
					!next[conditionIndex].rules[ruleIndex + 1]
				) {
					next[conditionIndex].rules[ruleIndex + 1] = createEmptyRule();
				}

				return next;
			});

			resetErrors();
		},
		[resetErrors]
	);

	const addRule = useCallback((conditionIndex) => {
		resetErrors();

		setDraftConditions((prev) => {
			const next = deepClone(prev);
			next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];
			next[conditionIndex].rules.push(createEmptyRule());
			return next;
		});
	}, [resetErrors]);

	const addReverseCondition = useCallback(
		(conditionIndex) => {
			resetErrors();

			setDraftConditions((prev) => {
				const next = deepClone(prev);

				next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];
				next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];

				let clone = deepClone(next[conditionIndex]);
				clone.id = undefined;

				const reverseOperators = {
					'==': '!=',
					'!=': '==',
					'>': '<',
					'<': '>',
					'checked': '!checked',
					'!checked': 'checked',
					'== value': '!= value',
					'!= value': '== value',
					'> value': '< value',
					'< value': '> value',
					'visible': 'invisible',
					'invisible': 'visible',
					'+': '-',
					'-': '+'
				};

				clone.rules.forEach(rule => {
					rule['equation'] = reverseOperators[rule['equation']];
				});

				clone.actions.forEach(action => {
					if (action.action === 'show') {
						action.action = 'hide';
					} else if (action.action === 'hide') {
						action.action = 'show';
					}
				});

				next.splice(conditionIndex + 1, 0, clone);

				return next;
			});
		},
		[resetErrors]
	);

	const deleteCondition = useCallback(
		(conditionIndex) => {
			resetErrors();

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.splice(conditionIndex, 1);
				return next;
			});
		},
		[resetErrors]
	);

	const deleteRule = useCallback(
		(conditionIndex, ruleIndex) => {
			resetErrors();

			setDraftConditions((prev) => {
				const next = deepClone(prev);

				if (!next[conditionIndex].rules) {
					return next;
				}

				next[conditionIndex].rules.splice(ruleIndex, 1);

				return next;
			});
		},
		[resetErrors]
	);

	const moveRule = useCallback(
		(conditionIndex, ruleIndex, direction) => {
			resetErrors();

			setDraftConditions((prev) => {
				const next = deepClone(prev);

				next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];

				const targetIndex = ruleIndex + direction;

				if (targetIndex < 0 || targetIndex >= next[conditionIndex].rules.length) {
					return next;
				}

				const temp = next[conditionIndex].rules[ruleIndex];
				next[conditionIndex].rules[ruleIndex] = next[conditionIndex].rules[targetIndex];
				next[conditionIndex].rules[targetIndex] = temp;

				return next;
			});
		},
		[resetErrors]
	);

	const addAction = useCallback((conditionIndex) => {
		resetErrors();

		setDraftConditions((prev) => {
			const next = deepClone(prev);

			next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];
			next[conditionIndex].actions.push(createEmptyAction());

			return next;
		});
	}, [resetErrors]);

	const updateAction = useCallback(
		(conditionIndex, actionIndex, key, value) => {
			setDraftConditions((prev) => {
				const next = deepClone(prev);

				next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];

				if (!next[conditionIndex].actions[actionIndex]) {
					next[conditionIndex].actions[actionIndex] = createEmptyAction();
				}

				next[conditionIndex].actions[actionIndex][key] = value;

				return next;
			});

			resetErrors();
		},
		[resetErrors]
	);

	const deleteAction = useCallback(
		(conditionIndex, actionIndex) => {
			resetErrors();

			setDraftConditions((prev) => {
				const next = deepClone(prev);

				next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];
				next[conditionIndex].actions.splice(actionIndex, 1);
				return next;
			});
		},
		[resetErrors]
	);

	const postId = useSelect((select) =>
		select('core/editor')?.getCurrentPostId?.(),
		[]
	);

	const targetPostId = blockProps?.attributes?.postId || postId;

	const saveConditionsRequest = useCallback(async (targetBlockId, conditionsToSave, props) => {
		const savedConditions = await apiFetch({
			path: `tsjippy/v2/forms/save_block_conditions`,
			method: 'POST',
			data: {
				postId: targetPostId,
				blockId: targetBlockId,
				conditions: conditionsToSave,
			},
		});

		if (props?.clientId) {
			updateBlockAttributes(props.clientId, {
				version: (props.attributes?.version || 0) + 1
			});
		}

		return savedConditions;
	}, [targetPostId, updateBlockAttributes]);

	const isLoading = useSelect(
		(select) => select('tsjippy-forms/conditions-store')?.isLoading?.(targetPostId) ?? false,
		[targetPostId]
	);

	const error = useSelect(
		(select) => select('tsjippy-forms/conditions-store')?.getError?.(targetPostId) ?? null,
		[targetPostId]
	);

	const hasLoaded = useSelect(
		(select) => select('tsjippy-forms/conditions-store')?.hasLoaded?.(targetPostId) ?? false,
		[targetPostId]
	);

	const handleSave = useCallback(async (targetBlockId) => {
		setIsSaving(true);

		const result = validateConditions(draftConditions, setFieldErrors);

		if (result.errors.length > 0) {
			setFieldErrors(result.fieldErrors);
			setFocusTarget(result.firstErrorTarget);
			setPulseTarget(result.firstErrorTarget);
			showToastError(
				__('Please fix the invalid conditions before saving.', 'tsjippy')
			);

			setIsSaving(false);
			return;
		}

		try {
			const savedConditions = await saveConditionsRequest(
				targetBlockId,
				draftConditions,
				blockProps
			);

			setCondition(
				targetBlockId,
				Array.isArray(savedConditions)
					? savedConditions
					: draftConditions
			);

			resetErrors();
			setSuccessMessage(__('Conditions saved successfully.', 'tsjippy'));
			showToastSuccess(__('Conditions saved.', 'tsjippy'));
		} catch (err) {
			showToastError(
				err?.message || __('Failed to save conditions.', 'tsjippy')
			);
		}

		setIsSaving(false);
	}, [
		draftConditions,
		blockProps,
		saveConditionsRequest,
		setCondition,
		resetErrors,
		showToastSuccess,
		showToastError,
	]);

	const handleReset = useCallback(() => {
		if (Array.isArray(conditions)) {
			resetErrors();
			setDraftConditions(deepClone(conditions));
			showToastSuccess(__('Changes reset.', 'tsjippy'));
		}
	}, [conditions, resetErrors, showToastSuccess]);

	const renderRuleRow = (rule, ruleIndex, conditionIndex) => {
		const isPulsed =
			pulseTarget &&
			pulseTarget.section === 'rules' &&
			pulseTarget.ruleIndex === ruleIndex;

		return (
			<div
				key={ruleIndex}
				className={`item ${isPulsed ? 'pulse' : ''}`}
				data-condition-index={conditionIndex}
				data-rule-index={ruleIndex}
			>
				<RuleRow
					conditionIndex={conditionIndex}
					rule={rule}
					ruleIndex={ruleIndex}
					formBlockOptions={formBlockOptions}
					onUpdate={updateRuleCondition}
					onDeleteRule={() => deleteRule(conditionIndex, ruleIndex)}
					onMoveRuleUp={() => moveRule(conditionIndex, ruleIndex, -1)}
					onMoveRuleDown={() => moveRule(conditionIndex, ruleIndex, 1)}
					canMoveRuleUp={ruleIndex > 0}
					canMoveRuleDown={ruleIndex < draftConditions[conditionIndex].rules.length - 1}
					ruleErrors={fieldErrors[conditionIndex]?.rules?.[ruleIndex] || {}}
				/>
			</div>
		);
	};

	const renderActionRow = (actionItem, actionIndex, conditionIndex, props) => {
		const actionErrors = fieldErrors[conditionIndex]?.actions?.[actionIndex] || {};
		const isPulsed =
			pulseTarget &&
			pulseTarget.section === 'actions' &&
			pulseTarget.actionIndex === actionIndex;

		const datalistOptions = [];
		const inputType = props?.attributes?.type;

		inputSchema.sharedAttributes
			.concat(inputSchema.types[inputType] || [])
			.forEach(data => datalistOptions.push(data.attribute));
		inputSchema.ariaAttributes
			.forEach(data => datalistOptions.push('aria-' + data.attribute));
		datalistOptions.sort();

		const actionOptions = [
			{ label: __('Select action', 'tsjippy'), value: '' },
			{ label: __('Show', 'tsjippy'), value: 'show' },
			{ label: __('Hide', 'tsjippy'), value: 'hide' },
			{ label: __('Toggle visibility', 'tsjippy'), value: 'toggle' },
		];

		if (props?.name === 'tsjippy-forms/input' || props?.name === 'tsjippy-forms/select') {
			actionOptions.push({
				label: __('Set property', 'tsjippy'),
				value: 'set-property',
			});
		}

		return (
			<div
				key={actionIndex}
				className={`rule-row inner item ${
					Object.keys(actionErrors).length > 0 ? 'invalid' : ''
				} ${isPulsed ? 'pulse' : ''}`}
				data-action-index={actionIndex}
			>
				<SelectControl
					label={__('Action', 'tsjippy')}
					value={actionItem?.action || ''}
					options={actionOptions}
					onChange={(value) => updateAction(conditionIndex, actionIndex, 'action', value)}
					help={actionErrors.action || ''}
					data-field-key="action"
				/>

				{(actionItem?.action || '') === 'set-property' && (
					<>
						<TextControl
							label={__('Property name', 'tsjippy')}
							value={actionItem?.['property-name'] || ''}
							onChange={(value) => updateAction(conditionIndex, actionIndex, 'property-name', value)}
							help={actionErrors.propertyName || ''}
							data-field-key="propertyName"
							list='block-properties'
						/>

						<datalist id="block-properties">
							{datalistOptions.map((attribute) => (
								<option value={attribute} key={attribute}></option>
							))}
						</datalist>

						<span className='condition-label' style={{ marginTop: '25px' }}>To</span>

						<ComboboxControl
							label={__('Property value', 'tsjippy')}
							value={actionItem?.['property-value'] || ''}
							options={(() => {
								const mappedOptions = formBlockOptions.map((data) => ({
									value: `the-value-of-${data.value}`,
									label: `The value of '${data.label}'`,
								}));

								const currentValue = actionItem?.['property-value'];
								const currentInputValue = filterValue?.trim();

								if (
									currentInputValue &&
									!mappedOptions.some(
										(opt) => opt.value === currentInputValue || opt.label === currentInputValue
									)
								) {
									mappedOptions.unshift({
										value: currentInputValue,
										label: `Fixed value: "${currentInputValue}"`,
									});
								}

								if (
									currentValue &&
									!mappedOptions.some((opt) => opt.value === currentValue)
								) {
									mappedOptions.unshift({
										value: currentValue,
										label: currentValue,
									});
								}

								return mappedOptions;
							})()}
							onFilterValueChange={(inputValue) => {
								setFilterValue(inputValue || '');
							}}
							onChange={(value) => {
								const finalValue = value !== undefined ? value : (filterValue || '');
								updateAction(conditionIndex, actionIndex, 'property-value', finalValue);
							}}
							help={actionErrors.propertyValue || ''}
							data-field-key="propertyValue"
						/>

						{['date', 'number', 'range', 'week', 'month'].includes(inputType) &&
							(actionItem?.['property-value'] || '').includes("the-value-of-") && (
								<NumberControl
									label={__('Amount to add to the block value', 'tsjippy')}
									isShiftStepEnabled={true}
									onChange={(value) => updateAction(conditionIndex, actionIndex, 'addition', value)}
									shiftStep={1}
									value={actionItem?.['addition'] || ''}
									spinControls='custom'
								/>
							)}
					</>
				)}

				<Button
					style={{ marginTop: '20px' }}
					variant="secondary"
					isDestructive
					onClick={() => deleteAction(conditionIndex, actionIndex)}
					icon={trash}
				>
					{__('Delete action', 'tsjippy')}
				</Button>
				<br />
				<h4>{__('Apply Actions to these blocks as well', 'tsjippy')}</h4>
				<SelectControl
					multiple
					label={__('Target blocks', 'tsjippy')}
					value={actionItem?.targets || []}
					options={[...(formBlockOptions || [])]}
					onChange={(values) => {
						const targets = Array.isArray(values) ? values : [values];
						updateAction(
							conditionIndex,
							actionIndex,
							'targets',
							[...new Set(targets)]
						);
					}}
				/>
			</div>
		);
	};

	const displayConditions = (props) => {
		if (!Array.isArray(draftConditions) || draftConditions.length === 0) {
			return (
				<>
					<p>{__('No conditions defined yet.', 'tsjippy')}</p>
					<Button variant="primary" onClick={addCondition}>
						{__('Add first condition', 'tsjippy')}
					</Button>
				</>
			);
		}

		return draftConditions.map((condition, conditionIndex) => (
			<div
				key={condition.id || `condition-${conditionIndex}`}
				className={`condition-row ${
					Array.isArray(condition['rules']) && condition['rules'].length === 0
						? 'condition-row--empty'
						: ''
				}`}
				data-condition-index={conditionIndex}
			>
				<span className="condition-label">If</span>

				{((condition.rules || []).length === 0) ? (
					<>
						<p>{__('No rules defined yet.', 'tsjippy')}</p>
						<Button variant="primary" onClick={() => addRule(conditionIndex)}>
							{__('Add rule', 'tsjippy')}
						</Button>
					</>
				) : (
					condition.rules.map((rule, ruleIndex) => renderRuleRow(rule, ruleIndex, conditionIndex))
				)}

				<br />

				<span className="condition-label">Then</span>

				{((condition.actions || []).length === 0) ? (
					<>
						<p>{__('No actions defined yet.', 'tsjippy')}</p>
						<Button variant="primary" onClick={() => addAction(conditionIndex)}>
							{__('Add action', 'tsjippy')}
						</Button>
					</>
				) : (
					condition.actions.map((action, actionIndex) =>
						renderActionRow(action, actionIndex, conditionIndex, props)
					)
				)}

				{(props?.name === 'tsjippy-forms/input' || props?.name === 'tsjippy-forms/select') && (
					<div className="actions">
						<Button variant="secondary" onClick={() => addAction(conditionIndex)} icon={plus}>
							{__('Add another action', 'tsjippy')}
						</Button>
					</div>
				)}

				<div className="actions">
					{condition.actions?.length === 1 &&
						['show', 'hide'].includes(condition.actions[0]?.['action']) && (
							<Button
								variant="secondary"
								onClick={() => addReverseCondition(conditionIndex)}
								icon={undo}
							>
								{__('Add Opposite Condition', 'tsjippy')}
							</Button>
						)}

					<Button
						variant="secondary"
						isDestructive
						onClick={() => deleteCondition(conditionIndex)}
						icon={trash}
					>
						{__('Delete condition', 'tsjippy')}
					</Button>
				</div>
			</div>
		));
	};

	const renderContent = (props) => {
		if (isLoading && !hasLoaded) {
			return (
				<>
					{__('Fetching Condition Data...', 'tsjippy')}
					<Spinner />
				</>
			);
		}

		if (error) {
			return (
				<Notice status="error" isDismissible={false}>
					{__('Error:', 'tsjippy')} {error}
				</Notice>
			);
		}

		return (
			<>
				{successMessage && (
					<Notice
						status="success"
						isDismissible
						onRemove={clearSuccessMessage}
					>
						{successMessage}
					</Notice>
				)}

				<div ref={modalRef}>
					<h3>{__('Conditions', 'tsjippy')}</h3>
					{displayConditions(props)}
				</div>

				<div style={{ marginTop: '16px', display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
					<Button variant="primary" onClick={addCondition}>
						{__('Add New Condition', 'tsjippy')}
					</Button>

					<Button
						variant="primary"
						onClick={() => handleSave(props?.attributes?.blockId || blockId)}
						disabled={!isDirty || !isValid || isSaving}
						accessibleWhenDisabled={true}
					>
						{isSaving
							? __('Saving...', 'tsjippy')
							: isDirty
								? __('Save conditions', 'tsjippy')
								: __('Saved', 'tsjippy')}
					</Button>

					<Button variant="secondary" onClick={handleReset} disabled={!isDirty}>
						{__('Reset changes', 'tsjippy')}
					</Button>

					<Button variant="secondary" onClick={handleClose}>
						{__('Close', 'tsjippy')}
					</Button>
				</div>

				{isDirty && (
					<p style={{ marginTop: '12px', color: '#b45309' }}>
						{__('You have unsaved changes.', 'tsjippy')}
					</p>
				)}
			</>
		);
	};

	if (!isVisible || typeof document === 'undefined') {
		return null;
	}

	return createPortal(
		<div
			id="block-conditions-modal"
			className="modal"
			onClick={handleOverlayClick}
		>
			<div
				className="modal-content"
				onClick={stopPropagation}
				onKeyDown={stopPropagation}
				style={{ maxWidth: '90vw' }}
			>
				<span className="close mobile-sticky" onClick={handleClose}>
					<svg
						width="24"
						height="24"
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						strokeWidth="2"
					>
						<line x1="18" y1="6" x2="6" y2="18" />
						<line x1="6" y1="6" x2="18" y2="18" />
					</svg>
				</span>

				{renderContent(blockProps)}
			</div>
		</div>,
		document.body
	);
}