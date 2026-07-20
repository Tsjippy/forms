import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps, useInnerBlocksProps, InspectorControls, BlockControls  } from '@wordpress/block-editor';
import { RadioControl, PanelBody, Button, Popover, TextControl, ToggleControl, CheckboxControl, SelectControl, Spinner, Flex, FlexItem, ToolbarGroup, ToolbarButton, Icon } from '@wordpress/components';
import { useState, useEffect } from 'react';
import apiFetch from "@wordpress/api-fetch";
import { RawHTML, Fragment } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { store as blockEditorStore } from '@wordpress/block-editor';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter, currentFilter } from '@wordpress/hooks';
import { html, seen, plus } from '@wordpress/icons';
import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
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

        // Recursive function to get all descendants
        const getAllInnerBlocks = ( blocks ) => {
            let allBlocks = [];
            
            blocks.forEach( ( block ) => {
                allBlocks.push( block );
                if ( block.innerBlocks && block.innerBlocks.length > 0 ) {
                    allBlocks = allBlocks.concat( getAllInnerBlocks( block.innerBlocks ) );
                }
            } );
            
            return allBlocks;
        };

        const parentIds = wp.data.select( 'core/block-editor' ).getBlockParents(props.clientId); 
        const parents 	= wp.data.select('core/block-editor').getBlocksByClientId(parentIds);
        let isChild		= false;

        let parentId	= -1;
        let parentForm  = -1;

        parents.forEach(parent => {
            if(parent.name == "tsjippy-forms/formbuilder"){ 
                isChild 	= true;
                parentId	= parent.attributes.id;
                parentForm  = parent;
            }
        });

        // Not a child, do not do anything
        if(!isChild){
            return (
                <BlockEdit {...props} />
            );
        }

        const allNestedBlocks = getAllInnerBlocks( parentForm.innerBlocks );

        const [ isConditionsFormVisible, setConditionsFormVisibility ]  = useState( false );
        const [ conditionsForm, setConditionsForm ]                     = useState( '' );

        let elementName	= props.attributes.name;

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

        const updateElementConditions = (ruleIndex, subRuleIndex, key, value) => {
            let newConditions   = [...conditions];

            // Create a new rule
            if(newConditions[ruleIndex]  == undefined ){
                ruleIndex = rulenewConditions.push([]) - 1;
            }

            // Create a new rule
            if(newConditions[ruleIndex][subRuleIndex]  == undefined ){
                subRuleIndex    = rulenewConditions.push({}) - 1;
            }

            newConditions[ruleIndex][subRuleIndex][key] = value;
        }

        const formElementOptions = () => {
            return allNestedBlocks.map(block => {
                let name    = block.attributes.name ?? block.attributes.text ?? '';
                let label   = block.name;
                if(name != ''){
                    label += `: ${name}`;
                }
                return { label: label, value: block.clientId }
            });
        }

        const extraOptions  = () => {
            if(elementConditions[0][0]['equation'] == '+' || elementConditions[0][0]['equation'] == '-'){
                return (
                    <>
                    <SelectControl
                        label   = "Element"
                        name    = "element-conditions[0][rules][0][conditional-field-2]"
                        value   = { conditionalElement2 }
                        options = { formElementOptions() }
                        onChange={ ( element ) => updateElementConditions( 0, 0, "conditional-field-2", element ) }
                    />

                    <SelectControl
                        label   = "equation 2"
                        name    = "element-conditions[0][rules][0][equation-2]"
                        value   = { equation2 }
                        options = { [
                            { label: '---', value: '' },
                            { label: 'equals', value: '==' },
                            { label: 'is not', value: '!=' },
                            { label: 'greather than', value: '>' },
                            { label: 'smaller than', value: '<' }
                        ] }
                        onChange={ ( equation ) => updateElementConditions( 0, 0, "equation-2", element ) }
                    />
                    </>
                )
            }
        }

        const conditions = useSelect(
            (select) => select('tsjippy-forms/conditions-store').getConditions(props.clientId),
            [props.clientId]
        );

        const isLoading = useSelect(
            (select) => select('tsjippy-forms/conditions-store').isLoading(),
            []
        );

        const error = useSelect(
            (select) => select('tsjippy-forms/conditions-store').getError(),
            []
        );        

        /**
         * Renders all the rule inputs
         */
        const conditionInputs   = () => {

            if (isLoading) {
                return <p>Loading...</p>;
            }

            if (error) {
                return <p>Error: {error}</p>;
            }

            for (const [index, condition] of Object.entries(conditions)){
                <div class="condition-row" data-condition-index={ index }>
                    <span class='condition-if'>If</span>
                    <br></br>
                    <div class="rule-row" data-rule-index={ index }>

                        <SelectControl
                            label   = "Element"
                            value   = { condition["conditional-field"] }
                            options = { formElementOptions() }
                            onChange={ ( element ) => updateElementConditions( index, 0, "conditional-field", element ) }
                        />

                        <SelectControl
                            label   = "equation"
                            value   = { equation }
                            options = { [
                                { label: '---', value: '' },
                                { label: 'has changed', value: 'changed' },
                                { label: 'is clicked', value: 'clicked' },
                                { label: 'equals', value: '==' },
                                { label: 'is not', value: '!=' },
                                { label: 'greather than', value: '>' },
                                { label: 'smaller than', value: '<' },
                                { label: 'is checked', value: 'checked' },
                                { label: 'is not checked', value: '!checked' },
                                { label: 'equals the value of', value: '== value' },
                                { label: 'does not equal the value of', value: '!= value' },
                                { label: 'greather than the value of', value: '> value' },
                                { label: 'smaller than the value of', value: '< value' },
                                { label: 'minus the value of', value: '-' },
                                { label: 'plus the value of', value: '+' },
                                { label: 'is visible', value: 'visible' },
                                { label: 'is not visible', value: 'invisible' }
                            ] }
                            onChange={ ( equation ) => updateElementConditions( index, 0, "equation", element ) }
                        />

                        { extraOptions() }

                        <TextControl
                            label    = "value"
                            value    = { value }
                            onChange = { ( value ) => updateElementConditions( index, 0, "conditional-value", element ) }
                        />

                        <Button
                            onClick={  () => updateElementConditions( index, 0, "combinator", "and" ) }
                            variant="primary"
                        >
                            AND
                        </Button>
                        <Button
                            onClick={  () => updateElementConditions( index, 0, "combinator", "or" ) }
                            variant="secundary"
                        >
                            OR
                        </Button>
                        <Button
                            onClick={  () => updateElementConditions( index, 0, "add", "or" ) }
                            variant="tertiary"
                        >
                            <Icon icon={ plus } />
                            Add another rule
                        </Button>

                        
                    </div>
                </div>
            }
        }

        /**
         * 
         * @returns The form modal
         */
        const elementConditions = () => {

            return (
                <>
                <div class='modal-content'>
                    <span class="close mobile-sticky">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </span>

                    { conditionInputs() }
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

                /* if(
                    conditionsForm != '' && 
                    document.querySelector(`#element-conditions-modal .loader`) != null
                ){
                    document.querySelector(`#element-conditions-modal .modal-content`).innerHTML = conditionsForm;
                }
                else if( document.querySelector(`#element-conditions-modal`) == null ){

                    div = document.createElement('div');
                    div.id ='element-conditions-modal-' + props.clientId;
                    div.classList.add("modal");
                    div.style.display = "unset"; 
                    div.style.zIndex  = "999999999 !important;"

                    let content = document.createElement('div');
                    content.classList.add("modal-content");

                    div.append(content);

                    

                    if( conditionsForm == ''){
                        content.innerHTML = Main.showLoader( null, true, 50, 'Loading Form', true);
                    }else{
                        content.innerHTML = conditionsForm;
                    }

                    return document.body.append(div);
                } */
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