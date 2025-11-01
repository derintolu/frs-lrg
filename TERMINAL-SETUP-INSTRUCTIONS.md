# Terminal Setup Instructions for Lending Resource Hub

## Context
This plugin has been migrated to use WordPress Plugin Boilerplate with Eloquent ORM. The backend PHP code is complete, but Composer dependencies must be installed before the plugin can be activated.

## Current Status
✅ Database migrations created (3 files)
✅ Eloquent models created (3 files)
✅ REST API controllers created (4 controllers)
✅ API routes registered (22 endpoints)
✅ FluentBooking integration created
✅ Shortcode handler created
⏳ Composer dependencies NOT installed yet
⏳ React frontend NOT copied yet

## Commands to Execute

### 1. Navigate to Plugin Directory
```bash
cd /app/public/wp-content/plugins/frs-lrg
```

### 2. Verify Current Location
```bash
pwd
```
Expected output: `/app/public/wp-content/plugins/frs-lrg`

### 3. Check Composer Files Exist
```bash
ls -la composer.json
```
Expected: Should see `composer.json` file

### 4. Install Composer Dependencies
```bash
composer install
```

Expected output:
- Installing prappo/wp-eloquent
- Generating autoload files
- Success message

### 5. Verify Vendor Directory Created
```bash
ls -la vendor/
```
Expected: Should see `vendor/` directory with autoload files

### 6. Verify Autoload File
```bash
ls -la vendor/autoload.php
```
Expected: File exists

### 7. Check Our Custom Files
```bash
ls -la includes/Models/
ls -la database/Migrations/
ls -la includes/Integrations/
ls -la includes/Controllers/
```

Expected files:
- **Models:** Partnership.php, LeadSubmission.php, PageAssignment.php
- **Migrations:** Partnerships.php, LeadSubmissions.php, PageAssignments.php
- **Integrations:** FluentBooking.php
- **Controllers:** Users/, Partnerships/, Leads/, Dashboard/

### 8. Test PHP Syntax (Optional)
```bash
php -l includes/Models/Partnership.php
php -l database/Migrations/Partnerships.php
php -l includes/Integrations/FluentBooking.php
```

Expected: "No syntax errors detected"

## What This Accomplishes

Once Composer dependencies are installed:

1. ✅ Eloquent ORM is available
2. ✅ Database migrations can run
3. ✅ Models can connect to database
4. ✅ Plugin can be activated in WordPress
5. ✅ Tables will be created automatically on activation

## Next Steps After Composer Install

1. Copy React source files from old plugin
2. Set up Vite build configuration
3. Install npm dependencies
4. Build React assets
5. Test plugin activation
6. Test portal shortcode

## Troubleshooting

### If composer not found:
```bash
which composer
```

If not available, try:
```bash
php composer.phar install
```

### If permission errors:
```bash
chmod -R 755 /app/public/wp-content/plugins/frs-lrg
```

### Check PHP version:
```bash
php -v
```
Required: PHP 8.1+

## Summary

Run this single command to complete setup:
```bash
cd /app/public/wp-content/plugins/frs-lrg && composer install && ls -la vendor/autoload.php
```

If successful, you'll see "vendor/autoload.php" exists.
