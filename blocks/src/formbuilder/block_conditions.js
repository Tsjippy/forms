import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls, BlockControls  } from '@wordpress/block-editor';
import { RadioControl, PanelBody, Button, Popover, TextControl, ToggleControl, CheckboxControl, SelectControl, Spinner, Flex, FlexItem, ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { useState, useEffect } from 'react';
import apiFetch from "@wordpress/api-fetch";
import { RawHTML, Fragment } from '@wordpress/block';
import { useSelect, useDispatch } from "@wordpress/data";
import { store as blockEditorStore } from '@wordpress/block-editor';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter, currentFilter } from '@wordpress/hooks';
import { html, seen } from '@wordpress/icons';
import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/block';

export const conditionsFormParser = () => {
    const [ isConditionsFormVisible, setConditionsFormVisibility ]  = useState( false );
    const [ conditionsForm, setConditionsForm ]                     = useState( '' );

    const blockConditions = () => {
        const [ conditionalBlock, setConditionalBlock ] = useState( '50%' );
        return (
            <>
            <div class='modal-content'>
                <span className="close mobile-sticky">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </span>

                <div className="condition-row" data-condition-index="0">
                    <span class='condition-if'>If</span>
                    <br></br>
                    <div className="rule-row" data-rule-index="0">
                        <input type="hidden" className="combinator" name="block-conditions[0][rules][0][combinator]" value="" />

                        <SelectControl
                            label   = "Block"
                            name    = "block-conditions[0][rules][0][conditional-field]"
                            value   = { conditionalBlock }
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
     * @returns Shows the conditions form for an block if needed
     */
    const showConditionsForm    = () => {
        if( document.querySelector(`#block-conditions-modal`) == null ){
            /**
             * Create the modal div to render the react inside
             */
            let div = document.createElement('div');
            div.id ='block-conditions-modal';
            div.classList.add("modal");
            document.body.append(div);
        }

        /**
         * Register the react component
         */
        const domNode = document.getElementById('block-conditions-modal');
        const root = createRoot(domNode);
        root.render(blockConditions());

        // Show the form
        if(isConditionsFormVisible){
        }

        return;
    }

    /**
     * Get the conditions form for this block
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