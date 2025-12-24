<?php
/**
 * Rentcast Controller
 *
 * Handles Rentcast API proxy endpoints.
 *
 * @package LendingResourceHub\Controllers\Rentcast
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Rentcast;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles Rentcast API-related actions.
 *
 * @package LendingResourceHub\Controllers\Rentcast
 */
class Actions {

	/**
	 * Rentcast API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Rentcast API base URL.
	 *
	 * @var string
	 */
	private $api_base = 'https://api.rentcast.io/v1';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Get API key from WordPress options or environment variable
		$this->api_key = defined( 'RENTCAST_API_KEY' )
			? RENTCAST_API_KEY
			: get_option( 'frs_rentcast_api_key', '' );
	}

	/**
	 * Get property valuation from Rentcast API.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function get_valuation( WP_REST_Request $request ) {
		$params         = $request->get_params();
		$valuation_type = $params['valuationType'] ?? 'value';

		// Call appropriate method based on valuation type
		if ( $valuation_type === 'rent' ) {
			$result = $this->get_rent_estimate( $params );
		} else {
			$result = $this->get_value_estimate( $params );
		}

		// Handle errors
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return successful response
		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Get property value estimate.
	 *
	 * @param array $params Property parameters.
	 * @return array|WP_Error Response data or error.
	 */
	private function get_value_estimate( array $params ) {
		if ( empty( $params['address'] ) ) {
			return new WP_Error(
				'missing_address',
				'Property address is required',
				array( 'status' => 400 )
			);
		}

		$query_params = array(
			'address' => sanitize_text_field( $params['address'] ),
		);

		if ( ! empty( $params['city'] ) ) {
			$query_params['city'] = sanitize_text_field( $params['city'] );
		}

		if ( ! empty( $params['state'] ) ) {
			$query_params['state'] = sanitize_text_field( $params['state'] );
		}

		if ( ! empty( $params['zipCode'] ) ) {
			$query_params['zipCode'] = sanitize_text_field( $params['zipCode'] );
		}

		$url = add_query_arg(
			$query_params,
			$this->api_base . '/avm/value'
		);

		return $this->make_request( $url );
	}

	/**
	 * Get property rent estimate.
	 *
	 * @param array $params Property parameters.
	 * @return array|WP_Error Response data or error.
	 */
	private function get_rent_estimate( array $params ) {
		if ( empty( $params['address'] ) ) {
			return new WP_Error(
				'missing_address',
				'Property address is required',
				array( 'status' => 400 )
			);
		}

		$query_params = array(
			'address' => sanitize_text_field( $params['address'] ),
		);

		if ( ! empty( $params['city'] ) ) {
			$query_params['city'] = sanitize_text_field( $params['city'] );
		}

		if ( ! empty( $params['state'] ) ) {
			$query_params['state'] = sanitize_text_field( $params['state'] );
		}

		if ( ! empty( $params['zipCode'] ) ) {
			$query_params['zipCode'] = sanitize_text_field( $params['zipCode'] );
		}

		$url = add_query_arg(
			$query_params,
			$this->api_base . '/avm/rent/long-term'
		);

		return $this->make_request( $url );
	}

	/**
	 * Make HTTP request to Rentcast API.
	 *
	 * @param string $url API endpoint URL.
	 * @return array|WP_Error Response data or error.
	 */
	private function make_request( string $url ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error(
				'missing_api_key',
				'Rentcast API key is not configured',
				array( 'status' => 500 )
			);
		}

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'X-Api-Key' => $this->api_key,
					'Accept'    => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$data        = json_decode( $body, true );

		if ( $status_code !== 200 ) {
			$error_message = isset( $data['message'] )
				? $data['message']
				: 'Rentcast API request failed';

			return new WP_Error(
				'rentcast_api_error',
				$error_message,
				array( 'status' => $status_code )
			);
		}

		return $data;
	}
}
