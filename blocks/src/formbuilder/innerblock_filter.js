import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls, BlockControls  } from '@wordpress/block-editor';
import { RadioControl, PanelBody, Button, Popover, TextControl, ToggleControl, CheckboxControl, SelectControl, Spinner, Flex, FlexItem, ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { useState, useEffect } from 'react';
import apiFetch from "@wordpress/api-fetch";
import { RawHTML, Fragment } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { store as blockEditorStore } from '@wordpress/block-editor';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter, currentFilter } from '@wordpress/hooks';
import { seen } from '@wordpress/icons';

/**
 * Add a button behind each child block
 */
const addButtonToInnerBlocks = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props) => {

        if(!props.isSelected){
            return (
                <BlockEdit {...props} />
            );
        }

        const [ isConditionsFormVisible, setConditionsFormVisibility ]  = useState( false );
        const [ conditionsForm, setConditionsForm ]                     = useState( '' );

        const parentIds = wp.data.select( 'core/block-editor' ).getBlockParents(props.clientId); 
        const parents 	= wp.data.select('core/block-editor').getBlocksByClientId(parentIds);
        let isChild		= false;

        let parentId	= -1;

        /* parents.forEach(e => {
            if(e.name == "tsjippy-forms/formbuilder"){ 
                isChild 	= true;
                parentId	= e.attributes.id;
            }
        }); */

        if(parents.length > 0 && parents[0].name == "tsjippy-forms/formbuilder"){ 
            isChild 	= true;
            parentId	= parents[0].attributes.id;
        }

        // Not a child, do not do anything
        if(!isChild){
            return (
                <BlockEdit {...props} />
            );
        }

        let elementName	= props.attributes.name;

        /**
         * Load the conditions form on first render to prevent waiting
         */
        useEffect( () => {
            if(elementName != undefined){

                apiFetch({
                    path: tsjippy.restApiPrefix + `/forms/request_form_conditions_html`,
                    method: "POST",
                    data: {
                        formid: parentId,
                        elementid: elementName
                    },
                }).then((res) => {
                    setConditionsForm(res);
                });
            }
        },
        []
    )

        /**
         * Get the conditions form for this element
         * 
         * @param {boolean} toggled 
         */
        const getConditionsForm = (toggled) => {
            setConditionsFormVisibility(toggled);
        }

        const toggleConditionsForm = () => {
            setConditionsFormVisibility(!isConditionsFormVisible);
        }

        /**
         * 
         * @returns Shows the conditions form for an element if needed
         */
        const showConditionsForm    = () => {
            
            if(isConditionsFormVisible){
                if( conditionsForm == ''){
                    return (
                        [
                            <br></br>,
                            <b>Loading Conditins Form...</b>,
                            <Spinner />,
                        ]
                    )
                }
                return (<RawHTML>{ conditionsForm } </RawHTML>);
            }

            return;
        }

        const blockControls  = () => {
            let buttonText  = "Set Input Conditions";

            if(isConditionsFormVisible){
                buttonText  = "Close Conditions Form";
            }
            return (
                <>
                <BlockControls>
                    <ToolbarGroup>
                        <ToolbarButton
                            icon    = { seen }
                            label   = { __( buttonText, 'tsjippy' ) }
                            onClick = { toggleConditionsForm }
                        />
                    </ToolbarGroup>
                </BlockControls>
                </>
            )
        }

        /**
         * Actual Rendering
         */
        return (
            <Fragment>
                { blockControls() }
                { showConditionsForm() }
                <BlockEdit {...props} />

                <InspectorControls>
                    <PanelBody  title = {__("Block Conditions", "tsjippy")} initialOpen = {false} onToggle={(value) => getConditionsForm(value)}>
                        <p>Close this to hide the conditions form again</p>
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'addButtonToInnerBlocks' );

// Registreer het filter in de Gutenberg editor
addFilter(
    'editor.BlockEdit',
    'tsjippy-forms/add-conditions-button',
    addButtonToInnerBlocks
);