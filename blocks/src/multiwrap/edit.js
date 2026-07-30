import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Placeholder } from '@wordpress/components';
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
	 * Store some child attributes in our own attributes
	 */
	// Get the inner block
	const innerBlocks = useSelect(
		(select) =>
			select('core/block-editor').getBlocks(clientId),
		[clientId]
	);

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Multi Wrap Settings', 'tsjippy')}>
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
			</PanelBody>
		</InspectorControls>
    			
		<fieldset { ...blockProps }>
			<legend>Multi Wrap</legend>
			{
				!hasInnerBlocks ?
					<>
						<Placeholder
							icon			= "layout"
							label			= { __("Add an input to this multi input wrapper", 'tsjippy') }
							instructions	= "Click to add a block"
						>
							{ /* Add the add button */ }
							<InnerBlocks
								orientation="vertical"
								renderAppender={InnerBlocks.ButtonBlockAppender}
							/>
						</Placeholder>
					</>
				: 
					<div class="clone-divs-wrapper">
						<div class="clone-div formstep step-hidden" data-div-id="0">
							<div class="button-wrapper" style="margin: auto; display:flex;">
								<button
									type="button"
									className="remove button hidden"
									style={{ flex: 1, maxWidth: 'max-content' }}
								>
									{ attributes.remove_button_content }
								</button>

								<button
									type="button"
									className="add button"
									style={{ flex: 1, maxWidth: 'max-content' }}
								>
									{ attributes.add_button_content }
								</button>

								<InnerBlocks.Content />
							</div>
						</div>
					</div>
			}
		</fieldset>
		</>
	);
}
