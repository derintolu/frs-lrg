<?php
/**
 * Server-side rendering for the biolink-page block
 *
 * @param array    $attributes Block attributes
 * @param string   $content    Block content
 * @param WP_Block $block      Block instance
 * @return string  Block HTML output
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use FRSUsers\Models\Profile;

// Helper function for social icons
function lrh_get_social_icon($platform) {
    $icons = array(
        'email' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" fill="none"/><polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/></svg>',
        'facebook' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'instagram' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
        'linkedin' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        'twitter' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'
    );

    return isset($icons[$platform]) ? $icons[$platform] : $icons['email'];
}

// Helper function for action icons
function lrh_get_action_icon($icon) {
    $icons = array(
        'phone' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
        'calendar' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>',
        'check-circle' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
        'calculator' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8-4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zM9 9H7V7h2v2zm4 0h-2V7h2v2zm4 0h-2V7h2v2z"/></svg>',
        'message-square' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>'
    );

    return isset($icons[$icon]) ? $icons[$icon] : $icons['phone'];
}

// Get the page owner's user ID
// Priority: 1) Block attribute user_id, 2) Meta field, 3) Post author
global $post;
$user_id = null;

// First check if user_id is provided in block attributes
if (!empty($attributes['user_id'])) {
    $user_id = intval($attributes['user_id']);
}

// If not in attributes, check post meta
if (!$user_id && $post) {
    $meta_user_id = get_post_meta($post->ID, 'frs_biolink_user', true);
    if ($meta_user_id) {
        $user_id = intval($meta_user_id);
    }
}

// Last resort: use post author
if (!$user_id && $post) {
    $user_id = intval($post->post_author);
}

$page_id = $attributes['page_id'] ?? ($post ? $post->ID : 0);

if (!$user_id) {
    return '<div class="frs-biolink-error">Error: No user ID found for biolink page</div>';
}

// Get user and person data
$user = get_user_by('ID', $user_id);
if (!$user) {
    return '<div class="frs-biolink-error">Error: User not found</div>';
}

// Get data from Profile model (FRSUsers plugin)
$profile = Profile::where('user_id', $user_id)->first();

if ($profile) {
    $name = trim($profile->first_name . ' ' . $profile->last_name);

    // Get title from profile
    $title = $profile->job_title ?: 'Senior Loan Officer';

    // Get headshot
    if ($profile->headshot_id) {
        $logo_url = wp_get_attachment_image_url($profile->headshot_id, 'medium');
        if (!$logo_url) {
            $logo_url = get_avatar_url($user_id);
        }
    } else {
        $logo_url = get_avatar_url($user_id);
    }

    $email = $profile->email ?: $user->user_email;
    $phone = $profile->phone_number ?: $profile->mobile_number ?: '';
    $arrive_link = $profile->arrive ?: '';
    // Try nmls field first, then nmls_number
    $nmls_number = $profile->nmls ?: $profile->nmls_number ?: '';
} else {
    // Fallback to user data if no Profile found
    $name = $user->display_name;
    $title = 'Senior Loan Officer';
    $logo_url = get_avatar_url($user_id);
    $email = $user->user_email;
    $phone = get_user_meta($user_id, 'phone', true) ?: get_user_meta($user_id, 'phone_number', true) ?: '';
    $arrive_link = get_user_meta($user_id, 'frs_arrive_link', true) ?: '';
    $nmls_number = get_user_meta($user_id, 'nmls_number', true) ?: '';
}

$company = !empty($nmls_number) ? 'NMLS# ' . $nmls_number : '21st Century Lending';
$bg_color = 'linear-gradient(135deg, #000000 43%, #180a62 154%)';
$bg_video_url = LRH_URL . 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4';
$company_logo_url = LRH_URL . 'assets/images/21C-Wordmark-White.svg';

// Phone URL for call button
$phone_url = !empty($phone) ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';

ob_start();
?>
<!-- Header Section -->
<div class="frs-biolink-header" style="position: relative; padding: 40px 0; text-align: center; color: white; overflow: hidden; min-height: 400px; background: <?php echo $bg_color; ?>; font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <!-- Video Background -->
    <video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;" autoplay muted loop playsinline>
        <source src="<?php echo esc_url($bg_video_url); ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>


    <!-- Content -->
    <div style="position: relative; z-index: 3; width: 100%; margin: 0 auto; padding: 10px 0 0 0; box-sizing: border-box;">
        <?php if ($company_logo_url): ?>
            <img src="<?php echo esc_url($company_logo_url); ?>" alt="21st Century Lending" style="max-width: 200px; height: auto; margin: 0 auto 15px auto; margin-top: 0; display: block; filter: brightness(1) contrast(1); transform: translateY(-10px);">
        <?php endif; ?>

        <?php if ($logo_url): ?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($name); ?>" style="width: clamp(120px, 14vw, 150px); height: clamp(120px, 14vw, 150px); border-radius: 50%; margin: 0 auto 5px auto; display: block; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 4px 20px rgba(0,0,0,0.3); object-fit: cover; margin-top: 0; transform: translateY(-5px);">
        <?php endif; ?>

        <?php if ($name): ?>
            <h1 style="margin: 0 0 5px 0; font-size: 2.2rem; font-weight: bold; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; transform: translateY(-5px);"><?php echo esc_html($name); ?></h1>
        <?php endif; ?>

        <?php if ($title): ?>
            <p style="margin: 0 0 5px 0; font-size: 1.2rem; opacity: 0.95; text-shadow: 0 1px 2px rgba(0,0,0,0.5); font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; transform: translateY(-5px);"><?php echo esc_html($title); ?></p>
        <?php endif; ?>

        <?php if ($company): ?>
            <p style="margin: 0; font-size: 1.1rem; opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.5); font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; transform: translateY(-5px);"><?php echo esc_html($company); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Social Media Section -->
<div class="frs-biolink-social" style="text-align: center; padding: 10px 0; background: transparent;">
    <div style="width: 100%; margin: 0 auto; padding: 0; box-sizing: border-box;">
        <div style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap;">
            <a href="mailto:<?php echo esc_attr($email); ?>" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: white; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-decoration: none; transition: transform 0.2s;">
                <?php echo lrh_get_social_icon('email'); ?>
            </a>
        </div>
    </div>
</div>

<!-- Call Me Now Button -->
<?php if ($phone_url): ?>
<div class="frs-biolink-button" style="padding: 8px 0; background: transparent;">
    <div style="width: 100%; margin: 0 auto; padding: 0; box-sizing: border-box;">
        <a href="<?php echo esc_url($phone_url); ?>" style="display: flex; align-items: center; justify-content: center; gap: 12px; background: linear-gradient(145deg, #f8f9fa, #e9ecef); color: #212529; text-decoration: none; padding: 18px 32px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04); font-size: 17.6px; font-weight: 500; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.8);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04)'">
            <?php echo lrh_get_action_icon('phone'); ?>
            <span><?php _e('Call Me Now', 'lending-resource-hub'); ?></span>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Schedule Appointment Button -->
<div class="frs-biolink-button" style="padding: 8px 0; background: transparent;">
    <div style="width: 100%; margin: 0 auto; padding: 0; box-sizing: border-box;">
        <a href="#schedule-appointment" style="display: flex; align-items: center; justify-content: center; gap: 12px; background: linear-gradient(145deg, #f8f9fa, #e9ecef); color: #212529; text-decoration: none; padding: 18px 32px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04); font-size: 17.6px; font-weight: 500; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.8);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04)'">
            <?php echo lrh_get_action_icon('calendar'); ?>
            <span><?php _e('Schedule Appointment', 'lending-resource-hub'); ?></span>
        </a>
    </div>
</div>

<!-- Hidden Form Container for Scheduling -->
<div id="schedule-appointment" data-form-id="7" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999; overflow-y: auto;">
    <button class="frs-form-back" style="position: absolute; top: 20px; left: 20px; background: none; border: none; font-size: 16px; cursor: pointer; z-index: 10; color: #333;">← Back to Profile</button>
    <div style="width: 100%; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 80px 20px 40px 20px; box-sizing: border-box;">
        <div style="width: 100%; max-width: 600px;">
            <h2 style="color: #333; margin-bottom: 40px; text-align: center; font-size: 32px; font-weight: 600;"><?php _e('Schedule Appointment', 'lending-resource-hub'); ?></h2>
            <div style="width: 100%;">
                <?php echo do_shortcode('[fluentform id="7" type="conversational"]'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Get Pre-Approved Button -->
<?php if ($arrive_link): ?>
<div class="frs-biolink-button" style="padding: 8px 0; background: transparent;">
    <div style="width: 100%; margin: 0 auto; padding: 0; box-sizing: border-box;">
        <a href="<?php echo esc_url($arrive_link); ?>" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 12px; background: linear-gradient(145deg, #1e40af, #1e3a8a); color: #ffffff; text-decoration: none; padding: 18px 32px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.08); font-size: 17.6px; font-weight: 500; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.1);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.2), 0 4px 8px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.08)'">
            <?php echo lrh_get_action_icon('check-circle'); ?>
            <span><?php _e('Get Pre-Approved', 'lending-resource-hub'); ?></span>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Free Rate Quote Button -->
<div class="frs-biolink-button" style="padding: 8px 0; background: transparent;">
    <div style="width: 100%; margin: 0 auto; padding: 0; box-sizing: border-box;">
        <a href="#rate-quote" style="display: flex; align-items: center; justify-content: center; gap: 12px; background: linear-gradient(145deg, #f8f9fa, #e9ecef); color: #212529; text-decoration: none; padding: 18px 32px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04); font-size: 17.6px; font-weight: 500; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.8);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04)'">
            <?php echo lrh_get_action_icon('calculator'); ?>
            <span><?php _e('Free Rate Quote', 'lending-resource-hub'); ?></span>
        </a>
    </div>
</div>

<!-- Hidden Form Container for Rate Quote -->
<div id="rate-quote" data-form-id="6" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999; overflow-y: auto;">
    <button class="frs-form-back" style="position: absolute; top: 20px; left: 20px; background: none; border: none; font-size: 16px; cursor: pointer; z-index: 10; color: #333;">← Back to Profile</button>
    <div style="width: 100%; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 80px 20px 40px 20px; box-sizing: border-box;">
        <div style="width: 100%; max-width: 600px;">
            <h2 style="color: #333; margin-bottom: 40px; text-align: center; font-size: 32px; font-weight: 600;"><?php _e('Free Rate Quote', 'lending-resource-hub'); ?></h2>
            <div style="width: 100%;">
                <?php echo do_shortcode('[fluentform id="6" type="conversational"]'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Thank You Section (Fullscreen) -->
<div id="thank-you" data-form-id="thank-you" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999; overflow-y: auto;">
    <div style="width: 100%; height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;">
        <div style="text-align: center;">
            <h2 style="color: #1e3a8a; font-size: 2.5rem; margin-bottom: 20px; font-weight: 600;"><?php _e('Thank You!', 'lending-resource-hub'); ?></h2>
            <p style="color: #666; font-size: 1.2rem; margin-bottom: 30px;"><?php _e('Your submission has been received. I will get back to you within 24 hours.', 'lending-resource-hub'); ?></p>
            <button onclick="window.location.hash=''" style="background: #1e3a8a; color: white; border: none; padding: 15px 40px; border-radius: 8px; font-size: 16px; font-weight: 500; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"><?php _e('Close', 'lending-resource-hub'); ?></button>
        </div>
    </div>
</div>

<script>
// Handle form visibility
document.addEventListener('DOMContentLoaded', function() {
    const formContainers = document.querySelectorAll('[data-form-id]');
    const backButtons = document.querySelectorAll('.frs-form-back');

    // Initially hide all forms
    formContainers.forEach(container => {
        container.style.display = 'none';
    });

    // Handle hash navigation for forms
    function showFormFromHash() {
        const hash = window.location.hash.substring(1); // Remove the #

        // Hide all forms first
        formContainers.forEach(container => {
            container.style.display = 'none';
        });

        // Show the form matching the hash
        if (hash) {
            const targetForm = document.getElementById(hash);
            if (targetForm) {
                targetForm.style.display = 'block';
                // Hide header and other content
                document.querySelector('.frs-biolink-header').style.display = 'none';
                document.querySelector('.frs-biolink-social').style.display = 'none';
                document.querySelectorAll('.frs-biolink-button').forEach(btn => btn.style.display = 'none');
            }
        } else {
            // Show main content
            document.querySelector('.frs-biolink-header').style.display = 'block';
            document.querySelector('.frs-biolink-social').style.display = 'block';
            document.querySelectorAll('.frs-biolink-button').forEach(btn => btn.style.display = 'block');
        }
    }

    // Listen for hash changes
    window.addEventListener('hashchange', showFormFromHash);

    // Check hash on page load
    showFormFromHash();

    // Handle back button clicks
    backButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.location.hash = '';
        });
    });

    // Handle hash link button clicks (prevent smooth scroll interference)
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href !== '#' && href.startsWith('#')) {
                e.preventDefault();
                window.location.hash = href;
            }
        });
    });

    // Listen for FluentForms submission success
    jQuery(document).on('fluentform_submission_success', function(event, data) {
        // Navigate to thank you page
        window.location.hash = 'thank-you';
    });
});
</script>

<?php
return ob_get_clean();
?>
