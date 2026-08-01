import { __ } from '@wordpress/i18n';
import { Multiple } from './Multiple.js';

import { usePrefill } from '../../../shared/usePrefill.js';

const prefillData = usePrefill();

export function InputHtml({
    attributes,
    blockProps,
    hasLabelParent,
}) {
    let html;

    if (['radio', 'checkbox'].includes(attributes.type)) {
        const staticOptions = (attributes.selectable_options || '')
            .split('\n')
            .filter(Boolean)
            .map((option) => {
                const [key, value] = option.split('|');
                return [{
                    value: key.trim(),
                    label: (value || key).trim(),
                }];
            });

        const options = [
            ...staticOptions,
            ...Object.entries(prefill.multi[attributes.selectable_options_dynamic ?? '']  || {})?.map(
                ([key, value]) => ({
                    value: String(key).trim(),
                    label: String(value || key).trim(),
                }))
        ];

        html = <div className="checkbox-wrapper" data-blockid={attributes.blockId}>
            {options.map((option, index) => {
                return (
                    <label {...blockProps} key={index}>
                        <input
                            type={attributes.type}
                            name={attributes.name}
                            value={option.value}
                            className="formbuilder"
                            autocomplete='on'
                            checked={attributes.inputAttributes?.checked === option.value}
                            data-blockid={attributes.blockId}
                            {...attributes.inputAttributes}
                        />
                        {__(option.label, 'tsjippy')}
                    </label>
                );
            })}
        </div>
    }else {
        html = (
            <input
                {...blockProps}
                type={attributes.type}
                name={attributes.name}
                className="formbuilder"
                data-blockid={attributes.blockId}
                autocomplete='on'
                {...attributes.inputAttributes}
            />
        );
    }

    return (
        attributes.multiple && !hasLabelParent ? 
            <Multiple
                inner      = { html }
                attributes = { attributes }
            />
        :
            html
    );
}