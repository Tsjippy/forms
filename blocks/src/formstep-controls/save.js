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
export default function save() {
	const blockProps = useBlockProps.save();


	return (
		<div class="multi-step-controls">
			<div class="multi-step-controls-wrapper">
				<Flex class="form-element-wrapper">
					<FlexItem>
						<button type="button" class="button hidden" name="previous-button">
							Previous
						</button>
					</FlexItem>
				</Flex>

				<Flex>
					<FlexItem>
						<span class="step active"></span>
						<span class="step"></span>
						<span class="step"></span>
						<span class="step"></span>
					</FlexItem>
				</Flex>
				
				<Flex>
					<FlexItem>
						<button type="button" class="button next-button" name="next-button">
							Next
						</button>
					</FlexItem>

					<FlexItem>
						<div class="submit-wrapper">
							<button type="button" class="button form-submit hidden" name="submit-form">
								Submit travel request
							</button>
						</div>
					</FlexItem>
				</Flex>
			</div>
		</div>
	);
}
