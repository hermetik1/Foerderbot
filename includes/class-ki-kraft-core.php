<?php
/**
 * The core plugin class.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin class that orchestrates all functionality.
 */
class KI_Kraft_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var object $loader
	 */
	protected $loader;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once KI_KRAFT_PLUGIN_DIR . 'includes/class-ki-kraft-rest.php';
		require_once KI_KRAFT_PLUGIN_DIR . 'includes/class-ki-kraft-faq.php';
		require_once KI_KRAFT_PLUGIN_DIR . 'includes/class-ki-kraft-member.php';
		require_once KI_KRAFT_PLUGIN_DIR . 'includes/class-ki-kraft-privacy.php';
		require_once KI_KRAFT_PLUGIN_DIR . 'includes/class-ki-kraft-branding.php';
		require_once KI_KRAFT_PLUGIN_DIR . 'includes/class-ki-kraft-indexer.php';
	}

	/**
	 * Set the plugin locale for internationalization.
	 */
	private function set_locale() {
		load_plugin_textdomain(
			'ki-kraft',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * Register admin-specific hooks.
	 */
	private function define_admin_hooks() {
		// Admin menu, enqueue scripts, etc.
	}

	/**
	 * Register public-facing hooks.
	 */
	private function define_public_hooks() {
		// Shortcodes, blocks, frontend scripts, etc.
	}

	/**
	 * Run the plugin.
	 */
	public function run() {
		// Initialize REST API
		add_action( 'rest_api_init', array( 'KI_Kraft_REST', 'register_routes' ) );
	}
}
