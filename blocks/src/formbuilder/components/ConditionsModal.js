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
import { useFormElementOptions } from '../hooks/useFormElementOptions';
import {
	plus,
	trash,
	copy,
	arrowUp,
	arrowDown
} from '@wordpress/icons';


import RuleRow from './RuleRow';

import {inputSchema} from './../../input/element_attributes.js';

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
function validateConditions(conditions) {
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

	conditions    = Array.isArray(conditions) ? conditions : [];
	const rules   = Array.isArray(conditions[0]?.rules) ? conditions[0].rules : [];
	const actions = Array.isArray(conditions[0]?.actions) ? conditions[0].actions : [];

	if (rules.length === 0) {
		errors.push(__('At least one rule group is required.', 'tsjippy'));
	}

	/**
	 * Loop over all conditions
	 */
	conditions.forEach((condition, conditionIndex) => {
		if (!Array.isArray(condition.rules) || condition.rules.length === 0) {
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

			return;
		}

		/**
		 * Loop over all rules of this condition
		 * And check validity
		 */
		condition.rules.forEach((rule, ruleIndex) => {

			((fieldErrors[conditionIndex] ||= {}).rules ||= [])[ruleIndex] ||= {};

			const ruleErrors = {};

			if (!rule?.['conditional-field']) {
				ruleErrors.conditionalField = __('Select an element.', 'tsjippy');

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

			if (isEquationRequiringValue(rule?.equation)) {
				const value = condition?.['conditional-value'];

				if (
					value === undefined ||
					value === null ||
					String(value).trim() === ''
				) {
					ruleErrors.conditionalValue = __('Enter a value.', 'tsjippy');

					if (firstErrorTarget.section === null) {
						firstErrorTarget.section = 'rules';
						firstErrorTarget.conditionIndex = conditionIndex;
						firstErrorTarget.ruleIndex = ruleIndex;
						firstErrorTarget.fieldKey = 'conditionalValue';
					}
				}
			}

			if (rule?.equation === '+' || rule?.equation === '-') {
				if (!condition?.['conditional-field-2']) {
					ruleErrors.conditionalField2 = __(
						'Select a second element.',
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
						__('Condition %1$d, rule %2$d has validation errors.', 'tsjippy'),
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
		condition.actions.forEach((actionItem, actionIndex) => {
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
	blockProps
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

	/**
	 * A conditions is an array of condition arrays
	 * Each condition has one or more rules
	 * And one or more actions
	 */
	const [draftConditions, setDraftConditions] = useState(
		[
			{
				rules:   [ createEmptyRule() ],
				actions: [ createEmptyAction() ],
			}
		]
	);
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

		const { section, conditionIndex, ruleIndex, actionIndex, fieldKey } = focusTarget;

		let selector = '';

		if (section === 'rules') {
			selector = `[data-rule-index="${conditionIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] input,
				[data-rule-index="${conditionIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] select,
				[data-rule-index="${conditionIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] textarea`;
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

	const addCondition = useCallback(() => {
		clearSuccessMessage();
		setValidationErrors([]);
		setFieldErrors({});

		setDraftConditions((prev) => {
			const next = deepClone(prev);
			next.push({
				rules: [createEmptyRule()],
				actions: [createEmptyAction()]
			});
			return next;
		});
	}, [clearSuccessMessage]);

	/**
	 * Update one rule on one condition.
	 */
	const updateRuleCondition = useCallback(
		(conditionIndex, ruleIndex, key, value) => {
			clearSuccessMessage();

			setDraftConditions((prev) => {
				const next = deepClone(prev);

				/**
				 * Create base structure if it does not exist yet
				 */
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

				// Add a new sub-rule
				if(key == 'combinator'){
					next[conditionIndex].rules[ruleIndex + 1] = createEmptyRule();
				}

				return next;
			});

			setValidationErrors([]);
			setFieldErrors({});
		},
		[clearSuccessMessage]
	);

	const addRule = useCallback((conditionIndex) => {
		clearSuccessMessage();
		setValidationErrors([]);
		setFieldErrors({});

		setDraftConditions((prev) => {
			const next = deepClone(prev);
			next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];
			next[conditionIndex].rules.push(createEmptyRule());
			return next;
		});
	}, [clearSuccessMessage]);

	const duplicateRule = useCallback(
		(conditionIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);

				next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];

				const ruleToDuplicate = next[conditionIndex].rules;

				if (!ruleToDuplicate) {
					return next;
				}

				const clonedRule = deepClone(ruleToDuplicate);
				next[conditionIndex].rules.splice(conditionIndex + 1, 0, clonedRule);

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const deleteCondition = useCallback(
		(conditionIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});

			setDraftConditions((prev) => {
				const next = deepClone(prev);

				next.splice(conditionIndex, 1);
				return next;
			});
		},
		[clearSuccessMessage]
	);

	const deleteRule = useCallback(
		(conditionIndex, ruleIndex) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});


			setDraftConditions((prev) => {
				const next = deepClone(prev);


				if (!next[conditionIndex].rules) {
					return next;
				}

				// Remove the rule
				next[conditionIndex].rules.splice(ruleIndex, 1);

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const moveRule = useCallback(
		(conditionIndex, ruleIndex, direction) => {
			clearSuccessMessage();
			setValidationErrors([]);
			setFieldErrors({});


			setDraftConditions((prev) => {
				const next = deepClone(prev);


				next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];

				const targetIndex = ruleIndex + direction;

				if (targetIndex < 0 || targetIndex >= next[conditionIndex].rules.length) {
					return next;
				}

				// Store the sub rule we are moving
				const temp = next[conditionIndex].rules[ruleIndex];

				// Store the rule that is currently in the desired location in the index of the rule we are moving
				next[conditionIndex].rules[ruleIndex] = next[conditionIndex].rules[targetIndex];

				// Store the rule in the new index
				next[conditionIndex].rules[targetIndex] = temp;

				return next;
			});
		},
		[clearSuccessMessage]
	);

	const addAction = useCallback((conditionIndex) => {
		clearSuccessMessage();
		setValidationErrors([]);
		setFieldErrors({});

		setDraftConditions((prev) => {
			const next = deepClone(prev);

			next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];
			next[conditionIndex].actions.push(createEmptyAction());
			return next;
		});
	}, [clearSuccessMessage]);

	const updateAction = useCallback(
		(conditionIndex, actionIndex, key, value) => {
			clearSuccessMessage();


			setDraftConditions((prev) => {
				const next = deepClone(prev);


				next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];

				if (!next[conditionIndex].actions[actionIndex]) {
					next[conditionIndex].actions[actionIndex] = createEmptyAction();
				}

				next[conditionIndex].actions[actionIndex][key] = value;
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

	const renderRuleRow	  = (rule, ruleIndex, conditionIndex) => {
		const ruleErrors = fieldErrors?.rules?.[ruleIndex] || {};
		const isPulsed =
			pulseTarget &&
			pulseTarget.section === 'rules' &&
			pulseTarget.ruleIndex === ruleIndex;
						
		return (
			<div
				key={ruleIndex}
				className={`condition-row__item ${
					ruleErrors ? 'condition-row__item--invalid' : ''
				} ${isPulsed ? 'condition-row__item--pulse' : ''}`}
				data-condition-index={ruleIndex}
			>
				
				<RuleRow
					conditionIndex={conditionIndex}
					rule={rule}
					ruleIndex={ruleIndex}
					formElementOptions={formElementOptions}
					onUpdate={updateRuleCondition}
					onDeleteRule={ () => deleteRule(conditionIndex, ruleIndex) }
					onMoveRuleUp={ () =>  moveRule(conditionIndex, ruleIndex, -1) }
					onMoveRuleDown={ () => moveRule(conditionIndex, ruleIndex, 1) }
					canMoveRuleUp={ ruleIndex > 0}
					canMoveRuleDown={ ruleIndex < draftConditions[conditionIndex].rules.length - 1}
					fieldErrors={ fieldErrors[conditionIndex]?.rules?.[ruleIndex] || {}}
				/>
				
			</div>
		);
	};

	const renderActionRow = (actionItem, actionIndex, conditionIndex, blockProps) => {
		const actionErrors = fieldErrors[conditionIndex]?.actions?.[actionIndex] || {};
		const isPulsed =
			pulseTarget &&
			pulseTarget.section === 'actions' &&
			pulseTarget.actionIndex === actionIndex;

		const datalistOptions	= [];
		inputSchema.sharedAttributes.concat(inputSchema.types[blockProps.attributes.type]).forEach(data => datalistOptions.push(data.attribute));
		inputSchema.ariaAttributes.forEach(data => datalistOptions.push('aria-'+data.attribute));
		
		return (
			<>
			<div
				key={actionIndex}
				className={`condition-row item ${
					Object.keys(actionErrors).length > 0 ? 'condition-row__item--invalid' : ''
				} ${isPulsed ? 'condition-row__item--pulse' : ''}`}
				data-action-index={actionIndex}
			>
				<SelectControl
					label={__('Action', 'tsjippy')}
					value={actionItem?.action || ''}
					options={[
						{ label: __('Select action', 'tsjippy'), value: '' },
						{ label: __('Show this block', 'tsjippy'), value: 'show' },
						{ label: __('Hide this block', 'tsjippy'), value: 'hide' },
						{ label: __('Toggle the visibility of this block', 'tsjippy'), value: 'toggle' },
						{ label: __('Set property', 'tsjippy'), value: 'set-property' },
					]}
					onChange={(value) => updateAction(conditionIndex, actionIndex, 'action', value)}
					help={actionErrors.action || ''}
					data-field-key="action"
				/>

				{(actionItem?.action || '') == 'set-property' && blockProps.name == 'tsjippy-forms/input' ?
					<>
					<TextControl
						label={__('Property name', 'tsjippy')}
						value={actionItem?.['property-name'] || ''}
						onChange={(value) => updateAction(conditionIndex, actionIndex, 'property-name', value)}
						help={actionErrors.propertyName || ''}
						data-field-key="propertyName"
						list='element-properties'
					/>

					<datalist id="element-properties">
						{datalistOptions.map((attribute) => <option value={attribute}></option>)}
					</datalist>

					To

					<TextControl
						label={__('Property value', 'tsjippy')}
						value={actionItem?.['property-value'] || ''}
						onChange={(value) => updateAction(conditionIndex, actionIndex, 'property-value', value)}
						help={actionErrors.propertyValue || ''}
						data-field-key="propertyValue"
						list="possible-elements"
					/>

					<datalist id="possible-elements">
						{formElementOptions.map((data) => <option value={"the-value-of-"+data.value}></option>)}
					</datalist>

					</>
					: ''

				}
			</div>
			
			<div className="condition-row__actions">
				<Button variant="secondary" onClick={() => addAction(conditionIndex)} icon={plus}>
					{__('Add another action', 'tsjippy')}
				</Button>

				<Button
					variant="secondary"
					onClick={() => duplicateAction(actionIndex)}
					icon={copy}
				>
					{__('Duplicate action', 'tsjippy')}
				</Button>

				<Button
					variant="secondary"
					isDestructive
					onClick={() => deleteAction(actionIndex)}
					icon={trash}
				>
					{__('Delete action', 'tsjippy')}
				</Button>
			</div>
			</>
		);
	};

	const displayConditions = (blockProps) => {
		if(draftConditions.length === 0){
			return (
				<>
					<p>{__('No conditions defined yet.', 'tsjippy')}</p>
					<Button variant="primary" onClick={addCondition}>
						{__('Add first condition', 'tsjippy')}
					</Button>
				</>
			);
		}

		/**
		 * Loop over all conditons
		 */
		return draftConditions.map((condition, conditionIndex) => (
			<>
			<div
				key       = {conditionIndex}
				className = {`condition-row ${
					Array.isArray(condition) && condition.length === 0
						? 'condition-row--empty'
						: ''
				}`}
				data-condition-index={conditionIndex}
			>
				<span className="condition-if">If</span>

				{((condition.rules || []).length === 0 ) ? (
					<>
						<p>{__('No rules defined yet.', 'tsjippy')}</p>
						<Button variant="primary" onClick={ () => addRule(conditionIndex) }>
							{__('Add rule', 'tsjippy')}
						</Button>
					</>
				) : (
					condition.rules.map((rule, ruleIndex) => renderRuleRow(rule, ruleIndex, conditionIndex))
				)}

				<br></br> 

				<span className="condition-if">Then</span>

				{((condition.actions || []).length === 0 ) ? (
					<>
						<p>{__('No actions defined yet.', 'tsjippy')}</p>
						<Button variant="primary" onClick={ () => addAction(conditionIndex) }>
							{__('Add action', 'tsjippy')}
						</Button>
					</>
				) : (
					condition.actions.map((action, actionIndex) => renderActionRow(action, actionIndex, conditionIndex, blockProps))
				)}

				{/* Action buttons for managing the current condition and rule. */}
				<div className="condition-row__actions">
					<Button
						variant="secondary"
						onClick={() => duplicateRule(conditionIndex)}
						icon={copy}
					>
						{__('Duplicate condition', 'tsjippy')}
					</Button>

					<Button
						variant="secondary"
						isDestructive
						onClick={() =>
							deleteCondition(conditionIndex, ruleIndex)
						}
						icon={trash}
					>
						{__('Delete condition', 'tsjippy')}
					</Button>
				</div>
			</div>
			</>
		))
	}

	const renderContent = useCallback((blockProps) => {
		if (isLoading && !hasLoaded) {
			return (
				<>
				Fetching Condition Data... 
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
					<h3>{__('Conditions', 'tsjippy')}</h3>

					{ displayConditions(blockProps) }
				</div>

				<div style={{ marginTop: '16px', display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
					<Button variant="primary" onClick={addCondition}>
						{__('Add New Condition', 'tsjippy')}
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
		addRule,
		addCondition,
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

				{renderContent(blockProps)}
			</div>
		</div>,
		document.body
	);
}