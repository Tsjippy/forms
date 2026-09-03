import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { SelectControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';

export const usePrefill = () => {
    const { data, isLoading } = useSelect(
        (select) => ({
            data: select('tsjippy/prefill').getData(),
            isLoading: select('tsjippy/prefill').isLoading(),
        }),
        []
    );

    const { fetchPrefill } = useDispatch('tsjippy/prefill');

    useEffect(() => {
        if (!data && !isLoading) {
            fetchPrefill();
        }
    }, [data, isLoading]);

    return {
        data,
        isLoading,
    };
};

export const PrefillOptionsSelector = ({ value, onChange }) => {
    const { data: prefillData, isLoading } = usePrefill();

    if (isLoading || !prefillData) {
        return <Spinner />;
    }

    return (
        <SelectControl
            label={__('Key for dynamically filled options', 'tsjippy')}
            value={value}
            options={[
                {
                    label: __('Select an option', 'tsjippy'),
                    value: '',
                },
                ...Object.keys(prefillData.multi || {}).map((key) => ({
                    label: key,
                    value: key,
                })),
            ]}
            onChange={onChange}
        />
    );
};

export const PrefillValueSelector = ({ value, onChange, allowMultiple }) => {
    const { data: prefillData, isLoading } = usePrefill();

    if (isLoading || !prefillData) {
        return <Spinner />;
    }

    const singleOptions = Object.keys(prefillData.single || {}).map((key) => ({
        label: key,
        value: key,
    }));

    const optionsMap = new Map();

    Object.keys(prefillData.single || {}).forEach((key) => {
        optionsMap.set(key, {
            label: key,
            value: key,
        });
    });

    if (allowMultiple) {
        Object.keys(prefillData.multi || {}).forEach((key) => {
            optionsMap.set(key, {
                label: key,
                value: key,
            });
        });
    }

    const options = [
        {
            label: __('Select an option', 'tsjippy'),
            value: '',
        },
        ...Array.from(optionsMap.values()).sort((a, b) =>
            a.label.localeCompare(b.label)
        ),
    ];

    return (
        <SelectControl
            label={__('Key for dynamically set value', 'tsjippy')}
            value={value}
            help={__(
                'Select a key for the dynamically set value. This is used to pre-fill the input field based on the current logged-in user.',
                'tsjippy'
            )}
            options={options}
            onChange={onChange}
        />
    );
};