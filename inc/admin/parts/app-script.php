<?php if(!defined('WPINC')){die;} ?>
<script	src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=<?= esc_attr($this->settings->get('google_maps_api_key_front')) ?>"></script>
<script>
	const { createApp } = Vue

	const app = createApp({
		methods:{
			display_popup(lat,lng,name){
				this.show_popup=true
				if(this.marker){
					this.marker.setMap(null)
					this.marker.setPosition({lat: parseFloat(lat), lng: parseFloat(lng)})
					this.marker.setTitle(name)
				}else{
					this.marker=new google.maps.Marker({
						position: {lat: parseFloat(lat), lng: parseFloat(lng)},
						title:name
					})
				}
				this.marker.setMap(this.map)
				this.map.panTo(this.marker.getPosition())
				this.map.setZoom(21)

				if(this.infowindow){
					this.infowindow.close()
				}

				//console.log(data,location)
				this.infowindow = new google.maps.InfoWindow({
					content: '<b>'+name+'</b>'
				});
				this.infowindow.open(this.map, this.marker);

			},
			save_location(location){
				let that=this
				location.saving=true
				console.clear()
				var jqxhr = jQuery.post( this.ajaxurl,{'location':location,'action': 'WPLocator_save_location'}, function(data) {
					console.log( "success",JSON.parse(data));
				})
				.done(function(data) {
					console.log('save_location done',JSON.parse(data))
				})
				.fail(function(jqxhr, textStatus, error) {
					console.log({jqxhr:jqxhr, textStatus:textStatus, error:error})
					var err = textStatus + ", " + error + ", " + jqxhr
					alert( "Request Failed: " + err )
				})
				.always(function() {
					console.log( "complete" )
					location.saving=null
					that.edited_location=null
				});
			},

			arrays_have_same_values(arr1,arr2){
				return arr1.every(element => {
					if (arr2.includes(element)){
						return true;
					}
					return false;
				})
			},
			arrays_have_comon_values(arr1,arr2){
				if(arr1.some(item => arr2.includes(item))){
					//console.log('found:',arr1,arr2)
					return true
				}else{
					//console.warn('arrays not matching:',arr1,arr2)
					return false
				}
				/*for(let i = 0; i < arr1.length; i++) {
					for(let j = 0; j < arr2.length; j++) {
						if(arr1[i] === arr2[j]) {
							return true;
						}
					}
				}
				return false;
			}*/
			},
			get_address_component(result,types=['country'],key='short_name'){
				let address_components = []
				if(result.address_components && Array.isArray(result.address_components)){
					address_components=result.address_components.filter(address_component=>this.arrays_have_comon_values(address_component.types,types))
				}
				if(address_components.length){
					return address_components[0][key]
				}
			},
			get_json_prop(obj,prop_string,fallback_prop_strings=[]){
				let props=prop_string.split('.')
				let res=obj
				if(props.length){
					let i=0
					while(res && props.length && i<20){
						i++
						prop=props.shift()
						if(res[prop])res=res[prop]
						//console.log(res)
					}
			}
			return typeof res==='string'?res:null
		},
		edit_location(location){
			this.edited_location=location.ID
			location.search_google_api=
			[
				location.meta_input._location_settings.name,
				location.meta_input._location_settings.city,
				location.meta_input._location_settings.state,
				location.meta_input._location_settings.route,
				location.meta_input._location_settings.street_number,
				location.meta_input._location_settings.zip_code,
				this.countries[location.meta_input._country],
				].join(' ')
			this.app_search(location.search_google_api,location)
			console.log({edit_location:location})
		},
		app_search(address,location,force=false){
			console.log({'searching': address})
			if (!force && address in localStorage) {
				json=localStorage.getItem(address)
				location.google_data=JSON.parse(json)
				console.log('location retrieved from localStorage')
				console.log(location.google_data)
				return
			}
			var jqxhr = jQuery.post( this.ajaxurl,{'address':address,'action': 'WPLocator_google_api_get'}, function() {
				console.log( "success" );
			})
			.done(function(json) {
				localStorage.setItem(address,json)
				location.google_data=JSON.parse(json)
				console.log(location.google_data)
			})
			.fail(function(jqxhr, textStatus, error) {
				var err = textStatus + ", " + error + ", " + jqxhr
				console.log( "Request Failed: " + err )
			})
			.always(function() {
				console.log( "complete" )
			});
		},
		initMap(){
			//console.log('initMap():refs',this.$refs)
			this.map = new google.maps.Map(
				this.$refs.map,
				{
					zoom: 6,
					center: {lat: 46.1653665, lng: 6.1047924},
				}
				)
		},
	},
	watch: {
		country_filter(newValue) {
			localStorage.setItem('google_compare_country_filter',JSON.stringify(newValue))
		},
	},
	mounted(){
		this.initMap()
		if(data=localStorage.getItem('google_compare_country_filter')){
			this.country_filter=JSON.parse(data)||[]

		}
	},
	computed:{
		available_country_filter_options(){
			return Object.fromEntries(this.locations_countries_keys.map(k => [k, this.country_filter_options[k]]).sort());
		},
		unavailable_country_filter_options(){
			return Object.fromEntries(Object.entries(this.country_filter_options).filter(e => !this.locations_countries_keys.includes(e[0])).sort())
		},
		locations_countries_keys(){
			return [...new Set(this.locations.map(location => location.meta_input._country))].filter(item=>item!='')
		},
		all_countries_keys(){
			return Object.keys(this.countries)
		},
		filtered_locations(){
			//console.log(this.country_filter)
			return this.locations.filter(location=>

				this.edited_location === location.ID
				||	
				(
					(this.search=='' || JSON.stringify(location).toLowerCase().match(this.search.toLowerCase()))
					&&	
					(
					//(this.search=='' || JSON.stringify(location).match(this.search)) && 
					//this.city_search!='' ||

						this.country_filter==null || (
							this.country_filter.includes(location.meta_input._country)
							||	this.country_filter=='' 
							||	(	this.country_filter===null	&&	!this.all_countries_keys.inludes(location.meta_input._country)	)
							)
						) 
					&&
					(this.city_search=='' || (location.meta_input._location_settings.city && location.meta_input._location_settings.city.toLowerCase().includes(this.city_search.toLowerCase())))

					)
				);
		},
		country_filter_options(){
			let country_search=this.country_search.toLowerCase()
			if(country_search==''){
				return Object.fromEntries(Object.entries(this.countries))
			}
			return Object.fromEntries(
				Object.entries(this.countries).filter(([key, value]) => {
					return country_search.includes(key.toLowerCase()) || value.toLowerCase().includes(country_search)
				})
				);

			/*return this.countries.filter((country,country_key)=> (this.country_search='' || JSON.stringify(country).match(this.country_search)) && (this.country_filter!==null && (this.country_filter==country.country || this.country_filter=='')))*/
		},
	},
	data() {
		return {
			/*mapping:{
				address_components:{
					street_number:{
						long_name:'meta_input._location_settings.street_number'
					},
					route:{
						long_name:'meta_input._location_settings.route'
					},
					locality:{
						long_name:'meta_input._location_settings.city'
					},
					administrative_area_level_2:{
						long_name:'meta_input._location_settings.state'
					},
					administrative_area_level_1:{
						long_name:'meta_input._location_settings.city'
					},
					country:{
						short_name:'meta_input._country'
					},
					postal_code:{
						long_name:'meta_input._location_settings.zip_code'
					},
				},
				geometry:{
					location: {
						lat:'meta_input._location_settings.lat',
						lng:'meta_input._location_settings.lng'
					},
				},
				place_id:'meta_input.place_id',
				details: {
					result: {
						international_phone_number: 'meta_input._location_settings.name',
						name: 'meta_input._location_settings.name',
						website:'meta_input._location_settings.website',
					},
				},
			},*/
			show_popup:false,
			map:null,
			marker:null,
			ajaxurl:<?= json_encode(admin_url('admin-ajax.php')) ?>,
			edited_location:null,
			search:'',
			country_search:'',
			city_search:'',
			country_filter:['FR'],
			location_settings_keys:<?= json_encode([
				'name'=>'',
				'city'=>'',
				'state'=>'',
				'lat'=>'',
				'lng'=>'',
				'country_name'=>'',
				'route'=>'',
				'street_number'=>'',
				'zip_code'=>'',
				'website'=>'',
				'phone'=>'',
				'email'=>'',
				]) ?>,
			countries: <?= json_encode(WC()->countries->get_countries()) ?>,
			locations: <?= json_encode(array_map(

				function($post){
					$location_settings=get_post_meta($post->ID,'_location_settings',true);
					if(!is_array($location_settings))$location_settings=[];
					$post->meta_input=[
						'_location_settings'=>array_merge([
							'city'=>'',
							'state'=>'',
							'lat'=>'',
							'lng'=>'',
							'country_name'=>'',
							'route'=>'',
							'street_number'=>'',
							'zip_code'=>'',
							'website'=>'',
							'phone'=>'',
							'email'=>'',
							'name'=>'',
						],$location_settings),
						'_google_compare_done'=>get_post_meta($post->ID,'_google_compare_done',true),
						'_pickup'=>get_post_meta($post->ID,'_pickup',true),
						'_country'=>get_post_meta($post->ID,'_country',true),
						'place_id'=>get_post_meta($post->ID,'place_id',true),
					];
					$post->categories=get_the_terms($post->ID, LOCATION_PT.'_category' );
					return $post;
				},

				get_posts( ['post_type'=>LOCATION_PT,'numberposts'=>-1,'post_status'=>['any','publish','draft'],'suppress_filters'=>true,'exclude'=>[]])

				)) ?>,
			locations_categories:<?= json_encode(get_terms( array(
				'taxonomy' => LOCATION_PT.'_category',
				'hide_empty' => false,
			) )) ?>
		}
	}
})
/*.directive("val",function(el, binding){
	el.val=binding.value
	console.log(el, binding)
})*/
.component("resultRow", resultRow)
.mount('#app')
</script>