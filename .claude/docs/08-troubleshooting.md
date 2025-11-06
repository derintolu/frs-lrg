# Troubleshooting

Common issues and solutions for the Lending Resource Hub plugin.

---

## Table of Contents

- [Plugin Won't Activate](#plugin-wont-activate)
- [API Returns 404](#api-returns-404)
- [Portal Shows Blank Screen](#portal-shows-blank-screen)
- [Changes Not Showing](#changes-not-showing)
- [Vite Dev Server Issues](#vite-dev-server-issues)
- [Database Issues](#database-issues)
- [React Build Errors](#react-build-errors)

---

## Plugin Won't Activate

### Symptom

White screen, fatal error, or "Plugin could not be activated" message when activating.

### Common Causes & Solutions

**1. PHP Version Too Old**

```bash
# Check PHP version
php -v

# Should be 8.1 or higher
# If not, update PHP in Local by Flywheel settings
```

**2. Missing Composer Dependencies**

```bash
# Install PHP dependencies
cd /path/to/frs-lrg
composer install

# Verify vendor directory exists
ls -la vendor/
```

**3. Syntax Errors**

```bash
# Check for PHP syntax errors
php -l lending-resource-hub.php
php -l includes/Core/Plugin.php

# Check all PHP files
find includes -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
```

**4. Conflicting Plugins**

```bash
# Deactivate all other plugins
wp plugin deactivate --all --exclude=frs-lrg

# Try activating
wp plugin activate frs-lrg

# Reactivate other plugins one by one
wp plugin activate plugin-name
```

**5. Check Error Log**

```bash
# WordPress debug log
tail -f /path/to/wp-content/debug.log

# PHP error log (Local by Flywheel)
tail -f ~/Library/Application\ Support/Local/logs/php-error.log
```

---

## API Returns 404

### Symptom

REST API endpoints return 404 Not Found error.

### Solutions

**1. Flush Rewrite Rules**

```bash
# Via WP-CLI
wp rewrite flush

# Or visit in browser
# http://hub21.local/wp-admin/options-permalink.php
# (Just visit, no need to change anything)
```

**2. Verify Routes are Registered**

```bash
# List all REST routes
wp rest route list | grep lrh

# Expected output:
# /lrh/v1/users/me
# /lrh/v1/partnerships
# /lrh/v1/leads
# etc.
```

**3. Check .htaccess**

```bash
# Ensure .htaccess exists
ls -la /path/to/wordpress/.htaccess

# Should contain WordPress rewrite rules
cat /path/to/wordpress/.htaccess | grep -A 5 "BEGIN WordPress"
```

**Expected .htaccess content:**

```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

**4. Check Route Registration**

**File:** `includes/Routes/api.php`

```php
// Ensure this line is at the bottom
Route::registerRoutes();
```

**File:** `includes/Core/Plugin.php`

```php
// Ensure routes are loaded
require_once LRH_PLUGIN_DIR . 'includes/Routes/api.php';
```

**5. Test with cURL**

```bash
# Test endpoint directly
curl -v "http://hub21.local/wp-json/lrh/v1/users/me"

# Should return JSON (even if unauthorized)
# If returns 404, routes not registered
```

---

## Portal Shows Blank Screen

### Symptom

Shortcode `[lrh_portal]` renders empty div, no React app visible.

### Solutions

**1. Check Browser Console**

```
Press F12 → Console tab
Look for JavaScript errors
```

**Common errors:**

- `lrhPortalConfig is not defined` - Config not passed from PHP
- `Failed to fetch module` - Vite dev server not running
- `Uncaught SyntaxError` - Build error in React code

**2. Verify Assets Loaded**

```
Press F12 → Network tab
Reload page
Filter by "JS"
```

**Should see:**
- `main.js` or `portal-dashboards.js` (200 OK)
- `main.css` or `portal-dashboards.css` (200 OK)

**If 404:**
- Run `npm run build`
- Check `assets/frontend/dist/` directory exists

**3. Check Root Element Exists**

```javascript
// In browser console
document.getElementById('lrh-portal-root')
// Should return: <div id="lrh-portal-root"></div>
// If null, shortcode not rendering div
```

**4. Verify Config Passed**

```javascript
// In browser console
window.lrhPortalConfig
// Should return object with userId, userName, etc.
// If undefined, PHP not passing config
```

**Fix:** Check `includes/Core/Shortcodes.php`

```php
wp_localize_script('lrh-portal-app', 'lrhPortalConfig', [
    'userId' => $user->ID,
    'userName' => $user->display_name,
    // ...
]);
```

**5. Check if Dev Server Running**

```bash
# Start dev server if not running
npm run dev:frontend

# Terminal should show:
# VITE v5.0.8  ready in 523 ms
# ➜  Local:   http://localhost:5173/
```

---

## Changes Not Showing

### CRITICAL: NEVER BLAME CACHING

**There is NO caching in development environment.**

### Troubleshooting Steps

**1. Check Dev Server Detected Change**

```bash
# Terminal running `npm run dev` should show:
10:23:45 AM [vite] hmr update /src/frontend/components/Dashboard.tsx
```

**If no output:**
- File not saved (check for dot next to filename in editor)
- Dev server not watching this file (check Vite config)
- Dev server crashed (check terminal for errors)

**2. Verify Computed Styles in Browser**

```
Press F12 → Elements tab
Select element
Check Styles or Computed tab
Verify your CSS rule appears
```

**3. Check for JavaScript Errors**

```
Press F12 → Console tab
Look for errors that might prevent updates
```

**4. Hard Reload Browser**

```
Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)
```

**5. Restart Dev Server**

```bash
# Stop server (Ctrl+C)
# Clear cache
rm -rf node_modules/.vite
# Restart
npm run dev:frontend
```

**6. Check if Editing Correct File**

```bash
# Search for text you're trying to change
grep -r "YourTextHere" src/frontend/

# Verify path matches file you're editing
```

**7. Verify Build Completed**

```bash
# If using production build
npm run build

# Terminal should show:
# ✓ built in 5.23s

# Check build output exists
ls -la assets/frontend/dist/main.js
```

---

## Vite Dev Server Issues

### Port Already in Use

**Error:**

```
Error: Port 5173 is already in use
```

**Solution:**

```bash
# Find process using port
lsof -ti:5173

# Kill process
lsof -ti:5173 | xargs kill -9

# Or use different port
vite --port 5175
```

### CORS Errors

**Error in browser console:**

```
Access to fetch at 'http://localhost:5173/@vite/client' from origin 'http://hub21.local'
has been blocked by CORS policy
```

**Solution:** Check Vite config

**File:** `vite.frontend.config.js`

```javascript
export default defineConfig({
  server: {
    cors: true,               // ✅ Must be true
    origin: 'http://localhost:5173',
    host: 'localhost',
    port: 5173,
  },
});
```

### HMR Not Working

**Symptom:** Full page reload instead of HMR

**Solutions:**

1. **Check WebSocket connection**

```
Press F12 → Network tab → WS filter
Should see connection to ws://localhost:5173
```

2. **Restart dev server**

```bash
npm run dev:frontend
```

3. **Clear Vite cache**

```bash
rm -rf node_modules/.vite
npm run dev:frontend
```

---

## Database Issues

### Table Not Created

**Symptom:** Error: "Table 'wp_partnerships' doesn't exist"

**Solutions:**

```bash
# 1. Deactivate and reactivate plugin
wp plugin deactivate frs-lrg
wp plugin activate frs-lrg

# 2. Verify table exists
wp db query "SHOW TABLES LIKE 'wp_partnerships'"

# 3. Check migration ran
wp db query "DESCRIBE wp_partnerships"

# 4. Manually run migration
# Edit includes/Core/Install.php, add error logging
```

### Eloquent ORM Not Working

**Error:** `Call to undefined method query()`

**Solutions:**

1. **Check Eloquent initialized**

**File:** `includes/Core/Plugin.php`

```php
use LendingResourceHub\Core\Database;

public function __construct() {
    Database::init(); // ✅ Must be called early
    // ...
}
```

2. **Verify composer dependencies**

```bash
composer show prappo/wp-eloquent
# Should show version ^3.0
```

3. **Check database credentials**

```php
// wp-config.php
define('DB_NAME', 'local');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_HOST', 'localhost');
```

---

## React Build Errors

### TypeScript Errors

**Error:**

```
src/components/Dashboard.tsx:12:5 - error TS2322: Type 'string' is not assignable to type 'number'.
```

**Solutions:**

```bash
# 1. Run type check
npm run type-check

# 2. Fix type errors in code
# Update types to match usage

# 3. If types are correct but still error, restart TS server
# VS Code: Cmd+Shift+P → "TypeScript: Restart TS Server"
```

### Missing Dependencies

**Error:**

```
Module not found: Can't resolve '@/components/ui/button'
```

**Solutions:**

```bash
# 1. Check file exists
ls src/components/ui/button.tsx

# 2. Check tsconfig.json alias
cat tsconfig.json | grep "@"
# Should show: "@/*": ["./src/frontend/*"]

# 3. Restart dev server
npm run dev:frontend
```

### Out of Memory

**Error:**

```
FATAL ERROR: Reached heap limit Allocation failed - JavaScript heap out of memory
```

**Solutions:**

```bash
# Increase Node.js memory
export NODE_OPTIONS="--max-old-space-size=4096"
npm run build

# Or add to package.json
"scripts": {
  "build": "NODE_OPTIONS='--max-old-space-size=4096' vite build"
}
```

---

## Quick Diagnostic Commands

```bash
# Check plugin status
wp plugin list | grep frs-lrg

# Check PHP version
php -v

# Check Node version
node -v

# Check if dev server running
lsof -i:5173

# Check database tables
wp db query "SHOW TABLES LIKE 'wp_%'"

# Check REST API routes
wp rest route list | grep lrh

# Check for PHP errors
tail -20 wp-content/debug.log

# Flush everything
wp rewrite flush
wp cache flush
rm -rf node_modules/.vite
```

---

## Getting Help

If issue persists after trying these solutions:

1. **Collect information:**
   - Error message (exact text)
   - Browser console output
   - Terminal output
   - Steps to reproduce

2. **Check logs:**
   - `wp-content/debug.log`
   - Browser console (F12)
   - Terminal output from `npm run dev`

3. **Provide context:**
   - What were you doing when error occurred?
   - What have you already tried?
   - Any recent changes to code?

---

## Related Documentation

- [01-development-workflow.md](./01-development-workflow.md) - Dev server setup
- [02-architecture.md](./02-architecture.md) - System architecture
- [07-common-tasks.md](./07-common-tasks.md) - Development tasks
