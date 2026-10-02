<?php
/**
 * Card List Partial for C23 Blogs.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_thumb = has_post_thumbnail();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'c23-blog-card c23-card-list' ); ?> itemscope itemtype="https://schema.org/BlogPosting">
	<?php if ( $has_thumb ) : ?>
		<div class="c23-card-media">
			<a href="<?php the_permalink(); ?>" class="c23-thumb-link" aria-label="<?php the_title_attribute(); ?>">
				<?php the_post_thumbnail( 'medium_large', array( 'class' => 'c23-card-img', 'itemprop' => 'image' ) ); ?>
			</a>
			<?php C23_Blogs_Templates::render_categories(); ?>
		</div>
	<?php endif; ?>

	<div class="c23-card-body">
		<?php if ( ! $has_thumb ) : ?>
			<div class="c23-card-preheader">
				<?php C23_Blogs_Templates::render_categories(); ?>
			</div>
		<?php endif; ?>

		<h2 class="c23-card-title" itemprop="headline">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<?php C23_Blogs_Templates::render_meta(); ?>

		<div class="c23-card-excerpt" itemprop="description">
			<?php the_excerpt(); ?>
		</div>

		<div class="c23-card-footer">
			<a href="<?php the_permalink(); ?>" class="c23-read-more-btn">
				<span><?php esc_html_e( 'Read Article', 'c23-blogs' ); ?></span>
				<svg class="c23-btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
			</a>
		</div>
	</div>
</article>
