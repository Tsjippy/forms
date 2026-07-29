import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

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

	/**
	 * Set a debounce for the id input so it disappears when we stop typing, not straight after the first character
	 */
	const [name, setName] = useState(attributes.name);

	useEffect(() => {
		setName(attributes.name);
	}, [attributes.name]);

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

				<TextareaControl
					label    = { __("Datalist Options", 'tsjippy')}
					help     = { __("One option per line. If the value and label differ separate them with a |  i.e. car|auto", 'tsjippy')}
					value    = { attributes.options }
					onChange = { ( value ) => setAttributes({ options: value }) }
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
				<TextControl
					label    = { __("Name", 'tsjippy')}
					value    = { name }
					onChange = { ( value ) => setName( value )}
				/> 

				<TextareaControl
					label    = { __("Selectable Options", 'tsjippy')}
					help     = { __("One option per line. If the value and label differ separate them with a |  i.e. car|auto", 'tsjippy')}
					value    = { attributes.options }
					onChange = { ( value ) => setAttributes({ options: value }) }
				/>
		</fieldset>
		</>
	);
}
