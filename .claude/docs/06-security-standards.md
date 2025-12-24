# Security Standards

Complete security guidelines and best practices for the Lending Resource Hub plugin.

---

## Table of Contents

- [Input Sanitization](#input-sanitization)
- [Database Queries](#database-queries)
- [Permission Checks](#permission-checks)
- [Nonce Verification](#nonce-verification)
- [Output Escaping](#output-escaping)
- [PHP 8.1+ Standards](#php-81-standards)

---

## Input Sanitization

### ALWAYS Sanitize User Input

**Never trust user input. ALWAYS sanitize before using.**

```php
// Email
$email = sanitize_email($_POST['email']);

// Text (single line)
$name = sanitize_text_field($_POST['name']);

// Text (multi-line, preserve line breaks)
$message = sanitize_textarea_field($_POST['message']);

// URL
$url = esc_url_raw($_POST['url']);

// Integer
$id = intval($_POST['id']);

// Floating point
$amount = floatval($_POST['amount']);

// HTML (allow safe tags)
$bio = wp_kses_post($_POST['bio']);

// Array of integers
$ids = array_map('intval', $_POST['ids'] ?? []);
```

### Custom Sanitization Functions

```php
<?php
namespace LendingResourceHub\Core;

class Sanitizer {
    /**
     * Sanitize phone number (US format)
     */
    public static function phone(string $phone): string {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Format as (XXX) XXX-XXXX
        if (strlen($phone) === 10) {
            return sprintf('(%s) %s-%s',
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6)
            );
        }

        return $phone;
    }

    /**
     * Sanitize array of emails
     */
    public static function emailArray(array $emails): array {
        return array_filter(
            array_map('sanitize_email', $emails),
            'is_email'
        );
    }

    /**
     * Sanitize JSON data
     */
    public static function json(string $json): ?array {
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }
}
```

---

## Database Queries

### CRITICAL: ALWAYS Use Eloquent or Prepared Statements

**NEVER use raw SQL with user input.**

### ✅ CORRECT - Using Eloquent ORM

```php
use LendingResourceHub\Models\Partnership;

// ✅ GOOD - Eloquent automatically escapes
$partnerships = Partnership::where('loan_officer_id', $user_id)
    ->where('status', 'active')
    ->get();

// ✅ GOOD - Eloquent with user input
$email = sanitize_email($_POST['email']);
$partnership = Partnership::where('partner_email', $email)->first();
```

### ✅ CORRECT - Using Prepared Statements

```php
global $wpdb;

// ✅ GOOD - $wpdb->prepare()
$user_id = intval($_GET['user_id']);
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}partnerships WHERE loan_officer_id = %d",
    $user_id
));

// ✅ GOOD - Multiple placeholders
$loan_officer_id = intval($_POST['loan_officer_id']);
$status = sanitize_text_field($_POST['status']);
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}partnerships
     WHERE loan_officer_id = %d AND status = %s",
    $loan_officer_id,
    $status
));
```

### ❌ NEVER DO THIS - Raw SQL Injection Vulnerability

```php
// ❌ EXTREMELY DANGEROUS - SQL INJECTION VULNERABILITY
$user_id = $_GET['user_id']; // Attacker can send: "1 OR 1=1"
$results = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}partnerships WHERE loan_officer_id = $user_id"
);

// ❌ STILL VULNERABLE - String interpolation
$email = $_POST['email']; // Attacker can send: "x' OR '1'='1"
$results = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}partnerships WHERE partner_email = '$email'"
);
```

### Prepared Statement Placeholders

```php
// %d - Integer
$wpdb->prepare("SELECT * FROM table WHERE id = %d", $id);

// %s - String
$wpdb->prepare("SELECT * FROM table WHERE name = %s", $name);

// %f - Float
$wpdb->prepare("SELECT * FROM table WHERE amount = %f", $amount);

// Multiple placeholders
$wpdb->prepare(
    "SELECT * FROM table WHERE user_id = %d AND status = %s AND amount > %f",
    $user_id,
    $status,
    $amount
);
```

---

## Permission Checks

### REST API Permissions

```php
<?php
namespace LendingResourceHub\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class PartnershipController {
    /**
     * Check if user can view partnerships
     */
    public function checkPermissions(WP_REST_Request $request): bool {
        // Must be logged in
        if (!is_user_logged_in()) {
            return false;
        }

        // Check user role
        $user = wp_get_current_user();
        $allowed_roles = ['loan_officer', 'realtor_partner', 'manager', 'administrator'];

        return !empty(array_intersect($allowed_roles, $user->roles));
    }

    /**
     * Get partnerships (with permission check)
     */
    public function index(WP_REST_Request $request): WP_REST_Response|WP_Error {
        // Double-check permissions in handler
        if (!$this->checkPermissions($request)) {
            return new WP_Error(
                'forbidden',
                'You do not have permission to view partnerships',
                ['status' => 403]
            );
        }

        $user_id = get_current_user_id();
        $partnerships = Partnership::forLoanOfficer($user_id)->get();

        return new WP_REST_Response([
            'success' => true,
            'data' => $partnerships,
        ], 200);
    }

    /**
     * Update partnership (check ownership)
     */
    public function update(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param('id');
        $partnership = Partnership::find($id);

        if (!$partnership) {
            return new WP_Error('not_found', 'Partnership not found', ['status' => 404]);
        }

        // Check if user owns this partnership
        $user_id = get_current_user_id();
        if ($partnership->loan_officer_id !== $user_id && $partnership->agent_id !== $user_id) {
            return new WP_Error(
                'forbidden',
                'You do not have permission to update this partnership',
                ['status' => 403]
            );
        }

        // Update
        $partnership->update([
            'status' => sanitize_text_field($request->get_param('status')),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'data' => $partnership,
        ], 200);
    }
}
```

### WordPress Capability Checks

```php
// Check if user can manage options
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

// Check if user has custom capability
if (!current_user_can('manage_partnerships')) {
    return new WP_Error('forbidden', 'Access denied', ['status' => 403]);
}

// Check if user can edit post
if (!current_user_can('edit_post', $post_id)) {
    wp_die('You cannot edit this post');
}

// Check multiple capabilities (any)
if (!current_user_can('manage_options') && !current_user_can('manage_portal_settings')) {
    wp_die('Insufficient permissions');
}
```

---

## Nonce Verification

### REST API Nonce

```php
// Check REST nonce in request
if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
    return new WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
}
```

### Form Nonce

```php
// Create nonce in form
<form method="post">
    <?php wp_nonce_field('save_partnership', 'partnership_nonce'); ?>
    <input type="text" name="email" />
    <button type="submit">Save</button>
</form>

// Verify nonce on submission
if (!isset($_POST['partnership_nonce']) || !wp_verify_nonce($_POST['partnership_nonce'], 'save_partnership')) {
    wp_die('Security check failed');
}
```

### AJAX Nonce

```javascript
// Frontend: Send nonce with AJAX request
fetch('/wp-json/lrh/v1/partnerships', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': window.lrhPortalConfig.restNonce,
  },
  body: JSON.stringify(data),
});
```

```php
// Backend: Create nonce
wp_localize_script('lrh-portal-app', 'lrhPortalConfig', [
    'restNonce' => wp_create_nonce('wp_rest'),
]);
```

---

## Output Escaping

### ALWAYS Escape Output

```php
// HTML content
echo esc_html($user_name);

// HTML attribute
<input type="text" value="<?php echo esc_attr($user_email); ?>" />

// URL
<a href="<?php echo esc_url($profile_url); ?>">Profile</a>

// JavaScript string
<script>
var userName = '<?php echo esc_js($user_name); ?>';
</script>

// Textarea content
<textarea><?php echo esc_textarea($bio); ?></textarea>

// Allow safe HTML tags
echo wp_kses_post($content); // Allows <p>, <a>, <strong>, etc.

// Allow specific HTML tags
echo wp_kses($content, [
    'a' => ['href' => [], 'title' => []],
    'strong' => [],
    'em' => [],
]);
```

### React Components (Automatic Escaping)

```tsx
// React automatically escapes variables
export function UserProfile({ name, bio }: { name: string; bio: string }): JSX.Element {
  return (
    <div>
      {/* Automatically escaped - safe from XSS */}
      <h1>{name}</h1>
      <p>{bio}</p>
    </div>
  );
}

// dangerouslySetInnerHTML - ONLY use when absolutely necessary
export function RichContent({ html }: { html: string }): JSX.Element {
  // ⚠️ DANGEROUS - Only use with sanitized HTML
  return <div dangerouslySetInnerHTML={{ __html: html }} />;
}
```

---

## PHP 8.1+ Standards

### Type Declarations

```php
<?php
namespace LendingResourceHub\Controllers;

class UserController {
    /**
     * Get user profile
     *
     * @param int $userId User ID
     * @return array|null User profile data or null if not found
     */
    public function getUserProfile(int $userId): ?array {
        $user = get_user_by('id', $userId);

        if (!$user) {
            return null;
        }

        return [
            'id' => $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
        ];
    }

    /**
     * Update user profile
     *
     * @param int $userId User ID
     * @param array $data Profile data
     * @return bool Success status
     */
    public function updateProfile(int $userId, array $data): bool {
        $updated = wp_update_user([
            'ID' => $userId,
            'display_name' => $data['name'] ?? '',
            'user_email' => $data['email'] ?? '',
        ]);

        return !is_wp_error($updated);
    }
}
```

### Readonly Properties (PHP 8.1+)

```php
<?php
namespace LendingResourceHub\Core;

class Config {
    public function __construct(
        public readonly string $apiUrl,
        public readonly string $pluginDir,
        public readonly string $pluginUrl,
        public readonly string $version,
    ) {}
}

// Usage
$config = new Config(
    apiUrl: rest_url('lrh/v1/'),
    pluginDir: LRH_PLUGIN_DIR,
    pluginUrl: LRH_PLUGIN_URL,
    version: LRH_VERSION,
);

echo $config->apiUrl; // OK
$config->apiUrl = 'new'; // Error: Cannot modify readonly property
```

### Named Arguments (PHP 8.0+)

```php
// Before (positional arguments)
$partnership = Partnership::create([
    'loan_officer_id' => 123,
    'partner_email' => 'realtor@example.com',
    'status' => 'pending',
]);

// After (named arguments)
$partnership = Partnership::create(
    loan_officer_id: 123,
    partner_email: 'realtor@example.com',
    status: 'pending',
);
```

### Strict Comparison

```php
// ✅ GOOD - Strict comparison (===)
if ($status === 'active') {
    // ...
}

if (in_array('loan_officer', $user->roles, true)) { // Third param: strict
    // ...
}

// ❌ BAD - Loose comparison (==)
if ($status == 'active') { // Can have unexpected results
    // ...
}
```

---

## Security Checklist

### Before Committing Code

- [ ] All user input sanitized
- [ ] All database queries use Eloquent or prepared statements
- [ ] Permission checks in place
- [ ] Nonces verified where needed
- [ ] All output escaped
- [ ] Type declarations on all functions
- [ ] Strict comparison used (===)
- [ ] No hardcoded secrets or API keys
- [ ] Error messages don't reveal sensitive info

### Code Review Questions

1. Can this input be manipulated by a user?
2. Is this database query using prepared statements?
3. Does this endpoint check user permissions?
4. Is this nonce being verified?
5. Is this output being escaped?
6. Could this code expose sensitive data?

---

## Related Documentation

- [04-backend-patterns.md](./04-backend-patterns.md) - Eloquent models, API controllers
- [05-frontend-patterns.md](./05-frontend-patterns.md) - React components
- [07-common-tasks.md](./07-common-tasks.md) - Adding secure endpoints
