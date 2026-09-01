import { __, sprintf } from '@wordpress/i18n';
import { Button, Spinner, Notice, SelectControl, TextControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';
import {
	useEffect,
	useMemo,
	useRef,
	useState,
	useCallback,
	createPortal,
} from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { getBlocksAsSelectOptions } from '../hooks/getBlocksAsSelectOptions.js';
import {
	plus,
	trash,
	copy,
	arrowUp,
	arrowDown,
	undo
} from '@wordpress/icons';
import apiFetch from '@wordpress/api-fetch';

import RuleRow from './RuleRow';
import {inputSchema} from './../../input/components/block_attributes.js';

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

	if(!Array.isArray(conditions) || conditions.length === 0){
		return {
			errors,
			fieldErrors,
			firstErrorTarget,
		};
	}

	conditions    = Array.isArray(conditions) ? conditions : [];

	/**
	 * Loop over all conditions
	 */
	conditions.forEach((condition, conditionIndex) => {

		if(condition.rules.length > 0) {
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

			if (isEquationRequiringValue(rule?.equation)) {
				const value = rule?.['conditional-value'];

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

			if (actionItem?.action == 'set-property') {
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
	blockId,
	allNestedBlocks,
	blockProps
}) {
	const { setCondition } = useDispatch(
		'tsjippy-forms/conditions-store'
	);

	const { createSuccessNotice, createErrorNotice } = useDispatch('core/notices');

	const conditions = useSelect(
		(select) => select('tsjippy-forms/conditions-store').getConditions(blockId),
		[blockId]
	);

	/**
	 * A conditions is an array of condition arrays
	 * Each condition has one or more rules
	 * And one or more actions
	 */
	const [draftConditions, setDraftConditions] = useState([]);
	const [successMessage, setSuccessMessage] = useState('');
	const [isSaving, setIsSaving] = useState(false);
	const [fieldErrors, setFieldErrors] = useState({});
	const [focusTarget, setFocusTarget] = useState(null);
	const [pulseTarget, setPulseTarget] = useState(null);
	const formBlockOptions = getBlocksAsSelectOptions(allNestedBlocks);
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
		return validateConditions(draftConditions);
	}, [draftConditions]);

	const isValid = validation.errors.length === 0;

	const isDirty = useMemo(() => {
		return JSON.stringify(draftConditions) !== JSON.stringify(conditions);
	}, [draftConditions, conditions]);

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
		resetErrors();

		setDraftConditions((prev) => {
			const next = deepClone(prev);

			// Create the new condition
			const newCondition	 = deepClone(next[0]);
			newCondition.rules	 = [createEmptyRule()];
			newCondition.actions = [createEmptyAction()];
			newCondition.id 	 = undefined;

			// Add to the array
			next.push(newCondition);

			return next;
		});
	}, [clearSuccessMessage]);

	/**
	 * Update one rule on one condition.
	 */
	const updateRuleCondition = useCallback(
		(conditionIndex, ruleIndex, key, value) => {
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
				if (
					key === 'combinator' &&
					!next[ruleIndex + 1]
				) {
					next[conditionIndex].rules[ruleIndex + 1] = createEmptyRule();
				}

				return next;
			});

			resetErrors();
		},
		[clearSuccessMessage]
	);

	const addRule = useCallback((conditionIndex) => {
		resetErrors();

		setDraftConditions((prev) => {
			const next = deepClone(prev);
			next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];
			next[conditionIndex].rules.push(createEmptyRule());
			return next;
		});
	}, [clearSuccessMessage]);

	const addReverseCondition = useCallback(
		(conditionIndex) => {
			resetErrors();

			setDraftConditions((prev) => {
				const next = deepClone(prev);

				/**
				 * Make sure rules and actions are arrays
				 */
				next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];
				next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];

				/**
				 * Clone the data
				 */
				let clone = deepClone(next[conditionIndex]);

				// Unset the condition id as this is a new one
				clone.id = undefined;

				/**
				 * Inverse the rules
				 */
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
					rule['equation']	= reverseOperators[rule['equation']];
				});

				clone.actions.forEach(action => {
					action['action']	= (action['action'] == 'show' ? 'hide' : 'show');
				});

				/**
				 * Insert the condition
				 */
				next.splice(conditionIndex + 1, 0, clone);

				return next;
			});
		},
		[clearSuccessMessage]
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
		[clearSuccessMessage]
	);

	const deleteRule = useCallback(
		(conditionIndex, ruleIndex) => {
			resetErrors();

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
			resetErrors();


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
		resetErrors();

		setDraftConditions((prev) => {
			const next = deepClone(prev);

			console.log(next)

			next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];
			next[conditionIndex].actions.push(createEmptyAction());

			console.log(next)

			return next;
		});
	}, [clearSuccessMessage]);

	const updateAction = useCallback(
		(conditionIndex, actionIndex, key, value) => {
			setDraftConditions((prev) => {
				const next = deepClone(prev);

				console.log(next)

				next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];

				if (!next[conditionIndex].actions[actionIndex]) {
					next[conditionIndex].actions[actionIndex] = createEmptyAction();
				}

				next[conditionIndex].actions[actionIndex][key] = value;

				console.log(next)

				return next;
			});

			resetErrors();
		},
		[clearSuccessMessage]
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
		[clearSuccessMessage]
	);


	const postId = useSelect( ( select ) => 
		select( 'core/editor' ).getCurrentPostId()
	, [] );

	/**
	 * Internal API helper for saving conditions.
	 * This is used by the store-owned save action and is not exported.
	 */
	async function saveConditionsRequest(blockId, conditions, props) {
		// update the conditions on the server
		const savedConditions = await apiFetch({
			path: `${tsjippy.restApiPrefix}/forms/save_block_conditions`,
			method: 'POST',
			data: {
				postId: postId,
				blockId: blockId,
				conditions: conditions,
			},
		});

		// update the form version to make sure the latest js is downloaded on clients
		props.setAttributes({version: props.attributes.version++});

		return savedConditions;
	}

	const isLoading = useSelect(
		(select) =>
			select('tsjippy-forms/conditions-store').isLoading(blockProps.attributes.postId),
		[blockProps.attributes.postId]
	);

	const error = useSelect(
		(select) =>
			select('tsjippy-forms/conditions-store').getError(blockProps.attributes.postId),
		[blockProps.attributes.postId]
	);

	const hasLoaded = useSelect(
		(select) =>
			select('tsjippy-forms/conditions-store').hasLoaded(blockProps.attributes.postId),
		[blockProps.attributes.postId]
	);

	const handleSave = useCallback(async (blockId) => {
		setIsSaving(true);

		const result = validateConditions(draftConditions);

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
				blockId,
				draftConditions,
				blockProps
			);

			setCondition(
				blockId,
				Array.isArray(savedConditions)
					? savedConditions
					: draftConditions
			);

			resetErrors();

			setSuccessMessage(__('Conditions saved successfully.', 'tsjippy'));

			showToastSuccess(__('Conditions saved.', 'tsjippy'));
		} catch (error) {
			showToastError(
				error?.message || 'Failed to save conditions.'
			);
		}

		setIsSaving(false);
	}, [
		blockId,
		draftConditions,
		blockProps,
		setCondition,
		showToastSuccess,
		showToastError,
	]);

	const resetErrors = () => {
		clearSuccessMessage();
		setFieldErrors({});
	}

	const handleReset = useCallback(() => {
		if(Array.isArray(conditions)){
			resetErrors();
			setDraftConditions(deepClone(conditions));
			showToastSuccess(__('Changes reset.', 'tsjippy'));
		}
	}, [conditions, clearSuccessMessage, showToastSuccess]);

	const renderRuleRow	  = (rule, ruleIndex, conditionIndex) => {
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
					onDeleteRule={ () => deleteRule(conditionIndex, ruleIndex) }
					onMoveRuleUp={ () =>  moveRule(conditionIndex, ruleIndex, -1) }
					onMoveRuleDown={ () => moveRule(conditionIndex, ruleIndex, 1) }
					canMoveRuleUp={ ruleIndex > 0}
					canMoveRuleDown={ ruleIndex < draftConditions[conditionIndex].rules.length - 1}
					ruleErrors={ fieldErrors[conditionIndex]?.rules?.[ruleIndex] || {}}
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
		inputSchema.sharedAttributes.concat(inputSchema.types[blockProps.attributes.type] || []).forEach(data => datalistOptions.push(data.attribute));
		inputSchema.ariaAttributes.forEach(data => datalistOptions.push('aria-' + data.attribute));
		datalistOptions.sort();
		
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
					options={[
						{ label: __('Select action', 'tsjippy'), value: '' },
						{ label: __('Show', 'tsjippy'), value: 'show' },
						{ label: __('Hide', 'tsjippy'), value: 'hide' },
						{ label: __('Toggle visibility', 'tsjippy'), value: 'toggle' },
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
						list='block-properties'
					/>

					<datalist id="block-properties">
						{datalistOptions.map((attribute) => <option value={attribute}></option>)}
					</datalist>

					<span class='condition-label' style={{marginTop: ' 25px'}}>To</span>

					<TextControl
						label          = {__('Property value', 'tsjippy')}
						value          = {actionItem?.['property-value'] || ''}
						onChange       = {(value) => updateAction(conditionIndex, actionIndex, 'property-value', value)}
						help           = {actionErrors.propertyValue || ''}
						data-field-key = "propertyValue"
						list           = "possible-blocks"
					/>

					<datalist id="possible-blocks">
						{formBlockOptions.map((data) => <option value={"the-value-of-"+data.value}>{data.label}</option>)}
					</datalist>

					{ 
						// If we selected another block to be the value of this property we should allow to add extra to the value
						['date', 'number', 'range', 'week', 'month'].includes(blockProps.attributes.type) && (actionItem?.['property-value'] || '').includes("the-value-of-") ?
							<NumberControl
							    label              = { __( 'Amount to add to the block value', 'tsjippy') }
								isShiftStepEnabled = { true }
								onChange           = {(value) => updateAction(conditionIndex, actionIndex, 'addition', value)}
								shiftStep          = { 1 }
								value              = {actionItem?.['addition'] || ''}
								spinControls       = 'custom'
							/>
							: ''
					}
					</>
					: ''
				}

				<Button
					style= {{marginTop: '20px'}}
					variant="secondary"
					isDestructive
					onClick={() => deleteAction(conditionIndex, actionIndex)}
					icon={trash}
				>
					{__('Delete action', 'tsjippy')}
				</Button>

				<h4>Apply Actions to these blocks as well</h4>
				<SelectControl
					multiple
					label={__('Target blocks', 'tsjippy')}
					value={actionItem?.targets || []}
					options={[
						...(formBlockOptions || []),
					]}
					onChange={(values) => {
						const targets = Array.isArray(values)
							? values
							: [values];

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

	const displayConditions = (blockProps) => {
		if(!Array.isArray(draftConditions) || draftConditions.length === 0){
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
				<span className="condition-label">If</span>

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

				<span className="condition-label">Then</span>

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
				
				<div className="actions">
					<Button variant="secondary" onClick={() => addAction(conditionIndex)} icon={plus}>
						{__('Add another action', 'tsjippy')}
					</Button>
				</div>

				{/* Action buttons for managing the current condition and rule. */}
				<div className="actions">
					{
						// Add a reverse button only for show/hide actions and simple conditions
						condition.actions.length == 1 && ['show', 'hide'].includes(condition.actions[0]['action']) &&
						<Button
							variant="secondary"
							onClick={() => addReverseCondition(conditionIndex)}
							icon={undo}
						>
							{__('Add Opposite Condition', 'tsjippy')}
						</Button>
					}

					<Button
						variant="secondary"
						isDestructive
						onClick={() =>
							deleteCondition(conditionIndex)
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
						onClick={() => handleSave(blockProps.attributes.blockId)}
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
		formBlockOptions,
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
	]);

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