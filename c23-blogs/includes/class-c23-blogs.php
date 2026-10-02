<?php
/**
 * Main Plugin Orchestrator Class for C23 Blogs.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class C23_Blogs {

	/**
	 * Single instance of the class.
	 *
	 * @var C23_Blogs|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return C23_Blogs
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once C23_BLOGS_PATH . 'includes/class-c23-blogs-post-type.php';
		require_once C23_BLOGS_PATH . 'includes/class-c23-blogs-settings.php';
		require_once C23_BLOGS_PATH . 'includes/class-c23-blogs-templates.php';
		require_once C23_BLOGS_PATH . 'includes/class-c23-blogs-frontend.php';
		require_once C23_BLOGS_PATH . 'includes/class-c23-blogs-widget.php';
	}

	/**
	 * Initialize hooks and modules.
	 */
	private function init_hooks() {
		C23_Blogs_Post_Type::init();
		C23_Blogs_Settings::init();
		C23_Blogs_Templates::init();
		C23_Blogs_Frontend::init();

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register widgets.
	 */
	public function register_widgets() {
		register_widget( 'C23_Blogs_Widget' );
	}

	/**
	 * Plugin activation callback.
	 */
	public static function activate() {
		require_once C23_BLOGS_PATH . 'includes/class-c23-blogs-post-type.php';
		C23_Blogs_Post_Type::register_post_type();
		C23_Blogs_Post_Type::register_taxonomies();
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation callback.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
