import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { useEffect } from '@wordpress/element';

// 1. Inject the 'blockId' attribute into ALL block configurations
function addIdAttribute( settings, name ) {
    // Optional: Skip specific core blocks if needed
    if ( ! settings.attributes ) {
        settings.attributes = {};
    }
    
    settings.attributes.blockId = {
        type: 'string',
    };

    return settings;
}
addFilter( 'blocks.registerBlockType', 'tsjippy-forms/add-id-attribute', addIdAttribute );

// 2. Intercept the Edit component to generate the ID ONLY for children of your parent
const addBlockId = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        const { clientId, attributes, setAttributes, context } = props;
        const { blockId } = attributes;

        /**
         * Find the parent form builder block
         */

        // Get the parent form
        const parents = wp.data.select('core/block-editor').getBlockParentsByBlockName(
            clientId, 
            'tsjippy-forms/formbuilder'
        );

        useEffect( () => {
            const isChildOfFormBuilder = parents.length > 0;

            if ( isChildOfFormBuilder && blockId !== clientId ) {
                setAttributes( { blockId: clientId } );
            } else if ( ! isChildOfFormBuilder && blockId ) {
                setAttributes( { blockId: undefined } );
            }

        }, [ parents, clientId, blockId, setAttributes ] );

        return <BlockEdit { ...props } />;
    };
}, 'addBlockId' );

addFilter( 'editor.BlockEdit', 'tsjippy-forms/addblock-id', addBlockId );