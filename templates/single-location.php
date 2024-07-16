<?php if(!defined('WPINC')){die;}
get_header();
?>
<div class="is-layout-flow wp-block-group alignwide" style="padding:20px;"><div class="wp-block-group__inner-container">
	<h1><?= get_the_title() ?></h1>
	<?php 
	$location_settings=(array) get_post_meta(get_the_ID(),'_location_settings',1);
	foreach ([
		[
			'name'=>__('Name','wp-locator'),
		],
		[
			'lat'=>__('Latitude','wp-locator'),
			'lng'=>__('Longitude','wp-locator'),
		],[
			'route'=>__('Address (route)','wp-locator'),
			'street_number'=>__('Number','wp-locator'),
		],[
			'city'=>__('City','wp-locator'),
		],[
			'zip_code'=>__('Zip Code','wp-locator'),
			'state'=>__('State','wp-locator'),
		],[
			'country_name'=>__('Country','wp-locator'),
		],[
			'website'=>__('Website','wp-locator'),
			'phone'=>__('International phone number','wp-locator'),
		],[
			'email'=>__('℮-mail','wp-locator')
		]
	] as $row_key => $row) {
		?>
		<div style=" display:flex;gap:25px;">
			<?php foreach ($row as $key => $value){ ?>
				<div><label style="font-weight: bold;"><?= $value ?></label> : <span><?php if(array_key_exists($key,$location_settings)){echo $location_settings[$key];} ?></span></div>
			<?php } ?>
		</div>
		<?php 
		//echo $key .' : '. $value . '<br>';
	}
	if(0){
		?><pre><?php print_r((array) get_post_meta(get_the_ID(),'_location_settings',1)) ?></pre><?php 
	}
	?>
</div></div>
<?php 
get_footer();
