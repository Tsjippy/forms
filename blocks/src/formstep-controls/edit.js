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
export default function Edit({ attributes, setAttributes, isSelected, clientId }) {
	const blockProps = useBlockProps();

	const getStepIndicators	= () => {
		/**
		 * Find the parent form builder block
		 * And add a formstep control block if needed
		 */

		// Get the parent block ids
		const parentIds = wp.data.select( 'core/block-editor' ).getBlockParents(clientId); 
		// Get the blocks
		const parents 	= wp.data.select('core/block-editor').getBlocksByClientId(parentIds);

		// Loop over all the parents to find the formbuilder block
		parents.forEach(parent => {
			if(parent.name == "tsjippy-forms/formbuilder"){

				let formsteps = parent.innerBlocks.filter(block => block.name == 'tsjippy-forms/formstep');

				setAttributes({ amount: formsteps.length })
			}
		});

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
						{ getStepIndicators() }
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