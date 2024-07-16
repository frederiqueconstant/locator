<?php
/**
* The template for displaying locations archive pages
*
* @link https://developer.wordpress.org/themes/basics/template-hierarchy/
*
* @package WordPress
* @subpackage WP_locator
* @since WP_locator 1.0
*/
if(!current_user_can('administrator') || get_current_user_id()!==2){
	include('locationsO.php');return;
}
get_header();
if(0 && !is_page()){
	?><a href="<?= get_permalink() ?>"><?php the_title(); ?></a><?php  
	return;
}
$description = get_the_archive_description();
//echo get_stylesheet_directory().'<br>'.get_stylesheet_directory_uri().'<hr>';
//pre(WC_Geolocation::geolocate_ip());
//echo parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH );
?>

<header class="page-header alignwide" style="margin-bottom:0">
	<?php //the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
	<?php if ( $description ) : ?>
		<div class="archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
	<?php endif; ?>
</header><!-- .page-header -->
<?php include('parts/app.php'); ?>
<?php 
$root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '';
?>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script>
	let markers=[],
	//markerCluster
	markerClusterOptions={
		minimumClusterSize:2,
		//ignoreHidden: true,
		minClusterSize: 2,
		zoomOnClick: true,
		maxZoom: 24,
		'styles':[{
			width: 30,
			height: 30,
			className: "custom-clustericon-1",
		},{
			width: 40,
			height: 40,
			className: "custom-clustericon-2",
		},{
			width: 50,
			height: 50,
			className: "custom-clustericon-3",
		}],
		'clusterClass':'custom-clustericon'
	}
	let //this.map,
	marker_icon=<?php if(file_exists(get_stylesheet_directory()).'/google-maps/.marker.png'){ ?>"<?= $root.parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) ?>/google-maps/marker.png"<?php }else{?> "null" <?php } ?>,
	clusterer_icons="<?php if(file_exists(get_stylesheet_directory()).'/google-maps/1.png'){ ?><?= $root.parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) ?>/google-maps/<?php }else{?> <?= parse_url( WPLocator::WP_LOCATOR_URL(), PHP_URL_PATH ) ?>/google-maps/ <?php } ?>cluster"
	const { createApp } = Vue
	const app = createApp({
		el: '#store-locator',
		data(){
			return {
				map:null,
				markerCluster:null,
				error_message:null,
				locations:[],
				wc_countries:<?php 
				$countries_obj   = WC()->countries;
				$wc_countries=$countries_obj->__get('countries');
				echo json_encode((array)$wc_countries);
				?>,
				searchCity:'',
				searchCountry:'',
				selectedCategory:"<?= esc_attr(get_post_meta(get_queried_object_id(), 'default_location_category', 1)) ?>",
				selectedCountry:'<?= WPLocator::get_country() ?>',
				selectedCity:'',
				page:0,
				itemsPerPage: 5,
				infowindow:null,
			}
		},
		mounted: function () {
			this.$nextTick(()=>{
				this.query_locations()
			})
		},
		watch:{
			filtered_locations:{
				deep:false,
				immediate:false,
				handler(new_filtered_locations,old_filtered_locations){
					//console.clear()
					console.log('new_filtered_locations',new_filtered_locations,Array.isArray(new_filtered_locations))
					console.log('old_filtered_locations',old_filtered_locations,Array.isArray(old_filtered_locations))
					if(1){
						if(Array.isArray(old_filtered_locations))old_filtered_locations.forEach((location, loc_index) =>{
							console.log('hide',location, loc_index)
							if(location.marker){
								location.marker.setVisible(false)

							}
						});
					}
					if(1){
						if(Array.isArray(this.locations))this.locations.forEach((location, loc_index) =>{
							if(location.marker){
								location.marker.setVisible(false)
								/*if(this.markerCluster){
									this.markerCluster.removeMarker(location.marker)
								}*/
							}
						});
					}

					if(Array.isArray(new_filtered_locations))new_filtered_locations.forEach((location, loc_index) =>{
						console.log('show',location, loc_index)
						if(location.marker){
							location.marker.setVisible(true)
						}
					});
						
						if(this.markerCluster){
							this.markerCluster.clearMarkers()
							//this.markerCluster.resetViewport()
						}
						let markers=this.locations.map(location=>location.marker).filter(marker=>marker.visible)
						console.log('markers',markers.map(marker=>marker.location.data.name+' : '+marker.location.data.city))
						if(this.map_check_or_init()){
							if(!this.markerCluster){
								console.log('this.map',this.map)
							this.markerCluster = new markerClusterer.MarkerClusterer({map:this.map/*, markers:markers*/});//,markerClusterOptions
						}
						if(1){
							console.log('resetting this.markerCluster',this.map,this.markerCluster)
							this.markerCluster.clearMarkers()
							//this.markerCluster.resetViewport();
							this.markerCluster.addMarkers(markers)
						}
						//this.markerCluster.repaint()
					}
					console.log({map:this.map,markerCluster:this.markerCluster.markers.map(marker=>marker.title+': '+(marker.visible?'visible':'hidden'))})
					//this.map_set_bounds()
				}
			},
		},//watch
		computed:{
			filtered_locations(){
				let ret
				if(!this.locations){
					ret = []
				}else{
					ret = this.locations.filter((location) => {
						if(!location.marker){
							this.set_location_marker(location)
						}
						console.log('categories:',location.categories)
						if (
							(this.selectedCountry=='' || location.data.country==this.selectedCountry) &&
							(this.selectedCity=='' || location.data.city==this.selectedCity) &&
							(this.selectedCategory=='' || location.categories.includes(this.selectedCategory))
							//&& (this.selectedCategory=='' || (location.categories==null && this.selectedCategory=='') || location.categories.includes(this.selectedCategory))
							){
							//if(location.marker)location.marker.setVisible(true)
						return true
					}else{
						//if(location.marker)location.marker.setVisible(false)
						return false
					}
				})
				}
				return ret
			},
			fixedItemsPerPage(){
				return Math.max(this.itemsPerPage,Math.ceil(this.filtered_locations.length/6));
			},
			numPages() {
				let numPages=Math.ceil(this.filtered_locations.length/this.fixedItemsPerPage);
				if(this.page>numPages)this.page=numPages-1
					return numPages
			},
			filtered_locations_paginated(){
				return this.paginate()
			},
			categories(){
				let unique_categories = [this.selectedCategory]
				this.locations.forEach((location, loc_index) =>{
					console.log(location, loc_index)
					if(location.categories!=null){
						location.categories.forEach((category, cat_index)=>{
							if(!unique_categories.includes(category)){
								unique_categories.push(category)
							}
						});
					}
				});
				let orderedCategories = unique_categories.sort((a, b) => {
					return b - a;
				})
				return orderedCategories;
			},
			countries(){
				let vueApp=this
				if(!this.locations){
					return []
				}
				let found_country=false
				let filtered_countries = this.locations.filter((location) => {
					if(!location.categories || !location.categories.length || (!location.categories.includes(this.selectedCategory) && this.selectedCategory!=''))return false
						if(location.data.country && location.data.country.toLowerCase()==vueApp.selectedCountry.toLowerCase())found_country=true
							return (location.data.country && location.data.country.toLowerCase().includes(this.searchCountry.toLowerCase()))||(location.data.country_name && location.data.country_name.toLowerCase().includes(this.searchCountry.toLowerCase()));
					})
				if(!filtered_countries.length || !found_country){
					console.log('resetting selectedCountry')
					vueApp.selectedCountry=''
				}
				let unique_countries = [...new Set(filtered_countries.map(function(location){ 
					if(!location.data.country || !location.data.country_name){
						if(location.data.country && app.wc_countries.hasOwnProperty(location.data.country)){
							location.data.country_name=app.wc_countries[location.data.country]
						}else{
							console.log(location.data)
						}
					}
					return {
						code:location.data.country,
						name:location.data.country_name
					}
				}))];
				unique_countries=Array.from(
					new Set(unique_countries.map((object) => JSON.stringify(object)))
					).map((string) => JSON.parse(string))

				let orderedCountries = unique_countries.sort((a, b) => {
					let A=a.name?a.name.toUpperCase():'',B=b.name?b.name.toUpperCase():''
					if (A < B) {
						return -1;
					}
					if (A > B) {
						return 1;
					}
					return 0;
				})
				return orderedCountries;
			},
			cities(){
				let vueApp=this
				if(!this.locations){
					return []
				}
				let found_city=false
				let filtered_cities = this.locations.filter((location) => {
					if(!location.categories || !location.categories.length || (!location.categories.includes(this.selectedCategory) && this.selectedCategory!=''))return false
						if(location.data.city && vueApp.selectedCity && location.data.city.toLowerCase()==vueApp.selectedCity.toLowerCase())found_city=true
							return (this.searchCity=='' || (location.data.city && location.data.city.toLowerCase().includes(this.searchCity.toLowerCase()))) && (this.selectedCountry=='' || (location.data.country && location.data.country.toLowerCase().includes(this.selectedCountry.toLowerCase())));
					})
				if(!filtered_cities.length || !found_city){
					console.log('resetting selectedCity')
					vueApp.selectedCity=''
					//return[]
				}
				let unique_cities = [...new Set(filtered_cities.map(function(location){ 
					return {
						code:location.data.city,
						name:location.data.city
					}
				}))];
				unique_cities=Array.from(
					new Set(unique_cities.map((object) => JSON.stringify(object)))
					).map((string) => JSON.parse(string))
				let orderedCities = unique_cities.sort((a, b) => {
					let A=a.name?a.name.toUpperCase():'',B=b.name?b.name.toUpperCase():''
					if (A < B) {
						return -1;
					}
					if (A > B) {
						return 1;
					}
					return 0;
				})
				return orderedCities;
			}
		},
		methods: {
			redraw(){

			},
			markerCluster_check_or_init(){
				if(this.map_check_or_init()){
					if(!this.markerCluster){
						let markers=this.locations.map(location=>location.marker)
					this.markerCluster = new markerClusterer.MarkerClusterer({map:this.map/*,markers:markers*/}/*this.filtered_locations.map(location=>location.marker)*/);//,markerClusterOptions
				}
			}else{
				return null
			}
			return this.markerCluster
		},
		map_check_or_init(){
			if(!this.map){
				let mapElement=document.getElementById('map')
				if(mapElement){
					this.map = new google.maps.Map(mapElement, {
						center: {lat: 46.1653665, lng: 6.1047924},
						zoom: 6,
					})
				}
			}
			return this.map
		},
		setPage(page) {
			this.page = page-1;
			//this.paginedCandidates = this.paginate()
			this.$refs.location_cards.scrollTo({top:0})
			this.$refs.location_cards.scrollIntoView({behavior: 'smooth', block: 'start'})
		},
		paginate() {
			return this.filtered_locations.slice(this.page*this.fixedItemsPerPage, this.fixedItemsPerPage * this.page + this.fixedItemsPerPage)        
		},
		map_set_bounds:function(){
			var bounds = new google.maps.LatLngBounds();
			//console.log(app.filtered_locations.length)
			$(app.filtered_locations).each(function(index, location){
				//console.log(location)
				if(location.data.lat && location.data.lng){
					bounds.extend(new google.maps.LatLng(location.data.lat, location.data.lng));
				}else{
					console.log('latlng error',location)
				}
			});
			//console.log([[bounds.getSouthWest().lat(),bounds.getSouthWest().lng()],[bounds.getNorthEast().lat(),bounds.getNorthEast().lng()]])
			if(this.map_check_or_init()){
				//this.map.panToBounds(bounds)//fitBounds
				console.log('bounds.getCenter()',bounds.getCenter())
				console.log('getZoomByBounds(this.map, bounds)',this.getZoomByBounds( this.map, bounds ))
				//this.map.panToBounds(bounds)
				this.map.panTo(bounds.getCenter())
				this.map.setZoom(this.getZoomByBounds( this.map, bounds ))
				if(0)this.smoothZoom(this.map, 12, 
					this.getZoomByBounds( this.map, bounds )
					//this.map.getZoom()
					)
					//this.map.fitBounds(bounds)
				//this.map.setZoom(Math.min(Math.max(parseInt(this.map.getZoom()-6), 1), 24))
			}else{
				console.error('could not set bounds: no this.map')
			}
		},
		getZoomByBounds( map, bounds ){
			console.log(this.map)
			let mapTypeId = this.map.getMapTypeId()
			let map_type=this.map.mapTypes.get( mapTypeId )
			var MAX_ZOOM = 21
			var MIN_ZOOM = 0
			if(map_type){
				MAX_ZOOM = map_type.maxZoom?map_type.maxZoom:21
				MIN_ZOOM = map_type.minZoom?map_type.minZoom:0
			}
			let mapProjection=this.map.getProjection()
			if(!mapProjection)return
				var ne= this.map.getProjection().fromLatLngToPoint( bounds.getNorthEast() );
			var sw= this.map.getProjection().fromLatLngToPoint( bounds.getSouthWest() ); 

			var worldCoordWidth = Math.abs(ne.x-sw.x);
			var worldCoordHeight = Math.abs(ne.y-sw.y);

			//Fit padding in pixels 
			var FIT_PAD = 40;

			for( var zoom = MAX_ZOOM; zoom >= MIN_ZOOM; --zoom ){ 
				if( worldCoordWidth*(1<<zoom)+2*FIT_PAD < $(this.map.getDiv()).width() && 
					worldCoordHeight*(1<<zoom)+2*FIT_PAD < $(this.map.getDiv()).height() )
					return zoom;
			}
			return 0;
		},
		smoothZoom (map, max, cnt){
			if (cnt >= max) {
				return;
			}
			else {
				/*z = google.maps.event.addListener(this.map, 'zoom_changed', function(event){
					google.maps.event.removeListener(z);
					app.smoothZoom(this.map, max, cnt + 1);
				});*/
				setTimeout(function(){this.map.setZoom(cnt)}, 80); // 80ms is what I found to work well on my system -- it might not work well on all systems
			}
		},
		set_markers(){
			this.filtered_locations.forEach((location,index)=>{
				if(!location.marker){
					this.set_location_marker(location)
				}
				/*if(location.marker && !location.marker.getMap()){
					location.marker.setMap(this.map)
				}*/
			})

		},
		set_location_marker(location){
			let marker,	data=location.data
			marker = new google.maps.Marker({
				position: {lat: parseFloat(data.lat), lng: parseFloat(data.lng)},
				title:location.data.name
				,icon:marker_icon
			});
			marker.location=location
			location.marker=marker
			marker.addListener('click', function() {
				//console.log('infowindow',app.infowindow)
				if(app.infowindow){
					app.infowindow.close()
				}
				let data=this.location.data
				console.log(data,this.location,this)
				app.infowindow = new google.maps.InfoWindow({
					content: '<b>'+this.location.data.name+'</b>'+'<br>'+
					data.route+((data.route && data.street_number && data.route.trim!='' && data.street_number.trim!='')?', ':'')+data.street_number+'<br>'+
					data.zip_code+' '+data.city+'<br>'+
					data.state+' '+data.country+'<br>'+
					'<br>'+
					data.country_name+'<br>'+
					'<br>'+
					(data.website?('<a href="'+data.website.replace(/"/g, "&#34;")+'" target="_blank">WEBSITE</a><br>'):'')+
					'<br>'+
					(data.phone?('<a href="tel:'+data.phone.replace(/"/g, "&#34;")+'" target="_blank">'+data.phone+'</a>'):'')+
					'<br>'
				});
				app.infowindow.open(this.map, this);
			});
			//marker.setMap(this.map);
			//markers.push(marker)
		},
		query_locations: function (newValue='') {
			let that=this
			// And here is our jQuery ajax call
			//console.log(wp_locator)
			if(!wp_locator){
				wp_locator={vuejs_ajax_url: <?= json_encode(admin_url('admin-ajax.php')) ?>}
			}
			$.post(
				//type:"POST",
				wp_locator.vuejs_ajax_url,
				{
					action:'wp_locator_query',
					// search_string:vm.search_val
				}
				).done(function(data) {
					//console.log(data)
					app.locations=JSON.parse(data)
					//console.log(app.locations)
					window.setTimeout(function(that){
						if(that.map_check_or_init()){
							that.markerCluster_check_or_init()
						}
						//this.map.setOptions({styles: get_map_style()})
						that.locations.forEach((location,index)=>{
							that.set_location_marker(location)
						});

					},1000,that)
				})
				.fail(function(error) {
					console.log(error)
					app.error_message = 'There seems to be an error. Please try again later.'
				})
			},
			zoom_location(event=null,location){
				//console.log(event,this,location)
				if(this.map){
					this.map.panTo(location.marker.getPosition())
					this.map.setZoom(21)
				}
				console.log(location.marker)
				console.log('infowindow',app.infowindow)
				if(app.infowindow){
					app.infowindow.close()
				}
				let data=location.data
				console.log(data,location)
				app.infowindow = new google.maps.InfoWindow({
					content: '<b>'+location.data.name+'</b>'+'<br>'+
					data.route+((data.route && data.street_number && data.route.trim!='' && data.street_number.trim!='')?', ':'')+data.street_number+'<br>'+
					data.zip_code+' '+data.city+'<br>'+
					data.state+' '+data.country+'<br>'+
					'<br>'+
					data.country_name+'<br>'+
					'<br>'+
					(data.website?('<a href="'+data.website.replace(/"/g, "&#34;")+'" target="_blank">WEBSITE</a><br>'):'')+
					'<br>'+
					(data.phone?('<a href="tel:'+data.phone.replace(/"/g, "&#34;")+'" target="_blank">'+data.phone+'</a>'):'')+
					'<br>'+

					'<div class="location-links">'+
					(location.place_id && location.place_id!=''?(
						'<div class="googlemaps-link">'+
						'<a href="https://www.google.com/maps/place/?q=place_id:'+location.place_id+'" target="_blank">'+
						'<i class="fas fa-this.map-marker-alt"></i>'+
						'</a>'+
						'</div>'
						):'')+
					(data.lat && data.lat!='' && data.lng && data.lng!=''?(
						'<div class="goto">'+
						'<a href="https://www.google.com/maps/dir/?api=1&destination='+data.lat+','+data.lng+'" target="_blank">'+
						'<i class="fas fa-location-arrow"></i>'+
						'</a>'+
						'</div>'
						):'')+
					'</div>'
				});
				app.infowindow.open(this.map, location.marker);

				//console.log(this.$refs)
				this.$refs.map.scrollIntoView({behavior: 'smooth'})
			},
			onChangeCategory(event) {
				//app.searchCountry=''
				//app.searchCity=''
				//app.selectedCity=''
				//app.map_set_bounds()
			},
			onChangeCountry(event) {
				app.selectedCity=''
				//console.log(this.markerCluster.getMarkers())

				//this.markerCluster.repaint();
				//app.map_set_bounds()			
			},
			onChangeCity(event) {
				//app.map_set_bounds()
			},
		}
	}).mount('#store-locator')

function get_map_style(){
	<?php include('parts/map-style.js'); ?>
}

</script>

<style>
	<?php include('parts/style.css'); ?>
</style>

<?php //$WPLocator->wp_locator_query(); ?>
<?php get_footer(); ?>
