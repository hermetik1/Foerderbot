<?php
/**
 * Plugin Name: Kraft AI Chat
 * Plugin URI: https://github.com/hermetik1/Foerderbot
 * Description: Dual Chatbot Plugin – FAQ-Bot für Gäste und Mitglieder-Bot für eingeloggte Nutzer. DSGVO-konform, White-Label, Multisite-ready.
 * Version: 1.0.0
 * Requires at least: 6.7
 * Requires PHP: 8.1
 * Author: KI Kraft
 * Author URI: https://ki-kraft.at
 * License: GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: kraft-ai-chat
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin constants.
 */
define( 'KRAFT_AI_CHAT_VERSION', '1.0.0' );
define( 'KRAFT_AI_CHAT_TEXTDOMAIN', 'kraft-ai-chat' );
define( 'KRAFT_AI_CHAT_REST_NS', 'kraft_ai_chat/v1' );
define( 'KRAFT_AI_CHAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KRAFT_AI_CHAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Backward compatibility constants (deprecated)
define( 'KI_KRAFT_VERSION', KRAFT_AI_CHAT_VERSION );
define( 'KI_KRAFT_PLUGIN_DIR', KRAFT_AI_CHAT_PLUGIN_DIR );
define( 'KI_KRAFT_PLUGIN_URL', KRAFT_AI_CHAT_PLUGIN_URL );

/**
 * Load the comprehensive debug logger for GitHub Copilot analysis
 */
require KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/debug-logger.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/class-ki-kraft-core.php';

/**
 * Begins execution of the plugin.
 */
function run_ki_kraft() {
	$plugin = new KI_Kraft_Core();
	$plugin->run();
}
run_ki_kraft();
