<?php
/**
 * Privacy and GDPR compliance handler.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles GDPR compliance, data export, and data erasure.
 */
class KI_Kraft_Privacy {

	/**
	 * Initialize privacy hooks.
	 */
	public static function init() {
		// Register data exporter
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );

		// Register data eraser
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );

		// Schedule retention cleanup
		if ( ! wp_next_scheduled( 'ki_kraft_retention_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'ki_kraft_retention_cleanup' );
		}
		add_action( 'ki_kraft_retention_cleanup', array( __CLASS__, 'cleanup_old_data' ) );
	}

	/**
	 * Register data exporter.
	 */
	public static function register_exporter( $exporters ) {
		$exporters['kraft-ai-chat'] = array(
			'exporter_friendly_name' => __( 'Kraft AI Chat Data', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'callback'               => array( __CLASS__, 'export_user_data' ),
		);
		return $exporters;
	}

	/**
	 * Register data eraser.
	 */
	public static function register_eraser( $erasers ) {
		$erasers['kraft-ai-chat'] = array(
			'eraser_friendly_name' => __( 'Kraft AI Chat Data', KRAFT_AI_CHAT_TEXTDOMAIN ),
			'callback'             => array( __CLASS__, 'erase_user_data' ),
		);
		return $erasers;
	}

	/**
	 * Export user data for GDPR.
	 */
	public static function export_user_data( $email_address, $page = 1 ) {
		// TODO: Export sessions, messages, and upload metadata
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	/**
	 * Erase user data for GDPR.
	 */
	public static function erase_user_data( $email_address, $page = 1 ) {
		// TODO: Delete sessions, messages, and upload metadata
		return array(
			'items_removed'  => 0,
			'items_retained' => 0,
			'messages'       => array(),
			'done'           => true,
		);
	}

	/**
	 * Cleanup old data based on retention policy.
	 */
	public static function cleanup_old_data() {
		// TODO: Delete data older than retention period
	}
}

// Initialize privacy hooks
KI_Kraft_Privacy::init();
