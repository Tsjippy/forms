import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Button, Dropdown, SelectControl, PanelBody, TextControl, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import './editor.scss';

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
    const { children, ...innerBlocksProps }  = useInnerBlocksProps( blockProps,
		{
			orientation: 'vertical', // Enables drag & drop functionality
		}
	);

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
		const timeoutId = setTimeout(() => {
			setAttributes({ text: labelText })
		}, 800);

		return () => clearTimeout(timeoutId);
	}, [labelText, 800]);

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
    			
		<fieldset { ...innerBlocksProps }>
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
						<Placeholder
							icon			= "layout"
							label			= { __("Add an input to this label", 'tsjippy') }
							instructions	= "Click to add a block"
						>
							{ /* Add the add button */ }
							<InnerBlocks.ButtonBlockAppender />
						</Placeholder>
					: 
						<label >
							{ attributes.text }
							{ children }
						</label>
			}
		</fieldset>
		</>
	);
}
