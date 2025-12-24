# External Dependencies

Complete guide to all external dependencies required by the Lending Resource Hub plugin.

---

## Table of Contents

- [WordPress Plugins](#wordpress-plugins)
- [PHP Composer Packages](#php-composer-packages)
- [NPM Packages](#npm-packages)
- [Critical Dependency: FRS Users Plugin](#critical-dependency-frs-users-plugin)

---

## WordPress Plugins

### Required Plugins

**1. FRS Users Plugin**

**CRITICAL DEPENDENCY** - The plugin WILL NOT work without this.

- **Purpose:** Provides the `Profile` model for user profile data
- **Used for:** User headshots, bios, contact info, social links
- **Namespace:** `FRSUsers\Models\Profile`
- **Status:** **REQUIRED**

**Installation:**
```bash
# Ensure FRS Users Plugin is installed and activated
wp plugin list | grep frs-users
```

**Model Usage:**
```php
use FRSUsers\Models\Profile;

// Get user profile
$profile = Profile::where('user_id', $user_id)->first();

// Access profile data
$name = $profile->full_name;
$email = $profile->email;
$headshot = $profile->headshot_url;
$bio = $profile->bio;
```

---

### Optional Plugins

**2. FluentBooking**

- **Purpose:** Appointment booking system
- **Integration:** Creates leads from booked appointments
- **Hook:** `fluent_booking/after_booking_scheduled`
- **Status:** Optional

**Integration File:** `includes/Integrations/FluentBooking.php`

```php
namespace LendingResourceHub\Integrations;

class FluentBooking {
    public function __construct() {
        add_action('fluent_booking/after_booking_scheduled', [$this, 'handleBooking'], 10, 2);
    }

    public function handleBooking($booking, $event): void {
        // Create lead from booking
        LeadSubmission::create([
            'loan_officer_id' => $event->user_id ?? 0,
            'first_name' => $booking->first_name,
            'last_name' => $booking->last_name,
            'email' => $booking->email,
            'phone' => $booking->phone ?? '',
            'lead_source' => 'FluentBooking',
        ]);
    }
}
```

**3. FluentForms**

- **Purpose:** Lead capture forms
- **Integration:** Creates leads from form submissions
- **Hook:** `fluentform_submission_inserted`
- **Status:** Optional

**Integration File:** `includes/Integrations/FluentForms.php`

```php
namespace LendingResourceHub\Integrations;

class FluentForms {
    public function __construct() {
        add_action('fluentform_submission_inserted', [$this, 'handleSubmission'], 10, 3);
    }

    public function handleSubmission($entryId, $formData, $form): void {
        // Create lead from form submission
        LeadSubmission::create([
            'loan_officer_id' => $this->getUserIdFromForm($form),
            'first_name' => $formData['first_name'] ?? '',
            'last_name' => $formData['last_name'] ?? '',
            'email' => $formData['email'] ?? '',
            'phone' => $formData['phone'] ?? '',
            'message' => $formData['message'] ?? '',
            'lead_source' => 'FluentForms',
        ]);
    }
}
```

**4. FluentCRM**

- **Purpose:** CRM integration for contact management
- **Integration:** Syncs leads to CRM contacts
- **Model:** `FluentCrm\App\Models\Subscriber`
- **Status:** Optional

**Integration File:** `includes/Integrations/FluentCRM.php`

```php
namespace LendingResourceHub\Integrations;

use FluentCrm\App\Models\Subscriber;

class FluentCRM {
    public function syncLead(LeadSubmission $lead): void {
        if (!class_exists('FluentCrm\App\Models\Subscriber')) {
            return;
        }

        Subscriber::updateOrCreate(
            ['email' => $lead->email],
            [
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'phone' => $lead->phone,
                'source' => $lead->lead_source,
                'status' => 'subscribed',
            ]
        );
    }
}
```

---

## PHP Composer Packages

### Required Packages

**File:** `composer.json`

```json
{
  "require": {
    "prappo/wp-eloquent": "^3.0"
  }
}
```

**1. prappo/wp-eloquent**

- **Version:** ^3.0.5
- **Purpose:** Eloquent ORM for WordPress
- **Used for:** Database models, query builder, relationships
- **Namespace:** `WeDevs\ORM\Eloquent`

**Installation:**
```bash
composer require prappo/wp-eloquent
```

**Initialization:**
```php
use WeDevs\ORM\Eloquent\Facade as DB;

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
```

---

## NPM Packages

### Production Dependencies

**File:** `package.json`

```json
{
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.20.0",
    "@radix-ui/react-avatar": "^1.0.4",
    "@radix-ui/react-dialog": "^1.0.5",
    "@radix-ui/react-dropdown-menu": "^2.0.6",
    "@radix-ui/react-select": "^2.0.0",
    "@radix-ui/react-tabs": "^1.0.4",
    "@radix-ui/react-label": "^2.0.2",
    "@radix-ui/react-slot": "^1.0.2",
    "lucide-react": "^0.294.0",
    "clsx": "^2.0.0",
    "tailwind-merge": "^2.0.0",
    "class-variance-authority": "^0.7.0",
    "jotai": "^2.6.0",
    "react-hook-form": "^7.48.0",
    "@hookform/resolvers": "^3.3.2",
    "zod": "^3.22.4"
  },
  "devDependencies": {
    "@types/react": "^18.2.43",
    "@types/react-dom": "^18.2.17",
    "@vitejs/plugin-react": "^4.2.1",
    "typescript": "^5.3.3",
    "vite": "^5.0.8",
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.16",
    "postcss": "^8.4.32",
    "@wordpress/scripts": "^27.0.0",
    "eslint": "^8.55.0",
    "prettier": "^3.1.0"
  }
}
```

### Key Dependencies Explained

**React Ecosystem:**
- `react` - UI library
- `react-dom` - DOM rendering
- `react-router-dom` - Client-side routing

**Radix UI Components:**
- `@radix-ui/*` - Accessible, unstyled component primitives
- Used for: Dialog, Dropdown, Select, Tabs, Avatar, etc.

**Icons:**
- `lucide-react` - Icon library (800+ icons)

**Utility:**
- `clsx` - Conditional className utility
- `tailwind-merge` - Merge Tailwind classes without conflicts
- `class-variance-authority` - Type-safe component variants

**State Management:**
- `jotai` - Atomic state management (3KB)

**Form Handling:**
- `react-hook-form` - Performant form library
- `@hookform/resolvers` - Validation resolvers
- `zod` - TypeScript-first schema validation

**Build Tools:**
- `vite` - Fast bundler with HMR
- `@vitejs/plugin-react` - Vite React plugin
- `typescript` - Type safety
- `tailwindcss` - Utility-first CSS framework
- `@wordpress/scripts` - Gutenberg block build tools

**Code Quality:**
- `eslint` - Linting
- `prettier` - Code formatting

### Installing Dependencies

```bash
# Install all dependencies
npm install

# Install specific dependency
npm install react-query

# Install dev dependency
npm install --save-dev @types/node
```

---

## Critical Dependency: FRS Users Plugin

### Why This is Critical

The Lending Resource Hub plugin **CANNOT FUNCTION** without the FRS Users Plugin because:

1. **Profile Model** - All user profile data is stored in FRS Users Plugin's `Profile` model
2. **No Fallback** - LRH does not have its own profile storage
3. **Hard Dependency** - Code directly imports `FRSUsers\Models\Profile`

### Profile Model Structure

**Namespace:** `FRSUsers\Models\Profile`

**Database Table:** `wp_frs_user_profiles`

**Key Fields:**
- `user_id` - WordPress user ID (foreign key)
- `full_name` - Display name
- `email` - Email address
- `phone_number` - Phone number
- `headshot_url` - Profile photo URL
- `bio` - Biography/description
- `title` - Job title
- `company` - Company name
- `website` - Website URL
- `social_links` - JSON of social media links

### Usage in LRH Plugin

**Getting Profile Data:**

```php
use FRSUsers\Models\Profile;

// Get profile by user ID
$profile = Profile::where('user_id', $user_id)->first();

if ($profile) {
    $name = $profile->full_name;
    $email = $profile->email;
    $headshot = $profile->headshot_url;
    $bio = $profile->bio;
}
```

**Updating Profile Data:**

```php
use FRSUsers\Models\Profile;

// Update profile
$profile = Profile::where('user_id', $user_id)->first();
if ($profile) {
    $profile->update([
        'full_name' => sanitize_text_field($_POST['name']),
        'email' => sanitize_email($_POST['email']),
        'bio' => sanitize_textarea_field($_POST['bio']),
    ]);
}
```

**Creating Profile:**

```php
use FRSUsers\Models\Profile;

// Create profile for new user
$profile = Profile::create([
    'user_id' => $user_id,
    'full_name' => $user->display_name,
    'email' => $user->user_email,
]);
```

### Checking if FRS Users Plugin is Active

```php
// Check if Profile model exists
if (!class_exists('FRSUsers\Models\Profile')) {
    wp_die('FRS Users Plugin is required. Please install and activate it.');
}

// Or use WP plugin check
if (!is_plugin_active('frs-users/frs-users.php')) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>Lending Resource Hub requires FRS Users Plugin to be activated.</p></div>';
    });
    return;
}
```

### What Happens Without FRS Users Plugin?

**Errors you'll see:**

```
Fatal error: Class 'FRSUsers\Models\Profile' not found
in /includes/Controllers/UserController.php on line 15
```

**Features that won't work:**
- User profile display
- Biolink pages (no profile data)
- Lead assignments (no user info)
- Partnership invitations (no contact info)
- Any feature requiring user profile data

### Installation Instructions

**Via WP-CLI:**

```bash
# Check if FRS Users Plugin is installed
wp plugin list | grep frs-users

# If not installed, install it
# (Assumes plugin is available in WordPress repository or local)
wp plugin install /path/to/frs-users.zip

# Activate
wp plugin activate frs-users
```

**Via WordPress Admin:**

1. Go to **Plugins → Add New**
2. Upload `frs-users.zip`
3. Click **Install Now**
4. Click **Activate**

---

## Dependency Checklist

Before starting development, verify all dependencies:

### WordPress Environment

- [ ] WordPress 6.4+
- [ ] PHP 8.1+
- [ ] MySQL 5.7+ or MariaDB 10.3+

### Required Plugins

- [ ] FRS Users Plugin (active)

### Optional Plugins (if using features)

- [ ] FluentBooking (for appointment leads)
- [ ] FluentForms (for form leads)
- [ ] FluentCRM (for CRM sync)

### PHP Packages

- [ ] prappo/wp-eloquent ^3.0 (installed via Composer)

### Node Packages

- [ ] Node.js 20+
- [ ] NPM packages installed (`npm install` run)

### Verification Commands

```bash
# Check WordPress version
wp core version

# Check PHP version
php -v

# Check plugins
wp plugin list

# Check Composer packages
composer show

# Check NPM packages
npm list --depth=0
```

---

## Related Documentation

- [02-architecture.md](./02-architecture.md) - How dependencies are used
- [04-backend-patterns.md](./04-backend-patterns.md) - Eloquent ORM usage
- [08-troubleshooting.md](./08-troubleshooting.md) - Dependency issues
