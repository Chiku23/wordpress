<?php
/**
 * Recent Blogs Widget for C23 Blogs.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class C23_Blogs_Widget extends WP_Widget {

	/**
	 * Construct the widget.
	 */
	public function __construct() {
		parent::__construct(
			'c23_blogs_widget',
			__( 'C23 Blogs List', 'c23-blogs' ),
			array(
				'description' => __( 'Display recent blog posts with thumbnails and metadata.', 'c23-blogs' ),
				'classname'   => 'c23-blogs-widget',
			)
		);
	}

	/**
	 * Output widget content on frontend.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Recent Blogs', 'c23-blogs' );
		$limit = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
		$show_thumb = ! empty( $instance['show_thumb'] );
		$show_date  = ! empty( $instance['show_date'] );

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		$query = new WP_Query( array(
			'post_type'      => C23_Blogs_Post_Type::POST_TYPE,
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
		) );

		if ( $query->have_posts() ) {
			echo '<ul class="c23-widget-list">';
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<li class="c23-widget-item">
					<?php if ( $show_thumb && has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="c23-widget-thumb">
							<?php the_post_thumbnail( 'thumbnail' ); ?>
						</a>
					<?php endif; ?>
					<div class="c23-widget-content">
						<a href="<?php the_permalink(); ?>" class="c23-widget-title"><?php the_title(); ?></a>
						<?php if ( $show_date ) : ?>
							<span class="c23-widget-date"><?php echo esc_html( get_the_date() ); ?></span>
						<?php endif; ?>
					</div>
				</li>
				<?php
			}
			echo '</ul>';
			wp_reset_postdata();
		} else {
			echo '<p class="c23-widget-empty">' . esc_html__( 'No blogs found.', 'c23-blogs' ) . '</p>';
		}

		echo $args['after_widget'];
	}

	/**
	 * Render widget backend form.
	 *
	 * @param array $instance
	 */
	public function form( $instance ) {
		$title      = isset( $instance['title'] ) ? $instance['title'] : __( 'Recent Blogs', 'c23-blogs' );
		$limit      = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
		$show_thumb = isset( $instance['show_thumb'] ) ? (bool) $instance['show_thumb'] : true;
		$show_date  = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : true;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'c23-blogs' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Number of blogs to show:', 'c23-blogs' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" step="1" min="1" max="20" value="<?php echo esc_attr( $limit ); ?>" size="3" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_thumb ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_thumb' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>"><?php esc_html_e( 'Display post thumbnail?', 'c23-blogs' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php esc_html_e( 'Display post date?', 'c23-blogs' ); ?></label>
		</p>
		<?php
	}

	/**
	 * Save widget settings.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']      = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['limit']      = ( ! empty( $new_instance['limit'] ) ) ? absint( $new_instance['limit'] ) : 5;
		$instance['show_thumb'] = ! empty( $new_instance['show_thumb'] ) ? 1 : 0;
		$instance['show_date']  = ! empty( $new_instance['show_date'] ) ? 1 : 0;
		return $instance;
	}
}
