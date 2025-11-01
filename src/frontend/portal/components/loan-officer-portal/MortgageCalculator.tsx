import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../ui/tabs';
import { MortgageInput } from '../ui/mortgage-input';
import { MortgageSelect } from '../ui/mortgage-select';
import { Calculator, Home } from 'lucide-react';
import {
  calculateConventional,
  calculateVA,
  calculateFHA,
  calculateRefinance,
  calculateAffordability,
  formatCurrency,
  formatCurrencyWithCents,
  formatPercent,
  type ConventionalInputs,
  type VAInputs,
  type FHAInputs,
  type RefinanceInputs,
  type AffordabilityInputs,
  type CalculationResults
} from '../../utils/mortgageCalculations';
import { PageHeader } from './PageHeader';

export function MortgageCalculator() {
  const [activeTab, setActiveTab] = useState('conventional');

  return (
    <div className="w-full max-w-7xl mx-auto p-6">
      {/* Page Header */}
      <PageHeader
        icon={Calculator}
        title="Mortgage Calculator"
        iconBgColor="linear-gradient(135deg, #3b82f6 0%, #2DD4DA 100%)"
      />
      <p className="text-muted-foreground mt-2 mb-6">
        Calculate payments for different mortgage types
      </p>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid w-full grid-cols-5 mb-6">
          <TabsTrigger value="conventional">Conventional</TabsTrigger>
          <TabsTrigger value="va">VA Loan</TabsTrigger>
          <TabsTrigger value="fha">FHA Loan</TabsTrigger>
          <TabsTrigger value="refinance">Refinance</TabsTrigger>
          <TabsTrigger value="affordability">Affordability</TabsTrigger>
        </TabsList>

        <TabsContent value="conventional">
          <ConventionalCalculator />
        </TabsContent>

        <TabsContent value="va">
          <VACalculator />
        </TabsContent>

        <TabsContent value="fha">
          <FHACalculator />
        </TabsContent>

        <TabsContent value="refinance">
          <RefinanceCalculator />
        </TabsContent>

        <TabsContent value="affordability">
          <AffordabilityCalculator />
        </TabsContent>
      </Tabs>
    </div>
  );
}

// Conventional Calculator
function ConventionalCalculator() {
  const [inputs, setInputs] = useState<ConventionalInputs>({
    homePrice: '' as any,
    downPayment: '' as any,
    interestRate: '' as any,
    loanTerm: '' as any,
    propertyTax: '' as any,
    insurance: '' as any,
    hoa: '' as any
  });

  const results = calculateConventional(inputs);

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      {/* Inputs Card - 2/3 width */}
      <Card className="lg:col-span-2">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Home className="h-5 w-5" />
            Loan Details
          </CardTitle>
        </CardHeader>
        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <MortgageInput
            label="Home Price"
            type="currency"
            value={inputs.homePrice}
            onChange={(val) => setInputs({...inputs, homePrice: val})}
            defaultValue={100000}
          />

          <div>
            <MortgageInput
              label="Down Payment"
              type="currency"
              value={inputs.downPayment}
              onChange={(val) => setInputs({...inputs, downPayment: val})}
              defaultValue={20000}
            />
            <p className="text-xs text-muted-foreground -mt-2 ml-1">
              {formatPercent((inputs.downPayment / inputs.homePrice) * 100)} of home price
            </p>
          </div>

          <MortgageInput
            label="Interest Rate"
            type="percent"
            value={inputs.interestRate}
            onChange={(val) => setInputs({...inputs, interestRate: val})}
            step="0.1"
            defaultValue={6.5}
          />

          <MortgageSelect
            label="Loan Term"
            value={String(inputs.loanTerm)}
            onChange={(val) => setInputs({...inputs, loanTerm: val})}
            options={[
              { value: '15', label: '15 years' },
              { value: '20', label: '20 years' },
              { value: '30', label: '30 years' }
            ]}
          />

          <MortgageInput
            label="Property Tax (Annual)"
            type="currency"
            value={inputs.propertyTax}
            onChange={(val) => setInputs({...inputs, propertyTax: val})}
            defaultValue={2000}
          />

          <MortgageInput
            label="Insurance (Annual)"
            type="currency"
            value={inputs.insurance}
            onChange={(val) => setInputs({...inputs, insurance: val})}
            defaultValue={1000}
          />

          <div className="md:col-span-2">
            <MortgageInput
              label="HOA Fees (Monthly)"
              type="currency"
              value={inputs.hoa}
              onChange={(val) => setInputs({...inputs, hoa: val})}
              defaultValue={0}
            />
          </div>
        </CardContent>
      </Card>

      {/* Results Card - 1/3 width */}
      <ResultsCard results={results} />
    </div>
  );
}

// VA Calculator
function VACalculator() {
  const [inputs, setInputs] = useState<VAInputs>({
    homePrice: '' as any,
    downPayment: '' as any,
    interestRate: '' as any,
    loanTerm: '' as any,
    vaFundingFeePercent: '' as any,
    propertyTax: '' as any,
    insurance: '' as any
  });

  const results = calculateVA(inputs);

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <Card className="lg:col-span-2">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Home className="h-5 w-5" />
            VA Loan Details
          </CardTitle>
        </CardHeader>
        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <MortgageInput
            label="Home Price"
            type="currency"
            value={inputs.homePrice}
            onChange={(val) => setInputs({...inputs, homePrice: val})}
            defaultValue={300000}
          />

          <MortgageInput
            label="Down Payment (Optional)"
            type="currency"
            value={inputs.downPayment}
            onChange={(val) => setInputs({...inputs, downPayment: val})}
            defaultValue={0}
          />

          <MortgageInput
            label="Interest Rate"
            type="percent"
            value={inputs.interestRate}
            onChange={(val) => setInputs({...inputs, interestRate: val})}
            step="0.1"
            defaultValue={6.25}
          />

          <MortgageSelect
            label="Loan Term"
            value={String(inputs.loanTerm)}
            onChange={(val) => setInputs({...inputs, loanTerm: val})}
            options={[
              { value: '15', label: '15 years' },
              { value: '20', label: '20 years' },
              { value: '30', label: '30 years' }
            ]}
          />

          <MortgageInput
            label="VA Funding Fee"
            type="percent"
            value={inputs.vaFundingFeePercent}
            onChange={(val) => setInputs({...inputs, vaFundingFeePercent: val})}
            step="0.1"
            defaultValue={2.3}
          />

          <MortgageInput
            label="Property Tax (Annual)"
            type="currency"
            value={inputs.propertyTax}
            onChange={(val) => setInputs({...inputs, propertyTax: val})}
            defaultValue={3000}
          />

          <div className="md:col-span-2">
            <MortgageInput
              label="Insurance (Annual)"
              type="currency"
              value={inputs.insurance}
              onChange={(val) => setInputs({...inputs, insurance: val})}
              defaultValue={1200}
            />
          </div>
        </CardContent>
      </Card>

      <ResultsCard results={results} />
    </div>
  );
}

// FHA Calculator
function FHACalculator() {
  const [inputs, setInputs] = useState<FHAInputs>({
    homePrice: '' as any,
    downPayment: '' as any,
    interestRate: '' as any,
    loanTerm: '' as any,
    upfrontMIP: '' as any,
    annualMIP: '' as any,
    propertyTax: '' as any,
    insurance: '' as any
  });

  const results = calculateFHA(inputs);

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <Card className="lg:col-span-2">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Home className="h-5 w-5" />
            FHA Loan Details
          </CardTitle>
        </CardHeader>
        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <MortgageInput
            label="Home Price"
            type="currency"
            value={inputs.homePrice}
            onChange={(val) => setInputs({...inputs, homePrice: val})}
            defaultValue={300000}
          />

          <div>
            <MortgageInput
              label="Down Payment (Min 3.5%)"
              type="currency"
              value={inputs.downPayment}
              onChange={(val) => setInputs({...inputs, downPayment: val})}
              defaultValue={10500}
            />
            <p className="text-xs text-muted-foreground -mt-2 ml-1">
              {formatPercent((inputs.downPayment / inputs.homePrice) * 100)} of home price
            </p>
          </div>

          <MortgageInput
            label="Interest Rate"
            type="percent"
            value={inputs.interestRate}
            onChange={(val) => setInputs({...inputs, interestRate: val})}
            step="0.1"
            defaultValue={6.5}
          />

          <MortgageSelect
            label="Loan Term"
            value={String(inputs.loanTerm)}
            onChange={(val) => setInputs({...inputs, loanTerm: val})}
            options={[
              { value: '15', label: '15 years' },
              { value: '20', label: '20 years' },
              { value: '30', label: '30 years' }
            ]}
          />

          <MortgageInput
            label="Upfront MIP"
            type="percent"
            value={inputs.upfrontMIP}
            onChange={(val) => setInputs({...inputs, upfrontMIP: val})}
            step="0.1"
            defaultValue={1.75}
          />

          <MortgageInput
            label="Annual MIP"
            type="percent"
            value={inputs.annualMIP}
            onChange={(val) => setInputs({...inputs, annualMIP: val})}
            step="0.1"
            defaultValue={0.85}
          />

          <MortgageInput
            label="Property Tax (Annual)"
            type="currency"
            value={inputs.propertyTax}
            onChange={(val) => setInputs({...inputs, propertyTax: val})}
            defaultValue={3000}
          />

          <MortgageInput
            label="Insurance (Annual)"
            type="currency"
            value={inputs.insurance}
            onChange={(val) => setInputs({...inputs, insurance: val})}
            defaultValue={1200}
          />
        </CardContent>
      </Card>

      <ResultsCard results={results} />
    </div>
  );
}

// Refinance Calculator
function RefinanceCalculator() {
  const [inputs, setInputs] = useState<RefinanceInputs>({
    currentLoanBalance: '' as any,
    currentInterestRate: '' as any,
    currentPayment: '' as any,
    newInterestRate: '' as any,
    newLoanTerm: '' as any,
    closingCosts: '' as any
  });

  const results = calculateRefinance(inputs);

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <Card className="lg:col-span-2">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calculator className="h-5 w-5" />
            Refinance Details
          </CardTitle>
        </CardHeader>
        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <MortgageInput
            label="Current Balance"
            type="currency"
            value={inputs.currentLoanBalance}
            onChange={(val) => setInputs({...inputs, currentLoanBalance: val})}
            defaultValue={250000}
          />

          <MortgageInput
            label="Current Rate"
            type="percent"
            value={inputs.currentInterestRate}
            onChange={(val) => setInputs({...inputs, currentInterestRate: val})}
            step="0.1"
            defaultValue={7.5}
          />

          <div className="md:col-span-2">
            <MortgageInput
              label="Current Monthly Payment"
              type="currency"
              value={inputs.currentPayment}
              onChange={(val) => setInputs({...inputs, currentPayment: val})}
              defaultValue={1748}
            />
          </div>


          <MortgageInput
            label="New Rate"
            type="percent"
            value={inputs.newInterestRate}
            onChange={(val) => setInputs({...inputs, newInterestRate: val})}
            step="0.1"
            defaultValue={6.0}
          />

          <MortgageSelect
            label="New Term"
            value={String(inputs.newLoanTerm)}
            onChange={(val) => setInputs({...inputs, newLoanTerm: val})}
            options={[
              { value: '15', label: '15 years' },
              { value: '20', label: '20 years' },
              { value: '30', label: '30 years' }
            ]}
          />

          <div className="md:col-span-2">
            <MortgageInput
              label="Closing Costs"
              type="currency"
              value={inputs.closingCosts}
              onChange={(val) => setInputs({...inputs, closingCosts: val})}
              defaultValue={5000}
            />
          </div>
        </CardContent>
      </Card>

      <RefinanceResultsCard results={results} />
    </div>
  );
}

// Affordability Calculator
function AffordabilityCalculator() {
  const [inputs, setInputs] = useState<AffordabilityInputs>({
    monthlyIncome: '' as any,
    monthlyDebts: '' as any,
    downPayment: '' as any,
    interestRate: '' as any,
    loanTerm: '' as any,
    propertyTax: '' as any,
    insurance: '' as any
  });

  const results = calculateAffordability(inputs);

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <Card className="lg:col-span-2">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calculator className="h-5 w-5" />
            Financial Details
          </CardTitle>
        </CardHeader>
        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <MortgageInput
            label="Monthly Income"
            type="currency"
            value={inputs.monthlyIncome}
            onChange={(val) => setInputs({...inputs, monthlyIncome: val})}
            defaultValue={6000}
          />

          <MortgageInput
            label="Monthly Debts"
            type="currency"
            value={inputs.monthlyDebts}
            onChange={(val) => setInputs({...inputs, monthlyDebts: val})}
            defaultValue={500}
          />

          <MortgageInput
            label="Down Payment"
            type="currency"
            value={inputs.downPayment}
            onChange={(val) => setInputs({...inputs, downPayment: val})}
            defaultValue={50000}
          />

          <MortgageInput
            label="Interest Rate"
            type="percent"
            value={inputs.interestRate}
            onChange={(val) => setInputs({...inputs, interestRate: val})}
            step="0.1"
            defaultValue={6.5}
          />

          <MortgageSelect
            label="Loan Term"
            value={String(inputs.loanTerm)}
            onChange={(val) => setInputs({...inputs, loanTerm: val})}
            options={[
              { value: '15', label: '15 years' },
              { value: '20', label: '20 years' },
              { value: '30', label: '30 years' }
            ]}
          />

          <MortgageInput
            label="Property Tax (Annual)"
            type="currency"
            value={inputs.propertyTax}
            onChange={(val) => setInputs({...inputs, propertyTax: val})}
            defaultValue={3000}
          />

          <div className="md:col-span-2">
            <MortgageInput
              label="Insurance (Annual)"
              type="currency"
              value={inputs.insurance}
              onChange={(val) => setInputs({...inputs, insurance: val})}
              defaultValue={1200}
            />
          </div>
        </CardContent>
      </Card>

      <AffordabilityResultsCard results={results} />
    </div>
  );
}

// Results Card Component
function ResultsCard({ results }: { results: CalculationResults }) {
  return (
    <Card className="h-fit" style={{
      background: 'linear-gradient(135deg, var(--brand-primary-blue) 0%, var(--brand-rich-teal) 100%)'
    }}>
      <CardHeader className="bg-black/20">
        <CardTitle className="text-white">Payment Summary</CardTitle>
      </CardHeader>
      <CardContent className="pt-6 space-y-4 text-white">
        <div className="text-center pb-4 border-b border-white/20">
          <p className="text-sm opacity-90 mb-1">Monthly Payment</p>
          <p className="text-4xl font-bold">
            {formatCurrencyWithCents(results.monthlyPayment)}
          </p>
        </div>

        <div className="space-y-3">
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Principal & Interest</span>
            <span className="font-semibold">{formatCurrency(results.principalAndInterest)}</span>
          </div>

          {results.monthlyTax !== undefined && results.monthlyTax > 0 && (
            <div className="flex justify-between text-sm">
              <span className="opacity-90">Property Tax</span>
              <span className="font-semibold">{formatCurrency(results.monthlyTax)}</span>
            </div>
          )}

          {results.monthlyInsurance !== undefined && results.monthlyInsurance > 0 && (
            <div className="flex justify-between text-sm">
              <span className="opacity-90">Insurance</span>
              <span className="font-semibold">{formatCurrency(results.monthlyInsurance)}</span>
            </div>
          )}

          {results.monthlyHOA !== undefined && results.monthlyHOA > 0 && (
            <div className="flex justify-between text-sm">
              <span className="opacity-90">HOA Fees</span>
              <span className="font-semibold">{formatCurrency(results.monthlyHOA)}</span>
            </div>
          )}

          {results.monthlyPMI !== undefined && results.monthlyPMI > 0 && (
            <div className="flex justify-between text-sm">
              <span className="opacity-90">PMI/MIP</span>
              <span className="font-semibold">{formatCurrency(results.monthlyPMI)}</span>
            </div>
          )}
        </div>

        <div className="pt-4 border-t border-white/20 space-y-2">
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Loan Amount</span>
            <span className="font-semibold">{formatCurrency(results.loanAmount || 0)}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Total Interest</span>
            <span className="font-semibold">{formatCurrency(results.totalInterest)}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Total Payment</span>
            <span className="font-semibold">{formatCurrency(results.totalPayment)}</span>
          </div>
        </div>

        {/* Progress Bar */}
        <div className="pt-4 space-y-2">
          <div className="flex justify-between text-xs opacity-90">
            <span>Principal</span>
            <span>Interest</span>
          </div>
          <div className="w-full bg-white/20 rounded-full h-2 overflow-hidden">
            <div
              className="bg-white h-full rounded-full transition-all"
              style={{
                width: `${results.totalPayment > 0 ? ((results.loanAmount || 0) / results.totalPayment) * 100 : 0}%`
              }}
            />
          </div>
          <div className="flex justify-between text-xs opacity-90">
            <span>{formatPercent(results.totalPayment > 0 ? ((results.loanAmount || 0) / results.totalPayment) * 100 : 0)}</span>
            <span>{formatPercent(results.totalPayment > 0 ? (results.totalInterest / results.totalPayment) * 100 : 0)}</span>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

// Refinance Results Card
function RefinanceResultsCard({ results }: { results: ReturnType<typeof calculateRefinance> }) {
  return (
    <Card className="h-fit" style={{
      background: 'linear-gradient(135deg, var(--brand-primary-blue) 0%, var(--brand-rich-teal) 100%)'
    }}>
      <CardHeader className="bg-black/20">
        <CardTitle className="text-white">Refinance Summary</CardTitle>
      </CardHeader>
      <CardContent className="pt-6 space-y-4 text-white">
        <div className="text-center pb-4 border-b border-white/20">
          <p className="text-sm opacity-90 mb-1">New Monthly Payment</p>
          <p className="text-4xl font-bold">
            {formatCurrencyWithCents(results.monthlyPayment)}
          </p>
        </div>

        <div className="space-y-3">
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Monthly Savings</span>
            <span className="font-semibold text-green-300">{formatCurrency(results.monthlySavings)}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Break-Even Point</span>
            <span className="font-semibold">{results.breakEvenMonths} months</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Lifetime Savings</span>
            <span className="font-semibold text-green-300">{formatCurrency(results.lifetimeSavings)}</span>
          </div>
        </div>

        <div className="pt-4 border-t border-white/20 space-y-2">
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Total Interest</span>
            <span className="font-semibold">{formatCurrency(results.totalInterest)}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Total Payment</span>
            <span className="font-semibold">{formatCurrency(results.totalPayment)}</span>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

// Affordability Results Card
function AffordabilityResultsCard({ results }: { results: ReturnType<typeof calculateAffordability> }) {
  return (
    <Card className="h-fit" style={{
      background: 'linear-gradient(135deg, var(--brand-primary-blue) 0%, var(--brand-rich-teal) 100%)'
    }}>
      <CardHeader className="bg-black/20">
        <CardTitle className="text-white">What You Can Afford</CardTitle>
      </CardHeader>
      <CardContent className="pt-6 space-y-4 text-white">
        <div className="text-center pb-4 border-b border-white/20">
          <p className="text-sm opacity-90 mb-1">Maximum Home Price</p>
          <p className="text-4xl font-bold">
            {formatCurrency(results.maxHomePrice)}
          </p>
        </div>

        <div className="space-y-3">
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Monthly Payment</span>
            <span className="font-semibold">{formatCurrencyWithCents(results.monthlyPayment)}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Maximum Loan Amount</span>
            <span className="font-semibold">{formatCurrency(results.maxLoanAmount)}</span>
          </div>
        </div>

        <div className="pt-4 border-t border-white/20 space-y-2">
          <div className="flex justify-between text-sm">
            <span className="opacity-90">Principal & Interest</span>
            <span className="font-semibold">{formatCurrency(results.principalAndInterest)}</span>
          </div>
          {results.monthlyTax !== undefined && results.monthlyTax > 0 && (
            <div className="flex justify-between text-sm">
              <span className="opacity-90">Property Tax</span>
              <span className="font-semibold">{formatCurrency(results.monthlyTax)}</span>
            </div>
          )}
          {results.monthlyInsurance !== undefined && results.monthlyInsurance > 0 && (
            <div className="flex justify-between text-sm">
              <span className="opacity-90">Insurance</span>
              <span className="font-semibold">{formatCurrency(results.monthlyInsurance)}</span>
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
