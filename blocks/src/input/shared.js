import { __ } from '@wordpress/i18n';

export const getInputHtml = (attributes, blockProps) => {
    if(['radio', 'checkbox'].includes(attributes.type)){
        return (
            attributes.selectable_options.split("\n").map(option =>{
                const [value, label] = option.split("|");
                return(
                    <label {...blockProps}>
                        <input type={ attributes.type } name={ attributes.name } value={ value } class='formbuilder' data-blockid={ attributes.blockId }/>
                        { __(label, 'tsjippy') }
                    </label>
                )

            })
        );
    }else if(attributes.type == 'select'){
        return (
            <select
                name={attributes.name}
                className="formbuilder"
                multiple={attributes.multiple}
                data-blockid={ attributes.blockId }
                {...blockProps}
            >
                {
                    attributes.selectable_options.split("\n").map(option =>{
                        const [value, label] = option.split("|");
                        return(
                            <option value={ value }> { __(label, 'tsjippy') } </option>
                        )
                    })
                }
            </select>
        )
    }else if(attributes.type == 'datalist'){
        return (
            <datalist id={ attributes.name } data-blockid={ attributes.blockId } {...blockProps}>
                {
                    attributes.selectable_options.split("\n").map(option =>{
                        const [value, label] = option.split("|");
                        return(
                            <option data-value={ value } value={ __(label, 'tsjippy') }></option>
                        )
                    })
                }
            </datalist>
        )
    }

    return(
        <input {...blockProps} type={ attributes.type } name={ attributes.name } value={ attributes.value } class='formbuilder' data-blockid={ attributes.blockId }/>
    );
}