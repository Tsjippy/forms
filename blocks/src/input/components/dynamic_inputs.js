import {
    Button,
    Dropdown,
    SelectControl,
    PanelBody,
    TextControl,
    Disabled,
    ToggleControl,
    __experimentalNumberControl as NumberControl,
    CheckboxControl,
    RadioControl
} from '@wordpress/components';

import * as elementAttributes from './element_attributes.js';

/**
 * Stores data-* attributes
 */
const storeDataAttributes = (
    type,
    newValue,
    name,
    saveFunction,
    all
) => {
    const updated = { ...all };

    if (type === 'name') {
        if (newValue !== name) {
            updated[newValue] = updated[name] ?? '';
            delete updated[name];
        }
    } else {
        updated[name] = newValue;
    }

    saveFunction(updated, 'data-*');
};

/**
 * Creates inputs based on an array
 */
export const dynamicInputs = (attributes, type, saveFunction) => {
    let inputData;

    if (type === 'area') {
        inputData = elementAttributes.inputSchema.ariaAttributes;
    } else {
        inputData = (
            elementAttributes.inputSchema.types?.[attributes.type] || []
        ).concat(elementAttributes.inputSchema.sharedAttributes);
    }

    const values = attributes.inputAttributes || [];

    const controls = [];

    inputData.forEach((data, index) => {
        const attributeName = data.attribute;
        let attributeValue  = values[data.attribute] ?? '';

        /**
         * Multiple data-* entries possible
         */
        if (attributeName === 'data-*') {
            controls.push(
                <h4
                    key="data-attributes-heading"
                    style={{ marginTop: '20px' }}
                >
                    Data Attributes
                </h4>
            );

            const dataAttributes =
                typeof attributeValue === 'object' && attributeValue !== null
                    ? attributeValue
                    : {};

            const entries =
                dataAttributes[''] === undefined
                    ? { ...dataAttributes, '': '' }
                    : dataAttributes;

            Object.entries(entries).forEach(([key, value], entryIndex) => {
                controls.push(
                    <TextControl
                        key={`data-name-${entryIndex}-${key}`}
                        label="data-name"
                        value={key}
                        onChange={(name) =>
                            storeDataAttributes(
                                'name',
                                name,
                                key,
                                saveFunction,
                                dataAttributes
                            )
                        }
                    />
                );

                controls.push(
                    <TextControl
                        key={`data-value-${entryIndex}-${key}`}
                        label={`data-${key} value`}
                        value={value}
                        onChange={(newValue) =>
                            storeDataAttributes(
                                'value',
                                newValue,
                                key,
                                saveFunction,
                                dataAttributes
                            )
                        }
                    />
                );
            });
        } else if (data.expectedType === 'string') {
            controls.push(
                <TextControl
                    key={`string-${attributeName}-${index}`}
                    label={attributeName}
                    value={attributeValue}
                    onChange={(value) =>
                        saveFunction(value, attributeName)
                    }
                />
            );
        } else if (data.expectedType === 'boolean') {
            controls.push(
                <ToggleControl
                    key={`boolean-${attributeName}-${index}`}
                    label={attributeName}
                    checked={!!attributeValue}
                    onChange={(checked) =>
                        saveFunction(checked, attributeName)
                    }
                />
            );
        } else if (data.expectedType === 'number') {
            controls.push(
                <NumberControl
                    key={`number-${attributeName}-${index}`}
                    label={attributeName}
                    isShiftStepEnabled={true}
                    onChange={(value) =>
                        saveFunction(value, attributeName)
                    }
                    shiftStep={1}
                    value={attributeValue}
                />
            );
        } else if (
            typeof data.expectedType === 'string' &&
            data.expectedType.includes('|')
        ) {
            const options = data.expectedType
                .split('|')
                .map((value) => ({
                    label: value,
                    value
                }));

            controls.push(
                <RadioControl
                    key={`radio-${attributeName}-${index}`}
                    label={attributeName}
                    selected={attributeValue}
                    options={options}
                    onChange={(selected) =>
                        saveFunction(selected, attributeName)
                    }
                />
            );
        } else {
            controls.push(
                <div key={`unknown-${attributeName}-${index}`}>
                    Not sure how to render this {data.expectedType}
                </div>
            );
        }
    });

    return controls;
};