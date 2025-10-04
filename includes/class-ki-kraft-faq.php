<?php
/**
 * FAQ Bot handler.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles FAQ bot queries for guests.
 */
class KI_Kraft_FAQ {

	/**
	 * Handle FAQ query.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public static function handle_query( $request ) {
		$query = $request->get_param( 'query' );

		if ( empty( $query ) ) {
			return new WP_Error( 'missing_query', __( 'Query parameter is required.', 'ki-kraft' ), array( 'status' => 400 ) );
		}

		// TODO: Implement fuzzy search and semantic vector search
		// TODO: Calculate cosine similarity
		// TODO: Return results with source badges

		return rest_ensure_response(
			array(
				'answer'  => __( 'This is a placeholder response.', 'ki-kraft' ),
				'sources' => array(),
			)
		);
	}
}
