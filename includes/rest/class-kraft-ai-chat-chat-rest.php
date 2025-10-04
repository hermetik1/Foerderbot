<?php
/**
 * REST API handler for chat functionality with rate limiting.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chat REST API endpoints with rate limiting.
 */
class Kraft_AI_Chat_Chat_REST {

	/**
	 * Rate limit configuration per endpoint (requests per minute).
	 *
	 * @var array
	 */
	private static $rate_limits = array(
		'member_session' => 10,
		'member_message' => 20,
		'faq_query'      => 30,
	);

	/**
	 * Register REST API routes.
	 */
	public static function register_routes() {
		// Member session creation
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/session',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_member_session' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		// Member sessions list
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/sessions',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'list_member_sessions' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		// Get session messages
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/session/(?P<id>[a-zA-Z0-9_]+)/messages',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_session_messages' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		// Update session (rename)
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/session/(?P<id>[a-zA-Z0-9_]+)',
			array(
				'methods'             => 'PATCH',
				'callback'            => array( __CLASS__, 'update_session' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		// Delete session
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/session/(?P<id>[a-zA-Z0-9_]+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'delete_session' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		// Member message
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/member/message',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_member_message' ),
				'permission_callback' => array( __CLASS__, 'member_permission_check' ),
			)
		);

		// FAQ query
		register_rest_route(
			KRAFT_AI_CHAT_REST_NS,
			'/faq/query',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_faq_query' ),
				'permission_callback' => '__return_true', // Public endpoint
			)
		);
	}

	/**
	 * Check if user has member permissions (logged in).
	 *
	 * @return bool
	 */
	public static function member_permission_check() {
		return is_user_logged_in();
	}

	/**
	 * Check rate limit for a specific endpoint.
	 *
	 * @param string $endpoint Endpoint identifier.
	 * @param string $identifier User ID or IP address.
	 * @return bool|int False if allowed, or seconds to wait if rate limited.
	 */
	private static function check_rate_limit( $endpoint, $identifier ) {
		if ( ! isset( self::$rate_limits[ $endpoint ] ) ) {
			return false; // No limit set
		}

		$limit = self::$rate_limits[ $endpoint ];
		$transient_key = 'kraft_ai_chat_rate_' . $endpoint . '_' . md5( $identifier );
		$requests = get_transient( $transient_key );

		if ( false === $requests ) {
			// First request in this window
			set_transient( $transient_key, 1, 60 ); // 60 seconds window
			return false;
		}

		if ( $requests >= $limit ) {
			// Rate limit exceeded
			$ttl = get_option( '_transient_timeout_' . $transient_key ) - time();
			return max( $ttl, 1 ); // Return seconds to wait
		}

		// Increment counter
		set_transient( $transient_key, $requests + 1, 60 );
		return false;
	}

	/**
	 * Get rate limit identifier (user ID or IP).
	 *
	 * @return string
	 */
	private static function get_rate_limit_identifier() {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			return 'user_' . $user_id;
		}
		
		// Use IP address for guests
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		return 'ip_' . $ip;
	}

	/**
	 * Return rate limit error response.
	 *
	 * @param int $retry_after Seconds to wait before retry.
	 * @return WP_REST_Response
	 */
	private static function rate_limit_response( $retry_after ) {
		$response = new WP_REST_Response(
			array(
				'success' => false,
				'code'    => 'rate_limit_exceeded',
				'message' => sprintf(
					__( 'Rate limit exceeded. Please try again in %d seconds.', KRAFT_AI_CHAT_TEXTDOMAIN ),
					$retry_after
				),
			),
			429
		);
		$response->header( 'Retry-After', $retry_after );
		return $response;
	}

	/**
	 * Return success response.
	 *
	 * @param array $data Response data.
	 * @return WP_REST_Response
	 */
	private static function success_response( $data ) {
		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $data,
			),
			200
		);
	}

	/**
	 * Return error response.
	 *
	 * @param string $code Error code.
	 * @param string $message Error message.
	 * @param int    $status HTTP status code.
	 * @return WP_REST_Response
	 */
	private static function error_response( $code, $message, $status = 400 ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'code'    => $code,
				'message' => $message,
			),
			$status
		);
	}

	/**
	 * Create a new member session.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function create_member_session( $request ) {
		// Check rate limit
		$identifier = self::get_rate_limit_identifier();
		$rate_check = self::check_rate_limit( 'member_session', $identifier );
		if ( false !== $rate_check ) {
			return self::rate_limit_response( $rate_check );
		}

		global $wpdb;
		$user_id = get_current_user_id();
		$session_id = 'sess_' . bin2hex( random_bytes( 16 ) );
		$current_time = current_time( 'mysql' );

		$result = $wpdb->insert(
			$wpdb->prefix . 'kraft_ai_chat_sessions',
			array(
				'session_id' => $session_id,
				'user_id'    => $user_id,
				'context'    => 'member',
				'title'      => null,
				'created_at' => $current_time,
				'updated_at' => $current_time,
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s' )
		);

		if ( ! $result ) {
			return self::error_response(
				'session_creation_failed',
				__( 'Could not create session.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				500
			);
		}

		return self::success_response(
			array(
				'session_id' => $session_id,
				'created_at' => $current_time,
			)
		);
	}

	/**
	 * Handle member message.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function handle_member_message( $request ) {
		// Check rate limit
		$identifier = self::get_rate_limit_identifier();
		$rate_check = self::check_rate_limit( 'member_message', $identifier );
		if ( false !== $rate_check ) {
			return self::rate_limit_response( $rate_check );
		}

		$session_id = $request->get_param( 'session_id' );
		$message = $request->get_param( 'message' );
		$client_msg_id = $request->get_param( 'client_msg_id' );

		if ( empty( $session_id ) || empty( $message ) ) {
			return self::error_response(
				'missing_parameters',
				__( 'Session ID and message are required.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				400
			);
		}

		global $wpdb;
		$user_id = get_current_user_id();

		// Verify session belongs to user
		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kraft_ai_chat_sessions 
				WHERE session_id = %s AND user_id = %d AND context = 'member'",
				$session_id,
				$user_id
			),
			ARRAY_A
		);

		if ( ! $session ) {
			return self::error_response(
				'invalid_session',
				__( 'Invalid session.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				403
			);
		}

		// Store user message
		$wpdb->insert(
			$wpdb->prefix . 'kraft_ai_chat_messages',
			array(
				'session_id'    => $session_id,
				'sender'        => 'user',
				'content'       => $message,
				'client_msg_id' => $client_msg_id,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		$user_msg_id = $wpdb->insert_id;

		// Create bot placeholder/stub response
		$bot_response = __( 'This is a placeholder response. AI integration will be implemented in a future update.', KRAFT_AI_CHAT_TEXTDOMAIN );
		$bot_client_msg_id = 'bot_' . bin2hex( random_bytes( 8 ) );

		$wpdb->insert(
			$wpdb->prefix . 'kraft_ai_chat_messages',
			array(
				'session_id'            => $session_id,
				'sender'                => 'bot',
				'content'               => $bot_response,
				'client_msg_id'         => $bot_client_msg_id,
				'reply_to_client_msg_id' => $client_msg_id,
				'created_at'            => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		// Return echo response with bot message
		return self::success_response(
			array(
				'user_message'    => array(
					'id'            => $user_msg_id,
					'content'       => $message,
					'client_msg_id' => $client_msg_id,
					'created_at'    => current_time( 'mysql' ),
				),
				'bot_response'    => array(
					'content'               => $bot_response,
					'client_msg_id'         => $bot_client_msg_id,
					'reply_to_client_msg_id' => $client_msg_id,
					'created_at'            => current_time( 'mysql' ),
				),
			)
		);
	}

	/**
	 * Handle FAQ query.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function handle_faq_query( $request ) {
		// Check rate limit
		$identifier = self::get_rate_limit_identifier();
		$rate_check = self::check_rate_limit( 'faq_query', $identifier );
		if ( false !== $rate_check ) {
			return self::rate_limit_response( $rate_check );
		}

		$query = $request->get_param( 'query' );

		if ( empty( $query ) ) {
			return self::error_response(
				'missing_query',
				__( 'Query parameter is required.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				400
			);
		}

		// Stub response until indexer is integrated
		$response = new WP_REST_Response(
			array(
				'success'    => true,
				'data'       => array(
					'answer'     => __( 'FAQ content is not yet indexed. This feature will be available after the indexer integration.', KRAFT_AI_CHAT_TEXTDOMAIN ),
					'sources'    => array(),
					'confidence' => 0,
					'no_content' => true,
				),
			),
			200
		);

		return $response;
	}

	/**
	 * List member sessions.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function list_member_sessions( $request ) {
		global $wpdb;
		$user_id = get_current_user_id();

		$sessions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.session_id, s.title, s.created_at, s.updated_at,
				(SELECT content FROM {$wpdb->prefix}kraft_ai_chat_messages m 
				 WHERE m.session_id = s.session_id 
				 ORDER BY m.created_at DESC LIMIT 1) as last_message
				FROM {$wpdb->prefix}kraft_ai_chat_sessions s
				WHERE s.user_id = %d AND s.context = 'member'
				ORDER BY s.updated_at DESC",
				$user_id
			),
			ARRAY_A
		);

		return self::success_response( $sessions );
	}

	/**
	 * Get session messages.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function get_session_messages( $request ) {
		global $wpdb;
		$user_id    = get_current_user_id();
		$session_id = $request->get_param( 'id' );

		// Verify session belongs to user
		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kraft_ai_chat_sessions 
				WHERE session_id = %s AND user_id = %d AND context = 'member'",
				$session_id,
				$user_id
			),
			ARRAY_A
		);

		if ( ! $session ) {
			return self::error_response(
				'invalid_session',
				__( 'Invalid session.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				403
			);
		}

		// Get messages
		$messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT sender as role, content, created_at
				FROM {$wpdb->prefix}kraft_ai_chat_messages
				WHERE session_id = %s
				ORDER BY created_at ASC",
				$session_id
			),
			ARRAY_A
		);

		// Transform sender to role
		foreach ( $messages as &$msg ) {
			if ( $msg['role'] === 'user' ) {
				$msg['role'] = 'user';
			} else {
				$msg['role'] = 'assistant';
			}
		}

		return self::success_response( $messages );
	}

	/**
	 * Update session (rename).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function update_session( $request ) {
		global $wpdb;
		$user_id    = get_current_user_id();
		$session_id = $request->get_param( 'id' );
		$title      = $request->get_param( 'title' );

		if ( empty( $title ) ) {
			return self::error_response(
				'missing_title',
				__( 'Title is required.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				400
			);
		}

		// Sanitize and validate title
		$title = sanitize_text_field( $title );
		if ( strlen( $title ) > 120 ) {
			$title = substr( $title, 0, 120 );
		}

		// Verify session belongs to user
		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kraft_ai_chat_sessions 
				WHERE session_id = %s AND user_id = %d AND context = 'member'",
				$session_id,
				$user_id
			),
			ARRAY_A
		);

		if ( ! $session ) {
			return self::error_response(
				'invalid_session',
				__( 'Invalid session.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				403
			);
		}

		// Update title
		$result = $wpdb->update(
			$wpdb->prefix . 'kraft_ai_chat_sessions',
			array(
				'title'      => $title,
				'updated_at' => current_time( 'mysql' ),
			),
			array(
				'session_id' => $session_id,
			),
			array( '%s', '%s' ),
			array( '%s' )
		);

		if ( false === $result ) {
			return self::error_response(
				'update_failed',
				__( 'Failed to update session.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				500
			);
		}

		return self::success_response(
			array(
				'session_id' => $session_id,
				'title'      => $title,
				'updated_at' => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Delete session.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function delete_session( $request ) {
		global $wpdb;
		$user_id    = get_current_user_id();
		$session_id = $request->get_param( 'id' );

		// Verify session belongs to user
		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kraft_ai_chat_sessions 
				WHERE session_id = %s AND user_id = %d AND context = 'member'",
				$session_id,
				$user_id
			),
			ARRAY_A
		);

		if ( ! $session ) {
			return self::error_response(
				'invalid_session',
				__( 'Invalid session.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				403
			);
		}

		// Delete messages first
		$wpdb->delete(
			$wpdb->prefix . 'kraft_ai_chat_messages',
			array( 'session_id' => $session_id ),
			array( '%s' )
		);

		// Delete session
		$result = $wpdb->delete(
			$wpdb->prefix . 'kraft_ai_chat_sessions',
			array( 'session_id' => $session_id ),
			array( '%s' )
		);

		if ( false === $result ) {
			return self::error_response(
				'delete_failed',
				__( 'Failed to delete session.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				500
			);
		}

		return self::success_response(
			array(
				'session_id' => $session_id,
				'deleted'    => true,
			)
		);
	}
}
