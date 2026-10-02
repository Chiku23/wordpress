<?php
/**
 * Modern Archive Template for C23 Blogs.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

C23_Blogs_Templates::get_header();

$layout_view = C23_Blogs_Settings::get( 'layout_view', 'grid' );
$grid_cols   = (int) C23_Blogs_Settings::get( 'grid_columns', 3 );
$container_class = ( 'list' === $layout_view ) ? 'c23-blogs-list-layout' : 'c23-blogs-grid-layout c23-cols-' . $grid_cols;
?>

<div class="c23-blogs-wrapper c23-archive-wrapper">
	<div class="c23-container">

		<?php if ( is_archive() ) : ?>
			<header class="c23-archive-header">
				<h1 class="c23-archive-title">
					<?php
					if ( is_post_type_archive( C23_Blogs_Post_Type::POST_TYPE ) ) {
						$archive_title = C23_Blogs_Settings::get( 'archive_title', __( 'Blogs', 'c23-blogs' ) );
						echo esc_html( ! empty( $archive_title ) ? $archive_title : __( 'Blogs', 'c23-blogs' ) );
					} else {
						the_archive_title();
					}
					?>
				</h1>
				<?php
				if ( is_post_type_archive( C23_Blogs_Post_Type::POST_TYPE ) ) {
					$archive_desc = C23_Blogs_Settings::get( 'archive_subtitle', '' );
				} else {
					$archive_desc = get_the_archive_description();
				}
				if ( ! empty( $archive_desc ) ) :
					?>
					<div class="c23-archive-desc"><?php echo wp_kses_post( wpautop( $archive_desc ) ); ?></div>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>

			<div class="c23-cards-container <?php echo esc_attr( $container_class ); ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'list' === $layout_view ) {
						include C23_BLOGS_PATH . 'templates/partials/card-list.php';
					} else {
						include C23_BLOGS_PATH . 'templates/partials/card-grid.php';
					}
				endwhile;
				?>
			</div>

			<div class="c23-pagination-wrap">
				<?php
				the_posts_pagination( array(
					'mid_size'           => 2,
					'prev_text'          => '&larr; ' . __( 'Previous', 'c23-blogs' ),
					'next_text'          => __( 'Next', 'c23-blogs' ) . ' &rarr;',
					'screen_reader_text' => __( 'Blogs navigation', 'c23-blogs' ),
				) );
				?>
			</div>

		<?php else : ?>

			<div class="c23-no-posts">
				<div class="c23-no-posts-icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
				</div>
				<h3><?php esc_html_e( 'No Blogs Found', 'c23-blogs' ); ?></h3>
				<p><?php esc_html_e( 'There are currently no blog posts published in this section. Please check back later.', 'c23-blogs' ); ?></p>
			</div>

		<?php endif; ?>

	</div>
</div>

<?php
C23_Blogs_Templates::get_footer();
