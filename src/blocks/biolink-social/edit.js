import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Button, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { links, layout } = attributes;
	const blockProps = useBlockProps();

	const addLink = () => {
		const newLinks = [...links, { platform: 'facebook', url: '' }];
		setAttributes({ links: newLinks });
	};

	const updateLink = (index, field, value) => {
		const newLinks = [...links];
		newLinks[index][field] = value;
		setAttributes({ links: newLinks });
	};

	const removeLink = (index) => {
		const newLinks = links.filter((_, i) => i !== index);
		setAttributes({ links: newLinks });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Social Links Settings', 'lending-resource-hub')}>
					<SelectControl
						label={__('Layout', 'lending-resource-hub')}
						value={layout}
						options={[
							{ label: __('Horizontal', 'lending-resource-hub'), value: 'horizontal' },
							{ label: __('Vertical', 'lending-resource-hub'), value: 'vertical' }
						]}
						onChange={(value) => setAttributes({ layout: value })}
					/>
					{links.map((link, index) => (
						<div key={index} style={{ marginBottom: '15px', paddingBottom: '15px', borderBottom: '1px solid #ddd' }}>
							<SelectControl
								label={__('Platform', 'lending-resource-hub')}
								value={link.platform}
								options={[
									{ label: 'Facebook', value: 'facebook' },
									{ label: 'Instagram', value: 'instagram' },
									{ label: 'LinkedIn', value: 'linkedin' },
									{ label: 'Twitter', value: 'twitter' },
									{ label: 'YouTube', value: 'youtube' }
								]}
								onChange={(value) => updateLink(index, 'platform', value)}
							/>
							<TextControl
								label={__('URL', 'lending-resource-hub')}
								value={link.url}
								onChange={(value) => updateLink(index, 'url', value)}
								type="url"
							/>
							<Button
								isDestructive
								onClick={() => removeLink(index)}
							>
								{__('Remove', 'lending-resource-hub')}
							</Button>
						</div>
					))}
					<Button
						isPrimary
						onClick={addLink}
					>
						{__('Add Social Link', 'lending-resource-hub')}
					</Button>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div
					style={{
						border: '2px dashed #2dd4da',
						padding: '20px',
						textAlign: 'center',
						background: '#f0fdfa',
						borderRadius: '8px'
					}}
				>
					<h3 style={{ margin: '0 0 10px 0' }}>
						{__('Social Links', 'lending-resource-hub')}
					</h3>
					<p style={{ margin: '0', color: '#666' }}>
						{links.length} {__('links configured', 'lending-resource-hub')}
					</p>
					<p style={{ margin: '10px 0 0 0', fontSize: '14px', color: '#888' }}>
						{__('Layout:', 'lending-resource-hub')} {layout}
					</p>
				</div>
			</div>
		</>
	);
}
