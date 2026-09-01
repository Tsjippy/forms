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
	formBlockOptions,
	onUpdate,
	onDeleteRule,
	onMoveRuleUp,
	onMoveRuleDown,
	canMoveRuleUp,
	canMoveRuleDown,
	ruleErrors = {},
}) {
	/* Available equation choices for the main equation dropdown. */
	const equationOptions = [
		{ label: __('has changed', 'tsjippy'), value: 'changed' },
		{ label: __('is clicked', 'tsjippy'), value: 'clicked' },
		{ label: __('Equals', 'tsjippy'), value: '==' },
		{ label: __('Does not equal', 'tsjippy'), value: '!=' },
		{ label: __('Greater than', 'tsjippy'), value: '>' },
		{ label: __('Less than', 'tsjippy'), value: '<' },
		{ label: __('Is checked', 'tsjippy'), value: 'checked' },
		{ label: __('Is not checked', 'tsjippy'), value: '!checked' },
		{ label: __('Equals the value of', 'tsjippy'), value: '== value' },
		{ label: __('Does not equal the value of', 'tsjippy'), value: '!= value' },
		{ label: __('Greater than the value of', 'tsjippy'), value: '> value' },
		{ label: __('Less than the value of', 'tsjippy'), value: '< value' },
		{ label: __('Plus the value of', 'tsjippy'), value: '+' },
		{ label: __('Minus the value of', 'tsjippy'), value: '-' },
		{ label: __('Is visible', 'tsjippy'), value: 'visible' },
		{ label: __('Is not visible', 'tsjippy'), value: 'invisible' },
	];

	/* Render the editable UI for one rule entry. */
	return (
		<div className={`rule-row inner ${
			Object.keys(ruleErrors).length > 0  ? 'invalid' : ''
		}`}>			
			<SelectControl
				label={__('Conditional Field', 'tsjippy')}
				value={rule?.['conditional-field'] || ''}
				options={[
					{ label: __('Select block', 'tsjippy'), value: '' },
					...(formBlockOptions || []),
				]}
				onChange={(block) =>
					onUpdate(conditionIndex, ruleIndex, 'conditional-field', block)
				}
				help={ruleErrors.conditionalField || ''}
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
				help={ruleErrors.equation || ''}
				data-field-key="equation"
			/>

			{(rule?.equation ?? '') !== '' && (
				<>
					{['== value', '!= value', '> value', '< value', '+', '-'].includes(rule.equation) && (
						<SelectControl
							label={__('Second block', 'tsjippy')}
							value={rule?.['conditional-field-2'] || ''}
							options={[
								{ label: __('Select second block', 'tsjippy'), value: '' },
								...(formBlockOptions || []),
							]}
							onChange={(block) =>
								onUpdate(conditionIndex, ruleIndex, 'conditional-field-2', block)
							}
							help={ruleErrors.conditionalField2 || ''}
							data-field-key="conditionalField2"
						/>
					)}

					{['+', '-'].includes(rule.equation) && (
						<SelectControl
							label={__('Second equation', 'tsjippy')}
							value={rule?.['equation-2'] || ''}
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
							help={ruleErrors.equation2 || ''}
							data-field-key="equation2"
						/>
					)}

					{['==', '!=', '>','<', '+', '-'].includes(rule.equation) && (
						<TextControl
							label={__('Value', 'tsjippy')}
							value={rule?.['conditional-value'] || ''}
							onChange={(value) =>
								onUpdate(conditionIndex, ruleIndex, 'conditional-value', value)
							}
							help={ruleErrors.conditionalValue || ''}
							data-field-key="conditionalValue"
						/>
					)}
				</>
			)}

			{/* AND / OR combinator controls. */}
			<div className="combinator">
				<Button
					variant={rule?.combinator === '&&' ? 'primary' : 'secondary'}
					isPressed={rule?.combinator === '&&'}
					aria-pressed={rule?.combinator === '&&'}
					onClick={() => onUpdate(conditionIndex, ruleIndex, 'combinator', '&&')}
					icon={row}
				>
					{__('AND', 'tsjippy')}

				</Button>

				<Button
					variant={rule?.combinator === '||' ? 'primary' : 'secondary'}
					isPressed={rule?.combinator === '||'}
					aria-pressed={rule?.combinator === '||'}
					onClick={() => onUpdate(conditionIndex, ruleIndex, 'combinator', '||')}
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