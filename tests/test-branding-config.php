<?php
/**
 * Test case for branding configuration.
 *
 * @package KI_Kraft
 */

/**
 * Test branding configuration functionality.
 */
class Test_KI_Kraft_Branding extends WP_UnitTestCase {

	/**
	 * Test that get_config returns all required branding fields.
	 */
	public function test_branding_config_has_all_fields() {
		$config = KI_Kraft_Branding::get_config();
		
		// Assert all required fields are present
		$this->assertArrayHasKey( 'logo_url', $config );
		$this->assertArrayHasKey( 'product_name', $config );
		$this->assertArrayHasKey( 'primary_color', $config );
		$this->assertArrayHasKey( 'secondary_color', $config );
		$this->assertArrayHasKey( 'theme', $config );
		$this->assertArrayHasKey( 'icon_color', $config );
		$this->assertArrayHasKey( 'header_text_color', $config );
		$this->assertArrayHasKey( 'faq_header_title', $config );
		$this->assertArrayHasKey( 'advisor_header_title', $config );
		$this->assertArrayHasKey( 'favicon_url', $config );
		$this->assertArrayHasKey( 'footer_text', $config );
		$this->assertArrayHasKey( 'privacy_url', $config );
		$this->assertArrayHasKey( 'imprint_url', $config );
		$this->assertArrayHasKey( 'powered_by', $config );
	}

	/**
	 * Test that get_config returns proper defaults.
	 */
	public function test_branding_config_defaults() {
		$config = KI_Kraft_Branding::get_config();
		
		// Assert default values
		$this->assertEquals( 'KI Kraft', $config['product_name'] );
		$this->assertEquals( '#3b82f6', $config['primary_color'] );
		$this->assertEquals( '#60a5fa', $config['secondary_color'] );
		$this->assertEquals( 'auto', $config['theme'] );
		$this->assertEquals( '#3b82f6', $config['icon_color'] );
		$this->assertEquals( '#111827', $config['header_text_color'] );
		$this->assertEquals( 'Häufige Fragen', $config['faq_header_title'] );
		$this->assertEquals( 'Mitglieder-Chat', $config['advisor_header_title'] );
		$this->assertTrue( $config['powered_by'] );
	}

	/**
	 * Test that localized script includes branding config.
	 */
	public function test_localized_script_includes_branding() {
		$core = new KI_Kraft_Core();
		
		// Enqueue scripts
		do_action( 'wp_enqueue_scripts' );
		
		// Get the localized data
		global $wp_scripts;
		$script_data = $wp_scripts->get_data( 'kraft-ai-chat-widget', 'data' );
		
		// Assert that kraftAIChatConfig is localized
		$this->assertStringContainsString( 'kraftAIChatConfig', $script_data );
		$this->assertStringContainsString( 'branding', $script_data );
	}

	/**
	 * Test that localized script includes settings.
	 */
	public function test_localized_script_includes_settings() {
		$core = new KI_Kraft_Core();
		
		// Enqueue scripts
		do_action( 'wp_enqueue_scripts' );
		
		// Get the localized data
		global $wp_scripts;
		$script_data = $wp_scripts->get_data( 'kraft-ai-chat-widget', 'data' );
		
		// Assert that settings are included
		$this->assertStringContainsString( 'settings', $script_data );
	}

	/**
	 * Test that branding config reads from new settings structure.
	 */
	public function test_branding_config_from_settings() {
		// Set up branding settings
		$branding_settings = array(
			'product_name'        => 'Test Chat',
			'primary_color'       => '#ff0000',
			'icon_color'          => '#00ff00',
			'faq_header_title'    => 'Test FAQ',
			'advisor_header_title' => 'Test Advisor',
		);
		update_option( 'kraft_ai_chat_branding', $branding_settings );
		
		$config = KI_Kraft_Branding::get_config();
		
		// Assert that values from settings are used
		$this->assertEquals( 'Test Chat', $config['product_name'] );
		$this->assertEquals( '#ff0000', $config['primary_color'] );
		$this->assertEquals( '#00ff00', $config['icon_color'] );
		$this->assertEquals( 'Test FAQ', $config['faq_header_title'] );
		$this->assertEquals( 'Test Advisor', $config['advisor_header_title'] );
		
		// Clean up
		delete_option( 'kraft_ai_chat_branding' );
	}

	/**
	 * Test that branding config falls back to legacy options.
	 */
	public function test_branding_config_legacy_fallback() {
		// Set up legacy options
		update_option( 'ki_kraft_product_name', 'Legacy Name' );
		update_option( 'ki_kraft_primary_color', '#123456' );
		
		$config = KI_Kraft_Branding::get_config();
		
		// Assert that legacy values are used when new settings don't exist
		$this->assertEquals( 'Legacy Name', $config['product_name'] );
		$this->assertEquals( '#123456', $config['primary_color'] );
		
		// Clean up
		delete_option( 'ki_kraft_product_name' );
		delete_option( 'ki_kraft_primary_color' );
	}
}
