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
$bg_video_url = LRH_URL . 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4';
$company_logo_url = LRH_URL . 'assets/images/21C-Wordmark-White.svg';

// Phone URL for call button
$phone_url = !empty($phone) ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';

ob_start();
?>
<div class="frs-biolink-page" id="frs-biolink-page-<?php echo esc_attr($user_id); ?>">

    <!-- Header Section -->
    <div class="frs-biolink-header">
        <!-- Video Background -->
        <video autoplay muted loop playsinline>
            <source src="<?php echo esc_url($bg_video_url); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>

        <!-- Content -->
        <div>
            <?php if ($company_logo_url): ?>
                <img src="<?php echo esc_url($company_logo_url); ?>" alt="21st Century Lending">
            <?php endif; ?>

            <?php if ($logo_url): ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($name); ?>">
            <?php endif; ?>

            <?php if ($name): ?>
                <h1><?php echo esc_html($name); ?></h1>
            <?php endif; ?>

            <?php if ($title): ?>
                <p><?php echo esc_html($title); ?></p>
            <?php endif; ?>

            <?php if ($company): ?>
                <p><?php echo esc_html($company); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Social Media Section -->
    <div class="frs-biolink-social">
        <div>
            <a href="mailto:<?php echo esc_attr($email); ?>">📧</a>
            <a href="#">📘</a>
            <a href="#">📷</a>
            <a href="#">💼</a>
        </div>
    </div>

    <!-- Action Buttons Section -->
    <div class="frs-biolink-buttons">

        <!-- Call Me Now Button -->
        <?php if ($phone_url): ?>
        <a href="<?php echo esc_url($phone_url); ?>" class="frs-biolink-button">
            <span>📞</span>
            <?php _e('Call Me Now', 'lending-resource-hub'); ?>
        </a>
        <?php endif; ?>

        <!-- Schedule Appointment Button -->
        <div class="frs-biolink-button-with-form">
            <button onclick="showForm('appointment')" class="frs-biolink-button">
                <span>📅</span>
                <?php _e('Schedule Appointment', 'lending-resource-hub'); ?>
            </button>

            <!-- Hidden Form Container -->
            <div id="form-appointment" class="frs-hidden-form">
                <?php echo do_shortcode('[fluentform id="7"]'); ?>
            </div>
        </div>

        <!-- Get Pre-Approved Button -->
        <?php if ($arrive_link): ?>
        <a href="<?php echo esc_url($arrive_link); ?>" target="_blank" class="frs-biolink-button frs-primary">
            <span>✅</span>
            <?php _e('Get Pre-Approved', 'lending-resource-hub'); ?>
        </a>
        <?php else: ?>
        <div class="frs-biolink-button">
            <span>✅</span>
            <?php _e('Get Pre-Approved', 'lending-resource-hub'); ?>
        </div>
        <?php endif; ?>

        <!-- Free Rate Quote Button -->
        <div class="frs-biolink-button-with-form">
            <button onclick="showForm('rate_quote')" class="frs-biolink-button">
                <span>🧮</span>
                <?php _e('Free Rate Quote', 'lending-resource-hub'); ?>
            </button>

            <!-- Hidden Form Container -->
            <div id="form-rate_quote" class="frs-hidden-form">
                <?php echo do_shortcode('[fluentform id="6"]'); ?>
            </div>
        </div>
    </div>

    <!-- Thank You Overlay (Hidden by default) -->
    <div id="frs-thank-you-overlay" class="frs-thank-you-overlay">
        <div>
            <h2><?php _e('Thank You!', 'lending-resource-hub'); ?></h2>
            <p><?php _e('Your submission has been received. I will get back to you within 24 hours.', 'lending-resource-hub'); ?></p>
            <button onclick="hideThankYou()"><?php _e('Close', 'lending-resource-hub'); ?></button>
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