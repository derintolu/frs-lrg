import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	calculateConventional,
	calculateVA,
	calculateFHA,
	calculateRefinance,
	calculateAffordability,
	formatCurrency,
	formatCurrencyWithCents,
	formatPercent
} from './calculations';

// Input component
function InputField({ label, value, onChange, type = 'number', prefix = '', suffix = '', min = 0, step = 'any' }) {
	const [isFocused, setIsFocused] = React.useState(false);
	const hasValue = value !== '' && value !== 0;

	return (
		<div className="mb-4 relative">
			<div
				className="flex items-center gap-2 bg-gray-200 rounded-lg border px-3 py-3 transition-all duration-200"
				style={{
					borderColor: isFocused || hasValue ? 'transparent' : '#d1d5db',
					borderWidth: '2px',
					borderImage: isFocused || hasValue ? 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%) 1' : 'none'
				}}
			>
				{prefix && (
					<span className="text-gray-500 text-sm">
						{prefix}
					</span>
				)}
				{suffix && (
					<span className="text-gray-500 text-sm">
						{suffix}
					</span>
				)}
				<input
					type={type}
					value={value}
					onChange={(e) => onChange(parseFloat(e.target.value) || 0)}
					onFocus={() => setIsFocused(true)}
					onBlur={() => setIsFocused(false)}
					min={min}
					step={step}
					placeholder={label}
					className="w-full bg-transparent border-none text-sm outline-none placeholder:opacity-0"
				/>
				<label
					className="absolute left-3 bg-gray-200 px-1 text-sm pointer-events-none transition-all duration-200 origin-left"
					style={{
						top: isFocused || hasValue ? '-0.5rem' : '0.75rem',
						transform: isFocused || hasValue ? 'scale(0.75)' : 'scale(1)',
						background: isFocused || hasValue ? 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)' : '#e5e7eb',
						WebkitBackgroundClip: isFocused || hasValue ? 'text' : 'border-box',
						WebkitTextFillColor: isFocused || hasValue ? 'transparent' : '#374151',
						backgroundClip: isFocused || hasValue ? 'text' : 'border-box'
					}}
				>
					{label}
				</label>
			</div>
		</div>
	);
}

// Results card component
function ResultsCard({ results, primaryColor, secondaryColor, type }) {
	const gradient = `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;

	return (
		<div style={{
			background: gradient,
			borderRadius: '8px',
			padding: '24px',
			color: '#ffffff',
			height: 'fit-content'
		}}>
			<h3 style={{ margin: '0 0 20px', fontSize: '18px', fontWeight: '600' }}>
				{__('Results', 'frs-partnership-portal')}
			</h3>

			{type === 'conventional' && (
				<>
					<ResultRow label={__('Monthly Payment', 'frs-partnership-portal')} value={formatCurrency(results.monthlyPayment)} large />
					<ResultRow label={__('Principal & Interest', 'frs-partnership-portal')} value={formatCurrency(results.principalAndInterest)} />
					{results.pmi > 0 && <ResultRow label={__('PMI', 'frs-partnership-portal')} value={formatCurrency(results.pmi)} />}
					<ResultRow label={__('Property Tax', 'frs-partnership-portal')} value={formatCurrency(results.propertyTax)} />
					<ResultRow label={__('Insurance', 'frs-partnership-portal')} value={formatCurrency(results.insurance)} />
					{results.hoa > 0 && <ResultRow label={__('HOA', 'frs-partnership-portal')} value={formatCurrency(results.hoa)} />}
					<div style={{ borderTop: '1px solid rgba(255,255,255,0.3)', margin: '16px 0', paddingTop: '16px' }}>
						<ResultRow label={__('Loan Amount', 'frs-partnership-portal')} value={formatCurrency(results.loanAmount)} />
						<ResultRow label={__('Down Payment', 'frs-partnership-portal')} value={formatPercent(results.downPaymentPercent)} />
						<ResultRow label={__('Total Interest', 'frs-partnership-portal')} value={formatCurrency(results.totalInterest)} />
					</div>
				</>
			)}

			{type === 'va' && (
				<>
					<ResultRow label={__('Monthly Payment', 'frs-partnership-portal')} value={formatCurrency(results.monthlyPayment)} large />
					<ResultRow label={__('Principal & Interest', 'frs-partnership-portal')} value={formatCurrency(results.principalAndInterest)} />
					<ResultRow label={__('Property Tax', 'frs-partnership-portal')} value={formatCurrency(results.propertyTax)} />
					<ResultRow label={__('Insurance', 'frs-partnership-portal')} value={formatCurrency(results.insurance)} />
					{results.hoa > 0 && <ResultRow label={__('HOA', 'frs-partnership-portal')} value={formatCurrency(results.hoa)} />}
					<div style={{ borderTop: '1px solid rgba(255,255,255,0.3)', margin: '16px 0', paddingTop: '16px' }}>
						<ResultRow label={__('Loan Amount', 'frs-partnership-portal')} value={formatCurrency(results.loanAmount)} />
						<ResultRow label={__('Funding Fee', 'frs-partnership-portal')} value={formatCurrency(results.fundingFee)} />
						<ResultRow label={__('Total Interest', 'frs-partnership-portal')} value={formatCurrency(results.totalInterest)} />
					</div>
				</>
			)}

			{type === 'fha' && (
				<>
					<ResultRow label={__('Monthly Payment', 'frs-partnership-portal')} value={formatCurrency(results.monthlyPayment)} large />
					<ResultRow label={__('Principal & Interest', 'frs-partnership-portal')} value={formatCurrency(results.principalAndInterest)} />
					<ResultRow label={__('Monthly MIP', 'frs-partnership-portal')} value={formatCurrency(results.monthlyMIP)} />
					<ResultRow label={__('Property Tax', 'frs-partnership-portal')} value={formatCurrency(results.propertyTax)} />
					<ResultRow label={__('Insurance', 'frs-partnership-portal')} value={formatCurrency(results.insurance)} />
					{results.hoa > 0 && <ResultRow label={__('HOA', 'frs-partnership-portal')} value={formatCurrency(results.hoa)} />}
					<div style={{ borderTop: '1px solid rgba(255,255,255,0.3)', margin: '16px 0', paddingTop: '16px' }}>
						<ResultRow label={__('Loan Amount', 'frs-partnership-portal')} value={formatCurrency(results.loanAmount)} />
						<ResultRow label={__('Upfront MIP', 'frs-partnership-portal')} value={formatCurrency(results.upfrontMIP)} />
						<ResultRow label={__('Total Interest', 'frs-partnership-portal')} value={formatCurrency(results.totalInterest)} />
					</div>
				</>
			)}

			{type === 'refinance' && (
				<>
					<ResultRow label={__('New Monthly Payment', 'frs-partnership-portal')} value={formatCurrency(results.newMonthlyPayment)} large />
					<ResultRow
						label={__('Monthly Savings', 'frs-partnership-portal')}
						value={formatCurrency(Math.abs(results.monthlySavings))}
						positive={results.monthlySavings > 0}
					/>
					<ResultRow label={__('Break-Even', 'frs-partnership-portal')} value={`${results.breakEvenMonths} months`} />
					<div style={{ borderTop: '1px solid rgba(255,255,255,0.3)', margin: '16px 0', paddingTop: '16px' }}>
						<ResultRow label={__('New Loan Amount', 'frs-partnership-portal')} value={formatCurrency(results.newLoanAmount)} />
						<ResultRow label={__('LTV Ratio', 'frs-partnership-portal')} value={formatPercent(results.ltvRatio)} />
						<ResultRow
							label={__('Lifetime Savings', 'frs-partnership-portal')}
							value={formatCurrency(Math.abs(results.lifetimeSavings))}
							positive={results.lifetimeSavings > 0}
						/>
					</div>
				</>
			)}

			{type === 'affordability' && (
				<>
					<ResultRow label={__('Max Home Price', 'frs-partnership-portal')} value={formatCurrency(results.maxHomePrice)} large />
					<ResultRow label={__('Max Loan Amount', 'frs-partnership-portal')} value={formatCurrency(results.maxLoanAmount)} />
					<ResultRow label={__('Est. Monthly Payment', 'frs-partnership-portal')} value={formatCurrency(results.estimatedMonthlyPayment)} />
					<div style={{ borderTop: '1px solid rgba(255,255,255,0.3)', margin: '16px 0', paddingTop: '16px' }}>
						<ResultRow label={__('Down Payment', 'frs-partnership-portal')} value={formatCurrency(results.downPayment)} />
						{results.estimatedPMI > 0 && <ResultRow label={__('Est. PMI', 'frs-partnership-portal')} value={formatCurrency(results.estimatedPMI)} />}
						<ResultRow label={__('Front-End Ratio', 'frs-partnership-portal')} value={formatPercent(results.frontEndRatio)} />
						<ResultRow label={__('Back-End Ratio', 'frs-partnership-portal')} value={formatPercent(results.backEndRatio)} />
					</div>
				</>
			)}
		</div>
	);
}

function ResultRow({ label, value, large = false, positive = null }) {
	const color = positive === null ? '#ffffff' : positive ? '#10b981' : '#ef4444';
	return (
		<div style={{
			display: 'flex',
			justifyContent: 'space-between',
			alignItems: 'center',
			marginBottom: large ? '16px' : '8px',
			paddingBottom: large ? '12px' : '0',
			borderBottom: large ? '1px solid rgba(255,255,255,0.3)' : 'none'
		}}>
			<span style={{ fontSize: large ? '14px' : '13px', opacity: 0.9 }}>{label}</span>
			<span style={{
				fontSize: large ? '24px' : '14px',
				fontWeight: large ? '700' : '600',
				color
			}}>
				{value}
			</span>
		</div>
	);
}

// Conventional Calculator
export function ConventionalCalculator({ attributes }) {
	const { showPropertyTax, showInsurance, showHOA, showPMI, primaryColor, secondaryColor } = attributes;

	const [homePrice, setHomePrice] = useState(300000);
	const [downPayment, setDownPayment] = useState(60000);
	const [interestRate, setInterestRate] = useState(6.5);
	const [loanTerm, setLoanTerm] = useState(30);
	const [propertyTax, setPropertyTax] = useState(3600);
	const [insurance, setInsurance] = useState(1200);
	const [hoa, setHOA] = useState(0);

	const [results, setResults] = useState(null);

	useEffect(() => {
		const calculated = calculateConventional({
			homePrice,
			downPayment,
			interestRate,
			loanTerm,
			propertyTax: showPropertyTax ? propertyTax : 0,
			insurance: showInsurance ? insurance : 0,
			hoa: showHOA ? hoa : 0
		});
		setResults(calculated);
	}, [homePrice, downPayment, interestRate, loanTerm, propertyTax, insurance, hoa, showPropertyTax, showInsurance, showHOA]);

	return (
		<div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '24px' }}>
			<div style={{
				display: 'grid',
				gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
				gap: '24px'
			}}>
				<div style={{
					background: '#ffffff',
					borderRadius: '8px',
					padding: '24px',
					border: '1px solid #e5e7eb',
					gridColumn: 'span 2'
				}}>
					<h3 style={{ margin: '0 0 20px', fontSize: '18px', fontWeight: '600', color: '#111827' }}>
						{__('Loan Details', 'frs-partnership-portal')}
					</h3>
					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
						<InputField label={__('Home Price', 'frs-partnership-portal')} value={homePrice} onChange={setHomePrice} prefix="$" />
						<InputField label={__('Down Payment', 'frs-partnership-portal')} value={downPayment} onChange={setDownPayment} prefix="$" />
						<InputField label={__('Interest Rate', 'frs-partnership-portal')} value={interestRate} onChange={setInterestRate} suffix="%" step="0.001" />
						<InputField label={__('Loan Term (years)', 'frs-partnership-portal')} value={loanTerm} onChange={setLoanTerm} />
					</div>

					{(showPropertyTax || showInsurance || showHOA) && (
						<>
							<h4 style={{ margin: '24px 0 16px', fontSize: '16px', fontWeight: '600', color: '#111827' }}>
								{__('Additional Costs (Annual)', 'frs-partnership-portal')}
							</h4>
							<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
								{showPropertyTax && <InputField label={__('Property Tax', 'frs-partnership-portal')} value={propertyTax} onChange={setPropertyTax} prefix="$" />}
								{showInsurance && <InputField label={__('Home Insurance', 'frs-partnership-portal')} value={insurance} onChange={setInsurance} prefix="$" />}
								{showHOA && <InputField label={__('HOA Fees', 'frs-partnership-portal')} value={hoa} onChange={setHOA} prefix="$" />}
							</div>
						</>
					)}
				</div>

				{results && <ResultsCard results={results} primaryColor={primaryColor} secondaryColor={secondaryColor} type="conventional" />}
			</div>
		</div>
	);
}

// VA Calculator
export function VACalculator({ attributes }) {
	const { showPropertyTax, showInsurance, showHOA, primaryColor, secondaryColor } = attributes;

	const [homePrice, setHomePrice] = useState(300000);
	const [downPayment, setDownPayment] = useState(0);
	const [interestRate, setInterestRate] = useState(6.25);
	const [loanTerm, setLoanTerm] = useState(30);
	const [fundingFeePercent, setFundingFeePercent] = useState(2.3);
	const [propertyTax, setPropertyTax] = useState(3600);
	const [insurance, setInsurance] = useState(1200);
	const [hoa, setHOA] = useState(0);

	const [results, setResults] = useState(null);

	useEffect(() => {
		const calculated = calculateVA({
			homePrice,
			downPayment,
			interestRate,
			loanTerm,
			fundingFeePercent,
			propertyTax: showPropertyTax ? propertyTax : 0,
			insurance: showInsurance ? insurance : 0,
			hoa: showHOA ? hoa : 0
		});
		setResults(calculated);
	}, [homePrice, downPayment, interestRate, loanTerm, fundingFeePercent, propertyTax, insurance, hoa, showPropertyTax, showInsurance, showHOA]);

	return (
		<div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '24px' }}>
			<div style={{
				display: 'grid',
				gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
				gap: '24px'
			}}>
				<div style={{
					background: '#ffffff',
					borderRadius: '8px',
					padding: '24px',
					border: '1px solid #e5e7eb',
					gridColumn: 'span 2'
				}}>
					<h3 style={{ margin: '0 0 20px', fontSize: '18px', fontWeight: '600', color: '#111827' }}>
						{__('VA Loan Details', 'frs-partnership-portal')}
					</h3>
					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
						<InputField label={__('Home Price', 'frs-partnership-portal')} value={homePrice} onChange={setHomePrice} prefix="$" />
						<InputField label={__('Down Payment', 'frs-partnership-portal')} value={downPayment} onChange={setDownPayment} prefix="$" />
						<InputField label={__('Interest Rate', 'frs-partnership-portal')} value={interestRate} onChange={setInterestRate} suffix="%" step="0.001" />
						<InputField label={__('Loan Term (years)', 'frs-partnership-portal')} value={loanTerm} onChange={setLoanTerm} />
						<InputField label={__('Funding Fee %', 'frs-partnership-portal')} value={fundingFeePercent} onChange={setFundingFeePercent} suffix="%" step="0.1" />
					</div>

					{(showPropertyTax || showInsurance || showHOA) && (
						<>
							<h4 style={{ margin: '24px 0 16px', fontSize: '16px', fontWeight: '600', color: '#111827' }}>
								{__('Additional Costs (Annual)', 'frs-partnership-portal')}
							</h4>
							<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
								{showPropertyTax && <InputField label={__('Property Tax', 'frs-partnership-portal')} value={propertyTax} onChange={setPropertyTax} prefix="$" />}
								{showInsurance && <InputField label={__('Home Insurance', 'frs-partnership-portal')} value={insurance} onChange={setInsurance} prefix="$" />}
								{showHOA && <InputField label={__('HOA Fees', 'frs-partnership-portal')} value={hoa} onChange={setHOA} prefix="$" />}
							</div>
						</>
					)}
				</div>

				{results && <ResultsCard results={results} primaryColor={primaryColor} secondaryColor={secondaryColor} type="va" />}
			</div>
		</div>
	);
}

// FHA Calculator
export function FHACalculator({ attributes }) {
	const { showPropertyTax, showInsurance, showHOA, primaryColor, secondaryColor } = attributes;

	const [homePrice, setHomePrice] = useState(300000);
	const [downPayment, setDownPayment] = useState(10500);
	const [interestRate, setInterestRate] = useState(6.5);
	const [loanTerm, setLoanTerm] = useState(30);
	const [propertyTax, setPropertyTax] = useState(3600);
	const [insurance, setInsurance] = useState(1200);
	const [hoa, setHOA] = useState(0);

	const [results, setResults] = useState(null);

	useEffect(() => {
		const calculated = calculateFHA({
			homePrice,
			downPayment,
			interestRate,
			loanTerm,
			propertyTax: showPropertyTax ? propertyTax : 0,
			insurance: showInsurance ? insurance : 0,
			hoa: showHOA ? hoa : 0
		});
		setResults(calculated);
	}, [homePrice, downPayment, interestRate, loanTerm, propertyTax, insurance, hoa, showPropertyTax, showInsurance, showHOA]);

	return (
		<div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '24px' }}>
			<div style={{
				display: 'grid',
				gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
				gap: '24px'
			}}>
				<div style={{
					background: '#ffffff',
					borderRadius: '8px',
					padding: '24px',
					border: '1px solid #e5e7eb',
					gridColumn: 'span 2'
				}}>
					<h3 style={{ margin: '0 0 20px', fontSize: '18px', fontWeight: '600', color: '#111827' }}>
						{__('FHA Loan Details', 'frs-partnership-portal')}
					</h3>
					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
						<InputField label={__('Home Price', 'frs-partnership-portal')} value={homePrice} onChange={setHomePrice} prefix="$" />
						<InputField label={__('Down Payment', 'frs-partnership-portal')} value={downPayment} onChange={setDownPayment} prefix="$" />
						<InputField label={__('Interest Rate', 'frs-partnership-portal')} value={interestRate} onChange={setInterestRate} suffix="%" step="0.001" />
						<InputField label={__('Loan Term (years)', 'frs-partnership-portal')} value={loanTerm} onChange={setLoanTerm} />
					</div>

					{(showPropertyTax || showInsurance || showHOA) && (
						<>
							<h4 style={{ margin: '24px 0 16px', fontSize: '16px', fontWeight: '600', color: '#111827' }}>
								{__('Additional Costs (Annual)', 'frs-partnership-portal')}
							</h4>
							<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
								{showPropertyTax && <InputField label={__('Property Tax', 'frs-partnership-portal')} value={propertyTax} onChange={setPropertyTax} prefix="$" />}
								{showInsurance && <InputField label={__('Home Insurance', 'frs-partnership-portal')} value={insurance} onChange={setInsurance} prefix="$" />}
								{showHOA && <InputField label={__('HOA Fees', 'frs-partnership-portal')} value={hoa} onChange={setHOA} prefix="$" />}
							</div>
						</>
					)}
				</div>

				{results && <ResultsCard results={results} primaryColor={primaryColor} secondaryColor={secondaryColor} type="fha" />}
			</div>
		</div>
	);
}

// Refinance Calculator
export function RefinanceCalculator({ attributes }) {
	const { primaryColor, secondaryColor } = attributes;

	const [currentLoanBalance, setCurrentLoanBalance] = useState(250000);
	const [homeValue, setHomeValue] = useState(300000);
	const [currentInterestRate, setCurrentInterestRate] = useState(7.5);
	const [currentPayment, setCurrentPayment] = useState(1748);
	const [newInterestRate, setNewInterestRate] = useState(6.25);
	const [newLoanTerm, setNewLoanTerm] = useState(30);
	const [closingCosts, setClosingCosts] = useState(5000);

	const [results, setResults] = useState(null);

	useEffect(() => {
		const calculated = calculateRefinance({
			currentLoanBalance,
			homeValue,
			currentInterestRate,
			currentPayment,
			newInterestRate,
			newLoanTerm,
			closingCosts
		});
		setResults(calculated);
	}, [currentLoanBalance, homeValue, currentInterestRate, currentPayment, newInterestRate, newLoanTerm, closingCosts]);

	return (
		<div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '24px' }}>
			<div style={{
				display: 'grid',
				gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
				gap: '24px'
			}}>
				<div style={{
					background: '#ffffff',
					borderRadius: '8px',
					padding: '24px',
					border: '1px solid #e5e7eb',
					gridColumn: 'span 2'
				}}>
					<h3 style={{ margin: '0 0 20px', fontSize: '18px', fontWeight: '600', color: '#111827' }}>
						{__('Current Loan', 'frs-partnership-portal')}
					</h3>
					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
						<InputField label={__('Current Loan Balance', 'frs-partnership-portal')} value={currentLoanBalance} onChange={setCurrentLoanBalance} prefix="$" />
						<InputField label={__('Home Value', 'frs-partnership-portal')} value={homeValue} onChange={setHomeValue} prefix="$" />
						<InputField label={__('Current Interest Rate', 'frs-partnership-portal')} value={currentInterestRate} onChange={setCurrentInterestRate} suffix="%" step="0.001" />
						<InputField label={__('Current Monthly Payment', 'frs-partnership-portal')} value={currentPayment} onChange={setCurrentPayment} prefix="$" />
					</div>

					<h3 style={{ margin: '24px 0 16px', fontSize: '18px', fontWeight: '600', color: '#111827' }}>
						{__('New Loan', 'frs-partnership-portal')}
					</h3>
					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
						<InputField label={__('New Interest Rate', 'frs-partnership-portal')} value={newInterestRate} onChange={setNewInterestRate} suffix="%" step="0.001" />
						<InputField label={__('New Loan Term (years)', 'frs-partnership-portal')} value={newLoanTerm} onChange={setNewLoanTerm} />
						<InputField label={__('Closing Costs', 'frs-partnership-portal')} value={closingCosts} onChange={setClosingCosts} prefix="$" />
					</div>
				</div>

				{results && <ResultsCard results={results} primaryColor={primaryColor} secondaryColor={secondaryColor} type="refinance" />}
			</div>
		</div>
	);
}

// Affordability Calculator
export function AffordabilityCalculator({ attributes }) {
	const { showPropertyTax, showInsurance, showHOA, primaryColor, secondaryColor } = attributes;

	const [annualIncome, setAnnualIncome] = useState(75000);
	const [monthlyDebts, setMonthlyDebts] = useState(500);
	const [downPayment, setDownPayment] = useState(30000);
	const [interestRate, setInterestRate] = useState(6.5);
	const [loanTerm, setLoanTerm] = useState(30);
	const [propertyTax, setPropertyTax] = useState(2400);
	const [insurance, setInsurance] = useState(1200);
	const [hoa, setHOA] = useState(0);

	const [results, setResults] = useState(null);

	useEffect(() => {
		const calculated = calculateAffordability({
			annualIncome,
			monthlyDebts,
			downPayment,
			interestRate,
			loanTerm,
			propertyTax: showPropertyTax ? propertyTax : 2400,
			insurance: showInsurance ? insurance : 1200,
			hoa: showHOA ? hoa : 0
		});
		setResults(calculated);
	}, [annualIncome, monthlyDebts, downPayment, interestRate, loanTerm, propertyTax, insurance, hoa, showPropertyTax, showInsurance, showHOA]);

	return (
		<div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '24px' }}>
			<div style={{
				display: 'grid',
				gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
				gap: '24px'
			}}>
				<div style={{
					background: '#ffffff',
					borderRadius: '8px',
					padding: '24px',
					border: '1px solid #e5e7eb',
					gridColumn: 'span 2'
				}}>
					<h3 style={{ margin: '0 0 20px', fontSize: '18px', fontWeight: '600', color: '#111827' }}>
						{__('Income & Debts', 'frs-partnership-portal')}
					</h3>
					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
						<InputField label={__('Annual Income', 'frs-partnership-portal')} value={annualIncome} onChange={setAnnualIncome} prefix="$" />
						<InputField label={__('Monthly Debts', 'frs-partnership-portal')} value={monthlyDebts} onChange={setMonthlyDebts} prefix="$" />
						<InputField label={__('Down Payment', 'frs-partnership-portal')} value={downPayment} onChange={setDownPayment} prefix="$" />
					</div>

					<h4 style={{ margin: '24px 0 16px', fontSize: '16px', fontWeight: '600', color: '#111827' }}>
						{__('Loan Terms', 'frs-partnership-portal')}
					</h4>
					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
						<InputField label={__('Interest Rate', 'frs-partnership-portal')} value={interestRate} onChange={setInterestRate} suffix="%" step="0.001" />
						<InputField label={__('Loan Term (years)', 'frs-partnership-portal')} value={loanTerm} onChange={setLoanTerm} />
					</div>

					{(showPropertyTax || showInsurance || showHOA) && (
						<>
							<h4 style={{ margin: '24px 0 16px', fontSize: '16px', fontWeight: '600', color: '#111827' }}>
								{__('Estimated Costs (Annual)', 'frs-partnership-portal')}
							</h4>
							<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }}>
								{showPropertyTax && <InputField label={__('Property Tax', 'frs-partnership-portal')} value={propertyTax} onChange={setPropertyTax} prefix="$" />}
								{showInsurance && <InputField label={__('Home Insurance', 'frs-partnership-portal')} value={insurance} onChange={setInsurance} prefix="$" />}
								{showHOA && <InputField label={__('HOA Fees', 'frs-partnership-portal')} value={hoa} onChange={setHOA} prefix="$" />}
							</div>
						</>
					)}
				</div>

				{results && <ResultsCard results={results} primaryColor={primaryColor} secondaryColor={secondaryColor} type="affordability" />}
			</div>
		</div>
	);
}
