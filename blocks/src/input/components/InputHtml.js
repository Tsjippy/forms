import { __ } from '@wordpress/i18n';
import { Multiple } from './Multiple.js';

export function InputHtml({
    attributes,
    blockProps,
    hasLabelParent,
}) {
    let html;

    if (['radio', 'checkbox'].includes(attributes.type)) {
        html = attributes.selectable_options.split("\n").map((option, index) => {
            const [value, label] = option.split("|");

            return (
                <label {...blockProps} key={index}>
                    <input
                        type={attributes.type}
                        name={attributes.name}
                        value={value}
                        className="formbuilder"
                        data-blockid={attributes.blockId}
                    />
                    {__(label, 'tsjippy')}
                </label>
            );
        });
    } else if (attributes.type === 'select') {
        html = (
            <select
                name={attributes.name}
                className="formbuilder"
                multiple={attributes.multiple}
                data-blockid={attributes.blockId}
                {...blockProps}
            >
                {attributes.selectable_options.split("\n").map((option, index) => {
                    const [value, label] = option.split("|");

                    return (
                        <option key={index} value={value}>
                            {__(label, 'tsjippy')}
                        </option>
                    );
                })}
            </select>
        );
    } else if (attributes.type === 'datalist') {
        html = (
            <datalist
                id={attributes.name}
                data-blockid={attributes.blockId}
                {...blockProps}
            >
                {attributes.selectable_options.split("\n").map((option, index) => {
                    const [value, label] = option.split("|");

                    return (
                        <option
                            key={index}
                            data-value={value}
                            value={__(label, 'tsjippy')}
                        />
                    );
                })}
            </datalist>
        );
    } else {
        html = (
            <input
                {...blockProps}
                type={attributes.type}
                name={attributes.name}
                value={attributes.value}
                className="formbuilder"
                data-blockid={attributes.blockId}
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