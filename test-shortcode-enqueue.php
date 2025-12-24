<?php
/**
 * Test script to verify shortcode asset enqueuing
 *
 * Usage: wp eval-file test-shortcode-enqueue.php
 */

// Simulate shortcode rendering
echo "=== Testing Shortcode Asset Enqueuing ===\n\n";

// Get the Frontend instance
$frontend = \LendingResourceHub\Assets\Frontend::get_instance();

echo "1. Frontend instance obtained\n";

// Call the public enqueue method
$frontend->enqueue_portal_assets_public();

echo "2. enqueue_portal_assets_public() called\n";

// Check if script is registered
global $wp_scripts;

if (isset($wp_scripts->registered['lrh-portal'])) {
    echo "3. ✓ Script 'lrh-portal' is REGISTERED\n";
    $script = $wp_scripts->registered['lrh-portal'];
    echo "   Source: " . $script->src . "\n";
    echo "   Dependencies: " . implode(', ', $script->deps) . "\n";
    echo "   In footer: " . ($script->extra['group'] ?? 'false') . "\n";
} else {
    echo "3. ✗ Script 'lrh-portal' is NOT registered\n";
}

// Check if script is enqueued
if (wp_script_is('lrh-portal', 'enqueued')) {
    echo "4. ✓ Script 'lrh-portal' is ENQUEUED\n";
} else {
    echo "4. ✗ Script 'lrh-portal' is NOT enqueued (this is expected, we only registered it)\n";
}

// Check inline scripts
if (isset($wp_scripts->registered['lrh-portal'])) {
    $script = $wp_scripts->registered['lrh-portal'];
    if (isset($script->extra['before']) && !empty($script->extra['before'])) {
        echo "5. ✓ Inline config script is attached\n";
        echo "   Config preview: " . substr($script->extra['before'][0], 0, 100) . "...\n";
    } else {
        echo "5. ✗ NO inline config script found\n";
    }
}

echo "\n=== Test Complete ===\n";
