import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { SelectControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

let cachedPrefillData = null;
let prefillPromise = null;

export const usePrefill = () => {
    const [prefillData, setPrefillData] = useState(
        cachedPrefillData || {
            single: {},
            multi: {},
        }
    );

    useEffect(() => {
        if (cachedPrefillData) {
            return;
        }

        if (!prefillPromise) {
            prefillPromise = apiFetch({
                path: `${tsjippy.restApiPrefix}/forms/get_prefill`,
                method: 'POST',
            }).then((res) => {
                cachedPrefillData = res;
                return res;
            });
        }

        prefillPromise
            .then((res) => {
                setPrefillData(res);
            })
            .catch((error) => {
                console.error('Failed to load prefill options', error);
            });
    }, []);

    return cachedPrefillData || prefillData;
};

export const PrefillOptionsSelector = ({ value, onChange }) => {
	const prefillData = usePrefill();

	return (
		Object.values(prefillData.single).length === 0 && Object.values(prefillData.multi).length === 0
		? 	<Spinner />
		: 
			<SelectControl
				label={__('Key for dynamically filled options', 'tsjippy')}
				value={value}
				options={[
					{
						label: __('Select an option', 'tsjippy'),
						value: '',
					},
					...Object.keys(prefillData?.multi || {}).map(
						(key) => ({
							label: key,
							value: key,
						})
					),
				]}
				onChange={onChange}
			/>
	);
};

export const PrefillValueSelector = ({ value, onChange }) => {
	const prefillData = usePrefill();

	return (
		Object.values(prefillData.single).length === 0 && Object.values(prefillData.multi).length === 0
			? 	<Spinner />
			: 
				<SelectControl
					label   = { __("Key for dynamically set value", 'tsjippy') }
					value   = {value}
					help    = { __("Select a key for the dynamically set value. This is used to pre-fill the input field based on the current logged-in user.", 'tsjippy') }
					options = {[
						{
							label: __('Select an option', 'tsjippy'),
							value: '',
						},
						...Object.keys(prefillData?.single || {}).map(
							(key) => ({
								label: key,
								value: key,
							})
						),
					]}
					onChange={onChange}
				/>
	);
};