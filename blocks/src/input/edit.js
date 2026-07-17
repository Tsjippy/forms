import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Button, Dropdown, SelectControl, PanelBody, TextControl, Disabled } from '@wordpress/components';
import './editor.scss';

const MY_TEMPLATE = [
    [ 'core/image', {} ],
    [ 'core/heading', { placeholder: 'Book Title' } ],
    [ 'core/paragraph', { placeholder: 'Summary' } ],
];

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
    const { children, ...innerBlocksProps }  = useInnerBlocksProps( blockProps, {
		template: MY_TEMPLATE
	});

	const getTypeOptions = () => {
		let typeOptions	= [];
		[
			"button",
			"checkbox",
			"color",
			"date",
			"datetime-local",
			"email",
			"file",
			"hidden",
			"image",
			"month",
			"number",
			"password",
			"radio",
			"range",
			"reset",
			"search",
			"submit",
			"tel",
			"text",
			"time",
			"url",
			"week",
		].forEach( type => { 
			typeOptions.push( {label: type, value: type });
		}); 
	
		return typeOptions;
	}

	const inputValue = () => {
		if(attributes.type != 'submit'){
			return '';
		}

		return (
			<TextControl
				label    = "Input Content"
				value    = { attributes.value }
				onChange = { ( value ) => setAttributes({ value: value })}
			/>
		);
	}

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Input Settings', 'tsjippy')}>
				<SelectControl
					label    = "Input Type"
					value    = { attributes.type }
					options  = { getTypeOptions() }
					onChange = { ( type ) => setAttributes({ type: type })}
				/>
				<TextControl
					label    = "Input Name"
					value    = { attributes.name }
					onChange = { ( name ) => setAttributes({ name: name })}
				/>
				{ inputValue() }
			</PanelBody>
		</InspectorControls>
		<div { ...blockProps }>
			<input type={ attributes.type } name={ attributes.name } value={ attributes.value } class='formbuilder'/>
		</div>
		</>
	);
}