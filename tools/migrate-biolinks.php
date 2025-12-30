<?php
/**
 * Biolink Migration Script
 *
 * Updates existing biolink posts from old plugin format to new plugin format.
 *
 * Changes:
 * - Block name: frs/biolink-page → lrh/biolink-page
 * - Metafield: _frs_assigned_user_id → frs_biolink_user
 * - Adds: _frs_loan_officer_id metafield
 * - Adds: _frs_page_views and _frs_page_conversions (if not exist)
 *
 * Usage: wp eval-file migrate-biolinks.php
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

// Security check
if (!defined('ABSPATH') && !defined('WP_CLI')) {
    die('Direct access not permitted.');
}

global $wpdb;

// Find all biolink posts
$args = array(
    'post_type'      => 'frs_biolink',
    'posts_per_page' => -1,
    'post_status'    => 'any',
);

$biolink_posts = get_posts($args);

echo "Found " . count($biolink_posts) . " biolink posts to migrate.\n\n";

$updated = 0;
$errors = 0;

foreach ($biolink_posts as $post) {
    echo "Processing: {$post->post_title} (ID: {$post->ID})\n";

    // 1. Update block name in post_content
    try {
        $old_content = $post->post_content;
        $new_content = str_replace('<!-- wp:frs/biolink-page', '<!-- wp:lrh/biolink-page', $old_content);

        if ($old_content !== $new_content) {
            // Use direct update to avoid hooks that might cause issues
            $wpdb->update(
                $wpdb->posts,
                array('post_content' => $new_content),
                array('ID' => $post->ID),
                array('%s'),
                array('%d')
            );
            echo "  ✅ Updated block name: frs/biolink-page → lrh/biolink-page\n";
        } else {
            echo "  ℹ️  Block name already correct\n";
        }
    } catch (Exception $e) {
        echo "  ❌ Error updating post content: " . $e->getMessage() . "\n";
        $errors++;
        continue;
    }

    // 2. Get user ID from metafields (try multiple sources)
    $user_id = get_post_meta($post->ID, 'frs_biolink_user', true); // Check new field first

    if (!$user_id) {
        $user_id = get_post_meta($post->ID, '_frs_assigned_user_id', true); // Old field
    }

    if (!$user_id) {
        $user_id = get_post_meta($post->ID, 'loan_officer_user', true); // Alternative old field
    }

    if ($user_id) {
        echo "  ℹ️  User ID: {$user_id}\n";

        // 3. Add new metafields
        update_post_meta($post->ID, 'frs_biolink_user', $user_id);
        update_post_meta($post->ID, '_frs_loan_officer_id', $user_id);
        update_post_meta($post->ID, 'frs_biolink_page', '1');
        echo "  ✅ Added metafields: frs_biolink_user, _frs_loan_officer_id, frs_biolink_page\n";

        // 4. Initialize stats if they don't exist
        if (!get_post_meta($post->ID, '_frs_page_views', true)) {
            update_post_meta($post->ID, '_frs_page_views', 0);
            echo "  ✅ Initialized _frs_page_views to 0\n";
        }

        if (!get_post_meta($post->ID, '_frs_page_conversions', true)) {
            update_post_meta($post->ID, '_frs_page_conversions', 0);
            echo "  ✅ Initialized _frs_page_conversions to 0\n";
        }

        $updated++;
        echo "  ✅ Migration completed for {$post->post_title}\n\n";
    } else {
        echo "  ⚠️  Warning: No user ID found for this biolink\n\n";
        $errors++;
    }
}

echo "\n================================\n";
echo "Migration Summary:\n";
echo "================================\n";
echo "Total posts: " . count($biolink_posts) . "\n";
echo "✅ Successfully updated: {$updated}\n";
echo "❌ Errors: {$errors}\n";
echo "\nMigration complete!\n";
