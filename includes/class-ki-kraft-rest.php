<?php
/**
 * REST API handler for the plugin.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API endpoints for KI_Kraft plugin.
 */
class KI_Kraft_REST {

	/**
	 * Register all REST API routes.
	 */
	public static function register_routes() {
		// FAQ routes
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/faq/query',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_FAQ', 'handle_query' ),
				'permission_callback' => '__return_true', // Public endpoint
			)
		);

		// Member routes
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/session',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_Member', 'create_session' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/sessions',
			array(
				'methods'             => 'GET',
				'callback'            => array( 'KI_Kraft_Member', 'get_sessions' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/message',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_Member', 'handle_message' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/upload',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_Indexer', 'handle_upload' ),
				'permission_callback' => array( __CLASS__, 'upload_permission_check' ),
			)
		);

		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/handoff',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_Member', 'handle_handoff' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		// Analytics routes
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/analytics/summary',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_analytics_summary' ),
				'permission_callback' => array( __CLASS__, 'analytics_permission_check' ),
			)
		);
		
		// Knowledge management routes
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/knowledge',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_knowledge_entries' ),
				'permission_callback' => array( __CLASS__, 'manage_knowledge_check' ),
			)
		);
		
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/knowledge',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'add_knowledge_entry' ),
				'permission_callback' => array( __CLASS__, 'manage_knowledge_check' ),
			)
		);
		
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/knowledge/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'delete_knowledge_entry' ),
				'permission_callback' => array( __CLASS__, 'manage_knowledge_check' ),
			)
		);
		
		// Branding routes
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/branding',
			array(
				'methods'             => 'GET',
				'callback'            => array( 'KI_Kraft_Branding', 'get_config' ),
				'permission_callback' => '__return_true',
			)
		);
		
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/branding',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_branding' ),
				'permission_callback' => array( __CLASS__, 'manage_options_check' ),
			)
		);
		
		// Seed data endpoint
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/seed',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'seed_sample_data' ),
				'permission_callback' => array( __CLASS__, 'manage_options_check' ),
			)
		);
	}

	/**
	 * Check if user has member permissions.
	 */
	public static function member_permission_check() {
		return is_user_logged_in();
	}

	/**
	 * Check if user has upload permissions.
	 */
	public static function upload_permission_check() {
		return current_user_can( 'kk_upload_member_docs' );
	}

	/**
	 * Check if user has analytics permissions.
	 */
	public static function analytics_permission_check() {
		return current_user_can( 'kk_view_analytics' );
	}
	
	/**
	 * Check if user can manage knowledge.
	 */
	public static function manage_knowledge_check() {
		return current_user_can( 'kk_manage_knowledge' );
	}
	
	/**
	 * Check if user can manage options.
	 */
	public static function manage_options_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get analytics summary.
	 */
	public static function get_analytics_summary( $request ) {
		global $wpdb;
		
		$days = $request->get_param( 'days' ) ? intval( $request->get_param( 'days' ) ) : 7;
		$date_from = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		
		// Total queries
		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ki_kraft_analytics
				WHERE created_at >= %s",
				$date_from
			)
		);
		
		// Answered vs unanswered
		$answered = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ki_kraft_analytics
				WHERE created_at >= %s AND answered = 1",
				$date_from
			)
		);
		
		// Top queries
		$top_queries = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, COUNT(*) as count
				FROM {$wpdb->prefix}ki_kraft_analytics
				WHERE created_at >= %s AND answered = 1
				GROUP BY query_hash
				ORDER BY count DESC
				LIMIT 10",
				$date_from
			),
			ARRAY_A
		);
		
		// Unanswered queries
		$unanswered = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, COUNT(*) as count
				FROM {$wpdb->prefix}ki_kraft_analytics
				WHERE created_at >= %s AND answered = 0
				GROUP BY query_hash
				ORDER BY count DESC
				LIMIT 10",
				$date_from
			),
			ARRAY_A
		);
		
		// Daily trends
		$trends = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(created_at) as date, COUNT(*) as count
				FROM {$wpdb->prefix}ki_kraft_analytics
				WHERE created_at >= %s
				GROUP BY DATE(created_at)
				ORDER BY date ASC",
				$date_from
			),
			ARRAY_A
		);
		
		return rest_ensure_response(
			array(
				'total'       => intval( $total ),
				'answered'    => intval( $answered ),
				'unanswered'  => intval( $total ) - intval( $answered ),
				'top_queries' => $top_queries,
				'unanswered_queries' => $unanswered,
				'trends'      => $trends,
				'period'      => $days,
			)
		);
	}
	
	/**
	 * Get knowledge entries.
	 */
	public static function get_knowledge_entries( $request ) {
		global $wpdb;
		
		$limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : 50;
		$offset = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 0;
		$scope = $request->get_param( 'scope' ) ? $request->get_param( 'scope' ) : '';
		
		$where = '';
		$params = array();
		
		if ( $scope ) {
			$where = 'WHERE scope = %s';
			$params[] = $scope;
		}
		
		$params[] = $limit;
		$params[] = $offset;
		
		$entries = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ki_kraft_knowledge
				$where
				ORDER BY created_at DESC
				LIMIT %d OFFSET %d",
				$params
			),
			ARRAY_A
		);
		
		$total = $wpdb->get_var(
			$scope ? $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ki_kraft_knowledge WHERE scope = %s",
				$scope
			) : "SELECT COUNT(*) FROM {$wpdb->prefix}ki_kraft_knowledge"
		);
		
		return rest_ensure_response(
			array(
				'entries' => $entries,
				'total'   => intval( $total ),
			)
		);
	}
	
	/**
	 * Add knowledge entry.
	 */
	public static function add_knowledge_entry( $request ) {
		$title = $request->get_param( 'title' );
		$content = $request->get_param( 'content' );
		$scope = $request->get_param( 'scope' ) ? $request->get_param( 'scope' ) : 'public';
		
		if ( empty( $title ) || empty( $content ) ) {
			return new WP_Error( 'missing_params', __( 'Title and content are required.', KRAFT_AI_CHAT_TEXTDOMAIN ), array( 'status' => 400 ) );
		}
		
		$id = KI_Kraft_FAQ::add_entry( $title, $content, $scope );
		
		if ( ! $id ) {
			return new WP_Error( 'insert_error', __( 'Could not add entry.', KRAFT_AI_CHAT_TEXTDOMAIN ), array( 'status' => 500 ) );
		}
		
		return rest_ensure_response(
			array(
				'success' => true,
				'id'      => $id,
			)
		);
	}
	
	/**
	 * Delete knowledge entry.
	 */
	public static function delete_knowledge_entry( $request ) {
		global $wpdb;
		
		$id = $request->get_param( 'id' );
		
		$result = $wpdb->delete(
			$wpdb->prefix . 'ki_kraft_knowledge',
			array( 'id' => $id ),
			array( '%d' )
		);
		
		return rest_ensure_response(
			array(
				'success' => (bool) $result,
			)
		);
	}
	
	/**
	 * Update branding.
	 */
	public static function update_branding( $request ) {
		$config = $request->get_json_params();
		
		$result = KI_Kraft_Branding::update_config( $config );
		
		return rest_ensure_response(
			array(
				'success' => $result,
			)
		);
	}
	
	/**
	 * Seed sample data.
	 */
	public static function seed_sample_data( $request ) {
		// Call the seeder function
		if ( function_exists( 'ki_kraft_seed_sample_data' ) ) {
			$count = ki_kraft_seed_sample_data();
			
			return rest_ensure_response(
				array(
					'success' => true,
					'count'   => $count,
					'message' => sprintf( __( 'Successfully added %d sample FAQ entries.', KRAFT_AI_CHAT_TEXTDOMAIN ), $count ),
				)
			);
		}
		
		return new WP_Error(
			'seed_unavailable',
			__( 'Seeder function is not available.', KRAFT_AI_CHAT_TEXTDOMAIN ),
			array( 'status' => 500 )
		);
	}
}
