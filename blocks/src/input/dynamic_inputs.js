import { Button, Dropdown, SelectControl, PanelBody, TextControl, Disabled, ToggleControl, __experimentalNumberControl as NumberControl, CheckboxControl, RadioControl  } from '@wordpress/components';

/**
 * Stores data- attributes
 * @param {*} type 
 * @param {*} newValue 
 * @param {*} name 
 * @param {*} saveFunction 
 * @param {*} all 
 */
const storeDataAtributes   = (type, newValue, name, saveFunction, all) => {

    console.log(type);

    // Remove old entry if it is a name update
    if(type == 'name'){
        all[newValue]   = all[name] ?? '';

        delete all[name];
    }else{
        all[name] = newValue;
    }

    saveFunction({...all}, 'data-*');
}

/**
 * Creates inputs based on an array
 */
export const dynamicInputs = (inputData, values, saveFunction) => {
    let controls	= [];
    
    inputData.forEach( data => {
        let attributeName	= data.attribute
        let attributeValue	= values[data.attribute] ?? '';

        /**
         * Multiple entries possible
         */
        if(attributeName == 'data-*'){
            // The name
            controls.push(<h4 style={{marginTop: '20px'}}>Data- Attributes</h4>);

            /**
             * attributeValue should be an array
             * of name values
             */
            if(attributeValue == ''){
                attributeValue  = {};
            }

            // Add an empty one to allow new data- attributes
            attributeValue[''] = '';

            // Loop over all existing data- attributes
            for (const [key, value] of Object.entries(attributeValue)) {
                controls.push(
                    <TextControl
                        label    = { `data-name` }
                        value    = { key }
                        onChange = { ( name ) => storeDataAtributes('name', name, key, saveFunction, attributeValue) }
                    />
                );

                controls.push(
                    <TextControl
                        label    = { `data-${key} value` }
                        value    = { value }
                        onChange = { ( value ) => storeDataAtributes('value', value, key, saveFunction, attributeValue) }
                    />
                );
            }
        }

        else if(data.expectedType == 'string'){
            controls.push(
                <TextControl
                    label    = { attributeName }
                    value    = { attributeValue }
                    onChange = { ( value ) => saveFunction(value, attributeName) }
                />
            )
        } else if(data.expectedType == 'boolean'){
            controls.push(
                <ToggleControl
                    label    = { attributeName }
                    checked  = {!!attributeValue}
                    onChange = { ( checked ) => saveFunction(checked, attributeName) }
                />
            )
        } else if(data.expectedType == 'number'){
            controls.push(
                <NumberControl
                    label    		   = { attributeName }
                    isShiftStepEnabled = { true }
                    onChange           = { ( value ) => saveFunction(value, attributeName) }
                    shiftStep          = { 1 }
                    value              = { attributeValue }
                />
            )
        } else if(data.expectedType.includes('|')){
            let options = [];
            data.expectedType.split('|').forEach(value => {
                options.push({ label: value, value: value });
            });

            controls.push(
                <RadioControl
                    label    = { attributeName }
                    selected = { attributeValue }
                    options  = { options }
                    onChange = { ( checked ) => saveFunction(checked, attributeName) }
                />
            )
        }else{
            controls.push(
                <div>Not sure how to render this {data.expectedType}</div>
            )
        }
    });

    return controls;
}