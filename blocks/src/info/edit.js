import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { InfoBox } from './components/InfoBox.js';

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
	const [text, setText] = useState(attributes.id);

	useEffect(() => {
		setText(attributes.text);
	}, [attributes.text]);

	useEffect(() => {
		const timeoutId = setTimeout(() => {
			setAttributes({ text: text })
		}, 800);

		return () => clearTimeout(timeoutId);
	}, [ text, attributes.text, setAttributes]);

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Info Settings', 'tsjippy')}>
				<TextareaControl
					label    = { __("Info Messages", 'tsjippy')}
					help     = { __("The text which is visible when hovered over the icon", 'tsjippy')}
					value    = { text }
					onChange = { ( value ) => setText( value ) }
				/>
			</PanelBody>
		</InspectorControls>
    			
		<fieldset { ...blockProps }>
			<legend>Info</legend>
				{
					attributes.text == '' ?
						<TextareaControl
							label    = { __("Info Messages", 'tsjippy')}
							help     = { __("The text which is visible when hovered over the icon", 'tsjippy')}
							value    = { text }
							onChange = { ( value ) => setText( value ) }
						/>
					:
						<InfoBox
								text={ attributes.text }
						/>
				}
		</fieldset>
		</>
	);
}
