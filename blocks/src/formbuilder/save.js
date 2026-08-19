/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks } from "@wordpress/block-editor";
import { FormSubmitter } from "./components/Submitter.js";

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
  const blockProps = useBlockProps.save();

  return (
    <form
      method={attributes.method}
      target={attributes.target}
      autocomplete={attributes.autocomplete}
      data-formName={attributes.name}
      data-blockId={attributes.blockId}
      {...blockProps}
    >
      <input type="hidden" name="block-id" value={attributes.blockId} />
      <input type="hidden" name="post-id" value={attributes.postId} />
      <InnerBlocks.Content />
      <FormSubmitter attributes={attributes} />
    </form>
  );
}
