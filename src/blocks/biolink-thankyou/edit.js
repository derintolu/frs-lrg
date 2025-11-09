import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { purpose, title, message } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Thank You Settings', 'lending-resource-hub')}>
					<SelectControl
						label={__('Purpose', 'lending-resource-hub')}
						value={purpose}
						options={[
							{ label: __('Rate Quote', 'lending-resource-hub'), value: 'rate_quote' },
							{ label: __('Appointment', 'lending-resource-hub'), value: 'appointment' },
							{ label: __('Contact', 'lending-resource-hub'), value: 'contact' },
							{ label: __('General', 'lending-resource-hub'), value: 'general' }
						]}
						onChange={(value) => setAttributes({ purpose: value })}
						help={__('This determines which form triggers this thank you message', 'lending-resource-hub')}
					/>
					<TextControl
						label={__('Title', 'lending-resource-hub')}
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
					<TextareaControl
						label={__('Message', 'lending-resource-hub')}
						value={message}
						onChange={(value) => setAttributes({ message: value })}
						rows={4}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div
					style={{
						border: '2px solid #10b981',
						padding: '30px',
						textAlign: 'center',
						background: '#f0fdf4',
						borderRadius: '8px'
					}}
				>
					<h2 style={{ margin: '0 0 15px 0', color: '#059669' }}>
						{title}
					</h2>
					<p style={{ margin: '0 0 15px 0', color: '#666', lineHeight: '1.6' }}>
						{message}
					</p>
					<p style={{ margin: '0', fontSize: '12px', color: '#888' }}>
						{__('Purpose:', 'lending-resource-hub')} {purpose}
					</p>
				</div>
			</div>
		</>
	);
}
