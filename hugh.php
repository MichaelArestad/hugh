<?php

/*
 * Plugin Name: Hugh
 * Plugin URI:  https://wordpress.org/plugins/hugh/
 * Description: Democratize coloring.
 * Version:     1.0
 * Author:      Michael Arestad and George Stephanis
 * Author URI:  http://blog.michaelarestad.com
 * Text Domain: hugh
 * Domain Path: /languages
 */

class Hugh { // Hugh is classy as fuck.

	public static function add_hooks() {
		add_action( 'widgets_init', array( __CLASS__, 'widgets_init' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'rest_api_init' ) );
		add_filter( 'hugh_css', array( __CLASS__, 'hugh_css' ) );
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

	public static function hugh_css( $css ) {
		$slug = get_template();

		switch( $slug ) {
			case 'twentysixteen' :
				ob_start();
				?>
				body,
				body.custom-background {
					background-color: {{ data.color }};
					transition: background-color .3s ease-in-out;
				}
				#page,
				#page * {
					background-color: {{ data.contrast }};
					color: {{ data.color }};
					transition: background-color .3s ease-in-out, color .3s ease-in-out;
				}
				#page button {
					background-color: {{ data.color }};
					color: {{ data.contrast }};
					transition: background-color .3s ease-in-out, color .3s ease-in-out;
				}
				#wpadminbar,
				#wpadminbar a,
				#wpadminbar .quicklinks .menupop ul.ab-sub-secondary,
				#wpadminbar .menupop .ab-sub-wrapper,
				#wpadminbar#wpadminbar .ab-label.ab-label,
				#wpadminbar .ab-item:before,
				#wpadminbar .ab-icon:before,
				#wpadminbar #adminbarsearch:before {
					color: {{ data.color }} !important;
					transition: color .3s ease-in-out;
				}
				#wpadminbar,
				#wpadminbar a,
				#wpadminbar .quicklinks .menupop ul.ab-sub-secondary,
				#wpadminbar .menupop .ab-sub-wrapper {
					background-color: {{ data.contrast }} !important;
					transition: background-color .3s ease-in-out;
				}
				#wpadminbar a:hover,
				#wpadminbar a:focus,
				#wpadminbar .ab-item:hover:before,
				#wpadminbar .ab-item:focus:before,
				#wpadminbar .ab-item:hover .ab-icon:before,
				#wpadminbar .ab-item:focus .ab-icon:before,
				#wpadminbar#wpadminbar .ab-item.ab-item:hover .ab-label,
				#wpadminbar#wpadminbar .ab-item.ab-item:focus .ab-label {
					color: {{ data.contrast }} !important;
				}
				#wpadminbar a:hover,
				#wpadminbar a:focus {
					background-color: {{ data.color }} !important;
				}
				<?php
				$css = ob_get_clean();
				break;

			case 'twentyfifteen' :
				ob_start();
				?>
				body,
				body.custom-background {
					background-color: {{ data.color }};
					transition: background-color .3s ease-in-out;
				}
				#wpadminbar,
				#wpadminbar a,
				#wpadminbar .quicklinks .menupop ul.ab-sub-secondary,
				#wpadminbar .menupop .ab-sub-wrapper,
				#wpadminbar#wpadminbar .ab-label.ab-label,
				#wpadminbar .ab-item:before,
				#wpadminbar .ab-icon:before,
				#wpadminbar #adminbarsearch:before {
					color: {{ data.contrast }} !important;
					transition: color .3s ease-in-out;
				}
				#wpadminbar,
				#wpadminbar a,
				#wpadminbar .quicklinks .menupop ul.ab-sub-secondary,
				#wpadminbar .menupop .ab-sub-wrapper {
					background-color: {{ data.color }} !important;
					transition: background-color .3s ease-in-out;
				}
				#wpadminbar a:hover,
				#wpadminbar a:focus,
				#wpadminbar .ab-item:hover:before,
				#wpadminbar .ab-item:focus:before,
				#wpadminbar .ab-item:hover .ab-icon:before,
				#wpadminbar .ab-item:focus .ab-icon:before,
				#wpadminbar#wpadminbar .ab-item.ab-item:hover .ab-label,
				#wpadminbar#wpadminbar .ab-item.ab-item:focus .ab-label {
					color: {{ data.color }} !important;
				}
				#wpadminbar a:hover,
				#wpadminbar a:focus {
					background-color: {{ data.contrast }} !important;
				}
				<?php
				$css = ob_get_clean();
				break;

			case 'twentyfourteen' :
				ob_start();
				?>
				body,
				body.custom-background,
				.site,
				.entry-header.entry-header,
				.entry-content.entry-content,
				.entry-meta.entry-meta,
				#secondary button {
					background-color: {{ data.color }};
					color: {{ data.contrast }} !important;
					transition: background-color .3s ease-in-out, color .3s ease-in-out;
				}
				article * {
					background-color: {{ data.color }} !important;
					color: {{ data.contrast }} !important;
					transition: background-color .3s ease-in-out, color .3s ease-in-out;
				}
				.site-header,
				.site-header *,
				#secondary,
				.site-footer,
				.search-toggle:before {
					background-color: {{ data.contrast }} !important;
					color: {{ data.color }} !important;
					transition: background-color .3s ease-in-out, color .3s ease-in-out;
				}
				#secondary  * {
					color: {{ data.color }} !important;
					transition: color .3s ease-in-out;
				}
				#wpadminbar,
				#wpadminbar a,
				#wpadminbar .quicklinks .menupop ul.ab-sub-secondary,
				#wpadminbar .menupop .ab-sub-wrapper,
				#wpadminbar#wpadminbar .ab-label.ab-label,
				#wpadminbar .ab-item:before,
				#wpadminbar .ab-icon:before,
				#wpadminbar #adminbarsearch:before {
					color: {{ data.contrast }} !important;
					transition: color .3s ease-in-out;
				}
				#wpadminbar,
				#wpadminbar a,
				#wpadminbar .quicklinks .menupop ul.ab-sub-secondary,
				#wpadminbar .menupop .ab-sub-wrapper  {
					background-color: {{ data.color }} !important;
					transition: background-color .3s ease-in-out;
				}
				#wpadminbar a:hover,
				#wpadminbar a:focus,
				#wpadminbar .ab-item:hover:before,
				#wpadminbar .ab-item:focus:before,
				#wpadminbar .ab-item:hover .ab-icon:before,
				#wpadminbar .ab-item:focus .ab-icon:before,
				#wpadminbar#wpadminbar .ab-item.ab-item:hover .ab-label,
				#wpadminbar#wpadminbar .ab-item.ab-item:focus .ab-label {
					color: {{ data.color }} !important;
				}
				#wpadminbar a:hover,
				#wpadminbar a:focus {
					background-color: {{ data.contrast }} !important;
				}
				<?php
				$css = ob_get_clean();
				break;
			default:
				// no changes
				break;
		}

		// Oh, and minify it too.
		return str_replace( array( "\t", "\r", "\n" ), '', $css );
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
			'colors'     => array_values( Hugh::get_colors() ),
		) );
		echo $args['before_widget'];
		?>
		<h1 class="widget-title">Make a color decision</h1>

		<form class="hugh__form">
			<input class="hugh__color" type="color" id="hugh_color" value="#ffffff" />
			<input class="hugh__label hugh__screen-reader-text" type="label" id="hugh_label" placeholder="<?php esc_attr_e( 'Leave a secret note', 'hugh' ); ?>" />
			<button class="hugh__submit" type="submit" >Apply!</button>
		</form>

		<div class="hugh__colorways"></div>

		<script type="text/html" id="tmpl-color-template">
			<a href="#" aria-label="recently used color" class="hugh__colorway" style="background-color:{{ data.color }}" title="{{ data.label }}" data-color="{{ data.color }}">
				<span class="hugh__screen-reader-text">{{{ data.label }}}</span>
				<div class="hugh__colorway-accent" style="background-color:{{ data.contrast }}"></div>
			</a>
		</script>
		<script type="text/html" id="tmpl-style-template">
		<?php ob_start(); ?>
			body,
			body.custom-background,
			html {
				background-color: {{ data.color }};
				color: {{{ data.contrast }}};
				transition: background-color .3s ease-in-out, color .3s ease-in-out;
			}
			#wpadminbar,
			#wpadminbar a,
			.ab-sub-wrapper,
			#wpadminbar#wpadminbar .ab-label.ab-label,
			#wpadminbar .ab-item:before,
			#wpadminbar .ab-icon:before,
			#wpadminbar #adminbarsearch:before {
				color: {{ data.contrast }} !important;
				transition: color .3s ease-in-out;
			}
			#wpadminbar,
			#wpadminbar a,
			.ab-sub-wrapper  {
				background-color: {{ data.color }} !important;
				transition: background-color .3s ease-in-out;
			}
			#wpadminbar a:hover,
			#wpadminbar a:focus,
			#wpadminbar .ab-item:hover:before,
			#wpadminbar .ab-item:focus:before,
			#wpadminbar .ab-item:hover .ab-icon:before,
			#wpadminbar .ab-item:focus .ab-icon:before,
			#wpadminbar#wpadminbar .ab-item.ab-item:hover .ab-label,
			#wpadminbar#wpadminbar .ab-item.ab-item:focus .ab-label {
				color: {{ data.color }} !important;
			}
			#wpadminbar a:hover,
			#wpadminbar a:focus {
				background-color: {{ data.contrast }} !important;
			}
		<?php echo apply_filters( 'hugh_css', ob_get_clean() ); ?>
		</script>
		<?php
		echo $args['after_widget'];
	}

}
