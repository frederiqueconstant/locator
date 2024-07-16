<?php if (!defined('WPINC')) {
	die;
} ?>
<script>
	jQuery(document).ready(function($) {
		let map, initmap_trials = 0,
		{
			createApp
		} = Vue
		var loacator_picker = createApp({
			data() {
				return {
					loading: false,

					o_location_settings_meta: null,
					o_place_id: null,

					location_settings_meta: <?= json_encode($location_settings_meta) ?>,
					_country: <?= json_encode($_country) ?>,
					place_id: <?= json_encode($place_id) ?>,
					is_pickup: <?= json_encode($is_pickup) ?>,
					post_categories: <?= json_encode($post_categories) ?>,
					categories: <?= json_encode($categories) ?>,
					results: null,
					countries: <?= json_encode($wc_countries) ?>,
					fields: <?= json_encode($fields) ?>,
				}
			},
			created() {
				this.o_location_settings_meta = {
					...this.location_settings_meta
				}
				this.o_place_id = this.place_id
				console.log(this)

			},
			mounted() {
				this.initMap()
			},
			methods: {
				submit(){
					$('#publishing-action input[type="submit"]').click()
				},
				fetch($event) {
					this.loading = true
					loacator_picker.loading = true
					console.log(this.location_settings_meta.search)
					var jqxhr = $.post(ajaxurl, {
						'address': this.location_settings_meta.search,
						'action': 'WPLocator_google_api_get',
						'whatever': 1234
					}, function() {
						console.log("success")
					})
					.done(function(json) {
						loacator_picker.process_json(json)
					})
					.fail(function(jqxhr, textStatus, error) {
						var err = textStatus + ", " + error + ", " + jqxhr
						console.log("Request Failed: " + err, jqxhr, textStatus, error)
						alert("Request Failed: " + err)
					})
					.always(function() {
						console.log("complete")
						loacator_picker.loading = false
						this.loading = false
					})
					jqxhr.always(function() {
						console.log("second complete")
						this.loading = false
					})
				},
				process_json(json) {
					if (json == '') {
						alert('no results found')
						return 'empty response'
					}
					try {
						data = JSON.parse(json)
						console.log({
							'data': data
						})
						if ('results' in data && Array.isArray(data.results) && data.results.length > 0) {
							console.log(data.results)
							this.results = data.results
						} else {
							console.log('no results in data')
							this.results = data.results
						}
					} catch (e) {
						alert(e) // error in the above string (in this case, yes)!
						console.log(json)
					}
				},
				initMap(event) {
					if (isNaN(parseFloat(this.location_settings_meta.lat)) || isNaN(parseFloat(this.location_settings_meta.lng))) {
						return
					}
					initmap_trials++
					let map = document.getElementById("map")
					if ((!google || !map) && initmap_trials < 5) {
						setTimeout(() => {
							this.initmap()
						}, 1000)
						return
					}
					var myLatlng = new google.maps.LatLng(parseFloat(this.location_settings_meta.lat), parseFloat(this.location_settings_meta.lng))
					console.log({
						lat: parseFloat(this.location_settings_meta.lat),
						lng: parseFloat(this.location_settings_meta.lng)
					})
					map = new google.maps.Map(map, {
						center: {
							lat: parseFloat(this.location_settings_meta.lat),
							lng: parseFloat(this.location_settings_meta.lng)
						},
						zoom: 16,
					})
					var marker = new google.maps.Marker({
						position: myLatlng,
						title: ""
					})
					marker.setMap(map)
					//map.setZoom(24);
					initmap_trials = 0
					return false
				},
				process_result(n) {
					if (typeof data === 'undefined') {
						alert('no address components in result')
						return
					}
					if (typeof data.results[n] === "object" && data.results[n] !== null) {
						let result = data.results[n]
						console.log(result)
						if ('address_components' in result) {
							result.address_components.forEach((prop, key) => {
								switch (prop.types.join('-')) {
								case "street_number":
									this.location_settings_meta.street_number = prop.long_name
									break
								case "route":
									this.location_settings_meta.route = prop.long_name
									break
								case "locality-political":
									this.location_settings_meta.city = prop.long_name
									break
								case "administrative_area_level_2-political":
									this.location_settings_meta.state = prop.long_name
									break
								case "administrative_area_level_1-political":
									if (this.location_settings_meta.city.trim() == '') this.location_settings_meta.city = prop.long_name
										break
								case "country-political":
										//this.location_settings_meta.country_name=prop.long_name)
									this.location_settings_meta.country = prop.short_name
									break
								case "postal_code":
									this.location_settings_meta.zip_code = prop.long_name
									break
								default:
									console.log(prop.types.join(), prop, key)
								}

							})
						}
						if ('geometry' in result && 'location' in result.geometry) {
							if ('lat' in result.geometry.location)
								this.location_settings_meta.lat = result.geometry.location.lat
							if ('lng' in result.geometry.location)
								this.location_settings_meta.lng = result.geometry.location.lng
						}
						['name', 'website'].forEach(key => {
							if (key in result && result[key].trim() != '') {
								this.location_settings_meta[key] = result[key]
							}
						});
						if ('international_phone_number' in result) {
							this.location_settings_meta.phone = result.international_phone_number
						} else if ('formatted_phone_number' in result) {
							this.location_settings_meta.phone = result.formatted_phone_number
						}
						['place_id'].forEach(key => {
							if (key in result && result[key].trim() != '') {
								this[key] = result[key]
							}
						});
						var new_title = this.location_settings_meta.search
						/*[this.location_settings_meta.name, this.location_settings_meta.zip_code, this.location_settings_meta.city, ].filter(function(el) {
							return el != null && el != '' && el != 'undefined'
						}).join(', ');*/
						this.location_settings_meta.searched = this.location_settings_meta.search
						if (wp.data && wp.data.dispatch('core/editor')) {
							wp.data.dispatch('core/editor').editPost({
								title: new_title
							})
						} else {
							$('#post-body-content #title').val(new_title)
						}
						if (new_title != '') {
							document.getElementById('title-prompt-text').classList.add("screen-reader-text")
						} else {
							document.getElementById('title-prompt-text').classList.remove("screen-reader-text")
						}

						document.getElementById('title').dispatchEvent(new Event('input', {
							bubbles: true
						}))
						this.initMap()
					} else {
						alert('no address components in result')
					}
				},
			},
		}).mount('#app')
		//$('#save-post,#publish').clone(true).attr('id', '').appendTo($('#app-post-actions'))
})
</script>