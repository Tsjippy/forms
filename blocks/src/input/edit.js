import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import {
    SelectControl,
    PanelBody,
    TextControl,
    ToggleControl,
    TextareaControl,
} from '@wordpress/components';
import {
    useState,
    useEffect,
    useMemo,
} from '@wordpress/element';
import { useSelect } from '@wordpress/data';

import './editor.scss';

import * as elementAttributes from './components/element_attributes.js';
import { dynamicInputs } from './components/dynamic_inputs.js';
import { InputHtml } from './components/InputHtml.js';
import {
    PrefillOptionsSelector,
    PrefillValueSelector,
} from '../../shared/usePrefill.js';
import AddOptions from '../../shared/AddOptions';

export default function Edit({
    attributes,
    setAttributes,
    isSelected,
    clientId,
}) {
    const blockProps = useBlockProps();

    const typeOptions = useMemo(
        () => [
            {
                label: __('Select an input type', 'tsjippy'),
                value: '',
            },
            ...elementAttributes.inputTypes.map((type) => ({
                label: type,
                value: type,
            })),
        ],
        []
    );

    const storeAttributeAttributes = (value, name) => {
        let newAttributes   = { ...(attributes.inputAttributes || {})};

        newAttributes[name] = value;

        setAttributes({
            inputAttributes:  newAttributes
        });
    };

    const [inputName, setInputName] = useState(
        attributes.name || ''
    );

    useEffect(() => {
        setInputName(attributes.name || '');
    }, [attributes.name]);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            if (inputName !== attributes.name) {
                setAttributes({ name: inputName });
            }
        }, 800);

        return () => clearTimeout(timeoutId);
    }, [inputName, attributes.name, setAttributes]);

    const labelChild = useSelect(
        (select) =>
            select('core/block-editor')
                .getBlockParentsByBlockName(
                    clientId,
                    'tsjippy-forms/label'
                ).length > 0,
        [clientId]
    );

    useEffect(() => {
        if (attributes.labelChild !== labelChild) {
            setAttributes({ labelChild });
        }
    }, [
        labelChild,
        attributes.labelChild,
        setAttributes,
    ]);

    const inputNameComponent = (
        <TextControl
            label="Input Name"
            value={inputName}
            onChange={setInputName}
        />
    );

    const inputTypeSelector = (
        <SelectControl
            label="Input Type"
            value={attributes.type}
            options={typeOptions}
            onChange={(type) => setAttributes({ type })}
        />
    );

    const inputValue =
        attributes.type === 'submit' ? (
            <TextControl
                label="Input Content"
                value={attributes.value}
                onChange={(value) =>
                    setAttributes({ value })
                }
            />
        ) : null;

    const selectableOptions =
        ['radio', 'checkbox', 'select'].includes(
            attributes.type
        ) ? (
            <>
                <h4>Static Options</h4>
				<AddOptions
					attributes={attributes}
					setAttributes={setAttributes}
					/>

                <h4>Dynamic Options (prefill)</h4>

                <PrefillOptionsSelector
                    value={
                        attributes.options_dynamic
                    }
                    onChange={(value) =>
                        setAttributes({
                            options_dynamic: value,
                        })
                    }
                />
            </>
        ) : null;

    const renderPropertiesForm = () => {
        if (!isSelected) {
            return (
                <InputHtml
                    attributes={attributes}
                    blockProps={blockProps}
                    labelChild={labelChild}
                />
            );
        }

        if (attributes.type === '') {
            return inputTypeSelector;
        }

        if (!attributes.name) {
            return (
                <>
                    {inputTypeSelector}
                    {inputNameComponent}
                </>
            );
        }

        const attributeControls = dynamicInputs(
            attributes,
            'default',
            storeAttributeAttributes
        );

        const ariaControls = attributes.ariaAttributes
            ? dynamicInputs(
                  attributes,
                  'aria',
                  storeAttributeAttributes
              )
            : [];

        return (
            <>
                <InputHtml
                    attributes={attributes}
                    blockProps={blockProps}
                    labelChild={labelChild}
                />

                {inputTypeSelector}
                {inputNameComponent}
                {selectableOptions}

				<h4>Dynamic Value (prefill)</h4>
                <PrefillValueSelector
                    value={attributes.dynamic_value}
                    onChange={(value) =>
                        setAttributes({
                            dynamic_value: value,
                        })
                    }
                />

                <div className="attributes-form">
                    <h3>Input properties</h3>

                    {attributeControls}

                    <ToggleControl
                        label={__(
                            'Add aria attributes',
                            'tsjippy'
                        )}
                        checked={
                            !!attributes.ariaAttributes
                        }
                        onChange={(ariaAttributes) =>
                            setAttributes({
                                ariaAttributes,
                            })
                        }
                    />

                    {ariaControls}
                </div>
            </>
        );
    };

    const legend = attributes.type.charAt(0).toUpperCase() + attributes.type.slice(1);

    return (
        <>
            <InspectorControls>
                <PanelBody
                    title={__(
                        'Input Settings',
                        'tsjippy'
                    )}
                >
                    <SelectControl
                        label="Input Type"
                        value={attributes.type}
                        options={typeOptions}
                        onChange={(type) =>
                            setAttributes({ type })
                        }
                    />

                    {inputNameComponent}
                    {inputValue}

                    <ToggleControl
                        label={__(
                            'Allow multiple answers',
                            'tsjippy'
                        )}
                        checked={!!attributes.multiple}
                        onChange={(multiple) =>
                            setAttributes({ multiple })
                        }
                    />

                    <ToggleControl
                        label={__(
                            'This is a required input',
                            'tsjippy'
                        )}
                        checked={!!attributes.required}
                        onChange={(required) =>
                            setAttributes({ required })
                        }
                    />

                    {attributes.multiple && (
                        <>
                            <TextControl
                                label="Add Button Text"
                                value={
                                    attributes.add_button_content
                                }
                                onChange={(
                                    add_button_content
                                ) =>
                                    setAttributes({
                                        add_button_content,
                                    })
                                }
                            />

                            <TextControl
                                label="Remove Button Text"
                                value={
                                    attributes.remove_button_content
                                }
                                onChange={(
                                    remove_button_content
                                ) =>
                                    setAttributes({
                                        remove_button_content,
                                    })
                                }
                            />
                        </>
                    )}
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                { legend } input
                <br></br>
                {renderPropertiesForm()}
            </div>
        </>
    );
}