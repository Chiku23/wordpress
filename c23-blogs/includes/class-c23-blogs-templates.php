<?php
/**
 * Template Loader and Display Helpers for C23 Blogs.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class C23_Blogs_Templates {

	/**
	 * Initialize template hooks.
	 */
	public static function init() {
		add_filter( 'archive_template', array( __CLASS__, 'load_archive_template' ) );
		add_filter( 'taxonomy_template', array( __CLASS__, 'load_taxonomy_template' ) );
		add_filter( 'single_template', array( __CLASS__, 'load_single_template' ) );
	}

	/**
	 * Cached header block markup.
	 *
	 * @var string|null
	 */
	private static $header_markup = null;

	/**
	 * Cached footer block markup.
	 *
	 * @var string|null
	 */
	private static $footer_markup = null;

	/**
	 * Render site header with full support for both Block Themes (FSE) and Classic Themes.
	 */
	public static function get_header() {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			// Pre-render header and footer blocks BEFORE wp_head() so WordPress parses blocks
			// and automatically enqueues block layout styles (core-block-supports, margins, flex) into wp_head()
			ob_start();
			if ( function_exists( 'block_template_part' ) ) {
				block_template_part( 'header' );
			}
			self::$header_markup = ob_get_clean();

			ob_start();
			if ( function_exists( 'block_template_part' ) ) {
				block_template_part( 'footer' );
			}
			self::$footer_markup = ob_get_clean();
			?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
	<?php echo self::$header_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php
		} else {
			get_header();
		}
	}

	/**
	 * Render site footer with full support for both Block Themes (FSE) and Classic Themes.
	 */
	public static function get_footer() {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			echo self::$footer_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
</div>
<?php wp_footer(); ?>
</body>
</html>
			<?php
		} else {
			get_footer();
		}
	}

	/**
	 * Locate archive template for c23_blogs.
	 *
	 * @param string $template
	 * @return string
	 */
	public static function load_archive_template( $template ) {
		if ( is_post_type_archive( C23_Blogs_Post_Type::POST_TYPE ) ) {
			// Check theme first
			$theme_template = locate_template( array( 'archive-c23_blogs.php', 'archive-blogs.php' ) );
			if ( $theme_template ) {
				return $theme_template;
			}
			$plugin_template = C23_BLOGS_PATH . 'templates/archive.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}
		return $template;
	}

	/**
	 * Locate taxonomy template for c23_blog_category and c23_blog_tag.
	 *
	 * @param string $template
	 * @return string
	 */
	public static function load_taxonomy_template( $template ) {
		if ( is_tax( C23_Blogs_Post_Type::TAX_CATEGORY ) || is_tax( C23_Blogs_Post_Type::TAX_TAG ) ) {
			$term = get_queried_object();
			$theme_template = locate_template( array(
				"taxonomy-{$term->taxonomy}-{$term->slug}.php",
				"taxonomy-{$term->taxonomy}.php",
				'archive-c23_blogs.php',
			) );
			if ( $theme_template ) {
				return $theme_template;
			}
			$plugin_template = C23_BLOGS_PATH . 'templates/archive.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}
		return $template;
	}

	/**
	 * Locate single post template for c23_blogs.
	 *
	 * @param string $template
	 * @return string
	 */
	public static function load_single_template( $template ) {
		global $post;
		if ( is_singular( C23_Blogs_Post_Type::POST_TYPE ) || ( $post && $post->post_type === C23_Blogs_Post_Type::POST_TYPE ) ) {
			$theme_template = locate_template( array( 'single-c23_blogs.php', 'single-blogs.php' ) );
			if ( $theme_template ) {
				return $theme_template;
			}
			$plugin_template = C23_BLOGS_PATH . 'templates/single.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}
		return $template;
	}

	/**
	 * Calculate estimated reading time for a post.
	 *
	 * @param int|WP_Post|null $post
	 * @return string
	 */
	public static function get_reading_time( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return '';
		}

		$words = str_word_count( wp_strip_all_tags( $post->post_content ) );
		$minutes = max( 1, (int) ceil( $words / 200 ) );

		/* translators: %d: reading time in minutes */
		return sprintf( _n( '%d min read', '%d mins read', $minutes, 'c23-blogs' ), $minutes );
	}

	/**
	 * Render post meta block.
	 *
	 * @param int|WP_Post|null $post
	 */
	public static function render_meta( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		$show_author = (bool) C23_Blogs_Settings::get( 'show_author', '1' );
		$show_date   = (bool) C23_Blogs_Settings::get( 'show_date', '1' );
		$show_time   = (bool) C23_Blogs_Settings::get( 'show_reading_time', '1' );

		if ( ! $show_author && ! $show_date && ! $show_time ) {
			return;
		}

		$author_id   = $post->post_author;
		$author_name = get_the_author_meta( 'display_name', $author_id );
		$author_url  = get_author_posts_url( $author_id );
		?>
		<div class="c23-post-meta">
			<?php if ( $show_author ) : ?>
				<span class="c23-meta-item c23-meta-author">
					<span class="c23-author-avatar"><?php echo get_avatar( $author_id, 24 ); ?></span>
					<a href="<?php echo esc_url( $author_url ); ?>" class="c23-author-name"><?php echo esc_html( $author_name ); ?></a>
				</span>
			<?php endif; ?>

			<?php if ( $show_date ) : ?>
				<span class="c23-meta-item c23-meta-date">
					<svg class="c23-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
					<time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time>
				</span>
			<?php endif; ?>

			<?php if ( $show_time ) : ?>
				<span class="c23-meta-item c23-meta-readtime">
					<svg class="c23-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
					<?php echo esc_html( self::get_reading_time( $post ) ); ?>
				</span>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render post category badges.
	 *
	 * @param int|WP_Post|null $post
	 */
	public static function render_categories( $post = null ) {
		if ( ! C23_Blogs_Settings::get( 'show_categories', '1' ) ) {
			return;
		}

		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		$terms = get_the_terms( $post->ID, C23_Blogs_Post_Type::TAX_CATEGORY );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			echo '<div class="c23-categories-list">';
			foreach ( $terms as $term ) {
				$term_link = get_term_link( $term );
				if ( ! is_wp_error( $term_link ) ) {
					echo '<a href="' . esc_url( $term_link ) . '" class="c23-category-badge">' . esc_html( $term->name ) . '</a>';
				}
			}
			echo '</div>';
		}
	}

	/**
	 * Render post tags.
	 *
	 * @param int|WP_Post|null $post
	 */
	public static function render_tags( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		$tags = get_the_terms( $post->ID, C23_Blogs_Post_Type::TAX_TAG );
		if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
			echo '<div class="c23-tags-wrap"><span class="c23-tags-label">' . esc_html__( 'Tags:', 'c23-blogs' ) . '</span> <div class="c23-tags-list">';
			foreach ( $tags as $tag ) {
				$tag_link = get_term_link( $tag );
				if ( ! is_wp_error( $tag_link ) ) {
					echo '<a href="' . esc_url( $tag_link ) . '" class="c23-tag-item">#' . esc_html( $tag->name ) . '</a>';
				}
			}
			echo '</div></div>';
		}
	}

	/**
	 * Render author bio box on single view.
	 *
	 * @param int|WP_Post|null $post
	 */
	public static function render_author_box( $post = null ) {
		if ( ! C23_Blogs_Settings::get( 'show_author_box', '1' ) ) {
			return;
		}

		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		$author_id   = $post->post_author;
		$author_name = get_the_author_meta( 'display_name', $author_id );
		$author_desc = get_the_author_meta( 'description', $author_id );
		$author_url  = get_author_posts_url( $author_id );
		?>
		<div class="c23-author-box">
			<div class="c23-author-box-avatar">
				<?php echo get_avatar( $author_id, 72 ); ?>
			</div>
			<div class="c23-author-box-content">
				<div class="c23-author-box-label"><?php esc_html_e( 'Written by', 'c23-blogs' ); ?></div>
				<h4 class="c23-author-box-name"><a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( $author_name ); ?></a></h4>
				<?php if ( ! empty( $author_desc ) ) : ?>
					<p class="c23-author-box-bio"><?php echo esc_html( $author_desc ); ?></p>
				<?php else : ?>
					<p class="c23-author-box-bio"><?php esc_html_e( 'Contributor & author on this blog.', 'c23-blogs' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render next & previous post navigation.
	 */
	public static function render_post_nav() {
		if ( ! C23_Blogs_Settings::get( 'show_post_nav', '1' ) ) {
			return;
		}

		$prev_post = get_adjacent_post( false, '', true );
		$next_post = get_adjacent_post( false, '', false );

		if ( ! $prev_post && ! $next_post ) {
			return;
		}
		?>
		<nav class="c23-post-nav">
			<div class="c23-nav-item c23-nav-prev <?php echo ! $prev_post ? 'c23-nav-empty' : ''; ?>">
				<?php if ( $prev_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>">
						<span class="c23-nav-subtitle">&larr; <?php esc_html_e( 'Previous Blog', 'c23-blogs' ); ?></span>
						<span class="c23-nav-title"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></span>
					</a>
				<?php endif; ?>
			</div>
			<div class="c23-nav-item c23-nav-next <?php echo ! $next_post ? 'c23-nav-empty' : ''; ?>">
				<?php if ( $next_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>">
						<span class="c23-nav-subtitle"><?php esc_html_e( 'Next Blog', 'c23-blogs' ); ?> &rarr;</span>
						<span class="c23-nav-title"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</nav>
		<?php
	}
}
