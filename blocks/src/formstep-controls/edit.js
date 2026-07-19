import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Flex, FlexItem } from '@wordpress/components';
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes, isSelected }) {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps } class="multi-step-controls">
			<div class="multi-step-controls-wrapper">
				<Flex class="form-element-wrapper">
					<FlexItem>
						<button type="button" class="button" name="previous-button">
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