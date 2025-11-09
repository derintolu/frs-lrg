import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { user_id } = attributes;

    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Biolink Page Settings', 'frs-partnership-portal')}>
                    <TextControl
                        label={__('User ID', 'frs-partnership-portal')}
                        value={user_id || ''}
                        onChange={(value) => setAttributes({ user_id: parseInt(value) || 0 })}
                        help={__('Leave empty to auto-detect from post author', 'frs-partnership-portal')}
                        type="number"
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <div style={{ padding: '20px', textAlign: 'center', backgroundColor: '#f0f0f0', border: '1px dashed #ccc' }}>
                    <h3>{__('Complete Biolink Page', 'frs-partnership-portal')}</h3>
                    <p>{__('This block will render the complete biolink page on the frontend.', 'frs-partnership-portal')}</p>
                    <p><small>{__('User ID:', 'frs-partnership-portal')} {user_id || __('Auto-detect', 'frs-partnership-portal')}</small></p>
                </div>
            </div>
        </>
    );
}