# Development Workflow

Complete guide to the development workflow for the Lending Resource Hub plugin.

---

## Table of Contents

- [Development Server Setup](#development-server-setup)
- [Hot Module Replacement (HMR)](#hot-module-replacement-hmr)
- [CORS Configuration](#cors-configuration)
- [Build Process](#build-process)
- [NPM Scripts Reference](#npm-scripts-reference)
- [WP-Now Server](#wp-now-server)
- [Git Workflow](#git-workflow)

---

## Development Server Setup

### CRITICAL RULE: Always Use Dev Server

**NEVER run `npm run build` repeatedly during development.**

```bash
# Start development servers (ALWAYS USE THIS)
npm run dev              # Both frontend (5173) and admin (5174)
npm run dev:frontend     # Frontend only (port 5173)
npm run dev:admin        # Admin only (port 5174)
```

### Why Dev Server is Mandatory

1. **Instant Updates** - Hot Module Replacement (HMR) applies changes without full page reload
2. **No Build Step** - Edit code and see changes immediately in browser
3. **Faster Development** - Rebuild takes 5-30 seconds, HMR takes 50-300ms
4. **Better Debugging** - Source maps work perfectly, see exact line numbers

### When to Build

Only run `npm run build` when:
- Committing changes to git
- Deploying to production
- Testing production bundle size
- Verifying everything works in production mode

---

## Hot Module Replacement (HMR)

### How HMR Works

1. You edit a React component in `src/frontend/` or `src/admin/`
2. Vite dev server detects the file change
3. HMR sends ONLY the changed module to browser via WebSocket
4. React Fast Refresh swaps the component WITHOUT losing state
5. You see changes in ~100ms

### Terminal Output

```bash
# Dev server running
VITE v5.0.8  ready in 523 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
  ➜  press h + enter to show help

# When you save a file
10:23:45 AM [vite] hmr update /src/frontend/components/Dashboard.tsx
10:23:45 AM [vite] page reload src/frontend/main.tsx (x3)
```

### Verifying HMR is Working

**Test it:**
1. Start dev server: `npm run dev:frontend`
2. Open browser to WordPress page with shortcode
3. Open browser DevTools Console
4. Edit a React component, save the file
5. Watch console for: `[vite] hot updated: /src/frontend/components/...`

**If you see full page reload instead of HMR:**
- Check CORS configuration (see below)
- Verify Vite dev server is running on correct port
- Check browser console for WebSocket errors

---

## CORS Configuration

### Why CORS is Required

- **WordPress runs on:** `http://hub21.local` or `http://localhost`
- **Vite dev server runs on:** `http://localhost:5173` (frontend) and `http://localhost:5174` (admin)
- **Browser blocks:** Cross-origin requests by default
- **Solution:** Configure Vite to allow WordPress domain

### Vite Frontend Config

**File:** `vite.frontend.config.js`

```javascript
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],

  server: {
    cors: true,                    // Enable CORS
    origin: 'http://localhost:5173', // Must match port
    host: 'localhost',             // Bind to localhost
    port: 5173,                    // Frontend port
    strictPort: true,              // Fail if port is busy
  },

  // ... rest of config
});
```

### Vite Admin Config

**File:** `vite.admin.config.js`

```javascript
export default defineConfig({
  // ... plugins, etc.

  server: {
    cors: true,
    origin: 'http://localhost:5174',
    host: 'localhost',
    port: 5174,                    // Admin port (different from frontend)
    strictPort: true,
  },
});
```

### Testing CORS

```bash
# Start dev server
npm run dev:frontend

# In browser console (on WordPress page)
fetch('http://localhost:5173/@vite/client')
  .then(r => console.log('CORS working!'))
  .catch(e => console.error('CORS failed:', e));
```

**Success:** Console logs `CORS working!`
**Failure:** Console logs `CORS policy: No 'Access-Control-Allow-Origin' header`

---

## Build Process

### Build Commands

```bash
# Build everything (frontend + admin + blocks)
npm run build

# Build specific targets
npm run build:frontend    # React frontend only
npm run build:admin       # React admin only
npm run block:build       # Gutenberg blocks only
```

### Build Output

**Frontend Build:**
```
assets/frontend/dist/
├── main.js              # React app bundle
├── main.css             # Compiled Tailwind CSS
└── assets/
    └── [hash].js        # Code-split chunks
```

**Admin Build:**
```
assets/admin/dist/
├── main.js              # Admin React app
├── main.css             # Admin styles
└── assets/
    └── [hash].js        # Code-split chunks
```

**Blocks Build:**
```
blocks/
├── biolink-card/
│   ├── index.js         # Block editor script
│   ├── style.css        # Frontend styles
│   └── editor.css       # Editor-only styles
└── [other blocks]...
```

### Build Performance

| Target | Time | Output Size |
|--------|------|-------------|
| Frontend | 5-8s | ~150KB (gzipped) |
| Admin | 4-6s | ~120KB (gzipped) |
| Blocks | 10-15s | ~80KB total |
| **All** | **20-30s** | **~350KB total** |

### Production Optimizations

Vite automatically applies:
- **Minification** - Removes whitespace, shortens variable names
- **Tree Shaking** - Removes unused code
- **Code Splitting** - Splits into smaller chunks for faster loading
- **CSS Optimization** - Removes unused Tailwind classes, minifies CSS

---

## NPM Scripts Reference

### Development Scripts

```bash
# Start dev servers
npm run dev              # Both frontend + admin (parallel)
npm run dev:frontend     # Frontend only (port 5173)
npm run dev:admin        # Admin only (port 5174)

# Start Gutenberg block dev
npm run block:start      # Webpack dev server for blocks
```

### Build Scripts

```bash
# Production builds
npm run build            # All targets (frontend + admin + blocks)
npm run build:frontend   # React frontend only
npm run build:admin      # React admin only
npm run block:build      # Gutenberg blocks only

# Watch mode (rebuild on file change)
npm run watch            # Watch all targets
npm run watch:frontend   # Watch frontend only
npm run watch:admin      # Watch admin only
```

### Utility Scripts

```bash
# Preview production build
npm run preview          # Preview frontend build
npm run preview:admin    # Preview admin build

# Linting and formatting
npm run lint             # ESLint check
npm run lint:fix         # ESLint auto-fix
npm run format           # Prettier format
npm run format:check     # Prettier check only

# Type checking
npm run type-check       # TypeScript type check (no emit)

# Tests
npm run test             # Run Jest tests
npm run test:watch       # Jest watch mode
npm run test:coverage    # Generate coverage report

# Storybook
npm run storybook        # Start Storybook dev server
npm run build-storybook  # Build static Storybook
```

### Documentation Scripts

```bash
# Documentation site (Fumadocs + Next.js)
npm run docs:dev         # Start docs dev server
npm run docs:build       # Build docs for production
npm run docs:preview     # Preview built docs
```

### Package.json Example

```json
{
  "scripts": {
    "dev": "concurrently \"npm run dev:frontend\" \"npm run dev:admin\"",
    "dev:frontend": "vite --config vite.frontend.config.js",
    "dev:admin": "vite --config vite.admin.config.js",
    "build": "npm run build:frontend && npm run build:admin && npm run block:build",
    "build:frontend": "vite build --config vite.frontend.config.js",
    "build:admin": "vite build --config vite.admin.config.js",
    "block:build": "wp-scripts build",
    "block:start": "wp-scripts start",
    "preview": "vite preview --config vite.frontend.config.js",
    "lint": "eslint src/",
    "format": "prettier --write \"src/**/*.{ts,tsx,js,jsx}\"",
    "type-check": "tsc --noEmit"
  }
}
```

---

## WP-Now Server

### What is WP-Now?

**WP-Now** is a modern WordPress development server that runs WordPress in Node.js without MAMP/XAMPP.

**Features:**
- WordPress instance in seconds (no Apache/MySQL setup)
- SQLite database (no MySQL required)
- Automatic PHP installation
- Hot reload for PHP changes

### Installing WP-Now

```bash
# Install globally
npm install -g @wp-now/wp-now

# Or use npx (no install)
npx @wp-now/wp-now start
```

### Using WP-Now with LRG Plugin

```bash
# Navigate to plugin directory
cd /path/to/frs-lrg

# Start WP-Now server
wp-now start

# Output:
# WordPress: 6.4
# PHP: 8.0
# SQLite: 3.x
# URL: http://localhost:8881
```

### WP-Now Configuration

**File:** `.wp-now.json`

```json
{
  "core": "WordPress/WordPress#6.4",
  "phpVersion": "8.1",
  "port": 8881,
  "plugins": [
    "."
  ],
  "config": {
    "WP_DEBUG": true,
    "WP_DEBUG_LOG": true,
    "WP_DEBUG_DISPLAY": false
  }
}
```

### WP-Now vs Local by Flywheel

| Feature | WP-Now | Local by Flywheel |
|---------|--------|-------------------|
| Setup time | 5 seconds | 5 minutes |
| Database | SQLite (file-based) | MySQL (server) |
| Web server | Node.js | Nginx/Apache |
| PHP | Auto-install | Bundled |
| Multiple sites | CLI args | GUI management |
| Production-like | No | Yes |
| **Best for** | Quick testing | Full development |

**Recommendation:** Use **Local by Flywheel** for this plugin (already configured at `hub21.local`).

---

## Git Workflow

### Branch Strategy

```bash
# 1. Create feature branch from main
git checkout main
git pull origin main
git checkout -b feature/descriptive-name-YYYY-MM-DD

# 2. Make changes, commit frequently
git add .
git commit -m "feat: add user dashboard component"

# 3. Push to remote
git push origin feature/descriptive-name-YYYY-MM-DD

# 4. Open pull request (if using PRs)
# Or merge directly to main (if solo dev)

# 5. Merge to main
git checkout main
git merge feature/descriptive-name-YYYY-MM-DD
git push origin main

# 6. Delete feature branch
git branch -d feature/descriptive-name-YYYY-MM-DD
git push origin --delete feature/descriptive-name-YYYY-MM-DD
```

### Commit Message Format

Use **Conventional Commits** format:

```bash
# Format
<type>(<scope>): <description>

# Types
feat: new feature
fix: bug fix
docs: documentation changes
refactor: code restructure (no feature change)
perf: performance improvement
security: security fix/improvement
test: add or update tests
chore: maintenance (dependencies, etc.)
cleanup: remove unused code

# Examples
feat(api): add endpoint for partnership statistics
fix(blocks): biolink block now renders user data correctly
docs(readme): update installation instructions
refactor(database): optimize lead query performance
perf(frontend): lazy load dashboard components
security(auth): add rate limiting to REST API
cleanup: remove duplicate UI components and old docs
```

### Pre-Commit Checklist

Before committing:

```bash
# 1. Run linter
npm run lint

# 2. Run type check
npm run type-check

# 3. Run tests
npm run test

# 4. Build for production
npm run build

# 5. Verify build output exists
ls -la assets/frontend/dist/main.js
ls -la assets/admin/dist/main.js

# 6. Commit
git add .
git commit -m "feat: your commit message"
```

### Automated Checks (GitHub Actions)

**File:** `.github/workflows/ci.yml`

```yaml
name: CI

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '20'
      - run: npm install
      - run: npm run lint
      - run: npm run type-check
      - run: npm run test
      - run: npm run build
```

---

## Development Best Practices

### 1. Always Use Dev Server

- Dev server for ALL development work
- Build ONLY when committing or deploying
- Never debug production build locally

### 2. Check Terminal Output

```bash
# Good - Dev server running
VITE v5.0.8  ready in 523 ms
[vite] hmr update /src/frontend/components/Dashboard.tsx

# Bad - Port already in use
Error: Port 5173 is already in use
Solution: Kill the other process or use different port

# Bad - Module not found
Error: Cannot find module '@/components/ui/button'
Solution: Check import path, verify file exists
```

### 3. Monitor Browser Console

- Check for HMR update messages
- Watch for React errors (red error overlay)
- Look for 404s (assets not loading)
- Verify API calls succeed

### 4. Clear Cache When Needed

```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Clear build output
rm -rf assets/frontend/dist
rm -rf assets/admin/dist

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
```

### 5. Use Correct Node Version

```bash
# Check current version
node -v

# Should be v20.x or higher
# If not, install via nvm:
nvm install 20
nvm use 20
```

---

## Troubleshooting Dev Server

### Port Already in Use

```bash
# Error
Error: Port 5173 is already in use

# Solution 1: Kill the process
lsof -ti:5173 | xargs kill -9

# Solution 2: Use different port
vite --port 5175
```

### HMR Not Working

```bash
# Check 1: Is dev server running?
lsof -i:5173
# Should show node process

# Check 2: Is WebSocket connecting?
# Browser DevTools > Network > WS tab
# Should show connection to ws://localhost:5173

# Check 3: CORS enabled?
# See vite.*.config.js server.cors setting
```

### Changes Not Showing

```bash
# NOT a caching issue (there is NO cache in dev)

# Check 1: Did dev server detect the change?
# Terminal should show: [vite] hmr update

# Check 2: Did you save the file?
# VS Code: Check for dot next to file name

# Check 3: Are you editing the correct file?
# Verify file path matches what's imported

# Check 4: Syntax error preventing HMR?
# Check browser console for React errors
```

### Build Fails

```bash
# TypeScript errors
npm run type-check
# Fix type errors before building

# Missing dependencies
npm install
# Reinstall if package.json changed

# Out of memory
NODE_OPTIONS="--max-old-space-size=4096" npm run build
# Increase Node.js memory limit
```

---

## Related Documentation

- [02-architecture.md](./02-architecture.md) - System architecture
- [05-frontend-patterns.md](./05-frontend-patterns.md) - React development patterns
- [08-troubleshooting.md](./08-troubleshooting.md) - Common issues and solutions
