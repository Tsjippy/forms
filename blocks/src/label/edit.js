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
    const { children, ...innerBlocksProps }  = useInnerBlocksProps( blockProps );

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

	/**
	 * Check if input in this label can have multiple answers
	 */
	const innerBlocks = useSelect(
		(select) =>
			select('core/block-editor').getBlocks(clientId),
		[clientId]
	);

	const isMultiple = innerBlocks.some(
		(block) => block.attributes.multiple
	);
	setAttributes({ isMultiple });

	const labelComponent	= (
		<label >
			{ attributes.text }
			{ children }
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
							<InnerBlocks
								orientation="vertical"
								renderAppender={InnerBlocks.ButtonBlockAppender}
							/>
						</Placeholder>
					: 
						isMultiple ?
							<div className="input-wrapper required flex" style= {{width: "85%"}}>
								<div className="clone-divs-wrapper">
									<div className="clone-div" data-div-id="0">
										<div className="button-wrapper" style={{ margin: 'auto', display:'flex'}}>
											{labelComponent}
											<button type="button" className="add button" style={{ flex: 1, maxWidth: 'max-content'}}>
												+
											</button>
										</div>
									</div>
								</div>
							</div>
						:
							labelComponent
			}
		</fieldset>
		</>
	);
}
