export const Multiple = ( props ) => {
    const addText    = props.attributes.add_button_content ?? '+';
    const removeText = props.attributes.remove_button_content ?? '-';
    const inputType  = props.attributes.type ?? '';

    return inputType === 'text' ? (
        <div className="option-wrapper">
            <ul className="list-selection-list" />
            <div className="multi-text-input-wrapper">
                { props.inner }
                <button
                    type="button"
                    className="small add-list-selection hidden"
                >
                    add
                </button>
            </div>
        </div>
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