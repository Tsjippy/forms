const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;
const { Fragment } = wp.element;
const { useSelect } = wp.data;

import UserMetaRequiredControls from '../../../shared/AddRequiredOptions';
/**
 * Add extra attributes to all blocks that already have a required attribute.
 */
addFilter(
	'blocks.registerBlockType',
	'tsjippy/forms-user-meta-attributes',
	(settings) => {
		if (
			!settings.attributes ||
			typeof settings.attributes.required === 'undefined'
		) {
			return settings;
		}

		settings.attributes = {
			...settings.attributes,
			notChild: {
				type: 'boolean',
				default: false,
			},
			remindByEmail: {
				type: 'boolean',
				default: false,
			},
			conditionMode: {
				type: "string",
				default: "and"
			},
			conditions: {
				type: "array",
				default: []
			}
		};

		return settings;
	}
);

/**
 * Show extra controls on child blocks of tsjippy-forms/formbuilder
 * when the parent has user_meta enabled.
 */
addFilter(
	'editor.BlockEdit',
	'tsjippy/forms-user-meta-controls',
	createHigherOrderComponent(
		(BlockEdit) =>
			(props) => {
				const {
					clientId,
					attributes,
					setAttributes,
				} = props;


				// Ignore blocks without a required attribute
				if (typeof attributes.required === 'undefined' || props.name.includes('tsjippy')) {
					return <BlockEdit {...props} />;
				}

				const userMetaEnabled = useSelect(
					(select) => {
						const editor = select('core/block-editor');

						const parentId =
							editor.getBlockParentsByBlockName(
								clientId,
								'tsjippy-forms/formbuilder'
							)?.[0];

						if (!parentId) {
							return false;
						}

						const parent = editor.getBlock(parentId);

						return parent?.attributes?.user_meta === true;
					},
					[clientId]
				);

				if (!userMetaEnabled) {
					return <BlockEdit {...props} />;
				}

				return (
					<Fragment>
						<BlockEdit {...props} />

						<InspectorControls>
							<PanelBody
								title="Required Options"
								initialOpen={true}
							>
								<UserMetaRequiredControls
									clientId={clientId}
									attributes={attributes}
									setAttributes={setAttributes}
								/>
							</PanelBody>
						</InspectorControls>
					</Fragment>
				);
			},
		'withUserMetaControls'
	)
);