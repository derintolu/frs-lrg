# Frontend File Mappings Inventory

**Last Updated:** 2025-12-03
**Purpose:** Complete inventory of all frontend source files, build outputs, and WordPress integrations for production deployment.

---

## 📋 Executive Summary

The plugin has **7 distinct frontend applications** plus **19 Gutenberg blocks**:

### React Applications (Vite):
1. **Main Frontend** - Portal infrastructure
2. **Admin** - WordPress admin interface
3. **Welcome Portal** - Onboarding experience
4. **Partnerships Section** - Partnership management
5. **Realtor Portal** - Realtor-specific interface
6. **Widget** - Mortgage calculator embeds
7. **Partner Company Portal** - (Not in main build)

### Gutenberg Blocks (wp-scripts):
19 blocks compiled separately with WordPress block build tools

---

## 🏗️ Vite Build Configurations

### 1. Frontend Portal (Main)
```javascript
// vite.frontend.config.js
{
  input: "src/frontend/main.jsx",
  outDir: "assets/frontend/dist",
  port: 5173
}
```

**Source Entry:** `src/frontend/main.jsx`
**Build Output:** `assets/frontend/dist/`
**WordPress Handle:** `lrh-frontend`
**Enqueued By:** `includes/Assets/Frontend.php::enqueue_scripts()`
**Loaded On:** Pages with portal shortcodes or specific portal page slugs

**Key Dependencies:**
- React Router (client-side routing)
- Tailwind CSS
- shadcn/ui components
- WordPress Interactivity API Router

**Shortcodes That Load This:**
- `[lrh_portal]`
- `[frs_partnership_portal]`
- `[lrh_portal_sidebar]`
- `[lrh_content_*]` (15+ content shortcodes)

---

### 2. Admin Interface
```javascript
// vite.admin.config.js
{
  input: "src/admin/main.jsx",
  outDir: "assets/admin/dist",
  port: 5174
}
```

**Source Entry:** `src/admin/main.jsx`
**Build Output:** `assets/admin/dist/`
**WordPress Handle:** `lrh-admin`
**Enqueued By:** Currently commented out in `plugin.php` (Admin uses PHP templates)
**Loaded On:** WordPress admin pages (when enabled)

**Note:** Admin interface is primarily PHP-based. React admin is available but not currently active.

---

### 3. Welcome Portal
```javascript
// vite.welcome-portal.config.js
{
  input: "src/frontend/welcome-portal-main.jsx",
  outDir: "assets/welcome-portal/dist",
  port: 5180
}
```

**Source Entry:** `src/frontend/welcome-portal-main.jsx`
**Build Output:** `assets/welcome-portal/dist/`
**WordPress Handle:** `lrh-welcome-portal`
**Enqueued By:** `includes/Assets/Frontend.php::enqueue_welcome_portal_assets()`
**Loaded On:** Welcome/onboarding pages

---

### 4. Partnerships Section
```javascript
// vite.partnerships-section.config.js
{
  input: "src/frontend/partnerships-section-main.tsx",
  outDir: "assets/partnerships-section/dist",
  port: 5179
}
```

**Source Entry:** `src/frontend/partnerships-section-main.tsx`
**Build Output:** `assets/partnerships-section/dist/`
**WordPress Handle:** `lrh-partnerships-section`
**Enqueued By:** `includes/Assets/Frontend.php::enqueue_partnerships_section_assets()`
**Loaded On:** Partnership management pages

---

### 5. Realtor Portal
```javascript
// vite.realtor-portal.config.js
{
  input: "src/frontend/realtor-portal-main.tsx",
  outDir: "assets/realtor-portal/dist",
  port: 5181
}
```

**Source Entry:** `src/frontend/realtor-portal-main.tsx`
**Build Output:** `assets/realtor-portal/dist/`
**WordPress Handle:** `lrh-realtor-portal`
**Enqueued By:** `includes/Assets/Frontend.php::enqueue_realtor_portal_assets()`
**Loaded On:** Realtor-specific portal pages

---

### 6. Mortgage Calculator Widget
```javascript
// vite.widget.config.js
{
  input: "src/widget/widget.tsx",
  outDir: "assets/widget/dist",
  port: 5182
}
```

**Source Entry:** `src/widget/widget.tsx`
**Build Output:** `assets/widget/dist/`
**WordPress Handle:** `frs-mortgage-calculator`
**Enqueued By:** `includes/Assets/Frontend.php::enqueue_widget_assets()`
**Loaded On:** Pages with mortgage calculator shortcode or block

**Special Features:**
- Standalone embeddable widget
- Can be used outside WordPress
- Includes Goalee integration

---

### 7. Partner Company Portal (Not in Main Build)
**Note:** There is an `assets/partner-company-portal/dist/` directory, but no corresponding Vite config in the root. This may be:
- Legacy/deprecated
- Built separately
- Part of another plugin

**Action Required:** Verify if this needs to be in the build pipeline.

---

## 🧱 Gutenberg Blocks (wp-scripts)

### Build Configuration
```bash
# package.json
"block:start": "wp-scripts start --webpack-src-dir=src/blocks --output-path=assets/blocks"
"block:build": "wp-scripts build --webpack-src-dir=src/blocks --output-path=assets/blocks"
```

**Source Directory:** `src/blocks/`
**Build Output:** `assets/blocks/`
**Build Tool:** `@wordpress/scripts` (Webpack-based)

### Complete Block List (19 blocks):

| Block Name | Source | Output | Purpose |
|------------|--------|--------|---------|
| **bento** | `src/blocks/bento/` | `assets/blocks/bento/` | Bento grid layout |
| **biolink-button** | `src/blocks/biolink-button/` | `assets/blocks/biolink-button/` | Biolink CTA button |
| **biolink-form** | `src/blocks/biolink-form/` | `assets/blocks/biolink-form/` | Biolink lead form |
| **biolink-header** | `src/blocks/biolink-header/` | `assets/blocks/biolink-header/` | Biolink page header |
| **biolink-hidden-form** | `src/blocks/biolink-hidden-form/` | `assets/blocks/biolink-hidden-form/` | Hidden form logic |
| **biolink-page** | `src/blocks/biolink-page/` | `assets/blocks/biolink-page/` | Full biolink page |
| **biolink-page-backup** | `src/blocks/biolink-page-backup/` | `assets/blocks/biolink-page-backup/` | Backup template |
| **biolink-social** | `src/blocks/biolink-social/` | `assets/blocks/biolink-social/` | Social links |
| **biolink-spacer** | `src/blocks/biolink-spacer/` | `assets/blocks/biolink-spacer/` | Spacing control |
| **biolink-thankyou** | `src/blocks/biolink-thankyou/` | `assets/blocks/biolink-thankyou/` | Thank you page |
| **block-1** | `src/blocks/block-1/` | `assets/blocks/block-1/` | Generic block |
| **loan-officer** | `src/blocks/loan-officer/` | `assets/blocks/loan-officer/` | LO profile card |
| **loan-officer-directory** | `src/blocks/loan-officer-directory/` | `assets/blocks/loan-officer-directory/` | LO directory |
| **mortgage-calculator** | `src/blocks/mortgage-calculator/` | `assets/blocks/mortgage-calculator/` | Calculator block |
| **mortgage-form** | `src/blocks/mortgage-form/` | `assets/blocks/mortgage-form/` | Mortgage form |
| **openhouse-carousel** | `src/blocks/openhouse-carousel/` | `assets/blocks/openhouse-carousel/` | Open house gallery |
| **prequal-heading** | `src/blocks/prequal-heading/` | `assets/blocks/prequal-heading/` | Prequal header |
| **prequal-subheading** | `src/blocks/prequal-subheading/` | `assets/blocks/prequal-subheading/` | Prequal subhead |
| **realtor-partner** | `src/blocks/realtor-partner/` | `assets/blocks/realtor-partner/` | Realtor card |

### Block Registration
Blocks are registered via:
- `includes/Controllers/Biolinks/Blocks.php`
- `includes/Controllers/Prequal/Blocks.php`
- `includes/Controllers/OpenHouse/Blocks.php`
- `includes/Controllers/PartnerPortals/Blocks.php`
- `includes/Core/Blocks.php`

---

## 📦 Production Build Process

### Complete Build Command
```bash
npm run build
```

**Expands to:**
```bash
vite build -c vite.frontend.config.js && \
vite build -c vite.admin.config.js && \
vite build -c vite.welcome-portal.config.js && \
vite build -c vite.partnerships-section.config.js && \
vite build -c vite.realtor-portal.config.js && \
vite build -c vite.widget.config.js && \
npm run block:build
```

### Build Outputs Created:
1. `assets/frontend/dist/manifest.json` + hashed assets
2. `assets/admin/dist/manifest.json` + hashed assets
3. `assets/welcome-portal/dist/manifest.json` + hashed assets
4. `assets/partnerships-section/dist/manifest.json` + hashed assets
5. `assets/realtor-portal/dist/manifest.json` + hashed assets
6. `assets/widget/dist/manifest.json` + hashed assets
7. `assets/blocks/*/index.js` + `index.asset.php` (19 blocks)

### Manifest Files
Each Vite build creates a `manifest.json` that maps source files to hashed output files:
```json
{
  "src/frontend/main.jsx": {
    "file": "assets/main-[hash].js",
    "css": ["assets/main-[hash].css"],
    "isEntry": true
  }
}
```

The `@kucrut/vite-for-wp` library automatically:
- Reads manifest in production
- Enqueues correct hashed files
- Handles CSS dependencies
- Falls back to dev server in development

---

## 🔌 WordPress Integration Points

### Asset Enqueuing System

**Primary Handler:** `includes/Assets/Frontend.php`

#### Methods:
```php
enqueue_scripts()                      // Main portal assets
enqueue_welcome_portal_assets()        // Welcome portal
enqueue_partnerships_section_assets()  // Partnerships
enqueue_realtor_portal_assets()        // Realtor portal
enqueue_widget_assets()                // Widget
```

#### Configuration Object
All frontends receive `window.frsPortalConfig`:
```javascript
{
  userId: number,
  userName: string,
  userEmail: string,
  userAvatar: string,
  userRole: 'loan_officer'|'realtor'|'manager'|'admin',
  userJobTitle: string,
  profileSlug: string,
  restNonce: string,
  apiUrl: string,  // REST API base
  gradientUrl: string,
  siteUrl: string,
  portalUrl: string,
  menuItems: array,
  currentUser: object,
  // ... more config
}
```

This is injected via `wp_localize_script()` in `Frontend.php:100`.

---

## 📊 Asset Loading Strategy

### Development Mode
- Dev server runs on multiple ports (5173-5182)
- Vite dev server serves files with HMR
- `@kucrut/vite-for-wp` reads `vite-dev-server.json`
- Assets loaded from `localhost:PORT`

### Production Mode
- All assets built to `assets/*/dist/`
- Manifest files used to resolve hashed filenames
- Assets served from plugin directory
- Cache-friendly with content hashes

### Conditional Loading
Portal assets only load when:
1. Portal shortcodes detected in post content
2. Page slug matches portal pages (`dashboard`, `portal`, etc.)
3. Filter `lrh_should_load_portal` returns true

This prevents unnecessary asset loading on non-portal pages.

---

## 🚀 Deployment Checklist

### Pre-Deployment
- [ ] Run `npm run build` to build all assets
- [ ] Verify all manifest files exist:
  - [ ] `assets/frontend/dist/manifest.json`
  - [ ] `assets/admin/dist/manifest.json`
  - [ ] `assets/welcome-portal/dist/manifest.json`
  - [ ] `assets/partnerships-section/dist/manifest.json`
  - [ ] `assets/realtor-portal/dist/manifest.json`
  - [ ] `assets/widget/dist/manifest.json`
- [ ] Verify all block assets built:
  - [ ] `assets/blocks/*/index.js` (19 blocks)
  - [ ] `assets/blocks/*/index.asset.php` (19 blocks)
- [ ] Check for build errors in console output
- [ ] Test on staging site first

### Deployment Files
**Include in deployment:**
```
assets/
  admin/dist/
  frontend/dist/
  welcome-portal/dist/
  partnerships-section/dist/
  realtor-portal/dist/
  widget/dist/
  blocks/
  images/
  css/
  js/
includes/
vendor/
*.php files
composer.json
```

**Exclude from deployment:**
```
src/
node_modules/
.git/
.github/
.storybook/
*.config.js
package.json
package-lock.json
tsconfig.json
```

### Post-Deployment
- [ ] Clear WordPress object cache
- [ ] Clear CDN cache (if applicable)
- [ ] Test portal loading on frontend
- [ ] Test blocks in Gutenberg editor
- [ ] Verify REST API endpoints
- [ ] Check browser console for errors

---

## 🔍 Verification Commands

### Check Build Outputs
```bash
# Verify all dist directories exist
ls -la assets/*/dist/

# Check manifest files
find assets -name "manifest.json" -type f

# Count built blocks
ls -1 assets/blocks/ | wc -l

# Verify block assets
find assets/blocks -name "index.js" | wc -l
find assets/blocks -name "index.asset.php" | wc -l
```

### Check Source Files
```bash
# Count source entry points
find src -name "*-main.jsx" -o -name "*-main.tsx"

# List all Vite configs
ls -1 vite.*.config.js

# Count block sources
ls -1 src/blocks/ | wc -l
```

### WordPress Verification
```bash
# Check if assets enqueue correctly
wp eval 'do_action("wp_enqueue_scripts"); global $wp_scripts; print_r(array_keys($wp_scripts->registered));'

# List registered blocks
wp block-type list

# Check for plugin errors
wp plugin status frs-lrg
```

---

## 🐛 Common Issues

### Issue: Assets not loading in production
**Cause:** Manifest files missing or incorrect
**Fix:** Run `npm run build` and verify manifest.json files exist

### Issue: Blocks not appearing in editor
**Cause:** Block assets not built or registered
**Fix:** Run `npm run block:build` and check `includes/*/Blocks.php`

### Issue: React app shows blank screen
**Cause:** Missing dependencies or PHP fatal error
**Fix:** Check browser console and WordPress debug.log

### Issue: Dev server CORS errors
**Cause:** Vite server configuration incorrect
**Fix:** Verify `server.cors: true` and `server.origin` in vite configs

---

## 📝 Notes

1. **No TypeScript Compilation:** TypeScript files (.tsx) are handled by Vite during build. No separate tsc step needed.

2. **Shared Components:** Common components in `src/components/` are used across multiple entry points. Vite tree-shakes unused code.

3. **CSS Strategy:** Tailwind CSS is processed by Vite. Each entry point includes only the CSS it needs (with PostCSS/Autoprefixer).

4. **React Dependencies:** React and ReactDOM are provided by WordPress core (`wp_enqueue_script('react')`) to avoid duplication.

5. **Router:** Using React Router v6 for client-side routing. WordPress Interactivity API Router is enqueued for compatibility.

6. **Legacy Compatibility:** Config object uses `frsPortalConfig` name for backward compatibility with old `frs-partnership-portal` plugin.

---

## 🔗 Related Documentation

- [01-development-workflow.md](.claude/docs/01-development-workflow.md) - Dev server usage
- [02-architecture.md](.claude/docs/02-architecture.md) - Overall architecture
- [05-frontend-patterns.md](.claude/docs/05-frontend-patterns.md) - React patterns
- [09-boilerplate-features.md](.claude/docs/09-boilerplate-features.md) - Build tools

---

**END OF INVENTORY**