# Landing Page Migration Plan

**Task:** Migrate personal landing page system from frs-partnership-portal to frs-lrg and ensure Fluent Forms integration

---

## Current State Analysis

### frs-partnership-portal (SOURCE)

#### PHP Files to Migrate

**1. `/includes/class-frs-landing-pages.php` (698 lines)**

**Purpose:** Complete landing page generation and editor system

**Key Features:**
- **Frontend Editor** - Iframe wrapper for WordPress block editor with custom UI
- **Block Filtering** - Limits allowed blocks per post type (biolink, prequal, openhouse)
- **Editor Locking** - Simplifies UI, disables inserter for content-only editing
- **Permission System** - Role-based editing (loan officers edit their pages, realtors edit partnerships)
- **Page Generation:**
  - `generate_lo_landing_page($user_id)` - Creates personal landing page for loan officer
  - `generate_cobranded_prequal_page($partnership)` - Creates co-branded prequal page
- **Content Builders:**
  - `build_personal_landing_content($user_id)` - Serialized blocks for personal page
  - `build_cobranded_prequal_content($lo_id, $agent_id)` - Serialized blocks for prequal

**Fluent Forms Integration:**
```php
// Line 676 - Already using Fluent Forms!
$form_url = home_url('/?fluent-form=3');

// Line 692 - Embedded in prequal-form block
'<!-- wp:frs/prequal-form {"formUrl":' . wp_json_encode($form_url) . '} /-->';
```

**Frontend Editor URL Pattern:**
```
?frs_lp_editor=1&page_id=123
```

**Methods:**
- `filter_allowed_blocks($allowed_blocks, $context)` - Block allowlist per post type
- `lock_editor_ui($settings, $context)` - Disable inserter, enforce content-only editing
- `maybe_render_frontend_editor()` - Render iframe editor wrapper
- `customize_admin_for_frontend_editor()` - Customize admin UI for loan officers/realtors
- `generate_lo_landing_page($user_id)` - Auto-generate personal landing page
- `generate_cobranded_prequal_page($partnership)` - Auto-generate co-branded prequal
- `build_personal_landing_content($user_id)` - Build block content for personal page
- `build_cobranded_prequal_content($lo_id, $agent_id)` - Build block content for prequal

---

**2. `/includes/class-frs-mortgage-landing-generator.php` (150+ lines)**

**Purpose:** Auto-generates mortgage landing pages (loan application & rate quote)

**Key Features:**
- **Two Page Types:**
  - `loan-app` - Loan application landing page
  - `rate-quote` - Rate quote landing page
- **WordPress Interactivity API** - Uses script modules
- **Custom Template** - Loads `single-frs_mortgage_lp.php`
- **Auto-Generation:** `generate_pages_for_user($user_id)` - Creates both pages for LO

**Methods:**
- `register_script_module()` - Register Interactivity API script
- `load_mortgage_template($template)` - Load custom single template
- `enqueue_scripts()` - Enqueue Interactivity API + Tailwind
- `generate_pages_for_user($user_id)` - Create loan-app + rate-quote pages
- `create_page($user_id, $template)` - Create single landing page
- `generate_block_markup($template, $name, $user_id)` - Build block content
- `get_user_page($user_id, $template)` - Check if page exists
- `get_user_data($user_id)` - Get user profile data (ACF integration)

---

#### Template Files

**`/single-frs_mortgage_lp.php`**

Custom single post template for mortgage landing pages.

---

### frs-lrg (DESTINATION)

#### What EXISTS

**Post Types:** ✅ Already registered in `includes/Core/PostTypes.php`
- `frs_biolink` - ✅ Registered
- `frs_prequal` - ✅ Registered
- `frs_openhouse` - ✅ Registered
- `frs_mortgage_lp` - ✅ Registered

**Blocks:** ✅ Many blocks exist in `blocks/` directory
- biolink blocks (8 blocks)
- prequal blocks (2 blocks)
- openhouse blocks (1 block)
- mortgage blocks (2 blocks)

**Integrations:**
- FluentBooking - ✅ Exists in `includes/Integrations/FluentBooking.php`
- FluentForms - ❓ No dedicated integration file found

---

#### What's MISSING

**Landing Page Generation System:**
- ❌ NO `class-frs-landing-pages.php` equivalent
- ❌ NO `class-frs-mortgage-landing-generator.php` equivalent
- ❌ NO frontend editor iframe system
- ❌ NO page auto-generation methods

**Result:** Users cannot auto-generate personal or co-branded landing pages in frs-lrg yet.

---

## Migration Requirements

### Phase 1: Copy PHP Files with Namespace Updates

#### File 1: Landing Pages Core

**Source:** `frs-partnership-portal/includes/class-frs-landing-pages.php`
**Destination:** `frs-lrg/includes/Core/LandingPages.php`

**Required Changes:**
1. Update namespace: `FRS_Partnership_Portal` → `LendingResourceHub\Core`
2. Update class name: `FRS_Portal_Landing_Pages` → `LandingPages`
3. Use Base trait: `use LendingResourceHub\Traits\Base;`
4. Update constants:
   - `FRS_PORTAL_PLUGIN_URL` → `LRH_URL`
   - `FRS_PORTAL_VERSION` → `LRH_VERSION`
5. Update ACF integration class reference:
   - `FRS_Portal_ACF_Integration` → Use frs-lrg's ACF class
6. Update meta key prefixes:
   - `_frs_` → `_lrh_` (optional, or keep for compatibility)
7. Update URL parameter:
   - `?frs_lp_editor=1` → `?lrh_lp_editor=1`

---

#### File 2: Mortgage Landing Generator

**Source:** `frs-partnership-portal/includes/class-frs-mortgage-landing-generator.php`
**Destination:** `frs-lrg/includes/Core/MortgageLandingGenerator.php`

**Required Changes:**
1. Update namespace: `→ LendingResourceHub\Core`
2. Update class name: `FRS_Mortgage_Landing_Generator` → `MortgageLandingGenerator`
3. Use Base trait
4. Update constants (URLs, version)
5. Update asset paths
6. Update user data retrieval to use Eloquent models if applicable

---

#### File 3: Template File

**Source:** `frs-partnership-portal/single-frs_mortgage_lp.php`
**Destination:** `frs-lrg/single-frs_mortgage_lp.php`

**Required Changes:**
1. Update plugin URL references
2. Ensure Tailwind CSS is loaded
3. Update any plugin-specific functions

---

### Phase 2: Initialize Classes

**File:** `frs-lrg/includes/Core/Plugin.php` or main plugin class

**Add:**
```php
// Landing page generation and editor
$this->landing_pages = new LandingPages();

// Mortgage landing page generator
$this->mortgage_landing_generator = new MortgageLandingGenerator();
```

---

### Phase 3: Fluent Forms Integration

#### Current Status

**frs-partnership-portal** - Already using Fluent Forms:
```php
// Embeds Fluent Forms via URL parameter
$form_url = home_url('/?fluent-form=3');

// Used in prequal-form block
'<!-- wp:frs/prequal-form {"formUrl":' . wp_json_encode($form_url) . '} /-->';
```

**frs-lrg** - Needs verification:
- ❓ Check if `frs/prequal-form` block exists and supports Fluent Forms URL
- ❓ Verify FluentForms plugin detection
- ❓ Check if form submissions route to lead_submissions table

---

#### Integration Checklist

**1. Verify prequal-form Block**

**File:** `frs-lrg/blocks/prequal-form/` or similar

**Check:**
- Does block accept `formUrl` attribute?
- Does it render Fluent Forms iframe/embed?
- Is form responsive and styled correctly?

**Example block attribute:**
```json
{
  "attributes": {
    "formUrl": {
      "type": "string",
      "default": ""
    }
  }
}
```

**Example render callback:**
```php
public function render_prequal_form($attributes) {
    $form_url = $attributes['formUrl'] ?? '';
    if (empty($form_url)) {
        return '<p>No form configured</p>';
    }

    return sprintf(
        '<div class="prequal-form-container">
            <iframe src="%s" width="100%%" height="800" frameborder="0"></iframe>
        </div>',
        esc_url($form_url)
    );
}
```

---

**2. Create FluentForms Integration Class**

**File:** `frs-lrg/includes/Integrations/FluentForms.php`

**Purpose:**
- Detect if FluentForms is active
- Provide helper methods for form URLs
- Hook into form submissions to create leads
- Map form fields to lead_submissions table

**Example Structure:**
```php
<?php

namespace LendingResourceHub\Integrations;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Models\LeadSubmission;

class FluentForms {
    use Base;

    public function init() {
        // Hook into FluentForms submission
        add_action('fluentform_submission_inserted', [$this, 'handle_submission'], 10, 3);

        // Add custom fields to forms if needed
        add_filter('fluentform_entry_lists_columns', [$this, 'add_custom_columns'], 10, 2);
    }

    /**
     * Check if FluentForms is active
     */
    public static function is_active() {
        return defined('FLUENTFORM') || class_exists('FluentForm\App\Modules\Form\Form');
    }

    /**
     * Get form URL by form ID
     */
    public static function get_form_url($form_id) {
        return home_url('/?fluent-form=' . intval($form_id));
    }

    /**
     * Handle form submission - create lead
     */
    public function handle_submission($entry_id, $form_data, $form) {
        // Only process specific forms (prequal, contact, etc.)
        $form_id = $form->id;
        $tracked_forms = [3, 4, 5]; // Configure which forms create leads

        if (!in_array($form_id, $tracked_forms, true)) {
            return;
        }

        // Extract data from submission
        $first_name = $form_data['names']['first_name'] ?? '';
        $last_name = $form_data['names']['last_name'] ?? '';
        $email = $form_data['email'] ?? '';
        $phone = $form_data['phone'] ?? '';

        // Get loan officer/agent from form hidden fields or URL params
        $loan_officer_id = $form_data['loan_officer_id'] ?? null;
        $agent_id = $form_data['agent_id'] ?? null;

        // Create lead in database
        LeadSubmission::create([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'loan_officer_id' => $loan_officer_id,
            'agent_id' => $agent_id,
            'lead_source' => 'fluent_form_' . $form_id,
            'status' => 'new',
            'custom_data' => json_encode($form_data),
            'created_date' => current_time('mysql'),
        ]);
    }

    /**
     * Add custom columns to FluentForms entry list
     */
    public function add_custom_columns($columns, $form_id) {
        $columns['loan_officer'] = 'Loan Officer';
        $columns['realtor_partner'] = 'Realtor Partner';
        return $columns;
    }
}
```

---

**3. Update Block Content Builders**

In `LandingPages::build_cobranded_prequal_content()`:

```php
// Get appropriate form ID from settings or use default
$form_id = get_option('lrh_prequal_form_id', 3);
$form_url = FluentForms::get_form_url($form_id);

// Include hidden fields for tracking
$form_url = add_query_arg([
    'loan_officer_id' => $loan_officer_id,
    'agent_id' => $agent_id,
    'partnership_id' => $partnership_id ?? 0
], $form_url);

// Embed in block
$content .= '<!-- wp:frs/prequal-form {"formUrl":' . wp_json_encode($form_url) . '} /-->';
```

---

**4. Add Form Settings to Admin**

**Location:** `src/admin/pages/settings/` (React admin)

**Settings to Add:**
- Prequal Form ID (which Fluent Forms form to use)
- Contact Form ID
- Lead capture settings
- Form field mapping

**Example Settings UI:**
```tsx
<div className="space-y-4">
  <h3 className="text-lg font-semibold">Fluent Forms Integration</h3>

  <div>
    <label>Pre-Qualification Form ID</label>
    <Input
      type="number"
      value={settings.prequal_form_id}
      onChange={(e) => updateSetting('prequal_form_id', e.target.value)}
    />
    <p className="text-sm text-muted-foreground">
      Enter the Fluent Forms ID for pre-qualification forms
    </p>
  </div>

  <div>
    <label>Contact Form ID</label>
    <Input
      type="number"
      value={settings.contact_form_id}
      onChange={(e) => updateSetting('contact_form_id', e.target.value)}
    />
  </div>

  {FluentForms.isActive ? (
    <Alert>
      <CheckCircle2 className="h-4 w-4" />
      <AlertTitle>FluentForms is active</AlertTitle>
      <AlertDescription>
        Form submissions will automatically create leads
      </AlertDescription>
    </Alert>
  ) : (
    <Alert variant="destructive">
      <AlertCircle className="h-4 w-4" />
      <AlertTitle>FluentForms not detected</AlertTitle>
      <AlertDescription>
        Please install and activate FluentForms to use form features
      </AlertDescription>
    </Alert>
  )}
</div>
```

---

## Implementation Steps

### Step 1: Migrate PHP Files

```bash
# Copy and rename files
cp frs-partnership-portal/includes/class-frs-landing-pages.php \
   frs-lrg/includes/Core/LandingPages.php

cp frs-partnership-portal/includes/class-frs-mortgage-landing-generator.php \
   frs-lrg/includes/Core/MortgageLandingGenerator.php

cp frs-partnership-portal/single-frs_mortgage_lp.php \
   frs-lrg/single-frs_mortgage_lp.php
```

---

### Step 2: Update Namespaces and Class Names

**LandingPages.php:**
```php
<?php

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

class LandingPages {
    use Base;

    // ... rest of class
}
```

**MortgageLandingGenerator.php:**
```php
<?php

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

class MortgageLandingGenerator {
    use Base;

    // ... rest of class
}
```

---

### Step 3: Update Constants and Paths

**Find and replace across both files:**
```
FRS_PORTAL_PLUGIN_URL → LRH_URL
FRS_PORTAL_VERSION → LRH_VERSION
FRS_Portal_ACF_Integration → LendingResourceHub\Core\ACF (or similar)
plugin_dir_path(dirname(__FILE__)) → LRH_DIR
```

---

### Step 4: Create FluentForms Integration

Create new file: `frs-lrg/includes/Integrations/FluentForms.php`

(Use example structure above)

---

### Step 5: Initialize Classes

**File:** `frs-lrg/includes/Core/Plugin.php` or wherever classes are initialized

```php
// Landing pages
$this->landing_pages = new LandingPages();

// Mortgage landing pages
$this->mortgage_landing_generator = new MortgageLandingGenerator();

// FluentForms integration
if (FluentForms::is_active()) {
    $this->fluent_forms = new FluentForms();
    $this->fluent_forms->init();
}
```

---

### Step 6: Verify Blocks Support Forms

Check `blocks/prequal-form/` or equivalent to ensure:
- Block accepts `formUrl` attribute
- Renders iframe or embed correctly
- Form is responsive

---

### Step 7: Add Admin Settings

Add FluentForms settings to admin React SPA (`src/admin/pages/settings/`)

---

### Step 8: Test End-to-End

1. Create personal landing page for loan officer
2. Create co-branded prequal page for partnership
3. Submit form on prequal page
4. Verify lead appears in `lead_submissions` table
5. Verify lead shows in admin dashboard
6. Test frontend editor (iframe)

---

## API Endpoints (if needed)

**May need to add:**
```php
// Generate personal landing page
$route->post('/landing-pages/generate/{user_id}',
    '\LendingResourceHub\Controllers\LandingPages\Actions@generate_personal');

// Generate co-branded page
$route->post('/landing-pages/generate/cobranded/{partnership_id}',
    '\LendingResourceHub\Controllers\LandingPages\Actions@generate_cobranded');

// Get user's landing pages
$route->get('/landing-pages/user/{user_id}',
    '\LendingResourceHub\Controllers\LandingPages\Actions@get_user_pages');
```

---

## Testing Checklist

### Personal Landing Pages

- [ ] Auto-generate personal landing page for loan officer
- [ ] Verify page uses correct template
- [ ] Verify user data populates correctly (name, email, phone, avatar)
- [ ] Test frontend editor access (loan officer can edit their page)
- [ ] Test editor permissions (other users cannot edit)
- [ ] Verify locked blocks (user can edit content but not add/remove blocks)

### Co-Branded Prequal Pages

- [ ] Auto-generate co-branded prequal page for partnership
- [ ] Verify both LO and realtor names display correctly
- [ ] Verify Fluent Forms embed works
- [ ] Test form submission creates lead
- [ ] Verify lead has correct `loan_officer_id` and `agent_id`
- [ ] Test both LO and realtor can edit co-branded page

### Mortgage Landing Pages

- [ ] Auto-generate loan-app page
- [ ] Auto-generate rate-quote page
- [ ] Verify Interactivity API script loads
- [ ] Verify custom template loads
- [ ] Test responsive design

### Fluent Forms Integration

- [ ] Verify FluentForms plugin detection
- [ ] Test form submission hook
- [ ] Verify lead creation in `lead_submissions` table
- [ ] Verify lead appears in admin dashboard
- [ ] Test field mapping (form fields → database columns)
- [ ] Test hidden field tracking (loan_officer_id, agent_id)

---

## Summary

**Files to Migrate:** 3 files
1. `class-frs-landing-pages.php` → `includes/Core/LandingPages.php`
2. `class-frs-mortgage-landing-generator.php` → `includes/Core/MortgageLandingGenerator.php`
3. `single-frs_mortgage_lp.php` → `single-frs_mortgage_lp.php`

**New Files to Create:** 1 file
1. `includes/Integrations/FluentForms.php` - Form submission handling

**Fluent Forms Status:**
- ✅ Already used in frs-partnership-portal
- ✅ No form system replacement needed - just migrate existing integration
- ❓ Need to verify blocks support in frs-lrg
- ❓ Need to add lead capture hooks

**Estimated Effort:**
- Phase 1 (Copy files, update namespaces): 1-2 hours
- Phase 2 (FluentForms integration class): 1 hour
- Phase 3 (Admin settings UI): 30 minutes
- Phase 4 (Testing): 1-2 hours
- **Total: 3-5 hours**
