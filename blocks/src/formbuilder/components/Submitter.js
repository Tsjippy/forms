import { __ } from '@wordpress/i18n';

export const FormSubmitter = ({attributes}) => {
    const indicators = Array.from({ length: attributes.step_amount }, (_, i) => (
		<span
			key={i}
			className={i === 0 ? 'step active' : 'step'}
		/>
	));

    return (
        attributes.step_amount === 0 ?
            <div class="submit-wrapper">
                <button type="button" className="button form-submit">
                    { __('Submit', 'tsjippy') + ' ' + attributes.name }
                </button>
            </div>
        : 
            <div className="multi-step-controls">
                <div className="multi-step-controls-wrapper">
                    <div style={{flex:1}}>
                        <button type="button" className="button hidden previous-button">
                            Previous
                        </button>
                    </div>

                    <div className="step-wrapper" style={{flex:1, textAlign: 'center', margin: 'auto' }}>
                        { indicators }
                    </div>
                    
                    <div style={{ flex:1 }}>
                        <button type="button" className="button next-button">
                            Next
                        </button>
                        
                        <div className="submit-wrapper">
                            <button type="button" className="button form-submit hidden">
                                { __('Submit', 'tsjippy') + ' ' + attributes.name }
                            </button>
                            </div>
                        </div>
                </div>
            </div>
    );
}