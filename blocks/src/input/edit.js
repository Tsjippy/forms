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
} from '@wordpress/components';
import {
    useRef,
    useState,
    useEffect,
    useMemo,
} from '@wordpress/element';
import { useDispatch,useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { switchToBlockType, createBlock } from '@wordpress/blocks';

import './editor.scss';

import * as blockAttributes from './components/block_attributes.js';
import { dynamicInputs } from './components/dynamic_inputs.js';
import { InputHtml } from './components/InputHtml.js';
import {
    PrefillOptionsSelector,
    PrefillValueSelector,
} from '../../shared/usePrefill.js';
import AddOptions from '../../shared/AddOptions';
import UserMetaRequiredControls from '../../shared/AddRequiredOptions';

export default function Edit({
    attributes,
    setAttributes,
    isSelected,
    clientId,
}) {
    const blockProps = useBlockProps();

    const { replaceBlock } = useDispatch( blockEditorStore );

    const typeOptions = useMemo(
        () => [
            {
                label: __('Select an input type', 'tsjippy'),
                value: '',
            },
            ...blockAttributes.inputTypes.map((type) => ({
                label: type,
                value: type,
            })),
        ],
        []
    );

    const storeAttributeAttributes = (value, name) => {
        setAttributes({
            inputAttributes: {
                ...(attributes.inputAttributes || {}),
                [name]:value,
            },
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
            setAttributes( {labelChild: labelChild});
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
            onChange={ ( type ) => {
                // If the user selects a file or image input type, replace the block with the file block
                if ( [ 'file', 'image' ].includes( type ) ) {
                    replaceBlock(
                        clientId,
                        createBlock( 'tsjippy-forms/file', {
                            name: attributes.name,
                        } )
                    );

                    return;
                }

                setAttributes( { type } );
            } }
        />
    );

    const selectableOptions =
        ['radio', 'checkbox', 'select'].includes(
            attributes.type
        ) ? (
            <>
                <ToggleControl
                    label={__(
                        'Each option on its own line',
                        'tsjippy'
                    )}
                    checked={!!attributes.radioNewLine}
                    onChange={(radioNewLine) =>
                        setAttributes({ radioNewLine })
                    }
                />
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

    const attributeControls = dynamicInputs(
        attributes,
        'default',
        storeAttributeAttributes
    );

    const legend = attributes.type
        ? attributes.type.charAt(0).toUpperCase() +
        attributes.type.slice(1)
        : '';

    return (
        <>
            <InspectorControls>
                <PanelBody
                    title={__(
                        'Input Settings',
                        'tsjippy'
                    )}

                    initialOpen={attributes.name == '' || attributes.type == ''}
                >
                    {inputTypeSelector}

                    {inputNameComponent}

                    <PrefillValueSelector
                        value={attributes.dynamic_value}
                        onChange={(value) =>
                            setAttributes({
                                dynamic_value: value,
                            })
                        }
                        allowMultiple={attributes.multiple}
                    />

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
                            'Required',
                            'tsjippy'
                        )}
                        checked={!!attributes.required}
                        onChange={(required) =>
                            setAttributes({ required })
                        }
                    />
                    <UserMetaRequiredControls
                        clientId={clientId}
                        attributes={attributes}
                        setAttributes={setAttributes}
                    />
                    
                    {attributeControls}

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

                {['checkbox', 'radio'].includes(attributes.type) && (
                    <PanelBody
                        title={__(
                            'Selectable Options',
                            'tsjippy'
                        )}
                        initialOpen={ true }
                    >
                        {selectableOptions}
                    </PanelBody>
                )}

                <PanelBody
                    title={__(
                        'Input Aria Attributes',
                        'tsjippy'
                    )}
                    initialOpen={ false }
                >
                    { dynamicInputs(
                        attributes,
                        'aria',
                        storeAttributeAttributes
                    ) }
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                { legend } input
                <br></br>
                
                {
                !attributes.type || !attributes.name
                     ?
                        <>
                            {inputTypeSelector}
                            {inputNameComponent}
                        </>
                    :
                        <InputHtml
                            attributes={attributes}
                            blockProps={blockProps}
                            labelChild={labelChild}
                        />
                }
                    
            </div>
        </>
    );
}