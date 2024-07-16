if (!defined('WPINC')) {
	die;
}
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'generate.php');
?>
<style type="text/css">
	<?php require_once(__DIR__ . DIRECTORY_SEPARATOR . 'style.css'); ?>
</style>
<div id="app" v-cloak>
	<template>
		<pre>{{[
			location_settings_meta,
			place_id,
			is_pickup,
			post_categories,
			categories,
		]}}</pre>
	</template>
	<div class="flex-column" style="min-width:300px;">
		<div>
			<h1>1) Select store Category</h1>
			<div class="flex">
				<div style="display:none;">
					<label for="location_settings_pickup">
						<div class="label"> Available for Pickup ?</div>
					</label>
					<input type="checkbox" id="location_settings_pickup" name="location_settings_is_pickup" v-model="is_pickup">
				</div>
				<div>
					<label class="flex align-center m-1">Categories</label>
					<template v-for="(term, c_key) in categories">
						<label><input type="checkbox" name="location_settings_categories[]" :value="term.term_id" v-model="post_categories">
							<div class="label">{{term.name}}</div>
						</label>
					</template>
				</div>
			</div>
			<hr>
		</div>
		<div>
			<h1>1.1) Select store type</h1>
			<div>
				<label for="store_type">Store Type:</label>
				<select id="store_type" name="location_settings[store_type]" v-model="location_settings_meta.store_type">
					<option value="N/A">N/A</option>
					<option value="A">A</option>
					<option value="B">B</option>
					<option value="C">C</option>
				</select>
			</div>
			<hr>
		</div>
		<div :class="{loading: loading}">
			<h1 class="loading-title">2) Search <span class="important">(Mandatory)</span>
				<span class="loader"></span>
			</h1>
			<p>* Selecting result will fill 3) Address, 4) Store details, 5) Google place ID & Geolocation. You can then verify the fields are correct.</p>
			<div class="flex">
				<label for="location_settings_search">
					<div class="label"> Search store</div>
					<div>
						<p class="important">Please type Name of the Store, street & country</p>
						<p>The store needs to be in Google Maps; if not register it <a href="https://support.google.com/business/answer/7107242" target="_blank">here</a></p>
					</div>
					<div class="w-100 flex-nowrap">
						<input type="text" v-model="location_settings_meta.search" id="location_settings_search" name="location_settings[search]" @keypress.enter.prevent="fetch" autocomplete="off">
						<button type="button" id="fetch_location_settings_name" @click.prevent="fetch" class="button button-primary button-large">Search</button>
					</div>
				</label>
				<span class="loader"></span>
				<div class="inline-block" v-if="results">
					<h1><span id="results_n_count">{{results.length}}</span> Results</h1>
					<div v-if="results.length">
						<p>Please select your address</p>
						<div class="flex">
							<div v-for="(result, result_key) in results" @click="process_result(result_key)" class="card" :class="{selected: result.place_id == place_id}">
								<div>
									{{result.name}}<br>
									{{result.formatted_address.replace(result.name + ',', '')}}<br>
								</div>
								<template>
									{{result_key}}
									<pre style="max-width:200px; max-height:8em; overflow:auto;">{{result}}</pre>
									<img :src="result.icon" alt="" style="float:left; max-width:120px; max-height:120px; height:auto;">
								</template>
							</div>
						</div>
					</div>
					<div v-else>
						Can't find your store? <a href="https://support.google.com/business/answer/7107242" target="_blank" class="important">Maybe you need to register it in Google.</a>
					</div>
				</div>
			</div>
		</div>
		<div>
			<hr>
		</div>
		<div class="flex align-start">
			<div v-show="location_settings_meta.searched && location_settings_meta.searched != ''">
				<h1>3) Address</h1>
				<table>
					<tr>
						<td>
							<label for="location_settings_name" class="flex align-center m-1">
								<div class="label"> Name <span class="important">(Title of the store - Don't leave it empty)</span></div>
							</label>
							<input type="text" v-model="location_settings_meta.name" id="location_settings_name" name="location_settings[name]" @keypress.enter.prevent="fetch">
							<div v-if="o_location_settings_meta.name && o_location_settings_meta.name != '' && o_location_settings_meta.name != location_settings_meta.name" class="replacer" @click="location_settings_meta.name = o_location_settings_meta.name">
								{{o_location_settings_meta.name}} <button @click.prevent="location_settings_meta.name = o_location_settings_meta.name">Restore</button>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for="location_settings_country" class="flex align-center m-1">
								<div class="label">
									<?= __('Country') ?>
								</div>
							</label>
							<div>
								<select name="location_settings[country]" id="location_settings_country" v-model="location_settings_meta.country">
									<option>Select</option>
									<template v-for="(label, country_code) in countries">
										<option :value="country_code">{{label}}</option>
									</template>
								</select>
								<div v-if="o_location_settings_meta.country != '' && o_location_settings_meta.country != location_settings_meta.country" class="replacer" @click="location_settings_meta.country = o_location_settings_meta.country">
									{{o_location_settings_meta.country}} <button @click.prevent="location_settings_meta.country = o_location_settings_meta.country">Restore</button>
								</div>
							</div>
						</td>
					</tr>
					<tr v-for="(row, row_key) in fields" :id="'location-inputs-row-' + row_key">
						<template v-for="(label, key) in row" :is="td">
							<td v-if="key == 'html'" colspan="2" v-html="label"></td>
							<td v-else>
								<label :for="'location_settings_' + key" class="flex align-center m-1">
									<div class="label">{{label}}</div>
								</label>
								<input type="text" v-model="location_settings_meta[key]" :id="'location_settings_' + key" :name="'location_settings[' + key + ']'">
								<div v-if="o_location_settings_meta[key] != '' && o_location_settings_meta[key] != location_settings_meta[key]" class="replacer" @click="location_settings_meta[key] = o_location_settings_meta[key]">
									{{o_location_settings_meta[key]}} <button @click.prevent="location_settings_meta[key] = o_location_settings_meta[key]">Restore</button>
								</div>
							</td>
						</template>
					</tr>
					<tr>
						<td>
							<label for="location_settings_place_id" class="flex align-center m-1">
								<div class="label"> Google Place ID</div>
							</label>
							<input type="text" v-model="place_id" id="location_settings_place_id" name="location_settings_place_id">
							<div v-if="o_place_id != '' && o_place_id != place_id" class="replacer" @click="place_id = o_place_id">
								{{o_place_id}} <button @click.prevent="place_id = o_place_id">Restore</button>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="map-wrapper">
				<div id="map"></div>
				<button @click.prevent="initMap" class="button button-primary button-large flex align-center" name="refresher" v-if="location_settings_meta.lat.trim && location_settings_meta.lng.trim && location_settings_meta.lat.trim() != '' && location_settings_meta.lng.trim() != ''">Refresh map <svg aria-hidden="true" style="width:25px; height:25px" focusable="false" data-icon="sync-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
						<path fill="currentColor" d="M370.72 133.28C339.458 104.008 298.888 87.962 255.848 88c-77.458.068-144.328 53.178-162.791 126.85-1.344 5.363-6.122 9.15-11.651 9.15H24.103c-7.498 0-13.194-6.807-11.807-14.176C33.933 94.924 134.813 8 256 8c66.448 0 126.791 26.136 171.315 68.685L463.03 40.97C478.149 25.851 504 36.559 504 57.941V192c0 13.255-10.745 24-24 24H345.941c-21.382 0-32.09-25.851-16.971-40.971l41.75-41.749zM32 296h134.059c21.382 0 32.09 25.851 16.971 40.971l-41.75 41.75c31.262 29.273 71.835 45.319 114.876 45.28 77.418-.07 144.315-53.144 162.787-126.849 1.344-5.363 6.122-9.15 11.651-9.15h57.304c7.498 0 13.194 6.807 11.807 14.176C478.067 417.076 377.187 504 256 504c-66.448 0-126.791-26.136-171.315-68.685L48.97 471.03C33.851 486.149 8 475.441 8 454.059V320c0-13.255 10.745-24 24-24z" class=""></path>
					</svg></button>
			</div>
		</div>
		<div v-if="location_settings_meta.searched && location_settings_meta.searched != ''">
			<hr>
			<h1>6) Save</h1>
			<div id="app-post-actions">
				<input type="submit" name="save" id="app-publish" class="button button-primary button-large" value="Update" @click.prevent="submit">
			</div>
		</div>
	</div>
	<?php include('script.php'); ?>
</div>
