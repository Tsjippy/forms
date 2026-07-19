import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Button, Dropdown, SelectControl, PanelBody, TextControl, Disabled, ToggleControl, __experimentalNumberControl as NumberControl, CheckboxControl, RadioControl  } from '@wordpress/components';
import './editor.scss';
import * as elementAttributes from './element_attributes.js';
import { dynamicInputs } from './dynamic_inputs.js';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes, isSelected }) {
	const blockProps = useBlockProps();

	const getTypeOptions = () => {
		let typeOptions	= [];
		elementAttributes.inputTypes.forEach( type => { 
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

	/**
	 * Stores the input attribute value
	 */
	const storeAttributeAttributes = (value, name) => {

		let inputAttributes	= {... attributes.inputAttributes};

		inputAttributes[name]	= value;

		setAttributes({ inputAttributes: inputAttributes })
	}

	/**
	 * The input type selector
	 */
	const inputTypeSelector = () => {
		return (
			<SelectControl
				label    = "Input Type"
				value    = { attributes.type }
				options  = { getTypeOptions() }
				onChange = { ( type ) => setAttributes({ type: type })}
			/>
		)
	}

	/**
	 * The input name component
	 */
	const inputName = () => {
		return (
			<TextControl
				label    = "Input Name"
				value    = { attributes.name }
				onChange = { ( name ) => setAttributes({ name: name })}
			/>
		)
	}

	/**
	 * Shows the input attributes form if this is an selected input
	 * 
	 * @returns 
	 */
	const propertiesForm = () => {
		if(!isSelected){
			return '';
		}

		// First set an input type
		if(attributes.type == ''){
			return (
				inputTypeSelector()
			);
		}

		// Then set a name
		if(attributes.name == ''){
			return (
				inputName()
			);
		}
		
		let attributeControls	= dynamicInputs(elementAttributes.inputSchema.sharedAttributes, attributes.inputAttributes, storeAttributeAttributes);

		let ariaControls 		= [];

		/**
		 * Add aria attributes if we need them
		 */
		if(attributes.ariaAttributes){
			ariaControls	= dynamicInputs(elementAttributes.inputSchema.ariaAttributes, attributes.inputAttributes, storeAttributeAttributes);
		}		

		return ( 
			<>
			{ inputTypeSelector() }
			{ inputName() }
			<div class="attributes-form">
				<h3>Input properties</h3>
				{ attributeControls }

				<ToggleControl
					label    = { __('Add aria attributes', 'tsjippy') }
					checked  = {!!attributes.ariaAttributes}
					onChange = { ( checked ) => setAttributes({ ariaAttributes: checked })}
				/>
				{ariaControls}
			</div> 
			</>
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

		<div { ...blockProps } >
			<fieldset>
    			<legend>
					{ (attributes.type).charAt(0).toUpperCase() + (attributes.type).slice(1) } input
				</legend>
				<input type={ attributes.type } name={ attributes.name } value={ attributes.value } class='formbuilder'/>
				{ propertiesForm() }
			</fieldset>
		</div>
		</>
	);
}