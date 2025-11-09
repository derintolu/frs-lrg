# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**Plugin Name:** Lending Resource Hub (LRH)
**Architecture:** WordPress Plugin Boilerplate + Eloquent ORM + React + TypeScript + Vite
**Environment:** Local by Flywheel (hub21.local)
**Live URL:** https://hub21.local
**Portal URL:** https://hub21.local/dashboard (requires login)

---

## 🚨 CRITICAL RULES 🚨

### 1. RESEARCH-FIRST APPROACH

**MANDATORY - Before making ANY changes:**

1. **READ THE ACTUAL CODE** - Never assume how something works
2. **STUDY EXISTING PATTERNS** - Look at how similar functionality is implemented
3. **EXAMINE DEPENDENCIES** - Read plugin/theme code to understand their system
4. **VERIFY YOUR UNDERSTANDING** - Use Grep, Read, Bash tools to confirm
5. **ASK CLARIFYING QUESTIONS** - Ask instead of guessing

**NEVER:**
- Assume how a WordPress plugin/theme works without reading its code
- Suggest solutions based on general knowledge instead of examining this codebase
- Make changes without understanding the existing architecture
- Guess at API endpoints, hooks, or data structures
- Say "it should work" without verifying it actually works

### 2. ALWAYS USE DEV SERVER

**NEVER run `npm run build` repeatedly during development.**

```bash
npm run dev              # Start both frontend (5173) and admin (5174)
```

**Why:**
- Hot Module Replacement (HMR) - changes apply INSTANTLY
- No build step required - edit code and see changes immediately
- Only build when done: `npm run build`

**CORS Configuration Required:**
```javascript
// vite.frontend.config.js & vite.admin.config.js
server: {
  cors: true,
  origin: 'http://localhost:5173',  // Match the port
  host: 'localhost',
  port: 5173,  // 5173 for frontend, 5174 for admin
}
```

### 2.5. PORTAL URL AND LOGIN

**CRITICAL - Portal URL:**
- Portal is located at: `https://hub21.local/dashboard`
- **NOT** at `/portal/lo` or `/lo/`
- **REQUIRES LOGIN** - You must be logged in as a user with `loan_officer` or `realtor_partner` role

**When using Chrome DevTools MCP:**
1. Navigate to: `https://hub21.local/dashboard`
2. If not logged in, you'll need to login first
3. Then you can inspect portal components

### 3. NEVER BLAME CACHING

When changes aren't showing, **NEVER** assume it's caching. There is **NO** caching in dev.

**Action steps:**
1. Check if dev server detected the change (look for HMR update in terminal)
2. Verify computed styles in browser DevTools
3. Check for JavaScript errors in browser console
4. Verify correct file was edited

### 4. PRE-WORK VERIFICATION CHECKLIST

**MANDATORY - Before starting ANY task, verify you are in the correct plugin:**

```bash
# 1. Verify working directory
pwd

# 2. Check current branch
git branch --show-current

# 3. Verify clean working tree
git status
```

**Expected Output for frs-lrg (ACTIVE DEVELOPMENT):**
```
/Users/derintolu/Local Sites/hub21/app/public/wp-content/plugins/frs-lrg
```

**Plugin Context:**
- **frs-lrg** = ACTIVE development plugin ✅ (ALL new work happens here)
- **frs-wp-users** = ACTIVE user profile plugin ✅ (User CRUD, webhooks, sync)
- **frs-partnership-portal** = DEPRECATED ⚠️ (reference only, being sunset)

**If you find yourself in frs-partnership-portal:**
1. STOP immediately
2. Navigate to frs-lrg: `cd /Users/derintolu/Local\ Sites/hub21/app/public/wp-content/plugins/frs-lrg`
3. Verify location with `pwd`
4. Continue work in correct plugin

**Why This Matters:**
Working in the wrong plugin wastes hours of development time. frs-partnership-portal is only kept for reference during migration to frs-lrg. Any work done there must be discarded and redone in frs-lrg.

---

## Quick Command Reference

```bash
# Development (USE THIS - NOT npm run build)
npm run dev              # Both frontend + admin with HMR
npm run dev:frontend     # Frontend only (port 5173)
npm run dev:admin        # Admin only (port 5174)

# Production build (ONLY WHEN DONE WITH DEVELOPMENT)
npm run build            # Build all: frontend + admin + blocks
npm run block:build      # Gutenberg blocks only

# WordPress CLI commands
wp plugin activate frs-lrg
wp plugin deactivate frs-lrg
wp rewrite flush

# Database queries
wp db query "SHOW TABLES LIKE 'wp_partnerships'"
wp db query "SELECT * FROM wp_partnerships LIMIT 5"

# PHP debugging (quick testing without writing files)
wp eval "echo 'Debug: ' . get_current_user_id();"
wp eval-file path/to/debug-script.php

# Post type operations
wp post-type list
wp post list --post_type=partnership --format=table

# User operations
wp user list --role=loan_officer
wp user meta get <user_id> <meta_key>

# Composer operations (after model changes)
composer dump-autoload
```

---

## Development Environment

### Local by Flywheel Setup
- **Site Name:** hub21
- **Domain:** hub21.local
- **WordPress Version:** 6.4+
- **PHP Version:** 8.1+
- **Database Prefix:** wp_

### Key URLs
- **Site:** https://hub21.local
- **Admin:** https://hub21.local/wp-admin
- **Portal:** https://hub21.local/dashboard (requires login)
- **REST API:** https://hub21.local/wp-json/lrh/v1/

### Dev Server Ports
- **Frontend Vite:** http://localhost:5173
- **Admin Vite:** http://localhost:5174
- **Storybook:** http://localhost:6006

### Required User Roles
- `loan_officer` - Full access to loan officer portal features
- `realtor_partner` - Full access to realtor partner portal features
- `administrator` - Full WordPress admin access

---

## Documentation Index

### Development Workflow
- **[.claude/docs/01-development-workflow.md](.claude/docs/01-development-workflow.md)** - Dev server, HMR, build process, npm scripts

### Architecture
- **[.claude/docs/02-architecture.md](.claude/docs/02-architecture.md)** - Backend (Eloquent ORM, REST API, Migrations), Frontend (React, Vite), Gutenberg Blocks

### CSS & Styling
- **[.claude/docs/03-css-styling.md](.claude/docs/03-css-styling.md)** - Transform scale math, Tailwind patterns, responsive design, breakpoints

### Backend Patterns
- **[.claude/docs/04-backend-patterns.md](.claude/docs/04-backend-patterns.md)** - Eloquent models, migrations, REST API routing, shortcodes, asset management

### Frontend Patterns
- **[.claude/docs/05-frontend-patterns.md](.claude/docs/05-frontend-patterns.md)** - React/TypeScript components, state management, routing

### Security Standards
- **[.claude/docs/06-security-standards.md](.claude/docs/06-security-standards.md)** - Input sanitization, database queries, permissions, PHP 8.1+ standards

### Common Development Tasks
- **[.claude/docs/07-common-tasks.md](.claude/docs/07-common-tasks.md)** - Adding tables, API endpoints, React components, Gutenberg blocks

### Troubleshooting
- **[.claude/docs/08-troubleshooting.md](.claude/docs/08-troubleshooting.md)** - Plugin activation, API 404s, blank screens, dev server issues

### Boilerplate Features
- **[.claude/docs/09-boilerplate-features.md](.claude/docs/09-boilerplate-features.md)** - Storybook, Documentation Site, Utility Scripts, Shadcn UI, GitHub Actions, Grunt

### External Dependencies
- **[.claude/docs/10-external-dependencies.md](.claude/docs/10-external-dependencies.md)** - WordPress plugins, PHP packages, NPM packages, model dependencies

### React SPA Admin Pattern
- **[.claude/docs/11-react-spa-admin-pattern.md](.claude/docs/11-react-spa-admin-pattern.md)** - How shadcn/ui components replace PHP admin pages, complete SPA implementation guide, multiplugin architecture

### PHP vs React Admin Comparison
- **[.claude/docs/12-php-vs-react-admin-comparison.md](.claude/docs/12-php-vs-react-admin-comparison.md)** - Decision matrix, complete comparison, migration path, hybrid approach, real-world examples

### Migration Status from frs-partnership-portal
- **[.claude/docs/13-migration-status-from-frs-partnership-portal.md](.claude/docs/13-migration-status-from-frs-partnership-portal.md)** - Tracking what has been migrated, replaced, and what remains. Database tables, REST API endpoints, UI components, post types, integrations. Includes migration patterns and architecture shift analysis.

### Migration Verification Checklist
- **[.claude/docs/14-migration-verification-checklist.md](.claude/docs/14-migration-verification-checklist.md)** - Comprehensive verification of dual-interface architecture. Confirmed: ALL 35+ frontend tools migrated, shortcodes, post types, enhanced features (Rentcast API, Calendar). Verification status of blocks, roles, integrations.

---

## Project Overview

**Purpose:** Learning management and partnership platform for 21st Century Lending

**Database Tables:**
- `wp_partnerships` - Partnership relationships
- `wp_lead_submissions` - Lead tracking
- `wp_page_assignments` - User-to-page mapping

**Namespace:** `LendingResourceHub`
**Route Prefix:** `lrh/v1`
**Text Domain:** `lending-resource-hub`

---

## Most Important Rules Summary

1. **Always use dev server** - `npm run dev`, NOT constant rebuilds
2. **Configure CORS** - Required for Vite dev server with local WordPress
3. **Research first** - Read actual code before making changes
4. **Never blame caching** - Assume issue is in your code
5. **Understand the math** - CSS transform scale = targetWidth ÷ actualWidth
6. **Verify in browser** - Use DevTools to check computed styles
7. **Use Eloquent ORM** - Never raw SQL queries
8. **Use md: breakpoint** - For desktop (768px), NOT lg: (1024px)
9. **Tailwind patterns** - Start with display type, add max-md:hidden or md:hidden
10. **Git workflow** - Branch → Develop → Test → Commit → Push → Merge → Push

**These rules prevent wasting hours on issues that could be avoided by understanding the tooling and architecture.**

---

## File Structure

```
frs-lrg/
├── .claude/docs/        # Detailed documentation
├── assets/
│   ├── admin/dist/      # Built admin assets
│   └── frontend/dist/   # Built frontend assets
├── blocks/              # Gutenberg blocks (16 blocks)
├── database/Migrations/ # Schema migrations
├── includes/
│   ├── Controllers/     # API endpoint controllers
│   ├── Models/          # Eloquent models
│   └── Routes/          # API route definitions
├── src/
│   ├── frontend/        # React frontend source
│   └── admin/           # React admin source
├── composer.json        # PHP dependencies
├── package.json         # NPM dependencies
└── vite.*.config.js     # Vite configs
```

---

**For detailed information on any topic, see the documentation files in `.claude/docs/`**
