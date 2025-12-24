<?php
/**
 * Block Bindings Registration
 *
 * Registers custom block bindings sources to bind block attributes
 * to dynamic user profile data from FRS Users plugin.
 *
 * @package LendingResourceHub\Core
 * @since 1.0.0
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Class BlockBindings
 *
 * Handles registration of custom block bindings sources.
 *
 * @package LendingResourceHub\Core
 */
class BlockBindings {

	use Base;

	/**
	 * Initialize block bindings.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_bindings_sources' ) );
		// WordPress doesn't automatically apply content bindings on frontend for custom sources
		// We need to manually process them via render_block filter
		add_filter( 'render_block', array( $this, 'apply_bindings_to_block' ), 10, 2 );
	}

	/**
	 * Register custom block bindings sources.
	 *
	 * @return void
	 */
	public function register_bindings_sources() {
		// User Profile bindings - pulls from FRSUsers\Models\Profile
		register_block_bindings_source(
			'frs-lrg/user-profile',
			array(
				'label'              => __( 'User Profile Data', 'lending-resource-hub' ),
				'get_value_callback' => array( $this, 'get_profile_value' ),
				'uses_context'       => array( 'postId', 'postType' ),
			)
		);
	}

	/**
	 * Get value from user profile for block binding.
	 *
	 * @param array     $source_args Binding source arguments with 'key' parameter.
	 * @param WP_Block  $block_instance Block instance.
	 * @param string    $attribute_name Attribute name being bound.
	 * @return string|int|null Value to bind.
	 */
	public function get_profile_value( $source_args, $block_instance, $attribute_name ) {
		// Get post ID - try block context first, then global
		$post_id = $block_instance->context['postId'] ?? get_the_ID();
		if ( ! $post_id ) {
			return null;
		}

		// Get user ID from post author
		$user_id = get_post_field( 'post_author', $post_id );
		if ( ! $user_id ) {
			return null;
		}

		// Get profile from Eloquent model
		if ( ! class_exists( 'FRSUsers\\Models\\Profile' ) ) {
			return null;
		}

		$profile = \FRSUsers\Models\Profile::where( 'user_id', $user_id )->first();
		if ( ! $profile ) {
			return null;
		}

		// Get the requested key
		$key = $source_args['key'] ?? '';
		if ( empty( $key ) ) {
			return null;
		}

		// Return value based on key
		return $this->get_value_by_key( $profile, $user_id, $key );
	}

	/**
	 * Apply bindings to blocks at render time.
	 *
	 * Intercepts block rendering and replaces content based on metadata bindings.
	 *
	 * @param string $block_content Block HTML content.
	 * @param array  $block Block data including attributes and metadata.
	 * @return string Modified block content.
	 */
	public function apply_bindings_to_block( $block_content, $block ) {
		// Only process blocks with bindings metadata
		if ( empty( $block['attrs']['metadata']['bindings'] ) ) {
			return $block_content;
		}

		$bindings = $block['attrs']['metadata']['bindings'];

		// Only process our binding source
		foreach ( $bindings as $attribute => $binding ) {
			if ( empty( $binding['source'] ) || 'frs-lrg/user-profile' !== $binding['source'] ) {
				continue;
			}

			$key = $binding['args']['key'] ?? '';
			if ( empty( $key ) ) {
				continue;
			}

			// Get post ID and user ID
			$post_id = get_the_ID();
			if ( ! $post_id ) {
				continue;
			}

			$user_id = get_post_field( 'post_author', $post_id );
			if ( ! $user_id ) {
				continue;
			}

			// Get profile
			if ( ! class_exists( 'FRSUsers\\Models\\Profile' ) ) {
				continue;
			}

			$profile = \FRSUsers\Models\Profile::where( 'user_id', $user_id )->first();
			if ( ! $profile ) {
				continue;
			}

			// Get replacement value
			$value = $this->get_value_by_key( $profile, $user_id, $key );
			if ( null === $value ) {
				continue;
			}

			// Apply replacement based on attribute type and block type
			$block_content = $this->replace_block_content( $block_content, $attribute, $value, $block );
		}

		return $block_content;
	}

	/**
	 * Get value by key from profile.
	 *
	 * @param object $profile Profile model instance.
	 * @param int    $user_id User ID.
	 * @param string $key Data key.
	 * @return mixed Value or null.
	 */
	private function get_value_by_key( $profile, $user_id, $key ) {
		switch ( $key ) {
			case 'full_name':
				return trim( $profile->first_name . ' ' . $profile->last_name );
			case 'first_name':
				return $profile->first_name;
			case 'last_name':
				return $profile->last_name;
			case 'full_name_slug':
				return sanitize_title( trim( $profile->first_name . ' ' . $profile->last_name ) );
			case 'job_title':
				return $profile->job_title ?: 'Loan Originator';
			case 'nmls_display':
				$nmls = $profile->nmls ?: $profile->nmls_number;
				return $nmls ? 'NMLS ' . ( strpos( $nmls, '#' ) === 0 ? $nmls : '#' . $nmls ) : '';
			case 'phone_formatted':
				$phone = $profile->phone_number ?: $profile->mobile_number;
				if ( ! $phone ) {
					return '';
				}
				$digits = preg_replace( '/\D/', '', $phone );
				return preg_replace( '/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $digits );
			case 'phone_link':
				$phone = $profile->phone_number ?: $profile->mobile_number;
				return $phone ? 'tel:' . preg_replace( '/\D/', '', $phone ) : '';
			case 'headshot_url':
				if ( $profile->headshot_id ) {
					$url = wp_get_attachment_url( $profile->headshot_id );
					if ( $url ) {
						return $url;
					}
				}
				return get_avatar_url( $user_id, array( 'size' => 500 ) );
			case 'headshot_id':
				return $profile->headshot_id ?: 0;
			case 'contact_button_text':
				return 'Contact ' . $profile->first_name . ' Today';
			default:
				return isset( $profile->$key ) ? $profile->$key : null;
		}
	}

	/**
	 * Replace content in block HTML.
	 *
	 * @param string $content Block HTML content.
	 * @param string $attribute Attribute name being replaced.
	 * @param mixed  $value Replacement value.
	 * @param array  $block Block data.
	 * @return string Modified content.
	 */
	private function replace_block_content( $content, $attribute, $value, $block ) {
		$block_name = $block['blockName'] ?? '';

		switch ( $attribute ) {
			case 'content':
				// For paragraphs and headings, replace inner content
				if ( in_array( $block_name, array( 'core/paragraph', 'core/heading' ), true ) ) {
					// Replace content between tags, preserving HTML structure
					$content = preg_replace( '/(<(?:p|h[1-6])[^>]*>).*?(<\/(?:p|h[1-6])>)/s', '$1' . esc_html( $value ) . '$2', $content );
				}
				break;

			case 'url':
				// For images and links, replace src/href attributes
				if ( 'core/image' === $block_name ) {
					$content = preg_replace( '/(<img[^>]*\s)src="[^"]*"/', '$1src="' . esc_url( $value ) . '"', $content );
				}
				if ( 'core/button' === $block_name ) {
					$content = preg_replace( '/(<a[^>]*\s)href="[^"]*"/', '$1href="' . esc_url( $value ) . '"', $content );
				}
				break;

			case 'id':
				// For images, replace class and data attributes
				if ( 'core/image' === $block_name && is_numeric( $value ) ) {
					$content = preg_replace( '/wp-image-\d+/', 'wp-image-' . intval( $value ), $content );
				}
				break;

			case 'text':
				// For buttons, replace link text
				if ( 'core/button' === $block_name ) {
					$content = preg_replace( '/(<a[^>]*>)[^<]*(<\/a>)/', '$1' . esc_html( $value ) . '$2', $content );
				}
				break;
		}

		return $content;
	}
}
