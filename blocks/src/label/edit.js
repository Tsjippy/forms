import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Button, Dropdown, SelectControl, PanelBody, TextControl, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import './editor.scss';
import { Multiple } from './../input/components/Multiple.js';
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
	 * Check for child blocks
	 */
	const hasInnerBlocks = useSelect(
        ( select ) => {
            const { getBlock } 	= select( 'core/block-editor' );
            const block 		= getBlock( clientId );
            return !!( block && block.innerBlocks.length > 0 );
        },
        [ clientId ]
    );

	/**
	 * Set a debounce for the label text input so it disappears when we stop typing, not straight after the first character
	 */
	const [labelText, setLabelText] = useState(attributes.text);

	useEffect(() => {
		setLabelText(attributes.text);
	}, [attributes.text]);

	useEffect(() => {
		const timeoutId = setTimeout(() => {
			setAttributes({ text: labelText })
		}, 800);

		return () => clearTimeout(timeoutId);
	}, [ labelText ]);

	/**
	 * Store some child attributes in our own attributes
	 */
	// Get the inner block
	const innerBlocks = useSelect(
		(select) =>
			select('core/block-editor').getBlocks(clientId),
		[clientId]
	);

	// store in the childAttr attribute
	const childBlockAttrs = innerBlocks[0]?.attributes;

	useEffect(() => {
		if (!childBlockAttrs) {
			return;
		}

		setAttributes({
			childAttr: {
				multiple: childBlockAttrs.multiple ?? false,
				add_button_content:
					childBlockAttrs.add_button_content ?? '+',
				remove_button_content:
					childBlockAttrs.remove_button_content ?? '-',
				type: childBlockAttrs.type ?? '',
				hidden: childBlockAttrs.hidden ?? '',
			},
		});
	}, [
		childBlockAttrs?.multiple,
		childBlockAttrs?.add_button_content,
		childBlockAttrs?.remove_button_content,
		childBlockAttrs?.type,
		childBlockAttrs?.hidden
	]);

	const labelComponent	= (
		<label >
			{ attributes.text }
			<InnerBlocks
				allowedBlocks={['tsjippy-forms/input']}
				orientation="vertical"
				renderAppender={false}
			/>
		</label>
	);

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Label Settings', 'tsjippy')}>
				<TextControl
					label    = "Label Text"
					value    = { labelText }
					onChange = { ( text ) => setLabelText( text )}
				/>
			</PanelBody>
		</InspectorControls>
    			
		<fieldset { ...blockProps }>
			<legend>Label</legend>
			{
				attributes.text == '' ?
					<TextControl
						label    = "Label Text"
						value    = { labelText }
						onChange = { ( text ) => setLabelText( text )}
					/> 
				:
					!hasInnerBlocks ?
						<>
							{ attributes.text }
							<Placeholder
								icon			= "layout"
								label			= { __("Add an input to this label", 'tsjippy') }
								instructions	= "Click to add a block"
							>
								{ /* Add the add button */ }
								<InnerBlocks
									allowedBlocks={['tsjippy-forms/input']}
									orientation="vertical"
									renderAppender={InnerBlocks.ButtonBlockAppender}
								/>
							</Placeholder>
						</>
					: 
						attributes.childAttr.multiple ?
							<Multiple
								inner      = { labelComponent }
								attributes = { attributes.childAttr }
							/>
						:
							labelComponent
			}
		</fieldset>
		</>
	);
}
