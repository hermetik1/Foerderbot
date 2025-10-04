<?php
/**
 * Test case for Chat REST API endpoints with rate limiting.
 *
 * @package KI_Kraft
 */

/**
 * Test Chat REST API functionality.
 */
class Test_Kraft_AI_Chat_REST extends WP_UnitTestCase {

	/**
	 * User ID for testing.
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Setup test case.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->user_id = $this->factory->user->create();
	}

	/**
	 * Test member session creation requires authentication.
	 */
	public function test_member_session_requires_auth() {
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/session' );
		$response = rest_do_request( $request );
		
		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test member session creation for authenticated user.
	 */
	public function test_member_session_creation() {
		wp_set_current_user( $this->user_id );
		
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/session' );
		$response = rest_do_request( $request );
		$data = $response->get_data();
		
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'session_id', $data['data'] );
		$this->assertArrayHasKey( 'created_at', $data['data'] );
		$this->assertStringStartsWith( 'sess_', $data['data']['session_id'] );
	}

	/**
	 * Test member message requires authentication.
	 */
	public function test_member_message_requires_auth() {
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/message' );
		$request->set_param( 'session_id', 'test_session' );
		$request->set_param( 'message', 'Hello' );
		
		$response = rest_do_request( $request );
		
		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test member message requires parameters.
	 */
	public function test_member_message_requires_parameters() {
		wp_set_current_user( $this->user_id );
		
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/message' );
		$response = rest_do_request( $request );
		$data = $response->get_data();
		
		$this->assertEquals( 400, $response->get_status() );
		$this->assertFalse( $data['success'] );
		$this->assertEquals( 'missing_parameters', $data['code'] );
	}

	/**
	 * Test member message with valid session.
	 */
	public function test_member_message_with_valid_session() {
		wp_set_current_user( $this->user_id );
		
		// Create session first
		$session_request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/session' );
		$session_response = rest_do_request( $session_request );
		$session_data = $session_response->get_data();
		$session_id = $session_data['data']['session_id'];
		
		// Send message
		$message_request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/message' );
		$message_request->set_param( 'session_id', $session_id );
		$message_request->set_param( 'message', 'Hello, this is a test message' );
		$message_request->set_param( 'client_msg_id', 'client_123' );
		
		$message_response = rest_do_request( $message_request );
		$data = $message_response->get_data();
		
		$this->assertEquals( 200, $message_response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'user_message', $data['data'] );
		$this->assertArrayHasKey( 'bot_response', $data['data'] );
		$this->assertEquals( 'Hello, this is a test message', $data['data']['user_message']['content'] );
		$this->assertEquals( 'client_123', $data['data']['user_message']['client_msg_id'] );
		$this->assertNotEmpty( $data['data']['bot_response']['content'] );
		$this->assertEquals( 'client_123', $data['data']['bot_response']['reply_to_client_msg_id'] );
	}

	/**
	 * Test member message with invalid session.
	 */
	public function test_member_message_with_invalid_session() {
		wp_set_current_user( $this->user_id );
		
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/message' );
		$request->set_param( 'session_id', 'invalid_session' );
		$request->set_param( 'message', 'Hello' );
		
		$response = rest_do_request( $request );
		$data = $response->get_data();
		
		$this->assertEquals( 403, $response->get_status() );
		$this->assertFalse( $data['success'] );
		$this->assertEquals( 'invalid_session', $data['code'] );
	}

	/**
	 * Test FAQ query endpoint is public.
	 */
	public function test_faq_query_is_public() {
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/faq/query' );
		$request->set_param( 'query', 'How do I join?' );
		
		$response = rest_do_request( $request );
		$data = $response->get_data();
		
		// Should return 200 even without authentication
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
	}

	/**
	 * Test FAQ query requires query parameter.
	 */
	public function test_faq_query_requires_query_parameter() {
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/faq/query' );
		$response = rest_do_request( $request );
		$data = $response->get_data();
		
		$this->assertEquals( 400, $response->get_status() );
		$this->assertFalse( $data['success'] );
		$this->assertEquals( 'missing_query', $data['code'] );
	}

	/**
	 * Test FAQ query returns stub response.
	 */
	public function test_faq_query_returns_stub_response() {
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/faq/query' );
		$request->set_param( 'query', 'What are your hours?' );
		
		$response = rest_do_request( $request );
		$data = $response->get_data();
		
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'answer', $data['data'] );
		$this->assertArrayHasKey( 'sources', $data['data'] );
		$this->assertArrayHasKey( 'no_content', $data['data'] );
		$this->assertTrue( $data['data']['no_content'] );
		$this->assertEmpty( $data['data']['sources'] );
	}

	/**
	 * Test rate limiting on member session endpoint.
	 */
	public function test_rate_limiting_member_session() {
		wp_set_current_user( $this->user_id );
		
		// Make multiple requests to exceed rate limit (limit is 10 per minute)
		$responses = array();
		for ( $i = 0; $i < 12; $i++ ) {
			$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/session' );
			$response = rest_do_request( $request );
			$responses[] = $response;
		}
		
		// Last requests should be rate limited
		$last_response = end( $responses );
		$data = $last_response->get_data();
		
		$this->assertEquals( 429, $last_response->get_status() );
		$this->assertFalse( $data['success'] );
		$this->assertEquals( 'rate_limit_exceeded', $data['code'] );
		
		// Check Retry-After header exists
		$headers = $last_response->get_headers();
		$this->assertArrayHasKey( 'Retry-After', $headers );
		$this->assertGreaterThan( 0, $headers['Retry-After'] );
	}

	/**
	 * Test rate limiting on member message endpoint.
	 */
	public function test_rate_limiting_member_message() {
		wp_set_current_user( $this->user_id );
		
		// Create a session
		$session_request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/session' );
		$session_response = rest_do_request( $session_request );
		$session_data = $session_response->get_data();
		$session_id = $session_data['data']['session_id'];
		
		// Make multiple message requests to exceed rate limit (limit is 20 per minute)
		$responses = array();
		for ( $i = 0; $i < 22; $i++ ) {
			$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/message' );
			$request->set_param( 'session_id', $session_id );
			$request->set_param( 'message', "Test message $i" );
			$response = rest_do_request( $request );
			$responses[] = $response;
		}
		
		// Last requests should be rate limited
		$last_response = end( $responses );
		$data = $last_response->get_data();
		
		$this->assertEquals( 429, $last_response->get_status() );
		$this->assertFalse( $data['success'] );
		$this->assertEquals( 'rate_limit_exceeded', $data['code'] );
		
		// Check Retry-After header
		$headers = $last_response->get_headers();
		$this->assertArrayHasKey( 'Retry-After', $headers );
	}

	/**
	 * Test rate limiting on FAQ query endpoint.
	 */
	public function test_rate_limiting_faq_query() {
		// Make multiple requests to exceed rate limit (limit is 30 per minute)
		$responses = array();
		for ( $i = 0; $i < 32; $i++ ) {
			$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/faq/query' );
			$request->set_param( 'query', "Question $i" );
			$response = rest_do_request( $request );
			$responses[] = $response;
		}
		
		// Last requests should be rate limited
		$last_response = end( $responses );
		$data = $last_response->get_data();
		
		$this->assertEquals( 429, $last_response->get_status() );
		$this->assertFalse( $data['success'] );
		$this->assertEquals( 'rate_limit_exceeded', $data['code'] );
		
		// Check Retry-After header
		$headers = $last_response->get_headers();
		$this->assertArrayHasKey( 'Retry-After', $headers );
	}

	/**
	 * Test unified success response schema.
	 */
	public function test_unified_success_response_schema() {
		wp_set_current_user( $this->user_id );
		
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/session' );
		$response = rest_do_request( $request );
		$data = $response->get_data();
		
		// Check success response has correct structure
		$this->assertArrayHasKey( 'success', $data );
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertIsArray( $data['data'] );
	}

	/**
	 * Test unified error response schema.
	 */
	public function test_unified_error_response_schema() {
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/faq/query' );
		$response = rest_do_request( $request );
		$data = $response->get_data();
		
		// Check error response has correct structure
		$this->assertArrayHasKey( 'success', $data );
		$this->assertFalse( $data['success'] );
		$this->assertArrayHasKey( 'code', $data );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertIsString( $data['code'] );
		$this->assertIsString( $data['message'] );
	}

	/**
	 * Test messages are persisted to database.
	 */
	public function test_messages_persisted_to_database() {
		global $wpdb;
		wp_set_current_user( $this->user_id );
		
		// Create session
		$session_request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/session' );
		$session_response = rest_do_request( $session_request );
		$session_data = $session_response->get_data();
		$session_id = $session_data['data']['session_id'];
		
		// Send message
		$message_request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/member/message' );
		$message_request->set_param( 'session_id', $session_id );
		$message_request->set_param( 'message', 'Test persistence' );
		rest_do_request( $message_request );
		
		// Check database for messages
		$messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kraft_ai_chat_messages WHERE session_id = %s",
				$session_id
			)
		);
		
		$this->assertGreaterThanOrEqual( 2, count( $messages ) ); // At least user + bot message
		
		// Check user message
		$user_message = null;
		$bot_message = null;
		foreach ( $messages as $msg ) {
			if ( $msg->sender === 'user' ) {
				$user_message = $msg;
			} elseif ( $msg->sender === 'bot' ) {
				$bot_message = $msg;
			}
		}
		
		$this->assertNotNull( $user_message );
		$this->assertEquals( 'Test persistence', $user_message->content );
		$this->assertNotNull( $bot_message );
		$this->assertNotEmpty( $bot_message->content );
	}
}
