import {
	Button,
	SelectControl,
	TextControl,
} from '@wordpress/components';

export default function ConditionalRules({
	rules = [],
	formBlocks = [],
	onChange,
}) {
	const fieldOptions = [
		{
			label: 'Select field',
			value: '',
		},
		...formBlocks.map((field) => ({
			label: field.label,
			value: field.id,
		})),
	];

	const addRule = () => {
		onChange([
			...rules,
			{
				field: '',
				value: '',
				email: '',
			},
		]);
	};

	const updateRule = (index, changes) => {
		const updated = [...rules];

		updated[index] = {
			...updated[index],
			...changes,
		};

		onChange(updated);
	};

	const removeRule = (index) => {
		onChange(
			rules.filter((_, i) => i !== index)
		);
	};

	return (
		<div className="conditional-rules">
			{rules.map((rule, index) => (
				<div
					key={index}
					className="conditional-rule"
					style={{
						border: '1px solid #ddd',
						padding: '12px',
						marginBottom: '10px',
					}}
				>
					<h4>Condition {index + 1}</h4>

					<SelectControl
						label="Field"
						value={rule.field}
						options={fieldOptions}
						onChange={(field) =>
							updateRule(index, {
								field,
							})
						}
					/>

					<TextControl
						label="Equals"
						value={rule.value}
						onChange={(value) =>
							updateRule(index, {
								value,
							})
						}
					/>

					<TextControl
						label="Email Address"
						value={rule.email}
						onChange={(email) =>
							updateRule(index, {
								email,
							})
						}
					/>

					<Button
						isDestructive
						onClick={() =>
							removeRule(index)
						}
					>
						Remove Condition
					</Button>
				</div>
			))}

			<Button
				variant="secondary"
				onClick={addRule}
			>
				Add Condition
			</Button>
		</div>
	);
}