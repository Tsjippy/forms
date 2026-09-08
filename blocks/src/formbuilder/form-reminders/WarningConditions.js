import { Button, SelectControl, TextControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const emptyCondition = {
    field: '',
    operator: 'equals',
    value: '',
};

export default function WarningConditions({ value = [], onChange }) {
    const [metaKeys, setMetaKeys] = useState([]);
    const [loadingMetaKeys, setLoadingMetaKeys] = useState(true);    

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

    const conditions = Array.isArray(value) ? value : [];

    const addCondition = () => {
        onChange([
            ...conditions,
            {
                ...emptyCondition,
            },
        ]);
    };

    const updateCondition = (index, key, nextValue) => {
        const updatedConditions = conditions.map((condition, conditionIndex) => {
            if (conditionIndex !== index) {
                return condition;
            }

            return {
                ...condition,
                [key]: nextValue,
            };
        });

        onChange(updatedConditions);
    };

    const removeCondition = (index) => {
        onChange(
            conditions.filter((condition, conditionIndex) => {
                return conditionIndex !== index;
            })
        );
    };

    return (
        <div className="tsjippy-warning-conditions">
            {conditions.length === 0 && (
                <p>No warning exclusions configured.</p>
            )}

            {conditions.map((condition, index) => (
                <div
                    className="tsjippy-warning-conditions__condition"
                    key={index}
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
                                    label: 'Submitted',
                                    value: 'submitted',
                                },
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

                    <TextControl
                        label="Value"
                        value={condition.value || ''}
                        onChange={(nextValue) =>
                            updateCondition(index, 'value', nextValue)
                        }
                    />

                    <Button
                        variant="secondary"
                        isDestructive
                        onClick={() => removeCondition(index)}
                    >
                        Remove exclusion
                    </Button>
                </div>
            ))}

            <Button variant="secondary" onClick={addCondition}>
                Add exclusion
            </Button>
        </div>
    );
}