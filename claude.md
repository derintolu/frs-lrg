# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Project Overview

**Plugin Name:** Lending Resource Hub (LRH)
**Previous Name:** FRS Partnership Portal
**Purpose:** Learning management and partnership platform for 21st Century Lending
**Architecture:** WordPress Plugin Boilerplate + Eloquent ORM + React + TypeScript
**PHP Version:** 8.1+
**WordPress Version:** 6.0+

This plugin manages partnerships between loan officers and real estate agents, tracks lead submissions, provides portal dashboards, and integrates with FluentBooking, FluentForms, and FluentCRM.

---

## Build Commands

```bash
# Install dependencies
composer install          # PHP dependencies (Eloquent ORM)
npm install              # JavaScript dependencies

# Development (runs both admin and frontend Vite dev servers)
npm run dev              # Ports 5173 (frontend) and 5174 (admin)
npm run dev:frontend     # Frontend only (port 5173)
npm run dev:admin        # Admin only (port 5174)
npm run dev:all          # Dev + Gutenberg blocks
npm run dev:server       # Dev + WordPress server

# Production build
npm run build            # Build both frontend and admin
npm run block:build      # Build Gutenberg blocks only

# Gutenberg blocks
npm run block:start      # Development mode for blocks

# Plugin management
npm run release          # Creates release package in /release folder
npm run rename           # Rename plugin (after updating plugin-config.json)

# Code quality
npm run format:check     # Check code formatting
npm run format:fix       # Fix code formatting
```

---

## High-Level Architecture

### Backend Architecture

**Framework:** WordPress Plugin Boilerplate with Laravel-style patterns

**Key Components:**
1. **Eloquent ORM** - Laravel 8.9's database layer via prappo/wp-eloquent
2. **RESTful API** - Custom routing system at `/wp-json/lrh/v1/`
3. **Migrations** - Database schema and data migrations
4. **Models** - Eloquent models for partnerships, leads, page assignments
5. **Controllers** - API endpoint handlers organized by feature
6. **Integrations** - FluentBooking (auto-calendar), FluentForms, FluentCRM

**Database Tables:**
- `wp_partnerships` - Partnership relationships between loan officers and agents
- `wp_lead_submissions` - Lead tracking from various sources
- `wp_page_assignments` - User-to-page mapping for personalized landing pages
- `wp_accounts` - Example/demo table from boilerplate

**Namespace:** `LendingResourceHub`
**Route Prefix:** `lrh/v1`
**Text Domain:** `lending-resource-hub`

### Frontend Architecture

**Framework:** React 18 + TypeScript + Vite

**Build System:**
- **Dual Vite configs:** Separate builds for admin (`vite.admin.config.js`) and frontend (`vite.frontend.config.js`)
- **Output:** Assets compiled to `assets/admin/dist/` and `assets/frontend/dist/`
- **Hot reload:** Vite dev servers on ports 5173 (frontend) and 5174 (admin)

**UI Stack:**
- React 18 with functional components and hooks
- TypeScript for type safety
- Tailwind CSS for styling
- Radix UI (shadcn/ui components) for accessible UI primitives
- React Router DOM for client-side routing
- Jotai for state management

**Portal Structure:**
- Rendered via `[lrh_portal]` shortcode
- React mounts to `#lrh-portal-root` div
- Config passed via `window.lrhPortalConfig` object
- Assets enqueued conditionally when shortcode is present

### Gutenberg Blocks Architecture

**Framework:** WordPress Block API v3 + Custom Controllers

**Build System:**
- Uses `@wordpress/scripts` for block compilation
- Source: `src/blocks/*/` directories
- Output: `assets/blocks/*/` directories
- Separate build from main Vite builds

**Block Categories:**
- `lrh-biolink` - Biolink page components (15 blocks total)

**Key Blocks:**
- `lrh/biolink-page` - Complete dynamic biolink page (main block)
- `lrh/biolink-header` - Header with video background
- `lrh/biolink-button` - CTA buttons
- `lrh/biolink-form` - Fluent Forms integration
- `lrh/biolink-social` - Social media links
- `lrh/loan-officer` - Loan officer profile cards
- `lrh/mortgage-calculator` - Mortgage calculations
- `lrh/openhouse-carousel` - Open house listings

**Critical Pattern - Dynamic Block Rendering:**

Blocks use server-side rendering with data pulled from `FRSUsers\Models\Profile` (Eloquent):

```php
// Block registration with PHP render callback
register_block_type(
    LRH_DIR . 'blocks/biolink-page/block.json',
    array(
        'render_callback' => array($this, 'render_biolink_page_block'),
    )
);

// Dynamic rendering pulls from Profile model
public function render_biolink_page_block($attributes) {
    $user_id = $this->get_biolink_user_id($attributes, $post);
    $profile = Profile::where('user_id', $user_id)->first();

    // Render using profile data
    return $this->render_all_sections($user_data);
}
```

**User ID Resolution Priority:**
1. Block attribute `user_id`
2. Post meta `frs_biolink_user` or `_frs_loan_officer_id`
3. Post author

**Metafield Integration:**
- `frs_biolink_user` - Links page to user
- `_frs_loan_officer_id` - Alternative user reference
- `_frs_page_views` - View tracking
- `_frs_page_conversions` - Conversion tracking

**Fluent Forms Integration in Blocks:**
- Forms embedded in hidden divs, shown on button click
- Form IDs: 7 (scheduling), 6 (rate quote)
- Uses conversational form type: `[fluentform type="conversational" id="7"]`
- Thank you overlay triggered via custom JS event `lrh_lead_captured`

**Auto-generation:**
```php
// Generate biolink page for user
$page = Blocks::generate_biolink_page($user_id);
// Creates page with lrh/biolink-page block
// Sets all metafields automatically
// Links to Profile model for data
```

---

## Critical Architecture Patterns

### 1. Migration System

**Location:** `database/Migrations/`

All migrations implement the `Migration` interface:
```php
interface Migration {
    public static function up();   // Create/modify schema
    public static function down(); // Rollback (rarely used in WordPress)
}
```

**Schema Migrations:**
- Use `Capsule::schema()->create()` for creating tables
- Always check `hasTable()` to prevent duplicate creation
- Use Laravel 8.9 schema builder methods
- Run automatically on plugin activation via `Install::init()`

**Data Migrations:**
- Use migration flags to prevent re-running: `get_option('migration_flag')`
- Mix `$wpdb` for raw queries and Eloquent for model operations
- Always check for existing data before inserting
- Log completion for debugging

**Example:**
```php
class Partnerships implements Migration {
    private static $table = 'partnerships';

    public static function up() {
        if (Capsule::schema()->hasTable(self::$table)) {
            return;
        }

        Capsule::schema()->create(self::$table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_officer_id');
            $table->string('partner_email');
            $table->timestamps();

            $table->index('loan_officer_id');
        });
    }
}
```

### 2. Eloquent ORM Usage

**Location:** `includes/Models/`

Always use Eloquent models instead of raw SQL queries.

**Model Structure:**
```php
namespace LendingResourceHub\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

class Partnership extends Model {
    protected $table = 'partnerships';

    protected $fillable = [
        'loan_officer_id',
        'agent_id',
        'partner_email',
        'status'
    ];

    // Relationships
    public function loanOfficer() {
        return $this->belongsTo(Users::class, 'loan_officer_id');
    }

    public function leads() {
        return $this->hasMany(LeadSubmission::class);
    }
}
```

**Common Operations:**
```php
// Query
$partnerships = Partnership::where('status', 'active')->get();
$partnership = Partnership::find($id);

// Create
Partnership::create(['loan_officer_id' => 123, 'partner_email' => 'agent@example.com']);

// Update
$partnership->status = 'completed';
$partnership->save();

// Relationships
$partnership->leads()->where('status', 'new')->get();
```

### 3. REST API Routing

**Location:** `includes/Routes/Api.php`

Uses custom routing library (`LendingResourceHub\Libs\API\Route`):

```php
Route::prefix('lrh/v1', function (Route $route) {
    // Basic routes
    $route->get('/users/me', '\LendingResourceHub\Controllers\Users\Actions@get_current_user');
    $route->post('/partnerships', '\LendingResourceHub\Controllers\Partnerships\Actions@create_partnership');

    // Dynamic parameters
    $route->get('/partnerships/lo/{id}', '\LendingResourceHub\Controllers\Partnerships\Actions@get_partnerships_for_lo');
});
```

**Controller Pattern:**
```php
namespace LendingResourceHub\Controllers\Users;

class Actions {
    public function get_current_user($request) {
        $user = wp_get_current_user();

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'id' => $user->ID,
                'email' => $user->user_email,
                'name' => $user->display_name
            ]
        ]);
    }
}
```

**Key Routes:**
- `/users/*` - User data and profiles (7 endpoints)
- `/partnerships/*` - Partnership CRUD and queries (9 endpoints)
- `/leads/*` - Lead submissions and tracking (6 endpoints)
- `/dashboard/stats/*` - Dashboard statistics (3 endpoints)
- `/settings/*` - System settings (3 endpoints)
- `/calendar/*` - FluentBooking calendar management (5 endpoints)

### 4. Shortcode System

**Location:** `includes/Core/Shortcode.php`

Shortcodes are registered in the `init()` method:

```php
add_shortcode('lrh_portal', array($this, 'render_portal'));
```

**Portal Rendering Flow:**
1. Shortcode renders `<div id="lrh-portal-root"></div>`
2. `Frontend::enqueue_portal_assets_public()` enqueues React bundle
3. JavaScript passes config via `window.lrhPortalConfig`
4. React app mounts to `#lrh-portal-root`

**Config Structure:**
```javascript
window.lrhPortalConfig = {
    userId: 123,
    userName: "John Doe",
    userEmail: "john@example.com",
    userRole: "loan_officer",
    apiUrl: "/wp-json/lrh/v1/",
    restNonce: "abc123..."
};
```

### 5. Asset Management

**Admin Assets:** `includes/Assets/Admin.php`
**Frontend Assets:** `includes/Assets/Frontend.php`

Assets are enqueued using the `@kucrut/vite-for-wp` helper:

```php
use LendingResourceHub\Libs\Assets;

Assets\enqueue_asset(
    LRH_DIR . '/assets/frontend/dist',
    'src/frontend/main.jsx',
    [
        'handle' => 'lrh-portal',
        'in-footer' => true
    ]
);
```

**Development vs Production:**
- Development: Vite serves from `http://localhost:5173` with HMR
- Production: Reads from `assets/*/dist/manifest.json` for versioned files

---

## Integration Points

### FluentBooking Integration

**Location:** `includes/Integrations/FluentBooking.php`

**Purpose:** Auto-create booking calendars for loan officers

**Hooks:**
- `user_register` - Creates calendar when user registers
- `set_user_role` - Creates calendar when user becomes loan officer
- `lrh_create_loan_officer_calendar` - Manual trigger action

**Key Methods:**
```php
FluentBooking::auto_create_calendar($user_id);          // Creates calendar
FluentBooking::has_calendar($user_id);                  // Check if exists
FluentBooking::get_calendar($user_id);                  // Get calendar data
FluentBooking::reset_calendar($user_id);                // Delete and recreate
```

**Database Tables Used:**
- `wp_fcal_calendars` - Calendar metadata
- `wp_fcal_calendar_events` - Event definitions

### FluentForms Integration

**Location:** `includes/Controllers/Forms/Actions.php`

**Webhook Handler:** `/form-submit` endpoint processes FluentForms submissions

**Flow:**
1. FluentForms submits to webhook
2. Extracts lead data from submission
3. Creates `LeadSubmission` record
4. Associates with partnership if applicable
5. Triggers FluentCRM sync if enabled

### FluentCRM Integration

**Referenced in:** Migration files and lead controllers

**Purpose:** Sync lead data to FluentCRM for email marketing

**Timestamp Field:** `synced_to_fluentcrm_at` in lead submissions table

---

## Common Development Tasks

### Adding a New Database Table

1. Create migration in `database/Migrations/YourTable.php`:
```php
namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;
use Prappo\WpEloquent\Database\Capsule\Manager as Capsule;
use Prappo\WpEloquent\Database\Schema\Blueprint;

class YourTable implements Migration {
    private static $table = 'your_table';

    public static function up() {
        if (Capsule::schema()->hasTable(self::$table)) {
            return;
        }

        Capsule::schema()->create(self::$table, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public static function down() {
        Schema::dropIfExists(self::$table);
    }
}
```

2. Create model in `includes/Models/YourModel.php`:
```php
namespace LendingResourceHub\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

class YourModel extends Model {
    protected $table = 'your_table';
    protected $fillable = ['name'];
}
```

3. Register migration in `includes/Core/Install.php`:
```php
private function install_tables() {
    // Existing migrations...
    YourTable::up();
}
```

4. Test:
```bash
wp plugin deactivate frs-lrg
wp plugin activate frs-lrg
wp db query "SHOW TABLES LIKE 'wp_your_table'"
```

### Adding a New API Endpoint

1. Add route in `includes/Routes/Api.php`:
```php
$route->get('/your-endpoint', '\LendingResourceHub\Controllers\YourFeature\Actions@your_method');
```

2. Create or update controller in `includes/Controllers/YourFeature/Actions.php`:
```php
namespace LendingResourceHub\Controllers\YourFeature;

class Actions {
    public function your_method($request) {
        // Permission check
        if (!current_user_can('read')) {
            return new \WP_Error('unauthorized', 'Unauthorized', ['status' => 401]);
        }

        // Use Eloquent
        $data = YourModel::all();

        return rest_ensure_response([
            'success' => true,
            'data' => $data
        ]);
    }
}
```

3. Test:
```bash
curl -X GET "http://hub21.local/wp-json/lrh/v1/your-endpoint"
```

### Adding a React Component to Portal

1. Create component in `src/frontend/components/YourComponent.tsx`:
```typescript
import React from 'react';

interface YourComponentProps {
    title: string;
}

export function YourComponent({ title }: YourComponentProps) {
    return (
        <div className="p-4">
            <h2 className="text-2xl font-bold">{title}</h2>
        </div>
    );
}
```

2. Import in parent component or router
3. Build:
```bash
npm run build
```

4. Test in browser

### Adding a Gutenberg Block

1. Create block directory in `blocks/your-block/`:
```
blocks/your-block/
├── block.json      # Block metadata
├── edit.js         # Editor interface (optional)
├── render.php      # Server-side render (for dynamic blocks)
└── style.css       # Block styles (optional)
```

2. Define block in `block.json`:
```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "lrh/your-block",
    "title": "Your Block",
    "category": "lrh-biolink",
    "attributes": {
        "userId": {
            "type": "number",
            "default": 0
        }
    }
}
```

3. Register block in `includes/Controllers/Biolinks/Blocks.php`:
```php
public function register_blocks() {
    register_block_type(
        LRH_DIR . 'blocks/your-block/block.json',
        array(
            'render_callback' => array($this, 'render_your_block'),
        )
    );
}

public function render_your_block($attributes) {
    // Pull data from Profile model
    $profile = Profile::where('user_id', $attributes['userId'])->first();

    // Return rendered HTML
    ob_start();
    include LRH_DIR . 'blocks/your-block/render.php';
    return ob_get_clean();
}
```

4. Build blocks:
```bash
npm run block:build
```

5. Test in block editor

**Note:** For dynamic blocks that pull from database, always use render callbacks with Eloquent models rather than static content.

---

## File Structure Reference

```
frs-lrg/
├── assets/
│   ├── admin/              # Admin React app source
│   ├── frontend/           # Frontend React app source
│   ├── blocks/             # Compiled Gutenberg blocks output
│   └── components/         # Shared React components
├── blocks/                 # Gutenberg blocks source (15 biolink blocks)
│   ├── biolink-page/       # Main dynamic biolink block
│   ├── biolink-header/     # Header with video background
│   ├── biolink-button/     # Action buttons
│   ├── biolink-form/       # Form integration
│   └── [other blocks]/     # Additional biolink components
├── config/                 # Plugin configuration
├── database/
│   ├── Migrations/         # Database schema migrations
│   └── Seeders/            # Data seeders
├── documentation/          # Marketing site/docs (optional)
├── includes/
│   ├── Admin/              # Admin menu classes
│   ├── Assets/             # Asset enqueue handlers
│   ├── Controllers/        # API endpoint controllers
│   ├── Core/               # Core functionality (Install, Api, Shortcode, etc.)
│   ├── Integrations/       # Third-party integrations
│   ├── Interfaces/         # PHP interfaces
│   ├── Models/             # Eloquent models
│   ├── Routes/             # API route definitions
│   ├── Traits/             # Reusable traits
│   └── functions.php       # Helper functions
├── libs/                   # Utility libraries
│   ├── assets.php          # Asset helpers
│   └── db.php              # Database connection bootstrap
├── views/                  # PHP templates (if needed)
├── vendor/                 # Composer dependencies
├── composer.json           # PHP dependencies
├── package.json            # NPM dependencies
├── plugin-config.json      # Plugin metadata
├── plugin.php              # Main plugin class
├── lending-resource-hub.php # Plugin entry point
├── vite.admin.config.js    # Vite config for admin
├── vite.frontend.config.js # Vite config for frontend
├── tailwind.config.js      # Tailwind CSS config
└── tsconfig.json           # TypeScript config
```

---

## Important Conventions

### PHP Coding Standards

1. **Namespace:** All classes use `LendingResourceHub` namespace
2. **Singleton Pattern:** Core classes use `Base` trait with `get_instance()` method
3. **Type Declarations:** Use PHP 8.1+ typed properties and return types
4. **WordPress Standards:** Follow WordPress coding standards for formatting
5. **Security:** Always sanitize input, escape output, use prepared statements

### React/TypeScript Standards

1. **Functional Components:** Use function components with hooks
2. **TypeScript:** Strict mode enabled, explicit types required
3. **Props Interfaces:** Define interfaces for all component props
4. **Naming:** PascalCase for components, camelCase for functions/variables
5. **File Extensions:** `.tsx` for components with JSX, `.ts` for utilities

### Database Conventions

1. **Table Names:** Lowercase with underscores (snake_case), plural
2. **WordPress Prefix:** Automatically added via `$wpdb->prefix`
3. **Migrations:** Always check `hasTable()` before creating
4. **Indexes:** Add indexes for foreign keys and frequently queried columns
5. **Timestamps:** Use `timestamps()` for created_at/updated_at

### Gutenberg Block Conventions

1. **Block Names:** Use `lrh/` prefix (e.g., `lrh/biolink-page`)
2. **API Version:** Use Block API v3 (`"apiVersion": 3`)
3. **Dynamic Rendering:** Always use PHP render callbacks for blocks that pull from database
4. **Profile Model:** Biolink blocks pull data from `FRSUsers\Models\Profile` (external dependency)
5. **Inline Styles:** For biolink blocks, use inline styles to ensure consistent rendering across themes
6. **Form Integration:** Use FluentForms shortcodes with `type="conversational"` for forms in blocks

---

## Migration Status

**Current Status:** Backend complete, frontend in progress

**Completed:**
- ✅ Database tables (partnerships, lead_submissions, page_assignments)
- ✅ Eloquent models with relationships
- ✅ 40+ REST API endpoints
- ✅ FluentBooking integration
- ✅ Migration system with old data migration
- ✅ Shortcode rendering system

**In Progress:**
- ⏳ React portal interface (copied from frs-partnership-portal v2.6.0)
- ⏳ Asset build configuration
- ⏳ Full portal dashboard implementation

**Migration Notes:**
- Old plugin: `frs-partnership-portal`
- New plugin: `frs-lrg` (Lending Resource Hub)
- Data migration: `MigrateOldData::up()` migrates from old tables
- Legacy shortcode support: `[frs_partnership_portal]` still works as alias

---

## Troubleshooting

### Plugin Won't Activate

Check:
```bash
# Composer dependencies installed?
ls -la vendor/autoload.php

# PHP syntax errors?
php -l includes/Models/Partnership.php

# Check error log
tail -f /app/public/wp-content/debug.log
```

### API Returns 404

```bash
# Flush permalinks
wp rewrite flush

# Check routes registered
curl http://hub21.local/wp-json/lrh/v1/
```

### Portal Shows Blank Screen

```bash
# Assets built?
ls -la assets/frontend/dist/

# Check browser console for errors
# DevTools → Console

# Rebuild
npm run build
```

### Vite Dev Server Issues

If using Local WP and dev server doesn't work:
1. Change Router mode to `localhost` in Local WP settings
2. Restart Vite dev server
3. Clear browser cache

---

## Key Differences from WordPress Plugin Boilerplate

This plugin is based on the WordPress Plugin Boilerplate but has been customized:

1. **Eloquent ORM** - Uses Laravel's database layer instead of WordPress `$wpdb` everywhere
2. **Custom Routing** - Custom API routing system instead of standard WordPress REST API
3. **Dual Vite Configs** - Separate builds for admin and frontend
4. **Migration System** - Laravel-style migrations with rollback capability
5. **Integrations** - Deep integration with Fluent plugins (Booking, Forms, CRM)
6. **Business Logic** - Partnership and lead management specific to lending industry

---

## External Dependencies

**PHP (via Composer):**
- `prappo/wp-eloquent` ^3.0 - Eloquent ORM for WordPress

**JavaScript (via NPM):**
- `react` ^18.2.0 - UI framework
- `react-dom` ^18.2.0 - React DOM renderer
- `react-router-dom` ^6.20.0 - Client-side routing
- `@radix-ui/*` - Accessible UI primitives
- `tailwindcss` ^3.3.5 - Utility-first CSS
- `vite` ^4.5.0 - Build tool
- `typescript` ^5.x - Type safety
- `lucide-react` - Icon library

**WordPress Plugins (Required):**
- **FRS Users Plugin** - Provides `FRSUsers\Models\Profile` model (CRITICAL DEPENDENCY)
- FluentBooking - Calendar/booking system
- FluentForms - Form builder with conversational forms
- FluentCRM - Email marketing

**WordPress Plugins (Optional):**
- Advanced Custom Fields (ACF) - Person custom post type (legacy support)

**External Model Dependencies:**
The biolink blocks system requires the `FRSUsers\Models\Profile` Eloquent model from the `frs-users` plugin. This model provides:
- `user_id` - WordPress user ID
- `first_name`, `last_name` - User name
- `email`, `phone_number`, `mobile_number` - Contact info
- `job_title` - Professional title
- `headshot_id` - WordPress attachment ID for profile photo
- `arrive` - Arrive pre-approval link

---

## Quick Reference

**Plugin Activation:**
```bash
wp plugin activate frs-lrg
```

**View Tables:**
```bash
wp db query "SHOW TABLES LIKE 'wp_partnerships'"
```

**List Routes:**
```bash
curl http://hub21.local/wp-json/lrh/v1/ | jq
```

**Check Shortcodes:**
```bash
wp shortcode list | grep lrh
```

**Build Assets:**
```bash
npm run build
```

**Dev Mode:**
```bash
npm run dev
```

---

## Additional Documentation

For more detailed information, see:
- `MIGRATION-GUIDE.md` - Complete database migration documentation
- `COMPLETE-MIGRATION-PLAN.md` - Full migration plan from old plugin
- `TERMINAL-CLAUDE-INSTRUCTIONS.md` - Step-by-step terminal commands
- `DIAGNOSTIC-CHECKLIST.md` - Troubleshooting checklist
