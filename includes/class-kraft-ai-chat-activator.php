<?php
/**
 * Plugin activation handler.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin activation and database migrations.
 */
class Kraft_AI_Chat_Activator {

	/**
	 * Run activation tasks.
	 */
	public static function activate() {
		// Load db helper
		require_once KRAFT_AI_CHAT_PLUGIN_DIR . 'includes/db.php';
		
		// Run schema upgrades (creates new tables)
		kraft_ai_chat_maybe_upgrade_schema();
		
		// Also run old activation for existing tables and setup
		$core = new KI_Kraft_Core();
		$core->activate();
	}

	/**
	 * Run deactivation tasks.
	 */
	public static function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
