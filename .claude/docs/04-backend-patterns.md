# Backend Patterns

Complete guide to backend development patterns for the Lending Resource Hub plugin.

---

## Table of Contents

- [Eloquent Models](#eloquent-models)
- [Migration System](#migration-system)
- [REST API Routing](#rest-api-routing)
- [Shortcode System](#shortcode-system)
- [Asset Management](#asset-management)
- [Integration Patterns](#integration-patterns)

---

## Eloquent Models

### Model Structure

All models extend `WeDevs\ORM\Eloquent\Model` and follow modern PHP 8.1+ standards.

**Base Model Example:**

**File:** `includes/Models/Partnership.php`

```php
<?php
namespace LendingResourceHub\Models;

use WeDevs\ORM\Eloquent\Model;

class Partnership extends Model {
    /**
     * Table name (without wp_ prefix)
     */
    protected $table = 'partnerships';

    /**
     * Primary key column
     */
    protected $primaryKey = 'id';

    /**
     * Auto-incrementing ID
     */
    public $incrementing = true;

    /**
     * Enable created_at and updated_at timestamps
     */
    public $timestamps = true;

    /**
     * Mass-assignable attributes
     * Only these fields can be filled via create() or update()
     */
    protected $fillable = [
        'loan_officer_id',
        'agent_id',
        'partner_email',
        'status',
        'partner_post_id',
        'invitation_sent_at',
        'accepted_at',
    ];

    /**
     * Hidden attributes (excluded from JSON/array output)
     */
    protected $hidden = [];

    /**
     * Type casting for attributes
     */
    protected $casts = [
        'loan_officer_id' => 'integer',
        'agent_id' => 'integer',
        'partner_post_id' => 'integer',
        'invitation_sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Partnership belongs to loan officer
     */
    public function loanOfficer() {
        return $this->belongsTo(\WP_User::class, 'loan_officer_id');
    }

    /**
     * Relationship: Partnership belongs to agent
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
     * Query Scope: Only active partnerships
     */
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    /**
     * Query Scope: Partnerships for specific loan officer
     */
    public function scopeForLoanOfficer($query, int $userId) {
        return $query->where('loan_officer_id', $userId);
    }

    /**
     * Query Scope: Partnerships for specific agent
     */
    public function scopeForAgent($query, int $userId) {
        return $query->where('agent_id', $userId);
    }

    /**
     * Accessor: Get formatted created date
     */
    public function getCreatedDateAttribute(): string {
        return $this->created_at->format('M d, Y');
    }

    /**
     * Mutator: Automatically format email before saving
     */
    public function setPartnerEmailAttribute(?string $value): void {
        $this->attributes['partner_email'] = $value ? strtolower(trim($value)) : null;
    }
}
```

### Using Models

**Create:**

```php
use LendingResourceHub\Models\Partnership;

// Method 1: create() - mass assignment
$partnership = Partnership::create([
    'loan_officer_id' => 123,
    'partner_email' => 'realtor@example.com',
    'status' => 'pending',
    'invitation_sent_at' => current_time('mysql'),
]);

// Method 2: new instance + save()
$partnership = new Partnership();
$partnership->loan_officer_id = 123;
$partnership->partner_email = 'realtor@example.com';
$partnership->status = 'pending';
$partnership->save();
```

**Read:**

```php
// Find by ID
$partnership = Partnership::find(123);

// Find or fail (throws exception if not found)
$partnership = Partnership::findOrFail(123);

// Get all
$partnerships = Partnership::all();

// Get with conditions
$partnerships = Partnership::where('status', 'active')
    ->where('loan_officer_id', 123)
    ->get();

// Get first matching
$partnership = Partnership::where('partner_email', 'realtor@example.com')->first();

// Get with relationships
$partnership = Partnership::with(['loanOfficer', 'agent', 'leads'])->find(123);

// Using scopes
$partnerships = Partnership::active()->forLoanOfficer(123)->get();
```

**Update:**

```php
// Method 1: find + update()
$partnership = Partnership::find(123);
$partnership->update([
    'status' => 'active',
    'accepted_at' => current_time('mysql'),
]);

// Method 2: find + property + save()
$partnership = Partnership::find(123);
$partnership->status = 'active';
$partnership->accepted_at = current_time('mysql');
$partnership->save();

// Method 3: where + update (bulk update)
Partnership::where('status', 'pending')
    ->where('created_at', '<', date('Y-m-d', strtotime('-30 days')))
    ->update(['status' => 'expired']);
```

**Delete:**

```php
// Method 1: find + delete()
$partnership = Partnership::find(123);
$partnership->delete();

// Method 2: destroy()
Partnership::destroy(123);

// Method 3: where + delete (bulk delete)
Partnership::where('status', 'declined')->delete();
```

### Relationships

**One-to-Many:**

```php
// Partnership has many leads
class Partnership extends Model {
    public function leads() {
        return $this->hasMany(LeadSubmission::class, 'partnership_id');
    }
}

// LeadSubmission belongs to partnership
class LeadSubmission extends Model {
    public function partnership() {
        return $this->belongsTo(Partnership::class, 'partnership_id');
    }
}

// Usage
$partnership = Partnership::find(123);
$leads = $partnership->leads; // Get all leads for this partnership

$lead = LeadSubmission::find(456);
$partnership = $lead->partnership; // Get partnership for this lead
```

**Many-to-Many:**

```php
// User has many roles (example with pivot table)
class User extends Model {
    public function roles() {
        return $this->belongsToMany(
            Role::class,
            'user_roles', // pivot table
            'user_id',    // foreign key on pivot
            'role_id'     // related key on pivot
        );
    }
}

// Usage
$user = User::find(123);
$roles = $user->roles; // Get all roles for this user
```

### Query Builder

**Complex Queries:**

```php
use LendingResourceHub\Models\LeadSubmission;

// Get leads with multiple conditions
$leads = LeadSubmission::query()
    ->where('loan_officer_id', 123)
    ->where('status', '!=', 'spam')
    ->whereDate('created_at', '>=', date('Y-m-d', strtotime('-30 days')))
    ->whereNotNull('phone')
    ->orderBy('created_at', 'desc')
    ->limit(50)
    ->get();

// Get counts
$total_leads = LeadSubmission::where('loan_officer_id', 123)->count();
$pending_leads = LeadSubmission::where('loan_officer_id', 123)
    ->where('status', 'pending')
    ->count();

// Get aggregates
$stats = LeadSubmission::where('loan_officer_id', 123)
    ->selectRaw('
        COUNT(*) as total,
        COUNT(CASE WHEN status = "contacted" THEN 1 END) as contacted,
        COUNT(CASE WHEN status = "converted" THEN 1 END) as converted
    ')
    ->first();

// Group by
$leads_by_source = LeadSubmission::query()
    ->select('lead_source', DB::raw('COUNT(*) as count'))
    ->groupBy('lead_source')
    ->get();
```

---

## Migration System

### Migration Structure

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
            KEY status (status),
            KEY created_at (created_at)
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

### Running Migrations

**File:** `includes/Core/Install.php`

```php
<?php
namespace LendingResourceHub\Core;

use LendingResourceHub\Database\Migrations\Partnerships;
use LendingResourceHub\Database\Migrations\LeadSubmissions;
use LendingResourceHub\Database\Migrations\PageAssignments;

class Install {
    /**
     * Run on plugin activation
     */
    public static function activate(): void {
        // Run all migrations
        (new Partnerships())->up();
        (new LeadSubmissions())->up();
        (new PageAssignments())->up();

        // Set plugin version
        update_option('lrh_version', LRH_VERSION);

        // Flush rewrite rules (for custom post types)
        flush_rewrite_rules();
    }

    /**
     * Run on plugin deactivation
     */
    public static function deactivate(): void {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Run on plugin uninstall
     */
    public static function uninstall(): void {
        // Only delete data if explicitly allowed
        if (defined('LRH_DELETE_DATA_ON_UNINSTALL') && LRH_DELETE_DATA_ON_UNINSTALL) {
            // Drop tables
            (new Partnerships())->down();
            (new LeadSubmissions())->down();
            (new PageAssignments())->down();

            // Delete options
            delete_option('lrh_version');
            delete_option('lrh_settings');
        }
    }
}
```

**Register in Main Plugin File:**

**File:** `lending-resource-hub.php`

```php
<?php
register_activation_hook(__FILE__, ['LendingResourceHub\Core\Install', 'activate']);
register_deactivation_hook(__FILE__, ['LendingResourceHub\Core\Install', 'deactivate']);
```

---

## REST API Routing

### Custom Route Library

**File:** `includes/Routes/Route.php`

```php
<?php
namespace LendingResourceHub\Routes;

class Route {
    private static string $namespace = 'lrh/v1';
    private static array $routes = [];

    public static function get(string $path, array $callback, ?callable $permission = null): void {
        self::register('GET', $path, $callback, $permission);
    }

    public static function post(string $path, array $callback, ?callable $permission = null): void {
        self::register('POST', $path, $callback, $permission);
    }

    public static function put(string $path, array $callback, ?callable $permission = null): void {
        self::register(['PUT', 'PATCH'], $path, $callback, $permission);
    }

    public static function delete(string $path, array $callback, ?callable $permission = null): void {
        self::register('DELETE', $path, $callback, $permission);
    }

    private static function register($methods, string $path, array $callback, ?callable $permission): void {
        self::$routes[] = [
            'methods' => $methods,
            'path' => $path,
            'callback' => $callback,
            'permission' => $permission ?? '__return_true',
        ];
    }

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

### Defining Routes

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
Route::put('/users/me/profile', [UserController::class, 'updateProfile'], 'is_user_logged_in');

// Partnership endpoints
Route::get('/partnerships', [PartnershipController::class, 'index'], 'is_user_logged_in');
Route::post('/partnerships', [PartnershipController::class, 'store'], 'is_user_logged_in');
Route::get('/partnerships/(?P<id>\d+)', [PartnershipController::class, 'show'], 'is_user_logged_in');
Route::put('/partnerships/(?P<id>\d+)', [PartnershipController::class, 'update'], 'is_user_logged_in');
Route::delete('/partnerships/(?P<id>\d+)', [PartnershipController::class, 'destroy'], 'is_user_logged_in');

// Lead endpoints
Route::get('/leads', [LeadController::class, 'index'], 'is_user_logged_in');
Route::post('/leads', [LeadController::class, 'store'], 'is_user_logged_in');
Route::get('/leads/(?P<id>\d+)', [LeadController::class, 'show'], 'is_user_logged_in');

// Dashboard stats
Route::get('/dashboard/stats', [DashboardController::class, 'getStats'], 'is_user_logged_in');

// Register all routes
Route::registerRoutes();
```

---

## Shortcode System

**File:** `includes/Core/Shortcodes.php`

```php
<?php
namespace LendingResourceHub\Core;

class Shortcodes {
    public function __construct() {
        add_shortcode('lrh_portal', [$this, 'renderPortal']);
        add_shortcode('lrh_biolink_dashboard', [$this, 'renderBiolinkDashboard']);
    }

    public function renderPortal($atts = []): string {
        if (!is_user_logged_in()) {
            return $this->loginRequired();
        }

        $user = wp_get_current_user();

        // Enqueue React app assets
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

        return '<div id="lrh-portal-root"></div>';
    }

    private function loginRequired(): string {
        $login_url = wp_login_url(get_permalink());
        return sprintf(
            '<div class="lrh-login-required">
                <p>Please <a href="%s">log in</a> to view the portal.</p>
            </div>',
            esc_url($login_url)
        );
    }
}
```

---

## Asset Management

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
        // Development or production mode detection
        $is_dev = defined('WP_DEBUG') && WP_DEBUG && file_exists(LRH_PLUGIN_DIR . '/assets/frontend/dist/.vite/manifest.json');

        if ($is_dev) {
            // Development: Use Vite dev server
            Vite\enqueue_asset(
                LRH_PLUGIN_DIR . '/assets/frontend/dist',
                'src/main.tsx',
                [
                    'handle' => 'lrh-portal-app',
                    'in-footer' => true,
                    'dependencies' => ['wp-element'],
                ]
            );
        } else {
            // Production: Use built assets
            wp_enqueue_script(
                'lrh-portal-app',
                LRH_PLUGIN_URL . 'assets/frontend/dist/main.js',
                ['wp-element'],
                LRH_VERSION,
                true
            );

            wp_enqueue_style(
                'lrh-portal-styles',
                LRH_PLUGIN_URL . 'assets/frontend/dist/main.css',
                [],
                LRH_VERSION
            );
        }
    }
}
```

---

## Integration Patterns

### FluentBooking Integration

**File:** `includes/Integrations/FluentBooking.php`

```php
<?php
namespace LendingResourceHub\Integrations;

use LendingResourceHub\Models\LeadSubmission;

class FluentBooking {
    public function __construct() {
        // Hook into FluentBooking appointment booked event
        add_action('fluent_booking/after_booking_scheduled', [$this, 'handleBooking'], 10, 2);
    }

    public function handleBooking($booking, $event): void {
        // Create lead submission from booking
        $lead = LeadSubmission::create([
            'loan_officer_id' => $event->user_id ?? 0,
            'first_name' => $booking->first_name,
            'last_name' => $booking->last_name,
            'email' => $booking->email,
            'phone' => $booking->phone ?? '',
            'lead_source' => 'FluentBooking',
            'status' => 'pending',
            'metadata' => json_encode([
                'booking_id' => $booking->id,
                'event_title' => $event->title,
                'scheduled_at' => $booking->start_time,
            ]),
        ]);

        // Optional: Sync to FluentCRM
        if (class_exists('FluentCrm\App\Models\Subscriber')) {
            $this->syncToFluentCRM($lead);
        }
    }

    private function syncToFluentCRM(LeadSubmission $lead): void {
        // Implementation in FluentCRM integration class
    }
}
```

### FluentForms Integration

**File:** `includes/Integrations/FluentForms.php`

```php
<?php
namespace LendingResourceHub\Integrations;

use LendingResourceHub\Models\LeadSubmission;

class FluentForms {
    public function __construct() {
        add_action('fluentform_submission_inserted', [$this, 'handleSubmission'], 10, 3);
    }

    public function handleSubmission($entryId, $formData, $form): void {
        // Get user ID from form meta or page owner
        $user_id = $this->getUserIdFromForm($form);

        // Create lead
        $lead = LeadSubmission::create([
            'loan_officer_id' => $user_id,
            'first_name' => $formData['first_name'] ?? '',
            'last_name' => $formData['last_name'] ?? '',
            'email' => $formData['email'] ?? '',
            'phone' => $formData['phone'] ?? '',
            'message' => $formData['message'] ?? '',
            'lead_source' => 'FluentForms',
            'status' => 'pending',
            'metadata' => json_encode([
                'form_id' => $form->id,
                'entry_id' => $entryId,
            ]),
        ]);
    }

    private function getUserIdFromForm($form): int {
        // Logic to determine user ID from form or page context
        return 0;
    }
}
```

---

## Related Documentation

- [02-architecture.md](./02-architecture.md) - Overall architecture
- [06-security-standards.md](./06-security-standards.md) - Security best practices
- [07-common-tasks.md](./07-common-tasks.md) - Adding models, endpoints, etc.
- [10-external-dependencies.md](./10-external-dependencies.md) - FRS Users Plugin, Fluent plugins
