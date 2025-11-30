# FRS Mortgage Calculator Widget

Standalone, embeddable mortgage calculator widget with lead capture and webhook integration.

## Features

- **Standalone Widget** - Fully self-contained, embeddable anywhere
- **Lead Capture** - Built-in form to collect contact information
- **Webhook Integration** - Send leads to any webhook endpoint
- **Email Notifications** - Send calculation results via email
- **Customizable Branding** - Custom colors, logos, and loan officer info
- **Responsive Design** - Works on desktop, tablet, and mobile
- **No Dependencies** - All dependencies bundled in single JS file

## Quick Start

### 1. Build the Widget

```bash
cd /Users/derintolu/Local\ Sites/hub21/app/public/wp-content/plugins/frs-lrg
npm run build:widget
```

This creates:
- `assets/widget/dist/frs-mortgage-calculator.js` - Widget JavaScript
- `assets/widget/dist/frs-mortgage-calculator.css` - Widget styles

### 2. Embed the Widget

Add to your HTML page:

```html
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="/path/to/frs-mortgage-calculator.css">
</head>
<body>
  <!-- Widget container -->
  <div id="mortgage-calculator"
       data-webhook-url="https://hooks.zapier.com/hooks/catch/123456/abcdef"
       data-email-api-url="/wp-json/frs-lrg/v1/send-calculation-email"
       data-brand-color="#3b82f6"
       data-loan-officer-name="John Doe"
       data-loan-officer-email="john@example.com"
       data-loan-officer-phone="(555) 123-4567"
       data-frs-mortgage-calculator
  ></div>

  <script src="/path/to/frs-mortgage-calculator.js"></script>
</body>
</html>
```

### 3. Or Initialize via JavaScript

```html
<div id="my-calculator"></div>

<script src="/path/to/frs-mortgage-calculator.js"></script>
<script>
  window.FRSMortgageCalculator.init('my-calculator', {
    webhookUrl: 'https://hooks.zapier.com/hooks/catch/123456/abcdef',
    emailEnabled: true,
    emailApiUrl: '/wp-json/frs-lrg/v1/send-calculation-email',
    brandColor: '#10b981',
    logoUrl: '/path/to/logo.png',
    loanOfficerName: 'Jane Smith',
    loanOfficerEmail: 'jane@example.com',
    loanOfficerPhone: '(555) 987-6543',
    showLeadForm: true
  });
</script>
```

## Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `webhookUrl` | string | undefined | URL to send lead data via POST |
| `emailEnabled` | boolean | true | Enable email notifications |
| `emailApiUrl` | string | undefined | REST API endpoint for email sending |
| `brandColor` | string | '#3b82f6' | Primary brand color (hex) |
| `logoUrl` | string | undefined | URL to company/LO logo |
| `loanOfficerName` | string | undefined | Loan officer's name |
| `loanOfficerEmail` | string | undefined | Loan officer's email |
| `loanOfficerPhone` | string | undefined | Loan officer's phone |
| `showLeadForm` | boolean | true | Show/hide lead capture form |
| `disclaimer` | string | (default) | Custom disclaimer text |

## Webhook Payload

When a lead submits the form, the widget sends this payload:

```json
{
  "lead": {
    "name": "John Smith",
    "email": "john@example.com",
    "phone": "(555) 123-4567"
  },
  "calculation": {
    "inputs": {
      "homePrice": 300000,
      "downPayment": 60000,
      "interestRate": 6.5,
      "loanTerm": 30,
      "propertyTax": 2000,
      "insurance": 1000,
      "hoa": 0,
      "propertyState": "California",
      "creditScore": "740-759"
    },
    "results": {
      "monthlyPayment": 1756.23,
      "principalAndInterest": 1516.90,
      "totalLoanAmount": 240000,
      "totalInterest": 306284
    }
  },
  "loanOfficer": {
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "(555) 987-6543"
  },
  "timestamp": "2025-01-15T10:30:00.000Z",
  "source": "mortgage-calculator-widget"
}
```

## Webhook Integration Examples

### Zapier

1. Create a new Zap with "Webhooks by Zapier" trigger
2. Choose "Catch Hook"
3. Copy the webhook URL
4. Use it in `data-webhook-url` attribute
5. Configure actions (add to CRM, send email, etc.)

### Make.com (Integromat)

1. Create a new scenario
2. Add "Webhooks" module as trigger
3. Copy the webhook URL
4. Use it in `data-webhook-url` attribute
5. Add modules to process the lead data

### Custom Webhook

```javascript
// Express.js example
app.post('/api/mortgage-leads', async (req, res) => {
  const { lead, calculation, loanOfficer, timestamp } = req.body;

  // Save to database
  await db.leads.insert({
    name: lead.name,
    email: lead.email,
    phone: lead.phone,
    monthly_payment: calculation.results.monthlyPayment,
    loan_amount: calculation.results.totalLoanAmount,
    loan_officer: loanOfficer.email,
    created_at: timestamp
  });

  // Send notification email
  await sendEmail({
    to: loanOfficer.email,
    subject: `New Lead: ${lead.name}`,
    body: `
      New mortgage calculator lead:
      Name: ${lead.name}
      Email: ${lead.email}
      Phone: ${lead.phone}
      Estimated Payment: $${calculation.results.monthlyPayment.toFixed(2)}/mo
    `
  });

  res.json({ success: true });
});
```

## Email Notification API

Create a WordPress REST API endpoint to send emails:

```php
// In your plugin or theme
add_action('rest_api_init', function() {
  register_rest_route('frs-lrg/v1', '/send-calculation-email', [
    'methods' => 'POST',
    'callback' => 'frs_send_calculation_email',
    'permission_callback' => '__return_true'
  ]);
});

function frs_send_calculation_email($request) {
  $data = $request->get_json_params();
  $lead = $data['lead'];
  $calc = $data['calculation'];
  $lo = $data['loanOfficer'];

  // Email to lead
  $to = $lead['email'];
  $subject = 'Your Mortgage Calculation Results';
  $message = "
    Hi {$lead['name']},

    Thank you for using our mortgage calculator! Here are your results:

    Estimated Monthly Payment: $" . number_format($calc['results']['monthlyPayment'], 2) . "
    Home Price: $" . number_format($calc['inputs']['homePrice'], 0) . "
    Down Payment: $" . number_format($calc['inputs']['downPayment'], 0) . "
    Loan Amount: $" . number_format($calc['results']['totalLoanAmount'], 0) . "
    Interest Rate: {$calc['inputs']['interestRate']}%

    Your loan officer: {$lo['name']}
    Email: {$lo['email']}
    Phone: {$lo['phone']}

    Ready to get pre-approved? Reply to this email!
  ";

  $headers = [
    'From: ' . $lo['name'] . ' <' . $lo['email'] . '>',
    'Reply-To: ' . $lo['email']
  ];

  wp_mail($to, $subject, $message, $headers);

  // Email to loan officer
  $lo_message = "
    New lead from mortgage calculator:

    Name: {$lead['name']}
    Email: {$lead['email']}
    Phone: {$lead['phone']}

    Estimated Payment: $" . number_format($calc['results']['monthlyPayment'], 2) . "
    Loan Amount: $" . number_format($calc['results']['totalLoanAmount'], 0) . "
  ";

  wp_mail($lo['email'], "New Lead: {$lead['name']}", $lo_message);

  return ['success' => true];
}
```

## Usage in WordPress

### Shortcode

Create a shortcode to embed the widget:

```php
add_shortcode('frs_mortgage_calculator', function($atts) {
  $atts = shortcode_atts([
    'webhook_url' => '',
    'email_api_url' => home_url('/wp-json/frs-lrg/v1/send-calculation-email'),
    'brand_color' => '#3b82f6',
    'logo_url' => '',
    'loan_officer_name' => '',
    'loan_officer_email' => '',
    'loan_officer_phone' => '',
    'show_lead_form' => 'true'
  ], $atts);

  wp_enqueue_style('frs-mortgage-calculator',
    plugins_url('assets/widget/dist/frs-mortgage-calculator.css', __FILE__)
  );
  wp_enqueue_script('frs-mortgage-calculator',
    plugins_url('assets/widget/dist/frs-mortgage-calculator.js', __FILE__),
    [],
    null,
    true
  );

  return sprintf(
    '<div id="frs-mortgage-calculator-%s"
          data-webhook-url="%s"
          data-email-api-url="%s"
          data-brand-color="%s"
          data-logo-url="%s"
          data-loan-officer-name="%s"
          data-loan-officer-email="%s"
          data-loan-officer-phone="%s"
          data-show-lead-form="%s"
          data-frs-mortgage-calculator
     ></div>',
    uniqid(),
    esc_attr($atts['webhook_url']),
    esc_attr($atts['email_api_url']),
    esc_attr($atts['brand_color']),
    esc_url($atts['logo_url']),
    esc_attr($atts['loan_officer_name']),
    esc_attr($atts['loan_officer_email']),
    esc_attr($atts['loan_officer_phone']),
    esc_attr($atts['show_lead_form'])
  );
});
```

Usage:
```
[frs_mortgage_calculator
  webhook_url="https://hooks.zapier.com/hooks/catch/123/abc"
  loan_officer_name="John Doe"
  loan_officer_email="john@example.com"
  brand_color="#10b981"
]
```

### Gutenberg Block

Register a Gutenberg block:

```javascript
// blocks/mortgage-calculator/index.js
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

registerBlockType('frs-lrg/mortgage-calculator', {
  title: 'Mortgage Calculator',
  icon: 'calculator',
  category: 'widgets',

  attributes: {
    webhookUrl: { type: 'string' },
    brandColor: { type: 'string', default: '#3b82f6' },
    loanOfficerName: { type: 'string' },
    loanOfficerEmail: { type: 'string' },
    showLeadForm: { type: 'boolean', default: true }
  },

  edit: ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();

    return (
      <>
        <InspectorControls>
          <PanelBody title="Configuration">
            <TextControl
              label="Webhook URL"
              value={attributes.webhookUrl}
              onChange={(webhookUrl) => setAttributes({ webhookUrl })}
            />
            <TextControl
              label="Brand Color"
              value={attributes.brandColor}
              onChange={(brandColor) => setAttributes({ brandColor })}
            />
            <TextControl
              label="Loan Officer Name"
              value={attributes.loanOfficerName}
              onChange={(loanOfficerName) => setAttributes({ loanOfficerName })}
            />
            <TextControl
              label="Loan Officer Email"
              value={attributes.loanOfficerEmail}
              onChange={(loanOfficerEmail) => setAttributes({ loanOfficerEmail })}
            />
            <ToggleControl
              label="Show Lead Form"
              checked={attributes.showLeadForm}
              onChange={(showLeadForm) => setAttributes({ showLeadForm })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          <div style={{ padding: '20px', border: '2px dashed #ccc', textAlign: 'center' }}>
            <strong>Mortgage Calculator Widget</strong>
            <p>Configure in sidebar →</p>
          </div>
        </div>
      </>
    );
  },

  save: ({ attributes }) => {
    return (
      <div
        data-frs-mortgage-calculator
        data-webhook-url={attributes.webhookUrl}
        data-brand-color={attributes.brandColor}
        data-loan-officer-name={attributes.loanOfficerName}
        data-loan-officer-email={attributes.loanOfficerEmail}
        data-show-lead-form={attributes.showLeadForm}
      />
    );
  }
});
```

## Development

### Run Dev Server

```bash
npm run dev:widget
```

Widget will be available at `http://localhost:5182`

### Build for Production

```bash
npm run build:widget
```

### File Structure

```
frs-lrg/
├── src/widget/
│   ├── MortgageCalculatorWidget.tsx  # Main widget component
│   └── widget.tsx                    # Entry point & initialization
├── vite.widget.config.js             # Vite build config
└── assets/widget/dist/               # Built files
    ├── frs-mortgage-calculator.js
    ├── frs-mortgage-calculator.css
    └── frs-mortgage-calculator.js.map
```

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari 14+, Chrome Android)

## Troubleshooting

### Widget Not Loading

1. Check browser console for errors
2. Ensure CSS and JS files are loaded
3. Verify container element exists before script loads

### Webhook Not Firing

1. Check browser network tab for failed requests
2. Verify webhook URL is correct
3. Check CORS settings on webhook endpoint
4. Check webhook endpoint logs

### Emails Not Sending

1. Verify `emailApiUrl` is correct
2. Check REST API endpoint is registered
3. Test with WP Mail SMTP plugin
4. Check email spam folder

### Styling Issues

1. Ensure CSS file is loaded before widget initializes
2. Check for CSS conflicts with parent page
3. Use browser DevTools to inspect styles
4. Widget uses Tailwind CSS classes

## License

Part of the FRS plugin suite.
