<?php if (!defined('WPINC')) {
	die;
} ?>
<div id="store-locator" style="margin-top:0" v-cloak>
	<div :class="{loading:loading}">
		<div class="loader" v-if="loading"></div>
		<?php /* if (current_user_can('administrator')) {
	
	echo '<div>' . pll_current_language() . '</div>';
	?>
	<div>searchCity: {{searchCity}}:{{typeof(searchCity)}}</div>
	<div>searchCountry: {{searchCountry}}:{{typeof(searchCountry)}}</div>
	<div>selectedCategory: {{selectedCategory}}:{{typeof(selectedCategory)}}</div>
	<div>selectedCountry: {{selectedCountry}}:{{typeof(selectedCountry)}}</div>
	<div>selectedCity: {{selectedCity}}:{{typeof(selectedCity)}}</div>
	<?php }*/ ?>
		<div class="locations_search">
			<div v-if="categories.length">
				<h3><?= __('Categories', 'woocommerce') ?></h3><br>
				<select name="categories" v-model="selectedCategory" @change="onChangeCategory($event)">
					<option v-if="categories.length>1" value="" class="option_all"><?= __('All', 'wp-locator') ?></option>
					<option v-for="(category,category_key) in categories.filter(cat=>cat!='')" :value="category" v-html="category" :key="category_key"></option>
				</select>
			</div>
			<!-- v-if="countries.length"-->
			<div>
				<h3><?= __('Country', 'wp-locator') ?></h3>
				<input type="text" v-model="searchCountry" autocomplete="off" placeholder="<?= __('Search...', 'wp-locator') ?>" class="pf-search">
				<select name="countries" v-model="selectedCountry" @change="onChangeCountry($event)"><!--@keydown="input($event,searchCountry)"-->
					<option value="" class="option_all" :key="'all'"><?= __('All', 'wp-locator') ?></option>
					<option v-for="(country,country_key) in countries" :value="country.code" v-html="country.name" :key="country_key"></option>
				</select>
			</div>
			<!-- v-if="cities.length"-->
			<div>
				<h3><?= __('City', 'wp-locator') ?></h3>
				<input type="text" v-model="searchCity" autocomplete="off" placeholder="<?= __('Search...', 'wp-locator') ?>" class="pf-search">
				<select name="cities" v-model="selectedCity" @change="onChangeCity($event)">
					<option value="" class="option_all"><?= __('All', 'wp-locator') ?></option>
					<option v-for="(city,city_key) in cities" :value="city.code" v-html="city.name" :key="city_key"></option>
				</select>
			</div>
			<div>
				<h3><?= __('Search...', 'wp-locator') ?></h3>
				<input type="text" v-model="searchText" autocomplete="off" placeholder="<?= __('Search...', 'wp-locator') ?>" class="pf-search">
			</div>
		</div>
		<div v-if="error_message" class="error error-message" v-html="error_message"></div>
		<div id="appresults">
			<div id="map" ref="map"></div>
			<div id="resultstext">
				<div class="location_cards" ref="location_cards">
					<div class="location_card" v-for="location in filtered_locations_paginated" @click="zoom_location($event,location)">
						<?php if (current_user_can('administrator')) {
						?>
							<a :href="'/?p='+location.id" target="_blank">View</a>
							<!--{{ JSON.stringify({id:location.id,data:location.data},null,2) }}-->
						<?php
						} ?>
						<div class="location-header">
							<h4 class="location_title" style="margin-top:0px;" v-if="location.data.name" v-html="location.data.name"></h4>
							<address class="location-address">
								<div v-if="location.data.route || location.data.street_number"><span v-if="location.data?.route">{{location.data.route}}</span><span v-if="location.data.route && location.data.street_number && location.data.route.trim!='' && location.data.street_number.trim!=''">,</span><span v-if="location.data?.street_number">{{location.data.street_number}}</span></div>
								<div v-if="location.data?.zip_code || location.data?.city"><span v-if="location.data?.zip_code">{{location.data.zip_code}}</span> <span v-if="location.data?.city">{{location.data.city}}</span></div>
								<div v-if="location.data?.state && (!location.data?.city || location.data?.city!=location.data?.state)">{{location.data?.state}}</div>
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
					</div>
				</div>
				<div class="locations_pagination" v-if="numPages>1">
					<button v-for="n in numPages" @click="setPage(n)" :class="{'active has-background has-text-color':n==page+1}">{{ n }}</button>
				</div>
			</div>
		</div>
	</div>
</div>