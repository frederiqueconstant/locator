<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
class WPLocator_settings {
	/**
	 * Gets the option for the given name. Returns the default value if the value does not exist.
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	private static $option_key = 'wp-locator';
	private static $option = null;
	private static $needs_save = false;
	private static $initialized = false;



	function __construct() {
		if ( ! self::$initialized ) {
			add_action( 'shutdown', [ $this, 'save_option' ] );
			self::$initialized = true;
		} else {
			exit( 'already initialized' );
		}
	}

	private function get_option() {
		return get_option( self::$option_key, [ 
			'google_maps_api_key' => '',
			'google_maps_api_key_front' => '',
			'google_maps_marker_pin_url' => '',
		] );
	}
	public function get( $name ) {
		if ( self::$option == null ) {
			self::$option = $this->get_option();
		}
		if ( is_array( self::$option ) && array_key_exists( $name, self::$option ) )
			return self::$option[ $name ];
		return null;
	}

	public function remove( $name ) {
		unset( self::$option[ $name ] );
		self::$needs_save = true;
		//update_option(self::$option_key,self::$option);
	}

	public function set( $name, $value ) {
		self::$option[ $name ] = $value;
		self::$needs_save = true;
		//update_option(self::$option_key,self::$option);
	}

	public function save_option() {
		if ( self::$needs_save )
			update_option( self::$option_key, self::$option );
	}

}