<?php
/**
 * Prequal Subheading Block Render
 * This block saves to post meta for use in the template
 */

// Get the saved meta value
$post_id = get_the_ID();
$subheading = get_post_meta($post_id, '_frs_prequal_subheading', true) ?: 'Get pre-qualified in minutes with our streamlined process.';

// Only show in editor as a preview
if (defined('REST_REQUEST') && REST_REQUEST) {
    ?>
    <div class="frs-prequal-subheading-preview" style="
        padding: 15px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 2px solid #cbd5e0;
        border-radius: 8px;
        text-align: center;
    ">
        <p style="font-size: 16px; color: #4a5568; margin: 0; line-height: 1.5;">
            <?php echo esc_html($subheading); ?>
        </p>
    </div>
    <?php
} else {
    // On frontend, don't display anything - the template will use the meta value
    echo '<!-- Prequal subheading meta saved: ' . esc_html($subheading) . ' -->';
}
?>