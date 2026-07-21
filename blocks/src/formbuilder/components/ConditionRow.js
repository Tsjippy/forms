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
	arrowDown
} from '@wordpress/icons';

/**
 * Render one condition row inside a rule group.
 * This component is presentational and sends all updates upward.
 */
export default function ConditionRow({
	condition,
	ruleIndex,
	subRuleIndex,
	formElementOptions,
	onUpdate,
	onDeleteRule,
	onMoveRuleUp,
	onMoveRuleDown,
	canMoveRuleUp,
	canMoveRuleDown,
	fieldErrors = {},
}) {
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
		if (!condition?.equation) {
			return null;
		}

		if (condition.equation === '+' || condition.equation === '-') {
			return (
				<>
					<SelectControl
						label={__('Second element', 'tsjippy')}
						value={condition['conditional-field-2'] || ''}
						options={[
							{ label: __('Select second element', 'tsjippy'), value: '' },
							...(formElementOptions || []),
						]}
						onChange={(element) =>
							onUpdate(ruleIndex, subRuleIndex, 'conditional-field-2', element)
						}
						help={fieldErrors.conditionalField2 || ''}
						data-field-key="conditionalField2"
					/>

					<SelectControl
						label={__('Second equation', 'tsjippy')}
						value={condition['equation-2'] || ''}
						options={[
							{ label: __('Select second equation', 'tsjippy'), value: '' },
							{ label: __('Equals', 'tsjippy'), value: '==' },
							{ label: __('Does not equal', 'tsjippy'), value: '!=' },
							{ label: __('Greater than', 'tsjippy'), value: '>' },
							{ label: __('Less than', 'tsjippy'), value: '<' },
						]}
						onChange={(equation2) =>
							onUpdate(ruleIndex, subRuleIndex, 'equation-2', equation2)
						}
						help={fieldErrors.equation2 || ''}
						data-field-key="equation2"
					/>
				</>
			);
		}

		return null;
	};

	/* Render the editable UI for one condition entry. */
	return (
		<div className="condition-row__inner">
			<SelectControl
				label="Size"
				value= '50%'
				options={ [
					{ label: 'Big', value: '100%' },
					{ label: 'Medium', value: '50%' },
					{ label: 'Small', value: '25%' },
				] }
				onChange={ ( newSize ) => setSize( newSize ) }
			/>
			
			<SelectControl
				label={__('Conditional field', 'tsjippy')}
				value={condition?.['conditional-field'] || ''}
				options={[
					{ label: __('Select element', 'tsjippy'), value: '' },
					...(formElementOptions || []),
				]}
				onChange={(element) =>
					onUpdate(ruleIndex, subRuleIndex, 'conditional-field', element)
				}
				help={fieldErrors.conditionalField || ''}
				data-field-key="conditionalField"
			/>

			<SelectControl
				label={__('Equation', 'tsjippy')}
				value={condition?.equation || ''}
				options={[
					{ label: __('Select equation', 'tsjippy'), value: '' },
					...equationOptions,
				]}
				onChange={(equation) =>
					onUpdate(ruleIndex, subRuleIndex, 'equation', equation)
				}
				help={fieldErrors.equation || ''}
				data-field-key="equation"
			/>

			{renderExtraOptions()}

			{condition?.equation &&
				condition.equation !== '+' &&
				condition.equation !== '-' && (
					<TextControl
						label={__('Value', 'tsjippy')}
						value={condition?.['conditional-value'] || ''}
						onChange={(value) =>
							onUpdate(ruleIndex, subRuleIndex, 'conditional-value', value)
						}
						help={fieldErrors.conditionalValue || ''}
						data-field-key="conditionalValue"
					/>
				)}

			{/* AND / OR combinator controls. */}
			<div className="condition-row__combinator">
				<Button
					variant={condition?.combinator === 'and' ? 'primary' : 'secondary'}
					isPressed={condition?.combinator === 'and'}
					aria-pressed={condition?.combinator === 'and'}
					onClick={() => onUpdate(ruleIndex, subRuleIndex, 'combinator', 'and')}
				>
					{__('AND', 'tsjippy')}
				</Button>

				<Button
					variant={condition?.combinator === 'or' ? 'primary' : 'secondary'}
					isPressed={condition?.combinator === 'or'}
					aria-pressed={condition?.combinator === 'or'}
					onClick={() => onUpdate(ruleIndex, subRuleIndex, 'combinator', 'or')}
				>
					{__('OR', 'tsjippy')}
				</Button>

				{canMoveRuleUp && (
					<Button
						variant="secondary"
						onClick={onMoveRuleUp}
						icon={arrowUp}
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