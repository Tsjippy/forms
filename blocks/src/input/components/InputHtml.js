import { __ } from '@wordpress/i18n';
import { Multiple } from './Multiple.js';

import { usePrefill } from '../../../shared/usePrefill.js';

export function InputHtml({
    attributes,
    blockProps,
    hasLabelParent,
    isSaving = false,
}) {

    let html;

    let prefillValue = '';

    if(!isSaving){
        var prefill = usePrefill();

        prefillValue = prefill?.single?.[attributes.dynamic_value ?? ''] ?? '';
    }

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
                        />
                        {__(option.label, 'tsjippy')}
                    </label>
                ))}
                %options-placeholder%
            </div>
        );
    } else if (attributes.type == 'textarea') {
        html = (
            <textarea
                {...blockProps}
                type={attributes.type}
                name={attributes.name}
                data-blockid={attributes.blockId}
                autoComplete="on"
                {...attributes.inputAttributes}
            >
                { isSaving ? "%value-placeholder%" : prefillValue }
            </textarea>
        );
    } else {
        html = (
            <input
                {...blockProps}
                type={attributes.type}
                name={attributes.name}
                data-blockid={attributes.blockId}
                autoComplete="on"
                {...attributes.inputAttributes}
                value = { isSaving ? "%value-placeholder%" :  prefillValue }
            />
        );
    }

    return attributes.multiple && !hasLabelParent ? (
        <Multiple
            inner={html}
            attributes={attributes}
        />
    ) : (
        html
    );
}