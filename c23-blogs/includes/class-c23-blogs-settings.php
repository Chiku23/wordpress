<?php
/**
 * Settings Handler for C23 Blogs.
 *
 * Provides a unified, well-organized tabbed settings page in the WP admin.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class C23_Blogs_Settings {

	/**
	 * Option name in WordPress options table.
	 */
	const OPTION_NAME = 'c23_blogs_settings';

	/**
	 * Settings cache.
	 *
	 * @var array|null
	 */
	private static $settings = null;

	/**
	 * Initialize settings hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'update_option_' . self::OPTION_NAME, array( __CLASS__, 'on_settings_updated' ), 10, 2 );
	}

	/**
	 * Automatically flush rewrite rules when archive_slug is changed.
	 *
	 * @param array $old_value
	 * @param array $value
	 */
	public static function on_settings_updated( $old_value, $value ) {
		$old_slug = isset( $old_value['archive_slug'] ) ? sanitize_title( $old_value['archive_slug'] ) : 'blogs';
		$new_slug = isset( $value['archive_slug'] ) ? sanitize_title( $value['archive_slug'] ) : 'blogs';
		if ( $old_slug !== $new_slug ) {
			C23_Blogs_Post_Type::register_post_type();
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Get all settings with defaults and backward compatibility.
	 *
	 * @return array
	 */
	public static function get_all() {
		if ( null !== self::$settings ) {
			return self::$settings;
		}

		$defaults = array(
			// Archive Page Header & Permalinks
			'archive_slug'        => 'blogs',
			'archive_title'       => 'Blogs',
			'archive_subtitle'    => 'Explore our latest articles, insights, and stories.',
			// Layout & Query
			'layout_view'         => 'grid',
			'grid_columns'        => '3',
			'posts_per_page'      => '9',
			'excerpt_length'      => '28',
			'excerpt_more'        => '...',
			// Metadata
			'show_reading_time'   => '1',
			'show_author'         => '1',
			'show_date'           => '1',
			'show_categories'     => '1',
			// Single Post Experience
			'show_author_box'     => '1',
			'show_post_nav'       => '1',
			'show_comments'       => '1',
			// Typography
			'title_font_family'   => 'inherit',
			'title_font_weight'   => '700',
			'card_title_size'     => '20',
			'single_title_size'   => '38',
			'body_font_family'    => 'inherit',
			'body_font_size'      => '16',
			'body_line_height'    => '1.6',
			// Colors & Styling
			'primary_color'       => '#2563eb',
			'title_color'         => '#1e293b',
			'text_color'          => '#64748b',
			'card_bg'             => '#ffffff',
			'card_radius'         => '12',
			'card_shadow'         => 'subtle',
		);

		$saved = get_option( self::OPTION_NAME, array() );

		// Fallback to legacy options if not saved yet in new unified array
		if ( empty( $saved ) ) {
			$legacy_blog_settings = get_option( 'BlogSetting', array() );
			$legacy_css_settings  = get_option( 'CSSsetting', array() );

			if ( ! empty( $legacy_blog_settings ) || ! empty( $legacy_css_settings ) ) {
				$saved = array();

				if ( ! empty( $legacy_blog_settings['blogsPageViewID'] ) ) {
					$saved['layout_view'] = sanitize_text_field( $legacy_blog_settings['blogsPageViewID'] );
				} elseif ( ! empty( $legacy_blog_settings['c23_blogsPageView'] ) ) {
					$saved['layout_view'] = sanitize_text_field( $legacy_blog_settings['c23_blogsPageView'] );
				}

				if ( ! empty( $legacy_blog_settings['blogsGridColumnID'] ) ) {
					$saved['grid_columns'] = sanitize_text_field( $legacy_blog_settings['blogsGridColumnID'] );
				} elseif ( ! empty( $legacy_blog_settings['c23_blogsGridColumn'] ) ) {
					$saved['grid_columns'] = sanitize_text_field( $legacy_blog_settings['c23_blogsGridColumn'] );
				}

				if ( ! empty( $legacy_css_settings['TitleTextcolor'] ) ) {
					$saved['title_color'] = sanitize_hex_color( $legacy_css_settings['TitleTextcolor'] );
				}
				if ( ! empty( $legacy_css_settings['DescriptionTextColor'] ) ) {
					$saved['text_color'] = sanitize_hex_color( $legacy_css_settings['DescriptionTextColor'] );
				}
			}
		}

		self::$settings = wp_parse_args( $saved, $defaults );
		return self::$settings;
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$settings = self::get_all();
		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}
		return $default;
	}

	/**
	 * Register the admin menu page under Blogs post type.
	 */
	public static function register_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=c23_blogs',
			__( 'Blog Settings', 'c23-blogs' ),
			__( 'Settings', 'c23-blogs' ),
			'manage_options',
			'c23-blogs-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register settings and sections.
	 */
	public static function register_settings() {
		register_setting(
			'c23_blogs_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * Sanitize all submitted settings.
	 *
	 * @param array $input
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$sanitized = array();

		// Archive Header & Permalinks
		$slug = ! empty( $input['archive_slug'] ) ? sanitize_title( $input['archive_slug'] ) : 'blogs';
		$sanitized['archive_slug']     = ! empty( $slug ) ? $slug : 'blogs';
		$sanitized['archive_title']    = ! empty( $input['archive_title'] ) ? sanitize_text_field( $input['archive_title'] ) : 'Blogs';
		$sanitized['archive_subtitle'] = isset( $input['archive_subtitle'] ) ? sanitize_textarea_field( $input['archive_subtitle'] ) : '';

		// Layout & Query
		$valid_layouts = array( 'grid', 'list' );
		$sanitized['layout_view'] = ( isset( $input['layout_view'] ) && in_array( $input['layout_view'], $valid_layouts, true ) ) ? $input['layout_view'] : 'grid';

		$valid_cols = array( '2', '3', '4' );
		$sanitized['grid_columns'] = ( isset( $input['grid_columns'] ) && in_array( (string) $input['grid_columns'], $valid_cols, true ) ) ? (string) $input['grid_columns'] : '3';

		$sanitized['posts_per_page'] = isset( $input['posts_per_page'] ) ? max( 1, min( 50, (int) $input['posts_per_page'] ) ) : 9;
		$sanitized['excerpt_length'] = isset( $input['excerpt_length'] ) ? max( 5, min( 100, (int) $input['excerpt_length'] ) ) : 28;
		$sanitized['excerpt_more']   = isset( $input['excerpt_more'] ) ? sanitize_text_field( $input['excerpt_more'] ) : '...';

		// Metadata Toggles
		$sanitized['show_reading_time'] = ! empty( $input['show_reading_time'] ) ? '1' : '0';
		$sanitized['show_author']       = ! empty( $input['show_author'] ) ? '1' : '0';
		$sanitized['show_date']         = ! empty( $input['show_date'] ) ? '1' : '0';
		$sanitized['show_categories']   = ! empty( $input['show_categories'] ) ? '1' : '0';

		// Single Post Experience
		$sanitized['show_author_box'] = ! empty( $input['show_author_box'] ) ? '1' : '0';
		$sanitized['show_post_nav']   = ! empty( $input['show_post_nav'] ) ? '1' : '0';
		$sanitized['show_comments']   = ! empty( $input['show_comments'] ) ? '1' : '0';

		// Typography
		$valid_title_fonts = array( 'inherit', 'Inter', 'Roboto', 'Outfit', 'Montserrat', 'Playfair Display', 'Merriweather', 'Georgia' );
		$sanitized['title_font_family'] = ( isset( $input['title_font_family'] ) && in_array( $input['title_font_family'], $valid_title_fonts, true ) ) ? $input['title_font_family'] : 'inherit';

		$valid_weights = array( '500', '600', '700', '800' );
		$sanitized['title_font_weight'] = ( isset( $input['title_font_weight'] ) && in_array( (string) $input['title_font_weight'], $valid_weights, true ) ) ? (string) $input['title_font_weight'] : '700';

		$sanitized['card_title_size']   = isset( $input['card_title_size'] ) ? max( 14, min( 36, (int) $input['card_title_size'] ) ) : 20;
		$sanitized['single_title_size'] = isset( $input['single_title_size'] ) ? max( 22, min( 60, (int) $input['single_title_size'] ) ) : 38;

		$valid_body_fonts = array( 'inherit', 'Inter', 'Roboto', 'Open Sans', 'Merriweather', 'Georgia' );
		$sanitized['body_font_family'] = ( isset( $input['body_font_family'] ) && in_array( $input['body_font_family'], $valid_body_fonts, true ) ) ? $input['body_font_family'] : 'inherit';

		$sanitized['body_font_size'] = isset( $input['body_font_size'] ) ? max( 12, min( 24, (int) $input['body_font_size'] ) ) : 16;

		$valid_line_heights = array( '1.4', '1.5', '1.6', '1.7', '1.8' );
		$sanitized['body_line_height'] = ( isset( $input['body_line_height'] ) && in_array( (string) $input['body_line_height'], $valid_line_heights, true ) ) ? (string) $input['body_line_height'] : '1.6';

		// Styling colors
		$sanitized['primary_color'] = ! empty( $input['primary_color'] ) ? sanitize_hex_color( $input['primary_color'] ) : '#2563eb';
		$sanitized['title_color']   = ! empty( $input['title_color'] ) ? sanitize_hex_color( $input['title_color'] ) : '#1e293b';
		$sanitized['text_color']    = ! empty( $input['text_color'] ) ? sanitize_hex_color( $input['text_color'] ) : '#64748b';
		$sanitized['card_bg']       = ! empty( $input['card_bg'] ) ? sanitize_hex_color( $input['card_bg'] ) : '#ffffff';
		$sanitized['card_radius']   = isset( $input['card_radius'] ) ? max( 0, min( 40, (int) $input['card_radius'] ) ) : 12;

		$valid_shadows = array( 'none', 'subtle', 'medium', 'elevated' );
		$sanitized['card_shadow'] = ( isset( $input['card_shadow'] ) && in_array( $input['card_shadow'], $valid_shadows, true ) ) ? $input['card_shadow'] : 'subtle';

		// Clear static cache
		self::$settings = null;

		return $sanitized;
	}

	/**
	 * Render the modern tabbed settings page with distributed sections.
	 */
	public static function render_settings_page() {
		$options = self::get_all();
		?>
		<div class="wrap c23-blogs-admin-wrap">
			<form method="post" action="options.php" id="c23-blogs-settings-form" class="c23-blogs-form">
				<?php settings_fields( 'c23_blogs_settings_group' ); ?>

				<div class="c23-blogs-header">
					<div class="c23-blogs-header-title">
						<span class="dashicons dashicons-welcome-write-blog c23-header-icon"></span>
						<div>
							<h1><?php esc_html_e( 'Blogs Settings', 'c23-blogs' ); ?></h1>
							<p class="c23-subtitle"><?php esc_html_e( 'Customize your blog archive, single post experience, typography, and colors.', 'c23-blogs' ); ?></p>
						</div>
					</div>
					<div class="c23-header-actions">
						<div class="c23-badge"><?php esc_html_e( 'Version 2.0', 'c23-blogs' ); ?></div>
						<button type="submit" class="button button-primary c23-submit-btn">
							<span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Changes', 'c23-blogs' ); ?>
						</button>
					</div>
				</div>

				<div class="c23-tabs-container">
					<nav class="c23-tabs-nav" role="tablist">
						<button type="button" class="c23-tab-btn active" data-tab="tab-archive">
							<span class="dashicons dashicons-layout"></span> <?php esc_html_e( 'Archive & Layout', 'c23-blogs' ); ?>
						</button>
						<button type="button" class="c23-tab-btn" data-tab="tab-single">
							<span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Single Post', 'c23-blogs' ); ?>
						</button>
						<button type="button" class="c23-tab-btn" data-tab="tab-meta">
							<span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Metadata & Badges', 'c23-blogs' ); ?>
						</button>
						<button type="button" class="c23-tab-btn" data-tab="tab-typography">
							<span class="dashicons dashicons-editor-textcolor"></span> <?php esc_html_e( 'Typography', 'c23-blogs' ); ?>
						</button>
						<button type="button" class="c23-tab-btn" data-tab="tab-styles">
							<span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Colors & Styling', 'c23-blogs' ); ?>
						</button>
					</nav>

					<!-- Tab 1: Archive & Layout -->
					<div class="c23-tab-content active" id="tab-archive">
						<div class="c23-panel">

							<!-- Section: Archive Header -->
							<div class="c23-section-header">
								<span class="dashicons dashicons-heading"></span>
								<h3><?php esc_html_e( 'Archive Page Header', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Header Display', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_archive_title"><?php esc_html_e( 'Archive Page Title', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Main heading displayed at the top of the blog archive page. Default: Blogs', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="text" id="c23_archive_title" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[archive_title]" value="<?php echo esc_attr( $options['archive_title'] ); ?>" class="c23-input-text" placeholder="Blogs" />
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_archive_subtitle"><?php esc_html_e( 'Archive Subtitle / Description', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Subtitle paragraph displayed beneath the archive heading.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<textarea id="c23_archive_subtitle" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[archive_subtitle]" rows="2" class="c23-textarea" placeholder="<?php esc_attr_e( 'Explore our latest articles...', 'c23-blogs' ); ?>"><?php echo esc_textarea( $options['archive_subtitle'] ); ?></textarea>
								</div>
							</div>

							<!-- Section: URL & Permalinks -->
							<div class="c23-section-header">
								<span class="dashicons dashicons-admin-links"></span>
								<h3><?php esc_html_e( 'URL Slug & Permalinks', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Routing', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_archive_slug"><?php esc_html_e( 'Blog Archive URL Slug', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Custom URL permalink slug for the blog archive and post URLs. (e.g. "blogs", "articles", "news"). Default: blogs', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control c23-slug-control">
									<span class="c23-slug-prefix"><?php echo esc_html( home_url( '/' ) ); ?></span>
									<input type="text" id="c23_archive_slug" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[archive_slug]" value="<?php echo esc_attr( $options['archive_slug'] ); ?>" class="c23-input-text c23-slug-input" placeholder="blogs" />
								</div>
							</div>

							<!-- Section: Layout & Cards -->
							<div class="c23-section-header">
								<span class="dashicons dashicons-grid-view"></span>
								<h3><?php esc_html_e( 'Layout & Cards View', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Appearance', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_layout_view"><?php esc_html_e( 'Archive View Layout', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Select whether your blog archive renders as a responsive card grid or horizontal list.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<select id="c23_layout_view" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[layout_view]" class="c23-select">
										<option value="grid" <?php selected( $options['layout_view'], 'grid' ); ?>><?php esc_html_e( 'Grid Layout (Modern Cards)', 'c23-blogs' ); ?></option>
										<option value="list" <?php selected( $options['layout_view'], 'list' ); ?>><?php esc_html_e( 'List Layout (Horizontal Cards)', 'c23-blogs' ); ?></option>
									</select>
								</div>
							</div>

							<div class="c23-field-row" id="row-grid-columns">
								<div class="c23-field-label">
									<label for="c23_grid_columns"><?php esc_html_e( 'Grid Columns', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Number of columns for desktop devices in grid layout.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<select id="c23_grid_columns" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[grid_columns]" class="c23-select">
										<option value="2" <?php selected( $options['grid_columns'], '2' ); ?>><?php esc_html_e( '2 Columns', 'c23-blogs' ); ?></option>
										<option value="3" <?php selected( $options['grid_columns'], '3' ); ?>><?php esc_html_e( '3 Columns (Default)', 'c23-blogs' ); ?></option>
										<option value="4" <?php selected( $options['grid_columns'], '4' ); ?>><?php esc_html_e( '4 Columns', 'c23-blogs' ); ?></option>
									</select>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_posts_per_page"><?php esc_html_e( 'Posts Per Page', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Number of blogs to display per archive page.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="number" id="c23_posts_per_page" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[posts_per_page]" value="<?php echo esc_attr( $options['posts_per_page'] ); ?>" min="1" max="50" class="c23-input-num" />
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_excerpt_length"><?php esc_html_e( 'Excerpt Word Length', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Approximate words in post excerpt previews.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="number" id="c23_excerpt_length" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[excerpt_length]" value="<?php echo esc_attr( $options['excerpt_length'] ); ?>" min="5" max="100" class="c23-input-num" />
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_excerpt_more"><?php esc_html_e( 'Excerpt Ellipsis / More', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Characters displayed at end of excerpt.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="text" id="c23_excerpt_more" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[excerpt_more]" value="<?php echo esc_attr( $options['excerpt_more'] ); ?>" class="c23-input-text" />
								</div>
							</div>

						</div>
					</div>

					<!-- Tab 2: Single Post Experience -->
					<div class="c23-tab-content" id="tab-single">
						<div class="c23-panel">

							<div class="c23-section-header">
								<span class="dashicons dashicons-welcome-write-blog"></span>
								<h3><?php esc_html_e( 'Single Post Features', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Reader Experience', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_show_author_box"><?php esc_html_e( 'Author Bio Box', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Show author bio box with avatar, name, and bio at bottom of single post view.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<label class="c23-switch">
										<input type="checkbox" id="c23_show_author_box" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_author_box]" value="1" <?php checked( $options['show_author_box'], '1' ); ?> />
										<span class="c23-slider"></span>
									</label>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_show_post_nav"><?php esc_html_e( 'Next / Previous Navigation', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Display links to adjacent blogs on single post view.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<label class="c23-switch">
										<input type="checkbox" id="c23_show_post_nav" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_post_nav]" value="1" <?php checked( $options['show_post_nav'], '1' ); ?> />
										<span class="c23-slider"></span>
									</label>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_show_comments"><?php esc_html_e( 'Enable Comments Section', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Allow readers to leave comments on blog posts.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<label class="c23-switch">
										<input type="checkbox" id="c23_show_comments" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_comments]" value="1" <?php checked( $options['show_comments'], '1' ); ?> />
										<span class="c23-slider"></span>
									</label>
								</div>
							</div>

						</div>
					</div>

					<!-- Tab 3: Metadata & Badges -->
					<div class="c23-tab-content" id="tab-meta">
						<div class="c23-panel">

							<div class="c23-section-header">
								<span class="dashicons dashicons-tag"></span>
								<h3><?php esc_html_e( 'Post Metadata Badges', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Visibility', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_show_reading_time"><?php esc_html_e( 'Estimated Reading Time', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Automatically calculate and display reading time based on content length.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<label class="c23-switch">
										<input type="checkbox" id="c23_show_reading_time" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_reading_time]" value="1" <?php checked( $options['show_reading_time'], '1' ); ?> />
										<span class="c23-slider"></span>
									</label>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_show_author"><?php esc_html_e( 'Post Author Meta', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Display the author name and avatar in cards and single view.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<label class="c23-switch">
										<input type="checkbox" id="c23_show_author" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_author]" value="1" <?php checked( $options['show_author'], '1' ); ?> />
										<span class="c23-slider"></span>
									</label>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_show_date"><?php esc_html_e( 'Publication Date', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Show publish date on blog posts.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<label class="c23-switch">
										<input type="checkbox" id="c23_show_date" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_date]" value="1" <?php checked( $options['show_date'], '1' ); ?> />
										<span class="c23-slider"></span>
									</label>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_show_categories"><?php esc_html_e( 'Category Badges', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Display blog category pills on cards and single view.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<label class="c23-switch">
										<input type="checkbox" id="c23_show_categories" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_categories]" value="1" <?php checked( $options['show_categories'], '1' ); ?> />
										<span class="c23-slider"></span>
									</label>
								</div>
							</div>

						</div>
					</div>

					<!-- Tab 4: Typography -->
					<div class="c23-tab-content" id="tab-typography">
						<div class="c23-panel">

							<!-- Section: Title Typography -->
							<div class="c23-section-header">
								<span class="dashicons dashicons-editor-textcolor"></span>
								<h3><?php esc_html_e( 'Blog Post Title Typography', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Headings', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_title_font_family"><?php esc_html_e( 'Title Font Family', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Choose a curated modern font family for all blog post titles.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<select id="c23_title_font_family" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[title_font_family]" class="c23-select">
										<option value="inherit" <?php selected( $options['title_font_family'], 'inherit' ); ?>><?php esc_html_e( 'Theme Default (Inherit)', 'c23-blogs' ); ?></option>
										<option value="Inter" <?php selected( $options['title_font_family'], 'Inter' ); ?>>Inter (Clean Sans)</option>
										<option value="Outfit" <?php selected( $options['title_font_family'], 'Outfit' ); ?>>Outfit (Modern Geometric)</option>
										<option value="Montserrat" <?php selected( $options['title_font_family'], 'Montserrat' ); ?>>Montserrat (Bold Modern)</option>
										<option value="Playfair Display" <?php selected( $options['title_font_family'], 'Playfair Display' ); ?>>Playfair Display (Editorial Serif)</option>
										<option value="Merriweather" <?php selected( $options['title_font_family'], 'Merriweather' ); ?>>Merriweather (Classic Serif)</option>
										<option value="Roboto" <?php selected( $options['title_font_family'], 'Roboto' ); ?>>Roboto (Neutral Sans)</option>
										<option value="Georgia" <?php selected( $options['title_font_family'], 'Georgia' ); ?>>Georgia (System Serif)</option>
									</select>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_title_font_weight"><?php esc_html_e( 'Title Font Weight', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Weight / boldness for blog titles.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<select id="c23_title_font_weight" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[title_font_weight]" class="c23-select">
										<option value="500" <?php selected( $options['title_font_weight'], '500' ); ?>><?php esc_html_e( '500 - Medium', 'c23-blogs' ); ?></option>
										<option value="600" <?php selected( $options['title_font_weight'], '600' ); ?>><?php esc_html_e( '600 - Semi-Bold', 'c23-blogs' ); ?></option>
										<option value="700" <?php selected( $options['title_font_weight'], '700' ); ?>><?php esc_html_e( '700 - Bold (Default)', 'c23-blogs' ); ?></option>
										<option value="800" <?php selected( $options['title_font_weight'], '800' ); ?>><?php esc_html_e( '800 - Extra Bold', 'c23-blogs' ); ?></option>
									</select>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_card_title_size"><?php esc_html_e( 'Card Title Font Size (px)', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Title font size on archive cards. Default: 20px', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="number" id="c23_card_title_size" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[card_title_size]" value="<?php echo esc_attr( $options['card_title_size'] ); ?>" min="14" max="36" class="c23-input-num" /> px
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_single_title_size"><?php esc_html_e( 'Single Post Title Font Size (px)', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Main title font size on single blog posts. Default: 38px', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="number" id="c23_single_title_size" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[single_title_size]" value="<?php echo esc_attr( $options['single_title_size'] ); ?>" min="22" max="60" class="c23-input-num" /> px
								</div>
							</div>

							<!-- Section: Body & Excerpt Typography -->
							<div class="c23-section-header">
								<span class="dashicons dashicons-editor-paragraph"></span>
								<h3><?php esc_html_e( 'Body & Excerpt Typography', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Reading Text', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_body_font_family"><?php esc_html_e( 'Body Font Family', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Font family for excerpt previews and single post article content.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<select id="c23_body_font_family" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[body_font_family]" class="c23-select">
										<option value="inherit" <?php selected( $options['body_font_family'], 'inherit' ); ?>><?php esc_html_e( 'Theme Default (Inherit)', 'c23-blogs' ); ?></option>
										<option value="Inter" <?php selected( $options['body_font_family'], 'Inter' ); ?>>Inter (Clean Sans)</option>
										<option value="Roboto" <?php selected( $options['body_font_family'], 'Roboto' ); ?>>Roboto (Neutral Sans)</option>
										<option value="Open Sans" <?php selected( $options['body_font_family'], 'Open Sans' ); ?>>Open Sans (Legible Sans)</option>
										<option value="Merriweather" <?php selected( $options['body_font_family'], 'Merriweather' ); ?>>Merriweather (Classic Serif)</option>
										<option value="Georgia" <?php selected( $options['body_font_family'], 'Georgia' ); ?>>Georgia (System Serif)</option>
									</select>
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_body_font_size"><?php esc_html_e( 'Body Text Font Size (px)', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Base font size for excerpts and article reading text. Default: 16px', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="number" id="c23_body_font_size" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[body_font_size]" value="<?php echo esc_attr( $options['body_font_size'] ); ?>" min="12" max="24" class="c23-input-num" /> px
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_body_line_height"><?php esc_html_e( 'Body Line Height', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Line spacing for comfortable reading.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<select id="c23_body_line_height" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[body_line_height]" class="c23-select">
										<option value="1.4" <?php selected( $options['body_line_height'], '1.4' ); ?>>1.4 (Compact)</option>
										<option value="1.5" <?php selected( $options['body_line_height'], '1.5' ); ?>>1.5 (Snug)</option>
										<option value="1.6" <?php selected( $options['body_line_height'], '1.6' ); ?>>1.6 (Standard - Recommended)</option>
										<option value="1.7" <?php selected( $options['body_line_height'], '1.7' ); ?>>1.7 (Comfortable)</option>
										<option value="1.8" <?php selected( $options['body_line_height'], '1.8' ); ?>>1.8 (Relaxed / Magazine)</option>
									</select>
								</div>
							</div>

						</div>
					</div>

					<!-- Tab 5: Colors & Styling -->
					<div class="c23-tab-content" id="tab-styles">
						<div class="c23-panel">

							<!-- Section: Color Palette -->
							<div class="c23-section-header">
								<span class="dashicons dashicons-art"></span>
								<h3><?php esc_html_e( 'Color Palette', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Brand Colors', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_primary_color"><?php esc_html_e( 'Primary Accent Color', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Accent color used for badges, read more buttons, links, and highlights.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="text" id="c23_primary_color" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[primary_color]" value="<?php echo esc_attr( $options['primary_color'] ); ?>" class="c23-color-picker" data-default-color="#2563eb" />
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_title_color"><?php esc_html_e( 'Blog Title Color', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Color for blog post titles.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="text" id="c23_title_color" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[title_color]" value="<?php echo esc_attr( $options['title_color'] ); ?>" class="c23-color-picker" data-default-color="#1e293b" />
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_text_color"><?php esc_html_e( 'Body / Excerpt Text Color', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Color for excerpt and descriptive text.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="text" id="c23_text_color" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[text_color]" value="<?php echo esc_attr( $options['text_color'] ); ?>" class="c23-color-picker" data-default-color="#64748b" />
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_card_bg"><?php esc_html_e( 'Card Background Color', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Background color for blog cards.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="text" id="c23_card_bg" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[card_bg]" value="<?php echo esc_attr( $options['card_bg'] ); ?>" class="c23-color-picker" data-default-color="#ffffff" />
								</div>
							</div>

							<!-- Section: Shape & Shadow -->
							<div class="c23-section-header">
								<span class="dashicons dashicons-format-image"></span>
								<h3><?php esc_html_e( 'Card Shape & Elevation', 'c23-blogs' ); ?></h3>
								<span class="c23-section-badge"><?php esc_html_e( 'Card Design', 'c23-blogs' ); ?></span>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_card_radius"><?php esc_html_e( 'Card Corner Radius (px)', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Border radius for cards and images in pixels.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<input type="number" id="c23_card_radius" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[card_radius]" value="<?php echo esc_attr( $options['card_radius'] ); ?>" min="0" max="40" class="c23-input-num" /> px
								</div>
							</div>

							<div class="c23-field-row">
								<div class="c23-field-label">
									<label for="c23_card_shadow"><?php esc_html_e( 'Card Shadow Style', 'c23-blogs' ); ?></label>
									<span class="c23-field-desc"><?php esc_html_e( 'Elevation shadow depth for blog cards.', 'c23-blogs' ); ?></span>
								</div>
								<div class="c23-field-control">
									<select id="c23_card_shadow" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[card_shadow]" class="c23-select">
										<option value="none" <?php selected( $options['card_shadow'], 'none' ); ?>><?php esc_html_e( 'None (Flat)', 'c23-blogs' ); ?></option>
										<option value="subtle" <?php selected( $options['card_shadow'], 'subtle' ); ?>><?php esc_html_e( 'Subtle (Default)', 'c23-blogs' ); ?></option>
										<option value="medium" <?php selected( $options['card_shadow'], 'medium' ); ?>><?php esc_html_e( 'Medium Elevation', 'c23-blogs' ); ?></option>
										<option value="elevated" <?php selected( $options['card_shadow'], 'elevated' ); ?>><?php esc_html_e( 'Elevated Float', 'c23-blogs' ); ?></option>
									</select>
								</div>
							</div>

						</div>
					</div>
				</div>

				<div class="c23-submit-bar">
					<div class="c23-save-hint">
						<span class="dashicons dashicons-yes-alt"></span>
						<span><?php esc_html_e( 'Settings take effect immediately across all blog archives and posts.', 'c23-blogs' ); ?></span>
					</div>
					<button type="submit" class="button button-primary c23-submit-btn">
						<span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save All Changes', 'c23-blogs' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}
}
