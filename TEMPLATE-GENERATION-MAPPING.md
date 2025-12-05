# Mortgage Calculator Landing Page - Dynamic Template Generation

## Overview

This document maps the hardcoded template data from post ID 69888 to dynamic user profile fields for automated landing page generation.

**Template Source:** Post ID 69888 (Blake Anthony Corkill example)
**User Data Source:** `wp_frs_profiles` table (FRS User Profiles plugin)
**Generation Goal:** Create personalized mortgage calculator landing pages for each loan officer

---

## Data Mapping

### 1. Profile Image

**Template Code:**
```html
<!-- wp:greenshift-blocks/image -->
<img src="https://beta.frs.works/wp-content/uploads/2025/08/1217_1755383024174.jpg"
     width="500" height="140"/>
<!-- /wp:greenshift-blocks/image -->
```

**Block Attributes:**
- `"mediaurl": "https://beta.frs.works/wp-content/uploads/2025/08/1217_1755383024174.jpg"`
- `"mediaid": 68911`
- `"originalWidth": 500`
- `"originalHeight": 500`

**Database Field:** `headshot_id` (BIGINT - WordPress media attachment ID)

**Replacement Logic:**
```php
$profile = Profile::find($profile_id);
$image_id = $profile->headshot_id;
$image_url = wp_get_attachment_url($image_id);
$image_meta = wp_get_attachment_metadata($image_id);
$original_width = $image_meta['width'];
$original_height = $image_meta['height'];
```

**Find/Replace:**
- `68911` → `{$image_id}`
- `https://beta.frs.works/wp-content/uploads/2025/08/1217_1755383024174.jpg` → `{$image_url}`
- Original width/height values → from image metadata

---

### 2. Full Name

**Template Instances:** 2 occurrences

**Instance 1: Card Heading**
```html
<h3 id="blake-anthony-corkill">Blake Anthony Corkill</h3>
```

**Instance 2: CTA Button**
```html
<a href="tel:‭8587221558‬">Contact Blake Today</a>
```

**Database Fields:**
- `first_name` (VARCHAR 255)
- `last_name` (VARCHAR 255)
- `display_name` (VARCHAR 255) - fallback

**Replacement Logic:**
```php
$full_name = trim($profile->first_name . ' ' . $profile->last_name);
if (empty($full_name)) {
    $full_name = $profile->display_name;
}

$first_name = $profile->first_name ?: explode(' ', $profile->display_name)[0];
```

**Find/Replace:**
- `Blake Anthony Corkill` → `{$full_name}`
- `Contact Blake Today` → `Contact {$first_name} Today`
- Heading ID: `blake-anthony-corkill` → `{sanitize_title($full_name)}`

---

### 3. Job Title

**Template Code:**
```html
<p class="has-text-align-center mb-1 has-text-color">Loan Originator</p>
```

**Database Field:** `job_title` (VARCHAR 255)

**Replacement Logic:**
```php
$job_title = $profile->job_title ?: 'Loan Originator'; // Default fallback
```

**Find/Replace:**
- `Loan Originator` → `{$job_title}`

---

### 4. NMLS Number

**Template Code:**
```html
<p class="has-text-align-center mb-4 has-text-color">NMLS #1570245</p>
```

**Database Fields:**
- `nmls` (VARCHAR 50)
- `nmls_number` (VARCHAR 50) - alternative field

**Replacement Logic:**
```php
$nmls = $profile->nmls ?: $profile->nmls_number;
// Ensure # prefix exists
$nmls_display = (strpos($nmls, '#') === 0) ? $nmls : '#' . $nmls;
```

**Find/Replace:**
- `NMLS #1570245` → `NMLS {$nmls_display}`

---

### 5. Phone Number

**Template Instances:** 2 occurrences

**Instance 1: Card Link (formatted)**
```html
<a href="tel:‭(858) 722-1558‬">(858) 722-1558‬</a>
```

**Instance 2: CTA Button href (digits only)**
```html
<a href="tel:‭8587221558‬">Contact Blake Today</a>
```

**Database Fields:**
- `phone_number` (VARCHAR 50)
- `mobile_number` (VARCHAR 50) - fallback

**Replacement Logic:**
```php
$phone = $profile->phone_number ?: $profile->mobile_number;

// Format for display: (XXX) XXX-XXXX
$phone_formatted = preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', preg_replace('/\D/', '', $phone));

// Strip for tel: link
$phone_digits = preg_replace('/\D/', '', $phone);
```

**Find/Replace:**
- Card: `(858) 722-1558` → `{$phone_formatted}`
- Card href: `tel:‭(858) 722-1558‬` → `tel:{$phone_digits}`
- Button href: `tel:‭8587221558‬` → `tel:{$phone_digits}`

---

## Static Elements (No Replacement Needed)

These remain the same for all loan officers:

1. **Hero Background Image:** House with wreath (`wp-image-69029`)
2. **Heading:** "Calculate Your Monthly Payment"
3. **Description:** "Get instant estimates on your mortgage payment..."
4. **Shortcode:** `[frs_mortgage_calculator show_lead_form="true"]`
5. **CTA Heading:** "Ready to Get Pre-Qualified?"
6. **CTA Description:** "Take the next step toward homeownership..."

---

## Block Structure Overview

```
wp:group (Main Container)
├── wp:cover (Hero Section with house background)
│   ├── wp:group (Hero Content)
│   │   └── wp:columns (Two columns)
│   │       ├── wp:column (Left - Heading & Description)
│   │       │   ├── wp:heading "Calculate Your Monthly Payment"
│   │       │   └── wp:paragraph (Description)
│   │       └── wp:column (Right - User Info Card)
│   │           └── wp:group (Card with background)
│   │               ├── wp:greenshift-blocks/image [REPLACE: image]
│   │               ├── wp:heading level=3 [REPLACE: full name]
│   │               ├── wp:paragraph [REPLACE: job title]
│   │               ├── wp:paragraph [REPLACE: NMLS]
│   │               └── wp:paragraph [REPLACE: phone]
├── wp:shortcode [frs_mortgage_calculator]
└── wp:group (CTA Section)
    ├── wp:heading "Ready to Get Pre-Qualified?"
    ├── wp:paragraph (Description)
    └── wp:buttons
        └── wp:button [REPLACE: button text with first name, href with phone]
```

---

## Implementation Approach

### Option 1: String Find/Replace
**Pros:** Simple, fast
**Cons:** Fragile if template structure changes

```php
function generate_mortgage_lp_for_profile($profile_id) {
    $profile = Profile::find($profile_id);
    $template = get_post_field('post_content', 69888);

    // Get image data
    $image_id = $profile->headshot_id;
    $image_url = wp_get_attachment_url($image_id);

    // Get name data
    $full_name = trim($profile->first_name . ' ' . $profile->last_name);
    $first_name = $profile->first_name;

    // Get professional data
    $job_title = $profile->job_title ?: 'Loan Originator';
    $nmls = $profile->nmls ?: $profile->nmls_number;
    $nmls_display = (strpos($nmls, '#') === 0) ? $nmls : '#' . $nmls;

    // Get phone data
    $phone = $profile->phone_number ?: $profile->mobile_number;
    $phone_formatted = preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', preg_replace('/\D/', '', $phone));
    $phone_digits = preg_replace('/\D/', '', $phone);

    // Perform replacements
    $replacements = [
        // Image
        '"mediaid":68911' => '"mediaid":' . $image_id,
        'https://beta.frs.works/wp-content/uploads/2025/08/1217_1755383024174.jpg' => $image_url,

        // Name
        'Blake Anthony Corkill' => $full_name,
        'blake-anthony-corkill' => sanitize_title($full_name),
        'Contact Blake Today' => 'Contact ' . $first_name . ' Today',

        // Professional
        'Loan Originator' => $job_title,
        'NMLS #1570245' => 'NMLS ' . $nmls_display,

        // Phone
        '(858) 722-1558' => $phone_formatted,
        '8587221558' => $phone_digits,
    ];

    $content = strtr($template, $replacements);

    // Create new post
    $post_id = wp_insert_post([
        'post_type' => 'frs_mortgage_lp',
        'post_title' => $full_name . ' - Mortgage Calculator',
        'post_content' => $content,
        'post_status' => 'draft',
        'post_author' => $profile->user_id ?: 1,
    ]);

    return $post_id;
}
```

### Option 2: Parse Blocks Array
**Pros:** More robust, handles block structure changes
**Cons:** More complex, slower

```php
function generate_mortgage_lp_for_profile($profile_id) {
    $profile = Profile::find($profile_id);
    $template = get_post_field('post_content', 69888);

    // Parse blocks
    $blocks = parse_blocks($template);

    // Traverse and modify specific blocks
    // ... recursive block traversal logic ...

    // Serialize back to HTML
    $content = serialize_blocks($blocks);

    // Create post
    // ...
}
```

**Recommended:** Start with **Option 1** (string replacement) for speed and simplicity. The template structure is stable and controlled.

---

## Database Query Example

```php
// Get all loan officers who need landing pages
$loan_officers = Profile::ofType('loan_officer')
    ->active()
    ->whereNotNull('headshot_id')
    ->whereNotNull('phone_number')
    ->get();

foreach ($loan_officers as $profile) {
    $post_id = generate_mortgage_lp_for_profile($profile->id);
    error_log("Created mortgage LP {$post_id} for {$profile->display_name}");
}
```

---

## Validation Rules

Before generating, validate profile has required data:

```php
function validate_profile_for_lp($profile) {
    $errors = [];

    if (!$profile->headshot_id) {
        $errors[] = 'Missing profile photo';
    }

    if (!$profile->first_name && !$profile->display_name) {
        $errors[] = 'Missing name';
    }

    if (!$profile->phone_number && !$profile->mobile_number) {
        $errors[] = 'Missing phone number';
    }

    if (!$profile->nmls && !$profile->nmls_number) {
        $errors[] = 'Missing NMLS number';
    }

    return $errors;
}
```

---

## Next Steps

1. **Create generation function** in `includes/Controllers/LandingPages/Generator.php`
2. **Add REST API endpoint** `/wp-json/frs-lrg/v1/landing-pages/generate-mortgage-lp`
3. **Add UI in React portal** - "Generate Mortgage Calculator Page" button
4. **Handle edge cases:**
   - Missing headshot → use default placeholder
   - Missing phone → hide phone section
   - Missing NMLS → hide NMLS line (for non-LOs)
5. **Add post meta** to link generated page back to profile ID
6. **Consider variations** - Different templates for different specialties?

---

## Questions to Address

1. Should we auto-publish or leave as draft?
2. What if profile already has a mortgage calculator page?
3. Should we update existing pages if profile data changes?
4. Do we need different templates for different `select_person_type`?
5. Should the shortcode include the loan officer ID parameter?
