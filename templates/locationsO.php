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
<div id="store-locator" style="margin-top:0">
	<?php if(current_user_can( 'administrator' )){ ?>
		<div>searchCity: {{searchCity}}:{{typeof(searchCity)}}</div>
		<div>searchCountry: {{searchCountry}}:{{typeof(searchCountry)}}</div>
		<div>selectedCategory: {{selectedCategory}}:{{typeof(selectedCategory)}}</div>
		<div>selectedCountry: {{selectedCountry}}:{{typeof(selectedCountry)}}</div>
		<div>selectedCity: {{selectedCity}}:{{typeof(selectedCity)}}</div>
	<?php } ?>
	<div class="locations_search">
		<div v-if="categories.length"><h3><?= __('Categories','woocommerce') ?></h3><br>
			<select name="categories" v-model="selectedCategory" @change="onChangeCategory($event)">
				<option value="" class="option_all"><?= __('All') ?></option>
				<option v-for="(category,category_key) in categories" :value="category" v-html="category" :key="category_key"></option>
			</select>
		</div>
		<!-- v-if="countries.length"-->
		<div><h3><?= __('Country') ?></h3>
			<input type="text" v-model="searchCountry" autocomplete="off" placeholder="<?= __('Search...') ?>" class="pf-search">
			<select name="countries" v-model="selectedCountry" @change="onChangeCountry($event)">
				<option value="" class="option_all" :key="'all'"><?= __('All') ?></option>
				<option v-for="(country,country_key) in countries" :value="country.code" v-html="country.name" :key="country_key"></option>
			</select>
		</div>
		<!-- v-if="cities.length"-->
		<div><h3><?= __('City') ?></h3>
			<input type="text" v-model="searchCity" autocomplete="off" placeholder="<?= __('Search...') ?>" class="pf-search">
			<select name="cities" v-model="selectedCity" @change="onChangeCity($event)">
				<option value="" class="option_all"><?= __('All') ?></option>
				<option v-for="(city,city_key) in cities" :value="city.code" v-html="city.name" :key="city_key"></option>
			</select>
		</div>
	</div>
	<div v-if="error_message" class="error error-message" v-html="error_message"></div>
	<div id="appresults">
		<div id="map" ref="map"></div>
		<div id="resultstext">
			<div class="location_cards" ref="location_cards">
				<div class="location_card" v-for="location in filtered_locations_paginated">
					
					<div @click.prevent="zoom_location($event,location)" class="location-header">
						<h4 class="location_title" style="margin-top:0px;" v-if="location.title" v-html="location.title"></h4>
						<address class="location-address">
							<div v-if="location.data.route && location.data.street_number">{{location.data.route}}<span v-if="location.data.route && location.data.street_number && location.data.route.trim!='' && location.data.street_number.trim!=''">,</span>{{location.data.street_number}}</div>
							<div v-if="location.data.zip_code && location.data.city">{{location.data.zip_code}} {{location.data.city}}</div>
							<div v-if="location.data.country_name">{{location.data.country_name}}</div>
						</address>
					</div>

					<div class="location-data">
						<div class="location-phone" v-if="location.data.phone"><a :href="'tel:'+location.data.phone" target="_blank">{{location.data.phone}}</a></div>
						<div class="location-email" v-if="location.data.email"><a :href="'mailto:'+location.data.email" target="_blank">{{location.data.email}}</a></div>
						<div class="location-website" v-if="location.data.website"><a :href="location.data.website" target="_blank">{{location.data.website.split(/[?&#]/)[0]}}</a></div>
					</div>
					<div class="location-links">
						<div class="googlemaps-link" v-if="location.place_id && location.place_id!=''">
							<a :href="'https://www.google.com/maps/place/?q=place_id:'+location.place_id" target="_blank">
								<i class="fas fa-map-marker-alt"></i>
							</a>
						</div>
						<div class="goto" v-if="location.data.lng && location.data.lat">
							<a :href="'https://www.google.com/maps/dir/?api=1&destination='+location.data.lat+','+location.data.lng" target="_blank">
								<i class="fas fa-paper-plane"></i>
							</a>
						</div>
					</div>
					<?php if( 0 && current_user_can('administrator')){ ?>
						<pre>{{location}}</pre>
					<?php } ?>
				</div>
			</div>
			<div class="locations_pagination" v-if="numPages>1">
				<button v-for="n in numPages" @click="setPage(n)" :class="{'active has-background has-text-color':n==page+1}">{{ n }}</button>
			</div>
		</div>
	</div>
	<?php if( 0 && current_user_can( 'administrator' )){ ?>
		<!--div>{{selectedCountry}}-{{selectedCity}}-{{selectedCategory}}</div-->

		<!--div v-for="location in locations" style="border:1px solid red;">
			<span v-if="selectedCountry==''||location.data.country==selectedCountry">country</span><span v-else style="color:red">country</span>
			<span v-if="selectedCity==''||selectedCity==undefined||location.data.city==selectedCity">city</span><span v-else style="color:red">city</span>
			<span v-if="selectedCategory==''||(location.categories==null&&selectedCategory=='')||$.inArray(selectedCategory,location.categories)>-1">categories</span><span v-else style="color:red">categories</span>
			{{location.data.city}} {{typeof(selectedCity)}}
		</div-->
		<!--pre>{{locations}}</pre-->
		<div class="flex pad-child">
			<div v-for="location,locations_key in filtered_locations" @click="zoom_location(event=null,location)">
				{{location.data.title}}
			</div>
		</div>

	<?php } ?>
</div>

<style type="text/css">
</style>
<?php 
$root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '';
?>
<script>
	let markers=[],markerClusterer=null;
	let map,
	marker_icon=<?php if(file_exists(get_stylesheet_directory()).'/google-maps/.marker.png'){ ?>"<?= $root.parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) ?>/google-maps/marker.png"<?php }else{?> "null" <?php } ?>,
	clusterer_icons="<?php if(file_exists(get_stylesheet_directory()).'/google-maps/1.png'){ ?><?= $root.parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) ?>/google-maps/<?php }else{?> <?= parse_url( WPLocator::WP_LOCATOR_URL(), PHP_URL_PATH ) ?>/google-maps/ <?php } ?>cluster"
	/*var clusterStyles = [
	{
		opt_textColor: 'white',
		url: clusterer_icons,
		//height: 80,
		//width: 56
	},
	{
		opt_textColor: 'white',
		url: clusterer_icons,
		//height: 80,
		//width: 56
	},
	{
		opt_textColor: 'white',
		url: clusterer_icons,
		//height: 80,
		//width: 56
	},{
		opt_textColor: 'white',
		url: clusterer_icons,
		//height: 80,
		//width: 56
	},{
		opt_textColor: 'white',
		url: clusterer_icons,
		//height: 80,
		//width: 56
	},
	];
	var mcOptions = {
		styles: clusterStyles
	};*/
	const { createApp } = Vue

	const app = createApp({
		el: '#store-locator',
		data(){return{
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
			infowindow:null
		}
	},
	mounted: function () {
			//console.log(wp_locator.vuejs_ajax_url)
		this.$nextTick(function () {
			this.searchcall()
		})
	},
	/*watch:{
		filtered_locations:{
			deep:true,
			handler(new_filtered_locations){
				if(markerClusterer)console.log(new_filtered_locations,markerClusterer.getMarkers())
			}
	},
},
*/

computed:{
	filtered_locations(){
			//console.clear()
			//console.log('__________________________________________')
		if(!this.locations){
			return []
		}
		return this.locations.filter((location) => {
				//return true
			if (
			    (this.selectedCountry=='' || location.data.country==this.selectedCountry) &&
			    (this.selectedCity=='' || location.data.city==this.selectedCity) &&
			    (this.selectedCategory=='' || (location.categories==null && this.selectedCategory=='') || $.inArray(this.selectedCategory,location.categories)>-1)
			    ){
					//console.log(location)
				//if(location.marker)location.marker.setVisible(true)
				return true
		}
		else{
				//if(location.marker)location.marker.setVisible(false)
			return false
		}
	})
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
		$.each(this.locations, function(index, val) {
			if(val.categories!=null){
				$.each(val.categories, function(index2, val2) {
					if(!unique_categories.includes(val2)){
						unique_categories.push(val2)
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
				//console.log(location) 
			if(!location.categories || !location.categories.length || (!location.categories.includes(this.selectedCategory) && this.selectedCategory!=''))return false
				if(location.data.country && location.data.country.toLowerCase()==vueApp.selectedCountry.toLowerCase())found_country=true
					return (location.data.country && location.data.country.toLowerCase().includes(this.searchCountry.toLowerCase()))||(location.data.country_name && location.data.country_name.toLowerCase().includes(this.searchCountry.toLowerCase()));
			})
		if(!filtered_countries.length || !found_country){
			console.log('resetting selectedCountry')
			vueApp.selectedCountry=''
				//return[]
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
		if(map){
				//map.panToBounds(bounds)//fitBounds
			console.log('bounds.getCenter()',bounds.getCenter())
			console.log('getZoomByBounds(map, bounds)',this.getZoomByBounds( map, bounds ))
				//map.panToBounds(bounds)
			map.panTo(bounds.getCenter())
			map.setZoom(this.getZoomByBounds( map, bounds ))
			if(0)this.smoothZoom(map, 12, 
			                     this.getZoomByBounds( map, bounds )
					//map.getZoom()
			                     )
					//map.fitBounds(bounds)
				//map.setZoom(Math.min(Math.max(parseInt(map.getZoom()-6), 1), 24))
		}else{
			console.log('no map')
		}
	},
	getZoomByBounds( map, bounds ){
		var MAX_ZOOM = map.mapTypes.get( map.getMapTypeId() ).maxZoom || 21 ;
		var MIN_ZOOM = map.mapTypes.get( map.getMapTypeId() ).minZoom || 0 ;

		var ne= map.getProjection().fromLatLngToPoint( bounds.getNorthEast() );
		var sw= map.getProjection().fromLatLngToPoint( bounds.getSouthWest() ); 

		var worldCoordWidth = Math.abs(ne.x-sw.x);
		var worldCoordHeight = Math.abs(ne.y-sw.y);

			//Fit padding in pixels 
		var FIT_PAD = 40;

		for( var zoom = MAX_ZOOM; zoom >= MIN_ZOOM; --zoom ){ 
			if( worldCoordWidth*(1<<zoom)+2*FIT_PAD < $(map.getDiv()).width() && 
			   worldCoordHeight*(1<<zoom)+2*FIT_PAD < $(map.getDiv()).height() )
				return zoom;
		}
		return 0;
	},
	smoothZoom (map, max, cnt){
		if (cnt >= max) {
			return;
		}
		else {
			z = google.maps.event.addListener(map, 'zoom_changed', function(event){
				google.maps.event.removeListener(z);
				app.smoothZoom(map, max, cnt + 1);
			});
			setTimeout(function(){map.setZoom(cnt)}, 80); // 80ms is what I found to work well on my system -- it might not work well on all systems
		}
	},
	searchcall: function (newValue='') {
			// And here is our jQuery ajax call
			//console.log(wp_locator)
		if(!wp_locator){
			wp_locator={vuejs_ajax_url: '/wp-admin/admin-ajax.php'}
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
		       	window.setTimeout(function(){
		       		let mapElement=document.getElementById('map')
		       		if(mapElement){
		       			map = new google.maps.Map(mapElement, {
		       				center: {lat: 46.1653665, lng: 6.1047924},
		       				zoom: 6,

		       			})
		       		}else{
		       			console.log('no mapElement?',document.getElementById('map'))
		       		}
						//map.setOptions({styles: get_map_style()})
		       		let marker;
		       		$.each(app.locations, function(index, location) {
		       			let data=location.data
		       			marker= new google.maps.Marker({
		       				position: {lat: parseFloat(data.lat), lng: parseFloat(data.lng)},
		       				title:location.title
		       				,icon:marker_icon
		       			});
		       			location.marker=marker
		       			marker.location=location
		       			marker.addListener('click', function() {
		       				console.log('infowindow',app.infowindow)
		       				if(app.infowindow){
		       					app.infowindow.close()
		       				}
		       				let data=this.location.data
		       				console.log(data,this.location)
		       				app.infowindow = new google.maps.InfoWindow({
		       					content: '<b>'+this.location.title+'</b>'+'<br>'+
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
		       				app.infowindow.open(map, this);
		       			});
		       			marker.setMap(map);
		       			markers.push(marker)
		       		});
		       		markerClusterer = new MarkerClusterer(map, markers,{
		       			<?php if(0){ ?>
								//,mcOptions
		       				{//imagePath: clusterer_icons,
									//imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
									//imageExtension:'png'^,
									//imageSizes:[56,80,20,20],
									//gridSize:
									//maxZoom: 4,
									//height: 32,width: 53,

		       					styles:[
		       					{height:37,width:37,textLineHeight:37,textSize:22,textColor: "#000",url:clusterer_icons+'1.png',anchorText:[18,22]},
		       					{height:37,width:37,textLineHeight:37,textSize:22,textColor: "#000",url:clusterer_icons+'2.png',anchorText:[18,22]},
		       					{height:37,width:37,textLineHeight:37,textSize:22,textColor: "#000",url:clusterer_icons+'3.png',anchorText:[18,22]},
		       					{height:37,width:37,textLineHeight:37,textSize:22,textColor: "#000",url:clusterer_icons+'4.png',anchorText:[18,22]},
		       					{height:37,width:37,textLineHeight:37,textSize:22,textColor: "#000",url:clusterer_icons+'5.png',anchorText:[18,22]},
		       					],
		       				}
		       			<?php }else{
		       				?>
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

		       				<?php 
		       			} ?>
		       		}
		       		);
		       		/*markerClusterer.setCalculator(function (markers, numStyles) {
		       			return {
		       				text: "",
		       				index: 0
		       			};
		       		});*/
		       	},1000)
					//console.log(app.cities)
					//app.selectedCity=app.cities.length?app.cities[0]:''
		       })
.fail(function(error) {
	console.log(error)
	app.error_message = 'There seems to be an error. Please try again later.'
})
},
zoom_location(event=null,location){
				//console.log(event,this,location)
	if(map){
		map.panTo(location.marker.getPosition())
		map.setZoom(21)
	}
	console.log(location.marker)
	console.log('infowindow',app.infowindow)
	if(app.infowindow){
		app.infowindow.close()
	}
	let data=location.data
	console.log(data,location)
	app.infowindow = new google.maps.InfoWindow({
		content: '<b>'+location.title+'</b>'+'<br>'+
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
		                                             '<i class="fas fa-map-marker-alt"></i>'+
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
	app.infowindow.open(map, location.marker);

				//console.log(this.$refs)
	this.$refs.map.scrollIntoView({behavior: 'smooth'})
},
onChangeCategory(event) {
				//app.searchCountry=''
				//app.searchCity=''
				//app.selectedCity=''
	app.map_set_bounds()
},
onChangeCountry(event) {
	app.selectedCity=''
				//console.log(markerClusterer.getMarkers())
	if(0)markerClusterer.getMarkers().forEach((el,index)=>{
		el.setVisible(!el.visible)
		console.log(index,el)
	})
					//markerClusterer.repaint();
		app.map_set_bounds()			
	},
	onChangeCity(event) {
		app.map_set_bounds()
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
