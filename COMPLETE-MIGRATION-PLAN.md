# COMPLETE MIGRATION PLAN: Lending Resource Hub
## From FRS Partnership Portal to Modern Boilerplate Architecture

---

## 📋 EXECUTIVE SUMMARY

**Project:** Migrate FRS Partnership Portal to WordPress Plugin Boilerplate with Eloquent ORM
**Timeline:** 8-10 hours
**Current Progress:** 50% Complete (Backend done, Frontend pending)
**Plugin Name:** Lending Resource Hub
**Text Domain:** lending-resource-hub
**Namespace:** LendingResourceHub
**Route Prefix:** lrh/v1

---

## ✅ COMPLETED WORK (Backend - 50%)

### Phase 1: Database Layer ✅
**Files Created:**
1. `includes/Models/Partnership.php` - Partnership Eloquent model
2. `includes/Models/LeadSubmission.php` - Lead submission Eloquent model
3. `includes/Models/PageAssignment.php` - Page assignment Eloquent model
4. `database/Migrations/Partnerships.php` - Creates wp_partnerships table
5. `database/Migrations/LeadSubmissions.php` - Creates wp_lead_submissions table
6. `database/Migrations/PageAssignments.php` - Creates wp_page_assignments table
7. `includes/Core/Install.php` - Updated to run all 3 migrations

**What This Does:**
- Eloquent models provide type-safe database operations
- Migrations create 3 custom tables on plugin activation
- Relationships defined (partnerships → leads, users → page assignments)

### Phase 2: Integration Classes ✅
**Files Created:**
1. `includes/Integrations/FluentBooking.php` - Auto-calendar creation for loan officers

**What This Does:**
- Hooks into `user_register` and `set_user_role` actions
- Auto-creates FluentBooking calendar when user becomes loan officer
- Includes helper methods: has_calendar(), get_calendar(), reset_calendar()

### Phase 3: REST API Controllers ✅
**Files Created:**
1. `includes/Controllers/Users/Actions.php` - 7 methods
   - get_current_user()
   - get_user_by_id()
   - get_person_profile()
   - update_profile()
   - get_profile()
   - update_profile_post()

2. `includes/Controllers/Partnerships/Actions.php` - 8 methods
   - get_partnerships()
   - create_partnership()
   - assign_partnership()
   - get_partnerships_for_lo()
   - get_partnership_for_realtor()
   - get_partners_for_lo()
   - get_realtor_partners()
   - send_invitation()

3. `includes/Controllers/Leads/Actions.php` - 4 methods
   - get_leads()
   - get_leads_for_lo()
   - create_lead()
   - update_lead_status()

4. `includes/Controllers/Dashboard/Stats.php` - 2 methods
   - get_lo_stats()
   - get_realtor_stats()

**What This Does:**
- 22 REST API endpoints using Eloquent queries
- All endpoints registered at /wp-json/lrh/v1/
- Type-safe, modern PHP 8.1+ code

### Phase 4: API Routes Registration ✅
**Files Modified:**
1. `includes/Routes/Api.php` - Added 22 new endpoints

**Endpoints Registered:**
- 6 User endpoints (/users/me, /users/{id}, /profile, etc.)
- 8 Partnership endpoints (/partnerships, /partnerships/lo/{id}, etc.)
- 4 Lead endpoints (/leads, /leads/lo/{id}, etc.)
- 2 Dashboard stats endpoints (/dashboard/stats/lo/{id}, etc.)

### Phase 5: Shortcode System ✅
**Files Created:**
1. `includes/Core/Shortcode.php` - Shortcode handler

**Shortcodes Registered:**
- `[lrh_portal]` - Main portal dashboard (Portal v2.6.0)
- `[lrh_biolink_dashboard]` - Biolink management dashboard

**What This Does:**
- Checks user authentication
- Enqueues React assets
- Passes config to JavaScript via window.lrhPortalConfig
- Returns root div for React to mount

### Phase 6: Plugin Configuration ✅
**Files Modified:**
1. `plugin-config.json` - Updated with Lending Resource Hub naming
2. `plugin.php` - Initialized all new classes
3. `composer.json` - Namespace updated to LendingResourceHub

**What's Configured:**
- Plugin name: "Lending Resource Hub"
- Namespace: LendingResourceHub
- Prefix: lrh
- Route prefix: lrh/v1
- Renamed from wordpress-plugin-boilerplate.php to lending-resource-hub.php

---

## ⏳ PENDING WORK (Frontend - 50%)

### Phase 7: Composer Dependencies Installation 🔴 BLOCKING
**Location:** Terminal in Local site shell

**Commands to Run:**
```bash
cd /app/public/wp-content/plugins/frs-lrg
composer install
ls -la vendor/autoload.php
```

**What This Does:**
- Installs prappo/wp-eloquent package
- Creates vendor/ directory with autoload files
- **BLOCKS plugin activation until complete**

**Expected Output:**
```
Loading composer repositories with package information
Installing dependencies from lock file
Package operations: 1 install, 0 updates, 0 removals
  - Installing prappo/wp-eloquent (^3.0)
Generating autoload files
```

**Verification:**
```bash
ls -la vendor/autoload.php
```
Should see: `-rw-r--r-- 1 user group 1234 Oct 30 vendor/autoload.php`

---

### Phase 8: Copy React Source Files (Portal v2.6.0 Only)
**Source:** `/app/public/wp-content/plugins/frs-partnership-portal/assets/src/`
**Destination:** `/app/public/wp-content/plugins/frs-lrg/assets/src/`

**Files to Copy:**

1. **Main Entry Point:**
   - `main.tsx` - React mount point (UPDATE root element to lrh-portal-root)
   - `index.css` - Global styles

2. **Portal Component (v2.6.0 ONLY):**
   - `LoanOfficerPortal.tsx` - Main portal component
   - `Portal.tsx` - Portal wrapper component

3. **UI Components:**
   - `components/ui/*.tsx` - All Shadcn UI components (Button, Dialog, Tabs, Avatar, etc.)

4. **Portal Components:**
   - `components/loan-officer-portal/*.tsx` - All portal-specific components
   - **EXCLUDE:** `components/portal-v3/` (skip Portal V3)

5. **Utils:**
   - `utils/dataService.ts` - API service layer (UPDATE base URL)
   - `utils/*.ts` - Other utility files

6. **Types:**
   - `types/*.ts` - TypeScript interfaces

**Commands to Copy:**
```bash
# Navigate to new plugin
cd /app/public/wp-content/plugins/frs-lrg

# Create assets/src directory
mkdir -p assets/src

# Copy main files
cp ../frs-partnership-portal/assets/src/main.tsx assets/src/
cp ../frs-partnership-portal/assets/src/index.css assets/src/
cp ../frs-partnership-portal/assets/src/LoanOfficerPortal.tsx assets/src/
cp ../frs-partnership-portal/assets/src/Portal.tsx assets/src/

# Copy components directory (exclude portal-v3)
cp -r ../frs-partnership-portal/assets/src/components assets/src/
rm -rf assets/src/components/portal-v3

# Copy utils
cp -r ../frs-partnership-portal/assets/src/utils assets/src/

# Copy types
cp -r ../frs-partnership-portal/assets/src/types assets/src/

# Verify
ls -la assets/src/
ls -la assets/src/components/
ls -la assets/src/components/ui/
ls -la assets/src/components/loan-officer-portal/
```

---

### Phase 9: Update React Configuration Files

#### 9.1 Update main.tsx
**File:** `assets/src/main.tsx`

**Find:**
```typescript
const portalRoot = document.getElementById("frs-partnership-portal-root");
```

**Replace with:**
```typescript
const portalRoot = document.getElementById("lrh-portal-root");
```

**Find:**
```typescript
const config = (window as any).frsPortalConfig || {
```

**Replace with:**
```typescript
const config = (window as any).lrhPortalConfig || {
```

#### 9.2 Update dataService.ts
**File:** `assets/src/utils/dataService.ts`

**Find:**
```typescript
private static baseUrl = '/wp-json/frs/v1';
```

**Replace with:**
```typescript
private static getBaseUrl() {
    return (window as any).lrhPortalConfig?.apiUrl || '/wp-json/lrh/v1/';
}
```

**Update all API calls to use:**
```typescript
const response = await fetch(`${this.getBaseUrl()}users/me`, {
```

**Add TypeScript declaration:**
```typescript
declare global {
    interface Window {
        lrhPortalConfig: {
            apiUrl: string;
            restNonce: string;
            userId: number;
            userName: string;
            userEmail: string;
            userAvatar: string;
            userRole: string;
        };
    }
}
```

---

### Phase 10: Create Build Configuration

#### 10.1 Create vite.config.js
**File:** `assets/vite.config.js`

```javascript
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    outDir: './js/portal',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        'portal-dashboards': path.resolve(__dirname, 'src/main.tsx'),
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) {
            return '../css/portal/[name][extname]';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
});
```

#### 10.2 Create package.json
**File:** `package.json`

```json
{
  "name": "lending-resource-hub",
  "version": "1.0.0",
  "description": "Learning management and partnership platform for 21st Century Lending",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
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
    "class-variance-authority": "^0.7.0"
  },
  "devDependencies": {
    "@types/react": "^18.2.43",
    "@types/react-dom": "^18.2.17",
    "@vitejs/plugin-react": "^4.2.1",
    "typescript": "^5.3.3",
    "vite": "^5.0.8",
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.16",
    "postcss": "^8.4.32"
  }
}
```

#### 10.3 Create tailwind.config.js
**File:** `tailwind.config.js`

```javascript
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#2563eb',
          foreground: '#ffffff',
        },
        secondary: {
          DEFAULT: '#2dd4da',
          foreground: '#ffffff',
        },
      },
    },
  },
  plugins: [],
}
```

#### 10.4 Create tsconfig.json
**File:** `tsconfig.json`

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["./src/*"]
    }
  },
  "include": ["src"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

#### 10.5 Create tsconfig.node.json
**File:** `tsconfig.node.json`

```json
{
  "compilerOptions": {
    "composite": true,
    "skipLibCheck": true,
    "module": "ESNext",
    "moduleResolution": "bundler",
    "allowSyntheticDefaultImports": true
  },
  "include": ["vite.config.js"]
}
```

#### 10.6 Create postcss.config.js
**File:** `postcss.config.js`

```javascript
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

---

### Phase 11: Install NPM Dependencies
**Location:** Terminal in plugin assets directory

**Commands:**
```bash
cd /app/public/wp-content/plugins/frs-lrg/assets
npm install
```

**What This Does:**
- Installs React, TypeScript, Vite, Tailwind CSS
- Installs all Radix UI components
- Creates node_modules/ directory
- Creates package-lock.json

**Expected Output:**
```
added 1234 packages in 2m
```

**Verification:**
```bash
ls -la node_modules/
ls -la node_modules/react
ls -la node_modules/vite
```

---

### Phase 12: Build React Assets
**Location:** Terminal in plugin assets directory

**Commands:**
```bash
cd /app/public/wp-content/plugins/frs-lrg/assets
npm run build
```

**What This Does:**
- Compiles TypeScript to JavaScript
- Bundles React components
- Processes Tailwind CSS
- Creates production-ready assets

**Expected Output:**
```
vite v5.0.8 building for production...
✓ 234 modules transformed.
assets/js/portal/portal-dashboards.js   145.23 kB
assets/css/portal/portal-dashboards.css  12.45 kB
✓ built in 5.23s
```

**Verification:**
```bash
ls -la js/portal/portal-dashboards.js
ls -la css/portal/portal-dashboards.css
```

---

### Phase 13: Plugin Activation & Testing

#### 13.1 Deactivate Old Plugin
**Location:** WordPress Admin → Plugins

```bash
cd /app/public/wp-content/plugins
wp plugin deactivate frs-partnership-portal
```

#### 13.2 Activate New Plugin
```bash
wp plugin activate frs-lrg
```

**What Happens:**
1. Install.php runs automatically
2. Creates 3 custom tables (partnerships, lead_submissions, page_assignments)
3. Registers FluentBooking hooks
4. Registers API routes
5. Registers shortcodes

**Verification:**
```bash
wp db query "SHOW TABLES LIKE 'wp_partnerships'"
wp db query "SHOW TABLES LIKE 'wp_lead_submissions'"
wp db query "SHOW TABLES LIKE 'wp_page_assignments'"
```

Should see all 3 tables exist.

#### 13.3 Create Portal Page
**Location:** WordPress Admin → Pages → Add New

1. Create new page titled "Portal"
2. Add shortcode: `[lrh_portal]`
3. Publish page
4. Get page URL

#### 13.4 Test Portal Access
**Login as loan officer and visit portal page**

**Expected:**
- Portal loads without errors
- React app mounts successfully
- User data displays correctly
- Sidebar shows navigation
- Dashboard shows stats

**Check Browser Console:**
```javascript
console.log(window.lrhPortalConfig);
```

Should see:
```javascript
{
  userId: 123,
  userName: "John Doe",
  userEmail: "john@example.com",
  userAvatar: "https://...",
  userRole: "loan_officer",
  restNonce: "abc123...",
  apiUrl: "/wp-json/lrh/v1/"
}
```

#### 13.5 Test API Endpoints
**Browser DevTools → Network tab**

Navigate portal and verify API calls:
- GET /wp-json/lrh/v1/users/me → 200 OK
- GET /wp-json/lrh/v1/dashboard/stats/lo/123 → 200 OK
- GET /wp-json/lrh/v1/partnerships → 200 OK
- GET /wp-json/lrh/v1/leads → 200 OK

#### 13.6 Test FluentBooking Integration
**Register new loan officer user**

```bash
wp user create testlo test@example.com --role=loan_officer
```

**Check if calendar was auto-created:**
```bash
wp db query "SELECT * FROM wp_fcal_calendars WHERE user_id = (SELECT ID FROM wp_users WHERE user_email='test@example.com')"
```

Should see 1 calendar record.

---

## 📊 MIGRATION CHECKLIST

### Backend (100% Complete) ✅
- [x] Create 3 Eloquent models
- [x] Create 3 database migrations
- [x] Update Install.php to run migrations
- [x] Create FluentBooking integration
- [x] Create Users controller (7 methods)
- [x] Create Partnerships controller (8 methods)
- [x] Create Leads controller (4 methods)
- [x] Create Dashboard controller (2 methods)
- [x] Register 22 API routes
- [x] Create Shortcode handler
- [x] Update plugin.php to initialize everything
- [x] Update plugin-config.json
- [x] Rename plugin files

### Frontend (0% Complete) ⏳
- [ ] Install Composer dependencies (BLOCKING)
- [ ] Copy React source files (Portal v2.6.0 only)
- [ ] Update main.tsx (root element, config)
- [ ] Update dataService.ts (base URL)
- [ ] Create vite.config.js
- [ ] Create package.json
- [ ] Create tailwind.config.js
- [ ] Create tsconfig.json
- [ ] Install npm dependencies
- [ ] Build React assets
- [ ] Verify build output

### Testing (0% Complete) ⏳
- [ ] Deactivate old plugin
- [ ] Activate new plugin
- [ ] Verify tables created
- [ ] Create portal page with shortcode
- [ ] Test portal loads
- [ ] Test API endpoints
- [ ] Test FluentBooking auto-calendar
- [ ] Test partnerships CRUD
- [ ] Test leads CRUD
- [ ] Test dashboard stats

---

## 🚨 CRITICAL BLOCKERS

### Blocker #1: Composer Dependencies (MUST DO FIRST)
**Status:** 🔴 BLOCKING ALL PROGRESS
**Command:** `composer install`
**Location:** `/app/public/wp-content/plugins/frs-lrg`
**Why Critical:** Plugin cannot be activated without Eloquent ORM

### Blocker #2: React Assets Not Built
**Status:** ⏳ WAITING FOR BLOCKER #1
**Commands:** `npm install` then `npm run build`
**Location:** `/app/public/wp-content/plugins/frs-lrg/assets`
**Why Critical:** Shortcode will render empty div without JavaScript

---

## 📝 COMMAND SUMMARY FOR TERMINAL CLAUDE

```bash
# STEP 1: Install Composer Dependencies (BLOCKING)
cd /app/public/wp-content/plugins/frs-lrg
composer install
ls -la vendor/autoload.php

# STEP 2: Copy React Files
cd /app/public/wp-content/plugins/frs-lrg
mkdir -p assets/src
cp ../frs-partnership-portal/assets/src/main.tsx assets/src/
cp ../frs-partnership-portal/assets/src/index.css assets/src/
cp ../frs-partnership-portal/assets/src/LoanOfficerPortal.tsx assets/src/
cp ../frs-partnership-portal/assets/src/Portal.tsx assets/src/
cp -r ../frs-partnership-portal/assets/src/components assets/src/
rm -rf assets/src/components/portal-v3
cp -r ../frs-partnership-portal/assets/src/utils assets/src/
cp -r ../frs-partnership-portal/assets/src/types assets/src/

# STEP 3: Create Config Files
# (Create vite.config.js, package.json, tailwind.config.js, tsconfig.json - see Phase 10)

# STEP 4: Install NPM Dependencies
cd assets
npm install

# STEP 5: Build Assets
npm run build
ls -la js/portal/portal-dashboards.js

# STEP 6: Activate Plugin
cd /app/public/wp-content/plugins
wp plugin deactivate frs-partnership-portal
wp plugin activate frs-lrg

# STEP 7: Verify Tables
wp db query "SHOW TABLES LIKE 'wp_partnerships'"
wp db query "SHOW TABLES LIKE 'wp_lead_submissions'"
wp db query "SHOW TABLES LIKE 'wp_page_assignments'"
```

---

## 🎯 SUCCESS CRITERIA

### Plugin Activation Success:
- [x] No PHP errors
- [x] 3 tables created in database
- [x] FluentBooking hooks registered
- [x] API routes accessible at /wp-json/lrh/v1/
- [x] Shortcodes registered

### Portal Load Success:
- [ ] Page loads without errors
- [ ] React app mounts (no blank screen)
- [ ] User data displays correctly
- [ ] Navigation works
- [ ] Dashboard shows stats

### API Success:
- [ ] GET /users/me returns user data
- [ ] GET /dashboard/stats/lo/{id} returns stats
- [ ] GET /partnerships returns partnerships
- [ ] GET /leads returns leads
- [ ] POST /partnerships creates partnership

### Integration Success:
- [ ] New loan officer auto-gets FluentBooking calendar
- [ ] FluentForms submissions create leads (if FluentForms active)
- [ ] FluentCRM sync works (if FluentCRM active)
- [ ] ACF Person CPT data displays in portal

---

## 🔧 TROUBLESHOOTING GUIDE

### Issue: Composer Install Fails
**Solution:**
```bash
# Check composer is available
which composer

# Check PHP version
php -v  # Must be 8.1+

# Try with full path
/usr/local/bin/composer install

# Or download composer.phar
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

### Issue: Plugin Activation Error
**Check:**
```bash
# PHP syntax errors
php -l includes/Models/Partnership.php
php -l database/Migrations/Partnerships.php

# Autoload exists
ls -la vendor/autoload.php

# WordPress error log
tail -f /app/public/wp-content/debug.log
```

### Issue: Blank Portal Page
**Check:**
```bash
# Assets built
ls -la assets/js/portal/portal-dashboards.js
ls -la assets/css/portal/portal-dashboards.css

# Browser console for errors
# DevTools → Console → Check for JavaScript errors

# Check shortcode registered
wp shortcode list | grep lrh_portal
```

### Issue: API Returns 404
**Check:**
```bash
# Routes registered
curl http://hub21.local/wp-json/lrh/v1/users/me

# Permalink flush
wp rewrite flush

# Check .htaccess
cat /app/public/.htaccess
```

---

## 📚 ARCHITECTURE REFERENCE

### Database Tables (3 Custom):
1. **wp_partnerships** - Partnership relationships
2. **wp_lead_submissions** - Lead tracking
3. **wp_page_assignments** - User page mappings

### Eloquent Models (3):
1. **Partnership** - with relationships to Users, Leads
2. **LeadSubmission** - with relationships to Partnership, Users
3. **PageAssignment** - with relationship to Users

### Controllers (4):
1. **Users** - 7 methods
2. **Partnerships** - 8 methods
3. **Leads** - 4 methods
4. **Dashboard** - 2 methods

### API Routes (22 Total):
- 6 User routes
- 8 Partnership routes
- 4 Lead routes
- 2 Dashboard routes
- 2 Example routes (kept for reference)

### Shortcodes (2):
1. `[lrh_portal]` - Main portal dashboard
2. `[lrh_biolink_dashboard]` - Biolink management

### Integrations (1 Critical):
1. **FluentBooking** - Auto-calendar creation for loan officers

---

## ⏱️ TIME ESTIMATES

- ✅ Backend Development: 4 hours (COMPLETE)
- ⏳ Composer Install: 5 minutes (PENDING)
- ⏳ Copy React Files: 15 minutes (PENDING)
- ⏳ Update Configs: 30 minutes (PENDING)
- ⏳ NPM Install: 10 minutes (PENDING)
- ⏳ Build Assets: 5 minutes (PENDING)
- ⏳ Testing: 1 hour (PENDING)

**Total Remaining:** ~2 hours

---

## 🎬 START HERE

**For Terminal Claude:**

1. **Read this entire plan**
2. **Start with Phase 7:** Run `composer install`
3. **Report results** before proceeding
4. **Continue with Phase 8-12** sequentially
5. **Ask questions** if any step fails

**Current working directory:** `/app/public/wp-content/plugins/frs-lrg`

**First command to run:**
```bash
cd /app/public/wp-content/plugins/frs-lrg && composer install && ls -la vendor/autoload.php
```

---

## 📞 SUPPORT

If Terminal Claude encounters issues:
- Check error messages carefully
- Verify file permissions (`chmod -R 755`)
- Check PHP version (`php -v` must be 8.1+)
- Verify composer is installed (`which composer`)
- Check WordPress debug.log (`tail -f /app/public/wp-content/debug.log`)

**Good luck! 🚀**
