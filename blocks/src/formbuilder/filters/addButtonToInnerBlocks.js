import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { seen } from '@wordpress/icons';
import ConditionsModal from '../components/ConditionsModal';

function getAllInnerBlocks(blocks) {
	let allBlocks = [];

	(blocks || []).forEach((block) => {
		allBlocks.push(block);

		if (block.innerBlocks && block.innerBlocks.length > 0) {
			allBlocks = allBlocks.concat(getAllInnerBlocks(block.innerBlocks));
		}
	});

	return allBlocks;
}

function isInsideFormBuilder(clientId) {
	const parentIds = wp.data
		.select('core/block-editor')
		.getBlockParents(clientId);

	const parents = wp.data
		.select('core/block-editor')
		.getBlocksByClientId(parentIds);

	let parentForm = null;

	for (const parent of parents) {
		if (parent?.name === 'tsjippy-forms/formbuilder') {
			parentForm = parent;
			break;
		}
	}

	return parentForm;
}

const addConditionsForm = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		if (!props.isSelected) {
			return <BlockEdit {...props} />;
		}

		const parentForm = isInsideFormBuilder(props.clientId);

		if (!parentForm) {
			return <BlockEdit {...props} />;
		}

		const allNestedBlocks = getAllInnerBlocks(parentForm.innerBlocks || []);
		const [isConditionsFormVisible, setConditionsFormVisibility] = useState(false);

		const toggleConditionsForm = () => {
			setConditionsFormVisibility((prev) => !prev);
		};

		const buttonText = isConditionsFormVisible
			? __('Close Conditions Form', 'tsjippy')
			: __('Set Input Conditions', 'tsjippy');

		return (
			<>
				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton
							icon={seen}
							label={buttonText}
							onClick={toggleConditionsForm}
						/>
					</ToolbarGroup>
				</BlockControls>

				<ConditionsModal
					isVisible={isConditionsFormVisible}
					onClose={toggleConditionsForm}
					elementId={props.clientId}
					allNestedBlocks={allNestedBlocks}
					blockProps={props}
				/>

				<BlockEdit {...props} />

				<InspectorControls>
					<PanelBody
						title={__('Block Conditions', 'tsjippy')}
						initialOpen={false}
					>
						<p>
							{__('Use the toolbar button to open or close the conditions editor.', 'tsjippy')}
						</p>
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'addConditionsForm');

addFilter(
	'editor.BlockEdit',
	'tsjippy-forms/add-conditions-button',
	addConditionsForm
);