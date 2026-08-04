import {
	PanelBody,
	RadioControl,
	TextControl,
} from '@wordpress/components';

import ConditionalRules from './ConditionalRules';

export default function EmailAddressPanel({
	title,
	value,
	formElements,
	onChange,
}) {
	const update = (changes) => {
		onChange({
			...value,
			...changes,
		});
	};

	return (
		<PanelBody
			title={title}
			initialOpen={false}
		>
			<RadioControl
				selected={value.type}
				options={[
					{
						label:
							'Fixed email address',
						value: 'fixed',
					},
					{
						label:
							'Conditional email address',
						value: 'conditional',
					},
				]}
				onChange={(type) =>
					update({ type })
				}
			/>

			{value.type === 'fixed' && (
				<TextControl
					label="Email Address"
					value={value.email || ''}
					onChange={(email) =>
						update({ email })
					}
				/>
			)}

			{value.type ===
				'conditional' && (
				<>
					<ConditionalRules
						rules={
							value.rules || []
						}
						formElements={
							formElements
						}
						onChange={(rules) =>
							update({
								rules,
							})
						}
					/>

					<TextControl
						label="Else Email Address"
						value={
							value.elseEmail ||
							''
						}
						onChange={(
							elseEmail
						) =>
							update({
								elseEmail,
							})
						}
					/>
				</>
			)}
		</PanelBody>
	);
}