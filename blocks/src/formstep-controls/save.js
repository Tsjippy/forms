/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, useInnerBlocksProps  } from '@wordpress/block-editor';
import { Flex, FlexItem } from '@wordpress/components';

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
	const blockProps = useBlockProps.save();

	const getStepIndicators	= () => {
		let indicators	= [];
		for (let i = 0; i < attributes.amount; i++) {
			if(i === 0){
				indicators.push(
					<span class="step active"></span>
				);
			}else{
				indicators.push(
					<span class="step"></span>
				);
			}
		}

		return indicators;
	}

	return (
		<div  {...blockProps} class="multi-step-controls">
			<div class="multi-step-controls-wrapper">
				<div class="multi-step-controls-wrapper">
					<div style="flex:1;">
						<button type="button" class="button hidden" name="previous-button">
							Previous
						</button>
					</div>
					
					<div class="step-wrapper" style="flex:1;text-align:center;margin:auto;">
						{ getStepIndicators() }
					</div>
					
					<div style="flex:1;">
						<button type="button" class="button next-button" name="next-button">
							Next
						</button>
					
						<div class="submit-wrapper">
        					<button type="button" class="button form-submit hidden" name="submit-form">
           						Submit travel request
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}
