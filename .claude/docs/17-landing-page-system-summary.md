# Landing Page System - Current State Summary

**Last Updated:** 2025-12-03
**Status:** Foundation 70% Complete

---

## Executive Summary

The landing page system foundation is **70% complete**. Most infrastructure exists:
- ✅ All 7 post types registered
- ✅ 12+ Gutenberg blocks built
- ✅ Template locking configured
- ✅ 3 generation methods working (biolink, mortgage, partner portals)
- ✅ 2 GET API endpoints for listing pages

**What's Missing:**
- ❌ 2 generation methods (prequal, open house)
- ❌ 5 POST API endpoints for triggering generation from React
- ❌ Frontend UI for creating pages
- ❌ Onboarding wizard integration

---

## Quick Reference

### Post Types (7 total)

| Post Type | Slug | Template Lock | Generation Method | Status |
|-----------|------|---------------|-------------------|--------|
| `frs_biolink` | `/l/` | None | `Biolinks\Blocks::generate_biolink_page()` | ✅ Works |
| `frs_prequal` | `/prequal/` | 'all' | ❌ Missing | ⚠️ Needs creation |
| `frs_openhouse` | `/open-house/` | None | ❌ Missing | ⚠️ Needs creation |
| `frs_mortgage_lp` | `/apply/` | 'all' | `MortgageLandingGenerator::generate_pages_for_user()` | ✅ Works |
| `frs_partner_portal` | `/partner/` | None | `PartnerPortals\Blocks::generate_partner_portal()` | ✅ Works |
| `frs_re_portal` | `/re/` | None | Not needed (admin created) | ✅ N/A |
| `lo_portal_page` | `/lo/` | None | Not needed (admin created) | ✅ N/A |

### Gutenberg Blocks (12 total)

**Biolink Blocks:**
- `lrh/biolink-page` - Main dynamic block
- `lrh/biolink-button`, `lrh/biolink-form`, `lrh/biolink-header`
- `lrh/biolink-social`, `lrh/biolink-spacer`, `lrh/biolink-thankyou`

**Prequal Blocks:**
- `lrh/prequal-heading` - Meta storage (line1, line2)
- `lrh/prequal-subheading`

**Open House Blocks:**
- `lrh/openhouse-carousel` - Photo carousel

**Mortgage Blocks:**
- `mortgage-calculator`
- `mortgage-form`

### API Endpoints

**GET Endpoints** (working):
- `GET /wp-json/lrh/v1/landing-pages/lo/:id` → `get_landing_pages_for_lo()`
- `GET /wp-json/lrh/v1/landing-pages/realtor/:id` → `get_landing_pages_for_realtor()`

**POST Endpoints** (needed):
- `POST /wp-json/lrh/v1/pages/generate/biolink`
- `POST /wp-json/lrh/v1/pages/generate/prequal`
- `POST /wp-json/lrh/v1/pages/generate/openhouse`
- `POST /wp-json/lrh/v1/pages/generate/mortgage`
- `POST /wp-json/lrh/v1/pages/generate/portal`

---

## Architecture Overview

### Page Generation Flow

```
┌─────────────────────────────────────────────────────────┐
│  React Portal UI (not yet built)                       │
│  - "Create Page" button                                │
│  - Page type selection modal                           │
│  - Configuration form (partner selection, etc.)        │
└───────────────────┬─────────────────────────────────────┘
                    │
                    │ POST /wp-json/lrh/v1/pages/generate/{type}
                    │ Body: { user_id, options }
                    ↓
┌─────────────────────────────────────────────────────────┐
│  REST API Controller (partially built)                 │
│  includes/Controllers/LandingPages/Actions.php          │
│  - Route to generation method                          │
│  - Validate permissions                                │
│  - Return page data                                    │
└───────────────────┬─────────────────────────────────────┘
                    │
                    │ Call static generation method
                    ↓
┌─────────────────────────────────────────────────────────┐
│  Generation Method (3 of 5 exist)                      │
│  - Biolinks\Blocks::generate_biolink_page()       ✅   │
│  - MortgageLandingGenerator::generate_pages...()  ✅   │
│  - PartnerPortals\Blocks::generate_partner...()   ✅   │
│  - Prequal\Blocks::generate_prequal_page()        ❌   │
│  - OpenHouse\Blocks::generate_openhouse_page()    ❌   │
└───────────────────┬─────────────────────────────────────┘
                    │
                    │ wp_insert_post()
                    ↓
┌─────────────────────────────────────────────────────────┐
│  WordPress Post                                         │
│  - Post content: Gutenberg blocks                      │
│  - Post meta: _frs_loan_officer_id, views, etc.       │
│  - Featured image (if applicable)                      │
└─────────────────────────────────────────────────────────┘
```

### Template Locking Strategy

**`frs_prequal` (Template Lock: 'all')**
```php
'template' => array(
    array( 'lrh/prequal-heading' ),
    array( 'lrh/prequal-subheading' ),
),
'template_lock' => 'all', // Users cannot add/remove/reorder blocks
```
**Effect:** Users can only EDIT the content of the two heading blocks, not change structure.

**`frs_mortgage_lp` (Template Lock: 'all')**
```php
'template' => array(), // Empty template
'template_lock' => 'all', // Structure completely locked
```
**Effect:** Content is generated programmatically via Interactivity API, users cannot edit in block editor.

**`frs_openhouse` (No Template Lock)**
```php
// No template or template_lock specified
```
**Effect:** Users have full block editor access (can add any blocks).

---

## Meta Fields Reference

All pages use these meta keys:

```php
// Required
'_frs_loan_officer_id' => user_id           // Page owner

// Co-branded pages only
'_frs_partner_user_id' => user_id           // Realtor partner
'_frs_partnership_id'  => partnership_id    // Optional partnership link

// Tracking
'_frs_page_views'       => 0                // View count
'_frs_page_conversions' => 0                // Conversion count

// Mortgage pages only
'_frs_lp_template' => 'rate-quote'          // or 'loan-app'

// Open house only
'_frs_property_address' => '123 Main St'    // Property address

// Biolink only
'frs_biolink_page' => '1'                   // Flag for biolink
'frs_biolink_user' => user_id               // Duplicate of owner ID
```

---

## Generation Method Pattern

All generation methods follow this pattern:

```php
public static function generate_[type]_page( $params ) {
    // 1. Get user profile data
    $profile = Profile::where( 'user_id', $user_id )->first();
    if ( ! $profile ) {
        return false;
    }

    // 2. Generate unique slug
    $slug = self::generate_unique_[type]_slug( $profile->first_name, $user_id );

    // 3. Build page content (Gutenberg blocks)
    $page_content = '<!-- wp:lrh/block-name {"user_id":' . $user_id . '} /-->';

    // 4. Prepare post data
    $page_data = array(
        'post_title'   => $profile->first_name . ' ' . $profile->last_name,
        'post_name'    => $slug,
        'post_content' => $page_content,
        'post_status'  => 'publish',
        'post_type'    => 'frs_[type]',
        'post_author'  => $user_id,
        'meta_input'   => array(
            '_frs_loan_officer_id'  => $user_id,
            '_frs_page_views'       => 0,
            '_frs_page_conversions' => 0,
            // Type-specific meta...
        ),
    );

    // 5. Insert post
    $page_id = wp_insert_post( $page_data );

    // 6. Set featured image (if applicable)
    if ( $profile->headshot_id ) {
        set_post_thumbnail( $page_id, $profile->headshot_id );
    }

    // 7. Return result
    return array(
        'id'       => $page_id,
        'url'      => get_permalink( $page_id ),
        'edit_url' => get_edit_post_link( $page_id, 'raw' ),
    );
}
```

---

## File Locations

### PHP
```
includes/
├── Core/
│   ├── PostTypes.php                      # All 7 post types registered
│   └── MortgageLandingGenerator.php       # Mortgage generation
├── Controllers/
│   ├── Biolinks/
│   │   └── Blocks.php                     # Biolink generation
│   ├── PartnerPortals/
│   │   └── Blocks.php                     # Portal generation
│   ├── LandingPages/
│   │   └── Actions.php                    # GET endpoints
│   └── [Prequal]/                         # ❌ Need to create
│       └── Blocks.php                     # ❌ Need to create
│   └── [OpenHouse]/                       # ❌ Need to create
│       └── Blocks.php                     # ❌ Need to create
```

### Gutenberg Blocks
```
src/blocks/
├── biolink-page/                          # ✅ Built
├── prequal-heading/                       # ✅ Built
├── prequal-subheading/                    # ✅ Built
├── openhouse-carousel/                    # ✅ Built
├── mortgage-calculator/                   # ✅ Built
└── mortgage-form/                         # ✅ Built
```

### React Frontend (not yet built)
```
src/frontend/portal/
├── components/
│   ├── LandingPageCreator.tsx            # ❌ Need to create
│   ├── PageTypeSelector.tsx              # ❌ Need to create
│   └── PageConfigForm.tsx                # ❌ Need to create
```

---

## Next Steps - Priority Order

### Phase 1: Complete Backend (High Priority)

1. ✅ Define landing page types and templates (DONE)
2. **[IN PROGRESS]** Create generation methods:
   - `includes/Controllers/Prequal/Blocks.php::generate_prequal_page()`
   - `includes/Controllers/OpenHouse/Blocks.php::generate_openhouse_page()`
3. Create POST API endpoints in `LandingPages/Actions.php`:
   - Wrap all 5 generation methods with REST endpoints
   - Add to `includes/Routes/Api.php`
4. Test generation methods via WP-CLI or Postman

### Phase 2: Build Frontend UI (Medium Priority)

5. Create page creation UI components:
   - "Create Page" button in dashboard
   - Page type selector modal
   - Configuration forms (partner selection, property address, etc.)
6. Wire up API calls
7. Test end-to-end page creation flow

### Phase 3: Onboarding Integration (Lower Priority)

8. Create onboarding wizard component
9. Add task completion tracking
10. Integrate with portal dashboard

---

## Testing Checklist

After each generation method is created:

- [ ] Test with valid user ID
- [ ] Test with invalid user ID (should fail gracefully)
- [ ] Verify post created with correct post_type
- [ ] Verify meta fields stored correctly
- [ ] Verify unique slug generation
- [ ] Verify featured image set (if applicable)
- [ ] Test with user without profile (should fail gracefully)
- [ ] Test generating duplicate page (should prevent or handle)

---

## Related Documentation

- **[16-landing-page-generation-spec.md](./16-landing-page-generation-spec.md)** - Complete specifications
- **[15-landing-page-migration-plan.md](./15-landing-page-migration-plan.md)** - Migration planning
- **[14-migration-verification-checklist.md](./14-migration-verification-checklist.md)** - Verification status
- **[13-migration-status-from-frs-partnership-portal.md](./13-migration-status-from-frs-partnership-portal.md)** - What was migrated

---

**Status:** Ready to proceed with Phase 1 - Create generation methods for prequal and open house
