<?php
/**
 * Test script to simulate full page rendering with shortcode
 *
 * Usage: wp eval-file test-page-render.php
 */

echo "=== Simulating Page Render with Shortcode ===\n\n";

// Simulate the_content filter processing
$content = '[frs_partnership_portal]';

echo "1. Original content: {$content}\n";

// Process shortcodes
$processed_content = do_shortcode($content);

echo "2. Processed content: {$processed_content}\n";

// Check if scripts are registered/enqueued
global $wp_scripts;

if (isset($wp_scripts->registered['lrh-portal'])) {
    echo "3. ✓ Portal script is REGISTERED\n";
    $script = $wp_scripts->registered['lrh-portal'];
    echo "   Source: " . $script->src . "\n";
} else {
    echo "3. ✗ Portal script is NOT registered\n";
}

if (wp_script_is('lrh-portal', 'enqueued')) {
    echo "4. ✓ Portal script is ENQUEUED\n";
} else if (wp_script_is('lrh-portal', 'registered')) {
    echo "4. ⚠ Portal script is registered but NOT enqueued\n";
    echo "   This might be OK if it gets enqueued during wp_footer\n";
} else {
    echo "4. ✗ Portal script is NOT enqueued\n";
}

// Check React dependencies
if (wp_script_is('react', 'enqueued')) {
    echo "5. ✓ React is enqueued\n";
} else {
    echo "5. ✗ React is NOT enqueued\n";
}

if (wp_script_is('react-dom', 'enqueued')) {
    echo "6. ✓ React DOM is enqueued\n";
} else {
    echo "6. ✗ React DOM is NOT enqueued\n";
}

// Check inline config
if (isset($wp_scripts->registered['lrh-portal']->extra['before'])) {
    echo "7. ✓ Inline config is attached\n";
    $config = $wp_scripts->registered['lrh-portal']->extra['before'][0];
    if (strpos($config, 'window.lrhPortalConfig') !== false) {
        echo "   Contains window.lrhPortalConfig\n";
    }
} else {
    echo "7. ✗ NO inline config attached\n";
}

echo "\n=== Simulating wp_footer (script output) ===\n\n";

// Get the scripts that would be printed
ob_start();
wp_print_scripts('lrh-portal');
$script_output = ob_get_clean();

if (strpos($script_output, 'lrh-portal') !== false) {
    echo "8. ✓ Script tag would be output\n";
    echo "   Length: " . strlen($script_output) . " bytes\n";
} else {
    echo "8. ✗ No script output\n";
}

if (strpos($script_output, 'window.lrhPortalConfig') !== false) {
    echo "9. ✓ Config would be output\n";
} else {
    echo "9. ✗ Config would NOT be output\n";
}

echo "\n=== Test Complete ===\n";
