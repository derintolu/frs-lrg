import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	TextControl,
	TextareaControl
} from '@wordpress/components';
import { ColorPicker } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const {
		defaultCalculatorType,
		showPropertyTax,
		showInsurance,
		showHOA,
		showPMI,
		primaryColor,
		secondaryColor,
		ctaButtonText,
		disclaimerText,
		showDisclaimer,
		enableLeadCapture,
		leadCaptureTitle,
		leadCaptureDescription
	} = attributes;

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Calculator Settings', 'frs-partnership-portal')} initialOpen={true}>
					<SelectControl
						label={__('Default Calculator Type', 'frs-partnership-portal')}
						value={defaultCalculatorType}
						options={[
							{ label: __('Conventional', 'frs-partnership-portal'), value: 'conventional' },
							{ label: __('VA Loan', 'frs-partnership-portal'), value: 'va' },
							{ label: __('FHA Loan', 'frs-partnership-portal'), value: 'fha' },
							{ label: __('Refinance', 'frs-partnership-portal'), value: 'refinance' },
							{ label: __('Affordability', 'frs-partnership-portal'), value: 'affordability' }
						]}
						onChange={(value) => setAttributes({ defaultCalculatorType: value })}
						help={__('Which calculator tab should be shown by default', 'frs-partnership-portal')}
					/>
				</PanelBody>

				<PanelBody title={__('Visible Fields', 'frs-partnership-portal')} initialOpen={false}>
					<ToggleControl
						label={__('Show Property Tax', 'frs-partnership-portal')}
						checked={showPropertyTax}
						onChange={(value) => setAttributes({ showPropertyTax: value })}
					/>
					<ToggleControl
						label={__('Show Home Insurance', 'frs-partnership-portal')}
						checked={showInsurance}
						onChange={(value) => setAttributes({ showInsurance: value })}
					/>
					<ToggleControl
						label={__('Show HOA Fees', 'frs-partnership-portal')}
						checked={showHOA}
						onChange={(value) => setAttributes({ showHOA: value })}
					/>
					<ToggleControl
						label={__('Show PMI', 'frs-partnership-portal')}
						checked={showPMI}
						onChange={(value) => setAttributes({ showPMI: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Colors', 'frs-partnership-portal')} initialOpen={false}>
					<div style={{ marginBottom: '16px' }}>
						<label style={{ display: 'block', marginBottom: '8px', fontWeight: '500' }}>
							{__('Primary Color', 'frs-partnership-portal')}
						</label>
						<ColorPicker
							color={primaryColor}
							onChangeComplete={(color) => setAttributes({ primaryColor: color.hex })}
							disableAlpha
						/>
					</div>
					<div>
						<label style={{ display: 'block', marginBottom: '8px', fontWeight: '500' }}>
							{__('Secondary Color', 'frs-partnership-portal')}
						</label>
						<ColorPicker
							color={secondaryColor}
							onChangeComplete={(color) => setAttributes({ secondaryColor: color.hex })}
							disableAlpha
						/>
					</div>
				</PanelBody>

				<PanelBody title={__('Lead Capture', 'frs-partnership-portal')} initialOpen={false}>
					<ToggleControl
						label={__('Enable Lead Capture', 'frs-partnership-portal')}
						checked={enableLeadCapture}
						onChange={(value) => setAttributes({ enableLeadCapture: value })}
						help={__('Show lead capture form after calculation', 'frs-partnership-portal')}
					/>
					{enableLeadCapture && (
						<>
							<TextControl
								label={__('Lead Capture Title', 'frs-partnership-portal')}
								value={leadCaptureTitle}
								onChange={(value) => setAttributes({ leadCaptureTitle: value })}
							/>
							<TextareaControl
								label={__('Lead Capture Description', 'frs-partnership-portal')}
								value={leadCaptureDescription}
								onChange={(value) => setAttributes({ leadCaptureDescription: value })}
								rows={3}
							/>
							<TextControl
								label={__('CTA Button Text', 'frs-partnership-portal')}
								value={ctaButtonText}
								onChange={(value) => setAttributes({ ctaButtonText: value })}
							/>
						</>
					)}
				</PanelBody>

				<PanelBody title={__('Disclaimer', 'frs-partnership-portal')} initialOpen={false}>
					<ToggleControl
						label={__('Show Disclaimer', 'frs-partnership-portal')}
						checked={showDisclaimer}
						onChange={(value) => setAttributes({ showDisclaimer: value })}
					/>
					{showDisclaimer && (
						<TextareaControl
							label={__('Disclaimer Text', 'frs-partnership-portal')}
							value={disclaimerText}
							onChange={(value) => setAttributes({ disclaimerText: value })}
							rows={3}
						/>
					)}
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div
					style={{
						padding: '40px 20px',
						background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
						borderRadius: '8px',
						color: '#ffffff',
						textAlign: 'center'
					}}
				>
					<h3 style={{ margin: '0 0 12px', fontSize: '24px', fontWeight: 'bold' }}>
						{__('Mortgage Calculator', 'frs-partnership-portal')}
					</h3>
					<p style={{ margin: '0', opacity: 0.9, fontSize: '14px' }}>
						{__('5-in-1 Calculator: ', 'frs-partnership-portal')}
						{defaultCalculatorType === 'conventional' && __('Conventional', 'frs-partnership-portal')}
						{defaultCalculatorType === 'va' && __('VA Loan', 'frs-partnership-portal')}
						{defaultCalculatorType === 'fha' && __('FHA Loan', 'frs-partnership-portal')}
						{defaultCalculatorType === 'refinance' && __('Refinance', 'frs-partnership-portal')}
						{defaultCalculatorType === 'affordability' && __('Affordability', 'frs-partnership-portal')}
						{' '}{__('(default)', 'frs-partnership-portal')}
					</p>
					<div style={{
						marginTop: '20px',
						padding: '12px 16px',
						background: 'rgba(255,255,255,0.2)',
						borderRadius: '6px',
						fontSize: '13px'
					}}>
						<div style={{ marginBottom: '8px' }}>
							<strong>{__('Visible Fields:', 'frs-partnership-portal')}</strong>
						</div>
						<div style={{ display: 'flex', gap: '12px', flexWrap: 'wrap', justifyContent: 'center' }}>
							{showPropertyTax && <span>✓ {__('Property Tax', 'frs-partnership-portal')}</span>}
							{showInsurance && <span>✓ {__('Insurance', 'frs-partnership-portal')}</span>}
							{showHOA && <span>✓ {__('HOA', 'frs-partnership-portal')}</span>}
							{showPMI && <span>✓ {__('PMI', 'frs-partnership-portal')}</span>}
						</div>
					</div>
					{enableLeadCapture && (
						<div style={{
							marginTop: '16px',
							padding: '12px 16px',
							background: 'rgba(255,255,255,0.2)',
							borderRadius: '6px',
							fontSize: '13px'
						}}>
							<strong>{__('Lead Capture Enabled', 'frs-partnership-portal')}</strong>
							<div style={{ marginTop: '4px', opacity: 0.9 }}>
								{ctaButtonText}
							</div>
						</div>
					)}
					<p style={{ marginTop: '20px', fontSize: '12px', opacity: 0.7 }}>
						{__('Configure settings in the sidebar →', 'frs-partnership-portal')}
					</p>
				</div>
			</div>
		</>
	);
}
