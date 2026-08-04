import {
    PanelBody,
    TextControl,
    TextareaControl,
} from '@wordpress/components';

import { RichText } from '@wordpress/block-editor';

import PlaceholderPicker from './PlaceholderPicker';

export default function EmailEditor({
    email,
    formElements = [],
    onChange,
}) {
    return (
        <PanelBody
            title="Message"
            initialOpen={false}
        >
            <PlaceholderPicker
                formElements={formElements}
            />

            <TextControl
                label="Subject"
                value={email.subject || ''}
                onChange={(subject) =>
                    onChange({ subject })
                }
            />

            <div className="tsjippy-email-message">
                <label
                    style={{
                        display: 'block',
                        marginBottom: '8px',
                        fontWeight: 600,
                    }}
                >
                    Message
                </label>

                <RichText
                    tagName="div"
                    className="tsjippy-email-editor"
                    value={email.message || ''}
                    allowedFormats={[
                        'core/bold',
                        'core/italic',
                        'core/link',
                        'core/strikethrough',
                    ]}
                    placeholder="Write your e-mail template..."
                    onChange={(message) =>
                        onChange({ message })
                    }
                />
            </div>

            <TextareaControl
                label="Additional Headers"
                value={email.headers || ''}
                onChange={(headers) =>
                    onChange({ headers })
                }
            />

            <TextareaControl
                label="Attachments"
                value={email.attachments || ''}
                onChange={(attachments) =>
                    onChange({
                        attachments,
                    })
                }
            />
        </PanelBody>
    );
}