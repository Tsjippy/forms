import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import {
    ToggleControl,
    TextControl,
    SelectControl,
    Button,
    Spinner,
	CheckboxControl,
} from '@wordpress/components';

export default function UserMetaRequiredControls({
    clientId,
    attributes,
    setAttributes,
}) {
    const [metaKeys, setMetaKeys] = useState([]);
    const [loadingMetaKeys, setLoadingMetaKeys] = useState(true);
    const [availableRoles, setAvailableRoles] = useState([]);
    const [loadingRoles, setLoadingRoles] = useState(true);

    /* Load available roles from the server for the inspector panel. */
	useEffect(() => {
        setLoadingRoles(true);

		apiFetch({
			path: `${tsjippy.restApiPrefix}/forms/get_roles`,
			method: 'POST',
		}).then((res) => {
			setAvailableRoles(Array.isArray(res) ? res : []);
		})
        .catch(() => {
            setAvailableRoles([]);
        })
        .finally(() => {
            setLoadingRoles(false);
        });
	}, []);

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
                    value: ''
                }
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

    /* Add or remove a role from the stored attributes. */
    const onRoleSelected = 
        (checked, roleSlug) => {
            let newRoles = [...attributes.roles];

            if (checked) {
                if (!newRoles.includes(roleSlug)) {
                    newRoles.push(roleSlug);
                }
            } else {
                newRoles = newRoles.filter((role) => role !== roleSlug);
            }

            setAttributes({ roles: newRoles});
        };

    /* Build role checkboxes for the inspector panel. */
    const RoleCheckboxes = () => {
        if(loadingRoles){
            return (<p><Spinner /></p>)
        }

        if (!availableRoles.length) {
            return <p>{__('No roles available.', 'tsjippy')}</p>;
        }

        return (
            <>
            <h4>Only Required When Roles Match</h4>

            <ToggleControl
                label={__("Inverse Roles Logic", "tsjippy")}
                checked={attributes.inverseRoles || false}
                onChange={(inverseRoles) =>
                    setAttributes({ inverseRoles })
                }
            />
            <h4>{attributes.inverseRoles ? "Not" : "Only"} Required For Users With Role</h4>
            {availableRoles.map((role) => {
                const roleSlug = role.slug || role.value || role;
                const roleLabel = role.label || role.name || roleSlug;

                return (
                    <CheckboxControl
                        key={roleSlug}
                        label={roleLabel}
                        checked={attributes.roles.includes(roleSlug)}
                        onChange={(checked) => onRoleSelected(checked, roleSlug)}
                    />
                );
            })}
            </>
        );
    };

    return (
        <>
            <div class="required-options">
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

                <h4>Only Required When Conditions Match</h4>

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

                <br></br>

                <RoleCheckboxes/>
            </div>
        </>
    );
}