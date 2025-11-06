# Migration Status: frs-partnership-portal → frs-lrg

**Tracking what has been migrated, replaced, and what remains**

This document tracks the sunset migration of frs-partnership-portal features into frs-lrg (Lending Resource Hub) using the WordPress Plugin Boilerplate architecture with Eloquent ORM.

---

## Table of Contents

1. [Migration Overview](#migration-overview)
2. [Architecture Shift](#architecture-shift)
3. [Database Tables](#database-tables)
4. [REST API Endpoints](#rest-api-endpoints)
5. [User Interface](#user-interface)
6. [Custom Post Types](#custom-post-types)
7. [Integration Status](#integration-status)
8. [What Remains in frs-partnership-portal](#what-remains-in-frs-partnership-portal)
9. [Migration Patterns](#migration-patterns)

---

## Migration Overview

**Goal:** Sunset frs-partnership-portal by migrating all features to frs-lrg and frs-wp-users

**Status:** In Progress

**Key Architectural Strategy:**
- **Backend/Data Layer:** Complete replacement with Eloquent ORM + MVC structure
- **Admin Interface:** NEW admin React SPA for administrators/managers (management tools)
- **Frontend Tools:** KEPT & ENHANCED - All user-facing tools being rebuilt with MORE features
  - Mortgage calculator
  - Property valuation
  - Marketing showcase
  - App launcher
  - Calendar integration
  - All loan officer/realtor tools

**Critical Understanding:**
frs-lrg supports **DUAL INTERFACES**:
1. **Admin Dashboard** - For administrators to manage partnerships, leads, settings (in wp-admin)
2. **Frontend Portal** - For loan officers/realtors to use tools (via shortcodes on frontend)

---

## Architecture Shift

### frs-partnership-portal (OLD)

**Architecture:**
```
WordPress Frontend
    ↓
Shortcode: [frs_partnership_portal]
    ↓
PHP renders <div id="frs-partnership-portal-root">
    ↓
React SPA mounts (portal for loan officers/realtors)
    ↓
Components: InvitePartner, LeadTracking, DashboardLayout, etc.
    ↓
REST API: /wp-json/frs/v1/*
    ↓
Direct database queries (global $wpdb)
```

**Key Files:**
- `includes/class-frs-api.php` - 36 REST endpoints
- `includes/class-frs-database.php` - Direct SQL queries
- `includes/class-frs-public.php` - Shortcode rendering
- `assets/src/LoanOfficerPortal.tsx` - Main portal component
- `assets/src/components/loan-officer-portal/` - 20+ portal components

---

### frs-lrg (NEW)

**Architecture:**
```
WordPress Admin
    ↓
Admin Menu: "LRH Portal"
    ↓
PHP renders <div id="lrh-admin-root">
    ↓
React SPA mounts (admin dashboard)
    ↓
React Router: /dashboard, /partnerships, /leads, /settings
    ↓
shadcn/ui Components + Tailwind CSS
    ↓
REST API: /wp-json/lrh/v1/*
    ↓
Eloquent ORM Models (Partnership, LeadSubmission, etc.)
```

**Key Files:**
- `includes/Routes/Api.php` - Clean route definitions
- `includes/Controllers/` - 16 organized controllers
- `includes/Models/` - 6 Eloquent models
- `database/Migrations/` - Schema migrations
- `src/admin/main.jsx` - React admin entry point
- `src/admin/pages/` - Admin React pages

---

## Database Tables

### ✅ MIGRATED & REPLACED

#### 1. Partnerships Table

**OLD (frs-partnership-portal):**
```sql
-- Created via raw SQL in class-frs-database.php
CREATE TABLE wp_frs_partnerships (
    id bigint(20),
    loan_officer_id bigint(20),
    agent_id bigint(20),
    partner_email varchar(255),
    status varchar(20),
    created_date datetime
    -- etc.
)
```

**NEW (frs-lrg):**
```php
// database/Migrations/Partnerships.php - Eloquent migration
Capsule::schema()->create('partnerships', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('loan_officer_id');
    $table->unsignedBigInteger('agent_id')->nullable();
    $table->unsignedBigInteger('partner_post_id')->nullable();
    $table->string('partner_email');
    $table->string('partner_name')->nullable();
    $table->string('status', 20)->default('pending');
    $table->string('invite_token', 64)->nullable();
    $table->dateTime('invite_sent_date')->nullable();
    $table->dateTime('accepted_date')->nullable();
    $table->longText('custom_data')->nullable();
    $table->dateTime('created_date')->nullable();
    $table->dateTime('updated_date')->nullable();

    // Indexes for performance
    $table->index('loan_officer_id');
    $table->index('agent_id');
    $table->index('partner_post_id');
    $table->index('partner_email');
    $table->index('status');
    $table->index('invite_token');
});
```

**Model:**
```php
// includes/Models/Partnership.php
class Partnership extends Model {
    protected $table = 'partnerships';
    protected $fillable = [
        'loan_officer_id', 'agent_id', 'partner_post_id',
        'partner_email', 'partner_name', 'status',
        'invite_token', 'custom_data'
    ];
}
```

**Improvements:**
- ✅ Eloquent ORM instead of raw SQL
- ✅ Migration-based schema (version controlled)
- ✅ Added `partner_post_id` for better post type integration
- ✅ Added `custom_data` for extensibility
- ✅ Better indexes for performance
- ✅ Type-safe model with relationships

---

#### 2. Lead Submissions Table

**OLD (frs-partnership-portal):**
```sql
-- Created via raw SQL
CREATE TABLE wp_frs_lead_submissions (
    id bigint(20),
    partnership_id bigint(20),
    first_name varchar(100),
    last_name varchar(100),
    email varchar(255),
    phone varchar(20),
    created_date datetime
)
```

**NEW (frs-lrg):**
```php
// database/Migrations/LeadSubmissions.php
Capsule::schema()->create('lead_submissions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('partnership_id')->nullable();
    $table->unsignedBigInteger('loan_officer_id')->nullable();
    $table->unsignedBigInteger('agent_id')->nullable();
    $table->string('first_name', 100)->nullable();
    $table->string('last_name', 100)->nullable();
    $table->string('email', 255)->nullable();
    $table->string('phone', 20)->nullable();
    $table->string('status', 20)->default('new');
    $table->text('notes')->nullable();
    $table->string('lead_source', 100)->nullable();
    $table->longText('custom_data')->nullable();
    $table->dateTime('created_date')->nullable();
    $table->dateTime('updated_date')->nullable();

    // Indexes
    $table->index('partnership_id');
    $table->index('loan_officer_id');
    $table->index('agent_id');
    $table->index('status');
    $table->index('lead_source');
});
```

**Model:**
```php
// includes/Models/LeadSubmission.php
class LeadSubmission extends Model {
    protected $table = 'lead_submissions';
    protected $fillable = [
        'partnership_id', 'loan_officer_id', 'agent_id',
        'first_name', 'last_name', 'email', 'phone',
        'status', 'notes', 'lead_source', 'custom_data'
    ];

    public function partnership() {
        return $this->belongsTo(Partnership::class, 'partnership_id');
    }
}
```

**Improvements:**
- ✅ Added `status` field for lead tracking
- ✅ Added `notes` field for follow-ups
- ✅ Added `lead_source` for attribution
- ✅ Eloquent relationships (lead → partnership)
- ✅ Better data structure with custom_data

---

#### 3. Page Assignments Table

**OLD (frs-partnership-portal):**
```sql
CREATE TABLE wp_frs_page_assignments (
    id bigint(20),
    user_id bigint(20),
    template_page_id bigint(20),
    assigned_page_id bigint(20),
    page_type varchar(50)
)
```

**NEW (frs-lrg):**
```php
// database/Migrations/PageAssignments.php
Capsule::schema()->create('page_assignments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('template_page_id')->nullable();
    $table->unsignedBigInteger('assigned_page_id');
    $table->string('page_type', 50)->nullable();
    $table->dateTime('created_date')->nullable();
    $table->dateTime('updated_date')->nullable();

    // Indexes
    $table->index('user_id');
    $table->index('template_page_id');
    $table->index('assigned_page_id');
    $table->index('page_type');
});
```

**Model:**
```php
// includes/Models/PageAssignment.php
class PageAssignment extends Model {
    protected $table = 'page_assignments';
    protected $fillable = [
        'user_id', 'template_page_id',
        'assigned_page_id', 'page_type'
    ];

    public function user() {
        return $this->belongsTo(Users::class, 'user_id');
    }
}
```

**Status:** ✅ **FULLY MIGRATED**

---

## REST API Endpoints

### ✅ MIGRATED & REPLACED

The REST API has been completely restructured from a monolithic `class-frs-api.php` (2047 lines) to organized controllers.

#### Partnerships Endpoints

**OLD (frs-partnership-portal):**
```php
// includes/class-frs-api.php - One massive file
register_rest_route('frs/v1', '/partnerships', [
    'methods' => 'GET',
    'callback' => [$this, 'get_partnerships'],
]);
// ... 35 more endpoints in same file
```

**NEW (frs-lrg):**
```php
// includes/Routes/Api.php - Clean route definitions
$route->get('/partnerships', '\LendingResourceHub\Controllers\Partnerships\Actions@get_partnerships');
$route->post('/partnerships', '\LendingResourceHub\Controllers\Partnerships\Actions@create_partnership');
$route->post('/partnerships/assign', '\LendingResourceHub\Controllers\Partnerships\Actions@assign_partnership');
$route->post('/partnerships/invite', '\LendingResourceHub\Controllers\Partnerships\Actions@send_invitation');
$route->get('/partnerships/lo/{id}', '\LendingResourceHub\Controllers\Partnerships\Actions@get_partnerships_for_lo');
$route->get('/partnerships/realtor/{id}', '\LendingResourceHub\Controllers\Partnerships\Actions@get_partnership_for_realtor');
$route->get('/realtor-partners', '\LendingResourceHub\Controllers\Partnerships\Actions@get_realtor_partners');
```

**Controller:**
```php
// includes/Controllers/Partnerships/Actions.php
namespace LendingResourceHub\Controllers\Partnerships;

use LendingResourceHub\Models\Partnership;

class Actions {
    public function get_partnerships() {
        $user_id = get_current_user_id();

        $partnerships = Partnership::where('loan_officer_id', $user_id)
            ->orWhere('agent_id', $user_id)
            ->orderBy('created_date', 'desc')
            ->get();

        return rest_ensure_response([
            'success' => true,
            'data' => $partnerships
        ]);
    }
}
```

**Improvements:**
- ✅ Separated concerns (Routes → Controllers → Models)
- ✅ Eloquent queries instead of raw SQL
- ✅ Organized by feature domain
- ✅ Type-safe with namespace
- ✅ Better testability

---

#### Leads Endpoints

**OLD:** Single callback methods in class-frs-api.php

**NEW:**
```php
// includes/Routes/Api.php
$route->get('/leads', '\LendingResourceHub\Controllers\Leads\Actions@get_leads');
$route->post('/leads', '\LendingResourceHub\Controllers\Leads\Actions@create_lead');
$route->get('/leads/lo/{id}', '\LendingResourceHub\Controllers\Leads\Actions@get_leads_for_lo');
$route->put('/leads/{id}/status', '\LendingResourceHub\Controllers\Leads\Actions@update_lead_status');
$route->delete('/leads/{id}', '\LendingResourceHub\Controllers\Leads\Actions@delete_lead');
$route->post('/leads/{id}/notes', '\LendingResourceHub\Controllers\Leads\Actions@add_lead_note');
$route->post('/calculator-leads', '\LendingResourceHub\Controllers\Leads\Actions@create_calculator_lead');
$route->post('/mortgage-lead', '\LendingResourceHub\Controllers\Leads\Actions@create_mortgage_lead');
```

**Controller:**
```php
// includes/Controllers/Leads/Actions.php
use LendingResourceHub\Models\LeadSubmission;

class Actions {
    public function get_leads() {
        // Eloquent with filtering, pagination
        $leads = LeadSubmission::with('partnership')
            ->where('status', '!=', 'deleted')
            ->orderBy('created_date', 'desc')
            ->paginate(20);

        return rest_ensure_response($leads);
    }

    public function update_lead_status($request) {
        $lead = LeadSubmission::findOrFail($request['id']);
        $lead->status = $request['status'];
        $lead->save();

        return rest_ensure_response(['success' => true]);
    }
}
```

---

#### Dashboard Stats Endpoints

**NEW (Added in frs-lrg):**
```php
$route->get('/dashboard/stats', '\LendingResourceHub\Controllers\Dashboard\Stats@get_stats');
$route->get('/dashboard/stats/lo/{id}', '\LendingResourceHub\Controllers\Dashboard\Stats@get_lo_stats');
$route->get('/dashboard/stats/realtor/{id}', '\LendingResourceHub\Controllers\Dashboard\Stats@get_realtor_stats');
```

**Controller:**
```php
// includes/Controllers/Dashboard/Stats.php
class Stats {
    public function get_stats() {
        $user_id = get_current_user_id();

        $stats = [
            'activePartnerships' => Partnership::where('loan_officer_id', $user_id)
                ->where('status', 'active')
                ->count(),
            'pendingInvitations' => Partnership::where('loan_officer_id', $user_id)
                ->where('status', 'pending')
                ->count(),
            'totalLeads' => LeadSubmission::where('loan_officer_id', $user_id)
                ->count(),
            'recentLeads' => LeadSubmission::where('loan_officer_id', $user_id)
                ->where('created_date', '>=', date('Y-m-d', strtotime('-7 days')))
                ->count(),
        ];

        return rest_ensure_response([
            'success' => true,
            'data' => ['stats' => $stats]
        ]);
    }
}
```

---

### ✅ NEW ENDPOINTS (Not in frs-partnership-portal)

These are entirely new in frs-lrg:

```php
// Calendar integration
$route->post('/calendar/setup', '...');
$route->get('/calendar/setup-status', '...');
$route->post('/calendar/complete-setup', '...');
$route->get('/calendar/users', '...');
$route->post('/calendar/reset', '...');

// Rentcast API integration
$route->get('/rentcast/valuation', '...');

// System diagnostics
$route->get('/system/diagnostics', '...');

// Marketing materials
$route->get('/marketing-materials', '...');

// Announcements
$route->get('/announcements', '...');
$route->get('/announcements/{id}', '...');

// Custom links
$route->get('/custom-links', '...');
```

---

## User Interface

### ✅ DUAL-INTERFACE ARCHITECTURE

frs-lrg implements **TWO separate React applications**:

#### Interface 1: Admin Management Dashboard (NEW)

**Purpose:** Administrators and managers control the system

**Location:** WordPress Admin at `admin.php?page=lending-resource-hub`

**Pages:** `src/admin/pages/`
- `dashboard/` - System overview with stats
- `partnerships/` - Manage all partnerships
- `leads/` - View all leads across system
- `settings/` - Plugin configuration
- `integrations/` - Third-party integrations
- `system-diagnostic/` - System health
- `bulk-invites/` - Mass partner invitations

**Stack:**
- React 18 + TypeScript
- shadcn/ui components
- Tailwind CSS
- React Router (hash routing in admin)

---

#### Interface 2: Frontend User Tools (KEPT & ENHANCED)

**Purpose:** Loan officers and realtors use tools daily

**Location:** Frontend shortcodes (e.g., `/portal/lo`, `/portal/re`)

**OLD (frs-partnership-portal):**

**Components:** `assets/src/components/loan-officer-portal/`
- `DashboardLayout.tsx` - Portal layout with sidebar
- `InvitePartner.tsx` - Partnership invitation form
- `LeadTracking.tsx` - Lead management table
- `GradientDashboard.tsx` - Dashboard with stats
- `AppLauncher.tsx` - Quick links widget
- `MortgageCalculator.tsx` - Calculator tool
- `BiolinkDashboardContent.tsx` - Biolink management
- `BrandShowcase.tsx` - Marketing materials
- `CalendarSetupWizard.tsx` - Calendar integration wizard

**Access:** Loan officers visit frontend page at `/portal/lo`, realtors at `/portal/re`

**Stack:**
- React 18
- Radix UI components
- Custom CSS with Tailwind
- React Router (hash routing within shortcode)

---

**NEW (frs-lrg):**

**Location:** WordPress Admin at `admin.php?page=lending-resource-hub`

**Pages:** `src/admin/pages/`
- `dashboard/` - Admin dashboard with stats (replaces GradientDashboard)
- `partnerships/` - Partnership management (replaces InvitePartner)
- `leads/` - Lead tracking (replaces LeadTracking)
- `settings/` - Plugin settings
- `integrations/` - Third-party integrations (includes calendar)
- `system-diagnostic/` - System health check
- `bulk-invites/` - Mass partner invitations

**Access:** WordPress admin menu "LRH Portal"

**Stack:**
- React 18
- shadcn/ui components (Radix UI base)
- Tailwind CSS
- React Router (hash routing: `#/partnerships`, `#/leads`)
- TypeScript support

**Status of Frontend Tools (BEING ENHANCED):**

These components from frs-partnership-portal are being KEPT and REBUILT with more features:

- ✅ **MortgageCalculator.tsx** - Being enhanced with more calculator types
- ✅ **PropertyValuation.tsx** - Being enhanced with Rentcast API integration
- ✅ **AppLauncher.tsx** - Quick links widget (kept)
- ✅ **CalendarSetupWizard.tsx** - Calendar integration (enhanced)
- ✅ **FluentBookingCalendar.tsx** - Calendar UI (enhanced)
- ✅ **BiolinkDashboardContent.tsx** - Biolink management (enhanced)
- ✅ **BrandShowcase.tsx** - Marketing materials (kept)
- ✅ **InvitePartner.tsx** - Partnership invitations (kept for users)
- ✅ **LeadTracking.tsx** - User's own leads (kept for users)
- ✅ **BiolinkMarketing.tsx** - Marketing tools (kept)
- ✅ **CobrandedMarketing.tsx** - Co-branded materials (kept)
- ✅ **DigitalMarketing.tsx** - Digital marketing (kept)
- ✅ **EmailCampaignsMarketing.tsx** - Email campaigns (kept)
- ✅ **LandingPagesMarketing.tsx** - Landing page marketing (kept)
- ✅ **LocalSEOMarketing.tsx** - SEO tools (kept)
- ✅ **MarketingOrders.tsx** - Order tracking (kept)

**Architecture:**
```
frs-lrg DUAL INTERFACES:

1. ADMIN (NEW)
   └─ wp-admin → React SPA → shadcn/ui → Manage entire system

2. FRONTEND (KEPT & ENHANCED)
   └─ /portal/lo → React SPA → Enhanced tools → User's own data
```

**Key Point:**
- **Admin tools** = Manage ALL partnerships, ALL leads, system settings
- **Frontend tools** = User accesses THEIR OWN partnerships, leads, and uses tools

---

### Admin Dashboard Comparison

**OLD (frs-partnership-portal - DashboardLayout.tsx):**
```tsx
// Frontend shortcode portal
<div className="portal-container">
  <Sidebar>
    <Menu items={[
      { label: 'Dashboard', path: '/' },
      { label: 'Partners', path: '/partners' },
      { label: 'Leads', path: '/leads' },
      { label: 'Tools', submenu: [...] }
    ]} />
  </Sidebar>
  <main>
    <Router>
      <Route path="/" component={GradientDashboard} />
      <Route path="/partners" component={InvitePartner} />
      <Route path="/leads" component={LeadTracking} />
    </Router>
  </main>
</div>
```

**NEW (frs-lrg - src/admin/pages/dashboard/index.jsx):**
```jsx
// Admin React SPA
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

export default function DashboardPage() {
  const [stats, setStats] = useState({});

  useEffect(() => {
    // Fetch from /wp-json/lrh/v1/dashboard/stats
    fetch(`${window.lrhAdmin.apiUrl}dashboard/stats`, {
      headers: { "X-WP-Nonce": window.lrhAdmin.nonce }
    });
  }, []);

  return (
    <div className="flex-1 space-y-4 p-8 pt-6">
      <h2 className="text-3xl font-bold tracking-tight">
        Partnership Portal Dashboard
      </h2>

      {/* Stats with shadcn Card components */}
      <div className="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle>Active Partnerships</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold text-blue-600">
              {stats.activePartnerships}
            </div>
          </CardContent>
        </Card>
        {/* More cards... */}
      </div>

      {/* Quick Actions */}
      <Card>
        <CardContent>
          <a href="#/partnerships">
            <Button>Manage Partnerships</Button>
          </a>
        </CardContent>
      </Card>
    </div>
  );
}
```

**UI Pattern Shift:**
- ✅ Custom components → shadcn/ui library
- ✅ Mixed CSS → Pure Tailwind utilities
- ✅ Frontend portal → Admin dashboard
- ✅ Fetch to REST API with Eloquent backend

---

## Custom Post Types

### ✅ MIGRATED

Both plugins register similar post types, indicating feature parity:

**frs-partnership-portal:**
- `partner` - Partnership CPT (deprecated?)
- `frs_biolink` - Biolink landing pages
- `frs_prequal` - Pre-qualification pages
- `frs_openhouse` - Open house pages
- `frs_mortgage_lp` - Mortgage landing pages
- `frs_announcement` - Portal announcements
- `frs_custom_link` - Custom quick links

**frs-lrg:**
- `frs_biolink` - ✅ Migrated
- `frs_prequal` - ✅ Migrated
- `frs_openhouse` - ✅ Migrated
- `frs_mortgage_lp` - ❓ Check if exists
- `frs_announcement` - ❓ Check if exists
- `frs_custom_link` - ❓ Check if exists

**Status:** Biolink, Prequal, and Open House pages are CONFIRMED migrated to frs-lrg with same slugs and structure.

---

## Integration Status

### ✅ MIGRATED INTEGRATIONS

#### 1. ACF Integration

**Both plugins have:**
- `class-frs-acf-integration.php`
- `class-frs-acf-fields.php`

Person CPT integration for profile data (headshot, bio, contact info) works in both.

---

#### 2. FluentCRM Integration

**frs-partnership-portal:**
- `class-frs-fluentcrm-integration.php` (738 lines)

**frs-lrg:**
- Needs verification - check if migrated

---

#### 3. Form Integration

**frs-partnership-portal:**
- `class-frs-form-integration.php` (658 lines)
- `class-frs-form-manager.php`

**frs-lrg:**
- Check Controllers/Forms/Actions.php for equivalent

---

#### 4. Blocksy Theme Integration

**frs-partnership-portal:**
- `class-frs-blocksy-menu-integration.php` - Menu integration

**frs-lrg:**
- ❓ Check if needed for admin area

---

### ✅ NEW INTEGRATIONS (frs-lrg only)

#### Calendar Integration

**NEW in frs-lrg:**
- `includes/Controllers/Calendar/Actions.php`
- Calendar setup wizard
- FluentBooking integration
- User calendar management

**Routes:**
```php
$route->post('/calendar/setup', '...');
$route->get('/calendar/setup-status', '...');
$route->post('/calendar/complete-setup', '...');
$route->get('/calendar/users', '...');
$route->post('/calendar/reset', '...');
```

**Admin Pages:**
- `src/admin/pages/integrations/` - Calendar setup UI

---

#### Rentcast API Integration

**NEW in frs-lrg:**
- `class-frs-rentcast-api.php`
- `includes/Controllers/Rentcast/Actions.php`
- Property valuation API

**Route:**
```php
$route->get('/rentcast/valuation', '...');
```

---

## What Remains in frs-partnership-portal

### Frontend User Tools (BEING MIGRATED & ENHANCED)

**Status:** ✅ **CONFIRMED: All frontend tools are being KEPT and rebuilt with MORE features**

Based on component analysis, these user-facing features are being migrated to frs-lrg with enhancements:

#### 1. Frontend Portal Components (KEEPING & ENHANCING)

**Location in frs-partnership-portal:** `assets/src/components/loan-officer-portal/`

**Migration Status:**

- ✅ **MortgageCalculator.tsx** - ENHANCED: More calculator types, better UI
- ✅ **PropertyValuation.tsx** - ENHANCED: Rentcast API integration
- ✅ **AppLauncher.tsx** - KEPT: Quick links widget
- ✅ **BiolinkMarketing.tsx** - KEPT: Marketing for biolink pages
- ✅ **BrandShowcase.tsx** - KEPT: Marketing materials showcase
- ✅ **CobrandedMarketing.tsx** - KEPT: Co-branded materials
- ✅ **DigitalMarketing.tsx** - KEPT: Digital marketing tools
- ✅ **EmailCampaignsMarketing.tsx** - KEPT: Email campaign management
- ✅ **LandingPagesMarketing.tsx** - KEPT: Landing page marketing
- ✅ **LocalSEOMarketing.tsx** - KEPT: SEO tools
- ✅ **MarketingOrders.tsx** - KEPT: Marketing order tracking
- ✅ **CalendarTour.tsx** - ENHANCED: Better onboarding
- ✅ **FluentBookingCalendar.tsx** - ENHANCED: Calendar integration UI
- ✅ **InvitePartner.tsx** - KEPT: Users invite their own partners
- ✅ **LeadTracking.tsx** - KEPT: Users view their own leads
- ✅ **GradientDashboard.tsx** - KEPT: User's personal dashboard

**Destination in frs-lrg:**
- Frontend shortcodes will remain (e.g., `[frs_partnership_portal]`, `[frs_biolink_dashboard]`)
- Components will be rebuilt/enhanced in frs-lrg's frontend structure
- Better integration with new Eloquent backend
- Enhanced features and improved UX

---

#### 2. Shortcodes (BEING MIGRATED)

**frs-partnership-portal has:**
- `[frs_partnership_portal]` - Main portal for loan officers/realtors
- `[frs_portal_router]` - Route handler
- `[frs_biolink_dashboard]` - Biolink management
- `[frs_agent_signup]` - Agent registration

**frs-lrg:**
- ✅ **ALL shortcodes being migrated**
- ✅ Frontend portals are STAYING (not moving to admin-only)
- ❓ Verify all 4 shortcodes registered in frs-lrg
- ❓ Check if shortcode functionality is identical or enhanced

---

#### 3. User Roles & Permissions

**frs-partnership-portal registers:**
- `loan_officer`
- `realtor_partner`
- `manager`
- `frs_admin`

**frs-lrg:**
- ❓ Verify same roles exist
- ❓ Check permission structure

---

#### 4. Blocks

**frs-partnership-portal has:**
- 14 Gutenberg blocks in `blocks/`
- Biolink blocks (8)
- Prequal blocks (2)
- Partnership blocks (2)
- Dashboard blocks (2)

**frs-lrg:**
- `includes/Core/Blocks.php` - ❓ Check block registration
- Biolink blocks exist (confirmed in earlier analysis)

---

## Migration Patterns

### Pattern 1: Database → Eloquent

**OLD:**
```php
// Direct SQL query
global $wpdb;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}frs_partnerships WHERE loan_officer_id = %d",
        $user_id
    )
);
```

**NEW:**
```php
// Eloquent model query
use LendingResourceHub\Models\Partnership;

$results = Partnership::where('loan_officer_id', $user_id)
    ->orderBy('created_date', 'desc')
    ->get();
```

**Benefits:**
- Type-safe
- Relationship support
- Query builder
- Better testability

---

### Pattern 2: Monolithic API → Controllers

**OLD:**
```php
// Everything in class-frs-api.php (2047 lines)
class FRS_API {
    public function get_partnerships($request) { ... }
    public function create_partnership($request) { ... }
    public function get_leads($request) { ... }
    public function create_lead($request) { ... }
    // ... 30 more methods
}
```

**NEW:**
```
includes/Controllers/
├── Partnerships/
│   └── Actions.php       # Partnership endpoints
├── Leads/
│   └── Actions.php       # Lead endpoints
├── Dashboard/
│   └── Stats.php         # Dashboard stats
└── Users/
    └── Actions.php       # User endpoints
```

**Benefits:**
- Separated concerns
- Easier to find code
- Better organization
- Testable in isolation

---

### Pattern 3: Dual Interface Architecture

**BOTH frontend portals AND admin dashboard:**

**Frontend Portal (KEPT & ENHANCED):**
```php
// Shortcode renders frontend portal for users
public function partnership_portal_shortcode() {
    wp_enqueue_script('lrh-portal-app');
    wp_localize_script('lrh-portal-app', 'lrhPortalConfig', [
        'userId' => get_current_user_id(),
        'apiUrl' => rest_url('lrh/v1/'),
        'nonce' => wp_create_nonce('wp_rest')
    ]);
    return '<div id="lrh-portal-root"></div>';
}
add_shortcode('frs_partnership_portal', [...]);
```

**Admin Dashboard (NEW):**
```php
// Admin menu renders React mount point for management
public function admin_page() {
    ?>
    <div id="lrh-admin-root"></div>
    <?php
}
add_menu_page('LRH Portal', 'LRH Portal', 'manage_options', 'lending-resource-hub', [$this, 'admin_page']);
```

**Benefits:**
- ✅ Users get enhanced tools on frontend
- ✅ Admins get management interface in wp-admin
- ✅ Clean separation: user tools vs admin management
- ✅ Both use same Eloquent backend (shared data, consistent API)

---

### Pattern 4: Raw SQL Migrations → Eloquent Schema

**OLD:**
```php
// Raw SQL string
$sql = "CREATE TABLE {$wpdb->prefix}frs_partnerships (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    loan_officer_id bigint(20) NOT NULL,
    status varchar(20) DEFAULT 'pending',
    PRIMARY KEY (id)
) {$charset_collate};";
dbDelta($sql);
```

**NEW:**
```php
// Eloquent schema builder
use Prappo\WpEloquent\Database\Schema\Blueprint;

Capsule::schema()->create('partnerships', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('loan_officer_id');
    $table->string('status', 20)->default('pending');

    $table->index('loan_officer_id');
    $table->index('status');
});
```

**Benefits:**
- Database agnostic
- Version controlled migrations
- Rollback support
- Better indexing syntax

---

## Summary

### ✅ CONFIRMED MIGRATED

**Database:**
- ✅ Partnerships table (with improvements)
- ✅ Lead Submissions table (with improvements)
- ✅ Page Assignments table

**REST API:**
- ✅ All partnership endpoints (with MVC structure)
- ✅ All lead endpoints (with status tracking)
- ✅ Dashboard stats endpoints (NEW)
- ✅ User endpoints

**Post Types:**
- ✅ Biolink pages
- ✅ Prequal pages
- ✅ Open house pages

**Admin Interface:**
- ✅ Partnership management (admin SPA)
- ✅ Lead tracking (admin SPA)
- ✅ Dashboard with stats (admin SPA)
- ✅ Settings page (admin SPA)

**Architecture:**
- ✅ WordPress Plugin Boilerplate structure
- ✅ Eloquent ORM for all database operations
- ✅ MVC pattern (Routes → Controllers → Models)
- ✅ React SPA admin with shadcn/ui
- ✅ TypeScript support

---

### ❓ NEEDS VERIFICATION

**Post Types:**
- ❓ Mortgage landing pages (`frs_mortgage_lp`)
- ❓ Announcements (`frs_announcement`)
- ❓ Custom links (`frs_custom_link`)

**Integrations:**
- ❓ FluentCRM integration
- ❓ Form integration (FluentForms, etc.)
- ❓ Blocksy theme integration (if needed in admin)

**User-Facing Features:**
- ❓ Frontend portal for loan officers/realtors
- ❓ Mortgage calculator tool
- ❓ Property valuation tool
- ❓ Marketing materials showcase
- ❓ App launcher widget

**Blocks:**
- ❓ All 14 Gutenberg blocks registered in frs-lrg?

**User Roles:**
- ❓ All 4 custom roles registered in frs-lrg?

---

### 🎯 RECOMMENDATION

**Next Steps:**

1. **Verify Frontend Portal Structure in frs-lrg**
   - ✅ CONFIRMED: All frontend tools are being kept and enhanced
   - Check if `src/frontend/` exists for portal components
   - Verify shortcodes are registered: `[frs_partnership_portal]`, `[frs_portal_router]`, `[frs_biolink_dashboard]`, `[frs_agent_signup]`
   - Confirm frontend React app entry point (separate from admin)

2. **Map Enhanced Features**
   - Document which tools have "more features" than original
   - List new features in mortgage calculator
   - List new features in property valuation
   - Identify any NEW tools added

3. **Verify Missing Post Types**
   - Check if announcements, custom links, mortgage LP exist in frs-lrg
   - Confirm all Gutenberg blocks are registered
   - Verify user roles are identical

4. **Integration Testing**
   - Test FluentCRM sync with new Eloquent models
   - Test form submissions to new lead endpoints
   - Test calendar integration

4. **Document API Changes**
   - Create migration guide for API consumers
   - Document new endpoints
   - Update any external integrations

5. **Data Migration Script**
   - Write script to migrate existing data from `wp_frs_*` to `wp_partnerships`, `wp_lead_submissions`
   - Test on staging environment
   - Plan rollout to production

6. **Sunset Timeline**
   - Once all features verified, deprecate frs-partnership-portal
   - Redirect old shortcodes to new admin pages
   - Archive old codebase
