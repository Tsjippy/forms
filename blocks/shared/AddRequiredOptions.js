import { useSelect } from '@wordpress/data';
import { ToggleControl } from '@wordpress/components';

export default function UserMetaRequiredControls({
	clientId,
	attributes,
	setAttributes,
}) {
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

	if (!attributes.required || !userMetaEnabled) {
		return null;
	}

	return (
		<>
			<ToggleControl
				label="Not Required For Children"
				checked={attributes.notChild || false}
				onChange={(notChild) =>
					setAttributes({
						notChild,
					})
				}
			/>

			<ToggleControl
				label="Remind By Email"
				checked={attributes.remindByEmail || false}
				onChange={(remindByEmail) =>
					setAttributes({
						remindByEmail,
					})
				}
			/>
		</>
	);
}