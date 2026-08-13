import { createHigherOrderComponent } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';
import { addFilter } from '@wordpress/hooks';

const withConditionIndicator = createHigherOrderComponent(
    (BlockListBlock) => {
        return (props) => {
            const hasConditions = useSelect(
                (select) =>
                    select('tsjippy-forms/conditions-store')
                        .hasConditions(props.attributes.blockId),
                [props.attributes.blockId]
            );

            const wrapperProps = {
                ...props.wrapperProps,
                className: `${
                    props.wrapperProps?.className || ''
                } ${hasConditions ? 'has-conditions' : ''}`,
            };

            return (
                <BlockListBlock
                    {...props}
                    wrapperProps={wrapperProps}
                />
            );
        };
    },
    'withConditionIndicator'
);

addFilter(
    'editor.BlockListBlock',
    'tsjippy/condition-indicator',
    withConditionIndicator
);