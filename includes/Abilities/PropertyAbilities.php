<?php
/**
 * Property Abilities
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

namespace LendingResourceHub\Abilities;

use WP_Error;

/**
 * Class PropertyAbilities
 *
 * Registers abilities for property data access via Rentcast API.
 */
class PropertyAbilities {

	/**
	 * Register all property abilities
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_lookup_property();
		self::register_get_property_valuation();
	}

	/**
	 * Register lookup-property ability
	 *
	 * @return void
	 */
	private static function register_lookup_property(): void {
		wp_register_ability(
			'lrh/lookup-property',
			array(
				'label'       => __( 'Lookup Property', 'lending-resource-hub' ),
				'description' => __( 'Looks up property information using Rentcast API by address or coordinates.', 'lending-resource-hub' ),
				'category'    => 'property-data',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'address' => array(
							'type'        => 'string',
							'description' => __( 'Full property address to lookup.', 'lending-resource-hub' ),
						),
						'city' => array(
							'type'        => 'string',
							'description' => __( 'City name.', 'lending-resource-hub' ),
						),
						'state' => array(
							'type'        => 'string',
							'description' => __( 'State code (e.g., CA, NY).', 'lending-resource-hub' ),
						),
						'zipcode' => array(
							'type'        => 'string',
							'description' => __( 'ZIP code.', 'lending-resource-hub' ),
						),
					),
					'required'             => array( 'address' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'success'  => array( 'type' => 'boolean' ),
						'address'  => array( 'type' => 'string' ),
						'city'     => array( 'type' => 'string' ),
						'state'    => array( 'type' => 'string' ),
						'zipcode'  => array( 'type' => 'string' ),
						'property_type' => array( 'type' => 'string' ),
						'bedrooms' => array( 'type' => 'integer' ),
						'bathrooms' => array( 'type' => 'number' ),
						'square_feet' => array( 'type' => 'integer' ),
						'year_built' => array( 'type' => 'integer' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_lookup_property' ),
				'permission_callback' => function() {
					return current_user_can( 'read' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => true,
						'idempotent' => true,
						'instructions' => 'Requires Rentcast API key to be configured in plugin settings.',
					),
				),
			)
		);
	}

	/**
	 * Execute lookup-property ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Property data or error.
	 */
	public static function execute_lookup_property( array $input ) {
		// Check if Rentcast API is configured
		$api_key = get_option( 'lrh_rentcast_api_key' );
		if ( empty( $api_key ) ) {
			return new WP_Error(
				'rentcast_not_configured',
				__( 'Rentcast API key is not configured.', 'lending-resource-hub' ),
				array( 'status' => 500 )
			);
		}

		// Build API request
		$address = sanitize_text_field( $input['address'] );
		$city = isset( $input['city'] ) ? sanitize_text_field( $input['city'] ) : '';
		$state = isset( $input['state'] ) ? sanitize_text_field( $input['state'] ) : '';
		$zipcode = isset( $input['zipcode'] ) ? sanitize_text_field( $input['zipcode'] ) : '';

		$api_url = 'https://api.rentcast.io/v1/properties';
		$query_params = array(
			'address' => $address,
		);

		if ( $city ) {
			$query_params['city'] = $city;
		}
		if ( $state ) {
			$query_params['state'] = $state;
		}
		if ( $zipcode ) {
			$query_params['zipCode'] = $zipcode;
		}

		$api_url = add_query_arg( $query_params, $api_url );

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'X-Api-Key' => $api_key,
					'Accept'    => 'application/json',
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'rentcast_request_failed',
				$response->get_error_message(),
				array( 'status' => 500 )
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error(
				'rentcast_api_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Rentcast API returned status code %d', 'lending-resource-hub' ),
					$status_code
				),
				array( 'status' => $status_code )
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data ) || ! is_array( $data ) ) {
			return new WP_Error(
				'rentcast_no_data',
				__( 'No property data found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		// Extract first result
		$property = is_array( $data ) && isset( $data[0] ) ? $data[0] : $data;

		return array(
			'success'       => true,
			'address'       => $property['address'] ?? $address,
			'city'          => $property['city'] ?? $city,
			'state'         => $property['state'] ?? $state,
			'zipcode'       => $property['zipCode'] ?? $zipcode,
			'property_type' => $property['propertyType'] ?? '',
			'bedrooms'      => isset( $property['bedrooms'] ) ? absint( $property['bedrooms'] ) : 0,
			'bathrooms'     => isset( $property['bathrooms'] ) ? floatval( $property['bathrooms'] ) : 0,
			'square_feet'   => isset( $property['squareFootage'] ) ? absint( $property['squareFootage'] ) : 0,
			'year_built'    => isset( $property['yearBuilt'] ) ? absint( $property['yearBuilt'] ) : 0,
		);
	}

	/**
	 * Register get-property-valuation ability
	 *
	 * @return void
	 */
	private static function register_get_property_valuation(): void {
		wp_register_ability(
			'lrh/get-property-valuation',
			array(
				'label'       => __( 'Get Property Valuation', 'lending-resource-hub' ),
				'description' => __( 'Gets estimated property value and market data using Rentcast API.', 'lending-resource-hub' ),
				'category'    => 'property-data',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'address' => array(
							'type'        => 'string',
							'description' => __( 'Full property address to value.', 'lending-resource-hub' ),
						),
						'city' => array(
							'type'        => 'string',
							'description' => __( 'City name.', 'lending-resource-hub' ),
						),
						'state' => array(
							'type'        => 'string',
							'description' => __( 'State code (e.g., CA, NY).', 'lending-resource-hub' ),
						),
						'zipcode' => array(
							'type'        => 'string',
							'description' => __( 'ZIP code.', 'lending-resource-hub' ),
						),
					),
					'required'             => array( 'address' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'success'   => array( 'type' => 'boolean' ),
						'address'   => array( 'type' => 'string' ),
						'price'     => array( 'type' => 'number' ),
						'price_low' => array( 'type' => 'number' ),
						'price_high' => array( 'type' => 'number' ),
						'rent_estimate' => array( 'type' => 'number' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_property_valuation' ),
				'permission_callback' => function() {
					return current_user_can( 'read' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => true,
						'idempotent' => true,
						'instructions' => 'Requires Rentcast API key to be configured in plugin settings.',
					),
				),
			)
		);
	}

	/**
	 * Execute get-property-valuation ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Valuation data or error.
	 */
	public static function execute_get_property_valuation( array $input ) {
		// Check if Rentcast API is configured
		$api_key = get_option( 'lrh_rentcast_api_key' );
		if ( empty( $api_key ) ) {
			return new WP_Error(
				'rentcast_not_configured',
				__( 'Rentcast API key is not configured.', 'lending-resource-hub' ),
				array( 'status' => 500 )
			);
		}

		// Build API request
		$address = sanitize_text_field( $input['address'] );
		$city = isset( $input['city'] ) ? sanitize_text_field( $input['city'] ) : '';
		$state = isset( $input['state'] ) ? sanitize_text_field( $input['state'] ) : '';
		$zipcode = isset( $input['zipcode'] ) ? sanitize_text_field( $input['zipcode'] ) : '';

		$api_url = 'https://api.rentcast.io/v1/avm/value';
		$query_params = array(
			'address' => $address,
		);

		if ( $city ) {
			$query_params['city'] = $city;
		}
		if ( $state ) {
			$query_params['state'] = $state;
		}
		if ( $zipcode ) {
			$query_params['zipCode'] = $zipcode;
		}

		$api_url = add_query_arg( $query_params, $api_url );

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'X-Api-Key' => $api_key,
					'Accept'    => 'application/json',
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'rentcast_request_failed',
				$response->get_error_message(),
				array( 'status' => 500 )
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error(
				'rentcast_api_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Rentcast API returned status code %d', 'lending-resource-hub' ),
					$status_code
				),
				array( 'status' => $status_code )
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data ) ) {
			return new WP_Error(
				'rentcast_no_data',
				__( 'No valuation data found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		return array(
			'success'       => true,
			'address'       => $data['address'] ?? $address,
			'price'         => isset( $data['price'] ) ? floatval( $data['price'] ) : 0,
			'price_low'     => isset( $data['priceRangeLow'] ) ? floatval( $data['priceRangeLow'] ) : 0,
			'price_high'    => isset( $data['priceRangeHigh'] ) ? floatval( $data['priceRangeHigh'] ) : 0,
			'rent_estimate' => isset( $data['rentEstimate'] ) ? floatval( $data['rentEstimate'] ) : 0,
		);
	}
}
