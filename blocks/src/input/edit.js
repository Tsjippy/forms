import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Button, Dropdown, SelectControl, PanelBody, TextControl, Disabled, ToggleControl, __experimentalNumberControl as NumberControl, CheckboxControl, RadioControl, TextareaControl  } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

import './editor.scss';
import * as elementAttributes from './element_attributes.js';
import { dynamicInputs } from './dynamic_inputs.js';
import { InputHtml } from './components/InputHtml.js';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes, isSelected, clientId }) {
	const blockProps = useBlockProps();

	const getTypeOptions = () => {
		let typeOptions	= [
			{label: __('Select an input type', 'tsjippy'), value: '' }
		];

		elementAttributes.inputTypes.forEach( type => { 
			typeOptions.push( {label: type, value: type });
		}); 
	
		return typeOptions;
	}

	/**
	 * For a submit type the value is what is shown in the button
	 */
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
	 * Set a debounce for the label text input so it disappears when we stop typing, not straight after the first character
	 */
	const [inputName, setInputName] = useState(attributes.name);

	useEffect(() => {
		const timeoutId = setTimeout(() => {
			setAttributes({ name: inputName })
		}, 800);

		return () => clearTimeout(timeoutId);
	}, [inputName, 800]);

	/**
	 * The input name component
	 */
	const inputNameComponent = () => {
		return (
			<TextControl
				label    = "Input Name"
				value    = { inputName }
				onChange = { ( name ) => setInputName( name )}
			/>
		)
	}

    const hasLabelParent = useSelect(
        (select) =>
            select('core/block-editor')
                .getBlockParentsByBlockName(clientId, 'tsjippy-forms/label')
                .length > 0,
        [clientId]
    );

	useEffect(() => {
		if (attributes.hasLabelParent !== hasLabelParent) {
			setAttributes({ hasLabelParent });
		}
	}, [hasLabelParent]);

	/**
	 * Shows the input attributes form if this is an selected input
	 * 
	 * @returns 
	 */
	const propertiesForm = () => {
		if(!isSelected){
			return(
				<InputHtml
					attributes={attributes}
					blockProps={blockProps}
					hasLabelParent={hasLabelParent}
				/>
			)
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
				[
					inputTypeSelector(),
					inputNameComponent()
				]
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

		/**
		 * Input type specific options
		 */
		const inputTypeSpecificOptions = () => {
			if(['radio', 'checkbox', 'select'].includes(attributes.type)){
				return (
					<TextareaControl
						label    = { __("Selectable Options", 'tsjippy')}
						help     = "One option per line. If the value and label differ seperate them with a |  i.e. car|auto"
						value    = { attributes.selectable_options }
						onChange = { ( value ) => setAttributes({ selectable_options: value }) }
					/>
				);
			}
		}

		return ( 
			<>
			<InputHtml
				attributes={attributes}
				blockProps={blockProps}
				hasLabelParent={hasLabelParent}
			/>

			{ inputTypeSelector() }
			{ inputNameComponent() }
			{ inputTypeSpecificOptions() }
			<div className="attributes-form">
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
				{ inputNameComponent() }
				{ inputValue() }

				<ToggleControl
					label    = { __('Hide', 'tsjippy') }
					checked  = {!!attributes.hidden}
					onChange = { ( checked ) => setAttributes({ hidden: checked })}
				/>

				<ToggleControl
					label    = { __('Allow multiple answers', 'tsjippy') }
					checked  = {!!attributes.multiple}
					onChange = { ( checked ) => setAttributes({ multiple: checked })}
				/>

				<ToggleControl
					label    = { __('This is a required input', 'tsjippy') }
					checked  = {!!attributes.required}
					onChange = { ( checked ) => setAttributes({ required: checked })}
				/>

				{
					/**
					 * If we allow multiple answer we have a + and - button
					 * This allows to customize that
					 */
					attributes.multiple ?
						<>
							<TextControl
								label    = "Add Button Text"
								value    = { attributes.add_button_content }
								onChange = { ( value ) => setAttributes({ add_button_content: value })}
							/>

							<TextControl
								label    = "Remove Button Text"
								value    = { attributes.remove_button_content }
								onChange = { ( value ) => setAttributes({ remove_button_content: value })}
							/>
						</>
					: ''
}
			</PanelBody>
		</InspectorControls>

		<div { ...blockProps } >
			<fieldset>
    			<legend>
					{ (attributes.type).charAt(0).toUpperCase() + (attributes.type).slice(1) } input
				</legend>
				{ propertiesForm() }
			</fieldset>
		</div>
		</>
	);
}