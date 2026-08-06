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

    if (['radio', 'checkbox'].includes(attributes.type)) {
        let options;
        let selectedValue = '';

        if(isSaving){
            options = attributes.selectable_options;
        }else{
            const prefill = usePrefill();
            
            const dynamicOptions = Object.entries(
                prefill?.multi?.[
                    attributes.selectable_options_dynamic ?? ''
                ] || {}
            ).map(([key, value]) => ({
                value: String(key).trim(),
                label: String(value || key).trim(),
            }));

            options = [
                ...attributes.selectable_options,
                ...dynamicOptions,
            ];

            selectedValue = prefill?.single?.[attributes.dynamic_value ?? ''] ?? '';
            
            console.log(prefill?.single?.[attributes.dynamic_value ?? ''] ?? '');
        }

        html = (
            <div
                {...blockProps}
                className={`${blockProps.className} checkbox-wrapper`}
                data-blockid={attributes.blockId}
                data-dynamicOptions={attributes.selectable_options_dynamic}
                data-dynamicValue={attributes.dynamic_value}
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
                            checked={ selectedValue === option.value }
                            data-blockid={attributes.blockId}
                            {...attributes.inputAttributes}
                        />
                        {__(option.label, 'tsjippy')}
                    </label>
                ))}
            </div>
        );
    } else {
        html = (
            <input
                {...blockProps}
                type={attributes.type}
                name={attributes.name}
                className="formbuilder"
                data-blockid={attributes.blockId}
                autoComplete="on"
                {...attributes.inputAttributes}
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