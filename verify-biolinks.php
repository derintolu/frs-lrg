<?php
/**
 * Verify Biolink Posts and Profile Data
 *
 * Checks if biolink posts have complete profile data.
 *
 * Usage: wp eval-file verify-biolinks.php
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

if (!defined('ABSPATH') && !defined('WP_CLI')) {
    die('Direct access not permitted.');
}

// Load composer autoloader if not already loaded
if (!class_exists('LendingResourceHub\Models\Profile')) {
    $autoload_path = dirname(__FILE__) . '/vendor/autoload.php';
    if (file_exists($autoload_path)) {
        require_once $autoload_path;
    }
}

use LendingResourceHub\Models\Profile;

// Find all biolink posts
$args = array(
    'post_type'      => 'frs_biolink',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
);

$biolink_posts = get_posts($args);

echo "Verifying " . count($biolink_posts) . " biolink posts...\n\n";

foreach ($biolink_posts as $post) {
    echo "========================================\n";
    echo "Post: {$post->post_title} (ID: {$post->ID})\n";
    echo "========================================\n";

    // Check metafields
    $user_id = get_post_meta($post->ID, 'frs_biolink_user', true);
    echo "User ID: " . ($user_id ? $user_id : "❌ MISSING") . "\n";

    if (!$user_id) {
        echo "⚠️  No user ID - biolink will not render correctly\n\n";
        continue;
    }

    // Check if profile exists
    $profile = Profile::where('user_id', $user_id)->first();

    if (!$profile) {
        echo "❌ No Profile found for user_id {$user_id}\n";
        echo "⚠️  Biolink will fail to render - profile data missing\n\n";
        continue;
    }

    echo "✅ Profile found (Profile ID: {$profile->id})\n";
    echo "\nProfile Data:\n";
    echo "  Name: {$profile->first_name} {$profile->last_name}\n";
    echo "  Email: {$profile->email}\n";
    echo "  Job Title: " . ($profile->job_title ?: "Not set") . "\n";
    echo "  Phone: " . ($profile->phone_number ?: $profile->mobile_number ?: "Not set") . "\n";
    echo "  Headshot ID: " . ($profile->headshot_id ?: "Not set") . "\n";
    echo "  ARRIVE Link: " . ($profile->arrive ?: "Not set") . "\n";

    // Check social links
    $socials = array();
    if ($profile->facebook_url) $socials[] = "Facebook";
    if ($profile->instagram_url) $socials[] = "Instagram";
    if ($profile->linkedin_url) $socials[] = "LinkedIn";
    if ($profile->twitter_url) $socials[] = "Twitter";
    if ($profile->youtube_url) $socials[] = "YouTube";
    if ($profile->tiktok_url) $socials[] = "TikTok";

    if (count($socials) > 0) {
        echo "  Social Links: " . implode(", ", $socials) . "\n";
    } else {
        echo "  Social Links: ⚠️  None set\n";
    }

    // Check required fields
    $missing = array();
    if (!$profile->first_name) $missing[] = "first_name";
    if (!$profile->last_name) $missing[] = "last_name";
    if (!$profile->email) $missing[] = "email";

    if (count($missing) > 0) {
        echo "\n⚠️  Warning - Missing critical fields: " . implode(", ", $missing) . "\n";
    } else {
        echo "\n✅ All critical fields present\n";
    }

    echo "\n";
}

echo "Verification complete!\n";
