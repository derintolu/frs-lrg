# FRS-LRG Integration Audit
**Date:** 2025-11-22
**Purpose:** Document existing code vs disconnections for weekend refactoring sprint

---

## ✅ WHAT EXISTS (All Code Is Built)

### **Backend - PHP/Eloquent**

#### Landing Pages
- ✅ `Controllers/LandingPages/Actions.php` - API endpoints
- ✅ Landing page types: Biolink, Prequal, Open House, Mortgage LP
- ✅ Queries by user role (LO vs Realtor)
- ✅ Post types: `frs_biolink`, `frs_prequal`, `frs_openhouse`, `frs_mortgage_lp`
- ✅ Metadata: `_frs_loan_officer_id`, `_frs_partner_user_id`, `_frs_lp_template`

#### Leads System
- ✅ `Models/LeadSubmission.php` - Eloquent model with 17 fields
- ✅ `Controllers/Leads/Actions.php` - Full CRUD API
- ✅ `get_leads()` - All leads with JOIN to users table
- ✅ `get_leads_for_lo()` - Filter by loan officer with date range
- ✅ `create_lead()` - Generic lead creation
- ✅ `create_calculator_lead()` - **SPECIAL: Calculator-specific endpoint exists!**
- ✅ `create_mortgage_lead()` - Mortgage LP form submission
- ✅ `update_lead_status()` - Status management
- ✅ `add_lead_note()` - Notes with user/timestamp
- ✅ Email notifications to LO on lead creation
- ✅ WordPress hooks: `do_action('lrh_calculator_lead_created')` (line 223)
- ✅ WordPress hooks: `do_action('lrh_mortgage_lead_created')` (line 402)

#### Partnerships
- ✅ `Models/Partnership.php` - Eloquent model
- ✅ `Controllers/Partnerships/Actions.php` - Full partnership lifecycle
- ✅ `create_partnership()` - Send invitation
- ✅ `assign_partnership()` - Direct assignment (no invitation)
- ✅ `get_partnerships_for_lo()` - LO's partnerships
- ✅ `get_partnership_for_realtor()` - Realtor's partnership
- ✅ HTML email template for invitations
- ✅ Integrates with `frs-wp-users` profiles (line 368-370)

### **Frontend - React/TypeScript**

#### Data Management
- ❌ **DataKit SDK** - NOT YET INTEGRATED (https://github.com/UseDataKit/SDK)
- ⚠️ Currently using manual `fetch()` calls - need to migrate to DataKit

#### Calculators (7 Types)
- ✅ `AffordabilityCalculator.tsx`
- ✅ `BuydownCalculator.tsx`
- ✅ `ConventionalCalculator.tsx`
- ✅ `DSCRCalculator.tsx`
- ✅ `NetProceedsCalculator.tsx`
- ✅ `RefinanceCalculator.tsx`
- ✅ `RentVsBuyCalculator.tsx`
- ✅ `ResultsCard.tsx` - Visual results display with donut chart
- ✅ Mortgage calculation utilities

#### Landing Page Editor
- ✅ `LandingPageEditor.tsx` - Block-based editor (**NOTE:** Will use Greenshift instead for production)
- ✅ Block types: hero, features, testimonial, cta, form
- ✅ Drag-and-drop interface
- ⚠️ **ARCHITECTURE CHANGE:** Auto-generated LPs will use **Greenshift**, not this React editor

#### Dashboards
- ✅ `LoanOfficerDashboard.tsx`
- ✅ `RealtorDashboard.tsx`
- ✅ `BiolinkDashboard.tsx`

#### Other
- ✅ `ProfileTour.tsx` - Guided tour (already exists!)
- ✅ `PortalLayout.tsx` + `PortalSidebar.tsx`

---

## ❌ WHAT'S DISCONNECTED (Needs Wiring)

### **Critical Gap #1: Calculator → Leads**
**Status:** ❌ NOT CONNECTED

**What Exists:**
- ✅ Backend API endpoint: `POST /lrh/v1/leads/calculator` (line 166 in Leads/Actions.php)
- ✅ Frontend calculators: All 7 types render and calculate

**What's Missing:**
- ❌ Calculator components don't call the lead API
- ❌ No lead capture form in `ResultsCard.tsx`
- ❌ No "Get My Rate" or "Contact Me" button
- ❌ No form fields for: first_name, last_name, email, phone

**Required Refactor:**
```typescript
// In ResultsCard.tsx - ADD THIS:
<Card className="mt-4">
  <CardHeader>
    <CardTitle>Get Your Personalized Rate</CardTitle>
  </CardHeader>
  <CardContent>
    <LeadCaptureForm
      calculatorType="affordability"
      calculationData={results}
      onSubmit={submitToLeadAPI}
    />
  </CardContent>
</Card>
```

**API Endpoint Already Exists:**
```
POST /wp-json/lrh/v1/calculator-leads
{
  first_name, last_name, email, phone,
  loan_officer_id, calculator_type, calculation_data
}
```

---

### **Critical Gap #2: Landing Pages → Calculator Embed**
**Status:** ❌ NOT CONNECTED

**What Exists:**
- ✅ All calculator components (React)
- ✅ Landing page post types
- ✅ Greenshift page builder (to be used)

**What's Missing:**
- ❌ No Greenshift block/shortcode for calculator embed
- ❌ Can't embed calculator in Greenshift-built landing page
- ❌ No calculator selection dropdown in Greenshift

**Required Refactor:**
```php
// CREATE: includes/Blocks/CalculatorShortcode.php
// Register WordPress shortcode that renders React calculator

add_shortcode('frs_calculator', function($atts) {
  $type = $atts['type'] ?? 'conventional'; // affordability, conventional, etc.

  // Enqueue React calculator assets
  wp_enqueue_script('frs-lrg-calculators');

  // Render container div for React to mount into
  return sprintf(
    '<div class="frs-calculator-embed" data-calculator-type="%s"></div>',
    esc_attr($type)
  );
});

// Usage in Greenshift:
// [frs_calculator type="conventional"]
// [frs_calculator type="affordability"]
```

**Greenshift Integration:**
- Create custom Greenshift block that allows selecting calculator type from dropdown
- Block renders the `[frs_calculator]` shortcode with selected type
- Calculator appears inline in the Greenshift-built landing page

---

### **Critical Gap #3: CRM Integration Hooks**
**Status:** ❌ HOOKS EXIST BUT NO LISTENERS

**What Exists:**
- ✅ Hook triggers: `do_action('lrh_calculator_lead_created', $lead_id, $params)` (line 223)
- ✅ Hook triggers: `do_action('lrh_mortgage_lead_created', $lead_id, $params)` (line 402)

**What's Missing:**
- ❌ No `add_action()` listeners registered
- ❌ No CRM integration class
- ❌ No webhook sender
- ❌ No retry logic

**Required Refactor:**
```php
// CREATE: includes/Integrations/CRM.php
class CRM {
  public function init() {
    add_action('lrh_calculator_lead_created', [$this, 'push_to_crm'], 10, 2);
    add_action('lrh_mortgage_lead_created', [$this, 'push_to_crm'], 10, 2);
  }

  public function push_to_crm($lead_id, $params) {
    $settings = get_option('lrh_crm_settings');
    // Send webhook to CRM
    wp_remote_post($settings['webhook_url'], [
      'body' => json_encode($params)
    ]);
  }
}
```

---

### **Critical Gap #4: Partnership Referrals → Leads**
**Status:** ❌ PARTNERSHIPS EXIST BUT DON'T CREATE LEADS

**What Exists:**
- ✅ Partnership creation & management
- ✅ LeadSubmission model has `partnership_id` field
- ✅ Partnership emails integrate with frs-wp-users

**What's Missing:**
- ❌ No "Submit Referral" endpoint that creates a lead
- ❌ Partners can create partnerships but can't send leads
- ❌ No lead routing from partner → assigned LO

**Required Refactor:**
```php
// In Controllers/Partnerships/Actions.php - ADD METHOD:
public function submit_referral(WP_REST_Request $request) {
  $partnership_id = $request->get_param('partnership_id');
  $partnership = Partnership::find($partnership_id);

  // Create lead from referral
  LeadSubmission::create([
    'partnership_id' => $partnership_id,
    'loan_officer_id' => $partnership->loan_officer_id,
    'agent_id' => $partnership->agent_id,
    'lead_source' => 'partner_referral',
    'first_name' => $request->get_param('first_name'),
    // ... other fields
  ]);
}
```

---

### **Critical Gap #5: Guided Tour Integration + Auto-Generate Landing Pages**
**Status:** ❌ EXISTS BUT NOT WIRED TO ONBOARDING & NO AUTO-GENERATION

**What Exists:**
- ✅ `ProfileTour.tsx` component
- ✅ Landing page creation API endpoints
- ✅ Partnership data (LO + Partner relationships)
- ✅ Post types for landing pages (frs_biolink, frs_prequal, etc.)

**What's Missing:**
- ❌ No first-login detection
- ❌ No auto-launch on new user
- ❌ No role-specific tour content
- ❌ No completion tracking
- ❌ **CRITICAL:** No auto-generation of cobranded landing pages during tour
- ❌ No Greenshift template system for LP generation
- ❌ No reusable basic templates (Greenshift blocks)
- ❌ No editable text content system
- ❌ No API call to create landing pages for LO + all their partners

**Landing Page Requirements:**
1. **Built with Greenshift** - Use Greenshift page builder blocks (not hard-coded HTML)
2. **Editable Text Content** - All text must be editable via Greenshift editor
3. **Reusable Templates** - Basic templates should work for different purposes (Biolink, Prequal, Open House, Mortgage LP)
4. **Dynamic Personalization** - Auto-populate LO name, partner name, contact info from profiles

**Required Refactor:**
```typescript
// In PortalLayout.tsx - ADD:
useEffect(() => {
  const hasSeenTour = localStorage.getItem('lrh_tour_completed');
  const userRole = getCurrentUserRole();

  if (!hasSeenTour) {
    setShowTour(true);

    // Auto-generate landing pages during tour
    if (userRole === 'loan_officer') {
      generateCobrandedLandingPages();
    }
  }
}, []);

async function generateCobrandedLandingPages() {
  // Get all partnerships for this LO
  const partnerships = await fetch('/wp-json/lrh/v1/partnerships/lo/{id}');

  // Get LO profile data for personalization
  const loProfile = await fetch('/wp-json/frs-users/v1/profiles/user/{id}');

  // For each partner, create cobranded landing pages from Greenshift templates
  for (const partnership of partnerships) {
    const partnerProfile = await fetch(`/wp-json/frs-users/v1/profiles/${partnership.agent_id}`);

    // Create each LP type from Greenshift template
    await createLandingPageFromTemplate({
      type: 'biolink',
      template: 'greenshift_biolink_template',
      loData: loProfile,
      partnerData: partnerProfile,
      partnership: partnership
    });

    // Same for: prequal, openhouse, mortgage_lp
  }
}
```

**Backend Template System Needed:**
```php
// CREATE: includes/Templates/GreenshiftTemplates.php
class GreenshiftTemplates {
  // Store reusable Greenshift block patterns
  public static function get_biolink_template() {
    return '<!-- wp:greenshift-blocks/... -->'; // Greenshift blocks JSON
  }

  // Merge template with dynamic data (LO name, partner name, etc.)
  public static function merge_template_data($template, $lo_data, $partner_data) {
    // Replace placeholders: {{lo_name}}, {{partner_name}}, {{phone}}, etc.
  }
}
```

---

### **Critical Gap #6: DataKit SDK Integration**
**Status:** ❌ NOT INTEGRATED

**What Exists:**
- ✅ REST API endpoints in `Controllers/`
- ✅ React components with manual `fetch()` calls
- ✅ Eloquent models for database queries

**What's Missing:**
- ❌ DataKit SDK not installed
- ❌ Frontend not using DataKit hooks for data fetching
- ❌ Backend not using DataKit for data views
- ❌ No unified data layer between frontend and backend

**Why DataKit:**
- Unified data management for both React and PHP
- Automatic state management and caching
- Type-safe data fetching
- Built-in loading/error states
- Optimistic updates and mutations

**Required Refactor:**
```bash
# Install DataKit SDK
npm install @usedatakit/sdk
```

```typescript
// Frontend - Replace manual fetch with DataKit hooks
// BEFORE:
const [leads, setLeads] = useState([]);
useEffect(() => {
  fetch('/wp-json/lrh/v1/leads/lo/123')
    .then(res => res.json())
    .then(data => setLeads(data));
}, []);

// AFTER:
import { useQuery } from '@usedatakit/sdk';
const { data: leads, isLoading, error } = useQuery({
  endpoint: '/wp-json/lrh/v1/leads/lo/123'
});
```

```php
// Backend - Use DataKit for data views
// Controllers return DataKit-formatted responses
class Actions {
  public function get_leads_for_lo($request) {
    $leads = LeadSubmission::where('loan_officer_id', $id)->get();

    // Return DataKit-compatible format
    return DataKit::response($leads, [
      'meta' => ['total' => $leads->count()],
      'cache' => 300 // 5 minute cache
    ]);
  }
}
```

---

## 🔌 REFACTORING CHECKLIST (Weekend Sprint)

### **Friday Evening / Saturday Early Morning (2 hours) - FOUNDATION**
- [ ] **DataKit SDK Integration**
  - [ ] Install DataKit SDK: `npm install @usedatakit/sdk`
  - [ ] Set up DataKit provider in React apps (portal, admin)
  - [ ] Create DataKit backend helper class for PHP controllers
  - [ ] Migrate 2-3 example endpoints to use DataKit (test integration)
  - [ ] Document DataKit patterns for team

### **Saturday Morning (4 hours)**
- [ ] **Calculator → Leads Integration**
  - [ ] Add `<LeadCaptureForm>` component (reusable)
  - [ ] Wire to `ResultsCard.tsx` for all 7 calculators
  - [ ] Connect to existing API: `POST /lrh/v1/calculator-leads` (use DataKit)
  - [ ] Test lead creation from calculator

### **Saturday Afternoon (4 hours)**
- [ ] **Landing Page → Calculator Embed (Greenshift)**
  - [ ] Create WordPress shortcode for calculator embed: `[frs_calculator type="..."]`
  - [ ] Create custom Greenshift block for calculator selection
  - [ ] Block renders shortcode with selected calculator type
  - [ ] Ensure React calculator assets enqueue on shortcode render
  - [ ] Test Greenshift LP with embedded calculator

### **Saturday Evening (4 hours)**
- [ ] **CRM Integration Layer**
  - [ ] Create `includes/Integrations/CRM.php`
  - [ ] Register `add_action` listeners for lead hooks
  - [ ] Add webhook sender with retry logic
  - [ ] Create settings page for CRM config

### **Sunday Morning (4 hours)**
- [ ] **Partnership Referrals → Leads**
  - [ ] Add `submit_referral()` method to Partnerships controller
  - [ ] Create referral form in RealtorDashboard
  - [ ] Test lead routing from partner to LO

- [ ] **Guided Tour + Auto-Generate Landing Pages (Greenshift)**
  - [ ] Wire ProfileTour to first-login detection
  - [ ] Create Greenshift template system (reusable block patterns)
  - [ ] Build 4 basic Greenshift templates: Biolink, Prequal, Open House, Mortgage LP
  - [ ] Add dynamic personalization (merge LO + Partner data into templates)
  - [ ] Add auto-generation of cobranded LPs during tour
  - [ ] Ensure all text content is editable via Greenshift editor
  - [ ] Test tour flow with LP generation

### **Sunday Afternoon (4 hours)**
- [ ] **Multisite Setup**
  - [ ] Create new Local multisite site
  - [ ] Migrate database from current hub21
  - [ ] Create 4 subsites
  - [ ] Network-activate plugins

### **Sunday Evening (4 hours)**
- [ ] **End-to-End Testing**
  - [ ] LO creates LP with calculator → Consumer submits → Lead appears in dashboard
  - [ ] Partner submits referral → LO receives lead
  - [ ] Lead pushes to CRM webhook
  - [ ] Test on all brand subsites

---

## 📊 DATA FLOW DIAGRAMS

### **Current State (Disconnected)**
```
Landing Pages  ❌  Calculators
     ❌               ❌
Lead Submissions  ❌  CRM Webhooks
     ❌
Partnerships (no lead creation)
```

### **Target State (Connected with DataKit)**
```
Landing Pages (Greenshift)
    ↓ (embed via shortcode)
Mortgage Calculators (React)
    ↓ (submit LeadCaptureForm - DataKit mutation)
POST /lrh/v1/calculator-leads (DataKit endpoint)
    ↓
LeadSubmission Model (wp_lead_submissions)
    ↓                           ↓
Loan Officer Dashboard    CRM Webhook (do_action hook)
(DataKit query)               ↓
    ↓                    Salesforce/HubSpot/FUB
Email Notification
```

**DataKit Data Flow:**
- React: `useMutation()` for lead submission
- PHP: `DataKit::response()` for API responses
- Automatic caching, loading states, error handling
- Type-safe data transfer

### **Partnership Referral Flow (New)**
```
Partner Dashboard
    ↓ (submit referral form)
POST /lrh/v1/partnerships/referral
    ↓
LeadSubmission::create([
  partnership_id, loan_officer_id, agent_id,
  lead_source => 'partner_referral'
])
    ↓
LO Dashboard (filtered by partnership)
    ↓
Shared view (partner sees status updates)
```

---

## 🎯 USER JOURNEYS & SUCCESS METRICS

### **Partner First-Time Login Journey**
1. Partner logs in for the first time
2. Portal tour automatically launches (guided onboarding)
3. **During the tour:** Auto-generate all cobranded landing pages for the loan officer with their loan originator partners
4. Tour walks through:
   - Landing pages created
   - How to submit referrals
   - Lead dashboard access
   - Partnership features

### **Success Metrics After Weekend Sprint:**

1. **Consumer Journey:**
   - Visit LP → Use calculator → Submit info → Lead appears in LO dashboard

2. **Partner Journey:**
   - Login to portal → Submit referral → Lead routes to LO → See status

3. **CRM Integration:**
   - Lead created → Webhook fires → CRM receives data → Retry on failure

4. **Multisite:**
   - 4 brand subsites working
   - Brand-specific gradients/colors
   - Plugin network-activated

---

## 📝 NOTES

- All backend APIs **already exist** - this is purely a **frontend → backend wiring** task
- No new database tables needed
- No new models needed
- All Eloquent relationships already defined
- Email templates already exist
- This is **90% configuration, 10% new code**

### **Landing Page Architecture**
- **Page Builder:** Greenshift (not custom React editor)
- **Templates:** Reusable Greenshift block patterns stored as WordPress post meta or options
- **Content:** All text editable via Greenshift editor (not hard-coded)
- **Personalization:** Dynamic merge of LO + Partner profile data into template placeholders
- **Template Types:** 4 base templates (Biolink, Prequal, Open House, Mortgage LP)
- **Reusability:** Same template can be used for multiple purposes by changing text content

### **Data Management Architecture**
- **SDK:** DataKit SDK (https://github.com/UseDataKit/SDK)
- **Frontend:** Use DataKit for all React data fetching/state management
- **Backend:** Use DataKit for PHP data views and API responses
- **Purpose:** Unified data layer for both frontend and backend
- **Integration Points:**
  - Replace manual `fetch()` calls in React with DataKit hooks
  - Use DataKit for dashboard data views (leads, partnerships, landing pages)
  - Use DataKit for form submissions and mutations
  - Backend controllers return DataKit-formatted responses

**✅ COMPLETED - DataKit SDK Integration (2025-11-22 23:15)**

**Installation Status - ALL 3 PLUGINS COMPLETE:**
- ✅ **frs-lrg:** SDK installed, autoloaded, helper class created & initialized ✅
- ✅ **frs-wp-users:** SDK installed, autoloaded, helper class created & initialized ✅
- ✅ **frs-buddypress-integration:** SDK installed, autoloaded, helper class created & initialized ✅

**What Was Done:**

**1. SDK Installation (All 3 Plugins)**
   - Cloned DataKit SDK from GitHub into `libs/datakit/` in each plugin
   - Ran `composer install --no-dev` to install dependencies
   - Total installs: 3 (one per plugin)

**2. Autoloader Integration (Fixed Conflict)**
   - Added conditional DataKit autoloader with `class_exists()` check to prevent duplicate loading
   - Only first plugin loads DataKit, others skip if already loaded
   - Files modified:
     - `frs-lrg/lending-resource-hub.php` (line 21-26)
     - `frs-wp-users/frs-wp-users.php` (line 28-33)
     - `frs-buddypress-integration/frs-buddypress-integration.php` (line 35-40)

**3. Helper Classes Created (All 3 Plugins)**

**frs-lrg:** `LendingResourceHub\Core\DataKit`
   - Location: `frs-lrg/includes/Core/DataKit.php`
   - DataViews: `create_leads_dataview()` - Calculator leads table
   - Shortcodes: `[datakit_leads]`
   - Initialized: `plugin.php` line 77-79

**frs-wp-users:** `FRSUsers\Core\DataKit`
   - Location: `frs-wp-users/includes/Core/DataKit.php`
   - DataViews:
     - `create_profiles_admin_dataview()` - Full admin table (all 51 fields)
     - `create_profiles_directory_dataview()` - Public grid view (active only)
   - Shortcodes: `[frs_profiles_admin]`, `[frs_profiles_directory]`
   - Initialized: `plugin.php` line 86-89

**frs-buddypress-integration:** `FRSBuddyPress\Core\DataKit`
   - Location: `frs-buddypress-integration/includes/Core/DataKit.php`
   - DataViews:
     - `create_activity_dataview()` - Activity stream list view (REAL BP DATA)
     - `create_members_dataview()` - Members grid view (REAL BP DATA + FRS profiles)
   - Shortcodes: `[frs_bp_activity]`, `[frs_bp_members]`
   - Initialized: `frs-buddypress-integration.php` line 128-131
   - **Uses Real Data:** Queries `bp_activity_get()` and `bp_core_get_users()`
   - **Profile Integration:** Pulls member_type from FRS User Profiles Eloquent model

**4. Available Shortcodes:**
```
# Lending Resource Hub (frs-lrg)
[datakit_leads]               - Calculator leads table

# User Profiles (frs-wp-users)
[frs_profiles_admin]          - Full admin profile table (requires manage_options)
[frs_profiles_directory]     - Public profile directory (grid)

# BuddyPress Integration
[frs_bp_activity]             - Activity stream (list)
[frs_bp_members]              - Member directory (grid)
```

**5. Usage Pattern:**
```php
// In PHP templates or admin pages
use LendingResourceHub\Core\DataKit;
$dataview = DataKit::get_instance()->create_leads_dataview();
echo DataKit::get_instance()->render_dataview( $dataview );

// In Gutenberg blocks or shortcodes
[datakit_leads]
[frs_profiles_directory]
```

**BuddyPress Integration Benefits:**
- ✅ Activity stream with real BP data (`bp_activity_get()`)
- ✅ Member directory with real BP users (`bp_core_get_users()`)
- ✅ Integrates FRS profile data (pulls `select_person_type` from Profile model)
- ✅ Shows avatars, last active times, activity content
- ✅ Filtering by activity type (updates, comments, profile changes, etc.)
- ✅ Can replace default BP member directory and activity loop (optional hooks provided)

**What's Next (Future Enhancements):**
- ✅ **frs-buddypress-integration:** Already uses real BP data
- ⏳ **frs-wp-users:** Replace mock data with real Eloquent Profile queries
- ⏳ **frs-lrg:** Replace mock data with real LeadSubmission Eloquent queries
- ⏳ Create custom DataSources that wrap REST API endpoints for AJAX filtering
- ⏳ Add bulk actions (activate, deactivate, delete)
- ⏳ Add "View" action to open profile detail modal
- ⏳ Migrate React admin components to use DataKit hooks
- ⏳ Wire DataKit DataViews into existing admin pages

---

## 🚀 READY TO START

All research complete. Code audit done. Refactoring plan documented.

**Next Step:** Begin multisite conversion while user creates new Local site.
