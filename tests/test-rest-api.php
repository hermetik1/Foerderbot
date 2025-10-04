<?php
/**
 * Test case for REST API endpoints.
 *
 * @package KI_Kraft
 */

/**
 * Test REST API functionality.
 */
class Test_KI_Kraft_REST extends WP_UnitTestCase {

	/**
	 * Test FAQ query endpoint.
	 */
	public function test_faq_query_endpoint() {
		$request = new WP_REST_Request( 'POST', '/ki_kraft/v1/faq/query' );
		$request->set_param( 'query', 'How do I join?' );

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'answer', $data );
	}

	/**
	 * Test member session creation requires login.
	 */
	public function test_member_session_requires_login() {
		$request  = new WP_REST_Request( 'POST', '/ki_kraft/v1/member/session' );
		$response = rest_do_request( $request );

		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test member session creation for logged-in user.
	 */
	public function test_member_session_creation() {
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$request  = new WP_REST_Request( 'POST', '/ki_kraft/v1/member/session' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'session_id', $data );
	}
}
