/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks  } from '@wordpress/block-editor';
import { Multiple } from './../input/components/Multiple.js';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	const blockProps 		= useBlockProps.save();

	return (
		<div class="clone-divs-wrapper" data-blockid={attributes.blockId }>
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
	);
}
