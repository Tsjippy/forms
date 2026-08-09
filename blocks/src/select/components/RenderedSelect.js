export const RenderedSelect = ({ attributes, blockProps, dynamicOptions = {}}) => {

	const options = [
		{
			value: '',
			label: 'Select an option',
		},
		...attributes.options,
		...Object.entries(dynamicOptions?.[attributes.options_dynamic ?? ''] || {})?.map(
			([key, value]) => ({
				value: String(key).trim(),
				label: String(value || key).trim(),
			}))
	];

	return (
		<select
			{...blockProps}
			name={attributes.name}
			autoFocus={attributes.autofocus}
			disabled={attributes.disabled}
			multiple={attributes.multiple}
			required={attributes.required}
			data-blockid={attributes.blockId}
		>
			{options.map((option, index) => {
				return (
					<option
						key={index}
						value={option.value}
					>
						{option.label}
					</option>
				);
			})}
			%options-placeholder%
		</select>
    );
};