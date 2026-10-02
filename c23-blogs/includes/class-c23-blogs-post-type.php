<?php
/**
 * Post Type and Taxonomies Handler for C23 Blogs.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class C23_Blogs_Post_Type {

	/**
	 * Post type identifier (kept strictly as 'c23_blogs' to prevent breaking existing data).
	 */
	const POST_TYPE = 'c23_blogs';

	/**
	 * Category taxonomy identifier.
	 */
	const TAX_CATEGORY = 'c23_blog_category';

	/**
	 * Tag taxonomy identifier.
	 */
	const TAX_TAG = 'c23_blog_tag';

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_archive_query' ) );
		add_filter( 'excerpt_length', array( __CLASS__, 'filter_excerpt_length' ), 999 );
		add_filter( 'excerpt_more', array( __CLASS__, 'filter_excerpt_more' ), 999 );
	}

	/**
	 * Register the c23_blogs post type.
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Blogs', 'Post Type General Name', 'c23-blogs' ),
			'singular_name'         => _x( 'Blog', 'Post Type Singular Name', 'c23-blogs' ),
			'menu_name'             => __( 'Blogs', 'c23-blogs' ),
			'name_admin_bar'        => __( 'Blog', 'c23-blogs' ),
			'archives'              => __( 'Blog Archives', 'c23-blogs' ),
			'attributes'            => __( 'Blog Attributes', 'c23-blogs' ),
			'parent_item_colon'     => __( 'Parent Blog:', 'c23-blogs' ),
			'all_items'             => __( 'All Blogs', 'c23-blogs' ),
			'add_new_item'          => __( 'Add New Blog', 'c23-blogs' ),
			'add_new'               => __( 'Add New', 'c23-blogs' ),
			'new_item'              => __( 'New Blog', 'c23-blogs' ),
			'edit_item'             => __( 'Edit Blog', 'c23-blogs' ),
			'update_item'           => __( 'Update Blog', 'c23-blogs' ),
			'view_item'             => __( 'View Blog', 'c23-blogs' ),
			'view_items'            => __( 'View Blogs', 'c23-blogs' ),
			'search_items'          => __( 'Search Blog', 'c23-blogs' ),
			'not_found'             => __( 'Not found', 'c23-blogs' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'c23-blogs' ),
			'featured_image'        => __( 'Featured Image', 'c23-blogs' ),
			'set_featured_image'    => __( 'Set featured image', 'c23-blogs' ),
			'remove_featured_image' => __( 'Remove featured image', 'c23-blogs' ),
			'use_featured_image'    => __( 'Use as featured image', 'c23-blogs' ),
			'insert_into_item'      => __( 'Insert into blog', 'c23-blogs' ),
			'uploaded_to_this_item' => __( 'Uploaded to this blog', 'c23-blogs' ),
			'items_list'            => __( 'Blogs list', 'c23-blogs' ),
			'items_list_navigation' => __( 'Blogs list navigation', 'c23-blogs' ),
			'filter_items_list'     => __( 'Filter blogs list', 'c23-blogs' ),
		);

		$archive_slug = sanitize_title( C23_Blogs_Settings::get( 'archive_slug', 'blogs' ) );
		if ( empty( $archive_slug ) ) {
			$archive_slug = 'blogs';
		}

		$args = array(
			'label'                 => __( 'Blog', 'c23-blogs' ),
			'description'           => __( 'C23 Blogs Posts', 'c23-blogs' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'revisions', 'custom-fields' ),
			'taxonomies'            => array( self::TAX_CATEGORY, self::TAX_TAG ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 10,
			'menu_icon'             => 'dashicons-welcome-write-blog',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => $archive_slug,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rewrite'               => array(
				'slug'       => $archive_slug,
				'with_front' => false,
			),
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register categories and tags for c23_blogs.
	 */
	public static function register_taxonomies() {
		// Category Taxonomy
		$cat_labels = array(
			'name'              => _x( 'Blog Categories', 'taxonomy general name', 'c23-blogs' ),
			'singular_name'     => _x( 'Blog Category', 'taxonomy singular name', 'c23-blogs' ),
			'search_items'      => __( 'Search Categories', 'c23-blogs' ),
			'all_items'         => __( 'All Categories', 'c23-blogs' ),
			'parent_item'       => __( 'Parent Category', 'c23-blogs' ),
			'parent_item_colon' => __( 'Parent Category:', 'c23-blogs' ),
			'edit_item'         => __( 'Edit Category', 'c23-blogs' ),
			'update_item'       => __( 'Update Category', 'c23-blogs' ),
			'add_new_item'      => __( 'Add New Category', 'c23-blogs' ),
			'new_item_name'     => __( 'New Category Name', 'c23-blogs' ),
			'menu_name'         => __( 'Categories', 'c23-blogs' ),
		);

		register_taxonomy(
			self::TAX_CATEGORY,
			array( self::POST_TYPE ),
			array(
				'hierarchical'      => true,
				'labels'            => $cat_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'blog-category',
					'with_front' => false,
				),
			)
		);

		// Tag Taxonomy
		$tag_labels = array(
			'name'                       => _x( 'Blog Tags', 'taxonomy general name', 'c23-blogs' ),
			'singular_name'              => _x( 'Blog Tag', 'taxonomy singular name', 'c23-blogs' ),
			'search_items'               => __( 'Search Tags', 'c23-blogs' ),
			'popular_items'              => __( 'Popular Tags', 'c23-blogs' ),
			'all_items'                  => __( 'All Tags', 'c23-blogs' ),
			'edit_item'                  => __( 'Edit Tag', 'c23-blogs' ),
			'update_item'                => __( 'Update Tag', 'c23-blogs' ),
			'add_new_item'               => __( 'Add New Tag', 'c23-blogs' ),
			'new_item_name'              => __( 'New Tag Name', 'c23-blogs' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'c23-blogs' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'c23-blogs' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'c23-blogs' ),
			'not_found'                  => __( 'No tags found.', 'c23-blogs' ),
			'menu_name'                  => __( 'Tags', 'c23-blogs' ),
		);

		register_taxonomy(
			self::TAX_TAG,
			array( self::POST_TYPE ),
			array(
				'hierarchical'          => false,
				'labels'                => $tag_labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'show_in_rest'          => true,
				'rewrite'               => array(
					'slug'       => 'blog-tag',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Configure main query for c23_blogs archives.
	 *
	 * @param WP_Query $query
	 */
	public static function filter_archive_query( $query ) {
		if ( ! is_admin() && $query->is_main_query() ) {
			if ( is_post_type_archive( self::POST_TYPE ) || is_tax( self::TAX_CATEGORY ) || is_tax( self::TAX_TAG ) ) {
				$per_page = (int) C23_Blogs_Settings::get( 'posts_per_page', 9 );
				$query->set( 'posts_per_page', $per_page );
			}
		}
	}

	/**
	 * Filter excerpt length for blogs.
	 *
	 * @param int $length
	 * @return int
	 */
	public static function filter_excerpt_length( $length ) {
		if ( get_post_type() === self::POST_TYPE ) {
			return (int) C23_Blogs_Settings::get( 'excerpt_length', 28 );
		}
		return $length;
	}

	/**
	 * Filter excerpt more string for blogs.
	 *
	 * @param string $more
	 * @return string
	 */
	public static function filter_excerpt_more( $more ) {
		if ( get_post_type() === self::POST_TYPE ) {
			return ' ' . C23_Blogs_Settings::get( 'excerpt_more', '...' );
		}
		return $more;
	}
}
