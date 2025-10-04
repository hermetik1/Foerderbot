<?php
/**
 * Database helper functions and schema management.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get current database schema version.
 *
 * @return int Current schema version.
 */
function kraft_ai_chat_get_schema_version() {
	return (int) get_option( 'kraft_ai_chat_schema_version', 0 );
}

/**
 * Set database schema version.
 *
 * @param int $version Schema version.
 */
function kraft_ai_chat_set_schema_version( $version ) {
	update_option( 'kraft_ai_chat_schema_version', $version );
}

/**
 * Check and run schema upgrades if needed.
 */
function kraft_ai_chat_maybe_upgrade_schema() {
	$current_version = kraft_ai_chat_get_schema_version();
	$target_version  = 1; // Current schema version
	
	if ( $current_version >= $target_version ) {
		return; // Already up to date
	}
	
	// Run migrations
	if ( $current_version < 1 ) {
		kraft_ai_chat_create_tables_v1();
		kraft_ai_chat_set_schema_version( 1 );
	}
}

/**
 * Create database tables for schema version 1.
 */
function kraft_ai_chat_create_tables_v1() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	
	// Sessions table
	$sql = "CREATE TABLE {$wpdb->prefix}kraft_ai_chat_sessions (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		session_id varchar(100) NOT NULL,
		user_id bigint(20) UNSIGNED DEFAULT NULL,
		context varchar(20) NOT NULL DEFAULT 'faq',
		created_at datetime NOT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY session_id (session_id),
		KEY user_id (user_id),
		KEY context (context),
		KEY created_at (created_at)
	) $charset_collate;";
	dbDelta( $sql );
	
	// Messages table
	$sql = "CREATE TABLE {$wpdb->prefix}kraft_ai_chat_messages (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		session_id varchar(100) NOT NULL,
		sender varchar(20) NOT NULL,
		content longtext NOT NULL,
		sources text DEFAULT NULL,
		client_msg_id varchar(100) DEFAULT NULL,
		reply_to_client_msg_id varchar(100) DEFAULT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY (id),
		KEY session_id (session_id),
		KEY sender (sender),
		KEY client_msg_id (client_msg_id),
		KEY created_at (created_at)
	) $charset_collate;";
	dbDelta( $sql );
}

/**
 * Get table prefix for plugin tables.
 *
 * @return string Table prefix.
 */
function kraft_ai_chat_get_table_prefix() {
	global $wpdb;
	return $wpdb->prefix . 'kraft_ai_chat_';
}

/**
 * Get sessions table name.
 *
 * @return string Table name.
 */
function kraft_ai_chat_sessions_table() {
	return kraft_ai_chat_get_table_prefix() . 'sessions';
}

/**
 * Get messages table name.
 *
 * @return string Table name.
 */
function kraft_ai_chat_messages_table() {
	return kraft_ai_chat_get_table_prefix() . 'messages';
}
