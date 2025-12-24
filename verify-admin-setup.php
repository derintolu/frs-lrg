<?php
/**
 * Quick verification script for admin setup
 * Run: wp eval-file verify-admin-setup.php
 */

echo "=== LRH Admin Setup Verification ===\n\n";

// 1. Check if assets are built
$admin_manifest = __DIR__ . '/assets/admin/dist/manifest.json';
$frontend_manifest = __DIR__ . '/assets/frontend/dist/manifest.json';

echo "1. Built Assets:\n";
echo "   Admin manifest: " . (file_exists($admin_manifest) ? "✓ EXISTS" : "✗ MISSING") . "\n";
echo "   Frontend manifest: " . (file_exists($frontend_manifest) ? "✓ EXISTS" : "✗ MISSING") . "\n\n";

// 2. Check if shortcodes are registered
global $shortcode_tags;
echo "2. Registered Shortcodes:\n";
echo "   [lrh_portal]: " . (isset($shortcode_tags['lrh_portal']) ? "✓ REGISTERED" : "✗ NOT REGISTERED") . "\n";
echo "   [lrh_portal_sidebar]: " . (isset($shortcode_tags['lrh_portal_sidebar']) ? "✓ REGISTERED" : "✗ NOT REGISTERED") . "\n";
echo "   [frs_partnership_portal]: " . (isset($shortcode_tags['frs_partnership_portal']) ? "✓ REGISTERED" : "✗ NOT REGISTERED") . "\n\n";

// 3. Check if routes are registered
$routes = rest_get_server()->get_routes();
echo "3. REST API Routes:\n";
$lrh_routes = array_filter(array_keys($routes), function($route) {
    return strpos($route, '/lrh/v1') !== false;
});
echo "   Found " . count($lrh_routes) . " LRH API routes\n";
echo "   Sample: " . (isset($lrh_routes[0]) ? $lrh_routes[0] : 'None') . "\n\n";

// 4. Check admin screen ID
echo "4. Expected Admin Screen ID:\n";
echo "   toplevel_page_lending-resource-hub\n\n";

echo "5. Access Admin Page:\n";
echo "   " . admin_url('admin.php?page=lending-resource-hub') . "\n";
echo "   " . admin_url('admin.php?page=lending-resource-hub#/system-diagnostic') . "\n\n";

echo "=== Verification Complete ===\n";
