import { render, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	ConventionalCalculator,
	VACalculator,
	FHACalculator,
	RefinanceCalculator,
	AffordabilityCalculator
} from './components.js';
import { LeadCaptureModal } from './lead-capture.js';

function MortgageCalculatorBlock({ attributes }) {
	const {
		defaultCalculatorType,
		showDisclaimer,
		disclaimerText,
		enableLeadCapture,
		primaryColor,
		secondaryColor
	} = attributes;

	const [activeTab, setActiveTab] = useState(defaultCalculatorType || 'conventional');
	const [showLeadModal, setShowLeadModal] = useState(false);
	const [calculationResults, setCalculationResults] = useState(null);

	const tabs = [
		{ id: 'conventional', label: __('Conventional', 'frs-partnership-portal') },
		{ id: 'va', label: __('VA Loan', 'frs-partnership-portal') },
		{ id: 'fha', label: __('FHA Loan', 'frs-partnership-portal') },
		{ id: 'refinance', label: __('Refinance', 'frs-partnership-portal') },
		{ id: 'affordability', label: __('Affordability', 'frs-partnership-portal') }
	];

	const handleCalculate = (results) => {
		setCalculationResults(results);
		if (enableLeadCapture) {
			setShowLeadModal(true);
		}
	};

	return (
		<div style={{
			maxWidth: '1200px',
			margin: '0 auto',
			padding: '20px'
		}}>
			{/* Tab Navigation */}
			<div style={{
				background: '#ffffff',
				borderRadius: '8px',
				padding: '8px',
				marginBottom: '24px',
				display: 'grid',
				gridTemplateColumns: 'repeat(5, 1fr)',
				gap: '8px',
				border: '1px solid #e5e7eb'
			}}>
				{tabs.map(tab => (
					<button
						key={tab.id}
						onClick={() => setActiveTab(tab.id)}
						style={{
							padding: '12px 16px',
							background: activeTab === tab.id
								? `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`
								: 'transparent',
							color: activeTab === tab.id ? '#ffffff' : '#374151',
							border: 'none',
							borderRadius: '6px',
							fontSize: '14px',
							fontWeight: '600',
							cursor: 'pointer',
							transition: 'all 0.2s'
						}}
					>
						{tab.label}
					</button>
				))}
			</div>

			{/* Calculator Content */}
			<div>
				{activeTab === 'conventional' && <ConventionalCalculator attributes={attributes} onCalculate={handleCalculate} />}
				{activeTab === 'va' && <VACalculator attributes={attributes} onCalculate={handleCalculate} />}
				{activeTab === 'fha' && <FHACalculator attributes={attributes} onCalculate={handleCalculate} />}
				{activeTab === 'refinance' && <RefinanceCalculator attributes={attributes} onCalculate={handleCalculate} />}
				{activeTab === 'affordability' && <AffordabilityCalculator attributes={attributes} onCalculate={handleCalculate} />}
			</div>

			{/* CTA Button */}
			{enableLeadCapture && (
				<div style={{
					marginTop: '32px',
					textAlign: 'center'
				}}>
					<button
						onClick={() => setShowLeadModal(true)}
						style={{
							padding: '16px 48px',
							background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
							color: '#ffffff',
							border: 'none',
							borderRadius: '8px',
							fontSize: '18px',
							fontWeight: '700',
							cursor: 'pointer',
							boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'
						}}
					>
						{attributes.ctaButtonText}
					</button>
				</div>
			)}

			{/* Disclaimer */}
			{showDisclaimer && disclaimerText && (
				<div style={{
					marginTop: '32px',
					padding: '16px',
					background: '#f9fafb',
					borderRadius: '8px',
					fontSize: '12px',
					color: '#6b7280',
					textAlign: 'center',
					border: '1px solid #e5e7eb'
				}}>
					{disclaimerText}
				</div>
			)}

			{/* Lead Capture Modal */}
			{enableLeadCapture && (
				<LeadCaptureModal
					isOpen={showLeadModal}
					onClose={() => setShowLeadModal(false)}
					attributes={attributes}
					calculationType={activeTab}
					calculationResults={calculationResults}
				/>
			)}
		</div>
	);
}

// Initialize all calculator blocks on the page
document.addEventListener('DOMContentLoaded', () => {
	const calculatorBlocks = document.querySelectorAll('.frs-mortgage-calculator-block');

	calculatorBlocks.forEach(block => {
		const attributes = JSON.parse(block.dataset.attributes || '{}');
		render(<MortgageCalculatorBlock attributes={attributes} />, block);
	});
});
