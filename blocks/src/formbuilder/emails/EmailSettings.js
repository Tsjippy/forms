import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

import EmailEditor from './EmailEditor';
import EmailTriggerPanel from './EmailTriggerPanel';
import EmailAddressPanel from './EmailAddressPanel';

export default function EmailSettings({
    emails = [],
    formElements = [],
    onChange,
}) {
    const [activeTab, setActiveTab] = useState(0);

    const updateEmail = (index, changes) => {
        const updated = [...emails];

        updated[index] = {
            ...updated[index],
            ...changes,
        };

        onChange(updated);
    };

    const addEmail = () => {
        const updated = [
            ...emails,
            {
                trigger: {
                    type: 'submitted',
                },
                sender: {
                    type: 'fixed',
                    email: '',
                    rules: [],
                },
                recipient: {
                    type: 'fixed',
                    email: '%email%',
                    rules: [],
                },
                subject: '',
                message: '',
                headers: '',
                attachments: '',
            },
        ];

        onChange(updated);
        setActiveTab(updated.length - 1);
    };

    const removeEmail = index => {
        const updated = emails.filter(
            (_, i) => i !== index
        );

        onChange(updated);

        setActiveTab(
            Math.max(0, activeTab - 1)
        );
    };

    const email = emails[activeTab];

    return (
        <div className="tsjippy-emails">
            <div className="tsjippy-email-tabs">
                {emails.map((item, index) => (
                    <Button
                        key={index}
                        variant={
                            activeTab === index
                                ? 'primary'
                                : 'secondary'
                        }
                        onClick={() =>
                            setActiveTab(index)
                        }
                    >
                        Email {index + 1}
                    </Button>
                ))}

                <Button
                    variant="primary"
                    onClick={addEmail}
                >
                    +
                </Button>
            </div>

            {email && (
                <>
                    <EmailTriggerPanel
                        value={email.trigger}
                        formElements={formElements}
                        onChange={trigger =>
                            updateEmail(
                                activeTab,
                                { trigger }
                            )
                        }
                    />

                    <EmailAddressPanel
                        title="Sender"
                        value={email.sender}
                        formElements={formElements}
                        onChange={sender =>
                            updateEmail(
                                activeTab,
                                { sender }
                            )
                        }
                    />

                    <EmailAddressPanel
                        title="Recipient"
                        value={email.recipient}
                        formElements={formElements}
                        onChange={recipient =>
                            updateEmail(
                                activeTab,
                                { recipient }
                            )
                        }
                    />

                    <EmailEditor
                        email={email}
                        onChange={changes =>
                            updateEmail(
                                activeTab,
                                changes
                            )
                        }
                    />

                    <Button
                        isDestructive
                        onClick={() =>
                            removeEmail(activeTab)
                        }
                    >
                        Remove Email
                    </Button>
                </>
            )}
        </div>
    );
}