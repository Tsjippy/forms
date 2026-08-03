import {
    PanelBody,
    RadioControl,
    SelectControl,
    TextControl,
} from '@wordpress/components';

export default function EmailTriggerPanel({
    value,
    formElements,
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
            title="Trigger"
            initialOpen={true}
        >
            <RadioControl
                selected={value.type}
                options={[
                    {
                        label:
                            'The form is submitted',
                        value: 'submitted',
                    },
                    {
                        label:
                            'The form is due for submission',
                        value: 'shouldsubmit',
                    },
                    {
                        label:
                            'Submitted with condition',
                        value: 'submittedcond',
                    },
                    {
                        label:
                            'Field changed to value',
                        value: 'fieldchanged',
                    },
                    {
                        label:
                            'One or more fields changed',
                        value: 'fieldschanged',
                    },
                    {
                        label:
                            'Submission deleted',
                        value: 'removed',
                    },
                    {
                        label: 'Disabled',
                        value: 'disabled',
                    },
                ]}
                onChange={type =>
                    update({ type })
                }
            />

            {value.type ===
                'submittedcond' && (
                <>
                    <SelectControl
                        label="Field"
                        value={value.element || ''}
                        options={[
                            {
                                label:
                                    'Select field',
                                value: '',
                            },
                            ...formElements,
                        ]}
                        onChange={element =>
                            update({ element })
                        }
                    />

                    <SelectControl
                        label="Operator"
                        value={
                            value.operator || '=='
                        }
                        options={[
                            {
                                label: 'Equals',
                                value: '==',
                            },
                            {
                                label:
                                    'Not Equals',
                                value: '!=',
                            },
                            {
                                label:
                                    'Greater Than',
                                value: '>',
                            },
                            {
                                label:
                                    'Less Than',
                                value: '<',
                            },
                        ]}
                        onChange={operator =>
                            update({ operator })
                        }
                    />

                    <TextControl
                        label="Value"
                        value={value.compare || ''}
                        onChange={compare =>
                            update({ compare })
                        }
                    />
                </>
            )}
        </PanelBody>
    );
}