import { __ } from '@wordpress/i18n';
import { Multiple } from './Multiple.js';
import { Fragment } from '@wordpress/element';

import { usePrefill } from '../../../shared/usePrefill.js';

export function InputHtml({
    attributes,
    blockProps,
    labelChild,
    isSaving = false,
}) {

    let html;

    let prefillValue = '';

    if(!isSaving){
        var prefill = usePrefill();

        prefillValue = prefill?.data?.single?.[attributes.dynamic_value || attributes.name || ''] || '';
    }

    const renderMultiple = !isSaving && attributes.multiple && !labelChild;

    /**
     * Checkboxes
     */
    if (['radio', 'checkbox'].includes(attributes.type)) {
        let options = [];

        if(isSaving){
            options = attributes.options;
        }else{        
            const dynamicOptions = Object.entries(
                prefill?.multi?.[
                    attributes.options_dynamic ?? ''
                ] || {}
            ).map(([key, value]) => ({
                value: String(key).trim(),
                label: String(value || key).trim(),
            }));

            options = [
                ...attributes.options,
                ...dynamicOptions,
            ];
        }

        html = (
            <div
                {...(!renderMultiple ? blockProps : {})}
                className={`${blockProps.className} checkbox-wrapper`}
                data-blockid={attributes.blockId}
            >
                {options.map((option, index) => (
                    <Fragment key={`${option.value}-${index}-wrapper`}>
                        <label
                            className={`checkbox-wrapper-label`}
                            style={{ marginRight: '5px' }}
                        >
                            <input
                                type={attributes.type}
                                name={`${attributes.name}${attributes.type === 'checkbox' ? '[]' : ''}`}
                                value={ option.value }
                                className="formbuilder"
                                autoComplete="on"
                                defaultChecked={ isSaving ? undefined : prefillValue.includes(option.value) }
                                data-blockid={attributes.blockId}
                                {...attributes.inputAttributes}
                                required={attributes.required}
                            />
                            {__(option.label, 'tsjippy')}
                        </label>

                        {attributes.radioNewLine && <br />}
                    </Fragment>
                ))}
                { isSaving && "%options-placeholder%" }
            </div>
        );
    } 
    
    /**
     * Text area
     */
    else if (attributes.type == 'textarea') {
        html = (
            <textarea
                {...(!renderMultiple ? blockProps : {})}
                type={attributes.type}
                name={attributes.name}
                required={attributes.required}
                data-blockid={attributes.blockId}
                autoComplete="on"
                {...attributes.inputAttributes}
                defaultValue={ isSaving || renderMultiple ? undefined : prefillValue }
                value={ isSaving || renderMultiple ? "%value-placeholder%" : undefined }
            />
        );
    } 
    
    /**
     * Others
     */
    else {
        html = (
            <input
                {...(!renderMultiple ? blockProps : {})}
                type={attributes.type}
                name={ attributes.name }
                required={ attributes.required }
                data-blockid={attributes.blockId}
                autoComplete="on"
                multiple={attributes.multiple}
                {...attributes.inputAttributes}
                defaultValue = { isSaving ? undefined :  prefillValue }
                value = { isSaving ? "%value-placeholder%" :  undefined }
                data-uselistvalue = { attributes.multiple ? attributes.datalistvalue : undefined}
            />
        );
    }

    /**
     * Render the the multiple version if not wrapped in an label and not a text input
     */
    return (
        !isSaving && 
        attributes.multiple && 
        (
            !labelChild ||
            ['text', "email", "tel", "url"].includes(attributes.type) 
        )
        ? (
            <Multiple
                inner={html}
                attributes={attributes}
                prefill={prefill}
                blockProps={blockProps}
            />
        ) : (
            html
        )
    );
}