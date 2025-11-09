<?php
/**
 * Prequal Main Heading Block Render
 * This block saves to post meta for use in the template
 */

// Get the saved meta values
$post_id = get_the_ID();
$line1 = get_post_meta($post_id, '_frs_prequal_heading_line1', true) ?: 'One Team. One Goal.';
$line2 = get_post_meta($post_id, '_frs_prequal_heading_line2', true) ?: 'From Approval to Close.';

// Only show in editor as a preview
if (defined('REST_REQUEST') && REST_REQUEST) {
    ?>
    <div class="frs-prequal-heading-preview" style="
        padding: 20px;
        background: linear-gradient(135deg, #1a365d 0%, #2b77c9 100%);
        border-radius: 8px;
        text-align: center;
        color: white;
    ">
        <p style="font-size: 24px; font-weight: bold; margin-bottom: 10px;">
            <?php echo esc_html($line1); ?>
        </p>
        <p style="font-size: 24px; font-weight: bold;">
            <?php echo esc_html($line2); ?>
        </p>
    </div>
    <?php
} else {
    // On frontend, don't display anything - the template will use the meta values
    echo '<!-- Prequal heading meta saved: Line 1: ' . esc_html($line1) . ' | Line 2: ' . esc_html($line2) . ' -->';
}
?>