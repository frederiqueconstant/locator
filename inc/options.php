<?php if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
?>
<div class="wrap">
	<?php
	if ( isset( $_POST['google_maps_api_key'] ) ) {
		$this->settings->set( 'google_maps_api_key', trim( sanitize_text_field( $_POST['google_maps_api_key'] ) ) );
	}
	if ( isset( $_POST['google_maps_api_key_front'] ) ) {
		$this->settings->set( 'google_maps_api_key_front', trim( sanitize_text_field( $_POST['google_maps_api_key_front'] ) ) );
	}
	if ( isset( $_POST['google_maps_marker_pin_url'] ) ) {
		$this->settings->set( 'google_maps_marker_pin_url', trim( sanitize_text_field( $_POST['google_maps_marker_pin_url'] ) ) );
	}
	echo '<p>Google Maps api key:</p>';
	?>
	<form method="POST" action="./?page=wp-locator">
		<label for="google_maps_api_key" style="display:block;margin-bottom:2em;">Needs <b>Places API</b> & <b>Geocoding
				API</b> with <b>server IP Address</b> restriction <input type="text" name="google_maps_api_key"
				style="display: block;width:333px;" value="<?= esc_attr( $this->settings->get( 'google_maps_api_key' ) ) ?>"
				id="google_maps_api_key">
		</label> <label for="google_maps_api_key_front" style="display:block;margin-bottom:2em;">Needs <b>Maps
				JavaScript API</b> with <b>HTTP referrers (websites)</b> restriction
			<input type="text" name="google_maps_api_key_front" style="display: block;width:333px;"
				value="<?= esc_attr( $this->settings->get( 'google_maps_api_key_front' ) ) ?>"
				id="google_maps_api_key_front">
		</label>
		<label for="google_maps_marker_pin_url" style="display:block;margin-bottom:2em;"><b>Google Maps Marker URL</b>
			(Better to use a media from same domain)
			<input type="text" name="google_maps_marker_pin_url" style="display: block;width:333px;"
				value="<?= esc_attr( $this->settings->get( 'google_maps_marker_pin_url' ) ) ?>"
				id="google_maps_marker_pin_url">
		</label>
		<input type="submit" class="button button-primary" value="<?= esc_attr( __( 'Save' ) ) ?>">
	</form>
	<hr>
	<?php if ( 0 ) { ?>
		<form method="POST" action="./?page=wp-locator" enctype="multipart/form-data">
			<input type="file" name="import">
			<input type="submit" class="button button-primary" value="<?= esc_attr( __( 'Import' ) ) ?>">
		</form>
		<?php
	}
	?>
</div>
<?php