# Instructions for Terminal Claude - Lending Resource Hub Migration

## Current Status
✅ Step 1 COMPLETE: Composer dependencies installed (prappo/wp-eloquent v3.0.5)
⏳ Steps 2-7 PENDING: React frontend setup

---

## STEP 2: Copy React Source Files (Portal v2.6.0 Only)

### Navigate to plugin directory
```bash
cd /app/public/wp-content/plugins/frs-lrg
```

### Create assets/src directory structure
```bash
mkdir -p assets/src
mkdir -p assets/src/components
mkdir -p assets/src/utils
mkdir -p assets/src/types
```

### Copy main React files
```bash
cp ../frs-partnership-portal/assets/src/main.tsx assets/src/
cp ../frs-partnership-portal/assets/src/index.css assets/src/
cp ../frs-partnership-portal/assets/src/LoanOfficerPortal.tsx assets/src/
```

### Copy Portal component (if it exists separately)
```bash
cp ../frs-partnership-portal/assets/src/Portal.tsx assets/src/ 2>/dev/null || echo "Portal.tsx not found as separate file (may be in LoanOfficerPortal.tsx)"
```

### Copy all components directory
```bash
cp -r ../frs-partnership-portal/assets/src/components/* assets/src/components/
```

### Remove Portal V3 (we only want v2.6.0)
```bash
rm -rf assets/src/components/portal-v3
```

### Copy utils directory
```bash
cp -r ../frs-partnership-portal/assets/src/utils/* assets/src/utils/
```

### Copy types directory (if exists)
```bash
cp -r ../frs-partnership-portal/assets/src/types/* assets/src/types/ 2>/dev/null || echo "No types directory found"
```

### Verify files copied
```bash
echo "=== Verifying copied files ==="
ls -la assets/src/ | head -20
echo ""
echo "=== Components ==="
ls -la assets/src/components/ | head -20
echo ""
echo "=== UI Components ==="
ls -la assets/src/components/ui/ | head -20
echo ""
echo "=== Portal Components ==="
ls -la assets/src/components/loan-officer-portal/ | head -20
echo ""
echo "=== Utils ==="
ls -la assets/src/utils/
```

---

## STEP 3: Update React Configuration in Copied Files

### Update main.tsx - Change root element ID
```bash
cd assets/src
```

Find and replace in main.tsx:
- OLD: `frs-partnership-portal-root`
- NEW: `lrh-portal-root`
- OLD: `frsPortalConfig`
- NEW: `lrhPortalConfig`

```bash
sed -i '' 's/frs-partnership-portal-root/lrh-portal-root/g' main.tsx
sed -i '' 's/frsPortalConfig/lrhPortalConfig/g' main.tsx
echo "✅ Updated main.tsx"
```

### Update dataService.ts - Change API base URL
```bash
cd utils
```

Find the baseUrl line and update it:
```bash
# Backup original
cp dataService.ts dataService.ts.backup

# Update base URL (this is a complex replacement, may need manual edit)
# The goal is to change '/wp-json/frs/v1' to use window.lrhPortalConfig.apiUrl
echo "⚠️  dataService.ts needs manual review - see instructions below"
```

**Manual edit needed for dataService.ts:**
1. Find: `private static baseUrl = '/wp-json/frs/v1'`
2. Replace with:
```typescript
private static getBaseUrl() {
    return (window as any).lrhPortalConfig?.apiUrl || '/wp-json/lrh/v1/';
}
```
3. Update all `fetch(this.baseUrl + ...)` to `fetch(this.getBaseUrl() + ...)`

---

## STEP 4: Create Build Configuration Files

### Navigate to assets directory
```bash
cd /app/public/wp-content/plugins/frs-lrg/assets
```

### Create vite.config.js
```bash
cat > vite.config.js << 'EOF'
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
EOF
echo "✅ Created vite.config.js"
```

### Create package.json
```bash
cat > package.json << 'EOF'
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
EOF
echo "✅ Created package.json"
```

### Create tailwind.config.js
```bash
cat > tailwind.config.js << 'EOF'
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
EOF
echo "✅ Created tailwind.config.js"
```

### Create tsconfig.json
```bash
cat > tsconfig.json << 'EOF'
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
EOF
echo "✅ Created tsconfig.json"
```

### Create tsconfig.node.json
```bash
cat > tsconfig.node.json << 'EOF'
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
EOF
echo "✅ Created tsconfig.node.json"
```

### Create postcss.config.js
```bash
cat > postcss.config.js << 'EOF'
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
EOF
echo "✅ Created postcss.config.js"
```

### Verify all config files created
```bash
echo "=== Configuration files ==="
ls -la *.json *.js
```

---

## STEP 5: Install NPM Dependencies

### Run npm install
```bash
cd /app/public/wp-content/plugins/frs-lrg/assets
npm install
```

**Expected output:**
```
added 1234 packages in 2m
```

### Verify node_modules created
```bash
ls -la node_modules/ | head -20
ls -la node_modules/react
ls -la node_modules/vite
ls -la node_modules/typescript
```

---

## STEP 6: Build React Assets

### Run production build
```bash
cd /app/public/wp-content/plugins/frs-lrg/assets
npm run build
```

**Expected output:**
```
vite v5.0.8 building for production...
✓ 234 modules transformed.
assets/js/portal/portal-dashboards.js   145.23 kB
assets/css/portal/portal-dashboards.css  12.45 kB
✓ built in 5.23s
```

### Verify build artifacts
```bash
echo "=== Build output ==="
ls -la js/portal/
ls -la css/portal/
```

**Must see:**
- `js/portal/portal-dashboards.js`
- `css/portal/portal-dashboards.css`

---

## STEP 7: Activate Plugin & Test

### Deactivate old plugin
```bash
cd /app/public/wp-content/plugins
wp plugin deactivate frs-partnership-portal
```

### Activate new plugin
```bash
wp plugin activate frs-lrg
```

**What happens:**
- Install.php runs migrations
- Creates 3 tables: wp_partnerships, wp_lead_submissions, wp_page_assignments
- Registers FluentBooking hooks
- Registers API routes at /wp-json/lrh/v1/
- Registers shortcodes: [lrh_portal], [lrh_biolink_dashboard]

### Verify tables created
```bash
wp db query "SHOW TABLES LIKE 'wp_partnerships'"
wp db query "SHOW TABLES LIKE 'wp_lead_submissions'"
wp db query "SHOW TABLES LIKE 'wp_page_assignments'"
```

**Expected:** Each query returns 1 table

### Verify API routes registered
```bash
wp rest route list | grep lrh
```

**Expected:** Should see routes like:
```
/lrh/v1/users/me
/lrh/v1/partnerships
/lrh/v1/leads
/lrh/v1/dashboard/stats/lo/<id>
```

### Verify shortcodes registered
```bash
wp shortcode list | grep lrh
```

**Expected:**
```
lrh_portal
lrh_biolink_dashboard
```

### Check for PHP errors
```bash
wp plugin list | grep lending-resource-hub
```

**Expected:** Status should be "active" with no errors

---

## STEP 8: Create Portal Page & Test

### Create portal page with shortcode
```bash
wp post create --post_type=page --post_title="Portal" --post_content="[lrh_portal]" --post_status=publish
```

### Get the page URL
```bash
wp post list --post_type=page --post_title="Portal" --format=table
```

### Test API endpoint manually
```bash
curl -s http://localhost/wp-json/lrh/v1/users/me
```

**Expected:** Should return JSON (may be error if not authenticated, but route should exist)

---

## CHECKLIST - Mark as Complete

- [ ] Step 2: React files copied
- [ ] Step 3: main.tsx updated
- [ ] Step 3: dataService.ts updated (may need manual edit)
- [ ] Step 4: All config files created (6 files)
- [ ] Step 5: npm install completed
- [ ] Step 6: npm run build completed
- [ ] Step 6: Build artifacts verified (JS + CSS exist)
- [ ] Step 7: Old plugin deactivated
- [ ] Step 7: New plugin activated
- [ ] Step 7: Tables verified
- [ ] Step 7: API routes verified
- [ ] Step 7: Shortcodes verified
- [ ] Step 8: Portal page created

---

## KNOWN ISSUES & MANUAL EDITS

### 1. dataService.ts API URL Update
**File:** `assets/src/utils/dataService.ts`

This file needs manual editing to update the API base URL. Look for:
```typescript
private static baseUrl = '/wp-json/frs/v1';
```

Replace the entire baseUrl approach with:
```typescript
private static getBaseUrl(): string {
    return (window as any).lrhPortalConfig?.apiUrl || '/wp-json/lrh/v1/';
}
```

Then find all instances of `this.baseUrl` and change to `this.getBaseUrl()`.

### 2. TypeScript Window Declaration
**File:** `assets/src/utils/dataService.ts` (add to top)

Add this TypeScript declaration:
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

## SUCCESS CRITERIA

### Backend Success (Already Complete):
✅ Composer dependencies installed
✅ 3 Eloquent models created
✅ 3 migrations created
✅ 4 controllers created (22 API methods)
✅ FluentBooking integration
✅ Shortcode handler
✅ Plugin renamed and configured

### Frontend Success (To Be Verified):
- [ ] React files copied (200+ files)
- [ ] Config files created (6 files)
- [ ] NPM dependencies installed (1200+ packages)
- [ ] Assets built successfully (JS + CSS)
- [ ] No build errors

### WordPress Success (To Be Verified):
- [ ] Plugin activates without errors
- [ ] 3 tables exist in database
- [ ] API routes return 200 (or 401 if auth required)
- [ ] Shortcodes registered
- [ ] Portal page loads (no blank screen)
- [ ] React app mounts (check browser console)

---

## TROUBLESHOOTING

### If npm install fails:
```bash
# Clear cache
npm cache clean --force

# Try again
npm install
```

### If build fails with TypeScript errors:
```bash
# Check which files have errors
npm run build 2>&1 | grep "error TS"

# May need to fix import paths or add type declarations
```

### If plugin activation fails:
```bash
# Check WordPress debug log
tail -f /app/public/wp-content/debug.log

# Check PHP syntax
php -l ../includes/Models/Partnership.php
php -l ../database/Migrations/Partnerships.php
```

### If API returns 404:
```bash
# Flush rewrite rules
wp rewrite flush

# Check .htaccess
cat /app/public/.htaccess | grep -A 5 "BEGIN WordPress"
```

---

## FINAL VERIFICATION COMMANDS

Run these after all steps complete:

```bash
# Check plugin status
wp plugin list | grep lending-resource-hub

# Check tables
wp db query "SELECT COUNT(*) FROM wp_partnerships"
wp db query "SELECT COUNT(*) FROM wp_lead_submissions"
wp db query "SELECT COUNT(*) FROM wp_page_assignments"

# Check API routes
wp rest route list | grep lrh | wc -l
# Should be 22+ routes

# Check shortcodes
wp shortcode list | grep lrh | wc -l
# Should be 2 shortcodes

# Check build output
ls -lh assets/js/portal/portal-dashboards.js
ls -lh assets/css/portal/portal-dashboards.css

# Both files should exist and have reasonable size (>100KB for JS, >10KB for CSS)
```

---

**START HERE:** Begin with Step 2 (Copy React files)

**Report back:** After each major step (2, 4, 5, 6, 7) with status
