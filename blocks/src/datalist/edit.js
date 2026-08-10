import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { PrefillOptionsSelector } from '../../shared/usePrefill.js';
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

	/**
	 * Set a debounce for the id input so it disappears when we stop typing, not straight after the first character
	 */
	const [listId, setListId] = useState(attributes.id);

	useEffect(() => {
		setListId(attributes.id);
	}, [attributes.id]);

	useEffect(() => {
		const timeoutId = setTimeout(() => {
			setAttributes({ id: listId })
		}, 800);

		return () => clearTimeout(timeoutId);
	}, [ listId, attributes.id, setAttributes]);

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Datalist Settings', 'tsjippy')}>
				<TextControl
					label    = { __("Datalist Id", 'tsjippy')}
					value    = { listId }
					onChange = { ( value ) => setListId( value )}
				/>

				<h4>Static Options</h4>
				<AddOptions
					attributes={attributes}
					setAttributes={setAttributes}
					/>
				
				<h4>Dynamic Options (prefill)</h4>
				<PrefillOptionsSelector
					value={ attributes.options_dynamic }
					onChange={ (value) => setAttributes({ options_dynamic: value }) }
				/>
			</PanelBody>
		</InspectorControls>
    			
		<fieldset { ...blockProps }>
			<legend>Datalist</legend>
				<TextControl
					label    = { __("Datalist Id", 'tsjippy')}
					value    = { listId }
					onChange = { ( value ) => setListId( value )}
				/> 

				<h4>Dynamic Options (prefill)</h4>
				<PrefillOptionsSelector
					value={ attributes.options_dynamic }
					onChange={ (value) => setAttributes({ options_dynamic: value }) }
				/>
		</fieldset>
		</>
	);
}
