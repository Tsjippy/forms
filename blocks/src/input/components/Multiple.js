export const Multiple = ( props ) => {
    const addText     = props?.attributes?.add_button_content ?? '+';
    const removeText  = props?.attributes?.remove_button_content ?? '-';

    return (
        <div>
            <div
                className="required flex"
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
        </div>
    );
};