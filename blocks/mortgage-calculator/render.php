<?php
/**
 * Server-side rendering for mortgage calculator block
 *
 * @param array $attributes Block attributes
 * @param string $content Block content
 * @return string Rendered HTML
 */

// Get current user/loan officer ID
$current_user = wp_get_current_user();
$loan_officer_id = 0;

// Try to get loan officer from post author if this is a landing page
if (is_singular()) {
	$post = get_post();
	if ($post) {
		$loan_officer_id = $post->post_author;
	}
}

// Fallback to current user if logged in
if ($loan_officer_id === 0 && is_user_logged_in()) {
	$loan_officer_id = $current_user->ID;
}

// Prepare attributes for JavaScript
$attributes_json = wp_json_encode($attributes);

// Add configuration to footer
add_action('wp_footer', function() use ($loan_officer_id) {
	?>
	<script>
		window.frsCalculatorConfig = window.frsCalculatorConfig || {};
		window.frsCalculatorConfig.loanOfficerId = <?php echo intval($loan_officer_id); ?>;
		window.frsCalculatorConfig.restNonce = "<?php echo esc_js(wp_create_nonce('wp_rest')); ?>";
	</script>
	<?php
}, 5);

// Wrapper classes
$wrapper_attributes = get_block_wrapper_attributes([
	'class' => 'frs-mortgage-calculator-block',
	'data-attributes' => esc_attr($attributes_json)
]);

// Render block container
// React will mount here via view.js
return sprintf(
	'<div %s></div>',
	$wrapper_attributes
);
