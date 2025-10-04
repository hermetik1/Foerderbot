<?php
/**
 * White-label branding handler.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages white-label branding configuration.
 */
class KI_Kraft_Branding {

	/**
	 * Get branding configuration.
	 *
	 * @return array
	 */
	public static function get_config() {
		return array(
			'logo_url'       => get_option( 'ki_kraft_logo_url', '' ),
			'product_name'   => get_option( 'ki_kraft_product_name', 'KI Kraft' ),
			'primary_color'  => get_option( 'ki_kraft_primary_color', '#3b82f6' ),
			'secondary_color' => get_option( 'ki_kraft_secondary_color', '#60a5fa' ),
			'favicon_url'    => get_option( 'ki_kraft_favicon_url', '' ),
			'footer_text'    => get_option( 'ki_kraft_footer_text', '' ),
			'privacy_url'    => get_option( 'ki_kraft_privacy_url', '' ),
			'imprint_url'    => get_option( 'ki_kraft_imprint_url', '' ),
			'powered_by'     => get_option( 'ki_kraft_powered_by', true ),
		);
	}

	/**
	 * Update branding configuration.
	 *
	 * @param array $config Configuration array.
	 * @return bool
	 */
	public static function update_config( $config ) {
		$updated = true;

		foreach ( $config as $key => $value ) {
			$option_name = 'ki_kraft_' . $key;
			$updated     = update_option( $option_name, $value ) && $updated;
		}

		return $updated;
	}

	/**
	 * Export branding configuration as JSON.
	 *
	 * @return string
	 */
	public static function export_config() {
		return wp_json_encode( self::get_config(), JSON_PRETTY_PRINT );
	}

	/**
	 * Import branding configuration from JSON.
	 *
	 * @param string $json JSON string.
	 * @return bool
	 */
	public static function import_config( $json ) {
		$config = json_decode( $json, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return false;
		}

		return self::update_config( $config );
	}
}
