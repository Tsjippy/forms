const { select } = wp.data;

wp.hooks.addFilter(
    'editor.BlockListBlock',
    'tsjippy/add-block-name',
    (BlockListBlock) => (props) => {
        const formBuilderParents = select(
            'core/block-editor'
        ).getBlockParentsByBlockName(
            props.clientId,
            'tsjippy-forms/formbuilder'
        );

        const isInsideFormBuilder = formBuilderParents.length > 0;

        if (!isInsideFormBuilder) {
            return <BlockListBlock {...props} />;
        }

        return (
            <BlockListBlock
                {...props}
                wrapperProps={{
                    ...props.wrapperProps,
                    'data-block-label': props.block.name.split('/')[1],
                }}
            />
        );
    }
);