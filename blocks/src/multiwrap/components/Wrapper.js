export const Wrapper = ({attributes, children}) => {
    return (
        <div className="clone-divs-wrapper" data-blockid={attributes.blockId }>
            <div className="clone-div" data-div-id="0">
                <div className="button-wrapper">
                    <button
                        type="button"
                        className="remove button hidden"
                    >
                        { attributes.remove_button_content }
                    </button>

                    <button
                        type="button"
                        className="add button"
                    >
                        { attributes.add_button_content }
                    </button>
                </div>

                { children }
            </div>
        </div>
    );
}