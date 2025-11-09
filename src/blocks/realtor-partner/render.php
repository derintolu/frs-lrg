<?php
/**
 * Realtor Partner Block - Dynamic Render
 *
 * @package FRS_Partnership_Portal
 */

// Get the post ID and realtor partner ID from meta
$post_id = get_the_ID();
$partner_id = get_post_meta($post_id, '_frs_partner_user_id', true);

// Get realtor partner data
$realtor = $partner_id ? get_user_by('id', $partner_id) : null;

// Extract user data with fallbacks
$name = $realtor ? $realtor->display_name : 'Realtor Partner';
$job_title = $partner_id ? get_user_meta($partner_id, 'job_title', true) : '';
$company = $partner_id ? get_user_meta($partner_id, 'company', true) : '';
$phone = $partner_id ? get_user_meta($partner_id, 'phone', true) : '';
$email = $realtor ? $realtor->user_email : '';
$bio = $partner_id ? get_user_meta($partner_id, 'description', true) : '';

// Get avatar URL
$avatar_url = $partner_id ? get_avatar_url($partner_id, ['size' => 250]) : '';

// Get responsive sizes from attributes
$avatar_sizes = $attributes['avatarSizes'] ?? [
    'mobile' => 'clamp(120px, 30vw, 180px)',
    'tablet' => 'clamp(180px, 25vw, 240px)',
    'desktop' => 'clamp(160px, 15vw, 220px)'
];

$name_sizes = $attributes['nameSizes'] ?? [
    'mobile' => 'clamp(1.2rem, 4vw, 1.8rem)',
    'tablet' => 'clamp(1.4rem, 3.5vw, 2rem)',
    'desktop' => 'clamp(1.3rem, 2vw, 1.8rem)'
];

$title_sizes = $attributes['titleSizes'] ?? [
    'mobile' => 'clamp(0.9rem, 3vw, 1.2rem)',
    'tablet' => 'clamp(1rem, 2.5vw, 1.4rem)',
    'desktop' => 'clamp(1.1rem, 2vw, 1.6rem)'
];

// Create a unique ID for this block instance
$block_id = 'frs-realtor-partner-' . uniqid();
?>

<div id="<?php echo esc_attr($block_id); ?>" class="frs-realtor-partner-block">
    <style>
        #<?php echo esc_attr($block_id); ?> {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1.5rem;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-avatar {
            width: <?php echo esc_attr($avatar_sizes['mobile']); ?>;
            height: <?php echo esc_attr($avatar_sizes['mobile']); ?>;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 1rem;
            border: 3px solid #0a6ff9;
            background: #f0f0f0;
            position: relative;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0a6ff9, #00a1ff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-name {
            font-size: <?php echo esc_attr($name_sizes['mobile']); ?>;
            font-weight: bold;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #00f2ff, #0a6ff9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-title {
            font-size: <?php echo esc_attr($title_sizes['mobile']); ?>;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-company {
            font-size: 0.95rem;
            color: #888;
            margin-top: 0.25rem;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-contact {
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-contact a {
            color: #0a6ff9;
            text-decoration: none;
            display: block;
            margin: 0.25rem 0;
        }

        #<?php echo esc_attr($block_id); ?> .frs-rp-contact a:hover {
            text-decoration: underline;
        }

        /* Tablet styles */
        @media (min-width: 768px) {
            #<?php echo esc_attr($block_id); ?> .frs-rp-avatar {
                width: <?php echo esc_attr($avatar_sizes['tablet']); ?>;
                height: <?php echo esc_attr($avatar_sizes['tablet']); ?>;
            }

            #<?php echo esc_attr($block_id); ?> .frs-rp-name {
                font-size: <?php echo esc_attr($name_sizes['tablet']); ?>;
            }

            #<?php echo esc_attr($block_id); ?> .frs-rp-title {
                font-size: <?php echo esc_attr($title_sizes['tablet']); ?>;
            }
        }

        /* Desktop styles */
        @media (min-width: 1024px) {
            #<?php echo esc_attr($block_id); ?> .frs-rp-avatar {
                width: <?php echo esc_attr($avatar_sizes['desktop']); ?>;
                height: <?php echo esc_attr($avatar_sizes['desktop']); ?>;
            }

            #<?php echo esc_attr($block_id); ?> .frs-rp-name {
                font-size: <?php echo esc_attr($name_sizes['desktop']); ?>;
            }

            #<?php echo esc_attr($block_id); ?> .frs-rp-title {
                font-size: <?php echo esc_attr($title_sizes['desktop']); ?>;
            }
        }
    </style>

    <div class="frs-rp-avatar">
        <?php if ($avatar_url): ?>
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($name); ?>" />
        <?php else: ?>
            <div class="frs-rp-avatar-placeholder">
                <?php echo esc_html(substr($name, 0, 1)); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="frs-rp-info">
        <h3 class="frs-rp-name"><?php echo esc_html($name); ?></h3>

        <div class="frs-rp-title">
            <?php echo esc_html($job_title ?: 'REALTOR®'); ?>
        </div>

        <?php if ($company): ?>
            <div class="frs-rp-company"><?php echo esc_html($company); ?></div>
        <?php endif; ?>

        <?php if ($phone || $email): ?>
            <div class="frs-rp-contact">
                <?php if ($phone): ?>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>">
                        <?php echo esc_html($phone); ?>
                    </a>
                <?php endif; ?>

                <?php if ($email): ?>
                    <a href="mailto:<?php echo esc_attr($email); ?>">
                        <?php echo esc_html($email); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($bio): ?>
            <div class="frs-rp-bio" style="margin-top: 1rem; font-size: 0.9rem; color: #666; max-width: 500px;">
                <?php echo wp_kses_post($bio); ?>
            </div>
        <?php endif; ?>
    </div>
</div>