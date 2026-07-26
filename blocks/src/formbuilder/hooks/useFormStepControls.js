import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';

export const useFormstepControls = (innerBlocks, clientId) => {
    const { insertBlock, removeBlock } = useDispatch('core/block-editor');

    useEffect(() => {
        const formsteps = (innerBlocks || []).filter(
            block => block.name === 'tsjippy-forms/formstep'
        );

        const controls = (innerBlocks || []).filter(
            block => block.name === 'tsjippy-forms/formstep-controls'
        );

        console.log(controls)

        const formSubmitter = (innerBlocks || []).filter(
            block => {
                return (
                    block.name === 'tsjippy-forms/input' &&
                    block.attributes.type    == 'submit'
                )
            }
        );

        /**
         * Remove the form submitter and add formstep controls
         */
        if (formsteps.length > 0 && controls.length === 0) {
            // Remove any existing form submit blocks
            console.log(formSubmitter);

            formSubmitter.forEach( block => {
                removeBlock(block.clientId);
            });

            // Insert the formstep button block
            insertBlock(
                createBlock('tsjippy-forms/formstep-controls', {amount: formsteps.length}),
                undefined,        // index    
                clientId   // rootClientId
            );
        }

        /**
         * Remove the formstep control and add form submitter
         */
        if(formSubmitter.length === 0 && formsteps.length === 0 && controls.length > 0){
            controls.forEach( block => {
                removeBlock(block.clientId);
            });

            // Insert the submitter
            insertBlock(
                createBlock('tsjippy-forms/input', { type: 'submit', name: 'submit', value: 'Submit the form' }),
                undefined,        // index    
                clientId   // rootClientId
            );
        }

        /**
         * Remove formstep controls and add submitter
         */
    }, [innerBlocks, insertBlock, clientId]);
};
