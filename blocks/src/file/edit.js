import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Rendered } from './components/Rendered.js';

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
			setAttributes({ name });
		}, 800);

		return () => clearTimeout(timeoutId);
	}, [name, setAttributes]);

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Dropdown Selector Settings', 'tsjippy')}>
				<TextControl
					label    = { __("Name", 'tsjippy')}
					value    = { name }
					onChange = { ( value ) => setName( value )}
				/>

				<ToggleControl
					label    = { __('Disabled', 'tsjippy') }
					checked  = {!!attributes.disabled}
					onChange = { ( checked ) => setAttributes({ disabled: checked })}
				/>

				<ToggleControl
					label    = { __('Allow multiple files', 'tsjippy') }
					checked  = {!!attributes.multiple}
					onChange = { ( checked ) => setAttributes({ multiple: checked })}
				/>

				<ToggleControl
					label    = { __('Required', 'tsjippy') }
					checked  = {!!attributes.required}
					onChange = { ( checked ) => setAttributes({ required: checked })}
				/>

				<TextControl
					label={__("Target Directory For The Uploads", 'tsjippy')}
					value={attributes.targetDir || ''}
					onChange={(value) => setAttributes({ targetDir: value })}
				/>

				<ToggleControl
					label    = { __('Add to the Media Library', 'tsjippy') }
					checked  = {!!attributes.library}
					onChange = { ( checked ) => setAttributes({ library: checked })}
				/>

				<ToggleControl
					label    = { __('Allow users to edit the image before upload', 'tsjippy') }
					checked  = {!!attributes.edit}
					onChange = { ( checked ) => setAttributes({ edit: checked })}
				/>

				<TextControl
					label    = { __("Store the uploaded file path/attachment id in this user meta key", 'tsjippy')}
					value    = { attributes.metaKey || '' }
					onChange = { ( value ) => setAttributes({ metaKey: value })}
				/>
			</PanelBody>
		</InspectorControls>
    			
		<fieldset { ...blockProps }>
			<legend>File Uploader</legend>
				{
					attributes.name == '' ?
						<TextControl
							label    = { __("Name", 'tsjippy')}
							value    = { name }
							onChange = { ( value ) => setName( value )}
						/> 
					:
						<Rendered
							attributes     = { attributes }
						/>
				}
		</fieldset>
		</>
	);
}
