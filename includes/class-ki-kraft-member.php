<?php
/**
 * Member Bot handler.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles member bot functionality for logged-in users.
 */
class KI_Kraft_Member {

	/**
	 * Create a new session.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public static function create_session( $request ) {
		global $wpdb;
		
		$user_id = get_current_user_id();
		$session_id = 'sess_' . bin2hex( random_bytes( 16 ) );

		$result = $wpdb->insert(
			$wpdb->prefix . 'ki_kraft_conversations',
			array(
				'session_id' => $session_id,
				'user_id'    => $user_id,
				'type'       => 'member',
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s', '%s' )
		);

		if ( ! $result ) {
			return new WP_Error( 'session_error', __( 'Could not create session.', 'ki-kraft' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'session_id'       => $session_id,
				'conversation_id'  => $wpdb->insert_id,
				'created_at'       => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Get user sessions.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public static function get_sessions( $request ) {
		global $wpdb;
		
		$user_id = get_current_user_id();
		$limit   = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : 20;
		$offset  = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 0;

		$sessions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ki_kraft_conversations
				WHERE user_id = %d
				ORDER BY updated_at DESC
				LIMIT %d OFFSET %d",
				$user_id,
				$limit,
				$offset
			),
			ARRAY_A
		);

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ki_kraft_conversations
				WHERE user_id = %d",
				$user_id
			)
		);

		return rest_ensure_response(
			array(
				'sessions' => $sessions,
				'total'    => intval( $total ),
				'limit'    => $limit,
				'offset'   => $offset,
			)
		);
	}

	/**
	 * Handle member message with RAG.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public static function handle_message( $request ) {
		global $wpdb;
		
		$session_id = $request->get_param( 'session_id' );
		$message    = $request->get_param( 'message' );
		$user_id    = get_current_user_id();

		if ( empty( $session_id ) || empty( $message ) ) {
			return new WP_Error( 'missing_params', __( 'Session ID and message are required.', 'ki-kraft' ), array( 'status' => 400 ) );
		}

		// Verify session belongs to user
		$conversation = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ki_kraft_conversations
				WHERE session_id = %s AND user_id = %d",
				$session_id,
				$user_id
			),
			ARRAY_A
		);

		if ( ! $conversation ) {
			return new WP_Error( 'invalid_session', __( 'Invalid session.', 'ki-kraft' ), array( 'status' => 403 ) );
		}

		// Store user message
		$wpdb->insert(
			$wpdb->prefix . 'ki_kraft_messages',
			array(
				'conversation_id' => $conversation['id'],
				'role'            => 'user',
				'content'         => $message,
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s' )
		);

		// Get user scope for knowledge search
		$user = wp_get_current_user();
		$scope = self::get_user_scope( $user );

		// Search knowledge base with user scope
		$results = self::search_knowledge_with_scope( $message, $scope, 3 );

		if ( empty( $results ) ) {
			$response_text = __( 'I could not find specific information about that. Would you like to escalate this to our support team?', 'ki-kraft' );
			$sources = array();
			$confidence = 0;
		} else {
			$response_text = $results[0]['content'];
			$sources = array_map(
				function ( $r ) {
					return array(
						'title' => $r['title'],
						'score' => $r['score'],
						'scope' => $r['scope'],
					);
				},
				$results
			);
			$confidence = $results[0]['score'];
		}

		// Store bot response
		$wpdb->insert(
			$wpdb->prefix . 'ki_kraft_messages',
			array(
				'conversation_id' => $conversation['id'],
				'role'            => 'assistant',
				'content'         => $response_text,
				'metadata'        => wp_json_encode( array( 'sources' => $sources ) ),
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		// Update conversation timestamp
		$wpdb->update(
			$wpdb->prefix . 'ki_kraft_conversations',
			array( 'updated_at' => current_time( 'mysql' ) ),
			array( 'id' => $conversation['id'] ),
			array( '%s' ),
			array( '%d' )
		);

		return rest_ensure_response(
			array(
				'response'   => $response_text,
				'sources'    => $sources,
				'confidence' => $confidence,
			)
		);
	}

	/**
	 * Get user scope for knowledge access.
	 *
	 * @param WP_User $user User object.
	 * @return array
	 */
	private static function get_user_scope( $user ) {
		$scopes = array( 'public', 'members' );
		
		foreach ( $user->roles as $role ) {
			$scopes[] = 'role:' . $role;
		}
		
		return $scopes;
	}

	/**
	 * Search knowledge base with scope.
	 *
	 * @param string $query Query string.
	 * @param array  $scopes Allowed scopes.
	 * @param int    $limit Result limit.
	 * @return array
	 */
	private static function search_knowledge_with_scope( $query, $scopes, $limit = 5 ) {
		global $wpdb;

		$scope_placeholders = implode( ',', array_fill( 0, count( $scopes ), '%s' ) );
		
		$sql = $wpdb->prepare(
			"SELECT id, title, content, scope,
			MATCH(title, content) AGAINST (%s IN NATURAL LANGUAGE MODE) as score
			FROM {$wpdb->prefix}ki_kraft_knowledge
			WHERE scope IN ($scope_placeholders)
			AND MATCH(title, content) AGAINST (%s IN NATURAL LANGUAGE MODE)
			ORDER BY score DESC
			LIMIT %d",
			array_merge( array( $query ), $scopes, array( $query, $limit ) )
		);

		$results = $wpdb->get_results( $sql, ARRAY_A );

		return $results ? $results : array();
	}

	/**
	 * Handle handoff to support.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public static function handle_handoff( $request ) {
		$session_id = $request->get_param( 'session_id' );
		$user_id    = get_current_user_id();
		$user       = wp_get_current_user();

		// In a real implementation, this would create a ticket or send an email
		// For now, we'll just log it
		do_action( 'ki_kraft_handoff_request', $session_id, $user_id, $user );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Your request has been forwarded to our support team. They will contact you soon.', 'ki-kraft' ),
			)
		);
	}

	/**
	 * Get conversation history.
	 *
	 * @param string $session_id Session ID.
	 * @param int    $user_id User ID.
	 * @return array
	 */
	public static function get_conversation_history( $session_id, $user_id ) {
		global $wpdb;

		$conversation = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ki_kraft_conversations
				WHERE session_id = %s AND user_id = %d",
				$session_id,
				$user_id
			),
			ARRAY_A
		);

		if ( ! $conversation ) {
			return array();
		}

		$messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ki_kraft_messages
				WHERE conversation_id = %d
				ORDER BY created_at ASC",
				$conversation['id']
			),
			ARRAY_A
		);

		return $messages;
	}
}
