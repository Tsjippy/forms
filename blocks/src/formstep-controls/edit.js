import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { Flex, FlexItem } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

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

	const amount	= useSelect( ( select ) => {
		/**
		 * Find the parent form builder block
		 * And add a formstep control block if needed
		 */
		const { getBlockParentsByBlockName, getBlocksByClientId } = select( 'core/block-editor' );

		// Get the parent form
        const parentIds = getBlockParentsByBlockName(
            clientId, 
            'tsjippy-forms/formbuilder'
        );

		if (!parentIds?.length) {
			return 0;
		}

		const parents	= getBlocksByClientId( parentIds );

		const parent = parents?.[0];
		
		return parent?.innerBlocks?.filter(
			block => block.name === 'tsjippy-forms/formstep'
		).length || 0;
	}, [ clientId ] );

	useEffect(() => {
		if (attributes.amount !== amount) {
			setAttributes({ amount });
		}
	}, [amount, attributes.amount, setAttributes]);

	const indicators = Array.from({ length: amount }, (_, i) => (
		<span
			key={i}
			className={i === 0 ? 'step active' : 'step'}
		/>
	));

	return (
		<fieldset { ...blockProps } className="multi-step-controls">
			<legend> Formstep Controls</legend>
			<div className="multi-step-controls-wrapper">
				<Flex className="form-element-wrapper">
					<FlexItem>
						<button type="button" className="button" name="previous-button">
							Previous
						</button>
					</FlexItem>
				</Flex>

				<Flex>
					<FlexItem>
						{ indicators }
					</FlexItem>
				</Flex>
				
				<Flex>
					<FlexItem>
						<button type="button" className="button next-button" name="next-button">
							Next
						</button>
					</FlexItem>

					<FlexItem>
						<div className="submit-wrapper">
							<button type="button" className="button form-submit hidden" name="submit-form">
								Submit travel request
							</button>
						</div>
					</FlexItem>
				</Flex>
			</div>
		</fieldset>
	);
}