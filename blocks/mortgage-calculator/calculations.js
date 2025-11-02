/**
 * Mortgage calculation utilities for WordPress block
 * JavaScript version of TypeScript utils
 */

export function formatCurrency(amount) {
	return new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
		minimumFractionDigits: 0,
		maximumFractionDigits: 0
	}).format(amount);
}

export function formatCurrencyWithCents(amount) {
	return new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
		minimumFractionDigits: 2,
		maximumFractionDigits: 2
	}).format(amount);
}

export function formatPercent(value) {
	return `${value.toFixed(3)}%`;
}

export function calculateConventional(inputs) {
	const {
		homePrice,
		downPayment,
		interestRate,
		loanTerm,
		propertyTax = 0,
		insurance = 0,
		hoa = 0
	} = inputs;

	const principal = homePrice - downPayment;
	const downPaymentPercent = (downPayment / homePrice) * 100;
	const monthlyRate = interestRate / 100 / 12;
	const numberOfPayments = loanTerm * 12;

	let monthlyPI;
	if (monthlyRate === 0) {
		monthlyPI = principal / numberOfPayments;
	} else {
		monthlyPI = (principal * monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
			(Math.pow(1 + monthlyRate, numberOfPayments) - 1);
	}

	const needsPMI = downPaymentPercent < 20;
	const monthlyPMI = needsPMI ? (principal * 0.005) / 12 : 0;

	const monthlyTaxes = propertyTax / 12;
	const monthlyInsurance = insurance / 12;
	const monthlyHOA = hoa / 12;

	const totalMonthlyPayment = monthlyPI + monthlyPMI + monthlyTaxes + monthlyInsurance + monthlyHOA;

	return {
		monthlyPayment: totalMonthlyPayment,
		principalAndInterest: monthlyPI,
		pmi: monthlyPMI,
		propertyTax: monthlyTaxes,
		insurance: monthlyInsurance,
		hoa: monthlyHOA,
		loanAmount: principal,
		downPaymentPercent,
		totalInterest: (monthlyPI * numberOfPayments) - principal,
		totalPaid: totalMonthlyPayment * numberOfPayments
	};
}

export function calculateVA(inputs) {
	const {
		homePrice,
		downPayment = 0,
		interestRate,
		loanTerm,
		propertyTax = 0,
		insurance = 0,
		hoa = 0,
		fundingFeePercent = 2.3
	} = inputs;

	const fundingFee = homePrice * (fundingFeePercent / 100);
	const principal = homePrice - downPayment + fundingFee;
	const monthlyRate = interestRate / 100 / 12;
	const numberOfPayments = loanTerm * 12;

	let monthlyPI;
	if (monthlyRate === 0) {
		monthlyPI = principal / numberOfPayments;
	} else {
		monthlyPI = (principal * monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
			(Math.pow(1 + monthlyRate, numberOfPayments) - 1);
	}

	const monthlyTaxes = propertyTax / 12;
	const monthlyInsurance = insurance / 12;
	const monthlyHOA = hoa / 12;

	const totalMonthlyPayment = monthlyPI + monthlyTaxes + monthlyInsurance + monthlyHOA;

	return {
		monthlyPayment: totalMonthlyPayment,
		principalAndInterest: monthlyPI,
		fundingFee,
		propertyTax: monthlyTaxes,
		insurance: monthlyInsurance,
		hoa: monthlyHOA,
		loanAmount: principal,
		totalInterest: (monthlyPI * numberOfPayments) - principal,
		totalPaid: totalMonthlyPayment * numberOfPayments
	};
}

export function calculateFHA(inputs) {
	const {
		homePrice,
		downPayment,
		interestRate,
		loanTerm,
		propertyTax = 0,
		insurance = 0,
		hoa = 0
	} = inputs;

	const upfrontMIP = homePrice * 0.0175;
	const baseLoan = homePrice - downPayment;
	const principal = baseLoan + upfrontMIP;

	const monthlyRate = interestRate / 100 / 12;
	const numberOfPayments = loanTerm * 12;

	let monthlyPI;
	if (monthlyRate === 0) {
		monthlyPI = principal / numberOfPayments;
	} else {
		monthlyPI = (principal * monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
			(Math.pow(1 + monthlyRate, numberOfPayments) - 1);
	}

	const annualMIPRate = 0.0085;
	const monthlyMIP = (baseLoan * annualMIPRate) / 12;

	const monthlyTaxes = propertyTax / 12;
	const monthlyInsurance = insurance / 12;
	const monthlyHOA = hoa / 12;

	const totalMonthlyPayment = monthlyPI + monthlyMIP + monthlyTaxes + monthlyInsurance + monthlyHOA;

	return {
		monthlyPayment: totalMonthlyPayment,
		principalAndInterest: monthlyPI,
		upfrontMIP,
		monthlyMIP,
		propertyTax: monthlyTaxes,
		insurance: monthlyInsurance,
		hoa: monthlyHOA,
		loanAmount: principal,
		totalInterest: (monthlyPI * numberOfPayments) - principal,
		totalPaid: totalMonthlyPayment * numberOfPayments
	};
}

export function calculateRefinance(inputs) {
	const {
		currentLoanBalance,
		homeValue,
		newInterestRate,
		newLoanTerm,
		closingCosts = 0,
		currentInterestRate,
		currentPayment
	} = inputs;

	const principal = currentLoanBalance + closingCosts;
	const monthlyRate = newInterestRate / 100 / 12;
	const numberOfPayments = newLoanTerm * 12;

	let newMonthlyPayment;
	if (monthlyRate === 0) {
		newMonthlyPayment = principal / numberOfPayments;
	} else {
		newMonthlyPayment = (principal * monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
			(Math.pow(1 + monthlyRate, numberOfPayments) - 1);
	}

	const monthlySavings = currentPayment - newMonthlyPayment;
	const breakEvenMonths = closingCosts > 0 ? Math.ceil(closingCosts / Math.abs(monthlySavings)) : 0;

	const totalNewLoanCost = newMonthlyPayment * numberOfPayments;
	const totalCurrentLoanCost = currentPayment * numberOfPayments;
	const lifetimeSavings = totalCurrentLoanCost - totalNewLoanCost;

	const ltvRatio = (currentLoanBalance / homeValue) * 100;

	return {
		newMonthlyPayment,
		monthlySavings,
		breakEvenMonths,
		lifetimeSavings,
		totalNewLoanCost,
		ltvRatio,
		newLoanAmount: principal,
		totalInterest: totalNewLoanCost - principal
	};
}

export function calculateAffordability(inputs) {
	const {
		annualIncome,
		monthlyDebts,
		downPayment,
		interestRate,
		loanTerm,
		propertyTax = 2400,
		insurance = 1200,
		hoa = 0
	} = inputs;

	const monthlyIncome = annualIncome / 12;
	const maxMonthlyPayment = monthlyIncome * 0.28;
	const maxTotalDebt = monthlyIncome * 0.36;

	const availableForHousing = Math.min(maxMonthlyPayment, maxTotalDebt - monthlyDebts);

	const monthlyTaxes = propertyTax / 12;
	const monthlyInsurance = insurance / 12;
	const monthlyHOA = hoa / 12;

	const availableForPI = availableForHousing - monthlyTaxes - monthlyInsurance - monthlyHOA;

	const monthlyRate = interestRate / 100 / 12;
	const numberOfPayments = loanTerm * 12;

	let maxLoanAmount;
	if (monthlyRate === 0) {
		maxLoanAmount = availableForPI * numberOfPayments;
	} else {
		maxLoanAmount = availableForPI * (Math.pow(1 + monthlyRate, numberOfPayments) - 1) /
			(monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments));
	}

	const maxHomePrice = maxLoanAmount + downPayment;
	const downPaymentPercent = (downPayment / maxHomePrice) * 100;

	const needsPMI = downPaymentPercent < 20;
	const estimatedPMI = needsPMI ? (maxLoanAmount * 0.005) / 12 : 0;

	const estimatedMonthlyPayment = availableForPI + estimatedPMI + monthlyTaxes + monthlyInsurance + monthlyHOA;

	return {
		maxHomePrice,
		maxLoanAmount,
		estimatedMonthlyPayment,
		downPayment,
		monthlyTaxes,
		monthlyInsurance,
		monthlyHOA,
		estimatedPMI,
		frontEndRatio: (estimatedMonthlyPayment / monthlyIncome) * 100,
		backEndRatio: ((estimatedMonthlyPayment + monthlyDebts) / monthlyIncome) * 100
	};
}
