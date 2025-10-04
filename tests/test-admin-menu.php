<?php
/**
 * Test case for admin menu registration.
 *
 * @package KI_Kraft
 */

/**
 * Test admin menu functionality.
 */
class Test_KI_Kraft_Admin_Menu extends WP_UnitTestCase {

	/**
	 * Instance of KI_Kraft_Core.
	 *
	 * @var KI_Kraft_Core
	 */
	private $core;

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Create admin user
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		
		// Initialize core
		$this->core = new KI_Kraft_Core();
		$this->core->add_admin_menu();
	}

	/**
	 * Test main menu page is registered.
	 */
	public function test_main_menu_registered() {
		global $menu;
		
		$found = false;
		foreach ( $menu as $item ) {
			if ( isset( $item[2] ) && 'kraft-ai-chat' === $item[2] ) {
				$found = true;
				$this->assertEquals( 'Kraft AI Chat', $item[0] );
				$this->assertEquals( 'manage_options', $item[1] );
				$this->assertEquals( 'dashicons-format-chat', $item[6] );
				break;
			}
		}
		
		$this->assertTrue( $found, 'Main menu "Kraft AI Chat" was not registered' );
	}

	/**
	 * Test Dashboard submenu is registered.
	 */
	public function test_dashboard_submenu_registered() {
		global $submenu;
		
		$this->assertArrayHasKey( 'kraft-ai-chat', $submenu );
		
		$found = false;
		foreach ( $submenu['kraft-ai-chat'] as $item ) {
			if ( 'kraft-ai-chat' === $item[2] ) {
				$found = true;
				$this->assertEquals( 'Dashboard', $item[0] );
				$this->assertEquals( 'manage_options', $item[1] );
				break;
			}
		}
		
		$this->assertTrue( $found, 'Dashboard submenu was not registered' );
	}

	/**
	 * Test Settings submenu is registered.
	 */
	public function test_settings_submenu_registered() {
		global $submenu;
		
		$this->assertArrayHasKey( 'kraft-ai-chat', $submenu );
		
		$found = false;
		foreach ( $submenu['kraft-ai-chat'] as $item ) {
			if ( 'kraft-ai-chat-settings' === $item[2] ) {
				$found = true;
				$this->assertEquals( 'Settings', $item[0] );
				$this->assertEquals( 'manage_options', $item[1] );
				break;
			}
		}
		
		$this->assertTrue( $found, 'Settings submenu was not registered' );
	}

	/**
	 * Test Analytics submenu is NOT registered.
	 */
	public function test_analytics_submenu_not_registered() {
		global $submenu;
		
		if ( isset( $submenu['kraft-ai-chat'] ) ) {
			foreach ( $submenu['kraft-ai-chat'] as $item ) {
				$this->assertNotEquals( 'kraft-ai-chat-analytics', $item[2], 'Analytics submenu should not be registered' );
			}
		}
	}

	/**
	 * Test that only 2 submenus are registered.
	 */
	public function test_submenu_count() {
		global $submenu;
		
		$this->assertArrayHasKey( 'kraft-ai-chat', $submenu );
		$this->assertCount( 2, $submenu['kraft-ai-chat'], 'Should have exactly 2 submenus: Dashboard and Settings' );
	}

	/**
	 * Test legacy redirect for old analytics page.
	 */
	public function test_legacy_analytics_redirect() {
		$_GET['page'] = 'kraft-ai-chat-analytics';
		
		// Capture redirect
		add_filter( 'wp_redirect', function( $location ) {
			$expected = admin_url( 'admin.php?page=kraft-ai-chat' );
			$this->assertEquals( $expected, $location );
			return false; // Prevent actual redirect
		} );
		
		$this->core->handle_legacy_redirects();
	}

	/**
	 * Test legacy redirect for old settings page.
	 */
	public function test_legacy_settings_redirect() {
		$_GET['page'] = 'kac-settings';
		
		// Capture redirect
		add_filter( 'wp_redirect', function( $location ) {
			$expected = admin_url( 'admin.php?page=kraft-ai-chat-settings' );
			$this->assertEquals( $expected, $location );
			return false; // Prevent actual redirect
		} );
		
		$this->core->handle_legacy_redirects();
	}

	/**
	 * Tear down test.
	 */
	public function tearDown(): void {
		unset( $_GET['page'] );
		parent::tearDown();
	}
}
