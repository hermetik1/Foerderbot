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
			'ki_kraft/v1',
			'/faq/query',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_FAQ', 'handle_query' ),
				'permission_callback' => '__return_true', // Public endpoint
			)
		);

		// Member routes
		register_rest_route(
			'ki_kraft/v1',
			'/member/session',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_Member', 'create_session' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		register_rest_route(
			'ki_kraft/v1',
			'/member/sessions',
			array(
				'methods'             => 'GET',
				'callback'            => array( 'KI_Kraft_Member', 'get_sessions' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		register_rest_route(
			'ki_kraft/v1',
			'/member/message',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_Member', 'handle_message' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		register_rest_route(
			'ki_kraft/v1',
			'/member/upload',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_Indexer', 'handle_upload' ),
				'permission_callback' => array( __CLASS__, 'upload_permission_check' ),
			)
		);

		register_rest_route(
			'ki_kraft/v1',
			'/member/handoff',
			array(
				'methods'             => 'POST',
				'callback'            => array( 'KI_Kraft_Member', 'handle_handoff' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		// Analytics routes
		register_rest_route(
			'ki_kraft/v1',
			'/analytics/summary',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_analytics_summary' ),
				'permission_callback' => array( __CLASS__, 'analytics_permission_check' ),
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
	 * Get analytics summary.
	 */
	public static function get_analytics_summary( $request ) {
		// TODO: Implement analytics aggregation
		return rest_ensure_response( array( 'success' => true ) );
	}
}
