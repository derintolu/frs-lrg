import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('lrh/mortgage-form', {
    edit: () => {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <div style={{ padding: '20px', border: '2px dashed #ccc', borderRadius: '8px', textAlign: 'center' }}>
                    <h3>Mortgage Application Form</h3>
                    <p>Multi-step form with calculator (renders on frontend)</p>
                </div>
            </div>
        );
    },
});
