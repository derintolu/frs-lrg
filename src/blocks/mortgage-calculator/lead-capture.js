import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export function LeadCaptureModal({ isOpen, onClose, attributes, calculationType, calculationResults }) {
	const { leadCaptureTitle, leadCaptureDescription, ctaButtonText, primaryColor, secondaryColor } = attributes;

	const [formData, setFormData] = useState({
		firstName: '',
		lastName: '',
		email: '',
		phone: ''
	});
	const [submitting, setSubmitting] = useState(false);
	const [submitted, setSubmitted] = useState(false);
	const [error, setError] = useState('');

	if (!isOpen) return null;

	const handleSubmit = async (e) => {
		e.preventDefault();
		setSubmitting(true);
		setError('');

		try {
			// Get loan officer ID from page
			const loanOfficerId = window.frsCalculatorConfig?.loanOfficerId || 0;

			const response = await fetch('/wp-json/frs/v1/calculator-leads', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.frsCalculatorConfig?.restNonce || ''
				},
				body: JSON.stringify({
					first_name: formData.firstName,
					last_name: formData.lastName,
					email: formData.email,
					phone: formData.phone,
					loan_officer_id: loanOfficerId,
					calculator_type: calculationType,
					calculation_data: calculationResults
				})
			});

			if (!response.ok) {
				throw new Error('Failed to submit lead');
			}

			setSubmitted(true);
			setTimeout(() => {
				onClose();
				setSubmitted(false);
				setFormData({ firstName: '', lastName: '', email: '', phone: '' });
			}, 2000);
		} catch (err) {
			setError(err.message || 'Failed to submit. Please try again.');
		} finally {
			setSubmitting(false);
		}
	};

	const gradient = `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;

	return (
		<div style={{
			position: 'fixed',
			top: 0,
			left: 0,
			right: 0,
			bottom: 0,
			background: 'rgba(0, 0, 0, 0.5)',
			display: 'flex',
			alignItems: 'center',
			justifyContent: 'center',
			zIndex: 10000,
			padding: '20px'
		}} onClick={onClose}>
			<div
				style={{
					background: '#ffffff',
					borderRadius: '12px',
					maxWidth: '500px',
					width: '100%',
					boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
					overflow: 'hidden'
				}}
				onClick={(e) => e.stopPropagation()}
			>
				{/* Header */}
				<div style={{
					background: gradient,
					padding: '24px',
					color: '#ffffff'
				}}>
					<div style={{
						display: 'flex',
						justifyContent: 'space-between',
						alignItems: 'start',
						marginBottom: '8px'
					}}>
						<h3 style={{ margin: 0, fontSize: '24px', fontWeight: 'bold' }}>
							{leadCaptureTitle}
						</h3>
						<button
							onClick={onClose}
							style={{
								background: 'transparent',
								border: 'none',
								color: '#ffffff',
								fontSize: '24px',
								cursor: 'pointer',
								padding: '0',
								lineHeight: '1'
							}}
						>
							×
						</button>
					</div>
					<p style={{ margin: 0, opacity: 0.9, fontSize: '14px' }}>
						{leadCaptureDescription}
					</p>
				</div>

				{/* Form */}
				<div style={{ padding: '24px' }}>
					{submitted ? (
						<div style={{
							textAlign: 'center',
							padding: '40px 20px'
						}}>
							<div style={{
								width: '64px',
								height: '64px',
								background: '#10b981',
								borderRadius: '50%',
								margin: '0 auto 16px',
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'center',
								fontSize: '32px',
								color: '#ffffff'
							}}>
								✓
							</div>
							<h4 style={{ margin: '0 0 8px', fontSize: '20px', fontWeight: '600', color: '#111827' }}>
								{__('Thank You!', 'frs-partnership-portal')}
							</h4>
							<p style={{ margin: 0, color: '#6b7280', fontSize: '14px' }}>
								{__('We\'ll be in touch soon.', 'frs-partnership-portal')}
							</p>
						</div>
					) : (
						<form onSubmit={handleSubmit}>
							<div className="grid grid-cols-2 gap-4 mb-4">
								<div>
									<label className="block mb-1.5 text-sm font-medium text-gray-700">
										{__('First Name', 'frs-partnership-portal')} <span className="text-red-500">*</span>
									</label>
									<input
										type="text"
										required
										value={formData.firstName}
										onChange={(e) => setFormData({ ...formData, firstName: e.target.value })}
										className="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
									/>
								</div>
								<div>
									<label className="block mb-1.5 text-sm font-medium text-gray-700">
										{__('Last Name', 'frs-partnership-portal')} <span className="text-red-500">*</span>
									</label>
									<input
										type="text"
										required
										value={formData.lastName}
										onChange={(e) => setFormData({ ...formData, lastName: e.target.value })}
										className="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
									/>
								</div>
							</div>

							<div className="mb-4">
								<label className="block mb-1.5 text-sm font-medium text-gray-700">
									{__('Email', 'frs-partnership-portal')} <span className="text-red-500">*</span>
								</label>
								<input
									type="email"
									required
									value={formData.email}
									onChange={(e) => setFormData({ ...formData, email: e.target.value })}
									className="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
								/>
							</div>

							<div className="mb-6">
								<label className="block mb-1.5 text-sm font-medium text-gray-700">
									{__('Phone', 'frs-partnership-portal')} <span className="text-red-500">*</span>
								</label>
								<input
									type="tel"
									required
									value={formData.phone}
									onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
									className="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
								/>
							</div>

							{error && (
								<div style={{
									marginBottom: '16px',
									padding: '12px',
									background: '#fef2f2',
									border: '1px solid #fecaca',
									borderRadius: '6px',
									color: '#991b1b',
									fontSize: '14px'
								}}>
									{error}
								</div>
							)}

							<button
								type="submit"
								disabled={submitting}
								style={{
									width: '100%',
									padding: '12px 24px',
									background: gradient,
									color: '#ffffff',
									border: 'none',
									borderRadius: '6px',
									fontSize: '16px',
									fontWeight: '600',
									cursor: submitting ? 'not-allowed' : 'pointer',
									opacity: submitting ? 0.7 : 1
								}}
							>
								{submitting ? __('Submitting...', 'frs-partnership-portal') : ctaButtonText}
							</button>
						</form>
					)}
				</div>
			</div>
		</div>
	);
}
