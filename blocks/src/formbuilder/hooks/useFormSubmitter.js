import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';

export const useFormSubmitter = (innerBlocks, clientId) => {
    const { replaceBlocks }        = useDispatch('core/block-editor');
    const { moveBlocksToPosition } = useDispatch('core/block-editor');

    useEffect(() => {
        const blocks    = innerBlocks || [];

        const formsteps = blocks.filter(
            block => block.name === 'tsjippy-forms/formstep'
        );

        const controls = blocks.filter(
            block => block.name === 'tsjippy-forms/formstep-controls'
        );

        const formSubmitter = blocks.filter(
            block => {
                return (
                    block.name === 'tsjippy-forms/input' &&
                    block.attributes.type    === 'submit'
                )
            }
        );

        /**
         * Remove the form submitter and add formstep controls
         */
        if (formsteps.length > 0 && controls.length === 0 && formSubmitter.length > 0) {

            // Remove any existing form submit blocks
            console.log(formSubmitter);

            replaceBlocks(
                formSubmitter.map(block => block.clientId),
                createBlock(
                    'tsjippy-forms/formstep-controls',
                    { amount: formsteps.length }
                )
            );
        }

        /**
         * Remove the formstep control and add form submitter
         */
        if(formSubmitter.length === 0 && formsteps.length === 0 && controls.length > 0){
            replaceBlocks(
                controls[0].clientId,
                createBlock('tsjippy-forms/input', { type: 'submit', name: 'submit', value: 'Submit the form' }),
            );
        }

        /**
         * Keep the controls at the bottom
         */
        const controlsBlock = innerBlocks.find(
            block => {
                return (
                    block.name === 'tsjippy-forms/formstep-controls' ||

                    (
                        block.name === 'tsjippy-forms/input' &&
                        block.attributes.type    === 'submit'
                    )
                )
            }
        );

        if (!controlsBlock) {
            return;
        }

        const lastIndex    = innerBlocks.length - 1;
        const currentIndex = innerBlocks.findIndex(
            block => block.clientId === controlsBlock.clientId
        );

        if (currentIndex !== lastIndex) {
            moveBlocksToPosition(
                [controlsBlock.clientId],
                clientId, // from root
                clientId, // to root
                lastIndex
            );
        }
    }, [innerBlocks, replaceBlocks, clientId, moveBlocksToPosition]);
};
