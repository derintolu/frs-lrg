# Comprehensive Goalee Calculator Analysis
## Complete Feature Comparison for Mortgage Calculator Widget

**Date:** 2025-11-06
**Purpose:** Document all features across all 7 Goalee calculator types to identify what's missing in our implementation

---

## 📈 CHART TYPES BY CALCULATOR

Each calculator has its own unique chart/visualization:

1. **Payment Calculator** → DONUT CHART with center text "$3,382.78 per month" + legend list
2. **Affordability Calculator** → HORIZONTAL BAR CHART (stacked) showing payment breakdown
3. **Buydown Calculator** → STACKED VERTICAL BAR CHART (payment + savings overlay)
4. **DSCR Calculator** → COMPARISON VERTICAL BAR CHART (Minimum vs Calculated)
5. **Refinance Calculator** → NO CHART (uses comparison table instead)
6. **Net Proceeds Calculator** → NO CHART (just result card + itemized list)
7. **Rent vs Buy Calculator** → LINE CHART (2 lines showing costs over 30 years)

---

## 🎯 UNIVERSAL FEATURES (All Calculators)

### Profile Section
- ✅ Avatar (circular)
- ✅ Name (bold, large)
- ✅ Phone with icon
- ✅ Email with icon
- ✅ NMLS# in blue

### Action Buttons (Always Present)
- ❌ **"Email me this"** button (blue) - Opens modal to email results
- ❌ **"Have a question?"** button (white with border) - Opens contact form
- ❌ **"Share"** button (blue) - Opens share modal (social media, copy link)

### Footer Elements
- ✅ Powered by Goalee logo/link
- ❌ **Collapsible Disclaimer** - Expandable section with legal text (starts collapsed)
- ✅ Copyright
- ✅ Social media icons

---

## 📊 CALCULATOR #1: PAYMENT CALCULATOR
**Chart Type: DONUT CHART with center text and legend list**

### Input Section (Left Column)

#### Loan Type Tabs - **CRITICAL FUNCTIONALITY**
**Current Issue:** Our tabs exist but don't do anything!

**What Loan Type Tabs MUST Do:**

**Conventional (Default):**
- PMI field label: "PMI (Monthly)"
- Down Payment: any amount (typically 6.50%+)
- No special fees

**FHA:**
- PMI field label changes to: **"Monthly MIP"**
- Adds field: **"Upfront MIP"** (percentage field, default 1.75%)
- Adds field: **"Annual MIP"** (percentage field, default 0.55%)
- Down Payment: minimum 3.50%
- Breakdown shows "Monthly MIP" not "PMI"

**VA:**
- PMI field label changes to: **"Funding Fee"**
- Adds field: **"Upfront Funding Fee"** (percentage)
- Down Payment: 0% allowed
- No MIP fields

**USDA:**
- Similar to FHA with guarantee fee instead of MIP

**Jumbo:**
- Higher loan minimums
- Different rate calculations

#### Input Fields (All Loan Types)
- Home Price (slider + text input with $)
- Down Payment (slider + text input with $ OR %)
- Interest Rate (slider + text input with %)
- Loan Term (dropdown: 15, 20, 30 year)
- Property Tax (slider + text input with $ - Annual)
- Home Insurance (slider + text input with $ - Monthly)
- HOA (slider + text input with $ - Monthly)
- PMI/MIP/Funding Fee (slider + text input with $ - Monthly)

### Results Section (Right Column)

#### Top Result Card (Dark Blue Background)
**Header Text:** "TOTAL MONTHLY PAYMENT"

**Center Large Amount with Donut Chart:**
- ❌ **Donut chart with center text**:
  - Large number: "$3,382.78"
  - Small text below: "per month"
- Chart shows breakdown by color (P&I, Tax, Insurance, HOA, PMI)

#### Detailed Payment Breakdown List
❌ **Missing: Itemized list with right-aligned amounts**
```
Principal & Interest       $2,594.39
Property Tax                 $416.67
Home Insurance               $166.67
HOA                          $125.00
PMI                           $80.05
─────────────────────────────────────
Total Monthly Payment      $3,382.78
```

#### Summary Boxes (Below Breakdown)
- ❌ "Home Value" (not "Home Price"!)
- ❌ "Base Mortgage Amount" (not "Loan Amount"!)
- Down Payment %
- Total Interest

---

## 📊 CALCULATOR #2: AFFORDABILITY
**Chart Type: HORIZONTAL BAR CHART (stacked) with payment breakdown**

**Completely Different Layout from Payment Calculator!**

### Input Section (Left)
- **Sliders for all inputs** (not text fields)
  - Gross Income (annual)
  - Monthly Debts
  - Home Price
  - Down Payment
  - Loan Term
  - Loan Amount
- ❌ **"Rental Income" checkbox** - adds rental income to qualifying

### Results Section (Right)

#### DTI Display (Prominent)
❌ **Two ratios shown**: "35.98%/46.45%"
- Front-end ratio / Back-end ratio

#### Qualification Message Box
❌ **Green box with checkmark:**
"Based on your income, you can afford a home up to $X with monthly payment of $Y"

#### Payment Chart
❌ **Horizontal bar chart** (NOT donut like Payment calculator)
- Shows payment breakdown as stacked horizontal bars

#### Loan Details Section
❌ **Itemized list showing:**
- Monthly Payment
- Down Payment Required
- Cash Needed at Closing
- Maximum Home Price
- Maximum Loan Amount

---

## 📊 CALCULATOR #3: BUYDOWN
**Chart Type: STACKED VERTICAL BAR CHART showing payment (dark blue) + savings overlay (green)**

### Input Section (Left)

#### Buydown Type Selection
❌ **Checkboxes for buydown types:**
- [ ] 3-2-1 Buydown
- [ ] 2-1 Buydown
- [ ] 1-1 Buydown
- [ ] 1-0 Buydown

#### Basic Loan Fields
- Loan Amount (slider + text with $)
- Rate (slider + text with %)
- Loan Term (dropdown)

#### Educational Content Section
❌ **"TYPES OF TEMPORARY BUYDOWN" section with:**
- "How Do Temporary Buydowns Work?" heading
- Explanation text
- List of each buydown type with description:
  - **3-2-1 Buydown:** Rate reduced 3% year 1, 2% year 2, 1% year 3
  - **2-1 Buydown:** Rate reduced 2% year 1, 1% year 2
  - **1-1 Buydown:** Rate reduced 1% in years 1 and 2
  - **1-0 Buydown:** Rate reduced 1% in year 1
- "What Happens After the Buydown Ends?" section

### Results Section (Right)

#### Top Result Card
❌ **"TOTAL BUYDOWN FEE FOR THIS LOAN IS"**
- Large amount: "$18,117.96"
- Message: "Please contact your loan officer to see if this program would be beneficial for you."

#### Stacked Bar Chart
❌ **"Estimated monthly payments for Buydown Period"**
- Shows 4 bars (Year 1, 2, 3, 4-30)
- Each bar split into:
  - Monthly payment (dark)
  - Monthly savings (green overlay)
- Labels show savings amount on each bar

#### Results Summary Table
❌ **Data table with columns:**
- Year
- Rate
- Full monthly payment
- Monthly payment with buydown
- Monthly savings
- Annual savings

Row for each period (1, 2, 3, 4-30)

---

## 📊 CALCULATOR #4: DSCR (Debt Service Coverage Ratio)
**Chart Type: COMPARISON VERTICAL BAR CHART (Minimum target vs Calculated)**

**For Investment Properties**

### Input Section (Left)

#### Basic Property Info
- Number of Units (dropdown: 1, 2, 3, 4)
- Property Value or Purchase Price (slider + text with $)
- Average Rent/Unit (slider + text with $)

#### Property Expenses
- Annual Property Taxes (slider + text with $)
- Annual Insurance (slider + text with $)
- Monthly HOA Fee (slider + text with $)
- Vacancy Rate (dropdown %)
- Annual Repairs and Maintenance (text with $)
- Annual Utilities (slider + text with $)

#### Loan Details
- Loan to Value (dropdown %)
- Interest Rate (dropdown %)
- Origination Fee (dropdown %)
- Closing Costs (slider + text with $)

### Results Section (Right)

#### Return Metrics Card (Dark Blue)
❌ **4 metric boxes in grid:**
```
CASH FLOW          CAP RATE
$33,792.24         19%

CASH ON CASH      DSCR RATE
RETURN
21.19%            2.46
```

#### Debt Service Insights
❌ **Green checkmark box:**
"Great! Your DSCR of 2.46 indicates strong cash flow. You should qualify for most DSCR loan programs."

#### DSCR Bar Chart
❌ **Comparison chart showing:**
- Minimum target: 1.00 (gray bar)
- Calculated: 2.46 (green bar)
- Y-axis from 0.0 to 3.0

#### Monthly Cash Flow Feedback
❌ **Green box with checkmark:**
"Positive Cash Flow: Your property generates $2,816.02/mo in positive cash flow - great for long-term investment."

#### Deal Breakdown Section
❌ **List with right-aligned amounts:**
- Loan Amount
- Down Payment
- Mortgage Payment
- Total Monthly Debt Service
- Origination Fee Amount

---

## 📊 CALCULATOR #5: REFINANCE
**Chart Type: NO CHART - Uses comparison TABLE with Current/New/Difference columns**

### Input Section (Left)

#### Refinance Goals
❌ **Checkbox options:**
- [ ] Low Monthly Payment
- [ ] Lower Interest Paid

#### Current Loan Section
- 1st Loan Amount (slider + text with $)
- 1st Loan Rate (slider + text with %)
- 1st Loan Term (text + dropdown year)
- 1st Loan Start date (date picker)
- ❌ **Expandable "Add Second Loan"** checkbox
- ❌ **Expandable "Credit Card Debt to Pay off"** checkbox

### Results Section (Right)

#### Alert Banner (Red if payment increases)
❌ **"Your monthly payment will increase $192.00 per month."**

#### Metric Cards (Dark Blue)
❌ **Two metrics side-by-side:**
```
MONTHLY PAYMENT        TOTAL INTEREST
INCREASE               DIFFERENCE
$192.00 ↗️             $162,840.00 ↘️
```

#### Refinance Comparison Table
❌ **3-column table:**
```
                Current      New          Difference
Loan Amount     $300,000     $261,000     $39,000 ↘️
Monthly Payment $1,610       $1,802       $192 ↗️
Loan Term       360 months   180 months   180 months ↘️
Interest Rate   5%           3%           2% ↘️
Total Interest  $226,275     $63,435      $162,840 ↘️
```
(↗️ = red up arrow for increases, ↘️ = green down arrow for decreases)

---

## 📊 CALCULATOR #6: NET PROCEEDS
**Chart Type: NO CHART - Just result card and itemized cost breakdown list**

### Input Section (Left)

#### Sale Information
- Expected Home Sale Price (slider + text with $)
- Remaining Mortgage Owed (slider + text with $)

#### Cost Fields (Show % and $ amount)
- Real Estate Agent Fees (% field + calculated $ amount shown)
  - Example: "% 6" → "$15,000.00"
- Closing Costs (% field + calculated $ amount)
- Seller Concessions (% field + calculated $ amount)

#### Fixed Cost Fields
- Home Prep and Repairs (slider + text with $)
- Moving and Seller Storage (slider + text with $)

### Results Section (Right)

#### Top Result Card (Dark Blue)
❌ **"ESTIMATED NET PROCEEDS"**
- Large amount: "$221,875.00"

#### Sub-metrics (Blue background)
❌ **Two amounts side-by-side:**
```
TOTAL AMOUNT           TOTAL COSTS
OWED AT CLOSING        TO SELL
$28,125.00             $28,125.00
```

#### Summary Message
❌ **Text explaining proceeds:**
"Based on this information, if your home sells for $250,000.00 you can expect to **receive around $221,875.00** after the payoff of your mortgage(s) and the costs to sell."

#### Total Costs to Sell Breakdown
❌ **Large header:** "Total Costs to Sell $28,125.00"

❌ **Itemized list:**
- Real Estate Agent Fees: $15,000.00
- Closing Costs: $3,125.00
- Seller Concessions: $5,000.00
- Home Prep and Repairs: $2,500.00
- Moving and Seller Storage: $2,500.00

---

## 📊 CALCULATOR #7: RENT VS BUY
**Chart Type: LINE CHART showing Net Costs Over Years (orange line = Rent, green line = Buy)**

**Most Complex Calculator**

### Input Section (Left)

#### Collapsible Sections (Accordions)
❌ **"Mortgage Information" (expanded by default)**
- Home Price (slider + text with $)
- Down Payment (slider + text with $ OR %)
- Loan Amount (slider + text with $)
- Interest Rate (slider + text with %)
- Loan Term (text + dropdown year)
- Start Date (date picker)
- PMI (Yearly) (slider + text with %)

❌ **"OPTIONAL" section**
- Home Insurance (text with $)
- Taxes (text with $ OR %)
- HOA Dues (text with $)

❌ **"Buying Assumptions" (collapsed, expandable)**
- Marginal Bracket (% field)
- Annual Costs (% field)
- Selling Costs (% field)
- Annual Appreciation (% field)

❌ **"Renting Assumptions" (collapsed, expandable)**
- Monthly Rent ($ field)
- Annual Rent Increase (% field)
- Renter's Insurance ($ field)
- Security Deposit ($ field)

### Results Section (Right)

#### Top Result Card
❌ **Dynamic recommendation:**
"After **3 years**, **buying** will be cheaper than renting."

#### Line Chart
❌ **"Net Costs Over Years"**
- X-axis: Years (5, 10, 15, 20, 25, 30)
- Y-axis: Costs ($100k, $200k, etc.)
- Two lines:
  - Orange line: Rent cost trajectory
  - Green line: Buy cost trajectory
- Lines cross at breakeven point

#### Results Summary
❌ **Green badge:** "BUYING becomes profitable in year 3"

#### Comparison Table
❌ **Two-column table:**
```
                    Buying           Renting
Cash Spent          $156,262.18      $74,404.44
Home Value          $546,363.50      --
Balance on Loan     $429,046.18      --
Closing Costs       $32,781.81       --
on Sale
Net Spent           $71,726.67       $74,404.44
```

#### Visual Comparison Bars
❌ **Two horizontal bars:**
- RENT (orange bar)
- BUY (green bar - shows it's better)

#### Net Spent Highlight
❌ **Shows year and amounts:**
```
Year    Buy            Rent
3       $71,726.67     $74,404.44
```

#### Buy Gain Display
❌ **Large green badge:** "BUY GAIN"
❌ **Large amount:** "$2,677.78"

#### Your Total Equity
❌ **Green badge:** "YOUR TOTAL EQUITY"
❌ **Large green amount:** "$117,317.32"

#### Cost Breakdown (Bottom)
❌ **Another detailed breakdown section** (partially visible)

---

## 🔍 SUMMARY OF MISSING FEATURES

### Priority 1: Critical UI Elements
1. ❌ Donut chart center text ("$3,382.78" + "per month")
2. ❌ Label changes: "Home Price" → "Home Value"
3. ❌ Label changes: "Loan Amount" → "Base Mortgage Amount"
4. ❌ Payment breakdown detailed list with right-aligned amounts
5. ❌ Collapsible Disclaimer section

### Priority 2: Critical Functionality
6. ❌ **Loan Type Tabs Functionality** - Changes fields, labels, calculations based on loan type
   - FHA: Shows "Upfront MIP", "Annual MIP", changes label to "Monthly MIP"
   - VA: Shows "Funding Fee", allows 0% down
   - USDA/Jumbo: Different minimums and calculations
7. ❌ "Email me this" button with modal
8. ❌ "Have a question?" button with contact form
9. ❌ "Share" button with share modal

### Priority 3: Calculator-Specific Features

**Affordability:**
- Slider-based inputs instead of text fields
- DTI ratio display (front-end/back-end)
- Rental income checkbox
- Horizontal bar chart
- Qualification message box
- Loan details section

**Buydown:**
- Buydown type checkboxes
- Educational content sections
- Stacked bar chart with savings overlay
- Results summary table
- Total buydown fee card

**DSCR:**
- Number of units dropdown
- Return metrics grid (4 boxes)
- DSCR insights with recommendations
- DSCR comparison bar chart
- Cash flow feedback box
- Deal breakdown section

**Refinance:**
- Refinance goals checkboxes
- Alert banner for payment increase/decrease
- Comparison table with arrows
- Expandable second loan section
- Credit card debt payoff section

**Net Proceeds:**
- Percentage fields that show calculated dollar amounts
- Estimated net proceeds card
- Summary message with proceeds explanation
- Total costs breakdown list

**Rent vs Buy:**
- Collapsible accordion sections
- Line chart showing cost trajectories over time
- Dynamic recommendation ("After X years, buying/renting is better")
- Breakeven point indicator
- Comparison table with buy vs rent columns
- Visual comparison bars
- Net spent highlight
- Buy gain display
- Total equity display
- Multiple cost breakdowns

---

## 📋 IMPLEMENTATION CHECKLIST

### Phase 1: Payment Calculator Enhancements
- [ ] Add center text to donut chart
- [ ] Change "Home Price" to "Home Value"
- [ ] Change "Loan Amount" to "Base Mortgage Amount"
- [ ] Add payment breakdown list below chart
- [ ] Make loan type tabs functional:
  - [ ] Conventional: standard PMI
  - [ ] FHA: Add Upfront MIP, Annual MIP fields, change label to "Monthly MIP"
  - [ ] VA: Add Funding Fee field, allow 0% down
  - [ ] USDA: Add guarantee fee
  - [ ] Jumbo: Higher minimums

### Phase 2: Universal Features
- [ ] Implement "Email me this" modal
- [ ] Implement "Have a question?" contact form
- [ ] Implement "Share" modal (social media + copy link)
- [ ] Add collapsible Disclaimer section

### Phase 3: Calculator-Specific Features
- [ ] Affordability: Sliders, DTI display, horizontal bar chart
- [ ] Buydown: Type selection, educational content, results table
- [ ] DSCR: Return metrics, insights, deal breakdown
- [ ] Refinance: Goals, comparison table with arrows, alert banner
- [ ] Net Proceeds: Cost breakdown with % calculations
- [ ] Rent vs Buy: Accordions, line chart, recommendation logic

---

**End of Analysis**
