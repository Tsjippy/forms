/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks  } from '@wordpress/block-editor';

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

	const labelComponent	= (
		<label { ...blockProps }>
			{attributes.text}
			<br></br>
			<InnerBlocks.Content />
		</label>
	);

	return (
		attributes.isMultiple ?
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
	);
}
