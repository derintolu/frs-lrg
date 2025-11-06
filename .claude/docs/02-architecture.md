# Architecture

Complete architectural overview of the Lending Resource Hub plugin.

---

## Table of Contents

- [System Overview](#system-overview)
- [Backend Architecture](#backend-architecture)
- [Frontend Architecture](#frontend-architecture)
- [Gutenberg Blocks](#gutenberg-blocks)
- [Data Flow](#data-flow)
- [Directory Structure](#directory-structure)

---

## System Overview

### Tech Stack Summary

**Backend:**
- PHP 8.1+ with modern features
- WordPress 6.4+
- Eloquent ORM (prappo/wp-eloquent)
- Custom REST API with Route library
- Database migrations system

**Frontend:**
- React 18 + TypeScript
- Vite 5 (dual configs: frontend + admin)
- Tailwind CSS + Shadcn UI
- Jotai (state management)
- React Router DOM

**Build Tools:**
- Vite 5.0 - React bundler (HMR, fast builds)
- @wordpress/scripts - Gutenberg block bundler
- TypeScript 5.3 - Type safety
- PostCSS + Autoprefixer

**External Integrations:**
- **FRS Users Plugin** - Profile model (CRITICAL dependency)
- FluentBooking - Appointment booking
- FluentForms - Lead capture forms
- FluentCRM - CRM integration

### Architecture Layers

```
┌─────────────────────────────────────────┐
│           WordPress Core                │
│    (Users, Posts, Meta, Hooks)          │
└─────────────────────────────────────────┘
              ▲         ▲
              │         │
┌─────────────┴─────┐   │
│  FRS Users Plugin │   │
│  (Profile Model)  │   │
└─────────────▲─────┘   │
              │         │
              │         │
┌─────────────┴─────────┴─────────────────┐
│      Lending Resource Hub Plugin        │
│                                          │
│  ┌────────────────────────────────────┐ │
│  │         Backend Layer              │ │
│  │  - Eloquent Models                 │ │
│  │  - REST API Controllers            │ │
│  │  - Database Migrations             │ │
│  │  - Shortcode Handlers              │ │
│  └────────────────────────────────────┘ │
│                  ▲                       │
│                  │ REST API              │
│                  ▼                       │
│  ┌────────────────────────────────────┐ │
│  │        Frontend Layer              │ │
│  │  - React Apps (Portal, Admin)      │ │
│  │  - Gutenberg Blocks                │ │
│  │  - UI Components                   │ │
│  └────────────────────────────────────┘ │
└──────────────────────────────────────────┘
              ▲         ▲
              │         │
        ┌─────┴───┐  ┌──┴──────┐
        │ Fluent  │  │ Fluent  │
        │ Booking │  │ Forms   │
        └─────────┘  └─────────┘
```

---

## Backend Architecture

### Eloquent ORM Integration

**Why Eloquent?**
- Clean, readable database queries
- Automatic escaping (SQL injection protection)
- Relationships (hasMany, belongsTo, etc.)
- Query builder with method chaining
- No raw SQL needed

**Installation:**
```json
// composer.json
{
  "require": {
    "prappo/wp-eloquent": "^3.0"
  }
}
```

**Initialization:**

**File:** `includes/Core/Database.php`

```php
<?php
namespace LendingResourceHub\Core;

use WeDevs\ORM\Eloquent\Facade as DB;

class Database {
    public static function init(): void {
        // Initialize Eloquent ORM
        DB::instance([
            'driver'    => 'mysql',
            'host'      => DB_HOST,
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'charset'   => DB_CHARSET,
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => $GLOBALS['wpdb']->prefix,
        ]);
    }
}
```

### Model Structure

**File:** `includes/Models/Partnership.php`

```php
<?php
namespace LendingResourceHub\Models;

use WeDevs\ORM\Eloquent\Model;

class Partnership extends Model {
    /**
     * Table name (without prefix)
     */
    protected $table = 'partnerships';

    /**
     * Primary key
     */
    protected $primaryKey = 'id';

    /**
     * Auto-increment ID
     */
    public $incrementing = true;

    /**
     * Timestamps (created_at, updated_at)
     */
    public $timestamps = true;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'loan_officer_id',
        'agent_id',
        'status',
        'partner_post_id',
        'invitation_sent_at',
        'accepted_at',
    ];

    /**
     * Attributes to cast to native types
     */
    protected $casts = [
        'loan_officer_id' => 'integer',
        'agent_id' => 'integer',
        'partner_post_id' => 'integer',
        'invitation_sent_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Relationship: Partnership belongs to loan officer (User)
     */
    public function loanOfficer() {
        return $this->belongsTo(\WP_User::class, 'loan_officer_id');
    }

    /**
     * Relationship: Partnership belongs to agent (User)
     */
    public function agent() {
        return $this->belongsTo(\WP_User::class, 'agent_id');
    }

    /**
     * Relationship: Partnership has many leads
     */
    public function leads() {
        return $this->hasMany(LeadSubmission::class, 'partnership_id');
    }

    /**
     * Scope: Only active partnerships
     */
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Partnerships for specific loan officer
     */
    public function scopeForLoanOfficer($query, int $userId) {
        return $query->where('loan_officer_id', $userId);
    }
}
```

### Migration System

**File:** `database/Migrations/Partnerships.php`

```php
<?php
namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;

class Partnerships implements Migration {
    /**
     * Run migration (create table)
     */
    public function up(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'partnerships';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            loan_officer_id bigint(20) UNSIGNED NOT NULL,
            agent_id bigint(20) UNSIGNED DEFAULT NULL,
            partner_email varchar(255) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            partner_post_id bigint(20) UNSIGNED DEFAULT NULL,
            invitation_sent_at datetime DEFAULT NULL,
            accepted_at datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY loan_officer_id (loan_officer_id),
            KEY agent_id (agent_id),
            KEY partner_email (partner_email),
            KEY status (status)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Rollback migration (drop table)
     */
    public function down(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'partnerships';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}
```

**Running Migrations:**

**File:** `includes/Core/Install.php`

```php
<?php
namespace LendingResourceHub\Core;

use LendingResourceHub\Database\Migrations\Partnerships;
use LendingResourceHub\Database\Migrations\LeadSubmissions;
use LendingResourceHub\Database\Migrations\PageAssignments;

class Install {
    public static function activate(): void {
        // Run migrations
        (new Partnerships())->up();
        (new LeadSubmissions())->up();
        (new PageAssignments())->up();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }

    public static function uninstall(): void {
        // Optionally drop tables on uninstall
        if (defined('LRH_DELETE_DATA_ON_UNINSTALL') && LRH_DELETE_DATA_ON_UNINSTALL) {
            (new Partnerships())->down();
            (new LeadSubmissions())->down();
            (new PageAssignments())->down();
        }
    }
}
```

### REST API Routing

**Custom Route Library:**

**File:** `includes/Routes/Route.php`

```php
<?php
namespace LendingResourceHub\Routes;

class Route {
    private static string $namespace = 'lrh/v1';
    private static array $routes = [];

    /**
     * Register GET route
     */
    public static function get(string $path, array $callback, ?callable $permission = null): void {
        self::register('GET', $path, $callback, $permission);
    }

    /**
     * Register POST route
     */
    public static function post(string $path, array $callback, ?callable $permission = null): void {
        self::register('POST', $path, $callback, $permission);
    }

    /**
     * Register PUT route
     */
    public static function put(string $path, array $callback, ?callable $permission = null): void {
        self::register(['PUT', 'PATCH'], $path, $callback, $permission);
    }

    /**
     * Register DELETE route
     */
    public static function delete(string $path, array $callback, ?callable $permission = null): void {
        self::register('DELETE', $path, $callback, $permission);
    }

    /**
     * Internal: Register route with WordPress
     */
    private static function register($methods, string $path, array $callback, ?callable $permission): void {
        self::$routes[] = [
            'methods' => $methods,
            'path' => $path,
            'callback' => $callback,
            'permission' => $permission ?? '__return_true',
        ];
    }

    /**
     * Register all routes with WordPress
     */
    public static function registerRoutes(): void {
        add_action('rest_api_init', function() {
            foreach (self::$routes as $route) {
                register_rest_route(
                    self::$namespace,
                    $route['path'],
                    [
                        'methods' => $route['methods'],
                        'callback' => $route['callback'],
                        'permission_callback' => $route['permission'],
                    ]
                );
            }
        });
    }
}
```

**Using Routes:**

**File:** `includes/Routes/api.php`

```php
<?php
use LendingResourceHub\Routes\Route;
use LendingResourceHub\Controllers\PartnershipController;
use LendingResourceHub\Controllers\LeadController;
use LendingResourceHub\Controllers\UserController;

// User endpoints
Route::get('/users/me', [UserController::class, 'getCurrentUser'], 'is_user_logged_in');
Route::get('/users/(?P<id>\d+)/profile', [UserController::class, 'getUserProfile'], 'is_user_logged_in');

// Partnership endpoints
Route::get('/partnerships', [PartnershipController::class, 'index'], 'is_user_logged_in');
Route::post('/partnerships', [PartnershipController::class, 'store'], 'is_user_logged_in');
Route::get('/partnerships/(?P<id>\d+)', [PartnershipController::class, 'show'], 'is_user_logged_in');
Route::put('/partnerships/(?P<id>\d+)', [PartnershipController::class, 'update'], 'is_user_logged_in');
Route::delete('/partnerships/(?P<id>\d+)', [PartnershipController::class, 'destroy'], 'is_user_logged_in');

// Lead endpoints
Route::get('/leads', [LeadController::class, 'index'], 'is_user_logged_in');
Route::post('/leads', [LeadController::class, 'store'], 'is_user_logged_in');

// Register all routes
Route::registerRoutes();
```

### Controller Pattern

**File:** `includes/Controllers/PartnershipController.php`

```php
<?php
namespace LendingResourceHub\Controllers;

use LendingResourceHub\Models\Partnership;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class PartnershipController {
    /**
     * Get all partnerships for current user
     */
    public function index(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $user_id = get_current_user_id();

        $partnerships = Partnership::query()
            ->where('loan_officer_id', $user_id)
            ->orWhere('agent_id', $user_id)
            ->with(['loanOfficer', 'agent', 'leads'])
            ->get();

        return new WP_REST_Response([
            'success' => true,
            'data' => $partnerships,
        ], 200);
    }

    /**
     * Create new partnership
     */
    public function store(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $user_id = get_current_user_id();

        // Validate input
        $email = sanitize_email($request->get_param('email'));
        if (!is_email($email)) {
            return new WP_Error(
                'invalid_email',
                'Invalid email address',
                ['status' => 400]
            );
        }

        // Create partnership
        $partnership = Partnership::create([
            'loan_officer_id' => $user_id,
            'partner_email' => $email,
            'status' => 'pending',
            'invitation_sent_at' => current_time('mysql'),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'data' => $partnership,
        ], 201);
    }

    /**
     * Get single partnership
     */
    public function show(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param('id');
        $partnership = Partnership::with(['loanOfficer', 'agent'])->find($id);

        if (!$partnership) {
            return new WP_Error(
                'not_found',
                'Partnership not found',
                ['status' => 404]
            );
        }

        // Check permissions
        $user_id = get_current_user_id();
        if ($partnership->loan_officer_id !== $user_id && $partnership->agent_id !== $user_id) {
            return new WP_Error(
                'forbidden',
                'Access denied',
                ['status' => 403]
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $partnership,
        ], 200);
    }

    /**
     * Update partnership
     */
    public function update(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param('id');
        $partnership = Partnership::find($id);

        if (!$partnership) {
            return new WP_Error('not_found', 'Partnership not found', ['status' => 404]);
        }

        // Update fields
        $partnership->update([
            'status' => sanitize_text_field($request->get_param('status')),
            'accepted_at' => current_time('mysql'),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'data' => $partnership,
        ], 200);
    }

    /**
     * Delete partnership
     */
    public function destroy(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param('id');
        $partnership = Partnership::find($id);

        if (!$partnership) {
            return new WP_Error('not_found', 'Partnership not found', ['status' => 404]);
        }

        $partnership->delete();

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Partnership deleted',
        ], 200);
    }
}
```

### Shortcode System

**File:** `includes/Core/Shortcodes.php`

```php
<?php
namespace LendingResourceHub\Core;

class Shortcodes {
    public function __construct() {
        add_shortcode('lrh_portal', [$this, 'renderPortal']);
        add_shortcode('lrh_biolink_dashboard', [$this, 'renderBiolinkDashboard']);
    }

    /**
     * Render main portal shortcode
     */
    public function renderPortal($atts = []): string {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view the portal.</p>';
        }

        $user = wp_get_current_user();

        // Enqueue React app
        wp_enqueue_script('lrh-portal-app');
        wp_enqueue_style('lrh-portal-styles');

        // Pass config to React
        wp_localize_script('lrh-portal-app', 'lrhPortalConfig', [
            'userId' => $user->ID,
            'userName' => $user->display_name,
            'userEmail' => $user->user_email,
            'userRole' => $user->roles[0] ?? 'subscriber',
            'apiUrl' => rest_url('lrh/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
        ]);

        // Return root element for React to mount
        return '<div id="lrh-portal-root"></div>';
    }

    /**
     * Render biolink dashboard shortcode
     */
    public function renderBiolinkDashboard($atts = []): string {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access the biolink dashboard.</p>';
        }

        wp_enqueue_script('lrh-biolink-app');
        wp_enqueue_style('lrh-biolink-styles');

        return '<div id="lrh-biolink-dashboard-root"></div>';
    }
}
```

### Asset Management

**Using @kucrut/vite-for-wp:**

**File:** `includes/Assets/Frontend.php`

```php
<?php
namespace LendingResourceHub\Assets;

use Kucrut\Vite;

class Frontend {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(): void {
        // Vite asset helper
        Vite\enqueue_asset(
            LRH_PLUGIN_DIR . '/assets/frontend/dist',
            'src/main.tsx',
            [
                'handle' => 'lrh-portal-app',
                'in-footer' => true,
            ]
        );
    }
}
```

---

## Frontend Architecture

### Dual Vite Configurations

**Frontend Config:** `vite.frontend.config.js`

```javascript
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src/frontend'),
    },
  },

  build: {
    outDir: './assets/frontend/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: './src/frontend/main.tsx',
    },
  },

  server: {
    cors: true,
    origin: 'http://localhost:5173',
    host: 'localhost',
    port: 5173,
  },
});
```

**Admin Config:** `vite.admin.config.js`

```javascript
export default defineConfig({
  // Same structure, different paths
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src/admin'),
    },
  },

  build: {
    outDir: './assets/admin/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: './src/admin/main.tsx',
    },
  },

  server: {
    port: 5174, // Different port from frontend
  },
});
```

### React App Structure

**File:** `src/frontend/main.tsx`

```tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { PortalApp } from './PortalApp';
import './index.css';

// Get config from WordPress
declare global {
  interface Window {
    lrhPortalConfig: {
      userId: number;
      userName: string;
      userEmail: string;
      userRole: string;
      apiUrl: string;
      restNonce: string;
    };
  }
}

// Mount React app
const rootElement = document.getElementById('lrh-portal-root');
if (rootElement) {
  ReactDOM.createRoot(rootElement).render(
    <React.StrictMode>
      <PortalApp config={window.lrhPortalConfig} />
    </React.StrictMode>
  );
}
```

**File:** `src/frontend/PortalApp.tsx`

```tsx
import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { Dashboard } from './components/Dashboard';
import { Partnerships } from './components/Partnerships';
import { Leads } from './components/Leads';

interface PortalAppProps {
  config: {
    userId: number;
    userName: string;
    userEmail: string;
    userRole: string;
    apiUrl: string;
    restNonce: string;
  };
}

export function PortalApp({ config }: PortalAppProps): JSX.Element {
  return (
    <BrowserRouter basename="/portal">
      <Routes>
        <Route path="/" element={<Dashboard config={config} />} />
        <Route path="/partnerships" element={<Partnerships config={config} />} />
        <Route path="/leads" element={<Leads config={config} />} />
      </Routes>
    </BrowserRouter>
  );
}
```

---

## Gutenberg Blocks

### Dynamic Rendering

**CRITICAL:** Gutenberg blocks require BOTH JavaScript (editor) AND PHP (render callback).

**Block Registration:**

**File:** `includes/Core/Blocks.php`

```php
<?php
namespace LendingResourceHub\Core;

class Blocks {
    public function __construct() {
        add_action('init', [$this, 'registerBlocks']);
    }

    public function registerBlocks(): void {
        $blocks_dir = LRH_PLUGIN_DIR . '/blocks/';

        // Register biolink card block
        register_block_type($blocks_dir . 'biolink-card/block.json', [
            'render_callback' => [$this, 'renderBiolinkCard'],
        ]);
    }

    /**
     * Render biolink card on frontend
     */
    public function renderBiolinkCard(array $attributes, string $content): string {
        // Get user data from FRS Users Plugin Profile model
        $user_id = get_current_user_id();
        $profile = \FRSUsers\Models\Profile::where('user_id', $user_id)->first();

        if (!$profile) {
            return '<p>Profile not found.</p>';
        }

        ob_start();
        ?>
        <div class="biolink-card">
            <img src="<?php echo esc_url($profile->headshot_url); ?>" alt="<?php echo esc_attr($profile->full_name); ?>">
            <h2><?php echo esc_html($profile->full_name); ?></h2>
            <p><?php echo esc_html($profile->title); ?></p>
            <p><?php echo esc_html($profile->bio); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}
```

**Block Definition:**

**File:** `blocks/biolink-card/block.json`

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "lrh/biolink-card",
  "title": "Biolink Card",
  "category": "lrh-blocks",
  "icon": "id-alt",
  "description": "Display user biolink card with profile data",
  "supports": {
    "html": false,
    "align": ["wide", "full"]
  },
  "attributes": {
    "backgroundColor": {
      "type": "string",
      "default": "#ffffff"
    }
  },
  "editorScript": "file:./index.js",
  "editorStyle": "file:./editor.css",
  "style": "file:./style.css"
}
```

**Block Editor Script:**

**File:** `blocks/biolink-card/index.js`

```jsx
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('lrh/biolink-card', {
  edit: () => {
    const blockProps = useBlockProps();

    return (
      <div {...blockProps}>
        <p>Biolink Card (Preview in editor)</p>
        <p>This will render user profile data on the frontend.</p>
      </div>
    );
  },

  save: () => {
    // Return null for dynamic blocks (PHP renders)
    return null;
  },
});
```

---

## Data Flow

### User Profile Update Flow

```
User edits profile in Portal
        ↓
React form submission
        ↓
POST /wp-json/lrh/v1/profile
        ↓
UserController::updateProfile()
        ↓
FRS Users Plugin Profile model update
        ↓
Database: wp_frs_user_profiles table
        ↓
Biolink block render callback reads updated data
        ↓
Frontend displays new profile data
```

### Partnership Creation Flow

```
Loan Officer invites Realtor
        ↓
POST /wp-json/lrh/v1/partnerships
        ↓
PartnershipController::store()
        ↓
Partnership::create() via Eloquent
        ↓
Database: wp_partnerships table
        ↓
Send invitation email (Notifications class)
        ↓
Realtor receives email with accept link
        ↓
Realtor clicks link
        ↓
PUT /wp-json/lrh/v1/partnerships/{id}
        ↓
PartnershipController::update()
        ↓
Partnership status → 'active'
        ↓
Both users see partnership in portal
```

### Lead Capture Flow

```
Visitor fills FluentForm on biolink page
        ↓
FluentForms processes submission
        ↓
Hook: fluentform_submission_inserted
        ↓
FluentBookingIntegration::handleFormSubmission()
        ↓
LeadSubmission::create() via Eloquent
        ↓
Database: wp_lead_submissions table
        ↓
(Optional) Sync to FluentCRM contact
        ↓
Loan Officer sees lead in portal
        ↓
GET /wp-json/lrh/v1/leads returns new lead
```

---

## Directory Structure

```
frs-lrg/
├── .claude/
│   └── docs/                    # This documentation
├── assets/
│   ├── frontend/
│   │   └── dist/                # Built frontend React app
│   └── admin/
│       └── dist/                # Built admin React app
├── blocks/                      # Gutenberg blocks (15 blocks)
│   ├── biolink-card/
│   ├── partnership-form/
│   └── ...
├── config/
│   └── plugin.php               # Plugin configuration
├── database/
│   └── Migrations/              # Database migration classes
│       ├── Partnerships.php
│       ├── LeadSubmissions.php
│       └── PageAssignments.php
├── documentation/               # Documentation site (Fumadocs + Next.js)
├── includes/
│   ├── Admin/                   # Admin dashboard classes
│   ├── Assets/                  # Asset management
│   │   ├── Frontend.php
│   │   └── Admin.php
│   ├── Controllers/             # REST API controllers (18 controllers)
│   │   ├── PartnershipController.php
│   │   ├── LeadController.php
│   │   ├── UserController.php
│   │   └── ...
│   ├── Core/                    # Core plugin classes
│   │   ├── Plugin.php           # Main plugin singleton
│   │   ├── Database.php         # Eloquent initialization
│   │   ├── Blocks.php           # Block registration
│   │   ├── Shortcodes.php       # Shortcode handlers
│   │   └── Install.php          # Activation/deactivation
│   ├── Integrations/            # External plugin integrations
│   │   ├── FluentBooking.php
│   │   ├── FluentForms.php
│   │   └── FluentCRM.php
│   ├── Interfaces/              # PHP interfaces
│   │   └── Migration.php
│   ├── Models/                  # Eloquent models (8 models)
│   │   ├── Partnership.php
│   │   ├── LeadSubmission.php
│   │   └── PageAssignment.php
│   ├── Routes/                  # API route definitions
│   │   ├── Route.php            # Route helper class
│   │   └── api.php              # Route registration
│   ├── Traits/                  # Reusable PHP traits
│   └── functions.php            # Helper functions
├── src/
│   ├── frontend/                # Frontend React source
│   │   ├── components/
│   │   ├── main.tsx
│   │   ├── PortalApp.tsx
│   │   └── index.css
│   └── admin/                   # Admin React source
│       ├── components/
│       ├── main.tsx
│       └── index.css
├── views/                       # PHP template files
│   ├── admin/
│   └── public/
├── .storybook/                  # Storybook configuration
├── composer.json                # PHP dependencies
├── package.json                 # NPM dependencies
├── vite.frontend.config.js      # Vite config for frontend
├── vite.admin.config.js         # Vite config for admin
├── tailwind.config.js           # Tailwind CSS config
├── tsconfig.json                # TypeScript config
└── lending-resource-hub.php     # Main plugin file
```

---

## Related Documentation

- [01-development-workflow.md](./01-development-workflow.md) - Dev server, build process
- [04-backend-patterns.md](./04-backend-patterns.md) - Eloquent, migrations, API
- [05-frontend-patterns.md](./05-frontend-patterns.md) - React components, state management
- [10-external-dependencies.md](./10-external-dependencies.md) - FRS Users Plugin, Fluent plugins
