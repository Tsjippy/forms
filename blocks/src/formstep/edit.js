import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Button, Dropdown, SelectControl, PanelBody, TextControl, Placeholder } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import './editor.scss';
import { store as blockEditorStore } from '@wordpress/block-editor';

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
    const { children, ...innerBlocksProps }  = useInnerBlocksProps( 
        blockProps, { 
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
     * Find the parent form builder block
     * And add a formstep control block if needed
     */

    // Get the parent form
    const parents = wp.data.select('core/block-editor').getBlockParentsByBlockName(
        clientId, 
        'tsjippy-forms/formbuilder'
    );

    // Loop over all the parents to find the formbuilder block
    parents.forEach(parent => {
        // Check if it is not already there
        if(parent.innerBlocks.filter(block => block.name == 'tsjippy-forms/formstep-controls').length > 0){
            return '';
        }

        let formsteps = parent.innerBlocks.filter(block => block.name == 'tsjippy-forms/formstep');

        // Create a formstep controls block
        const newBlock = createBlock( "tsjippy-forms/formstep-controls", { amount: formsteps.length});

        // Insert the new block into the parent's inner blocks
        const { insertBlock } = useDispatch( 'core/block-editor' );
        insertBlock( newBlock, undefined, parent.clientId );
    });

	if ( ! hasInnerBlocks ) {
        return (
            <div { ...blockProps }>
                <Placeholder
                    icon			= "layout"
                    label			= { __("Add an input to this formstep", 'tsjippy') }
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
		<div { ...innerBlocksProps } class="formstep" style = {{padding: '20px'}}>
			<label >
				{ attributes.text }
				{ children }
			</label>
		</div>
		</>
	);
}


