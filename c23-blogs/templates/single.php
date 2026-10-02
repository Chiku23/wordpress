<?php
/**
 * Modern Single Post Template for C23 Blogs.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

C23_Blogs_Templates::get_header();
?>

<div class="c23-blogs-wrapper c23-single-wrapper">
	<div class="c23-container c23-single-container">

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'c23-single-article' ); ?> itemscope itemtype="https://schema.org/BlogPosting">

				<!-- Post Header -->
				<header class="c23-single-header">
					<?php C23_Blogs_Templates::render_categories(); ?>

					<h1 class="c23-single-title" itemprop="headline"><?php the_title(); ?></h1>

					<?php C23_Blogs_Templates::render_meta(); ?>
				</header>

				<!-- Featured Image Hero -->
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="c23-single-hero">
						<?php the_post_thumbnail( 'large', array( 'class' => 'c23-single-img', 'itemprop' => 'image' ) ); ?>
						<?php
						$caption = get_the_post_thumbnail_caption();
						if ( ! empty( $caption ) ) :
							?>
							<figcaption class="c23-hero-caption"><?php echo esc_html( $caption ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<!-- Content Body -->
				<div class="c23-single-content" itemprop="articleBody">
					<?php
					the_content();

					wp_link_pages( array(
						'before' => '<div class="c23-page-links">' . esc_html__( 'Pages:', 'c23-blogs' ),
						'after'  => '</div>',
					) );
					?>
				</div>

				<!-- Post Footer: Tags, Author, Nav -->
				<footer class="c23-single-footer">
					<?php C23_Blogs_Templates::render_tags(); ?>

					<?php C23_Blogs_Templates::render_author_box(); ?>

					<?php C23_Blogs_Templates::render_post_nav(); ?>
				</footer>

				<!-- Comments Section -->
				<?php
				$show_comments = (bool) C23_Blogs_Settings::get( 'show_comments', '1' );
				if ( $show_comments && ( comments_open() || get_comments_number() ) ) :
					?>
					<div class="c23-comments-section">
						<?php comments_template(); ?>
					</div>
				<?php endif; ?>

			</article>

		<?php endwhile; ?>

	</div>
</div>

<?php
C23_Blogs_Templates::get_footer();
