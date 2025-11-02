<?php
/**
 * Server-side rendering for the biolink-thankyou block
 *
 * @param array    $attributes Block attributes
 * @param string   $content    Block content
 * @param WP_Block $block      Block instance
 * @return string  Block HTML output
 */

// Get the page owner's user ID
global $post;
$user_id = $post->post_author;

// Get user data
$user = get_user_by('id', $user_id);
if (!$user) {
    return '';
}

// Get loan officer's first name
$full_name = $user->display_name;
$first_name = explode(' ', $full_name)[0];

// Get attributes with defaults
$purpose = $attributes['purpose'] ?? 'general';
$custom_title = $attributes['title'] ?? '';
$custom_message = $attributes['message'] ?? '';

// Create personalized messages based on purpose
$messages = array(
    'rate_quote' => array(
        'title' => $custom_title ?: "Thanks for Your Interest!",
        'message' => $custom_message ?: "I've received your rate quote request and will get back to you with personalized rates within the next few hours."
    ),
    'appointment' => array(
        'title' => $custom_title ?: "Appointment Request Received!",
        'message' => $custom_message ?: "Thank you for scheduling time with me. I'll confirm your appointment details shortly and look forward to speaking with you."
    ),
    'prequalification' => array(
        'title' => $custom_title ?: "Pre-qualification Started!",
        'message' => $custom_message ?: "Great! I've received your pre-qualification information. I'll review everything and get back to you with your options within 24 hours."
    ),
    'general' => array(
        'title' => $custom_title ?: "Thank You!",
        'message' => $custom_message ?: "Your submission has been received. I will get back to you within 24 hours."
    )
);

$selected_message = $messages[$purpose] ?? $messages['general'];

// Build the HTML output
$html = '<div class="frs-thankyou-overlay" id="frs-thankyou-' . esc_attr($purpose) . '" style="display: none;">';
$html .= '<div class="frs-thankyou-modal">';
$html .= '<div class="frs-thankyou-content">';
$html .= '<div class="frs-thankyou-header">';
$html .= '<h2>' . esc_html($selected_message['title']) . '</h2>';
$html .= '<button class="frs-thankyou-close" aria-label="Close">&times;</button>';
$html .= '</div>';
$html .= '<div class="frs-thankyou-body">';
$html .= '<div class="frs-thankyou-icon">✓</div>';
$html .= '<p>' . wp_kses_post($selected_message['message']) . '</p>';
$html .= '<p class="frs-thankyou-signature">- ' . esc_html($first_name) . '</p>';
$html .= '</div>';
$html .= '<div class="frs-thankyou-footer">';
$html .= '<button class="frs-thankyou-ok">OK</button>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';

// Add styles
$html .= '<style>
.frs-thankyou-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.frs-thankyou-modal {
    background: white;
    border-radius: 10px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}
.frs-thankyou-content {
    padding: 0;
}
.frs-thankyou-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px 10px 0 0;
    position: relative;
    text-align: center;
}
.frs-thankyou-header h2 {
    margin: 0;
    font-size: 24px;
}
.frs-thankyou-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    opacity: 0.7;
}
.frs-thankyou-close:hover {
    opacity: 1;
}
.frs-thankyou-body {
    padding: 30px;
    text-align: center;
}
.frs-thankyou-icon {
    width: 60px;
    height: 60px;
    background: #4CAF50;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: white;
    margin: 0 auto 20px;
}
.frs-thankyou-body p {
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 15px;
    color: #333;
}
.frs-thankyou-signature {
    font-style: italic;
    color: #666;
    font-size: 14px;
}
.frs-thankyou-footer {
    padding: 20px;
    text-align: center;
    border-top: 1px solid #eee;
}
.frs-thankyou-ok {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-size: 16px;
    cursor: pointer;
    transition: transform 0.2s;
}
.frs-thankyou-ok:hover {
    transform: translateY(-2px);
}
</style>';

// Add JavaScript for functionality
$html .= '<script>
document.addEventListener("DOMContentLoaded", function() {
    const overlay = document.getElementById("frs-thankyou-' . esc_js($purpose) . '");
    if (overlay) {
        const closeBtn = overlay.querySelector(".frs-thankyou-close");
        const okBtn = overlay.querySelector(".frs-thankyou-ok");

        function closeModal() {
            overlay.style.display = "none";
        }

        if (closeBtn) closeBtn.addEventListener("click", closeModal);
        if (okBtn) okBtn.addEventListener("click", closeModal);

        // Close on overlay click
        overlay.addEventListener("click", function(e) {
            if (e.target === overlay) closeModal();
        });

        // Show modal when triggered by form submission
        document.addEventListener("frs_lead_captured", function(e) {
            if (e.detail && e.detail.purpose === "' . esc_js($purpose) . '") {
                overlay.style.display = "flex";
            }
        });
    }
});
</script>';

return $html;