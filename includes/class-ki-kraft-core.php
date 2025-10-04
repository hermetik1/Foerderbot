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
		$this->register_default_settings();
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
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/rest/class-kraft-ai-chat-settings-rest.php';
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
		// Main menu page
		add_menu_page(
			__( 'Kraft AI Chat', KRAFT_AI_CHAT_TEXTDOMAIN ),
			__( 'Kraft AI Chat', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'manage_options',
			'kraft-ai-chat',
			array( $this, 'render_dashboard_page' ),
			'dashicons-format-chat',
			65
		);
		
		// Dashboard submenu (mirrors parent)
		add_submenu_page(
			'kraft-ai-chat',
			__( 'Dashboard', KRAFT_AI_CHAT_TEXTDOMAIN ),
			__( 'Dashboard', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'manage_options',
			'kraft-ai-chat',
			array( $this, 'render_dashboard_page' )
		);
		
		// Settings submenu (separate page)
		add_submenu_page(
			'kraft-ai-chat',
			__( 'Settings', KRAFT_AI_CHAT_TEXTDOMAIN ),
			__( 'Settings', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'manage_options',
			'kraft-ai-chat-settings',
			array( $this, 'render_settings_page' )
		);
		
		// Add help tabs for dashboard
		add_action( 'load-toplevel_page_kraft-ai-chat', array( $this, 'add_dashboard_help_tabs' ) );
		
		// Add help tabs for settings
		add_action( 'load-kraft-ai-chat_page_kraft-ai-chat-settings', array( $this, 'add_settings_help_tabs' ) );
		
		// Handle old page redirects
		add_action( 'admin_init', array( $this, 'handle_legacy_redirects' ) );
	}
	
	/**
	 * Render dashboard page (SPA).
	 */
	public function render_dashboard_page() {
		echo '<div id="ki-kraft-admin-root"></div>';
	}
	
	/**
	 * Render settings page (separate screen).
	 */
	public function render_settings_page() {
		echo '<div id="ki-kraft-admin-root"></div>';
	}
	
	/**
	 * Add help tabs for dashboard.
	 */
	public function add_dashboard_help_tabs() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		
		$screen->add_help_tab(
			array(
				'id'      => 'kac-getting-started',
				'title'   => __( 'Getting Started', KRAFT_AI_CHAT_TEXTDOMAIN ),
				'content' => '<p>' . __( 'Welcome to Kraft AI Chat! Use the Dashboard tabs to manage your chatbot, knowledge base, analytics, privacy settings, and branding.', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</p>',
			)
		);
		
		$screen->add_help_tab(
			array(
				'id'      => 'kac-disclaimer',
				'title'   => __( 'Disclaimer', KRAFT_AI_CHAT_TEXTDOMAIN ),
				'content' => '<p>' . __( 'This plugin uses AI technology. Responses are generated automatically and should be reviewed for accuracy. The plugin does not guarantee correctness of AI-generated content.', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</p>',
			)
		);
		
		$screen->add_help_tab(
			array(
				'id'      => 'kac-faq',
				'title'   => __( 'FAQ', KRAFT_AI_CHAT_TEXTDOMAIN ),
				'content' => '<p><strong>' . __( 'How do I add knowledge?', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</strong><br>' . __( 'Go to the Knowledge Base tab to add and manage entries.', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</p>',
			)
		);
		
		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</strong></p>' .
			'<p><a href="https://github.com/hermetik1/Foerderbot" target="_blank">' . __( 'Documentation', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</a></p>'
		);
	}
	
	/**
	 * Add help tabs for settings.
	 */
	public function add_settings_help_tabs() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		
		$screen->add_help_tab(
			array(
				'id'      => 'kac-configuration',
				'title'   => __( 'Configuration', KRAFT_AI_CHAT_TEXTDOMAIN ),
				'content' => '<p>' . __( 'Configure general settings, API keys, rate limits, knowledge defaults, analytics, privacy, and branding options from this page.', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</p>',
			)
		);
		
		$screen->add_help_tab(
			array(
				'id'      => 'kac-privacy-notes',
				'title'   => __( 'Privacy Notes', KRAFT_AI_CHAT_TEXTDOMAIN ),
				'content' => '<p>' . __( 'Ensure compliance with GDPR and data protection laws. Review privacy settings carefully, especially data retention and external AI service usage.', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</p>',
			)
		);
		
		$screen->add_help_tab(
			array(
				'id'      => 'kac-support',
				'title'   => __( 'Support', KRAFT_AI_CHAT_TEXTDOMAIN ),
				'content' => '<p>' . __( 'Need help? Visit our support documentation or contact support through GitHub issues.', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</p>',
			)
		);
		
		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</strong></p>' .
			'<p><a href="https://github.com/hermetik1/Foerderbot" target="_blank">' . __( 'Documentation', KRAFT_AI_CHAT_TEXTDOMAIN ) . '</a></p>'
		);
	}
	
	/**
	 * Handle legacy page redirects.
	 */
	public function handle_legacy_redirects() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		
		// Redirect old Analytics page to Dashboard
		if ( 'kraft-ai-chat-analytics' === $page || 'kac-analytics' === $page ) {
			wp_safe_redirect( admin_url( 'admin.php?page=kraft-ai-chat' ) );
			exit;
		}
		
		// Redirect old Settings slugs to new Settings page
		if ( 'kac-settings' === $page ) {
			wp_safe_redirect( admin_url( 'admin.php?page=kraft-ai-chat-settings' ) );
			exit;
		}
	}
	
	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'kraft-ai-chat' ) === false ) {
			return;
		}
		
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		
		// Check if assets exist before enqueuing
		$admin_css_path = KRAFT_AI_CHAT_PLUGIN_DIR . 'assets/admin.css';
		$admin_js_path  = KRAFT_AI_CHAT_PLUGIN_DIR . 'assets/admin.js';
		
		if ( ! file_exists( $admin_css_path ) || ! file_exists( $admin_js_path ) ) {
			return;
		}
		
		// Enqueue styles
		wp_enqueue_style(
			'kraft-ai-chat-admin',
			KRAFT_AI_CHAT_PLUGIN_URL . 'assets/admin.css',
			array(),
			filemtime( $admin_css_path )
		);
		
		// Enqueue scripts
		wp_enqueue_script(
			'kraft-ai-chat-admin',
			KRAFT_AI_CHAT_PLUGIN_URL . 'assets/admin.js',
			array( 'wp-element', 'wp-i18n' ),
			filemtime( $admin_js_path ),
			true
		);
		
		// Localize script with page context
		wp_localize_script(
			'kraft-ai-chat-admin',
			'kraftAIChatAdmin',
			array(
				'apiUrl'   => rest_url( KRAFT_AI_CHAT_REST_NS ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'branding' => KI_Kraft_Branding::get_config(),
				'page'     => $page,
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
	 * Register default settings on activation.
	 */
	private function register_default_settings() {
		$settings_groups = array(
			'general'   => array(
				'site_enabled'       => true,
				'default_lang'       => 'de',
				'cache_enabled'      => true,
				'cache_ttl'          => 86400,
				'rate_limit_enabled' => true,
				'rate_limit_max'     => 60,
				'rate_limit_window'  => 3600,
			),
			'privacy'   => array(
				'retention_enabled'      => true,
				'retention_days'         => 365,
				'external_ai_enabled'    => false,
				'consent_required'       => true,
				'data_export_enabled'    => true,
				'data_erase_enabled'     => true,
				'collect_local_analytics' => false,
			),
			'branding'  => array(
				'logo_url'        => '',
				'product_name'    => 'KI Kraft',
				'primary_color'   => '#3b82f6',
				'secondary_color' => '#60a5fa',
				'favicon_url'     => '',
				'footer_text'     => '',
				'privacy_url'     => '',
				'imprint_url'     => '',
				'powered_by'      => true,
			),
			'knowledge' => array(
				'chunk_max_tokens'  => 500,
				'chunk_overlap'     => 50,
				'similarity_threshold' => 0.7,
				'max_results'       => 5,
			),
			'analytics' => array(
				'enabled'         => true,
				'retention_days'  => 90,
				'anonymize_ip'    => true,
				'track_feedback'  => true,
			),
		);

		foreach ( $settings_groups as $group => $defaults ) {
			$option_name = 'kraft_ai_chat_' . $group;
			if ( false === get_option( $option_name ) ) {
				add_option( $option_name, $defaults );
			}
		}
	}

	/**
	 * Run the plugin.
	 */
	public function run() {
		// Initialize REST API
		add_action( 'rest_api_init', array( 'KI_Kraft_REST', 'register_routes' ) );
		add_action( 'rest_api_init', array( 'Kraft_AI_Chat_Settings_REST', 'register_routes' ) );
	}
}
