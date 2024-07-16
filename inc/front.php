<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
class WPLocator_front {
	public $settings = null;
	private $parent = null;
	function __construct( $parent = null ) {
		$this->parent = $parent;
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'wp-locator-settings.php';
		$this->settings = new WPLocator_settings( $this );
	}
	function init() {
		add_action( 'after_setup_theme', [ $this, 'remove_admin_bar' ], PHP_INT_MAX, 0 );
		add_filter( 'archive_template', [ $this, 'get_custom_post_type_template' ], PHP_INT_MAX, 10, 3 );
		//add_filter( 'page_template', [$this,'get_page_template'],PHP_INT_MAX,10,3 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		//add_filter( 'woocommerce_cart_ready_to_calc_shipping', [$this,'woocommerce_cart_ready_to_calc_shipping'],10,1 );
	}
	function woocommerce_cart_ready_to_calc_shipping( $bool ) {
		return false;
	}
	function get_the_archive_title( $title, $original_title = null, $prefix = null ) {
		//pre(get_queried_object());
		//return get_the_title(get_queried_object_id());
		return __( 'All Locations', 'wp-locator' );
	}
	function remove_admin_bar() {
		if ( current_user_can( 'edit_location' ) && ! is_admin() ) {
			show_admin_bar( true );
		}
	}

	function enqueue_scripts() {
		//pre(get_page_template_slug());
		$querriedObject = get_queried_object();
		if ( get_post_type() == LOCATION_PT || ( is_page() && get_page_template_slug() == 'templates/locations.php' ) || ( $querriedObject && property_exists( $querriedObject, 'taxonomy' ) && $querriedObject->taxonomy == LOCATION_PT . '_category' ) ) {
			//$url=WPLocator::WP_LOCATOR_URL();
			//$dir=WPLocator::WP_LOCATOR_DIRECTORY();
			wp_register_script( 'vuejs',
				//$url.'lib/vue.min.js',
				'https://unpkg.com/vue@3',
				[ 'jquery' ],
				//filemtime($dir.'lib/vue.min.js'),
				'3',
				false );
			wp_enqueue_script( 'vuejs' );
			wp_localize_script( 'vuejs', 'wp_locator', [ 'vuejs_ajax_url' => admin_url( 'admin-ajax.php' ) ] );
			wp_register_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $this->settings->get( 'google_maps_api_key_front' ) . '&language=' . pll_current_language(), [ 'jquery', 'vuejs' ], false, false );
			wp_enqueue_script( 'google-maps' );
			/*wp_register_script( 'google-maps-markerclusterer',
							  //'https://unpkg.com/@google/markerclusterer@2.0.6/dist/markerclusterer.min.js'
							  //'https://unpkg.com/@googlemaps/markerclustererplus/dist/index.min.js'
							  //'https://unpkg.com/@googlemaps/markerclustererplus@1.2.10/dist/index.min.js'
							  //'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js'
							  ,['jquery','vuejs'],false,false);*/
			wp_enqueue_script( 'google-maps-markerclusterer' );
		}
	}
	function get_custom_post_type_template( $template, $type, $templates ) {
		global $post;
		$querriedObject = get_queried_object();
		if ( is_post_type_archive( 'location' ) || ( $querriedObject && property_exists( $querriedObject, 'taxonomy' ) && $querriedObject->taxonomy == LOCATION_PT . '_category' ) ) {
			add_filter( 'get_the_archive_title', [ $this, 'get_the_archive_title' ], 10, 3 );
			$template = $this->parent->WP_LOCATOR_DIRECTORY() . 'templates/locations.php';
		}
		return $template;
	}

	/*function get_page_template( $template, $type, $templates ) {
			  //echo $template;
			  var_dump($templates);
			  global $post;
			  if ( is_post_type_archive ( 'location' ) ) {
				  add_filter( 'get_the_archive_title', [$this,'get_the_archive_title'] ,10,3);
				  $template = dirname( __FILE__ ) . '/templates/locations.php';
			  }
			  return $template;
		  }*/

}