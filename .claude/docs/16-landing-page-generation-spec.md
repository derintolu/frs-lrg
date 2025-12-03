# Landing Page Generation System - Specification

**Created:** 2025-12-03
**Status:** Planning Phase
**Purpose:** Define page types, templates, and block filtering for user-generated landing pages

---

## Overview

This system allows loan officers and realtor partners to generate their own landing pages through the portal interface. Each page type has:
- A custom post type
- A single dynamic block that pulls data from the Profile model
- A curated block allowlist for the Gutenberg editor
- FluentForms integration for lead capture

---

## Landing Page Types

### 1. Biolink Pages (`frs_biolink`)

**Status:** ✅ Generation method exists
**File:** `includes/Controllers/Biolinks/Blocks.php::generate_biolink_page()`
**Template:** `<!-- wp:lrh/biolink-page {"user_id":123} /-->`

**Purpose:** Personal landing page with social links, contact info, and lead capture form

**Dynamic Block Renders:**
- Profile photo/headshot
- Name and title
- Company information
- Social media links
- Call-to-action buttons
- Embedded FluentForm (conversational type)

**Block Allowlist (for editing):**
- Current: Single dynamic block only (no editing)
- Proposed: Add biolink-specific blocks for customization:
  - `lrh/biolink-header` - Custom header section
  - `lrh/biolink-button` - CTA buttons
  - `lrh/biolink-social` - Social links
  - `lrh/biolink-form` - Form embed
  - `lrh/biolink-spacer` - Spacing control

**Meta Fields:**
- `frs_biolink_page`: "1"
- `frs_biolink_user`: user_id
- `_frs_loan_officer_id`: user_id
- `_frs_page_views`: 0
- `_frs_page_conversions`: 0

### 2. Mortgage Landing Pages (`frs_mortgage_lp`)

**Status:** ✅ Generation method exists
**File:** `includes/Core/MortgageLandingGenerator.php::generate_pages_for_user()`
**Templates:**
- Loan Application: Dynamic content from Profile
- Rate Quote: Dynamic content from Profile

**Purpose:** Two-page mortgage workflow (rate quote → loan application)

**Features:**
- WordPress Interactivity API for state management
- Multi-step forms
- Progress indicators
- Auto-generates TWO pages per user (loan-app and rate-quote)

**Block Allowlist:**
- Current: Generated via PHP templates (not Gutenberg blocks)
- Proposed: Convert to block-based templates:
  - `lrh/mortgage-form-step` - Form steps
  - `lrh/mortgage-progress` - Progress bar
  - `lrh/mortgage-calculator` - Rate calculator
  - `lrh/mortgage-submit` - Submit button

**Meta Fields:**
- Not specified in current implementation
- Should add: page type (loan-app vs rate-quote), view/conversion tracking

### 3. Prequal Pages (`frs_prequal`)

**Status:** ⚠️ Post type registered, NO generation method
**File:** Post type registered somewhere, blocks not yet created

**Purpose:** Pre-qualification landing page (co-branded with realtor partner)

**Requires:**
- New generation method: `Prequal\Blocks::generate_prequal_page($lo_id, $realtor_id)`
- New dynamic block: `lrh/prequal-page`
- Co-branding logic (two profiles displayed)

**Proposed Template:**
```
<!-- wp:lrh/prequal-page {"loan_officer_id":123,"realtor_id":456} /-->
```

**Dynamic Block Should Render:**
- Both LO and realtor profiles
- Combined branding
- Pre-qualification form
- Partnership information

**Block Allowlist:**
- `lrh/prequal-header` - Header with both profiles
- `lrh/prequal-form` - Pre-qual form
- `lrh/prequal-benefits` - Benefits section
- Core blocks: paragraph, heading, image, button

**Meta Fields (proposed):**
- `_frs_loan_officer_id`: user_id
- `_frs_realtor_partner_id`: user_id
- `_frs_partnership_id`: partnership_id (if applicable)
- `_frs_page_views`: 0
- `_frs_page_conversions`: 0

### 4. Open House Pages (`frs_openhouse`)

**Status:** ⚠️ Post type registered, NO generation method
**File:** Post type registered somewhere, blocks not yet created

**Purpose:** Open house landing page (co-branded with realtor partner)

**Requires:**
- New generation method: `OpenHouse\Blocks::generate_openhouse_page($lo_id, $realtor_id, $property_data)`
- New dynamic block: `lrh/openhouse-page`
- Property information integration
- Rentcast API integration for property data

**Proposed Template:**
```
<!-- wp:lrh/openhouse-page {"loan_officer_id":123,"realtor_id":456,"property_address":"123 Main St"} /-->
```

**Dynamic Block Should Render:**
- Property photos and details
- Open house date/time
- Both LO and realtor profiles
- Mortgage calculator
- Contact form

**Block Allowlist:**
- `lrh/openhouse-header` - Property showcase
- `lrh/openhouse-details` - Event details
- `lrh/openhouse-calculator` - Mortgage calculator
- `lrh/openhouse-form` - RSVP/contact form
- Core blocks: paragraph, heading, image, gallery

**Meta Fields (proposed):**
- `_frs_loan_officer_id`: user_id
- `_frs_realtor_partner_id`: user_id
- `_frs_property_address`: string
- `_frs_open_house_date`: datetime
- `_frs_page_views`: 0
- `_frs_page_conversions`: 0

### 5. Partner Portals (`frs_partner_portal`)

**Status:** ✅ Generation method exists
**File:** `includes/Controllers/PartnerPortals/Blocks.php::generate_partner_portal()`
**Template:** `<!-- wp:lrh/partner-portal-page /-->`

**Purpose:** Multi-tool portal for realtor partnerships

**Dynamic Block Renders:**
- Custom branding (colors, logos, button styles via Carbon Fields)
- Multiple LO profiles
- Partnership tools (biolinks, prequals, open houses)
- Shared resources

**Block Allowlist:**
- Current: Single dynamic block only
- Carbon Fields used for branding customization

**Meta Fields (via Carbon Fields):**
- `pp_loan_officers`: array of LO associations
- `pp_buddypress_group_id`: int
- `pp_primary_color`: string
- `pp_secondary_color`: string
- `pp_logo_id`: int
- `pp_button_style`: string

---

## Block Filtering Strategy

### Approach 1: Single Dynamic Block (Current)

**Used by:** Biolinks, Partner Portals

**Pros:**
- Simple to implement
- Consistent output
- No risk of users breaking layout
- Fast to render

**Cons:**
- Limited customization
- Users can't add content
- Must edit through settings forms instead

**Example:**
```
<!-- wp:lrh/biolink-page {"user_id":123} /-->
```

### Approach 2: Curated Block Allowlist (Proposed)

**Recommended for:** Prequal, Open House, eventually Biolink enhancements

**Pros:**
- Users can customize content
- Native Gutenberg editing experience
- More flexible for different use cases
- Can still enforce structure

**Cons:**
- More complex to implement
- Risk of layout issues if users add too much
- Requires block allowlist filtering

**Implementation:**
```php
// In render.php or block registration
add_filter( 'allowed_block_types_all', function( $allowed_blocks, $context ) {
    if ( $context->post && $context->post->post_type === 'frs_prequal' ) {
        return array(
            'lrh/prequal-header',
            'lrh/prequal-form',
            'lrh/prequal-benefits',
            'core/paragraph',
            'core/heading',
            'core/image',
            'core/button',
        );
    }
    return $allowed_blocks;
}, 10, 2 );
```

### Approach 3: Template Lock (Recommended)

**Best of both worlds**

Lock block structure but allow content editing:

```php
$template = array(
    array( 'lrh/prequal-header', array(), array() ),
    array( 'core/paragraph', array(
        'placeholder' => 'Add your introduction...',
    )),
    array( 'lrh/prequal-form', array(), array() ),
);

register_post_type( 'frs_prequal', array(
    'template'       => $template,
    'template_lock'  => 'all', // or 'insert' to allow reordering
));
```

---

## Editor Integration

### Current State

**Existing Components:**
- `src/frontend/portal/components/LandingPageEditor.tsx` - Not fully wired
- `src/frontend/portal/utils/landingPageGenerator.ts` - Not connected to backend

**Missing:**
- Iframe embedding of Gutenberg editor
- Block allowlist enforcement
- Save/publish workflow
- Preview functionality

### Proposed Implementation

**1. Editor Iframe Approach:**

```typescript
// LandingPageEditor.tsx
<iframe
  src={`${wpAdminUrl}/post.php?post=${pageId}&action=edit`}
  style={{ width: '100%', height: '800px', border: 'none' }}
  title="Page Editor"
/>
```

**Problems with this approach:**
- Requires authentication in iframe
- Can't easily filter blocks from outside iframe
- Hard to customize editor interface

**2. Block Editor Component (Better):**

Use `@wordpress/block-editor` package directly in React:

```typescript
import { BlockEditorProvider, BlockList } from '@wordpress/block-editor';
import { createBlock } from '@wordpress/blocks';

// Render Gutenberg editor directly in React
<BlockEditorProvider value={blocks} onChange={setBlocks}>
  <BlockList />
</BlockEditorProvider>
```

**Benefits:**
- Full control over allowed blocks
- Custom UI around editor
- Direct save to REST API
- Better UX integration

---

## FluentForms Integration

### Current Pattern (from Biolinks)

```php
$form_args = array(
    'post_type'   => 'fluentform',
    'post_status' => 'publish',
    'meta_query'  => array(
        array(
            'key'     => '_ff_form_settings',
            'value'   => '"conversationType":"classic"',
            'compare' => 'LIKE',
        ),
    ),
);
$forms = get_posts( $form_args );
```

**Assigns form to page via:**
- Meta field: Form ID stored in page meta or block attributes
- Dynamic rendering: Form rendered via shortcode in block output

### Proposed Pattern for New Pages

**1. Form Selection During Generation:**
- User selects form from dropdown (populated with available FluentForms)
- Form ID stored in block attributes or post meta
- Form type validated (conversational, classic, etc.)

**2. Form Types by Page Type:**
- **Biolink:** Conversational forms (quick contact)
- **Prequal:** Multi-step forms (qualification workflow)
- **Open House:** Simple RSVP forms
- **Mortgage LP:** Complex mortgage application forms

**3. Lead Tracking Integration:**
- Form submission triggers lead creation in `wp_lead_submissions`
- Associates lead with page via `page_id` field
- Updates conversion count: `_frs_page_conversions`

---

## Page Generation API

### Option 1: Static Methods (Current)

**Pros:**
- Already implemented for biolinks and portals
- Simple to call from PHP
- No new API endpoints needed

**Cons:**
- Can't call from React frontend directly
- Requires PHP intermediary

**Example:**
```php
$result = \LendingResourceHub\Controllers\Biolinks\Blocks::generate_biolink_page( $user_id );
```

### Option 2: REST API Endpoints (Recommended)

**Pros:**
- Can call from React frontend
- Consistent with existing architecture
- Easier to test and debug
- Better separation of concerns

**Cons:**
- Requires new endpoint creation
- Need to wrap existing static methods

**Proposed Endpoints:**

```
POST /wp-json/lrh/v1/pages/generate/biolink
POST /wp-json/lrh/v1/pages/generate/prequal
POST /wp-json/lrh/v1/pages/generate/openhouse
POST /wp-json/lrh/v1/pages/generate/mortgage
POST /wp-json/lrh/v1/pages/generate/portal
```

**Request Body Example:**
```json
{
  "user_id": 123,
  "page_type": "prequal",
  "options": {
    "realtor_id": 456,
    "partnership_id": 789,
    "form_id": 12
  }
}
```

**Response:**
```json
{
  "success": true,
  "page": {
    "id": 567,
    "title": "John Doe Pre-Qualification",
    "url": "https://hub21.local/prequal/john-doe",
    "edit_url": "https://hub21.local/wp-admin/post.php?post=567&action=edit",
    "type": "frs_prequal"
  }
}
```

---

## Summary: What Already Exists vs. What Needs to Be Built

### ✅ CONFIRMED - Already Built

**Post Types** (all registered in `includes/Core/PostTypes.php`):
1. `frs_biolink` - Biolink landing pages (slug: 'l')
2. `frs_prequal` - Prequal pages (slug: 'prequal', template_lock: 'all')
3. `frs_openhouse` - Open house pages (slug: 'open-house')
4. `frs_mortgage_lp` - Mortgage landing pages (slug: 'apply', template_lock: 'all')
5. `frs_partner_portal` - Partner company portals (slug: 'partner')
6. `frs_re_portal` - RE portal pages (slug: 're')
7. `lo_portal_page` - LO portal pages (slug: 'lo')

**Gutenberg Blocks** (all in `src/blocks/`):
1. `lrh/biolink-page` - Main biolink dynamic block
2. `lrh/biolink-button`, `lrh/biolink-form`, `lrh/biolink-header`, `lrh/biolink-social`, `lrh/biolink-spacer`, `lrh/biolink-thankyou`
3. `lrh/prequal-heading` - Stores in post meta (_frs_prequal_heading_line1/line2)
4. `lrh/prequal-subheading` - Prequal subheading block
5. `lrh/openhouse-carousel` - Photo carousel with address
6. `mortgage-calculator` - Mortgage calculator block
7. `mortgage-form` - Mortgage form block

**Generation Methods:**
1. `Biolinks\Blocks::generate_biolink_page($user_id)` ✅
2. `MortgageLandingGenerator::generate_pages_for_user($user_id)` ✅ (creates 2 pages)
3. `PartnerPortals\Blocks::generate_partner_portal($group_id, $lo_ids, $name, $company)` ✅

**API Endpoints** (`includes/Controllers/LandingPages/Actions.php`):
1. `get_landing_pages_for_lo($request)` - Returns biolink, prequal, openhouse, mortgage pages for loan officer ✅
2. `get_landing_pages_for_realtor($request)` - Returns prequal and openhouse pages for realtor partner ✅

**Template Configuration:**
1. `frs_prequal` - Has template: `[['frs/prequal-heading'], ['frs/prequal-subheading']]`, template_lock: 'all' ✅
2. `frs_mortgage_lp` - Has empty template array, template_lock: 'all' ✅

**Meta Fields Pattern** (already in use):
- `_frs_loan_officer_id` - Owner user ID
- `_frs_partner_user_id` - Realtor partner ID (for co-branded)
- `_frs_page_views` - View count
- `_frs_page_conversions` - Conversion count
- `_frs_lp_template` - Mortgage template type (rate-quote or loan-app)

### ❌ NEEDS TO BE CREATED

**Generation Methods:**
1. Prequal: `Prequal\Blocks::generate_prequal_page($lo_id, $realtor_id, $partnership_id = null)`
2. Open House: `OpenHouse\Blocks::generate_openhouse_page($lo_id, $realtor_id, $property_address)`

**POST API Endpoints** (for triggering generation from React):
1. `POST /wp-json/lrh/v1/pages/generate/biolink` - Wrap existing method
2. `POST /wp-json/lrh/v1/pages/generate/prequal` - New endpoint
3. `POST /wp-json/lrh/v1/pages/generate/openhouse` - New endpoint
4. `POST /wp-json/lrh/v1/pages/generate/mortgage` - Wrap existing method
5. `POST /wp-json/lrh/v1/pages/generate/portal` - Wrap existing method

**Frontend UI Components:**
1. "Create Page" button in portal dashboard
2. Page type selection modal
3. Configuration form (select realtor partner, property address, etc.)
4. Success notification with links to view/edit

### Phase 2: Editor Integration (Required)

1. React component using `@wordpress/block-editor`
2. Block filtering enforcement
3. Save/publish workflow
4. Preview functionality
5. Integration with portal UI

### Phase 3: User Interface (Required)

1. "Create Page" buttons in portal
2. Page type selection modal
3. Configuration forms (realtor selection, form selection, etc.)
4. Page management dashboard
5. Edit/delete/duplicate actions

### Phase 4: Onboarding (Later)

1. Onboarding wizard component
2. Task completion tracking
3. Progress indicators
4. Tour/tooltips for first-time users

---

## Next Steps

1. **Confirm specifications** - Review this doc with stakeholders
2. **Create missing generation methods** - Prequal and Open House
3. **Build REST API endpoints** - Wrap generation methods
4. **Implement block filtering** - Template locks and allowlists
5. **Build editor component** - React integration with block editor
6. **Create UI components** - Portal interface for page generation
7. **Test workflow** - End-to-end page creation and editing
8. **Build onboarding** - Wizard that uses the page generation system

---

**Status:** Specification complete, awaiting approval to proceed with implementation
