import { Children, isValidElement } from '@wordpress/element';
import { cloneElement } from '@wordpress/element';

export const Multiple = ( props ) => {
    console.log(props);

    const addText     = props?.attributes?.add_button_content ?? '+';
    const removeText  = props?.attributes?.remove_button_content ?? '-';
    const inputType   = props?.attributes?.type ?? '';
    
    const children    = Children.toArray(props?.inner?.props?.children);

    const labelText = children.find(
        child => typeof child === 'string'
    );

    const labelElement = children.find(
        child =>
            isValidElement(child) &&
            (child.type === 'h4' ||
            child.props?.className === 'label-text')
    );

    const inputElements = children.filter(
        child =>
            isValidElement(child) &&
            child.type !== 'h4' &&
            child.type !== 'br'
    );

    console.log(inputElements);

    return inputType === 'text' ? (
        <>
            {labelText && (
                <h4 className="label-text">{labelText}</h4>
            )}

            {labelElement && (labelElement)}

            { props?.isSaving || false ?
                <div className={`${props.className ?? ''} option-wrapper`}>
                    <ul className="list-selection-list" />
                    <div className="multi-text-input-wrapper">
                        {inputElements}
                        {children.length === 0 && (props.inner)}
                        <button
                            type="button"
                            className="small add-list-selection hidden"
                        >
                            add
                        </button>
                    </div>
                </div>
                :
                    inputElements
            }
        </>
    ) : (
        <div
            className="input-wrapper required flex"
            style={{ width: '85%' }}
        >
            <div className="clone-divs-wrapper">
                <div className="clone-div" data-div-id="0">
                    <div
                        className="button-wrapper"
                        style={{ margin: 'auto', display: 'flex' }}
                    >
                        {props.inner}

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
            </div>
        </div>
    );
};