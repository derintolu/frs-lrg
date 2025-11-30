# 🎨 Mortgage Calculator - Realtor Partner Custom Branding Guide

## Overview

The Mortgage Calculator Widget now supports **custom branding** for realtor partners! You can customize the gradient colors, borders, and brand colors to match your realtor partner's brand identity.

## Quick Start

Add custom branding by setting data attributes on the mortgage calculator container:

```html
<div id="mortgage-calculator"
     data-loan-officer-id="123"
     data-webhook-url="https://your-site.com/webhook"
     data-gradient-start="#ff6b6b"
     data-gradient-end="#feca57">
</div>
```

## Available Branding Options

### Gradient Colors

| Attribute | Description | Example | Default |
|-----------|-------------|---------|---------|
| `data-gradient-start` | Starting color of gradient | `#ff6b6b` | `#2563eb` (blue) |
| `data-gradient-end` | Ending color of gradient | `#feca57` | `#2dd4da` (teal) |

### Additional Branding

| Attribute | Description | Example | Default |
|-----------|-------------|---------|---------|
| `data-brand-color` | Primary button color | `#ff6b6b` | `#3b82f6` |
| `data-border-color` | Border color override | `#feca57` | - |
| `data-logo-url` | Custom logo URL | `https://...` | - |

## Where Gradients Are Applied

The custom gradient colors will be automatically applied to:

✅ **Tab Active States** - Active calculator tab highlighting
✅ **Loan Officer Profile Avatar** - Border around profile image
✅ **Loan Officer Name** - Gradient text effect on name
✅ **NMLS Number** - Gradient text effect on license number
✅ **Page Header Icon** - Calculator icon background
✅ **All Branded UI Elements** - Consistent branding throughout

## Recommended Color Combinations

### Professional & Corporate

```html
<!-- Classic Blue -->
data-gradient-start="#3742fa"
data-gradient-end="#5352ed"

<!-- Executive Navy -->
data-gradient-start="#1e3799"
data-gradient-end="#4a69bd"

<!-- Corporate Teal -->
data-gradient-start="#0984e3"
data-gradient-end="#74b9ff"
```

### Warm & Inviting

```html
<!-- Sunset Orange -->
data-gradient-start="#ff7675"
data-gradient-end="#fdcb6e"

<!-- Warm Red/Orange -->
data-gradient-start="#ff6b6b"
data-gradient-end="#feca57"

<!-- Golden Hour -->
data-gradient-start="#ee5a24"
data-gradient-end="#f79f1f"
```

### Modern & Fresh

```html
<!-- Nature Green -->
data-gradient-start="#00b894"
data-gradient-end="#55efc4"

<!-- Mint Fresh -->
data-gradient-start="#00d2d3"
data-gradient-end="#1dd1a1"

<!-- Ocean Breeze -->
data-gradient-start="#00b894"
data-gradient-end="#6c5ce7"
```

### Premium & Elegant

```html
<!-- Royal Purple -->
data-gradient-start="#6c5ce7"
data-gradient-end="#a29bfe"

<!-- Deep Purple/Blue -->
data-gradient-start="#5f27cd"
data-gradient-end="#48dbfb"

<!-- Luxury Gold -->
data-gradient-start="#f39c12"
data-gradient-end="#e74c3c"
```

## Complete Example

```html
<!DOCTYPE html>
<html>
<head>
    <title>Mortgage Calculator - Custom Branding</title>
</head>
<body>
    <!-- Realtor Partner #1: Warm Red/Orange Theme -->
    <div id="mortgage-calculator"
         data-loan-officer-id="123"
         data-webhook-url="https://your-site.com/webhook"
         data-gradient-start="#ff6b6b"
         data-gradient-end="#feca57"
         data-brand-color="#ff6b6b"
         data-logo-url="https://your-site.com/logo.png">
    </div>

    <!-- Load the widget script -->
    <script src="/wp-content/plugins/frs-lrg/assets/widget/dist/assets/widget-[hash].js"></script>
</body>
</html>
```

## WordPress Shortcode Usage

If using the WordPress shortcode:

```php
[mortgage_calculator
    loan_officer_id="123"
    webhook_url="https://your-site.com/webhook"
    gradient_start="#ff6b6b"
    gradient_end="#feca57"
    brand_color="#ff6b6b"
    logo_url="https://your-site.com/logo.png"]
```

## Technical Implementation

### How It Works

1. The widget reads `data-*` attributes from the container element
2. Custom gradient colors are passed to all branded components
3. CSS custom properties are dynamically set for gradient application
4. All UI elements automatically inherit the custom branding

### Files Modified

- `src/widget/MortgageCalculatorWidget.tsx` - Main widget component
- `src/widget/components/BrandedTabs.tsx` - Custom tabs with gradient support
- `src/widget/widget.tsx` - Configuration reading
- `includes/WidgetConfig.php` - Interface definition (if applicable)

### CSS Variables

The widget sets the following CSS custom properties:

```css
--brand-primary-blue: [gradientStart]
--brand-rich-teal: [gradientEnd]
```

These are used throughout the component styling via:

```css
background: linear-gradient(135deg, var(--brand-primary-blue) 0%, var(--brand-rich-teal) 100%);
```

## Testing Custom Branding

### Browser Console Test

Open the browser console and test gradient changes:

```javascript
const container = document.getElementById('mortgage-calculator');
container.dataset.gradientStart = '#ff6b6b';
container.dataset.gradientEnd = '#feca57';
location.reload(); // Reload to apply
```

### Multiple Instances

You can have multiple calculator instances with different branding on the same page:

```html
<!-- Default Branding -->
<div id="calculator-default"></div>

<!-- Custom Branding #1 -->
<div id="calculator-realtor1"
     data-gradient-start="#ff6b6b"
     data-gradient-end="#feca57"></div>

<!-- Custom Branding #2 -->
<div id="calculator-realtor2"
     data-gradient-start="#5f27cd"
     data-gradient-end="#48dbfb"></div>
```

## Best Practices

### Color Selection

1. **Contrast**: Ensure gradient colors have sufficient contrast for readability
2. **Brand Consistency**: Use colors from the realtor's brand guidelines
3. **Accessibility**: Test gradients meet WCAG AA standards for text contrast
4. **Testing**: Preview on both light and dark backgrounds

### Gradient Direction

All gradients use a consistent 135-degree angle (diagonal top-left to bottom-right):

```css
linear-gradient(135deg, [start] 0%, [end] 100%)
```

### Color Format

- Use **6-digit hex codes** (e.g., `#ff6b6b`)
- Include the `#` symbol
- Avoid RGB, HSL, or color names

## Troubleshooting

### Gradients Not Showing

1. **Check attribute format**: Ensure `data-gradient-start` and `data-gradient-end` are set
2. **Verify hex codes**: Must be valid 6-digit hex (e.g., `#ff6b6b`)
3. **Clear cache**: Hard refresh the page (Cmd+Shift+R or Ctrl+Shift+R)
4. **Console errors**: Check browser console for JavaScript errors

### Gradients Look Wrong

1. **Color order**: Swap `gradientStart` and `gradientEnd` values
2. **Contrast**: Adjust colors for better visual hierarchy
3. **Test on devices**: Check appearance on mobile, tablet, desktop

## Demo File

See `REALTOR-BRANDING-DEMO.html` for a complete interactive demonstration with multiple branding examples.

## Support

For questions or issues with custom branding:

1. Check this README first
2. Review the demo file for examples
3. Test in browser console to isolate issues
4. Contact plugin support with specific details

## Changelog

### v1.0.0 - 2025-01-30
- ✨ Initial release of custom branding feature
- ✨ Support for gradient start/end colors
- ✨ CSS custom properties integration
- ✨ BrandedTabs component
- 📝 Complete documentation and examples
