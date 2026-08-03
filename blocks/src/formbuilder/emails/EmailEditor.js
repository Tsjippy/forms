import {
    PanelBody,
    TextControl,
    TextareaControl,
} from '@wordpress/components';

import PlaceholderPicker from './PlaceholderPicker';

export default function EmailEditor({
    email,
    onChange,
    formElements = [],
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
                value={email.subject}
                onChange={subject =>
                    onChange({ subject })
                }
            />

            <TextareaControl
                label="Message"
                rows={12}
                value={email.message}
                onChange={message =>
                    onChange({ message })
                }
            />

            <TextareaControl
                label="Headers"
                value={email.headers}
                onChange={headers =>
                    onChange({ headers })
                }
            />

            <TextareaControl
                label="Attachments"
                value={email.attachments}
                onChange={attachments =>
                    onChange({ attachments })
                }
            />
        </PanelBody>
    );
}