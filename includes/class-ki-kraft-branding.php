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
		// Get branding from new settings structure first, fallback to old individual options
		$branding_settings = get_option( 'kraft_ai_chat_branding', array() );
		
		return array(
			'logo_url'            => $branding_settings['logo_url'] ?? get_option( 'ki_kraft_logo_url', '' ),
			'product_name'        => $branding_settings['product_name'] ?? get_option( 'ki_kraft_product_name', 'KI Kraft' ),
			'primary_color'       => $branding_settings['primary_color'] ?? get_option( 'ki_kraft_primary_color', '#3b82f6' ),
			'secondary_color'     => $branding_settings['secondary_color'] ?? get_option( 'ki_kraft_secondary_color', '#60a5fa' ),
			'theme'               => $branding_settings['theme'] ?? 'auto',
			'icon_color'          => $branding_settings['icon_color'] ?? get_option( 'ki_kraft_icon_color', '#3b82f6' ),
			'header_text_color'   => $branding_settings['header_text_color'] ?? get_option( 'ki_kraft_header_text_color', '#111827' ),
			'faq_header_title'    => $branding_settings['faq_header_title'] ?? 'Häufige Fragen',
			'advisor_header_title' => $branding_settings['advisor_header_title'] ?? 'Mitglieder-Chat',
			'favicon_url'         => $branding_settings['favicon_url'] ?? get_option( 'ki_kraft_favicon_url', '' ),
			'footer_text'         => $branding_settings['footer_text'] ?? get_option( 'ki_kraft_footer_text', '' ),
			'privacy_url'         => $branding_settings['privacy_url'] ?? get_option( 'ki_kraft_privacy_url', '' ),
			'imprint_url'         => $branding_settings['imprint_url'] ?? get_option( 'ki_kraft_imprint_url', '' ),
			'powered_by'          => $branding_settings['powered_by'] ?? get_option( 'ki_kraft_powered_by', true ),
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
