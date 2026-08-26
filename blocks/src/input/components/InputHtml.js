import { __ } from '@wordpress/i18n';
import { Multiple } from './Multiple.js';

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

        prefillValue = prefill?.single?.[attributes.dynamic_value ?? ''] ?? '';
    }

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
                {...blockProps}
                className={`${blockProps.className} checkbox-wrapper`}
                data-blockid={attributes.blockId}
            >
                {options.map((option, index) => (
                    <label
                        key={`${option.value}-${index}`}
                    >
                        <input
                            type={attributes.type}
                            name={attributes.name}
                            value={option.value}
                            className="formbuilder"
                            autoComplete="on"
                            checked={ prefillValue === option.value }
                            data-blockid={attributes.blockId}
                            {...attributes.inputAttributes}
                            required={attributes.required}
                        />
                        {__(option.label, 'tsjippy')}
                    </label>
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
                {...blockProps}
                type={attributes.type}
                name={attributes.name}
                required={attributes.required}
                data-blockid={attributes.blockId}
                autoComplete="on"
                {...attributes.inputAttributes}
            >
                { isSaving ? "%value-placeholder%" : prefillValue }
            </textarea>
        );
    } 
    
    /**
     * Others
     */
    else {
        html = (
            <input
                {...blockProps}
                type={attributes.type}
                name={attributes.name}
                required={attributes.required}
                data-blockid={attributes.blockId}
                autoComplete="on"
                {...attributes.inputAttributes}
                value = { isSaving ? "%value-placeholder%" :  prefillValue }
            />
        );
    }

    /**
     * Render the the multpiple version if not wrapped in an label and not a text input
     */
    return attributes.multiple && !labelChild && !['text', "email", "tel", "text", "url"].includes(attributes.type) ? (
        <Multiple
            inner={html}
            attributes={attributes}
            isSaving={isSaving}
        />
    ) : (
        html
    );
}