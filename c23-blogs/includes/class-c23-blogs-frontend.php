<?php
/**
 * Frontend and Admin Assets Handler for C23 Blogs.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class C23_Blogs_Frontend {

	/**
	 * Initialize asset hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue frontend CSS and inline custom styles.
	 */
	public static function enqueue_frontend_assets() {
		$settings = C23_Blogs_Settings::get_all();

		// Enqueue Google Fonts if needed
		self::enqueue_google_fonts( $settings );

		// Enqueue modern frontend stylesheet
		wp_enqueue_style(
			'c23-blogs-frontend',
			C23_BLOGS_URL . 'assets/css/frontend.css',
			array(),
			C23_BLOGS_VERSION
		);

		// Dynamic styles based on settings
		$primary      = ! empty( $settings['primary_color'] ) ? $settings['primary_color'] : '#2563eb';
		$title_color  = ! empty( $settings['title_color'] ) ? $settings['title_color'] : '#1e293b';
		$text_color   = ! empty( $settings['text_color'] ) ? $settings['text_color'] : '#64748b';
		$card_bg      = ! empty( $settings['card_bg'] ) ? $settings['card_bg'] : '#ffffff';
		$radius       = isset( $settings['card_radius'] ) ? (int) $settings['card_radius'] : 12;

		// Typography
		$title_font   = self::get_font_stack( $settings['title_font_family'] ?? 'inherit' );
		$body_font    = self::get_font_stack( $settings['body_font_family'] ?? 'inherit' );
		$title_weight = ! empty( $settings['title_font_weight'] ) ? (int) $settings['title_font_weight'] : 700;
		$card_title_sz   = ! empty( $settings['card_title_size'] ) ? (int) $settings['card_title_size'] : 20;
		$single_title_sz = ! empty( $settings['single_title_size'] ) ? (int) $settings['single_title_size'] : 38;
		$body_sz         = ! empty( $settings['body_font_size'] ) ? (int) $settings['body_font_size'] : 16;
		$body_lh         = ! empty( $settings['body_line_height'] ) ? (float) $settings['body_line_height'] : 1.6;

		// Shadow
		$shadow_val = '0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03)';
		if ( isset( $settings['card_shadow'] ) ) {
			switch ( $settings['card_shadow'] ) {
				case 'none':
					$shadow_val = 'none';
					break;
				case 'medium':
					$shadow_val = '0 4px 16px -2px rgba(0, 0, 0, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04)';
					break;
				case 'elevated':
					$shadow_val = '0 12px 30px -4px rgba(0, 0, 0, 0.12), 0 4px 10px -2px rgba(0, 0, 0, 0.06)';
					break;
			}
		}

		$custom_css = "
			:root {
				--c23-primary: {$primary};
				--c23-title-color: {$title_color};
				--c23-text-color: {$text_color};
				--c23-card-bg: {$card_bg};
				--c23-radius: {$radius}px;
				--c23-card-shadow: {$shadow_val};
				--c23-title-font: {$title_font};
				--c23-title-weight: {$title_weight};
				--c23-card-title-size: {$card_title_sz}px;
				--c23-single-title-size: {$single_title_sz}px;
				--c23-body-font: {$body_font};
				--c23-body-size: {$body_sz}px;
				--c23-body-line-height: {$body_lh};
			}
		";

		wp_add_inline_style( 'c23-blogs-frontend', $custom_css );
	}

	/**
	 * Map font name to full CSS font-family stack.
	 *
	 * @param string $font
	 * @return string
	 */
	private static function get_font_stack( $font ) {
		switch ( $font ) {
			case 'Inter':
				return "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
			case 'Outfit':
				return "'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
			case 'Montserrat':
				return "'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
			case 'Playfair Display':
				return "'Playfair Display', Georgia, serif";
			case 'Merriweather':
				return "'Merriweather', Georgia, serif";
			case 'Roboto':
				return "'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
			case 'Open Sans':
				return "'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
			case 'Georgia':
				return "Georgia, 'Times New Roman', serif";
			default:
				return "inherit";
		}
	}

	/**
	 * Enqueue Google Fonts if selected.
	 *
	 * @param array $settings
	 */
	private static function enqueue_google_fonts( $settings ) {
		$google_fonts = array( 'Inter', 'Outfit', 'Montserrat', 'Playfair Display', 'Merriweather', 'Roboto', 'Open Sans' );
		$to_load      = array();

		$title_f = $settings['title_font_family'] ?? '';
		$body_f  = $settings['body_font_family'] ?? '';

		if ( in_array( $title_f, $google_fonts, true ) ) {
			$to_load[] = $title_f;
		}
		if ( in_array( $body_f, $google_fonts, true ) ) {
			$to_load[] = $body_f;
		}

		$to_load = array_unique( $to_load );
		if ( empty( $to_load ) ) {
			return;
		}

		$font_queries = array();
		foreach ( $to_load as $font ) {
			$font_name = str_replace( ' ', '+', $font );
			$font_queries[] = "family={$font_name}:wght@400;500;600;700;800";
		}

		$font_url = 'https://fonts.googleapis.com/css2?' . implode( '&', $font_queries ) . '&display=swap';

		wp_enqueue_style( 'c23-blogs-google-fonts', $font_url, array(), null );
	}

	/**
	 * Enqueue admin styles and scripts for settings page.
	 *
	 * @param string $hook
	 */
	public static function enqueue_admin_assets( $hook ) {
		// Only enqueue on our plugin's settings page
		if ( strpos( $hook, 'c23-blogs-settings' ) === false ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'c23-blogs-admin',
			C23_BLOGS_URL . 'assets/css/admin.css',
			array( 'wp-color-picker' ),
			C23_BLOGS_VERSION
		);

		wp_enqueue_script(
			'c23-blogs-admin',
			C23_BLOGS_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			C23_BLOGS_VERSION,
			true
		);
	}
}
