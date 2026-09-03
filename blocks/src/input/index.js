/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
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
	save,

	// This updates the List View name dynamically
    __experimentalLabel: (attributes, { context }) => {
        const { type, name } = attributes;
        
        // Return a fallback if the attribute is empty
        return type ? `${type.charAt(0).toUpperCase() + type.slice(1)} input: ${name}` : __('Input', 'tsjippy');
    },
} );
