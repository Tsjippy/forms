import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { PrefillOptionsSelector, PrefillValueSelector, usePrefill } from '../../shared/usePrefill.js';
import { RenderedSelect } from './components/RenderedSelect.js';
import AddOptions from '../../shared/AddOptions';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes, clientId }) {
	const blockProps = useBlockProps();

	const {
		data: prefillData,
		isLoading,
	} = usePrefill();

	/**
	 * Set a debounce for the id input so it disappears when we stop typing, not straight after the first character
	 */
	const [name, setName] = useState(attributes.name);

	// Update the name when the attribute changes
	useEffect(() => {
		setName(attributes.name);
	}, [attributes.name]);

	// Update the attribute with a delay
	useEffect(() => {
		const timeoutId = setTimeout(() => {
			setAttributes({ name: name })
		}, 800);

		return () => clearTimeout(timeoutId);
	}, [ name, attributes.name, setAttributes]);

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Dropdown Selector Settings', 'tsjippy')}>
				<TextControl
					label    = { __("Name", 'tsjippy')}
					value    = { name }
					onChange = { ( value ) => setName( value )}
				/>
				<h4>Selectable Options</h4>
				<AddOptions
					attributes={attributes}
					setAttributes={setAttributes}
					/>

				<h4>Dynamic Options</h4>
				<PrefillOptionsSelector
					value    = { attributes.options_dynamic }
					onChange = { (value) => setAttributes({ options_dynamic: value }) }
				/>

				<h4>Dynamic Value</h4>
				<PrefillValueSelector
					value    = { attributes.dynamic_selected_value }
					onChange = { (value) => setAttributes({ dynamic_selected_value: value }) }
				/>

				<ToggleControl
					label    = { __('Autofocus', 'tsjippy') }
					checked  = {!!attributes.autofocus}
					onChange = { ( checked ) => setAttributes({ autofocus: checked })}
				/>

				<ToggleControl
					label    = { __('Disabled', 'tsjippy') }
					checked  = {!!attributes.disabled}
					onChange = { ( checked ) => setAttributes({ disabled: checked })}
				/>

				<ToggleControl
					label    = { __('Allow multiple selections', 'tsjippy') }
					checked  = {!!attributes.multiple}
					onChange = { ( checked ) => setAttributes({ multiple: checked })}
				/>

				<ToggleControl
					label    = { __('Required', 'tsjippy') }
					checked  = {!!attributes.required}
					onChange = { ( checked ) => setAttributes({ required: checked })}
				/>
			</PanelBody>
		</InspectorControls>
    			
		<fieldset { ...blockProps }>
			<legend>Dropdown Select</legend>
				{
					attributes.name == '' ?
						<TextControl
							label    = { __("Name", 'tsjippy')}
							value    = { name }
							onChange = { ( value ) => setName( value )}
						/> 
					:
						isLoading || !prefillData
						? 	<Spinner />
						:
						<RenderedSelect
							attributes     = { attributes }
							blockProps 	   = { blockProps }
							dynamicOptions = { prefillData.multi }
							defaultValue   = { attributes.dynamic_selected_value || '' }
						/>
				}
		</fieldset>
		</>
	);
}
