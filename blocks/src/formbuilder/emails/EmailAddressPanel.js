import {
    PanelBody,
    RadioControl,
    TextControl,
} from '@wordpress/components';

export default function EmailAddressPanel({
    title,
    value,
    onChange,
}) {
    const update = changes => {
        onChange({
            ...value,
            ...changes,
        });
    };

    return (
        <PanelBody
            title={title}
            initialOpen={false}
        >
            <RadioControl
                selected={value.type}
                options={[
                    {
                        label:
                            'Fixed address',
                        value: 'fixed',
                    },
                    {
                        label:
                            'Conditional address',
                        value: 'conditional',
                    },
                ]}
                onChange={type =>
                    update({ type })
                }
            />

            {value.type === 'fixed' && (
                <TextControl
                    label="Email Address"
                    value={value.email || ''}
                    onChange={email =>
                        update({ email })
                    }
                />
            )}
        </PanelBody>
    );
}