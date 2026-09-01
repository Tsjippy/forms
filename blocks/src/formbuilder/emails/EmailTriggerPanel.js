import {
    PanelBody,
    RadioControl,
    SelectControl,
    TextControl,
} from '@wordpress/components';

export default function EmailTriggerPanel({
    value,
    formBlocks,
    onChange
}) {
    const update = (changes) => {
        onChange({
            ...value,
            ...changes,
        });
    };

    const fieldOptions = [
		{
			label: 'Select field',
			value: '',
		},
		...formBlocks.map((field) => ({
			label: field.label,
			value: field.id,
		})),
	];

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
                            'Submitted and meets condition',
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
                        label: 'Days before booking starts',
                        value: 'before-stay',
                    },
                    {
                        label: 'Days after booking finished',
                        value: 'after-stay',
                    },
                    {
                        label:
                            'Submission removed',
                        value: 'removed',
                    },
                    {
                        label: 'Disabled',
                        value: 'disabled',
                    },
                ]}
                onChange={(type) =>
                    update({ type })
                }
            />

            {value.type ===
                'submittedcond' && (
                <>
                    <SelectControl
                        label="Field"
                        value={value.block || ''}
                        options={[
                            {
                                label:
                                    'Select Field',
                                value: '',
                            },
                            ...formBlocks,
                        ]}
                        onChange={(block) =>
                            update({ block })
                        }
                    />

                    <SelectControl
                        label="Operator"
                        value={
                            value.operator ||
                            '=='
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
                        onChange={(operator) =>
                            update({ operator })
                        }
                    />

                    <TextControl
                        label="Value"
                        value={
                            value.compare || ''
                        }
                        onChange={(compare) =>
                            update({ compare })
                        }
                    />
                </>
            )}

            {value.type === 'fieldchanged' && (
                <>
                    <SelectControl
                        label="Field"
                        value={
                            value.conditionalField || ''
                        }
                        options={value.fieldOptions}
                        onChange={(conditionalField) =>
                            update({
                                conditionalField,
                            })
                        }
                    />

                    <TextControl
                        label="Value"
                        value={
                            value.conditionalValue || ''
                        }
                        onChange={(conditionalValue) =>
                            update({
                                conditionalValue,
                            })
                        }
                    />
                </>
            )}

            {value.type === 'fieldschanged' && (
                <SelectControl
                    multiple
                    label="Fields"
                    value={value.conditionalFields || []}
                    options={fieldOptions}
                    onChange={(conditionalFields) =>
                        update({
                            conditionalFields,
                        })
                    }
                />
            )}

            {value.type === 'before-stay' && (
                <TextControl
                    type="number"
                    label="Days Before Booking Starts"
                    value={value.daysBefore || ''}
                    onChange={(daysBefore) =>
                        update({
                            daysBefore: parseInt(daysBefore, 10) || 0,
                        })
                    }
                />
            )}

            {value.type === 'after-stay' && (
                <TextControl
                    type="number"
                    label="Days After Booking Finished"
                    help="0 means on the end date."
                    value={value.daysAfter || ''}
                    onChange={(daysAfter) =>
                        update({
                            daysAfter: parseInt(daysAfter, 10) || 0,
                        })
                    }
                />
            )}
        </PanelBody>
    );
}