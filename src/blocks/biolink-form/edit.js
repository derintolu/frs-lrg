import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { formId, submitText } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Form Settings', 'lending-resource-hub')}>
					<TextControl
						label={__('Form ID', 'lending-resource-hub')}
						value={formId}
						onChange={(value) => setAttributes({ formId: value })}
						help={__('Enter the Fluent Forms ID', 'lending-resource-hub')}
					/>
					<TextControl
						label={__('Submit Button Text', 'lending-resource-hub')}
						value={submitText}
						onChange={(value) => setAttributes({ submitText: value })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div
					style={{
						border: '2px solid #2563eb',
						padding: '20px',
						textAlign: 'center',
						background: '#f0f9ff',
						borderRadius: '8px'
					}}
				>
					<h3 style={{ margin: '0 0 10px 0' }}>
						{__('Contact Form', 'lending-resource-hub')}
					</h3>
					<p style={{ margin: '0', color: '#666' }}>
						{__('Form ID:', 'lending-resource-hub')} {formId || __('Not set', 'lending-resource-hub')}
					</p>
					<p style={{ margin: '10px 0 0 0', fontSize: '14px', color: '#888' }}>
						{__('Button:', 'lending-resource-hub')} {submitText}
					</p>
				</div>
			</div>
		</>
	);
}
