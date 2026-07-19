import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Button, Dropdown, SelectControl, PanelBody, TextControl, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
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

	if ( ! hasInnerBlocks ) {
        return (
            <div { ...blockProps }>
                <Placeholder
                    icon			= "layout"
                    label			= { __("Add an input to this label", 'tsjippy') }
                    instructions	= "Click to add a block"
                >
                    { /* Add the add button */ }
                    <InnerBlocks.ButtonBlockAppender />
                </Placeholder>
                
                { /* Keep context/API active */ }
                <div style={ { display: 'none' } }>
                    <InnerBlocks />
                </div>
            </div>
        );
    }

	return (
		<>
		<InspectorControls>
			<PanelBody title={__('Label Settings', 'tsjippy')}>
				<TextControl
					label    = "Label Text"
					value    = { attributes.text }
					onChange = { ( text ) => setAttributes({ text: text })}
				/>
			</PanelBody>
		</InspectorControls>
    			
		<fieldset { ...innerBlocksProps }>
			<legend>Label Element</legend>
			<label >
				{ attributes.text }
				{ children }
				<InnerBlocks.ButtonBlockAppender />
			</label>
		</fieldset>
		</>
	);
}
