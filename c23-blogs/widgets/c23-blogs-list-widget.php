<?php
/**
 * Legacy widget file stub for backward compatibility.
 *
 * @package C23_Blogs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __DIR__ ) . '/includes/class-c23-blogs-widget.php';

// Alias for old class name if needed
if ( ! class_exists( 'Blogs_Widget' ) ) {
	class Blogs_Widget extends C23_Blogs_Widget {}
}
