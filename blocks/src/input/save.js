/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */

import { useBlockProps } from '@wordpress/block-editor';

import { InputHtml } from './components/InputHtml.js';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into post_content.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element}
 */
export default function save({ attributes }) {

    const blockProps = useBlockProps.save();

    return (
        ['text', "email", "tel", "text", "url"].includes(attributes.type) && attributes.multiple ?
            <div className={`${blockProps.className ?? ''} option-wrapper`}>
                <ul className="list-selection-list" />
                <div className="multi-text-input-wrapper">
                    <InputHtml
                        attributes={attributes}
                        blockProps={blockProps}
                        labelChild={false}
                        isSaving={true}
                    />
                    <button
                        type="button"
                        className="small add-list-selection hidden"
                    >
                        add
                    </button>
                </div>
            </div>
        :
            <InputHtml
                attributes={attributes}
                blockProps={blockProps}
                labelChild={attributes.labelChild}
                isSaving={true}
            />
    );
}
