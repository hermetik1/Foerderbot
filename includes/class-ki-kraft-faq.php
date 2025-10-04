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
			return new WP_Error( 'missing_query', __( 'Query parameter is required.', KRAFT_AI_CHAT_TEXTDOMAIN ), array( 'status' => 400 ) );
		}

		// Log analytics
		self::log_query( $query, false );

		// Search knowledge base
		$results = self::search_knowledge( $query, 'public', 5 );

		if ( empty( $results ) ) {
			// No results found - log as unanswered
			self::log_query( $query, false, false );
			
			return rest_ensure_response(
				array(
					'answer'  => __( 'I could not find an answer to your question. Would you like to contact our support team?', KRAFT_AI_CHAT_TEXTDOMAIN ),
					'sources' => array(),
					'confidence' => 0,
					'fallback' => true,
				)
			);
		}

		// Get best result
		$best_result = $results[0];
		
		// Log as answered
		self::log_query( $query, true, true );

		return rest_ensure_response(
			array(
				'answer'     => $best_result['content'],
				'sources'    => array_map(
					function ( $r ) {
						return array(
							'title' => $r['title'],
							'score' => $r['score'],
						);
					},
					array_slice( $results, 0, 3 )
				),
				'confidence' => $best_result['score'],
			)
		);
	}

	/**
	 * Search knowledge base.
	 *
	 * @param string $query Query string.
	 * @param string $scope Scope filter.
	 * @param int    $limit Result limit.
	 * @return array
	 */
	private static function search_knowledge( $query, $scope = 'public', $limit = 5 ) {
		global $wpdb;

		// Fulltext search
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title, content, 
				MATCH(title, content) AGAINST (%s IN NATURAL LANGUAGE MODE) as score
				FROM {$wpdb->prefix}ki_kraft_knowledge
				WHERE scope = %s
				AND MATCH(title, content) AGAINST (%s IN NATURAL LANGUAGE MODE)
				ORDER BY score DESC
				LIMIT %d",
				$query,
				$scope,
				$query,
				$limit
			),
			ARRAY_A
		);

		return $results ? $results : array();
	}

	/**
	 * Log query for analytics.
	 *
	 * @param string $query Query string.
	 * @param bool   $answered Whether query was answered.
	 * @param int    $feedback User feedback (1/-1).
	 */
	private static function log_query( $query, $answered, $feedback = null ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'ki_kraft_analytics',
			array(
				'query'      => $query,
				'query_hash' => hash( 'sha256', strtolower( trim( $query ) ) ),
				'answered'   => $answered ? 1 : 0,
				'feedback'   => $feedback,
				'latency'    => 0,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%d', '%d', '%s' )
		);
	}

	/**
	 * Add FAQ entry.
	 *
	 * @param string $title Title.
	 * @param string $content Content.
	 * @param string $scope Scope.
	 * @return int|false
	 */
	public static function add_entry( $title, $content, $scope = 'public' ) {
		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'ki_kraft_knowledge',
			array(
				'title'      => $title,
				'content'    => $content,
				'scope'      => $scope,
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}
}
