<?php
/**
 * REST API handler for plugin settings.
 *
 * @package KI_Kraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings REST API endpoints.
 */
class Kraft_AI_Chat_Settings_REST {

	/**
	 * Register settings REST API routes.
	 */
	public static function register_routes() {
		$groups = array( 'general', 'privacy', 'branding', 'knowledge', 'analytics', 'integrations', 'accounts' );

		foreach ( $groups as $group ) {
			register_rest_route(
				KRAFT_AI_CHAT_REST_NS,
				'/settings/' . $group,
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( __CLASS__, 'get_settings' ),
						'permission_callback' => array( __CLASS__, 'check_permissions' ),
						'args'                => array(
							'group' => array(
								'required' => false,
								'default'  => $group,
							),
						),
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( __CLASS__, 'update_settings' ),
						'permission_callback' => array( __CLASS__, 'check_permissions' ),
						'args'                => self::get_schema_for_group( $group ),
					),
				)
			);
		}
	}

	/**
	 * Check if user has permission to manage settings.
	 */
	public static function check_permissions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get settings for a group.
	 */
	public static function get_settings( $request ) {
		$route = $request->get_route();
		preg_match( '/\/settings\/([a-z_]+)$/', $route, $matches );
		$group = $matches[1] ?? 'general';

		$option_name = 'kraft_ai_chat_' . $group;
		$defaults    = self::get_defaults_for_group( $group );
		$settings    = get_option( $option_name, $defaults );

		// Ensure all default keys exist
		$settings = wp_parse_args( $settings, $defaults );

		return rest_ensure_response( $settings );
	}

	/**
	 * Update settings for a group.
	 */
	public static function update_settings( $request ) {
		$route = $request->get_route();
		preg_match( '/\/settings\/([a-z_]+)$/', $route, $matches );
		$group = $matches[1] ?? 'general';

		$option_name = 'kraft_ai_chat_' . $group;
		$params      = $request->get_json_params();

		if ( empty( $params ) ) {
			return new WP_Error(
				'invalid_data',
				__( 'No settings data provided.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				array( 'status' => 400 )
			);
		}

		// Sanitize settings
		$sanitized = self::sanitize_settings( $params, $group );

		if ( is_wp_error( $sanitized ) ) {
			return $sanitized;
		}

		// Update option
		$updated = update_option( $option_name, $sanitized );

		if ( ! $updated && get_option( $option_name ) !== $sanitized ) {
			return new WP_Error(
				'update_failed',
				__( 'Failed to update settings.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				array( 'status' => 500 )
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $sanitized,
			)
		);
	}

	/**
	 * Get default values for a settings group (public for use by Core).
	 */
	public static function get_defaults_for_group( $group ) {
		$defaults = array(
			'general'   => array(
				'site_enabled'       => true,
				'faq_enabled'        => false,
				'advisor_enabled'    => false,
				'max_message_length' => 1000,
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
				'logo_url'           => '',
				'product_name'       => 'KI Kraft',
				'primary_color'      => '#3b82f6',
				'secondary_color'    => '#60a5fa',
				'theme'              => 'auto',
				'icon_color'         => '#3b82f6',
				'header_text_color'  => '#111827',
				'faq_header_title'   => 'Häufige Fragen',
				'advisor_header_title' => 'Mitglieder-Chat',
				'favicon_url'        => '',
				'footer_text'        => '',
				'privacy_url'        => '',
				'imprint_url'        => '',
				'powered_by'         => true,
			),
			'knowledge' => array(
				'chunk_max_tokens'     => 500,
				'chunk_overlap'        => 50,
				'similarity_threshold' => 0.7,
				'max_results'          => 5,
			),
			'analytics' => array(
				'enabled'                => true,
				'retention_days'         => 90,
				'anonymize_ip'           => true,
				'track_feedback'         => true,
				'collect_local_analytics' => false,
			),
			'integrations' => array(
				'openai_api_key'  => '',
				'whisper_api_key' => '',
				'rag_service'     => '',
				'rag_endpoint'    => '',
			),
			'accounts' => array(
				'account_page_id'      => 0,
				'profile_url'          => '',
				'profile_url_override' => '',
			),
		);

		return $defaults[ $group ] ?? array();
	}

	/**
	 * Get schema for a settings group.
	 */
	private static function get_schema_for_group( $group ) {
		$schemas = array(
			'general'   => array(
				'site_enabled'       => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'faq_enabled'        => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'advisor_enabled'    => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'max_message_length' => array(
					'type'              => 'integer',
					'minimum'           => 50,
					'maximum'           => 4000,
					'sanitize_callback' => 'absint',
				),
				'default_lang'       => array(
					'type'              => 'string',
					'enum'              => array( 'de', 'en', 'fr', 'es', 'it' ),
					'sanitize_callback' => 'sanitize_text_field',
				),
				'cache_enabled'      => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'cache_ttl'          => array(
					'type'              => 'integer',
					'minimum'           => 60,
					'maximum'           => 604800,
					'sanitize_callback' => 'absint',
				),
				'rate_limit_enabled' => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'rate_limit_max'     => array(
					'type'              => 'integer',
					'minimum'           => 10,
					'maximum'           => 1000,
					'sanitize_callback' => 'absint',
				),
				'rate_limit_window'  => array(
					'type'              => 'integer',
					'minimum'           => 60,
					'maximum'           => 86400,
					'sanitize_callback' => 'absint',
				),
			),
			'privacy'   => array(
				'retention_enabled'      => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'retention_days'         => array(
					'type'              => 'integer',
					'minimum'           => 0,
					'maximum'           => 3650,
					'sanitize_callback' => 'absint',
				),
				'external_ai_enabled'    => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'consent_required'       => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'data_export_enabled'    => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'data_erase_enabled'     => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'collect_local_analytics' => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
			),
			'branding'  => array(
				'logo_url'           => array(
					'type'              => 'string',
					'format'            => 'uri',
					'sanitize_callback' => 'esc_url_raw',
				),
				'product_name'       => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'primary_color'      => array(
					'type'              => 'string',
					'pattern'           => '^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$',
					'sanitize_callback' => 'sanitize_hex_color',
				),
				'secondary_color'    => array(
					'type'              => 'string',
					'pattern'           => '^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$',
					'sanitize_callback' => 'sanitize_hex_color',
				),
				'theme'              => array(
					'type'              => 'string',
					'enum'              => array( 'light', 'dark', 'auto' ),
					'sanitize_callback' => 'sanitize_text_field',
				),
				'icon_color'         => array(
					'type'              => 'string',
					'pattern'           => '^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$',
					'sanitize_callback' => 'sanitize_hex_color',
				),
				'header_text_color'  => array(
					'type'              => 'string',
					'pattern'           => '^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$',
					'sanitize_callback' => 'sanitize_hex_color',
				),
				'faq_header_title'   => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'advisor_header_title' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'favicon_url'        => array(
					'type'              => 'string',
					'format'            => 'uri',
					'sanitize_callback' => 'esc_url_raw',
				),
				'footer_text'        => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'privacy_url'        => array(
					'type'              => 'string',
					'format'            => 'uri',
					'sanitize_callback' => 'esc_url_raw',
				),
				'imprint_url'        => array(
					'type'              => 'string',
					'format'            => 'uri',
					'sanitize_callback' => 'esc_url_raw',
				),
				'powered_by'         => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
			),
			'knowledge' => array(
				'chunk_max_tokens'  => array(
					'type'              => 'integer',
					'minimum'           => 100,
					'maximum'           => 2000,
					'sanitize_callback' => 'absint',
				),
				'chunk_overlap'     => array(
					'type'              => 'integer',
					'minimum'           => 0,
					'maximum'           => 500,
					'sanitize_callback' => 'absint',
				),
				'similarity_threshold' => array(
					'type'              => 'number',
					'minimum'           => 0,
					'maximum'           => 1,
				),
				'max_results'       => array(
					'type'              => 'integer',
					'minimum'           => 1,
					'maximum'           => 20,
					'sanitize_callback' => 'absint',
				),
			),
			'analytics' => array(
				'enabled'         => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'retention_days'  => array(
					'type'              => 'integer',
					'minimum'           => 7,
					'maximum'           => 365,
					'sanitize_callback' => 'absint',
				),
				'anonymize_ip'    => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'track_feedback'  => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'collect_local_analytics' => array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
			),
			'integrations' => array(
				'openai_api_key'  => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'whisper_api_key' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'rag_service'     => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'rag_endpoint'    => array(
					'type'              => 'string',
					'format'            => 'uri',
					'sanitize_callback' => 'esc_url_raw',
				),
			),
			'accounts' => array(
				'account_page_id'      => array(
					'type'              => 'integer',
					'minimum'           => 0,
					'sanitize_callback' => 'absint',
				),
				'profile_url'          => array(
					'type'              => 'string',
					'format'            => 'uri',
					'sanitize_callback' => 'esc_url_raw',
				),
				'profile_url_override' => array(
					'type'              => 'string',
					'format'            => 'uri',
					'sanitize_callback' => 'esc_url_raw',
				),
			),
		);

		return $schemas[ $group ] ?? array();
	}

	/**
	 * Sanitize settings based on group schema.
	 */
	private static function sanitize_settings( $settings, $group ) {
		$schema    = self::get_schema_for_group( $group );
		$sanitized = array();
		$errors    = array();

		foreach ( $settings as $key => $value ) {
			if ( ! isset( $schema[ $key ] ) ) {
				continue;
			}

			$field_schema = $schema[ $key ];

			// Apply sanitization callback
			if ( isset( $field_schema['sanitize_callback'] ) ) {
				$sanitized[ $key ] = call_user_func( $field_schema['sanitize_callback'], $value );
			} else {
				$sanitized[ $key ] = $value;
			}

			// Validate type
			if ( isset( $field_schema['type'] ) ) {
				$valid = self::validate_type( $sanitized[ $key ], $field_schema['type'] );
				if ( ! $valid ) {
					$errors[ $key ] = sprintf(
						/* translators: 1: field name, 2: expected type */
						__( 'Field %1$s must be of type %2$s.', KRAFT_AI_CHAT_TEXTDOMAIN ),
						$key,
						$field_schema['type']
					);
				}
			}

			// Validate minimum
			if ( isset( $field_schema['minimum'] ) && is_numeric( $sanitized[ $key ] ) ) {
				if ( $sanitized[ $key ] < $field_schema['minimum'] ) {
					$errors[ $key ] = sprintf(
						/* translators: 1: field name, 2: minimum value */
						__( 'Field %1$s must be at least %2$s.', KRAFT_AI_CHAT_TEXTDOMAIN ),
						$key,
						$field_schema['minimum']
					);
				}
			}

			// Validate maximum
			if ( isset( $field_schema['maximum'] ) && is_numeric( $sanitized[ $key ] ) ) {
				if ( $sanitized[ $key ] > $field_schema['maximum'] ) {
					$errors[ $key ] = sprintf(
						/* translators: 1: field name, 2: maximum value */
						__( 'Field %1$s must be at most %2$s.', KRAFT_AI_CHAT_TEXTDOMAIN ),
						$key,
						$field_schema['maximum']
					);
				}
			}

			// Validate pattern
			if ( isset( $field_schema['pattern'] ) && is_string( $sanitized[ $key ] ) ) {
				if ( ! preg_match( '/' . $field_schema['pattern'] . '/', $sanitized[ $key ] ) ) {
					$errors[ $key ] = sprintf(
						/* translators: %s: field name */
						__( 'Field %s has invalid format.', KRAFT_AI_CHAT_TEXTDOMAIN ),
						$key
					);
				}
			}

			// Validate enum
			if ( isset( $field_schema['enum'] ) && ! in_array( $sanitized[ $key ], $field_schema['enum'], true ) ) {
				$errors[ $key ] = sprintf(
					/* translators: %s: field name */
					__( 'Field %s has invalid value.', KRAFT_AI_CHAT_TEXTDOMAIN ),
					$key
				);
			}
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error(
				'validation_failed',
				__( 'Settings validation failed.', KRAFT_AI_CHAT_TEXTDOMAIN ),
				array(
					'status' => 400,
					'errors' => $errors,
				)
			);
		}

		return $sanitized;
	}

	/**
	 * Validate value type.
	 */
	private static function validate_type( $value, $type ) {
		switch ( $type ) {
			case 'boolean':
				return is_bool( $value );
			case 'integer':
				return is_int( $value );
			case 'number':
				return is_numeric( $value );
			case 'string':
				return is_string( $value );
			default:
				return true;
		}
	}
}
