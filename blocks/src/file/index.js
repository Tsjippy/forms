/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType, createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save: () => null,
	transforms: {
        // Define how to turn OTHER blocks into YOUR block
        from: [
            {
                type: 'block',
                blocks: [ "tsjippy-forms/input" ],
                transform: ( attributes ) => {
                    return createBlock( metadata.name, {
                        name: attributes.name,
                        multiple: attributes.multiple,
                        required: attributes.required
                    } );
                },
            },
        ]
    },
} );