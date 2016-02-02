<?php

/*
 * Plugin Name: Hugh
 * Plugin URI:  https://wordpress.org/plugins/hugh/
 * Description: Democratize coloring.
 * Version:     0.1.0
 * Author:      Michael Arestad and George Stephanis
 * Author URI:  http://blog.michaelarestad.com
 * Text Domain: hugh
 * Domain Path: /languages
 */

class Hugh { // Hugh is classy as fuck.

	public static function add_hooks() {
		add_action( 'widgets_init', array( __CLASS__, 'widgets_init' ) );
	}

	public static function widgets_init() {
		register_widget( 'Hugh_Widget' );
	}
}

Hugh::add_hooks();

class Hugh_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'class_name'  => 'hugh_widget',
			'description' => __( 'Hugh is classy.', 'hugh' ),
		);
		parent::__construct( 'hugh_widget', 'Hugh Widget', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		?>
		<h1>STUFF HAPPENS HERE</h1>
		<?php
		echo $args['after_widget'];
	}

}