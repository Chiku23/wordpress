<?php
/**
 * Plugin Name: C23 Blogs
 * Plugin URI: https://github.com/Chiku23/wordpress
 * Description: A simple modern plugin for WordPress to publish blog posts, it has features like responsive card layouts, estimated reading time, author boxes, categorized archives, and unified settings.
 * Version: 2.0.0
 * Author: Chiku23
 * Text Domain: c23-blogs
 * Domain Path: /languages
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Plugin Constants
define( 'C23_BLOGS_VERSION', '2.0.0' );
define( 'C23_BLOGS_FILE', __FILE__ );
define( 'C23_BLOGS_PATH', plugin_dir_path( __FILE__ ) );
define( 'C23_BLOGS_URL', plugin_dir_url( __FILE__ ) );

// Load Core Plugin Class
require_once C23_BLOGS_PATH . 'includes/class-c23-blogs.php';

// Register Activation & Deactivation Hooks
register_activation_hook( __FILE__, array( 'C23_Blogs', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'C23_Blogs', 'deactivate' ) );

/**
 * Return the main C23_Blogs instance.
 *
 * @return C23_Blogs
 */
function c23_blogs() {
	return C23_Blogs::instance();
}

// Bootstrap plugin
c23_blogs();

// =========================================================================
// Backward Compatibility Layer for Legacy Functions
// =========================================================================
if ( ! function_exists( 'c23_blogsposttyperegister' ) ) {
	function c23_blogsposttyperegister() {
		// Handled by C23_Blogs_Post_Type
	}
}

if ( ! function_exists( 'c23_blogs_excerpt_length' ) ) {
	function c23_blogs_excerpt_length( $length ) {
		return C23_Blogs_Post_Type::filter_excerpt_length( $length );
	}
}

if ( ! function_exists( 'c23_blogs_excerpt_more' ) ) {
	function c23_blogs_excerpt_more( $more ) {
		return C23_Blogs_Post_Type::filter_excerpt_more( $more );
	}
}