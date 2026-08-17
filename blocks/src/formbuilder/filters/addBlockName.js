wp.hooks.addFilter(
    'editor.BlockListBlock',
    'my-plugin/group-heading',
    (BlockListBlock) => (props) => {
        if (props.block.name.includes('tsjippy')) {
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
