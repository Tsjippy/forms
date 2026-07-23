import { __ } from '@wordpress/i18n';
import {
	Button,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import {
	plus,
	trash,
	copy,
	arrowUp,
	arrowDown,
	row
} from '@wordpress/icons';

/**
 * Render one rule row inside a rule group.
 * This component is presentational and sends all updates upward.
 */
export default function RuleRow({
	conditionIndex,
	rule,
	ruleIndex,
	formElementOptions,
	onUpdate,
	onDeleteRule,
	onMoveRuleUp,
	onMoveRuleDown,
	canMoveRuleUp,
	canMoveRuleDown,
	fieldErrors = {},
}) {
	console.log("rendering row")
	/* Available equation choices for the main equation dropdown. */
	const equationOptions = [
		{ label: __('Equals', 'tsjippy'), value: '==' },
		{ label: __('Does not equal', 'tsjippy'), value: '!=' },
		{ label: __('Greater than', 'tsjippy'), value: '>' },
		{ label: __('Less than', 'tsjippy'), value: '<' },
		{ label: __('Equals value', 'tsjippy'), value: '== value' },
		{ label: __('Does not equal value', 'tsjippy'), value: '!= value' },
		{ label: __('Greater than value', 'tsjippy'), value: '> value' },
		{ label: __('Less than value', 'tsjippy'), value: '< value' },
		{ label: __('Add', 'tsjippy'), value: '+' },
		{ label: __('Subtract', 'tsjippy'), value: '-' },
	];

	/* Render additional fields for arithmetic-style equations. */
	const renderExtraOptions = () => {
		if (!rule?.equation) {
			return null;
		}

		if (rule.equation === '+' || rule.equation === '-') {
			return (
				<>
					<SelectControl
						label={__('Second element', 'tsjippy')}
						value={rule['conditional-field-2'] || ''}
						options={[
							{ label: __('Select second element', 'tsjippy'), value: '' },
							...(formElementOptions || []),
						]}
						onChange={(element) =>
							onUpdate(conditionIndex, ruleIndex, 'conditional-field-2', element)
						}
						help={fieldErrors.conditionalField2 || ''}
						data-field-key="conditionalField2"
					/>

					<SelectControl
						label={__('Second equation', 'tsjippy')}
						value={rule['equation-2'] || ''}
						options={[
							{ label: __('Select second equation', 'tsjippy'), value: '' },
							{ label: __('Equals', 'tsjippy'), value: '==' },
							{ label: __('Does not equal', 'tsjippy'), value: '!=' },
							{ label: __('Greater than', 'tsjippy'), value: '>' },
							{ label: __('Less than', 'tsjippy'), value: '<' },
						]}
						onChange={(equation2) =>
							onUpdate(conditionIndex, ruleIndex, 'equation-2', equation2)
						}
						help={fieldErrors.equation2 || ''}
						data-field-key="equation2"
					/>
				</>
			);
		}

		return null;
	};

	/* Render the editable UI for one rule entry. */
	return (
		<div className="rule-row __inner">			
			<SelectControl
				label={__('Conditional Field', 'tsjippy')}
				value={rule?.['conditional-field'] || ''}
				options={[
					{ label: __('Select element', 'tsjippy'), value: '' },
					...(formElementOptions || []),
				]}
				onChange={(element) =>
					onUpdate(conditionIndex, ruleIndex, 'conditional-field', element)
				}
				help={fieldErrors.conditionalField || ''}
				data-field-key="conditionalField"
			/>

			<SelectControl
				label={__('Equation', 'tsjippy')}
				value={rule?.equation || ''}
				options={[
					{ label: __('Select equation', 'tsjippy'), value: '' },
					...equationOptions,
				]}
				onChange={(equation) =>
					onUpdate(conditionIndex, ruleIndex, 'equation', equation)
				}
				help={fieldErrors.equation || ''}
				data-field-key="equation"
			/>

			{renderExtraOptions()}

			<TextControl
				label={__('Value', 'tsjippy')}
				value={rule?.['conditional-value'] || ''}
				onChange={(value) =>
					onUpdate(conditionIndex, ruleIndex, 'conditional-value', value)
				}
				help={fieldErrors.conditionalValue || ''}
				data-field-key="conditionalValue"
			/>

			{/* AND / OR combinator controls. */}
			<div className="rule-row__combinator">
				<Button
					variant={rule?.combinator === 'and' ? 'primary' : 'secondary'}
					isPressed={rule?.combinator === 'and'}
					aria-pressed={rule?.combinator === 'and'}
					onClick={() => onUpdate(conditionIndex, ruleIndex, 'combinator', 'and')}
					icon={row}
				>
					{__('AND', 'tsjippy')}

				</Button>

				<Button
					variant={rule?.combinator === 'or' ? 'primary' : 'secondary'}
					isPressed={rule?.combinator === 'or'}
					aria-pressed={rule?.combinator === 'or'}
					onClick={() => onUpdate(conditionIndex, ruleIndex, 'combinator', 'or')}
					icon={row}
				>
					{__('OR', 'tsjippy')}
				</Button>

				{canMoveRuleUp && (
					<Button
						variant="secondary"
						onClick={onMoveRuleUp}
						icon={arrowUp}
						style= {{width: '140px'}}
					>
						{__('Move rule up', 'tsjippy')}
					</Button>
				)}

				{canMoveRuleDown && (
					<Button
						variant="secondary"
						onClick={onMoveRuleDown}
						icon={arrowDown}
					>
						{__('Move rule down', 'tsjippy')}
					</Button>
				)}

				<Button
					variant="secondary"
					isDestructive
					onClick={onDeleteRule}
					icon={trash}
				>
					{__('Delete rule', 'tsjippy')}
				</Button>
			</div>
		</div>
	);
}