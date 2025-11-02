import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const {
        form_id,
        form_type,
        form_purpose
    } = attributes;

    const blockProps = useBlockProps();

    const formTypeOptions = [
        { label: __('Conversational', 'frs-partnership-portal'), value: 'conversational' },
        { label: __('Standard', 'frs-partnership-portal'), value: 'standard' },
        { label: __('Multi-step', 'frs-partnership-portal'), value: 'multi-step' }
    ];

    const formPurposeOptions = [
        { label: __('Rate Quote', 'frs-partnership-portal'), value: 'rate_quote' },
        { label: __('Schedule Appointment', 'frs-partnership-portal'), value: 'appointment' },
        { label: __('Contact', 'frs-partnership-portal'), value: 'contact' },
        { label: __('General Inquiry', 'frs-partnership-portal'), value: 'general' }
    ];

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Form Settings', 'frs-partnership-portal')}>
                    <TextControl
                        label={__('Form ID', 'frs-partnership-portal')}
                        value={form_id}
                        onChange={(value) => setAttributes({ form_id: value })}
                        help={__('Enter the Fluent Forms ID', 'frs-partnership-portal')}
                    />
                    <SelectControl
                        label={__('Form Type', 'frs-partnership-portal')}
                        value={form_type}
                        options={formTypeOptions}
                        onChange={(value) => setAttributes({ form_type: value })}
                    />
                    <SelectControl
                        label={__('Form Purpose', 'frs-partnership-portal')}
                        value={form_purpose}
                        options={formPurposeOptions}
                        onChange={(value) => setAttributes({ form_purpose: value })}
                        help={__('This determines which button triggers this form', 'frs-partnership-portal')}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <div
                    style={{
                        border: '2px dashed #ccc',
                        padding: '20px',
                        textAlign: 'center',
                        background: '#f9f9f9',
                        borderRadius: '8px'
                    }}
                >
                    <h3 style={{ margin: '0 0 10px 0' }}>
                        {__('Hidden Form Section', 'frs-partnership-portal')}
                    </h3>
                    <p style={{ margin: '0 0 10px 0', color: '#666' }}>
                        {form_purpose === 'rate_quote' && __('Rate Quote Form', 'frs-partnership-portal')}
                        {form_purpose === 'appointment' && __('Schedule Appointment Form', 'frs-partnership-portal')}
                        {form_purpose === 'contact' && __('Contact Form', 'frs-partnership-portal')}
                        {form_purpose === 'general' && __('General Inquiry Form', 'frs-partnership-portal')}
                    </p>
                    <p style={{ margin: '0', fontSize: '14px', color: '#888' }}>
                        {__('Form ID:', 'frs-partnership-portal')} {form_id} | {__('Type:', 'frs-partnership-portal')} {form_type}
                    </p>
                    <p style={{ margin: '10px 0 0 0', fontSize: '12px', color: '#999' }}>
                        {__('This form will slide up from the bottom when triggered', 'frs-partnership-portal')}
                    </p>
                </div>
            </div>
        </>
    );
}