import { cloneElement } from '@wordpress/element';

export const Multiple = ( props ) => {
    const addText     = props?.attributes?.add_button_content ?? '+';
    const removeText  = props?.attributes?.remove_button_content ?? '-';
    const prefill     = props?.prefill ?? {};

    console.log('Multiple prefill', prefill);

    let value =
        prefill?.data?.multi?.[props?.attributes?.dynamic_value || props?.attributes?.name || '']
        ?? prefill?.data?.single?.[props?.attributes?.dynamic_value || props?.attributes?.name || '']
        ?? [];

    if(typeof(value) === 'object' && value !== null){
        value = Object.values(value);
    }

    const values = Array.isArray(value)
        ? value
        : value
            ? [value]
            : [];

    var childEl = props.inner;
    var label = null;
    React.Children.toArray(props.inner.props.children).forEach((child) => {
        if (React.isValidElement(child)) {
            childEl = child;
        }else{
            label = child;
        }
    });

    return (
        ['text', "email", "tel", "url"].includes(props?.attributes?.type) ?
            <div className={`${props?.blockProps?.className ?? ''} option-wrapper`}>
                <ul className="list-selection-list">
                    {(values.length ? values : ['']).map((value, index) => (
                        <li
                            key={index}
                            className="list-selection"
                        >
                            <button
                                type="button"
                                className="small remove-list-selection"
                            >
                                <span className="remove-list-selection">×</span>
                            </button>

                            <input
                                type="hidden"
                                name={props?.attributes?.name}
                                value={value}
                            />

                            <span className="selected-name">
                                {value}
                            </span>
                        </li>
                    ))}
                </ul>
                <div className="multi-text-input-wrapper">
                    {childEl}
                    <button
                        type="button"
                        className="small add-list-selection hidden"
                    >
                        add
                    </button>
                </div>
            </div>
        :
            <div
                className="required flex"
                style={{ width: '85%' }}
            >
                <div className="clone-divs-wrapper">
                    {(values.length ? values : ['']).map((value, index) => (
                        <div key={index} className="clone-div" data-div-id={index}>
                            <div
                                className="button-wrapper"
                                style={{ margin: 'auto', display: 'flex' }}
                            >
                                {
                                    props.inner?.type === 'textarea'
                                        ? cloneElement(props.inner, {
                                            children: value,
                                        })
                                        : cloneElement(props.inner, {
                                            value,
                                        })
                                }

                                <button
                                    type="button"
                                    className="remove button hidden"
                                    style={{ flex: 1, maxWidth: 'max-content' }}
                                >
                                    {removeText}
                                </button>

                                <button
                                    type="button"
                                    className="add button"
                                    style={{ flex: 1, maxWidth: 'max-content' }}
                                >
                                    {addText}
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
    );
};