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
		
		// Register activation/deactivation hooks
		register_activation_hook( KI_KRAFT_PLUGIN_DIR . 'ki-kraft.php', array( $this, 'activate' ) );
		register_deactivation_hook( KI_KRAFT_PLUGIN_DIR . 'ki-kraft.php', array( $this, 'deactivate' ) );
	}
	
	/**
	 * Plugin activation.
	 */
	public function activate() {
		$this->create_tables();
		$this->setup_capabilities();
		flush_rewrite_rules();
	}
	
	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}
	
	/**
	 * Create custom database tables.
	 */
	private function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		// Conversations table
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ki_kraft_conversations (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id varchar(100) NOT NULL,
			user_id bigint(20) UNSIGNED NOT NULL,
			type varchar(20) NOT NULL DEFAULT 'member',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY user_id (user_id)
		) $charset_collate;";
		dbDelta( $sql );
		
		// Messages table
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ki_kraft_messages (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			conversation_id bigint(20) UNSIGNED NOT NULL,
			role varchar(20) NOT NULL,
			content text NOT NULL,
			metadata text,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY conversation_id (conversation_id)
		) $charset_collate;";
		dbDelta( $sql );
		
		// Knowledge base table
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ki_kraft_knowledge (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			content text NOT NULL,
			embedding text,
			scope varchar(50) NOT NULL DEFAULT 'public',
			metadata text,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY scope (scope),
			FULLTEXT KEY content_fulltext (title, content)
		) $charset_collate;";
		dbDelta( $sql );
		
		// Analytics table
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ki_kraft_analytics (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			query text NOT NULL,
			query_hash varchar(64) NOT NULL,
			answered tinyint(1) NOT NULL DEFAULT 0,
			feedback tinyint(1),
			latency int,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY query_hash (query_hash),
			KEY answered (answered),
			KEY created_at (created_at)
		) $charset_collate;";
		dbDelta( $sql );
	}
	
	/**
	 * Setup custom capabilities.
	 */
	private function setup_capabilities() {
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( 'kk_upload_member_docs' );
			$admin_role->add_cap( 'kk_view_analytics' );
			$admin_role->add_cap( 'kk_manage_knowledge' );
		}
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
		require_once KI_KRAFT_PLUGIN_DIR . 'includes/class-ki-kraft-seeder.php';
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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}
	
	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'KI Kraft', 'ki-kraft' ),
			__( 'KI Kraft', 'ki-kraft' ),
			'manage_options',
			'ki-kraft',
			array( $this, 'render_admin_page' ),
			'dashicons-format-chat',
			30
		);
		
		add_submenu_page(
			'ki-kraft',
			__( 'Dashboard', 'ki-kraft' ),
			__( 'Dashboard', 'ki-kraft' ),
			'manage_options',
			'ki-kraft',
			array( $this, 'render_admin_page' )
		);
		
		add_submenu_page(
			'ki-kraft',
			__( 'Settings', 'ki-kraft' ),
			__( 'Settings', 'ki-kraft' ),
			'manage_options',
			'ki-kraft-settings',
			array( $this, 'render_admin_page' )
		);
		
		add_submenu_page(
			'ki-kraft',
			__( 'Analytics', 'ki-kraft' ),
			__( 'Analytics', 'ki-kraft' ),
			'kk_view_analytics',
			'ki-kraft-analytics',
			array( $this, 'render_admin_page' )
		);
	}
	
	/**
	 * Render admin page.
	 */
	public function render_admin_page() {
		echo '<div id="ki-kraft-admin-root"></div>';
	}
	
	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'ki-kraft' ) === false ) {
			return;
		}
		
		wp_enqueue_style(
			'ki-kraft-admin',
			KI_KRAFT_PLUGIN_URL . 'assets/admin.css',
			array(),
			KI_KRAFT_VERSION
		);
		
		wp_enqueue_script(
			'ki-kraft-admin',
			KI_KRAFT_PLUGIN_URL . 'assets/admin.js',
			array( 'wp-element', 'wp-i18n' ),
			KI_KRAFT_VERSION,
			true
		);
		
		wp_localize_script(
			'ki-kraft-admin',
			'kiKraftAdmin',
			array(
				'apiUrl'   => rest_url( 'ki_kraft/v1' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'branding' => KI_Kraft_Branding::get_config(),
			)
		);
	}

	/**
	 * Register public-facing hooks.
	 */
	private function define_public_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		add_shortcode( 'ki_kraft_chatbot', array( $this, 'render_chatbot_shortcode' ) );
		add_action( 'init', array( $this, 'register_blocks' ) );
	}
	
	/**
	 * Enqueue public assets.
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style(
			'ki-kraft-widget',
			KI_KRAFT_PLUGIN_URL . 'assets/widget.css',
			array(),
			KI_KRAFT_VERSION
		);
		
		wp_enqueue_script(
			'ki-kraft-widget',
			KI_KRAFT_PLUGIN_URL . 'assets/widget.js',
			array(),
			KI_KRAFT_VERSION,
			true
		);
		
		wp_localize_script(
			'ki-kraft-widget',
			'KIKraftConfig',
			array(
				'apiUrl'   => rest_url( 'ki_kraft/v1' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'user'     => $this->get_user_config(),
				'branding' => KI_Kraft_Branding::get_config(),
				'i18n'     => array(
					'send'       => __( 'Send', 'ki-kraft' ),
					'typing'     => __( 'Typing...', 'ki-kraft' ),
					'placeholder' => __( 'Type your message...', 'ki-kraft' ),
					'close'      => __( 'Close', 'ki-kraft' ),
				),
			)
		);
	}
	
	/**
	 * Get user configuration.
	 */
	private function get_user_config() {
		if ( ! is_user_logged_in() ) {
			return array( 'loggedIn' => false );
		}
		
		$user = wp_get_current_user();
		return array(
			'loggedIn'   => true,
			'userId'     => $user->ID,
			'displayName' => $user->display_name,
			'avatarUrl'  => get_avatar_url( $user->ID ),
			'roles'      => $user->roles,
		);
	}
	
	/**
	 * Render chatbot shortcode.
	 */
	public function render_chatbot_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'type' => 'faq',
			),
			$atts
		);
		
		return '<div class="kk-widget" data-type="' . esc_attr( $atts['type'] ) . '"></div>';
	}
	
	/**
	 * Register Gutenberg blocks.
	 */
	public function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		
		register_block_type(
			'ki-kraft/chatbot',
			array(
				'editor_script'   => 'ki-kraft-block-editor',
				'editor_style'    => 'ki-kraft-block-editor',
				'style'           => 'ki-kraft-widget',
				'render_callback' => array( $this, 'render_chatbot_shortcode' ),
			)
		);
	}

	/**
	 * Run the plugin.
	 */
	public function run() {
		// Initialize REST API
		add_action( 'rest_api_init', array( 'KI_Kraft_REST', 'register_routes' ) );
	}
}
