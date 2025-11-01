<?php
/**
 * Debug script to check asset enqueuing
 *
 * Usage: Visit your dashboard page, then run:
 * wp eval 'global $wp_scripts; print_r(array_keys($wp_scripts->registered));' | grep lrh
 */

// Check if scripts are enqueued
add_action('wp_footer', function() {
    global $wp_scripts;

    echo "\n<!-- DEBUG: LRH Scripts -->\n";
    echo "<!-- Registered: " . (isset($wp_scripts->registered['lrh-portal']) ? 'YES' : 'NO') . " -->\n";
    echo "<!-- Enqueued: " . (wp_script_is('lrh-portal', 'enqueued') ? 'YES' : 'NO') . " -->\n";
    echo "<!-- Queue: " . (wp_script_is('lrh-portal', 'queue') ? 'YES' : 'NO') . " -->\n";

    if (isset($wp_scripts->registered['lrh-portal'])) {
        $script = $wp_scripts->registered['lrh-portal'];
        echo "<!-- Source: " . $script->src . " -->\n";
    }
}, 9999);
