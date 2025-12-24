<?php
/**
 * Debug script to check shortcode registration
 *
 * Usage: wp eval-file debug-shortcodes.php
 */

global $shortcode_tags;

echo "=== Registered Shortcodes ===\n\n";

$portal_shortcodes = array(
    'lrh_portal',
    'lrh_portal_sidebar',
    'frs_partnership_portal'
);

foreach ($portal_shortcodes as $shortcode) {
    if (isset($shortcode_tags[$shortcode])) {
        echo "✓ [{$shortcode}] - REGISTERED\n";
        $callback = $shortcode_tags[$shortcode];
        if (is_array($callback)) {
            echo "  Callback: " . get_class($callback[0]) . "::" . $callback[1] . "\n";
        }
    } else {
        echo "✗ [{$shortcode}] - NOT REGISTERED\n";
    }
}

echo "\n=== Test Rendering ===\n\n";

// Test render
$test_content = '[lrh_portal]';
echo "Input: {$test_content}\n";
echo "Output: " . do_shortcode($test_content) . "\n";
