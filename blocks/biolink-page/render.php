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

// Get data from People CPT linked to this user
$person_data = null;
if (class_exists('FRS_ACF_Fields') && method_exists('FRS_ACF_Fields', 'get_loan_officer_person')) {
    $person_data = FRS_ACF_Fields::get_loan_officer_person($user_id);
}

// Use People CPT data if available, otherwise fall back to user data
if ($person_data && is_array($person_data)) {
    $name = $person_data['name'] ?: $user->display_name;

    // Always use "Licensed Loan Officer" with NMLS number if available
    $nmls_number = $person_data['nmls_number'] ?? '';
    if (!empty($nmls_number)) {
        $title = 'Licensed Loan Officer | NMLS# ' . $nmls_number;
    } else {
        $title = 'Licensed Loan Officer';
    }

    $logo_url = $person_data['headshot'] ?: get_avatar_url($user_id);
    $email = $person_data['primary_business_email'] ?: $user->user_email;
    // Check multiple phone fields in person data
    $phone = $person_data['phone'] ?: $person_data['phone_number'] ?: '';
    $arrive_link = $person_data['arrive'] ?: '';
} else {
    // Fallback to user data if no People CPT found
    $name = $user->display_name;

    // Always use "Licensed Loan Officer" with NMLS number if available
    $nmls_number = get_user_meta($user_id, 'nmls_number', true);
    if (!empty($nmls_number)) {
        $title = 'Licensed Loan Officer | NMLS# ' . $nmls_number;
    } else {
        $title = 'Licensed Loan Officer';
    }

    $logo_url = get_avatar_url($user_id);
    $email = $user->user_email;
    // Check multiple phone meta keys
    $phone = get_user_meta($user_id, 'phone', true) ?: get_user_meta($user_id, 'phone_number', true) ?: '';
    $arrive_link = get_user_meta($user_id, 'frs_arrive_link', true) ?: '';
}

$company = '21st Century Lending';
$bg_color = 'linear-gradient(135deg, #000000 43%, #180a62 154%)';
$bg_video_url = FRS_PORTAL_PLUGIN_URL . 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4';
$company_logo_url = FRS_PORTAL_PLUGIN_URL . 'assets/images/21C-Wordmark-White.svg';

// Phone URL for call button
$phone_url = !empty($phone) ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';

ob_start();
?>
<div class="frs-biolink-page" id="frs-biolink-page-<?php echo esc_attr($user_id); ?>">

    <!-- Header Section -->
    <div class="frs-biolink-header" style="position: relative; padding: 40px 0; text-align: center; color: white; overflow: hidden; min-height: 400px; background: <?php echo $bg_color; ?>; font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <!-- Video Background -->
        <video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;" autoplay muted loop playsinline>
            <source src="<?php echo esc_url($bg_video_url); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>

        <!-- Content -->
        <div style="position: relative; z-index: 3; max-width: 570px; margin: 0 auto; padding: 10px 20px 0 20px; box-sizing: border-box;">
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
    <div class="frs-biolink-social" style="padding: 20px; text-align: center; background: white;">
        <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="mailto:<?php echo esc_attr($email); ?>" style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: #4a90e2; color: white; border-radius: 50%; text-decoration: none; font-size: 24px; transition: transform 0.2s;">📧</a>
            <a href="#" style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: #3b5998; color: white; border-radius: 50%; text-decoration: none; font-size: 24px; transition: transform 0.2s;">📘</a>
            <a href="#" style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: #e4405f; color: white; border-radius: 50%; text-decoration: none; font-size: 24px; transition: transform 0.2s;">📷</a>
            <a href="#" style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: #0077b5; color: white; border-radius: 50%; text-decoration: none; font-size: 24px; transition: transform 0.2s;">💼</a>
        </div>
    </div>

    <!-- Action Buttons Section -->
    <div class="frs-biolink-buttons" style="padding: 20px; background: white; max-width: 400px; margin: 0 auto;">

        <!-- Call Me Now Button -->
        <?php if ($phone_url): ?>
        <a href="<?php echo esc_url($phone_url); ?>" class="frs-biolink-button" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 20px; margin-bottom: 15px; background: #ffffff; color: #000000; border: 1px solid #ddd; border-radius: 25px; text-decoration: none; font-weight: 500; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <span style="font-size: 20px;">📞</span>
            <?php _e('Call Me Now', 'lending-resource-hub'); ?>
        </a>
        <?php endif; ?>

        <!-- Schedule Appointment Button -->
        <div class="frs-biolink-button-with-form" style="margin-bottom: 15px;">
            <button onclick="showForm('appointment')" class="frs-biolink-button" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 20px; background: #ffffff; color: #000000; border: 1px solid #ddd; border-radius: 25px; font-weight: 500; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <span style="font-size: 20px;">📅</span>
                <?php _e('Schedule Appointment', 'lending-resource-hub'); ?>
            </button>

            <!-- Hidden Form Container -->
            <div id="form-appointment" class="frs-hidden-form" style="display: none; margin-top: 15px; padding: 20px; background: #f9f9f9; border-radius: 10px;">
                <?php echo do_shortcode('[fluentform id="7"]'); ?>
            </div>
        </div>

        <!-- Get Pre-Approved Button -->
        <?php if ($arrive_link): ?>
        <a href="<?php echo esc_url($arrive_link); ?>" target="_blank" class="frs-biolink-button frs-primary" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 20px; margin-bottom: 15px; background: #1e3a8a; color: #ffffff; border: none; border-radius: 25px; text-decoration: none; font-weight: 500; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <span style="font-size: 20px;">✅</span>
            <?php _e('Get Pre-Approved', 'lending-resource-hub'); ?>
        </a>
        <?php else: ?>
        <div class="frs-biolink-button" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 20px; margin-bottom: 15px; background: #f5f5f5; color: #999; border: 1px solid #ddd; border-radius: 25px; font-weight: 500;">
            <span style="font-size: 20px;">✅</span>
            <?php _e('Get Pre-Approved', 'lending-resource-hub'); ?>
        </div>
        <?php endif; ?>

        <!-- Free Rate Quote Button -->
        <div class="frs-biolink-button-with-form" style="margin-bottom: 15px;">
            <button onclick="showForm('rate_quote')" class="frs-biolink-button" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 20px; background: #ffffff; color: #000000; border: 1px solid #ddd; border-radius: 25px; font-weight: 500; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <span style="font-size: 20px;">🧮</span>
                <?php _e('Free Rate Quote', 'lending-resource-hub'); ?>
            </button>

            <!-- Hidden Form Container -->
            <div id="form-rate_quote" class="frs-hidden-form" style="display: none; margin-top: 15px; padding: 20px; background: #f9f9f9; border-radius: 10px;">
                <?php echo do_shortcode('[fluentform id="6"]'); ?>
            </div>
        </div>
    </div>

    <!-- Thank You Overlay (Hidden by default) -->
    <div id="frs-thank-you-overlay" class="frs-thank-you-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;">
        <div style="background: white; padding: 40px; border-radius: 10px; text-align: center; max-width: 400px; margin: 20px;">
            <h2 style="margin: 0 0 15px 0; color: #1e3a8a;"><?php _e('Thank You!', 'lending-resource-hub'); ?></h2>
            <p style="margin: 0 0 20px 0; color: #666;"><?php _e('Your submission has been received. I will get back to you within 24 hours.', 'lending-resource-hub'); ?></p>
            <button onclick="hideThankYou()" style="background: #1e3a8a; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;"><?php _e('Close', 'lending-resource-hub'); ?></button>
        </div>
    </div>
</div>

<script>
// Form toggle functions
function showForm(formType) {
    // Hide all forms first
    const allForms = document.querySelectorAll('.frs-hidden-form');
    allForms.forEach(form => form.style.display = 'none');

    // Show the requested form
    const targetForm = document.getElementById('form-' + formType);
    if (targetForm) {
        targetForm.style.display = 'block';
        targetForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function hideThankYou() {
    const overlay = document.getElementById('frs-thank-you-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Listen for form submissions to show thank you message
document.addEventListener('DOMContentLoaded', function() {
    // Listen for FluentForms submissions
    document.addEventListener('frs_lead_captured', function(event) {
        const overlay = document.getElementById('frs-thank-you-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }
    });

    // Also listen for any FluentForms completion events
    jQuery(document).on('fluentform_submission_success', function(event, data) {
        const overlay = document.getElementById('frs-thank-you-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }
    });
});
</script>

<?php
return ob_get_clean();
?>