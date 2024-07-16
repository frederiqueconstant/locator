<?php if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! WPLocator::import_allowed() ) {
	exit( 'You can not import locations. Please contact Admins' );
}
if ( ! current_user_can( 'administrator' ) || ! get_current_user_id() == 2 )
	exit( 'forbidden' );
$allposts = get_posts( array( 'post_type' => LOCATION_PT, 'numberposts' => -1, 'post_status' => [ 
	'publish',
	'inactive',
	'inactive ',
	'any'
] ) );
echo count( $allposts ) . ' Posts in status : ';
$allposts_statuses = [];
foreach ( $allposts as $eachpost ) {
	if ( ! in_array( $eachpost->post_status, $allposts_statuses ) ) {
		$allposts_statuses[] = $eachpost->post_status;
	}
	if ( ! empty( $_GET['trash_all'] ) )
		wp_delete_post( $eachpost->ID, true );
}
echo join( ',', $allposts_statuses ) . '<hr>';
?>
<div class="wrap">
	<form method="POST" action="#" enctype="multipart/form-data">
		<input type="file" name="import">
		<input type="submit" class="button button-primary" value="<?= esc_attr( __( 'Import' ) ) ?>">
	</form>
	<?php
	if ( isset( $_FILES['import'] ) ) {
		//echo '<pre>';
		//var_dump($_FILES['import']);
		//var_dump($_FILES["import"]["tmp_name"]);
		if ( file_exists( $_FILES["import"]["tmp_name"] ) ) {
			//var_dump(file_get_contents($_FILES["import"]["tmp_name"]));
			$row = 0;
			if ( ( $handle = fopen( $_FILES["import"]["tmp_name"], "r" ) ) !== FALSE ) {

				$countries_obj = new WC_Countries();
				$wc_countries = $countries_obj->__get( 'countries' );
				//var_dump($wc_countries);
				//exit();
				$delimiter = ',';
				$enclosure = '"';
				$escape_char = "\\";
				$keys = [ 'id', 'status', 'country', 'title', 'description', 'place_id', 'categories', 'is_pickup', 'city', 'state', 'lat', 'lng', 'country_name', 'route', 'street_number', 'zip_code', 'website', 'phone', 'email', 'name' ];
				$mapping = [];
				$mysql_time_format = "Y-m-d H:i:s";
				$created = 0;
				$updated = 0;
				while ( ( $data = fgetcsv( $handle, null, $delimiter, $enclosure, $escape_char ) ) !== FALSE ) {
					$row++;
					if ( $row == 1 ) {
						if ( ! empty( array_diff( $keys, $data ) ) ) {
							exit( 'columns are not correct' );
						}
						foreach ( $keys as $key ) {
							$mapping[ $key ] = array_search( $key, $data );
						}
						if ( 0 ) {
							?>
							<pre><?php foreach ( $mapping as $k => $v ) {
								echo $k . '=> ' . $v . PHP_EOL;
							} ?></pre>
						<?php
						}
					} else {
						//echo $row.')'.print_r($data,1).'<br>';
						$post = null;
						if ( ! empty( $data[ $mapping['id'] ] ) ) {
							$post = get_post( $data[ $mapping['id'] ], OBJECT, 'raw' );
						}
						if ( empty( $post ) && ! empty( $data[ $mapping['place_id'] ] ) ) {
							$args = array(
								'post_type' => LOCATION_PT,
								'meta_query' => [ 
									'relation' => 'AND',
									[ 
										'key' => 'place_id',
										'value' => $data[ $mapping['place_id'] ],
										'compare' => '='
									]
								]

							);

							$query = new WP_Query( $args );

							if ( $query->have_posts() ) :
								while ( $query->have_posts() ) :
									$query->the_post();
									$post = get_post( get_the_ID(), OBJECT, 'raw' );
									//$title = get_post_meta($post_id, 'book_title');
								endwhile;
							endif;
						}
						if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) || $post->post_type != LOCATION_PT ) {
							//var_dump(['post'=>$post]);
							//break;
							$created++;
							$post_id = wp_insert_post( [ 
								'post_title' => $data[ $mapping['title'] ],
								'post_status' => in_array( $data[ $mapping['status'] ], [ 'publish', 'draft', 'trash' ] ) ? $data[ $mapping['status'] ] : 'draft',
								'post_type' => LOCATION_PT,
								'post_name' => sanitize_title( $data[ $mapping['title'] ] ),
								'tax_input' => [ LOCATION_PT . '_category' => $data[ $mapping['categories'] ] ], //array_map(function($cat){return [LOCATION_PT.'_category'=>trim($cat)];}, explode(',',$data[$mapping['categories']])),
								'post_excerpt' => $data[ $mapping['description'] ],
								'meta_input' => [ 
									'_location_settings' => [ 
										'city' => $data[ $mapping['city'] ],
										'state' => $data[ $mapping['state'] ],
										'lat' => $data[ $mapping['lat'] ],
										'lng' => $data[ $mapping['lng'] ],
										'country_name' => $data[ $mapping['country_name'] ],
										'route' => $data[ $mapping['route'] ],
										'street_number' => $data[ $mapping['street_number'] ],
										'zip_code' => $data[ $mapping['zip_code'] ],
										'website' => $data[ $mapping['website'] ],
										'phone' => $data[ $mapping['phone'] ],
										'email' => $data[ $mapping['email'] ],
										'name' => $data[ $mapping['name'] ],
									],
									'_pickup' => ! empty( $data[ $mapping['is_pickup'] ] ),
									'_country' => $data[ $mapping['country'] ],
									'place_id' => $data[ $mapping['place_id'] ],
									'imported' => 1,
								],
							] );
							//$post=get_post($post_id);
						} else {
							//echo '<hr>';
							//continue;
							$updated++;
							wp_update_post( [ 
								'ID' => $post->ID,
								'post_title' => $data[ $mapping['title'] ],
								'post_status' => in_array( $data[ $mapping['status'] ], [ 'publish', 'draft', 'trash' ] ) ? $data[ $mapping['status'] ] : 'draft',
								'post_type' => LOCATION_PT,
								'post_name' => sanitize_title( $data[ $mapping['title'] ] ),
								'tax_input' => [ LOCATION_PT . '_category' => $data[ $mapping['categories'] ] ], //array_map(function($cat){return [LOCATION_PT.'_category'=>trim($cat)];}, explode(',',$data[$mapping['categories']])),
								'post_excerpt' => $data[ $mapping['description'] ],
								'post_modified' => gmdate( $mysql_time_format, strtotime( current_time( 'mysql' ) ) ),
								'post_modified_gmt' => gmdate( $mysql_time_format, ( strtotime( current_time( 'mysql' ) ) + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
								'meta_input' => [ 
									'_location_settings' => [ 
										'city' => $data[ $mapping['city'] ],
										'state' => $data[ $mapping['state'] ],
										'lat' => $data[ $mapping['lat'] ],
										'lng' => $data[ $mapping['lng'] ],
										'country_name' => $data[ $mapping['country_name'] ],
										'route' => $data[ $mapping['route'] ],
										'street_number' => $data[ $mapping['street_number'] ],
										'zip_code' => $data[ $mapping['zip_code'] ],
										'website' => $data[ $mapping['website'] ],
										'phone' => $data[ $mapping['phone'] ],
										'email' => $data[ $mapping['email'] ],
										'name' => $data[ $mapping['name'] ],
									],
									'_pickup' => ! empty( $data[ $mapping['is_pickup'] ] ),
									'_country' => $data[ $mapping['country'] ],
									'place_id' => $data[ $mapping['place_id'] ],
									'imported' => 1,
								]
							] );
							//break;
						}
					}
				}
				echo '<h2>' . $created . ' location' . ( $created > 1 ? 's' : '' ) . ' created and ' . $updated . ' location' . ( $updated > 1 ? 's' : '' ) . ' updated</h2>';
			}
		}
	}
	?>

	<p>First <b>line (columns)</b> of the csv file must be as:</p>
	<pre><?php foreach ( [ 'id', 'status (publish/draft/trash/pending)', 'country (2 letters country code)', 'title', 'description', 'place_id', 'categories (coma separated list of terms)', 'is_pickup (1 if used for pickups)', 'city', 'state', 'lat', 'lng', 'country_name', 'route', 'street_number', 'zip_code', 'website', 'phone', 'email', 'name' ] as $k => $v ) {
		echo $k . '=> ' . $v . PHP_EOL;
	} ?></pre>
	<p>If <b>id</b> column is empty or not found or not of the same post type location will be created else it will be
		replaced.</p>
</div>