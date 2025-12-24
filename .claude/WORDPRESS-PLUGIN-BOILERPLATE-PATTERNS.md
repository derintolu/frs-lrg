# WordPress Plugin Boilerplate - Opinionated Patterns

**THIS IS THE CORRECT WAY TO BUILD WITH THIS BOILERPLATE**

Study these patterns from working plugins (frs-wp-users, frs-partnership-portal) and ALWAYS follow them.

---

## Rule #0: Admin Architecture (MOST IMPORTANT)

### 🚨 CRITICAL: Admin = PHP Templates, Frontend = React Apps

**The WordPress admin interface uses traditional PHP templates, NOT React.**

**React is ONLY used for frontend shortcodes.**

### ✅ CORRECT Pattern (from frs-partnership-portal)

**Admin Menu with PHP Callbacks:**
```php
// includes/Admin/Menu.php
class Menu {
    use Base;

    public function menu() {
        add_menu_page(
            'My Plugin',
            'My Plugin',
            'manage_options',
            'my-plugin',
            array($this, 'dashboard_page'), // ← PHP callback method
            'dashicons-admin-generic',
            3
        );

        add_submenu_page(
            'my-plugin',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'my-plugin',
            array($this, 'dashboard_page') // ← PHP callback
        );

        add_submenu_page(
            'my-plugin',
            'Partnerships',
            'Partnerships',
            'manage_options',
            'my-partnerships', // ← Real slug, not hash route
            array($this, 'partnerships_page') // ← PHP callback
        );
    }

    public function dashboard_page() {
        Dashboard::get_instance()->render();
    }

    public function partnerships_page() {
        Partnerships::get_instance()->render();
    }
}
```

**Admin Page Class:**
```php
// includes/Admin/Dashboard.php
namespace MyPlugin\Admin;

use MyPlugin\Models\Partnership;
use MyPlugin\Traits\Base;

class Dashboard {
    use Base;

    public function render() {
        // Get data using Eloquent
        $total = Partnership::count();
        $active = Partnership::where('status', 'active')->count();

        // Load PHP template
        include MY_PLUGIN_DIR . 'views/admin/dashboard.php';
    }
}
```

**Admin Template:**
```php
// views/admin/dashboard.php
<div class="wrap">
    <h1><?php esc_html_e('Dashboard', 'my-plugin'); ?></h1>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div style="background: #fff; padding: 20px; border-left: 4px solid #2271b1;">
            <h3 style="font-size: 32px; color: #2271b1;">
                <?php echo esc_html($active); ?>
            </h3>
            <p style="color: #646970;">
                <?php esc_html_e('Active Partnerships', 'my-plugin'); ?>
            </p>
        </div>
    </div>
</div>
```

**Plugin Initialization:**
```php
// plugin.php
public function init() {
    if (is_admin()) {
        Menu::get_instance()->init();
        // NOTE: Do NOT bootstrap Admin assets class
        // Admin uses PHP templates, not React
    }

    // Frontend assets for shortcodes (React apps)
    Frontend::get_instance()->bootstrap();
}
```

### ❌ WRONG Pattern - Do NOT do this!

```php
// ❌ WRONG - Hash routes in menu
add_submenu_page(
    'my-plugin',
    'Partnerships',
    'Partnerships',
    'manage_options',
    'my-plugin/#/partnerships', // ❌ Hash route doesn't work
    null // ❌ No callback
);

// ❌ WRONG - React div in admin page
public function admin_page() {
    ?>
    <div id="my-plugin-admin-root"></div> <!-- ❌ This is for frontend, not admin -->
    <?php
}

// ❌ WRONG - Bootstrapping React admin assets
if (is_admin()) {
    Admin::get_instance()->bootstrap(); // ❌ Remove this!
}
```

**Why This Matters:**
- WordPress admin pages expect PHP callbacks, not React routers
- Hash routes (`#/route`) don't work with WordPress menu system
- React admin apps add unnecessary complexity
- Traditional PHP admin pages are faster and more maintainable
- React should only be used for complex frontend UIs (shortcodes)

---

## Rule #1: Admin Asset Enqueuing Pattern

**NOTE: This rule applies ONLY if you need React for a specific admin feature (rare).**
**For most admin pages, use Rule #0 (PHP templates) instead.**

### ✅ CORRECT Pattern (from frs-wp-users)

```php
// includes/Assets/Admin.php
namespace YourPlugin\Assets;

use YourPlugin\Traits\Base;
use YourPlugin\Libs\Assets;

class Admin {
    use Base;

    const HANDLE = 'your-plugin-admin';
    const OBJ_NAME = 'yourPluginAdmin';
    const DEV_SCRIPT = 'src/admin/main.jsx';

    private $allowed_screens = array(
        'toplevel_page_your-plugin-slug',
        'your-plugin-slug_page_submenu-page',
    );

    public function bootstrap() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
    }

    public function enqueue_script( $screen ) {
        // SIMPLE! Just check if screen matches
        if ( in_array( $screen, $this->allowed_screens, true ) ) {
            Assets\enqueue_asset(
                YOUR_PLUGIN_DIR . '/assets/admin/dist',
                self::DEV_SCRIPT,
                $this->get_config()
            );
            wp_localize_script( self::HANDLE, self::OBJ_NAME, $this->get_data() );
        }
    }
}
```

### ❌ WRONG Pattern - Do NOT do this!

```php
public function enqueue_script( $screen ) {
    // ❌ DON'T add frontend template logic in Admin class!
    if ( ! is_admin() ) {
        $template_slug = get_page_template_slug();
        // This is for Frontend.php, not Admin.php!
    }

    // ❌ DON'T manipulate $current_screen variable unnecessarily
    $current_screen = $screen; // Just use $screen directly!
}
```

**Why this matters:** The `$screen` parameter from `admin_enqueue_scripts` hook already contains the correct screen ID. Don't overcomplicate it.

---

## Rule #2: Plugin Initialization Pattern

### ✅ CORRECT Pattern

```php
// plugin.php
final class YourPlugin {
    use Base;

    public function init() {
        // Admin components ONLY in admin
        if ( is_admin() ) {
            Menu::get_instance()->init();
            Admin::get_instance()->bootstrap(); // ← This enqueues admin assets
        }

        // Frontend components
        Frontend::get_instance()->bootstrap();

        // Core functionality (works in both admin and frontend)
        API::get_instance()->init();
        Shortcode::get_instance()->init();
    }
}
```

**Key Points:**
- Admin assets class goes in `if ( is_admin() )` block
- Always call `->bootstrap()` method
- Frontend class bootstraps outside admin check
- Core functionality (API, shortcodes) runs everywhere

---

## Rule #3: Admin Screen IDs

WordPress admin screen IDs follow these patterns:

```
toplevel_page_{menu-slug}                    // Main menu page
{parent-slug}_page_{submenu-slug}            // Submenu page
```

**Example for LRH plugin:**
- Main page: `toplevel_page_lending-resource-hub`
- Submenu: `lending-resource-hub_page_partnerships` (if you had a regular submenu)

**For HashRouter React apps:**
The submenu items use `#/route` which all share the same screen ID as the parent page.

---

## Rule #4: Localized Data Structure

### What React App Expects

```javascript
// Admin React app uses:
window.yourPluginAdmin = {
    apiUrl: 'http://site.local/wp-json/prefix/v1/',
    nonce: 'abc123...',
    userId: 1,
    userName: 'Admin User',
    userEmail: 'admin@example.com',
    isAdmin: true
};
```

### How to Provide It

```php
public function get_data() {
    $current_user = wp_get_current_user();

    return array(
        'apiUrl'    => rest_url( YOUR_ROUTE_PREFIX . '/' ),
        'nonce'     => wp_create_nonce( 'wp_rest' ),
        'userId'    => $current_user->ID,
        'userName'  => $current_user->display_name,
        'userEmail' => $current_user->user_email,
        'isAdmin'   => is_admin(),
    );
}
```

**⚠️ Important:** Make sure `apiUrl` includes the trailing slash after the route prefix!

---

## Rule #5: File Structure

```
your-plugin/
├── includes/
│   ├── Admin/
│   │   └── Menu.php          # Registers admin menu items
│   ├── Assets/
│   │   ├── Admin.php         # Enqueues ADMIN React app
│   │   └── Frontend.php      # Enqueues FRONTEND React app
│   ├── Core/
│   │   ├── Api.php           # Registers REST API routes
│   │   ├── Shortcode.php     # Registers shortcodes
│   │   └── Install.php       # Database migrations
│   ├── Controllers/          # API endpoint handlers
│   ├── Models/               # Eloquent models
│   └── Routes/
│       └── Api.php           # Route definitions
├── src/
│   ├── admin/                # Admin React app
│   │   ├── main.jsx          # Entry point
│   │   ├── routes.jsx        # HashRouter routes
│   │   └── pages/            # Page components
│   └── frontend/             # Frontend React app
│       ├── portal/main.tsx   # Portal entry point
│       └── components/       # Reusable components
├── assets/
│   ├── admin/dist/           # Built admin assets
│   └── frontend/dist/        # Built frontend assets
└── plugin.php                # Main plugin class
```

---

## Rule #6: Admin Menu + React SPA Pattern

### Menu Registration

```php
// includes/Admin/Menu.php
public function menu() {
    // Main menu
    add_menu_page(
        'My Plugin',
        'My Plugin',
        'manage_options',
        'my-plugin',  // ← This becomes toplevel_page_my-plugin
        array( $this, 'admin_page' ),
        'dashicons-admin-generic'
    );

    // Submenu items for React HashRouter
    add_submenu_page(
        'my-plugin',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'my-plugin',  // Same slug = replaces "My Plugin" with "Dashboard"
        array( $this, 'admin_page' )
    );

    add_submenu_page(
        'my-plugin',
        'Settings',
        'Settings',
        'manage_options',
        'my-plugin/#/settings',  // ← Hash route
        null  // ← No callback for hash routes
    );
}

public function admin_page() {
    echo '<div id="my-plugin-admin-root"></div>';
}
```

### React Router Setup

```jsx
// src/admin/routes.jsx
import { createHashRouter } from "react-router-dom";

export const router = createHashRouter([
  {
    path: "/",
    element: <Layout />,
    children: [
      { path: "/", element: <Dashboard /> },
      { path: "settings", element: <Settings /> },
    ],
  },
]);
```

```jsx
// src/admin/main.jsx
import { RouterProvider } from "react-router-dom";
import { router } from "./routes";

const el = document.getElementById("my-plugin-admin-root");
if (el) {
  ReactDOM.createRoot(el).render(<RouterProvider router={router} />);
}
```

---

## Rule #7: Build Commands

```bash
# Development (with HMR)
npm run dev              # Both admin + frontend
npm run dev:admin        # Admin only
npm run dev:frontend     # Frontend only

# Production build
npm run build            # Builds admin, frontend, and blocks

# What this does:
# 1. Runs vite build for frontend → assets/frontend/dist/
# 2. Runs vite build for admin → assets/admin/dist/
# 3. Runs wp-scripts build for blocks → assets/blocks/
```

**⚠️ Critical:** Always run `npm run build` before testing admin pages to ensure latest code is compiled!

---

## Rule #8: Common Mistakes to Avoid

### ❌ Mistake 1: Mixing Frontend Logic in Admin Class

```php
// ❌ WRONG - This is Admin.php, not Frontend.php!
public function enqueue_script( $screen ) {
    if ( ! is_admin() ) {
        // Frontend logic doesn't belong here!
    }
}
```

### ❌ Mistake 2: Forgetting to Rebuild

```php
// You changed: src/admin/pages/Dashboard.jsx
// You see: Old dashboard with no changes
// Why: Forgot to run npm run build!
```

### ❌ Mistake 3: Wrong Screen ID

```php
private $allowed_screens = array(
    'my-plugin',  // ❌ Wrong! Missing prefix
);

// ✅ Correct:
private $allowed_screens = array(
    'toplevel_page_my-plugin',  // Main menu page
);
```

### ❌ Mistake 4: Missing apiUrl Trailing Slash

```php
'apiUrl' => rest_url( 'prefix/v1' ),  // ❌ Missing /
'apiUrl' => rest_url( 'prefix/v1/' ), // ✅ Correct
```

---

## Rule #9: Debugging Checklist

**If admin page shows blank:**

1. **Check if assets are built:**
   ```bash
   ls -la assets/admin/dist/
   # Should see: manifest.json, assets/ folder
   ```

2. **Check browser console:**
   - Open DevTools → Console
   - Look for React errors
   - Check if JS files are loading (Network tab)

3. **Check screen ID:**
   ```php
   add_action( 'admin_enqueue_scripts', function( $screen ) {
       error_log( 'Current screen: ' . $screen );
   });
   ```

4. **Check if script is enqueued:**
   - View page source
   - Search for your handle: `lrh-admin` or `your-plugin-admin`
   - Should see: `<script src="...admin/dist/assets/main-xxx.js"`

5. **Check localized data:**
   - View page source
   - Search for: `var lrhAdmin = {`
   - Should see all data: apiUrl, nonce, userId, etc.

---

## Rule #10: The Golden Checklist

Before declaring admin page "done":

- [ ] Admin.php has NO frontend template logic
- [ ] allowed_screens uses correct `toplevel_page_{slug}` format
- [ ] enqueue_script() method is simple (just screen check)
- [ ] Ran `npm run build` after React changes
- [ ] Tested in browser (not just assumed it works)
- [ ] Checked browser console for errors
- [ ] Verified assets load in Network tab
- [ ] Confirmed localized data structure matches React expectations

---

## Summary: The Boilerplate Way

**Philosophy:** Keep it simple. The boilerplate handles the complexity.

1. **Admin assets** → Enqueue ONLY on admin screens, check screen ID, done.
2. **Frontend assets** → Enqueue on frontend, check for shortcode, done.
3. **Don't mix concerns** → Admin.php for admin, Frontend.php for frontend.
4. **Always build** → React changes require `npm run build`.
5. **Follow patterns** → Study working plugin (frs-wp-users), copy the pattern.

**When in doubt:** Look at frs-wp-users and do exactly what it does.

---

## Quick Reference

### File Locations
- Admin React app: `src/admin/`
- Admin entry: `src/admin/main.jsx`
- Admin routes: `src/admin/routes.jsx`
- Admin pages: `src/admin/pages/`
- Admin enqueue: `includes/Assets/Admin.php`
- Menu registration: `includes/Admin/Menu.php`
- Plugin init: `plugin.php`

### Key Commands
```bash
npm run build        # Build everything
npm run dev:admin    # Dev mode with HMR
```

### Screen ID Format
```
toplevel_page_{menu-slug}
{parent}_page_{submenu}
```

### Essential Data
```php
'apiUrl' => rest_url( PREFIX . '/' ),
'nonce'  => wp_create_nonce( 'wp_rest' ),
```

---

**Last Updated:** 2025-01-11
**Learned From:** frs-wp-users (working plugin)
**Applied To:** frs-lrg (Lending Resource Hub)
