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
import { html, seen } from '@wordpress/icons';
import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

export const conditionsFormParser = () => {
    const [ isConditionsFormVisible, setConditionsFormVisibility ]  = useState( false );
    const [ conditionsForm, setConditionsForm ]                     = useState( '' );

    const elementConditions = () => {
        const [ conditionalElement, setConditionalElement ] = useState( '50%' );
        return (
            <>
            <div class='modal-content'>
                <span class="close mobile-sticky">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </span>

                <div class="condition-row" data-condition-index="0">
                    <span class='condition-if'>If</span>
                    <br></br>
                    <div class="rule-row" data-rule-index="0">
                        <input type="hidden" class="combinator" name="element-conditions[0][rules][0][combinator]" value="" />

                        <SelectControl
                            label   = "Element"
                            name    = "element-conditions[0][rules][0][conditional-field]"
                            value   = { conditionalElement }
                            options = { [
                                { label: 'Big', value: '100%' },
                                { label: 'Medium', value: '50%' },
                                { label: 'Small', value: '25%' },
                            ] }
                            onChange={ ( newSize ) => setSize( newSize ) }
                        />
                    </div>
                </div>
            </div>
            </>
        );
    }

    /**
     * 
     * @returns Shows the conditions form for an element if needed
     */
    const showConditionsForm    = () => {
        if( document.querySelector(`#element-conditions-modal`) == null ){
            /**
             * Create the modal div to render the react inside
             */
            let div = document.createElement('div');
            div.id ='element-conditions-modal';
            div.classList.add("modal");
            document.body.append(div);
        }

        /**
         * Register the react component
         */
        const domNode = document.getElementById('element-conditions-modal');
        const root = createRoot(domNode);
        root.render(elementConditions());

        // Show the form
        if(isConditionsFormVisible){
        }

        return;
    }

    /**
     * Load the conditions form on first render to prevent waiting
     */
    /* useEffect( () => {
        if(elementName != undefined){

            apiFetch({
                path: tsjippy.restApiPrefix + `/forms/request_form_conditions_modal`,
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
    ) */

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
                        onClick = { setConditionsFormVisibility(!isConditionsFormVisible) }
                    />
                </ToolbarGroup>
            </BlockControls>
            </>
        )
    }

    return (
        <>
        { blockControls() }
        </>
    )
}