import { __, sprintf } from '@wordpress/i18n';
import { Button, Spinner, Notice, SelectControl, TextControl } from '@wordpress/components';
import {
	useEffect,
	useMemo,
	useRef,
	useState,
	useCallback,
	createPortal,
} from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import ConditionRow from './ConditionRow';
import { useFormElementOptions } from '../hooks/useFormElementOptions';
import {
	plus,
	trash,
	copy,
	arrowUp,
	arrowDown
} from '@wordpress/icons';

/**
 * Create a blank condition object.
 */
function createEmptyCondition() {
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
		'==',
		'!=',
		'>',
		'<',
		'== value',
		'!= value',
		'> value',
		'< value',
		'+',
		'-',
	].includes(equation);
}

/**
 * Validate the current conditions object.
 */
function validateConditions(state) {
	const errors = [];
	const fieldErrors = {
		rules: {},
		actions: {},
	};

	const firstErrorTarget = {
		section: null,
		ruleIndex: null,
		conditionIndex: null,
		actionIndex: null,
		fieldKey: null,
	};

	const rules = Array.isArray(state?.rules) ? state.rules : [];
	const actions = Array.isArray(state?.actions) ? state.actions : [];

	if (rules.length === 0) {
		errors.push(__('At least one rule group is required.', 'tsjippy'));
	}

	rules.forEach((rule, ruleIndex) => {
		if (!Array.isArray(rule) || rule.length === 0) {
			errors.push(
				sprintf(
					__('Rule group %d must contain at least one condition.', 'tsjippy'),
					ruleIndex + 1
				)
			);

			if (firstErrorTarget.section === null) {
				firstErrorTarget.section = 'rules';
				firstErrorTarget.ruleIndex = ruleIndex;
				firstErrorTarget.conditionIndex = 0;
				firstErrorTarget.fieldKey = 'conditionalField';
			}

			return;
		}

		fieldErrors.rules[ruleIndex] = fieldErrors.rules[ruleIndex] || {};

		rule.forEach((condition, conditionIndex) => {
			const conditionErrors = {};

			if (!condition?.['conditional-field']) {
				conditionErrors.conditionalField = __('Select an element.', 'tsjippy');

				if (firstErrorTarget.section === null) {
					firstErrorTarget.section = 'rules';
					firstErrorTarget.ruleIndex = ruleIndex;
					firstErrorTarget.conditionIndex = conditionIndex;
					firstErrorTarget.fieldKey = 'conditionalField';
				}
			}

			if (!condition?.equation) {
				conditionErrors.equation = __('Select an equation.', 'tsjippy');

				if (firstErrorTarget.section === null) {
					firstErrorTarget.section = 'rules';
					firstErrorTarget.ruleIndex = ruleIndex;
					firstErrorTarget.conditionIndex = conditionIndex;
					firstErrorTarget.fieldKey = 'equation';
				}
			}

			if (isEquationRequiringValue(condition?.equation)) {
				const value = condition?.['conditional-value'];

				if (
					value === undefined ||
					value === null ||
					String(value).trim() === ''
				) {
					conditionErrors.conditionalValue = __('Enter a value.', 'tsjippy');

					if (firstErrorTarget.section === null) {
						firstErrorTarget.section = 'rules';
						firstErrorTarget.ruleIndex = ruleIndex;
						firstErrorTarget.conditionIndex = conditionIndex;
						firstErrorTarget.fieldKey = 'conditionalValue';
					}
				}
			}

			if (condition?.equation === '+' || condition?.equation === '-') {
				if (!condition?.['conditional-field-2']) {
					conditionErrors.conditionalField2 = __(
						'Select a second element.',
						'tsjippy'
					);

					if (firstErrorTarget.section === null) {
						firstErrorTarget.section = 'rules';
						firstErrorTarget.ruleIndex = ruleIndex;
						firstErrorTarget.conditionIndex = conditionIndex;
						firstErrorTarget.fieldKey = 'conditionalField2';
					}
				}

				if (!condition?.['equation-2']) {
					conditionErrors.equation2 = __(
						'Select a second equation.',
						'tsjippy'
					);

					if (firstErrorTarget.section === null) {
						firstErrorTarget.section = 'rules';
						firstErrorTarget.ruleIndex = ruleIndex;
						firstErrorTarget.conditionIndex = conditionIndex;
						firstErrorTarget.fieldKey = 'equation2';
					}
				}
			}

			if (Object.keys(conditionErrors).length > 0) {
				fieldErrors.rules[ruleIndex][conditionIndex] = conditionErrors;
				errors.push(
					sprintf(
						__('Rule %1$d, condition %2$d has validation errors.', 'tsjippy'),
						ruleIndex + 1,
						conditionIndex + 1
					)
				);
			}
		});
	});

	actions.forEach((actionItem, actionIndex) => {
		const actionErrors = {};

		if (!actionItem?.action) {
			actionErrors.action = __('Select an action.', 'tsjippy');

			if (firstErrorTarget.section === null) {
				firstErrorTarget.section = 'actions';
				firstErrorTarget.actionIndex = actionIndex;
				firstErrorTarget.fieldKey = 'action';
			}
		}

		if (!actionItem?.['property-name']) {
			actionErrors.propertyName = __('Enter a property name.', 'tsjippy');

			if (firstErrorTarget.section === null) {
				firstErrorTarget.section = 'actions';
				firstErrorTarget.actionIndex = actionIndex;
				firstErrorTarget.fieldKey = 'propertyName';
			}
		}

		if (!actionItem?.['property-value']) {
			actionErrors.propertyValue = __('Enter a property value.', 'tsjippy');

			if (firstErrorTarget.section === null) {
				firstErrorTarget.section = 'actions';
				firstErrorTarget.actionIndex = actionIndex;
				firstErrorTarget.fieldKey = 'propertyValue';
			}
		}

		if (Object.keys(actionErrors).length > 0) {
			fieldErrors.actions[actionIndex] = actionErrors;
			errors.push(
				sprintf(
					__('Action %d has validation errors.', 'tsjippy'),
					actionIndex + 1
				)
			);
		}
	});

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
	elementId,
	allNestedBlocks,
}) {
	const { saveConditions, setError } = useDispatch(
		'tsjippy-forms/conditions-store'
	);
	const { createSuccessNotice, createErrorNotice } = useDispatch('core/notices');

	const conditions = useSelect(
		(select) => select('tsjippy-forms/conditions-store').getConditions(elementId),
		[elementId]
	);

	const isLoading = useSelect(
		(select) => select('tsjippy-forms/conditions-store').isLoading(elementId),
		[elementId]
	);

	const isSaving = useSelect(
		(select) => select('tsjippy-forms/conditions-store').isSaving(elementId),
		[elementId]
	);

	const error = useSelect(
		(select) => select('tsjippy-forms/conditions-store').getError(elementId),
		[elementId]
	);

	const hasLoaded = useSelect(
		(select) => select('tsjippy-forms/conditions-store').hasLoaded(elementId),
		[elementId]
	);

	const [draftConditions, setDraftConditions] = useState({
		rules: [],
		actions: [],
	});
	const [successMessage, setSuccessMessage] = useState('');
	const [validationErrors, setValidationErrors] = useState([]);
	const [fieldErrors, setFieldErrors] = useState({});
	const [focusTarget, setFocusTarget] = useState(null);
	const [pulseTarget, setPulseTarget] = useState(null);

	const formElementOptions = useFormElementOptions(allNestedBlocks);
	const modalRef = useRef(null);
	const previousBodyOverflow = useRef('');

	useEffect(() => {
		if (isVisible && conditions) {
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
		if (!focusTarget || !modalRef.current) {
			return;
		}

		const { section, ruleIndex, conditionIndex, actionIndex, fieldKey } =
			focusTarget;

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
		return validateConditions(draftConditions);
	}, [draftConditions]);

	const isDirty = useMemo(() => {
		return JSON.stringify(draftConditions) !== JSON.stringify(conditions);
	}, [draftConditions, conditions]);

	const isValid = validation.errors.length === 0;

	const clearSuccessMessage = useCallback(() => {
		setSuccessMessage('');
	}, []);

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

	/**
	 * Update one field on one condition.
	 * IMPORTANT: use value so dynamic fields like "combinator"
	 * are written correctly.
	 */
	const updateRuleCondition = useCallback(
		(ruleIndex, subRuleIndex, key, value) => {
			clearSuccessMessage();

			console.log(ruleIndex)
			console.log(subRuleIndex)
			console.log(key)
			console.log(value)

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.rules = Array.isArray(next.rules) ? next.rules : [];

				if (!next.rules[ruleIndex]) {
					next.rules[ruleIndex] = [];
				}

				if (!next.rules[ruleIndex][subRuleIndex]) {
					next.rules[ruleIndex][subRuleIndex] = createEmptyCondition();
				}

				next.rules[ruleIndex][subRuleIndex][key] = value;

				// Add a new sub-rule
				if(key == 'combinator'){
					next.rules[ruleIndex][subRuleIndex + 1] = createEmptyCondition();
				}

				return next;
			});

			setValidationErrors([]);
			setFieldErrors({});
		},
		[clearSuccessMessage]
	);

	const addRule = useCallback(() => {
		clearSuccessMessage();
		setValidationErrors([]);
		setFieldErrors({});

		setDraftConditions((prev) => {
			const next = deepClone(prev);
			next.rules = Array.isArray(next.rules) ? next.rules : [];
			next.rules.push([createEmptyCondition()]);
			return next;
		});
	}, [clearSuccessMessage]);

	const addConditionToExistingRule = useCallback(
		(ruleIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.rules = Array.isArray(next.rules) ? next.rules : [];

				if (!next.rules[ruleIndex]) {
					next.rules[ruleIndex] = [];
				}

				next.rules[ruleIndex].push(createEmptyCondition());

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const duplicateRule = useCallback(
		(ruleIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.rules = Array.isArray(next.rules) ? next.rules : [];

				const ruleToDuplicate = next.rules[ruleIndex];

				if (!ruleToDuplicate) {
					return next;
				}

				const clonedRule = deepClone(ruleToDuplicate);
				next.rules.splice(ruleIndex + 1, 0, clonedRule);

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const deleteRule = useCallback(
		(ruleIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.rules = Array.isArray(next.rules) ? next.rules : [];
				next.rules.splice(ruleIndex, 1);
				return next;
			});
		},
		[clearSuccessMessage]
	);

	const deleteCondition = useCallback(
		(ruleIndex, subRuleIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.rules = Array.isArray(next.rules) ? next.rules : [];

				if (!next.rules[ruleIndex]) {
					return next;
				}

				next.rules[ruleIndex].splice(subRuleIndex, 1);

				if (next.rules[ruleIndex].length === 0) {
					next.rules.splice(ruleIndex, 1);
				}

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const moveRule = useCallback(
		(ruleIndex, direction) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.rules = Array.isArray(next.rules) ? next.rules : [];

				const targetIndex = ruleIndex + direction;

				if (targetIndex < 0 || targetIndex >= next.rules.length) {
					return next;
				}

				const temp = next.rules[ruleIndex];
				next.rules[ruleIndex] = next.rules[targetIndex];
				next.rules[targetIndex] = temp;

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const moveCondition = useCallback(
		(ruleIndex, subRuleIndex, direction) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.rules = Array.isArray(next.rules) ? next.rules : [];

				if (!next.rules[ruleIndex]) {
					return next;
				}

				const targetIndex = subRuleIndex + direction;

				if (targetIndex < 0 || targetIndex >= next.rules[ruleIndex].length) {
					return next;
				}

				const temp = next.rules[ruleIndex][subRuleIndex];
				next.rules[ruleIndex][subRuleIndex] = next.rules[ruleIndex][targetIndex];
				next.rules[ruleIndex][targetIndex] = temp;

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const addAction = useCallback(() => {
		clearSuccessMessage();
		setValidationErrors([]);
		setFieldErrors({});

		setDraftConditions((prev) => {
			const next = deepClone(prev);
			next.actions = Array.isArray(next.actions) ? next.actions : [];
			next.actions.push(createEmptyAction());
			return next;
		});
	}, [clearSuccessMessage]);

	const updateAction = useCallback(
		(actionIndex, key, value) => {
			clearSuccessMessage();

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.actions = Array.isArray(next.actions) ? next.actions : [];

				if (!next.actions[actionIndex]) {
					next.actions[actionIndex] = createEmptyAction();
				}

				next.actions[actionIndex][key] = value;
				return next;
			});

			setValidationErrors([]);
			setFieldErrors({});
		},
		[clearSuccessMessage]
	);

	const duplicateAction = useCallback(
		(actionIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.actions = Array.isArray(next.actions) ? next.actions : [];

				const actionToDuplicate = next.actions[actionIndex];

				if (!actionToDuplicate) {
					return next;
				}

				next.actions.splice(actionIndex + 1, 0, deepClone(actionToDuplicate));
				return next;
			});
		},
		[clearSuccessMessage]
	);

	const deleteAction = useCallback(
		(actionIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.actions = Array.isArray(next.actions) ? next.actions : [];
				next.actions.splice(actionIndex, 1);
				return next;
			});
		},
		[clearSuccessMessage]
	);

	const moveAction = useCallback(
		(actionIndex, direction) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);
				next.actions = Array.isArray(next.actions) ? next.actions : [];

				const targetIndex = actionIndex + direction;

				if (targetIndex < 0 || targetIndex >= next.actions.length) {
					return next;
				}

				const temp = next.actions[actionIndex];
				next.actions[actionIndex] = next.actions[targetIndex];
				next.actions[targetIndex] = temp;

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const handleSave = useCallback(async () => {
		const result = validateConditions(draftConditions);

		if (result.errors.length > 0) {
			setValidationErrors(result.errors);
			setFieldErrors(result.fieldErrors);
			setFocusTarget(result.firstErrorTarget);
			setPulseTarget(result.firstErrorTarget);
			showToastError(
				__('Please fix the invalid conditions before saving.', 'tsjippy')
			);
			return;
		}

		try {
			setValidationErrors([]);
			setFieldErrors({});
			setError(elementId, null);

			await saveConditions(elementId, draftConditions);

			setSuccessMessage(__('Conditions saved successfully.', 'tsjippy'));
			showToastSuccess(__('Conditions saved successfully.', 'tsjippy'));
		} catch (saveError) {
			const message =
				saveError?.message || __('Saving failed. Please try again.', 'tsjippy');

			setError(elementId, message);
			showToastError(message);
		}
	}, [
		draftConditions,
		elementId,
		saveConditions,
		setError,
		showToastError,
		showToastSuccess,
	]);

	const handleReset = useCallback(() => {
		setDraftConditions(deepClone(conditions));
		clearSuccessMessage();
		setValidationErrors([]);
		setFieldErrors({});
		showToastSuccess(__('Changes reset.', 'tsjippy'));
	}, [conditions, clearSuccessMessage, showToastSuccess]);

	const renderActionRow = (actionItem, actionIndex) => {
		const actionErrors = fieldErrors?.actions?.[actionIndex] || {};
		const isPulsed =
			pulseTarget &&
			pulseTarget.section === 'actions' &&
			pulseTarget.actionIndex === actionIndex;

		return (
			<div
				key={actionIndex}
				className={`condition-row__item ${
					Object.keys(actionErrors).length > 0 ? 'condition-row__item--invalid' : ''
				} ${isPulsed ? 'condition-row__item--pulse' : ''}`}
				data-action-index={actionIndex}
			>
				<SelectControl
					label={__('Action', 'tsjippy')}
					value={actionItem?.action || ''}
					options={[
						{ label: __('Select action', 'tsjippy'), value: '' },
						{ label: __('Set property', 'tsjippy'), value: 'set-property' },
						{ label: __('Add value', 'tsjippy'), value: 'add-value' },
						{ label: __('Subtract value', 'tsjippy'), value: 'subtract-value' },
						{ label: __('Toggle', 'tsjippy'), value: 'toggle' },
					]}
					onChange={(value) => updateAction(actionIndex, 'action', value)}
					help={actionErrors.action || ''}
					data-field-key="action"
				/>

				<TextControl
					label={__('Property name', 'tsjippy')}
					value={actionItem?.['property-name'] || ''}
					onChange={(value) => updateAction(actionIndex, 'property-name', value)}
					help={actionErrors.propertyName || ''}
					data-field-key="propertyName"
				/>

				<TextControl
					label={__('Property value', 'tsjippy')}
					value={actionItem?.['property-value'] || ''}
					onChange={(value) => updateAction(actionIndex, 'property-value', value)}
					help={actionErrors.propertyValue || ''}
					data-field-key="propertyValue"
				/>

				<TextControl
					label={__('Property name 1', 'tsjippy')}
					value={actionItem?.['property-name1'] || ''}
					onChange={(value) => updateAction(actionIndex, 'property-name1', value)}
					data-field-key="propertyName1"
				/>

				<TextControl
					label={__('Action value', 'tsjippy')}
					value={actionItem?.['action-value'] || ''}
					onChange={(value) => updateAction(actionIndex, 'action-value', value)}
					data-field-key="actionValue"
				/>

				<TextControl
					label={__('Addition', 'tsjippy')}
					value={actionItem?.addition || ''}
					onChange={(value) => updateAction(actionIndex, 'addition', value)}
					data-field-key="addition"
				/>

				<div className="condition-row__actions">
					<Button variant="secondary" onClick={addAction} icon={plus}>
						{__('Add another action', 'tsjippy')}
					</Button>

					<Button
						variant="secondary"
						onClick={() => duplicateAction(actionIndex)}
						icon={copy}
					>
						{__('Duplicate action', 'tsjippy')}
					</Button>

					{actionIndex > 0 && (
						<Button
							variant="secondary"
							onClick={() => moveAction(actionIndex, -1)}
							icon={arrowUp}
						>
							{__('Move action up', 'tsjippy')}
						</Button>
					)}

					{actionIndex < (draftConditions.actions || []).length - 1 && (
						<Button
							variant="secondary"
							onClick={() => moveAction(actionIndex, 1)}
							icon={arrowDown}
						>
							{__('Move action down', 'tsjippy')}
						</Button>
					)}

					<Button
						variant="secondary"
						isDestructive
						onClick={() => deleteAction(actionIndex)}
						icon={trash}
					>
						{__('Delete action', 'tsjippy')}
					</Button>
				</div>
			</div>
		);
	};

	const renderContent = useCallback(() => {
		if (isLoading && !hasLoaded) {
			return <Spinner />;
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

				{validationErrors.length > 0 && (
					<Notice
						status="error"
						isDismissible
						onRemove={() => setValidationErrors([])}
					>
						<strong>{__('Please fix the following issues:', 'tsjippy')}</strong>
						<ul style={{ marginTop: '8px', marginBottom: 0, paddingLeft: '18px' }}>
							{validationErrors.map((item, index) => (
								<li key={index}>{item}</li>
							))}
						</ul>
					</Notice>
				)}

				<div ref={modalRef}>
					<h3>{__('Rules', 'tsjippy')}</h3>

					{(draftConditions.rules || []).length === 0 ? (
						<>
							<p>{__('No rules defined yet.', 'tsjippy')}</p>
							<Button variant="primary" onClick={addRule}>
								{__('Add first rule', 'tsjippy')}
							</Button>
						</>
					) : (
						(draftConditions.rules || []).map((rule, ruleIndex) => (
							<div
								key={ruleIndex}
								className={`condition-row ${
									Array.isArray(rule) && rule.length === 0
										? 'condition-row--empty'
										: ''
								}`}
								data-rule-index={ruleIndex}
							>
								<span className="condition-if">If</span>

								{(Array.isArray(rule) ? rule : []).map((condition, subRuleIndex) => {
									const hasErrors =
										fieldErrors?.rules?.[ruleIndex]?.[subRuleIndex] &&
										Object.keys(fieldErrors.rules[ruleIndex][subRuleIndex]).length > 0;

									const isPulsed =
										pulseTarget &&
										pulseTarget.section === 'rules' &&
										pulseTarget.ruleIndex === ruleIndex &&
										pulseTarget.conditionIndex === subRuleIndex;

									return (
										<div
											key={subRuleIndex}
											className={`condition-row__item ${
												hasErrors ? 'condition-row__item--invalid' : ''
											} ${isPulsed ? 'condition-row__item--pulse' : ''}`}
											data-condition-index={subRuleIndex}
										>
											<ConditionRow
												condition={condition}
												ruleIndex={ruleIndex}
												subRuleIndex={subRuleIndex}
												formElementOptions={formElementOptions}
												onUpdate={updateRuleCondition}
												onDeleteRule={() => deleteRule(ruleIndex)}
												onMoveRuleUp={() => moveRule(ruleIndex, -1)}
												onMoveRuleDown={() => moveRule(ruleIndex, 1)}
												canMoveRuleUp={ruleIndex > 0}
												canMoveRuleDown={ruleIndex < draftConditions.rules.length - 1}
												fieldErrors={fieldErrors?.rules?.[ruleIndex]?.[subRuleIndex] || {}}
											/>
										</div>
									);
								})}

								{/* Action buttons for managing the current condition and rule. */}
								<div className="condition-row__actions">
									<Button
										variant="secondary"
										onClick={() =>
											addConditionToExistingRule(ruleIndex)
										}
										icon={plus}
									>
										{__('Add another condition', 'tsjippy')}
									</Button>

									<Button
										variant="secondary"
										onClick={() => duplicateRule(ruleIndex)}
										icon={copy}
									>
										{__('Duplicate condition', 'tsjippy')}
									</Button>

									<Button
										variant="secondary"
										isDestructive
										onClick={() =>
											deleteCondition(ruleIndex, subRuleIndex)
										}
										icon={trash}
									>
										{__('Delete condition', 'tsjippy')}
									</Button>
								</div>
							</div>
						))
					)}

					<div style={{ marginTop: '16px' }}>
						<Button variant="primary" onClick={addRule}>
							{__('Add new rule', 'tsjippy')}
						</Button>
					</div>

					<h3 style={{ marginTop: '32px' }}>{__('Actions', 'tsjippy')}</h3>

					{(draftConditions.actions || []).length === 0 ? (
						<>
							<p>{__('No actions defined yet.', 'tsjippy')}</p>
							<Button variant="primary" onClick={addAction}>
								{__('Add first action', 'tsjippy')}
							</Button>
						</>
					) : (
						(draftConditions.actions || []).map((actionItem, actionIndex) =>
							renderActionRow(actionItem, actionIndex)
						)
					)}
				</div>

				<div style={{ marginTop: '16px', display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
					<Button variant="primary" onClick={addRule}>
						{__('Add new rule', 'tsjippy')}
					</Button>

					<Button variant="primary" onClick={addAction}>
						{__('Add new action', 'tsjippy')}
					</Button>

					<Button
						variant="primary"
						onClick={handleSave}
						disabled={!isDirty || !isValid || isSaving}
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
	}, [
		addAction,
		addConditionToExistingRule,
		addRule,
		clearSuccessMessage,
		conditions,
		deleteCondition,
		deleteRule,
		draftConditions,
		error,
		fieldErrors,
		formElementOptions,
		handleClose,
		handleReset,
		handleSave,
		hasLoaded,
		isDirty,
		isLoading,
		isSaving,
		moveAction,
		moveCondition,
		moveRule,
		pulseTarget,
		successMessage,
		updateAction,
		updateRuleCondition,
		validationErrors,
	]);

	if (!isVisible || typeof document === 'undefined') {
		return null;
	}

	return createPortal(
		<div
			id="element-conditions-modal"
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
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</span>

				{renderContent()}
			</div>
		</div>,
		document.body
	);
}