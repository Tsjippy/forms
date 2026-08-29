import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import {
    ToggleControl,
    TextControl,
    SelectControl,
    Button,
    Spinner
} from '@wordpress/components';

export default function UserMetaRequiredControls({
    clientId,
    attributes,
    setAttributes,
}) {
    const [metaKeys, setMetaKeys] = useState([]);
    const [loadingMetaKeys, setLoadingMetaKeys] = useState(true);

    const userMetaEnabled = useSelect(
        (select) => {
            if (!attributes.required) {
                return false;
            }

            const editor = select('core/block-editor');

            const parentId = editor.getBlockParentsByBlockName(
                clientId,
                'tsjippy-forms/formbuilder'
            )?.[0];

            return (
                editor.getBlock(parentId)?.attributes?.user_meta === true
            );
        },
        [clientId, attributes.required]
    );

    useEffect(() => {
        setLoadingMetaKeys(true);

        apiFetch({
            path: `${tsjippy.restApiPrefix}/forms/get_user_meta_keys`,
            method: 'POST',
        })
            .then((keys) => {
                setMetaKeys(
                    (keys || []).map((key) => ({
                        label: key,
                        value: key,
                    }))
                );
            })
            .catch(() => {
                setMetaKeys([]);
            })
            .finally(() => {
                setLoadingMetaKeys(false);
            });
    }, []);

    if (!attributes.required || !userMetaEnabled) {
        return null;
    }

    const conditions =
        attributes.conditions?.length
            ? attributes.conditions
            : [
                {
                    key: '',
                    operator: 'equals',
                    value: '',
                },
            ];

    const updateCondition = (index, field, value) => {
        const newConditions = [...conditions];

        newConditions[index] = {
			...newConditions[index],
			[field]:value,
		};

		// Empty value when changing the operator
		if (
			field === 'operator' &&
			['empty', 'not_empty'].includes(value)
		) {
			newConditions[index].value = '';
		}

        setAttributes({
            conditions: newConditions,
        });
    };

    const addCondition = () => {
        setAttributes({
            conditions: [
                ...conditions,
                {
                    key: '',
                    operator: 'equals',
                    value: '',
                },
            ],
        });
    };

    const removeCondition = (index) => {
        const newConditions = conditions.filter(
            (_, i) => i !== index
        );

        setAttributes({
            conditions:
                newConditions.length > 0
                    ? newConditions
                    : [
                        {
                            key: '',
                            operator: 'equals',
                            value: '',
                        },
                    ],
        });
    };

    return (
        <>
            <ToggleControl
                label="Not Required For Children"
                checked={attributes.notChild || false}
                onChange={(notChild) =>
                    setAttributes({ notChild })
                }
            />

            <ToggleControl
                label="Remind By Email"
                checked={attributes.remindByEmail || false}
                onChange={(remindByEmail) =>
                    setAttributes({ remindByEmail })
                }
            />

            <SelectControl
                label="Condition Matching"
                value={attributes.conditionMode || 'and'}
                options={[
                    {
                        label: 'All Conditions (AND)',
                        value: 'and',
                    },
                    {
                        label: 'Any Condition (OR)',
                        value: 'or',
                    },
                ]}
                onChange={(conditionMode) =>
                    setAttributes({ conditionMode })
                }
            />

            {conditions.map((condition, index) => (
                <div
                    key={index}
                    style={{
                        border: '1px solid #ddd',
                        padding: '12px',
                        marginBottom: '12px',
                        borderRadius: '4px',
                    }}
                >
                    <SelectControl
                        label="User Meta Key"
                        value={condition.key || ''}
                        options={[
                            {
                                label: loadingMetaKeys
                                    ? 'Loading user meta keys...'
                                    : 'Select a user meta key',
                                value: '',
                                disabled: true,
                            },
                            ...metaKeys,
                        ]}
                        disabled={loadingMetaKeys}
                        onChange={(value) =>
                            updateCondition(index, 'key', value)
                        }
                    />

                    <SelectControl
                        label="Operator"
                        value={condition.operator || 'equals'}
                        options={[
                            {
                                label: 'Equals',
                                value: 'equals',
                            },
                            {
                                label: 'Not Equals',
                                value: 'not_equals',
                            },
                            {
                                label: 'Contains',
                                value: 'contains',
                            },
                            {
                                label: 'Does Not Contain',
                                value: 'not_contains',
                            },
                            {
                                label: 'Greater Than',
                                value: 'gt',
                            },
                            {
                                label: 'Greater Than Or Equal',
                                value: 'gte',
                            },
                            {
                                label: 'Less Than',
                                value: 'lt',
                            },
                            {
                                label: 'Less Than Or Equal',
                                value: 'lte',
                            },
                            {
                                label: 'Is Empty',
                                value: 'empty',
                            },
                            {
                                label: 'Is Not Empty',
                                value: 'not_empty',
                            },
                        ]}
                        onChange={(value) =>
                            updateCondition(
                                index,
                                'operator',
                                value
                            )
                        }
                    />

                    {!['empty', 'not_empty'].includes(
                        condition.operator
                    ) && (
                        <TextControl
                            label="Value"
                            value={condition.value || ''}
                            onChange={(value) =>
                                updateCondition(
                                    index,
                                    'value',
                                    value
                                )
                            }
                        />
                    )}

                    <Button
                        isDestructive
                        variant="secondary"
                        onClick={() => removeCondition(index)}
                    >
                        Remove Condition
                    </Button>
                </div>
            ))}

            <Button
                variant="primary"
                onClick={addCondition}
            >
                Add Condition
            </Button>
        </>
    );
}