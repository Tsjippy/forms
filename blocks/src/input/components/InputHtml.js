import { __ } from '@wordpress/i18n';
import { Multiple } from './Multiple.js';

export function InputHtml({
    attributes,
    blockProps,
    hasLabelParent,
}) {
    let html;

    if (['radio', 'checkbox'].includes(attributes.type)) {
        html = <div className="checkbox-wrapper" data-blockid={attributes.blockId}>
            {attributes.selectable_options.split("\n").concat(prefill.multi[attributes.selectable_options_dynamic] || []).map((option, index) => {
                const [value, label = value] = option.split("|");

                return (
                    <label {...blockProps} key={index}>
                        <input
                            type={attributes.type}
                            name={attributes.name}
                            value={value}
                            className="formbuilder"
                            autocomplete='on'
                            checked={attributes.inputAttributes?.checked === value}
                            data-blockid={attributes.blockId}
                            {...attributes.inputAttributes}
                        />
                        {__(label, 'tsjippy')}
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