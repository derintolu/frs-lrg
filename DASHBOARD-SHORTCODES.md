# Dashboard Section Shortcodes

This document lists all the new shortcodes created for individual dashboard sections and pages.

## Dashboard Section Cards

These shortcodes render individual feature cards from the Marketing Hub dashboard:

### `[lrh_booking_calendar_card]`
Renders the Booking Calendar card (large 2-column card)
- **Navigates to:** `/marketing/calendar`
- **Mount ID:** `lrh-booking-calendar-card-root`
- **Data Attribute:** `data-lrh-card="booking-calendar"`

### `[lrh_landing_pages_card]`
Renders the Landing Pages card (small 1-column card)
- **Navigates to:** `/marketing/landing-pages`
- **Mount ID:** `lrh-landing-pages-card-root`
- **Data Attribute:** `data-lrh-card="landing-pages"`

### `[lrh_brand_guide_card]`
Renders the Brand Guide card (small 1-column card)
- **Navigates to:** `/marketing/brand-guide`
- **Mount ID:** `lrh-brand-guide-card-root`
- **Data Attribute:** `data-lrh-card="brand-guide"`

### `[lrh_print_social_media_card]`
Renders the Print & Social Media card (large 2-column card)
- **Navigates to:** `/marketing/orders`
- **Mount ID:** `lrh-print-social-media-card-root`
- **Data Attribute:** `data-lrh-card="print-social-media"`

---

## Full Page Shortcodes

These shortcodes render complete pages from the portal routes:

### `[lrh_marketing_overview]`
Renders the Marketing Hub dashboard page
- **Route:** `/`
- **Component:** `MarketingOverview`
- **Mount ID:** `lrh-marketing-overview-root`
- **Data Attribute:** `data-lrh-page="marketing-overview"`

### `[lrh_my_profile]`
Renders the My Profile page
- **Route:** `/profile`
- **Component:** `MyProfile`
- **Mount ID:** `lrh-my-profile-root`
- **Data Attribute:** `data-lrh-page="my-profile"`

### `[lrh_lead_tracking]`
Renders the Lead Tracking page
- **Route:** `/leads`
- **Component:** `LeadTracking`
- **Mount ID:** `lrh-lead-tracking-root`
- **Data Attribute:** `data-lrh-page="lead-tracking"`

### `[lrh_fluent_booking_calendar]`
Renders the full Booking Calendar page
- **Route:** `/marketing/calendar`
- **Component:** `FluentBookingCalendar`
- **Mount ID:** `lrh-fluent-booking-calendar-root`
- **Data Attribute:** `data-lrh-page="fluent-booking-calendar"`

### `[lrh_landing_pages]`
Renders the Landing Pages management page
- **Route:** `/marketing/landing-pages`
- **Component:** `LandingPagesMarketing`
- **Mount ID:** `lrh-landing-pages-page-root`
- **Data Attribute:** `data-lrh-page="landing-pages"`

### `[lrh_email_campaigns]`
Renders the Email Campaigns page
- **Route:** `/marketing/email-campaigns`
- **Component:** `EmailCampaignsMarketing`
- **Mount ID:** `lrh-email-campaigns-root`
- **Data Attribute:** `data-lrh-page="email-campaigns"`

### `[lrh_local_seo]`
Renders the Local SEO page
- **Route:** `/marketing/local-seo`
- **Component:** `LocalSEOMarketing`
- **Mount ID:** `lrh-local-seo-root`
- **Data Attribute:** `data-lrh-page="local-seo"`

### `[lrh_brand_showcase]`
Renders the Brand Guide/Showcase page
- **Route:** `/marketing/brand-guide`
- **Component:** `BrandShowcase`
- **Mount ID:** `lrh-brand-showcase-root`
- **Data Attribute:** `data-lrh-page="brand-showcase"`

### `[lrh_marketing_orders]`
Renders the Marketing Orders page
- **Route:** `/marketing/orders`
- **Component:** `MarketingOrders`
- **Mount ID:** `lrh-marketing-orders-root`
- **Data Attribute:** `data-lrh-page="marketing-orders"`

### `[lrh_mortgage_calculator_page]`
Renders the Mortgage Calculator page
- **Route:** `/tools/mortgage-calculator`
- **Component:** `MortgageCalculator`
- **Mount ID:** `lrh-mortgage-calculator-page-root`
- **Data Attribute:** `data-lrh-page="mortgage-calculator"`

### `[lrh_property_valuation]`
Renders the Property Valuation page
- **Route:** `/tools/property-valuation`
- **Component:** `PropertyValuation`
- **Mount ID:** `lrh-property-valuation-root`
- **Data Attribute:** `data-lrh-page="property-valuation"`

### `[lrh_settings]`
Renders the Settings page
- **Route:** `/profile/settings`
- **Component:** `Settings`
- **Mount ID:** `lrh-settings-root`
- **Data Attribute:** `data-lrh-page="settings"`

---

## Implementation Status

### ✅ PHP Shortcode Registration
- All shortcodes registered in `includes/Core/Shortcode.php`
- Each shortcode outputs a div with unique ID and data attribute
- Assets enqueued via `Frontend::enqueue_portal_assets_public()` or `Frontend::enqueue_dashboard_cards_assets()`

### ⏳ React Component Integration (TODO)
Need to create React entry points that:
1. Detect the data attributes (`data-lrh-card` and `data-lrh-page`)
2. Mount the appropriate React component to each div
3. Handle component-specific configuration and props

### Example Implementation Pattern

For dashboard cards:
```typescript
// New file: src/frontend/dashboard-cards/main.tsx
const cardRoots = document.querySelectorAll('[data-lrh-card]');
cardRoots.forEach(root => {
  const cardType = root.getAttribute('data-lrh-card');
  const component = getCardComponent(cardType);
  createRoot(root).render(component);
});
```

For individual pages:
```typescript
// New file: src/frontend/pages/main.tsx
const pageRoots = document.querySelectorAll('[data-lrh-page]');
pageRoots.forEach(root => {
  const pageType = root.getAttribute('data-lrh-page');
  const component = getPageComponent(pageType);
  createRoot(root).render(component);
});
```

---

## Usage Examples

### Use in WordPress Pages
```
[lrh_booking_calendar_card]
```

### Use in Custom Templates
```php
<?php echo do_shortcode('[lrh_my_profile]'); ?>
```

### Use in Widgets (Block Editor)
Add a Shortcode block and enter the shortcode.

---

## Notes

- All shortcodes require the user to be logged in (inherited from asset enqueue logic)
- Card shortcodes are intended for dashboard composition
- Page shortcodes render full-page components without sidebar
- All components will need proper data fetching and error handling
