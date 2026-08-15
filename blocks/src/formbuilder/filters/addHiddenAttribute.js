import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { Fragment, useEffect } from '@wordpress/element';
import { useSelect, select } from '@wordpress/data';

/**
 * Add the hidden and form builder attribute.
 */
addFilter(
	'blocks.registerBlockType',
	'tsjippy-forms/add-hidden-attribute',
	( settings ) => {
		settings.attributes = {
			...settings.attributes,
			hidden: {
				type: 'boolean',
				default: false,
			},
			formbuilderChild:{
				type: 'boolean',
				default: false,
			}
		};

		return settings;
	}
);

/**
 * Add the toggle control.
 */
const withHiddenControl = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			const { attributes, setAttributes, clientId } = props;

			const { getBlockParents, getBlockName } =
                select( 'core/block-editor' );

			const blockParents = getBlockParents( clientId );

            const isInsideFormBuilder = blockParents.some(
                ( parentId ) =>
                    getBlockName( parentId ) === 'tsjippy-forms/formbuilder'
            );

			const isInsideLabel = blockParents.some(
                ( parentId ) =>
                    getBlockName( parentId ) === 'tsjippy-forms/label'
            );

			if ( ! isInsideFormBuilder ) {
				return <BlockEdit { ...props } />;
			}

			useEffect( () => {
				setAttributes( { formbuilderChild: true } );
			}, [ isInsideFormBuilder ] );

			if ( isInsideLabel ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<Fragment>
					<BlockEdit { ...props } />

					<InspectorControls>
						<PanelBody title="Visibility">
							<ToggleControl
								label="Hidden"
								checked={ !! attributes.hidden }
								onChange={ ( hidden ) =>
									setAttributes( { hidden } )
								}
							/>
						</PanelBody>
					</InspectorControls>
				</Fragment>
			);
		};
	},
	'withHiddenControl'
);

addFilter(
	'editor.BlockEdit',
	'tsjippy-forms/hidden-control',
	withHiddenControl
);

/**
 * Add the hidden class in the editor.
 */
const withHiddenClass = createHigherOrderComponent(
    ( BlockListBlock ) => {
        return ( props ) => {
            const { clientId } = props;

            const { getBlockParents, getBlockName } =
                select( 'core/block-editor' );

			const blockParents = getBlockParents( clientId );

            const isInsideFormBuilder = blockParents.some(
                ( parentId ) =>
                    getBlockName( parentId ) === 'tsjippy-forms/formbuilder'
            );

			const isInsideLabel = blockParents.some(
                ( parentId ) =>
                    getBlockName( parentId ) === 'tsjippy-forms/label'
            );

            if ( ! isInsideFormBuilder || isInsideLabel ) {
                return <BlockListBlock { ...props } />;
            }

            const className = [
                props.className,
                props.attributes?.hidden ? 'will-be-hidden' : '',
				'formbuilder-child'
            ]
                .filter( Boolean )
                .join( ' ' );

            return (
                <BlockListBlock
                    { ...props }
                    className={ className }
                />
            );
        };
    },
    'withHiddenClass'
);

addFilter(
    'editor.BlockListBlock',
    'tsjippy-forms/hidden-class',
    withHiddenClass
);