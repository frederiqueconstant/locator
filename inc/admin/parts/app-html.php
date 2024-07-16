<?php if(!defined('WPINC')){die;} ?>
<div id="app" style="display:flex;">
	<div>
		<div class="flex inline wrap sticky-left">
			<div class="flex column">
				<label class="flex gap">
					<div class="flex-1">Search </div>
					<input type="text" v-model="search">
				</label>
				<label class="flex gap">
					<div class="flex-1">Country </div>
					<!--{{locations_countries_keys}}-->
					<input type="text" v-model="country_search">
				</label>
				<label class="flex gap">
					<div class="flex-1">City </div>
					<!--{{locations_countries_keys}}-->
					<input type="text" v-model="city_search">
				</label>
			</div>
			<select v-model="country_filter" multiple size="10">
				<option value="">All</option>
				<option value="null">Unknown</option>
				<option v-for="(country,country_key) in available_country_filter_options" :value="country_key" :key="country_key+'yes'" v-html="(country==''||country==null)?country_key:country"></option>
				<option v-for="(country,country_key) in unavailable_country_filter_options" :value="country_key" style="color:red" :key="country_key+'no'" v-html="(country==''||country==null)?country_key:country"></option>
			</select>
			<pre>{{country_filter}}</pre>
		</div>
		<table>
			<thead>
				<tr>
					<th></th>
					<th>ID</th>
					<th><span class="dashicons dashicons-location"></span></th>
					<th>Done?</th>
					<th>post_title</th>
					<th>categories</th>
					<th>post_status</th>
					<th>is_pickup</th>
					<th>place_id</th>
					<th>country</th>
					<th v-for="(setting_value,settings_key) in location_settings_keys" class="setting-key" :class="settings_key">{{settings_key}}</th>
				</tr>
			</thead>
			<template is="tr" v-for="(location,location_key) in filtered_locations" :key="location_key">
				<!--------------------------------------------------------------------------------------->
				<tr v-if="edited_location!==location.ID">
					<td @click="edit_location(location)" class="sticky-left">
						<div class="flex">
							<div>{{location_key+1}}</div>
							<span class="dashicons dashicons-edit"></span>
						</div>
					</td>
					<td>{{location.ID}}</td>
					<td><button @click="display_popup(location.meta_input._location_settings.lat,location.meta_input._location_settings.lng,location.meta_input._location_settings.name)" v-if="location.meta_input._location_settings.lat && location.meta_input._location_settings.lng && (location.meta_input._location_settings.lat.trim() + location.meta_input._location_settings.lng.trim()).length "><span class="dashicons dashicons-location"></span></button></td>
					<td><input type="checkbox" v-model="location.meta_input._google_compare_done" disabled class="always-show"></td>
					<td>{{location.post_title}}</td>
					<td><div v-if="location.categories">{{location.categories.map(cat=>cat.name).join(', ')}}</div></td>
					<td>{{location.post_status}}</td>
					<td><input type="checkbox" v-model="location.meta_input._pickup" disabled class="always-show"></td>
					<td>{{location.meta_input.place_id}}</td>
					<td>
						{{location.meta_input._country}}
					</td>
					<td v-for="(setting_value,settings_key) in location_settings_keys" class="setting-key" :class="settings_key">{{location.meta_input._location_settings[settings_key]}}</td>
				</tr>
				<!--------------------------------------------------------------------------------------->
				<tr v-else>
					<td class="selected-location sticky-left">
						<div>{{location_key+1}}</div>
						<template v-if="!location.saving">
							<button><span class="dashicons dashicons-no"  @click="edited_location=null"></span></button>
							<button><span class="dashicons dashicons-yes" @click="save_location(location)"></span></button>
						</template>
						<div v-else>Saving</div>
					</td>
					<td>{{location.ID}}</td>
					<td><button @click="display_popup(location.meta_input._location_settings.lat,location.meta_input._location_settings.lng,location.meta_input._location_settings.name)" v-if="location.meta_input._location_settings.lat && location.meta_input._location_settings.lng && ((location.meta_input._location_settings.lat+'').trim() + (location.meta_input._location_settings.lng+'').trim()).length "><span class="dashicons dashicons-location"></span></button></td>
					<td><input type="checkbox" v-model="location.meta_input._google_compare_done"></td>
					<td><input type="text" v-model="location.post_title"></td>
					<td>
						<select multiple v-model="location.categories">
							<option v-for="(cat,cat_key) in locations_categories" :value="cat">{{cat.name}}</option>
						</select>
					</td>
					<td>{{location.post_status}}</td>
					<td><input type="checkbox" v-model="location.meta_input._pickup"></td>
					<td><input type="text" v-model="location.meta_input.place_id"></td>
					<td>
						<div class="flex">
							<!--input type="checkbox" v-model="location.edit" v-if="location.edit"-->
							<select v-model="location.meta_input._country">
								<option v-for="(country,country_key) in countries" :value="country_key">{{country}}</option>
							</select>
							<!--div v-else>{{location.meta_input._country}}</div-->
						</div>
					</td>
					<td v-for="(setting_value,settings_key) in location_settings_keys" class="setting-key" :class="settings_key"><input type="text" v-model="location.meta_input._location_settings[settings_key]"></td>
				</tr>
				<!--------------------------------------------------------------------------------------->
				<tr v-if="location.ID===edited_location && location.google_data" is="vue:resultRow" @display-popup="display_popup" :location_settings_keys="location_settings_keys" :location="location" :result="result" :result_key="result_key" v-for="(result,result_key) in location.google_data.results"></tr>
				<!--------------------------------------------------------------------------------------->
				<tr v-if="location.ID===edited_location">
					<td colspan="6" class="selected-location">
						<template v-if="location.search_google_api">
							<input type="text" v-model="location.search_google_api" style="width:100%;">
							<button @click="app_search(location.search_google_api,location,true)">Search</button>
						</template>
						<template v-else>
							Eroor please contact DEVs
							{{
								location.search_google_api=
								[
								location.meta_input._location_settings.name,
								location.meta_input._location_settings.city,
								location.meta_input._location_settings.state,
								location.meta_input._location_settings.route,
								location.meta_input._location_settings.street_number,
								location.meta_input._location_settings.zip_code,
								countries[location.meta_input._country],
								].join(' ')
							}}</template>
							<!--pre v-if="location.google_data">
								{{location.google_data}}
							</pre-->
						</td>
					</tr>
					<!--------------------------------------------------------------------------------------->
				</template>
			</table>
		</div>
		<div class="app_popup" ref="popup" v-show="show_popup">
			<div class="app_popup-menu"><button class="close-button" @click="show_popup=false">X</button></div>
			<div ref="map" class="map"></div>
		</div>
	</div>
