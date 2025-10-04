<?php
/**
 * Test case for Settings REST API endpoints.
 *
 * @package KI_Kraft
 */

/**
 * Test Settings REST API functionality.
 */
class Test_Settings_REST extends WP_UnitTestCase {

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Regular user ID.
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Setup test environment.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$this->user_id  = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Load settings REST API class
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/rest/class-kraft-ai-chat-settings-rest.php';
		Kraft_AI_Chat_Settings_REST::register_routes();
	}

	/**
	 * Test that non-admin users cannot access settings.
	 */
	public function test_settings_require_admin() {
		wp_set_current_user( $this->user_id );

		$request  = new WP_REST_Request( 'GET', '/kraft_ai_chat/v1/settings/general' );
		$response = rest_do_request( $request );

		$this->assertEquals( 403, $response->get_status() );
	}

	/**
	 * Test getting general settings.
	 */
	public function test_get_general_settings() {
		wp_set_current_user( $this->admin_id );

		$request  = new WP_REST_Request( 'GET', '/kraft_ai_chat/v1/settings/general' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'site_enabled', $data );
		$this->assertArrayHasKey( 'default_lang', $data );
		$this->assertArrayHasKey( 'cache_enabled', $data );
	}

	/**
	 * Test getting privacy settings.
	 */
	public function test_get_privacy_settings() {
		wp_set_current_user( $this->admin_id );

		$request  = new WP_REST_Request( 'GET', '/kraft_ai_chat/v1/settings/privacy' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'retention_enabled', $data );
		$this->assertArrayHasKey( 'retention_days', $data );
		$this->assertArrayHasKey( 'external_ai_enabled', $data );
	}

	/**
	 * Test getting branding settings.
	 */
	public function test_get_branding_settings() {
		wp_set_current_user( $this->admin_id );

		$request  = new WP_REST_Request( 'GET', '/kraft_ai_chat/v1/settings/branding' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'product_name', $data );
		$this->assertArrayHasKey( 'primary_color', $data );
		$this->assertArrayHasKey( 'logo_url', $data );
	}

	/**
	 * Test getting knowledge settings.
	 */
	public function test_get_knowledge_settings() {
		wp_set_current_user( $this->admin_id );

		$request  = new WP_REST_Request( 'GET', '/kraft_ai_chat/v1/settings/knowledge' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'chunk_max_tokens', $data );
		$this->assertArrayHasKey( 'chunk_overlap', $data );
		$this->assertArrayHasKey( 'similarity_threshold', $data );
	}

	/**
	 * Test getting analytics settings.
	 */
	public function test_get_analytics_settings() {
		wp_set_current_user( $this->admin_id );

		$request  = new WP_REST_Request( 'GET', '/kraft_ai_chat/v1/settings/analytics' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'enabled', $data );
		$this->assertArrayHasKey( 'retention_days', $data );
	}

	/**
	 * Test updating general settings.
	 */
	public function test_update_general_settings() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/general' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'site_enabled'       => false,
					'default_lang'       => 'en',
					'cache_enabled'      => true,
					'cache_ttl'          => 3600,
					'rate_limit_enabled' => true,
					'rate_limit_max'     => 100,
					'rate_limit_window'  => 3600,
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertEquals( 'en', $data['data']['default_lang'] );
		$this->assertEquals( 100, $data['data']['rate_limit_max'] );
	}

	/**
	 * Test updating privacy settings.
	 */
	public function test_update_privacy_settings() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/privacy' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'retention_enabled'      => true,
					'retention_days'         => 180,
					'external_ai_enabled'    => false,
					'consent_required'       => true,
					'data_export_enabled'    => true,
					'data_erase_enabled'     => true,
					'collect_local_analytics' => true,
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertEquals( 180, $data['data']['retention_days'] );
	}

	/**
	 * Test validation failure for invalid color.
	 */
	public function test_invalid_color_validation() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/branding' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'primary_color' => 'invalid-color',
				)
			)
		);

		$response = rest_do_request( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test validation failure for out-of-range value.
	 */
	public function test_out_of_range_validation() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/general' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'cache_ttl' => 999999, // Exceeds maximum
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'errors', $data['data'] );
	}

	/**
	 * Test validation failure for invalid enum value.
	 */
	public function test_invalid_enum_validation() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/general' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'default_lang' => 'invalid-lang',
				)
			)
		);

		$response = rest_do_request( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test floating settings defaults.
	 */
	public function test_floating_settings_defaults() {
		wp_set_current_user( $this->admin_id );

		$request  = new WP_REST_Request( 'GET', '/kraft_ai_chat/v1/settings/general' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'floating_enabled', $data );
		$this->assertArrayHasKey( 'floating_default_type', $data );
		$this->assertArrayHasKey( 'floating_position', $data );
		$this->assertFalse( $data['floating_enabled'] );
		$this->assertEquals( 'faq', $data['floating_default_type'] );
		$this->assertEquals( 'br', $data['floating_position'] );
	}

	/**
	 * Test updating floating settings.
	 */
	public function test_update_floating_settings() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/general' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'floating_enabled'      => true,
					'floating_default_type' => 'member',
					'floating_position'     => 'bl',
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertTrue( $data['data']['floating_enabled'] );
		$this->assertEquals( 'member', $data['data']['floating_default_type'] );
		$this->assertEquals( 'bl', $data['data']['floating_position'] );
	}

	/**
	 * Test floating position enum validation.
	 */
	public function test_floating_position_validation() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/general' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'floating_position' => 'invalid',
				)
			)
		);

		$response = rest_do_request( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test floating type enum validation.
	 */
	public function test_floating_type_validation() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/general' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'floating_default_type' => 'invalid',
				)
			)
		);

		$response = rest_do_request( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test API key masking in GET requests.
	 */
	public function test_api_key_masking() {
		wp_set_current_user( $this->admin_id );

		// First, set an API key
		update_option(
			'kraft_ai_chat_integrations',
			array(
				'openai_api_key'  => 'sk-1234567890abcdef',
				'whisper_api_key' => 'wk-abcdefghijklmnop',
				'rag_service'     => 'test',
				'rag_endpoint'    => 'http://example.com',
			)
		);

		// Now get the settings
		$request  = new WP_REST_Request( 'GET', '/kraft_ai_chat/v1/settings/integrations' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertStringContainsString( '*', $data['openai_api_key'] );
		$this->assertStringContainsString( '*', $data['whisper_api_key'] );
		$this->assertStringEndsWith( 'cdef', $data['openai_api_key'] );
		$this->assertStringEndsWith( 'mnop', $data['whisper_api_key'] );
	}

	/**
	 * Test API key preservation when not changed.
	 */
	public function test_api_key_preservation() {
		wp_set_current_user( $this->admin_id );

		// Set initial API key
		update_option(
			'kraft_ai_chat_integrations',
			array(
				'openai_api_key'  => 'sk-original-key-12345',
				'whisper_api_key' => 'wk-original-key-67890',
				'rag_service'     => 'test',
				'rag_endpoint'    => 'http://example.com',
			)
		);

		// Update other fields without changing API keys (send masked value)
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/integrations' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'openai_api_key'  => '****************12345', // Masked value
					'whisper_api_key' => '****************67890', // Masked value
					'rag_service'     => 'updated',
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );

		// Verify the original key is preserved
		$saved = get_option( 'kraft_ai_chat_integrations' );
		$this->assertEquals( 'sk-original-key-12345', $saved['openai_api_key'] );
		$this->assertEquals( 'wk-original-key-67890', $saved['whisper_api_key'] );
		$this->assertEquals( 'updated', $saved['rag_service'] );
	}

	/**
	 * Test updating API key with new value.
	 */
	public function test_api_key_update() {
		wp_set_current_user( $this->admin_id );

		// Set initial API key
		update_option(
			'kraft_ai_chat_integrations',
			array(
				'openai_api_key'  => 'sk-old-key',
				'whisper_api_key' => 'wk-old-key',
				'rag_service'     => 'test',
				'rag_endpoint'    => 'http://example.com',
			)
		);

		// Update with new API key
		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/integrations' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'openai_api_key' => 'sk-new-key-value',
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );

		// Verify the new key is saved
		$saved = get_option( 'kraft_ai_chat_integrations' );
		$this->assertEquals( 'sk-new-key-value', $saved['openai_api_key'] );
	}

	/**
	 * Test branding settings with valid hex colors.
	 */
	public function test_update_branding_settings() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/branding' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'product_name'    => 'My Custom Bot',
					'primary_color'   => '#ff5733',
					'secondary_color' => '#33c1ff',
					'powered_by'      => false,
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertEquals( 'My Custom Bot', $data['data']['product_name'] );
		$this->assertEquals( '#ff5733', $data['data']['primary_color'] );
	}

	/**
	 * Test knowledge settings update.
	 */
	public function test_update_knowledge_settings() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/knowledge' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'chunk_max_tokens'  => 1000,
					'chunk_overlap'     => 100,
					'similarity_threshold' => 0.8,
					'max_results'       => 10,
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertEquals( 1000, $data['data']['chunk_max_tokens'] );
		$this->assertEquals( 0.8, $data['data']['similarity_threshold'] );
	}

	/**
	 * Test analytics settings update.
	 */
	public function test_update_analytics_settings() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/analytics' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'enabled'        => true,
					'retention_days' => 180,
					'anonymize_ip'   => true,
					'track_feedback' => true,
				)
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertEquals( 180, $data['data']['retention_days'] );
	}

	/**
	 * Test that empty POST returns error.
	 */
	public function test_empty_post_returns_error() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/kraft_ai_chat/v1/settings/general' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( array() ) );

		$response = rest_do_request( $request );
		$this->assertEquals( 400, $response->get_status() );
	}
}
