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
if (!defined('WPINC')) {
    die;
}
/*if(0)if(!current_user_can('administrator') || get_current_user_id()!==2){
    include('locationsO.php');return;
}*/
if (!isset($_SERVER['HTTP_REFERER']) || str_starts_with($_SERVER['HTTP_REFERER'], get_site_url())) {
    get_header();
} else {
?>
    <!doctype html>
    <html <?php language_attributes(); ?> <?php //html_classes();
                                            ?>>

    <head>
        <meta charset="<?php bloginfo('charset'); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>
        <?php wp_body_open(); ?>
        <div id="page" class="site">
            <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'theme'); ?></a>


            <div id="content" class="site-content">
                <div id="primary" class="content-area">
                    <main id="main" class="site-main" role="main">
                    <?php
                }
                if (0 && !is_page()) {
                    ?><a href="<?= get_permalink() ?>"><?php the_title(); ?></a><?php
                                                                                return;
                                                                            }
                                                                            $description = get_the_archive_description();
                                                                            //echo get_stylesheet_directory().'<br>'.get_stylesheet_directory_uri().'<hr>';
                                                                            //pre(WC_Geolocation::geolocate_ip());
                                                                            //echo parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH );
                                                                                ?>

                    <header class="page-header alignwide" style="margin-bottom:0">
                        <?php //the_archive_title( '<h1 class="page-title">', '</h1>' );
                        ?>
                        <?php if ($description) : ?>
                            <div class="archive-description"><?php echo wp_kses_post(wpautop($description)); ?></div>
                        <?php endif; ?>
                    </header><!-- .page-header -->
                    <style>
                        <?php include('parts/style.css');
                        ?>
                    </style>

                    <?php include('parts/app.php'); ?>

                    <?php
                    $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '';
                    ?>
                    <!--script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script-->
                    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
                    <!--https://github.com/googlemaps/js-markerclusterer/blob/47c6fb51355001a827fa472c5193670ca523f2cc/src/markerclusterer.ts#L165-->
                    <script>
                        jQuery(document).ready(function($) {
                            let renderer = {
                                render: function({
                                    count,
                                    position
                                }, stats) {
                                    // change color if this cluster has more markers than the mean cluster
                                    const color = count > Math.max(10, stats.clusters.markers.mean) ? "hsl(194deg 100% 24%)" : "hsl(0deg 100% 24%)";
                                    // create svg url with fill color
                                    const svg = window.btoa(`
				<svg fill="${color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
				<circle cx="120" cy="120" opacity=".8" r="70" />
				<circle cx="120" cy="120" opacity=".4" r="90" />
				<circle cx="120" cy="120" opacity=".2" r="110" />
				</svg>`);
                                    // create marker using svg icon
                                    return new google.maps.Marker({
                                        position,
                                        icon: {
                                            url: `data:image/svg+xml;base64,${svg}`,
                                            scaledSize: new google.maps.Size(45, 45),
                                        },
                                        label: {
                                            text: String(count),
                                            color: "rgba(255,255,255,0.9)",
                                            fontSize: "12px",
                                        },
                                        title: `Cluster of ${count} markers`,
                                        // adjust zIndex to be above other markers
                                        zIndex: Number(google.maps.Marker.MAX_ZINDEX) + count,
                                    });
                                }
                            }

                            /*
                             */
                            let //this.map,
                                marker_icon =
                                <?php
                                //WP_LOCATOR_URL()
                                //WP_LOCATOR_DIRECTORY()
                                $marker = WPLocator::get_instance()->front->settings->get('google_maps_marker_pin_url');
                                if (!empty($marker)) {
                                    echo json_encode($marker);
                                } elseif (file_exists(get_stylesheet_directory() . '/google-maps/marker-pin.png')) {
                                ?> "<?= $root . parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH) ?>/google-maps/marker-pin.png"
                        <?php
                                } elseif (file_exists(WPLocator::WP_LOCATOR_DIRECTORY() . 'google-maps/marker-pin.png')) {
                        ?>
                                "<?= parse_url(WPLocator::WP_LOCATOR_URL(), PHP_URL_PATH) ?>google-maps/marker-pin.png"
                        <?php
                                } else {
                        ?>
                            null
                        <?php
                                } ?>,
                        clusterer_icons = "<?php
                                            if (file_exists(get_stylesheet_directory()) . '/google-maps/1.png') { ?><?= $root . parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH) ?>/google-maps/<?php
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        ?><?= parse_url(WPLocator::WP_LOCATOR_URL(), PHP_URL_PATH) ?>/google-maps/ <?php
                                                                                                                                                                                                                                                                                } ?>cluster"

                        const icon = {
                            url: marker_icon,
                            //size: new google.maps.Size(40,54),
                            //origin: new google.maps.Point(0,0),
                            //anchor: new google.maps.Point(20,27),
                        }


                        const {
                            createApp
                        } = Vue
                        const store_locator_app = createApp({
                            el: '#store-locator',
                            data() {
                                return {
                                    loading: true,
                                    map: null,
                                    markerCluster: null,
                                    error_message: null,
                                    locations: [],
                                    wc_countries: <?php
                                                    $countries_obj   = WC()->countries;
                                                    $wc_countries = $countries_obj->get_countries(); //__get('countries');
                                                    echo json_encode((array)$wc_countries);
                                                    ?>,
                                    searchText: '',
                                    searchCity: '',
                                    searchCountry: '',
                                    selectedCategory: "<?= esc_attr(pll__(get_post_meta(get_queried_object_id(), 'default_location_category', 1), 'wp-locator')) ?>",
                                    hiddenCategories: <?= json_encode(array_map(function ($cat) {
                                                            return pll__($cat, 'wp-locator');
                                                        }, (array)get_post_meta(get_queried_object_id(), 'hidden_location_categories', 1))) ?>,
                                    selectedCountry: '<?= WPLocator::get_country() ?>',
                                    selectedCity: '',
                                    page: 0,
                                    itemsPerPage: 5,
                                    infowindow: null,
                                }
                            },
                            mounted: function() {
                                console.log({
                                    selectedCountry: this.selectedCountry
                                })
                                this.initMap()
                                //this.$nextTick(()=>{
                                this.query_locations()
                                //})
                            },
                            watch: {
                                filtered_locations: {
                                    deep: false,
                                    immediate: false,
                                    handler(new_filtered_locations, old_filtered_locations) {
                                        //console.log('watch.filtered_locations')
                                        let all_markers = this.locations.map(location => location.marker)
                                        let markers_to_keep = new_filtered_locations.map(location => location.marker)
                                        let markers_to_remove = all_markers.filter(marker => !markers_to_keep.includes(marker))
                                        //console.log(markers_to_remove)
                                        markers_to_remove.forEach((marker, index) => {
                                            //marker.setVisible(false)
                                            //marker.setMap(null)
                                            index = this.markerCluster.markers.indexOf(marker)
                                            if (index !== -1) this.markerCluster.markers.splice(index, 1);
                                        })
                                        markers_to_keep.forEach((marker, index) => {
                                            //marker.setMap(this.map)
                                            //marker.setVisible(true)
                                            if (!this.markerCluster.markers.includes(marker)) {
                                                this.markerCluster.markers.push(marker);
                                            }
                                        })
                                        //console.log(markers_to_keep)
                                        //console.warn(all_markers.length,markers_to_remove.length,markers_to_keep.length)
                                        /*		if(0){
                                        	this.$nextTick(()=>{
                                        		console.warn('filtered_locations_watched',this.map?'has map':'no map')
                                        		//this.generate_markerCluster(new_filtered_locations.map(location=>location.marker))
                                        		old_filtered_locations.forEach((location,index)=>{
                                        			location.marker.setMap(null)
                                        			location.marker.setVisible(false)
                                        			index = this.markerCluster.markers.indexOf(location.marker)
                                        			if(index !== -1)this.markerCluster.markers.splice(index, 1);
                                        		})
                                        		//this.markerCluster.removeMarkers(old_filtered_locations.map(location=>location.marker),true)
                                        		new_filtered_locations.forEach((location,index)=>{
                                        			location.marker.setMap(this.map)
                                        			location.marker.setVisible(true)
                                        			if (!this.markerCluster.markers.includes(location.marker)) {
                                        				this.markerCluster.markers.push(location.marker);
                                        			}

                                        			//this.markerCluster.addMarker(location.marker)
                                        		})

                                        		//this.markerCluster.addMarkers(old_filtered_locations.map(location=>location.marker))
                                        	})
                                        }*/
                                        //google.maps.event.removeListener(this.markerCluster.idleListener);
                                        //this.markerCluster.reset();
                                        this.map_set_bounds()
                                        //this.markerCluster.reset()
                                        //this.markerCluster.render()
                                        //console.log('watch.filtered_locations done')
                                    }
                                },
                            }, //watch
                            computed: {
                                filtered_locations() {
                                    console.time('filtering locations')
                                    let ret
                                    if (!this.locations) {
                                        ret = []
                                    } else {
                                        let searchText = this.searchText.toLowerCase()
                                        ret = this.locations.filter((location) => {
                                            if (!location.marker) {
                                                this.set_location_marker(location)
                                            }
                                            //console.log('categories:',location.categories)
                                            if (
                                                (this.selectedCountry == '' || location.data.country == this.selectedCountry) &&
                                                (this.selectedCity == '' || location.data.city == this.selectedCity) &&
                                                (this.selectedCategory == '' || !Array.isArray(location.categories) || location.categories.includes(this.selectedCategory)) &&
                                                (searchText == '' || (
                                                    JSON.stringify(location.data).toLocaleLowerCase().includes(searchText)
                                                    //|| location.data.name && location.data.name.toLowerCase().includes(searchText) 
                                                    //|| location.data.city && location.data.city.toLowerCase().includes(searchText) 
                                                    //|| location.data.route && location.data.route.toLowerCase().includes(searchText)
                                                ))
                                            ) {
                                                //if(location.marker)location.marker.setVisible(true)
                                                return true
                                            } else {
                                                //if(location.marker)location.marker.setVisible(false)
                                                return false
                                            }
                                        })
                                    }
                                    console.timeEnd('filtering locations')
                                    return ret
                                },
                                fixedItemsPerPage() {
                                    return Math.max(this.itemsPerPage, Math.ceil(this.filtered_locations.length / 6));
                                },
                                numPages() {
                                    let numPages = Math.ceil(this.filtered_locations.length / this.fixedItemsPerPage);
                                    if (this.page > numPages) this.page = numPages - 1
                                    return numPages
                                },
                                filtered_locations_paginated() {
                                    return this.paginate()
                                },
                                categories() {
                                    let unique_categories = [this.selectedCategory]
                                    <?php if (!empty($default_category = get_post_meta(get_queried_object_id(), 'default_location_category', 1))) {
                                    ?>
                                        return <?= json_encode([pll__($default_category, 'wp-locator')]) ?>
                                    <?php
                                    } else {
                                    ?>
                                        this.locations.forEach((location, loc_index) => {
                                            //console.log('categories()',location, loc_index)
                                            if (location.categories != null) {
                                                location.categories.forEach((category, cat_index) => {
                                                    if (!unique_categories.includes(category)) {
                                                        unique_categories.push(category)
                                                    }
                                                });
                                            }
                                        });
                                        let orderedCategories = unique_categories.sort((a, b) => {
                                            return b - a;
                                        })
                                        return orderedCategories;
                                    <?php
                                    } ?>

                                },
                                countries() {
                                    let vueApp = this
                                    if (!this.locations) {
                                        return []
                                    }
                                    let found_country = false
                                    let filtered_countries = this.locations.filter((location) => {
                                        if (!location.categories || !location.categories.length || (!location.categories.includes(this.selectedCategory) && this.selectedCategory != '')) return false
                                        if (location.data.country && location.data.country.toLowerCase() == vueApp.selectedCountry.toLowerCase()) found_country = true
                                        return (location.data.country && location.data.country.toLowerCase() == this.searchCountry.toLowerCase() /*.includes(this.searchCountry.toLowerCase())*/ ) || (location.data.country_name && location.data.country_name.toLowerCase().includes(this.searchCountry.toLowerCase()));
                                    })
                                    if (vueApp.selectedCountry != '')
                                        if (filtered_countries.length && !found_country) {
                                            console.log('resetting selectedCountry')
                                            vueApp.selectedCountry = ''
                                        }
                                    let unique_countries = [...new Set(filtered_countries.map(function(location) {
                                        if (location.data.country && vueApp.wc_countries.hasOwnProperty(location.data.country)) {
                                            location.data.country_name = vueApp.wc_countries[location.data.country]
                                        }
                                        if (!location.data.country || !location.data.country_name) {
                                            if (location.data.country && vueApp.wc_countries.hasOwnProperty(location.data.country)) {
                                                location.data.country_name = vueApp.wc_countries[location.data.country]
                                            } else {
                                                console.log(location.data)
                                            }
                                        }
                                        return {
                                            code: location.data.country,
                                            name: location.data.country_name
                                        }
                                    }))];
                                    unique_countries = Array.from(
                                        new Set(unique_countries.map((object) => JSON.stringify(object)))
                                    ).map((string) => JSON.parse(string))

                                    let orderedCountries = unique_countries.sort((a, b) => {
                                        let A = a.name ? a.name.toUpperCase() : '',
                                            B = b.name ? b.name.toUpperCase() : ''
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
                                cities() {
                                    let vueApp = this
                                    if (!this.locations) {
                                        return []
                                    }
                                    let found_city = false
                                    let filtered_cities = this.locations.filter((location) => {
                                        if (!location.categories || !location.categories.length || (!location.categories.includes(this.selectedCategory) && this.selectedCategory != '')) return false
                                        if (location.data.city && vueApp.selectedCity && location.data.city.toLowerCase() == vueApp.selectedCity.toLowerCase()) found_city = true
                                        return (this.searchCity == '' || (location.data.city && location.data.city.toLowerCase().includes(this.searchCity.toLowerCase()))) && (this.selectedCountry == '' || (location.data.country && location.data.country.toLowerCase().includes(this.selectedCountry.toLowerCase())));
                                    })
                                    if (vueApp.selectedCity != '')
                                        if (!filtered_cities.length || !found_city) {
                                            //console.log('resetting selectedCity')
                                            vueApp.selectedCity = ''
                                            //return[]
                                        }
                                    let unique_cities = [...new Set(filtered_cities.map(function(location) {
                                        return {
                                            code: location.data.city,
                                            name: location.data.city
                                        }
                                    }))];
                                    unique_cities = Array.from(
                                        new Set(unique_cities.map((object) => JSON.stringify(object)))
                                    ).map((string) => JSON.parse(string))
                                    let orderedCities = unique_cities.sort((a, b) => {
                                        let A = a.name ? a.name.toUpperCase() : '',
                                            B = b.name ? b.name.toUpperCase() : ''
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
                                /*input($event,where){
                                	console.log($event,where)
                                },*/
                                initMap() {
                                    console.time('initMap')
                                    //console.log('initMap():refs',this.$refs)
                                    this.map = new google.maps.Map(
                                        this.$refs.map, {
                                            zoom: 6,
                                            center: {
                                                lat: 46.1653665,
                                                lng: 6.1047924
                                            },
                                            mapTypeControlOptions: {
                                                mapTypeIds: ["roadmap", "satellite", "hybrid", "terrain", "styled_map"],
                                            },
                                        }
                                    )
                                    this.map.mapTypes.set("styled_map", this.get_map_style());
                                    this.map.setMapTypeId("styled_map");
                                    console.timeEnd('initMap')
                                },
                                setPage(page) {
                                    this.page = page - 1;
                                    //this.paginedCandidates = this.paginate()
                                    this.$refs.location_cards.scrollTo({
                                        top: 0
                                    })
                                    this.$refs.location_cards.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'start'
                                    })
                                },
                                paginate() {
                                    return this.filtered_locations.slice(this.page * this.fixedItemsPerPage, this.fixedItemsPerPage * this.page + this.fixedItemsPerPage)
                                },
                                map_set_bounds: function() {
                                    var bounds = new google.maps.LatLngBounds();
                                    this.filtered_locations.forEach((location, index) => {
                                        if (location.data.lat && location.data.lng) {
                                            bounds.extend(new google.maps.LatLng(location.data.lat, location.data.lng));
                                        } else {
                                            console.log('latlng error', location)
                                        }
                                    });
                                    this.map.panTo(bounds.getCenter())
                                    this.map.setZoom(this.getZoomByBounds(this.map, bounds))
                                },
                                getZoomByBounds(map, bounds) {
                                    let mapTypeId = this.map.getMapTypeId()
                                    let map_type = this.map.mapTypes.get(mapTypeId)
                                    let MAX_ZOOM = 21
                                    let MIN_ZOOM = 0
                                    if (map_type) {
                                        MAX_ZOOM = map_type.maxZoom ? map_type.maxZoom : 21
                                        MIN_ZOOM = map_type.minZoom ? map_type.minZoom : 0
                                    }
                                    let mapProjection = this.map.getProjection()
                                    if (mapProjection) {
                                        let ne = mapProjection.fromLatLngToPoint(bounds.getNorthEast());
                                        let sw = mapProjection.fromLatLngToPoint(bounds.getSouthWest());

                                        let worldCoordWidth = Math.abs(ne.x - sw.x);
                                        let worldCoordHeight = Math.abs(ne.y - sw.y);
                                        let FIT_PAD = 40;
                                        for (let zoom = MAX_ZOOM; zoom >= MIN_ZOOM; --zoom) {
                                            if (worldCoordWidth * (1 << zoom) + 2 * FIT_PAD < $(this.map.getDiv()).width() &&
                                                worldCoordHeight * (1 << zoom) + 2 * FIT_PAD < $(this.map.getDiv()).height())
                                                return zoom;
                                        }
                                    }
                                    return 0;
                                },
                                set_locations_markers(locations) {
                                    //console.log('set_locations_markers',locations,locations.forEach)
                                    locations.forEach((location, index) => {
                                        if (!location.marker) {
                                            this.set_location_marker(location)
                                        }
                                    })
                                },
                                set_location_marker(location) {
                                    let data = location.data
                                    if (!data || !data.lat || !data.lng) {
                                        console.error(location)
                                    }
                                    //data.name=location.name
                                    /*
                                    origin: new google.maps.Point(0, 0),
                                    anchor: new google.maps.Point(0, 32),
                                    */


                                    let marker = new google.maps.Marker({
                                        position: {
                                            lat: parseFloat(data.lat),
                                            lng: parseFloat(data.lng)
                                        },
                                        title: data.name,
                                        icon: icon
                                    })
                                    location.marker = marker;
                                    marker.location_data = data
                                    google.maps.event.addListener(marker, 'click', function() {
                                        //console.log('infowindow',store_locator_app.infowindow)
                                        if (store_locator_app.infowindow) {
                                            store_locator_app.infowindow.close()
                                        }
                                        let data = this.location_data
                                        //console.log(data,this)
                                        store_locator_app.infowindow = store_locator_app.get_infowindow({
                                            place_id: location.place_id,
                                            ...data
                                        })
                                        store_locator_app.infowindow.open(store_locator_app.map, this);
                                    });
                                    //marker.setMap(this.map);
                                    //markers.push(marker)
                                },
                                generate_markerCluster(markers) {
                                    let map = this.map
                                    //let render=this.render
                                    let algorithm = new markerClusterer.SuperClusterAlgorithm({
                                        //radius: 60,
                                        //minZoom:0,
                                        //maxZoom: 16
                                    })
                                    //console.log(algorithm,)
                                    this.markerCluster = new markerClusterer.MarkerClusterer({
                                        algorithm,
                                        map,
                                        markers,
                                        renderer: renderer
                                    })
                                    //console.log(this.markerCluster)
                                },
                                query_locations: function(newValue = '') {
                                    let vueApp = this
                                    // And here is our jQuery ajax call
                                    //console.log(wp_locator)
                                    if (!wp_locator) {
                                        wp_locator = {
                                            vuejs_ajax_url: <?= json_encode(admin_url('admin-ajax.php')) ?>
                                        }
                                    }

                                    console.time("locations retrieved");
                                    $.post(
                                            //type:"POST",
                                            wp_locator.vuejs_ajax_url, {
                                                action: 'wp_locator_query',
                                                lang: <?= json_encode(pll_current_language('slug')) ?>,
                                                // search_string:vm.search_val
                                            }
                                        ).done(function(data) {
                                            console.timeEnd("locations retrieved");
                                            console.time("markers set");
                                            let new_locations = JSON.parse(data)
                                            //console.log('query_locations',new_locations,vueApp.map)
                                            if (vueApp.hiddenCategories.length > 0 && Array.isArray(new_locations)) {
                                                new_locations = new_locations.filter(location => {
                                                    return !vueApp.hiddenCategories.some(item => location.categories && Array.isArray(location.categories) && location.categories.includes(item))
                                                })
                                            }

                                            vueApp.set_locations_markers(new_locations)

                                            vueApp.locations = new_locations
                                            let markers = new_locations.map((location, index) => {
                                                //console.log(location.marker)
                                                return location.marker
                                            })
                                            console.timeEnd('markers set')
                                            console.time("generate_markerCluster");
                                            vueApp.generate_markerCluster(markers)
                                            console.timeEnd('generate_markerCluster')
                                        })
                                        .fail(function(error) {
                                            console.log(error)
                                            vueApp.error_message = 'There seems to be an error. Please try again later.'
                                        }).always(function() {
                                            vueApp.loading = false
                                        });
                                },
                                render({
                                    count,
                                    position
                                }, stats) {
                                    // change color if this cluster has more markers than the mean cluster
                                    const color = count > Math.max(10, stats.clusters.markers.mean) ? "#ff6969" : "#00a2d3";
                                    // create svg url with fill color
                                    const svg = window.btoa(`
						<svg fill="${color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
						<circle cx="120" cy="120" opacity=".6" r="70" />
						<circle cx="120" cy="120" opacity=".3" r="90" />
						<circle cx="120" cy="120" opacity=".2" r="110" />
						</svg>`);
                                    // create marker using svg icon
                                    return new google.maps.Marker({
                                        position,
                                        icon: {
                                            url: `data:image/svg+xml;base64,${svg}`,
                                            scaledSize: new google.maps.Size(145, 145),
                                        },
                                        label: {
                                            text: String(count),
                                            color: "rgba(255,255,255,0.9)",
                                            fontSize: "12px",
                                        },
                                        title: `Cluster of ${count} markers`,
                                        // adjust zIndex to be above other markers
                                        zIndex: Number(google.maps.Marker.MAX_ZINDEX) + count,
                                    });
                                },
                                get_infowindow(data) {
                                    return new google.maps.InfoWindow({
                                        content: '<b>' + data.name + '</b>' + '<br>' +
                                            data.route + ((data.route && data.street_number && data.route.trim != '' && data.street_number.trim != '') ? ', ' : '') + data.street_number + '<br>' +
                                            data.zip_code + ' ' + data.city + '<br>' +
                                            ((data.state && (!data.city || data.city != data.state)) ? data.state /*+' '+data.country*/ + '<br>' : '') +
                                            data.country_name + '<br>' +
                                            '<br>' +
                                            (data.website && data.website.trim() != '' ? ('<a href="' + data.website.replace(/"/g, "&#34;") + '" target="_blank">' + data.website + '</a><br><br>') : '') +
                                            (data.phone && data.phone.trim() != '' ? ('<a href="tel:' + data.phone.replace(/"/g, "&#34;") + '" target="_blank">' + data.phone + '</a><br><br>') : '') +
                                            (data.email && data.email.trim() != '' ? ('<a href="mailto:' + data.email.replace(/"/g, "&#34;") + '" target="_blank">' + data.email + '</a><br><br>') : '') +
                                            //'<br><div class="location-links">'+
                                            /*(location.place_id && location.place_id!=''?(
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
                                            ):'')+*/
                                            '<br><div class="location-links">\
						<div class="googlemaps-link">\
						<a href="https://www.google.com/maps/place/?q=place_id:' + data.place_id + '" target="_blank">\
						<i class="fas fa-map-marker-alt"></i>\
						</a>\
						</div>\
						<div class="goto">\
						<a href="https://www.google.com/maps/dir/?api=1&destination=' + data.lat + ',' + data.lng + '" target="_blank">\
						<i class="fas fa-paper-plane"></i>\
						</a>\
						</div>\
						</div>' +
                                            '' //'</div>'
                                    });
                                },
                                zoom_location(event = null, location) {
                                    //console.log(event,this,location)
                                    if (this.map) {
                                        this.map.panTo(location.marker.getPosition())
                                        this.map.setZoom(21)
                                    }
                                    //console.log(location.marker)
                                    //console.log('infowindow',store_locator_app.infowindow)
                                    if (store_locator_app.infowindow) {
                                        store_locator_app.infowindow.close()
                                    }
                                    let data = location.data
                                    //console.log(data,location)
                                    store_locator_app.infowindow = this.get_infowindow({
                                        place_id: location.place_id,
                                        ...data
                                    })
                                    store_locator_app.infowindow.open(store_locator_app.map, location.marker);

                                    //console.log(this.$refs)
                                    this.$refs.map.scrollIntoView({
                                        behavior: 'smooth'
                                    })
                                },
                                onChangeCategory(event) {
                                    //app.searchCountry=''
                                    //app.searchCity=''
                                    //app.selectedCity=''
                                    //app.map_set_bounds()
                                },
                                onChangeCountry(event) {
                                    store_locator_app.selectedCity = ''
                                    //console.log(this.markerCluster.getMarkers())

                                    //this.markerCluster.repaint();
                                    //app.map_set_bounds()			
                                },
                                onChangeCity(event) {
                                    //app.map_set_bounds()
                                },
                                get_map_style() {
                                    <?php include('parts/map-style.js'); ?>
                                }
                            }
                        }).mount('#store-locator')



                        });
                    </script>

                    <?php //$WPLocator->wp_locator_query();
                    ?>
                    <?php if (!isset($_SERVER['HTTP_REFERER']) || str_starts_with($_SERVER['HTTP_REFERER'], get_site_url())) {
                        get_footer();
                    } else {

                    ?>
                    </main><!-- #main -->
                </div><!-- #primary -->
            </div><!-- #content -->


        </div><!-- #page -->

        <?php wp_footer(); ?>

    </body>

    </html>
<?php
                    }
?>