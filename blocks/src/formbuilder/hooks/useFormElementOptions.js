import { useMemo } from '@wordpress/element';

export function useFormElementOptions(allNestedBlocks) {
	return useMemo(() => {
		return (allNestedBlocks || []).map((block) => {
			let name = block.attributes?.name ?? block.attributes?.text ?? '';
			let label = block.name;

			if (name !== '') {
				label += `: ${name}`;
			}

			return {
				label,
				value: block.clientId,
			};
		});
	}, [allNestedBlocks]);
}