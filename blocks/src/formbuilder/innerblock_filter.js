import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { RadioControl, PanelBody, Button, Popover, TextControl, ToggleControl, CheckboxControl, SelectControl, Spinner, Flex, FlexItem } from '@wordpress/components';
import { useState, useEffect } from 'react';
import apiFetch from "@wordpress/api-fetch";
import { RawHTML, Fragment } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { store as blockEditorStore } from '@wordpress/block-editor';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';

/**
 * Add a button behind each child block
 */
const addButtonToInnerBlocks = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {

        const [ isConditionsFormVisible, setConditionsFormVisibility ]  = useState( false );
        const [ conditionsForm, setConditionsForm ]                     = useState( '' );

        const parentIds = wp.data.select( 'core/block-editor' ).getBlockParents(props.clientId); 
        const parents 	= wp.data.select('core/block-editor').getBlocksByClientId(parentIds);
        let isChild		= false;

        let parentId	= -1;

        parents.forEach(e => {
            if(e.name == "tsjippy-forms/formbuilder"){ 
                isChild 	= true;
                parentId	= e.attributes.id;
            }
        });

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
            apiFetch({
                path: tsjippy.restApiPrefix + `/forms/request_form_conditions_html`,
                method: "POST",
                data: {
                    formid: parentId,
                    elementid: elementName
                },
            }).then((res) => {

                console.log(props)
                setConditionsForm(res);
            });
        },
        []
    )

        /**
         * Get the conditions form for this element
         * 
         * @param {boolean} toggled 
         */
        const getConditionsForm = (toggled) => {
            console.log(toggled);
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
                            <Spinner />,
                            <b>Loading Conditins Form...</b>
                        ]
                    )
                }
                return (<RawHTML> { conditionsForm } </RawHTML>);
            }

            return '';
        }

        const showMain  = () => {
            let buttonText  = "Set Input Conditions";

            if(isConditionsFormVisible){
                buttonText  = "Close Conditions Form";
            }
            return (
                <Flex>
                    <FlexItem>
                        <BlockEdit {...props} />
                    </FlexItem>

                    <FlexItem>
                        <Button
                            variant = "secundary"
                            onClick = { toggleConditionsForm }
                        >
                            { buttonText }
                        </Button>
                    </FlexItem>
                </Flex>
            )
        }

        /**
         * Actual Rendering
         */
        return (
            <Fragment>
                { showMain() }
                { showConditionsForm() }

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