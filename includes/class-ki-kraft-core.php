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
		register_activation_hook( KRAFT_AI_CHAT_PLUGIN_DIR . 'ki-kraft.php', array( $this, 'activate' ) );
		register_deactivation_hook( KRAFT_AI_CHAT_PLUGIN_DIR . 'ki-kraft.php', array( $this, 'deactivate' ) );
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
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/class-ki-kraft-rest.php';
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/class-ki-kraft-faq.php';
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/class-ki-kraft-member.php';
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/class-ki-kraft-privacy.php';
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/class-ki-kraft-branding.php';
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/class-ki-kraft-indexer.php';
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/class-ki-kraft-seeder.php';
	}

	/**
	 * Set the plugin locale for internationalization.
	 */
	private function set_locale() {
		load_plugin_textdomain(
			KRAFT_AI_CHAT_TEXTDOMAIN,
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
			__( 'Kraft AI Chat', KRAFT_AI_CHAT_TEXTDOMAIN ),
			__( 'Kraft AI Chat', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'manage_options',
			'kraft-ai-chat',
			array( $this, 'render_admin_page' ),
			'dashicons-format-chat',
			30
		);
		
		add_submenu_page(
			'kraft-ai-chat',
			__( 'Dashboard', KRAFT_AI_CHAT_TEXTDOMAIN ),
			__( 'Dashboard', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'manage_options',
			'kraft-ai-chat',
			array( $this, 'render_admin_page' )
		);
		
		add_submenu_page(
			'kraft-ai-chat',
			__( 'Settings', KRAFT_AI_CHAT_TEXTDOMAIN ),
			__( 'Settings', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'manage_options',
			'kraft-ai-chat-settings',
			array( $this, 'render_admin_page' )
		);
		
		add_submenu_page(
			'kraft-ai-chat',
			__( 'Analytics', KRAFT_AI_CHAT_TEXTDOMAIN ),
			__( 'Analytics', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'kk_view_analytics',
			'kraft-ai-chat-analytics',
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
		if ( strpos( $hook, 'kraft-ai-chat' ) === false ) {
			return;
		}
		
		wp_enqueue_style(
			'kraft-ai-chat-admin',
			KRAFT_AI_CHAT_PLUGIN_URL . 'assets/admin.css',
			array(),
			KRAFT_AI_CHAT_VERSION
		);
		
		wp_enqueue_script(
			'kraft-ai-chat-admin',
			KRAFT_AI_CHAT_PLUGIN_URL . 'assets/admin.js',
			array( 'wp-element', 'wp-i18n' ),
			KRAFT_AI_CHAT_VERSION,
			true
		);
		
		wp_localize_script(
			'kraft-ai-chat-admin',
			'kraftAIChatAdmin',
			array(
				'apiUrl'   => rest_url( KRAFT_AI_CHAT_REST_NS ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'branding' => KI_Kraft_Branding::get_config(),
			)
		);
		
		wp_set_script_translations( 'kraft-ai-chat-admin', KRAFT_AI_CHAT_TEXTDOMAIN );
	}

	/**
	 * Register public-facing hooks.
	 */
	private function define_public_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		// Register canonical shortcode
		add_shortcode( 'kraft_ai_chat_chatbot', array( $this, 'render_chatbot_shortcode' ) );
		// Register deprecated shortcode with warning
		add_shortcode( 'ki_kraft_chatbot', array( $this, 'render_deprecated_chatbot_shortcode' ) );
		add_action( 'init', array( $this, 'register_blocks' ) );
	}
	
	/**
	 * Enqueue public assets.
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style(
			'kraft-ai-chat-widget',
			KRAFT_AI_CHAT_PLUGIN_URL . 'assets/widget.css',
			array(),
			KRAFT_AI_CHAT_VERSION
		);
		
		wp_enqueue_script(
			'kraft-ai-chat-widget',
			KRAFT_AI_CHAT_PLUGIN_URL . 'assets/widget.js',
			array(),
			KRAFT_AI_CHAT_VERSION,
			true
		);
		
		wp_localize_script(
			'kraft-ai-chat-widget',
			'kraftAIChatConfig',
			array(
				'apiUrl'   => rest_url( KRAFT_AI_CHAT_REST_NS ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'user'     => $this->get_user_config(),
				'branding' => KI_Kraft_Branding::get_config(),
				'i18n'     => array(
					'send'        => __( 'Send', KRAFT_AI_CHAT_TEXTDOMAIN ),
					'typing'      => __( 'Typing...', KRAFT_AI_CHAT_TEXTDOMAIN ),
					'placeholder' => __( 'Type your message...', KRAFT_AI_CHAT_TEXTDOMAIN ),
					'close'       => __( 'Close', KRAFT_AI_CHAT_TEXTDOMAIN ),
				),
			)
		);
		
		wp_set_script_translations( 'kraft-ai-chat-widget', KRAFT_AI_CHAT_TEXTDOMAIN );
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
	 * Render deprecated chatbot shortcode with warning.
	 */
	public function render_deprecated_chatbot_shortcode( $atts ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			_doing_it_wrong(
				'ki_kraft_chatbot',
				sprintf(
					/* translators: 1: Deprecated shortcode, 2: New shortcode */
					__( 'The %1$s shortcode is deprecated. Please use %2$s instead.', KRAFT_AI_CHAT_TEXTDOMAIN ),
					'<code>[ki_kraft_chatbot]</code>',
					'<code>[kraft_ai_chat_chatbot]</code>'
				),
				KRAFT_AI_CHAT_VERSION
			);
		}
		
		return $this->render_chatbot_shortcode( $atts );
	}
	
	/**
	 * Register Gutenberg blocks.
	 */
	public function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		
		register_block_type(
			'kraft-ai-chat/chatbot',
			array(
				'editor_script'   => 'kraft-ai-chat-block-editor',
				'editor_style'    => 'kraft-ai-chat-block-editor',
				'style'           => 'kraft-ai-chat-widget',
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
