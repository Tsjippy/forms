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
        const staticOptions = (attributes.selectable_options || '')
            .split('\n')
            .filter(Boolean)
            .map((option) => {
                const [key, value] = option.split('|');

                const trimmedKey = String(key || '').trim();

                return {
                    value: trimmedKey,
                    label: String(value || trimmedKey).trim(),
                };
            });

        let options;
        let selectedValue = '';

        if(isSaving){
            options = staticOptions;
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
                ...staticOptions,
                ...dynamicOptions,
            ];

            selectedValue = prefill?.single?.[attributes.dynamic_value ?? ''];
            
            console.log(prefill?.single?.[attributes.dynamic_value ?? '']);
        }

        html = (
            <div
                className="checkbox-wrapper"
                data-blockid={attributes.blockId}
            >
                {options.map((option, index) => (
                    <label
                        {...blockProps}
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