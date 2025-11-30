import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { MortgageInput } from '../ui/mortgage-input';
import { MortgageSelect } from '../ui/mortgage-select';
import { ToggleButton } from '../ui/toggle-button';
import { Home } from 'lucide-react';
import {
  calculateConventional,
  formatCurrency,
  formatPercent,
  type ConventionalInputs
} from '../../utils/mortgageCalculations';
import { ResultsCard } from './ResultsCard';
import { US_STATES, CREDIT_SCORES } from './constants';

export function ConventionalCalculator() {
  const [inputs, setInputs] = useState<ConventionalInputs>({
    homePrice: '' as any,
    downPayment: '' as any,
    interestRate: '' as any,
    loanTerm: '' as any,
    propertyTax: '' as any,
    insurance: '' as any,
    hoa: '' as any
  });

  const [downPaymentMode, setDownPaymentMode] = useState<'$' | '%'>('$');
  const [propertyStateIndex, setPropertyStateIndex] = useState(4); // California
  const [creditScoreIndex, setCreditScoreIndex] = useState(5); // 740-759

  const results = calculateConventional(inputs);

  const handleDownPaymentChange = (value: number) => {
    if (downPaymentMode === '%') {
      const dollarAmount = (inputs.homePrice * value) / 100;
      setInputs({...inputs, downPayment: dollarAmount});
    } else {
      setInputs({...inputs, downPayment: value});
    }
  };

  const getDownPaymentDisplayValue = () => {
    if (downPaymentMode === '%') {
      return inputs.homePrice > 0 ? (inputs.downPayment / inputs.homePrice) * 100 : 0;
    }
    return inputs.downPayment;
  };

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <Card className="lg:col-span-2">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Home className="h-5 w-5" />
            Loan Details
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <MortgageInput
              label="Home Price"
              type="currency"
              value={inputs.homePrice}
              onChange={(val) => setInputs({...inputs, homePrice: val})}
              defaultValue={300000}
            />

            <MortgageInput
              label="Down Payment"
              type={downPaymentMode === '$' ? 'currency' : 'percent'}
              value={getDownPaymentDisplayValue()}
              onChange={handleDownPaymentChange}
              defaultValue={downPaymentMode === '$' ? 60000 : 20}
              rightElement={
                <ToggleButton
                  options={['$', '%']}
                  value={downPaymentMode}
                  onChange={(val) => setDownPaymentMode(val as '$' | '%')}
                />
              }
            />

            <MortgageSelect
              label="Property State"
              value={String(propertyStateIndex)}
              onChange={(val) => setPropertyStateIndex(val)}
              options={US_STATES.map((state, idx) => ({ value: String(idx), label: state }))}
            />

            <MortgageSelect
              label="Credit Score"
              value={String(creditScoreIndex)}
              onChange={(val) => setCreditScoreIndex(val)}
              options={CREDIT_SCORES.map((score, idx) => ({ value: String(idx), label: score.label }))}
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
          </div>

          <div className="text-[10px] text-gray-400 leading-tight pt-4 border-t border-gray-200">
            <p>
              Results received from this calculator are designed for comparative purposes only, and accuracy is not guaranteed.
              This calculator is made available to you as an educational tool only and calculations are based on borrower-input information.
              This is not an advertisement for the above terms, interest rates, or payment amounts. We do not guarantee the accuracy of any
              information or inputs by users of the software. This calculator does not have the ability to pre-qualify you for any loan program
              which should be verified independently with one of our Loan Consultants. Qualification for loan programs may require additional
              information such as credit scores and cash reserves which is not gathered in this calculator. Information such as interest rates
              and pricing are user input should not be perceived as a quote. Additional fees such as HOA dues are not included in calculations.
              All information such as interest rates, taxes, insurance, PMI payments, etc. are estimates and should be used for comparison only.
              We do not guarantee any of the information obtained by this calculator. To get an accurate rate and fee quote and to be prequalified
              or preapproved please use the contact form to request further assistance.
            </p>
          </div>
        </CardContent>
      </Card>

      <ResultsCard results={results} />
    </div>
  );
}
