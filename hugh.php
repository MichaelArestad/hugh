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
		add_action( 'rest_api_init', array( __CLASS__, 'rest_api_init' ) );
	}

	public static function widgets_init() {
		register_widget( 'Hugh_Widget' );
	}

	public static function rest_api_init() {
		register_rest_route( 'hugh/v1', '/colors', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => __CLASS__ . '::rest_get_colors',
		) );

		// Add new application passwords
		register_rest_route( 'hugh/v1', '/colors/add', array(
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => __CLASS__ . '::rest_add_color',
			'args' => array(
				'color' => array(
					'required' => true,
				),
				'label' => array(
					'default' => '',
				),
			),
		) );
	}

	public static function rest_get_colors( $data ) {
		return array_values( self::get_colors() );
	}

	public static function rest_add_color( $data ) {
		$new_color = strtolower( $data['color'] );
		$new_label = wp_kses( $data['label'], array() );

		if ( ! preg_match( '/^#[\da-f]{6}$/', $new_color ) ) {
			return new WP_Error( 'bad-color', __( 'The specified color is in an invalid format.', 'hugh' ) );
		}

		return self::add_color( $new_color, $new_label );
	}

	public static function get_colors() {
		$colors = get_transient( 'hugh_colors' );
		if ( ! $colors ) {
			return array();
		}
		return (array) $colors;
	}

	public static function add_color( $color, $label ) {
		$colors = self::get_colors();
		$colors[ $color ] = array(
			'color' => $color,
			'label' => $label,
			'time'  => time(),
		);

		uasort( $colors, array( __CLASS__, 'sort_by_time' ) );

		// Only store 100 colors max.
		if ( sizeof( $colors ) > 100 ) {
			$colors = array_slice( $colors, 0, 100, true );
		}

		set_transient( 'hugh_colors', $colors );

		return $colors[ $color ];
	}

	public static function sort_by_time( $a, $b ) {
		return $a['time'] - $b['time'];
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
		wp_enqueue_style( 'hugh', plugins_url( 'hugh/hugh.css' ) );
		wp_enqueue_script( 'hugh', plugins_url( 'hugh/hugh.js' ), array( 'wp-util' ), time() );
		wp_localize_script( 'hugh', 'Hugh', array(
			'root'       => esc_url_raw( rest_url() ),
			'namespace'  => 'hugh/v1',
		) );
		echo $args['before_widget'];
		?>
		<h1>Hugh</h1>

		<form class="hugh__form">
			<input class="hugh__color" type="color" id="hugh_color" value="#ffffff" />
			<input class="hugh__label" type="label" id="hugh_label" placeholder="<?php esc_attr_e( 'Tell me why...', 'hugh' ); ?>" />
			<button class="hugh__submit" type="submit" ><span class="hugh__screen-reader-text">Submit</span>!</button>
		</form>

		<div class="hugh__colorways"></div>

		<script type="text/html" id="tmpl-color-template">
			<a href="#" aria-label="recently used color" class="hugh__colorway" style="background-color:{{{ data.color }}}" title="{{{ data.label }}}" data-color="{{{ data.color }}}">
				<span class="hugh__screen-reader-text">{{{ data.label }}}</span>
				<div class="hugh__colorway-accent"></div>
			</a>
		</script>
		<script type="text/html" id="tmpl-style-template">
			#content {
				background-color: {{{ data.color }}};
				-webkit-transition: background .3s ease-in-out;
				transition: background .3s ease-in-out;
			}
		</script>
		<?php
		echo $args['after_widget'];
	}

}
