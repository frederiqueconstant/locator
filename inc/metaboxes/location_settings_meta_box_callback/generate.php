<?php if ( ! defined( 'WPINC' ) ) {
	die;
}
wp_nonce_field( 'location_settings_nonce', 'location_settings_nonce' );
$fields = [ 
	[ 
		'route' => 'Address (route without number)',
		'street_number' => 'Number',
	], [ 
		'city' => 'City',
	], [ 
		'zip_code' => 'Zip Code',
		'state' => 'State',
	], [ 
		'html' => '<hr><h1>4) Store details</h1>',
	], [ 
		'website' => 'Website',
		'phone' => 'International phone number',
	], [ 
		'email' => '℮-mail'
	],
	[ 
		'html' => '
		<hr><h1>5) Geolocation</h1><p>If you don\'t know about geolocation coordinates simply verify location on the map<p>
		<p class="important">If any field is empty the location will not show on the <i>Store locator</i></p>
		',
	],
	[ 
		'lat' => 'Latitude',
		'lng' => 'Longitude',
	],
];
$location_settings_meta = (array) get_post_meta( $post->ID, '_location_settings', true );
$location_settings_meta = wp_parse_args( $location_settings_meta,
	array_map( '__return_empty_string', array_filter( array_merge( ...$fields ), function ($key) {
		return $key != 'html';
	}, ARRAY_FILTER_USE_KEY ) )
);
$_country = get_post_meta( $post->ID, '_country', true );
if ( empty( $location_settings_meta['country'] ) )
	$location_settings_meta['country'] = $_country;
$place_id = get_post_meta( $post->ID, 'place_id', true );
$is_pickup = get_post_meta( $post->ID, '_pickup', true );
$post_categories = wp_get_post_terms( $post->ID, LOCATION_PT . '_category', [ 'fields' => 'ids' ] );
$categories = get_terms( [ 
	'taxonomy' => LOCATION_PT . '_category',
	'hide_empty' => false,
] );

$countries_obj = new WC_Countries();
$wc_countries = $countries_obj->__get( 'countries' );
$can_edit_post_country = get_user_meta( get_current_user_id(), 'can_edit_locations_countries', true );
if ( ! is_array( $can_edit_post_country ) )
	$can_edit_post_country = [];
//var_dump($can_edit_post_country);
if ( ! empty( $can_edit_post_country ) ) {
	foreach ( $wc_countries as $key => $label ) {
		if ( ! in_array( $key, $can_edit_post_country ) && $key != $_country ) {
			unset( $wc_countries[ $key ] );
		}
	}
}