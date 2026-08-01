export const RenderedSelect = ({ attributes, blockProps, dynamicOptions = {}}) => {
	const staticOptions = (attributes.options || '')
		.split('\n')
		.filter(Boolean)
		.map((option) => {
			const [key, value] = option.split('|');
			return [{
				value: key.trim(),
				label: (value || key).trim(),
			}];
		});

	const options = [
		...staticOptions,
		...Object.entries(dynamicOptions?.[attributes.options_dynamic] || {})?.map(
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
				console.log(option);
				return (
					<option
						key={index}
						value={option.value}
					>
						{option.label}
					</option>
				);
			})}
		</select>
    );
};