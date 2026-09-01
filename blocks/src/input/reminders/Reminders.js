import { Button, SelectControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const EQUATIONS = [
	{ label: '---', value: '' },
	{ label: 'equals', value: '==' },
	{ label: 'is not', value: '!=' },
	{ label: 'greater than', value: '>' },
	{ label: 'smaller than', value: '<' },
	{ label: 'has submitted', value: 'submitted' },
];

export default function ReminderConditions({
	conditions = [],
	roles = {},
	metaKeys = {},
	onChange,
}) {
	const updateCondition = (index, field, value) => {
		const updated = [...conditions];

		updated[index] = {
			...updated[index],
			value,
		};

		onChange(updated);
	};

	const addCondition = (index, combinator) => {
		const updated = [...conditions];

		updated[index] = {
			...updated[index],
			combinator,
		};

		updated.push({
			'meta-key': '',
			equation: '',
			'conditional-value': '',
			combinator: '',
		});

		onChange(updated);
	};

	const removeCondition = (index) => {
		onChange(conditions.filter((_, i) => i !== index));
	};

	return (
		<div className="conditions-wrapper">
            <SelectControl
                multiple
                label="Do not remind if user has role"
                value={roles}
                options={Object.entries(allRoles).map(([value, label]) => ({
                    value,
                    label,
                }))}
                onChange={(selected) =>
                    setAttributes({ roles: selected })
                }
            />
            
			{conditions.map((condition, index) => {
				const metaKey = condition['meta-key'];
				const arrayKeys =
					metaKeys?.[metaKey]?.keys || [];

				return (
					<div
						key={index}
						className="warning-conditions block-conditions"
					>
						<TextControl
							label="Meta Key"
							value={metaKey || ''}
							onChange={(value) =>
								updateCondition(
									index,
									'meta-key',
									value
								)
							}
						/>

						{arrayKeys.length > 0 && (
							<TextControl
								label="Index"
								value={
									condition['meta-key-index'] || ''
								}
								onChange={(value) =>
									updateCondition(
										index,
										'meta-key-index',
										value
									)
								}
							/>
						)}

						<SelectControl
							value={condition.equation || ''}
							options={EQUATIONS}
							onChange={(value) =>
								updateCondition(
									index,
									'equation',
									value
								)
							}
						/>

						{condition.equation !== 'submitted' && (
							<TextControl
								label="Value"
								value={
									condition['conditional-value'] || ''
								}
								onChange={(value) =>
									updateCondition(
										index,
										'conditional-value',
										value
									)
								}
							/>
						)}

						<div className="condition-actions">
							<Button
								variant={
									condition.combinator === 'and'
										? 'primary'
										: 'secondary'
								}
								onClick={() =>
									addCondition(index, 'and')
								}
							>
								AND
							</Button>

							<Button
								variant={
									condition.combinator === 'or'
										? 'primary'
										: 'secondary'
								}
								onClick={() =>
									addCondition(index, 'or')
								}
							>
								OR
							</Button>

							<Button
								isDestructive
								onClick={() =>
									removeCondition(index)
								}
							>
								Remove
							</Button>
						</div>
					</div>
				);
			})}
		</div>
	);
}