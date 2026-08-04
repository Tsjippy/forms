import { Button, SelectControl, TextControl } from '@wordpress/components';

const emptyCondition = {
    field: '',
    operator: 'equals',
    value: '',
};

export default function WarningConditions({ value = [], onChange }) {
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
                nextValue,
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
                    <TextControl
                        label="Field"
                        value={condition.field || ''}
                        onChange={(nextValue) =>
                            updateCondition(index, 'field', nextValue)
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
                                label: 'Does not equal',
                                value: 'not_equals',
                            },
                            {
                                label: 'Contains',
                                value: 'contains',
                            },
                            {
                                label: 'Is empty',
                                value: 'empty',
                            },
                            {
                                label: 'Is not empty',
                                value: 'not_empty',
                            },
                        ]}
                        onChange={(nextValue) =>
                            updateCondition(index, 'operator', nextValue)
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