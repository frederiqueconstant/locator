<?php if(!defined('WPINC')){die;} ?>
<template id="result-row">
	<tr class="google_data-result">
		<td class="selected-location">{{match}}%</td>
		<td></td>
		<td><button @click="$emit('display-popup',lat,lng,name)" v-if="lat && lng && ((lat+'').trim() + (lng+'').trim()).length "><span class="dashicons dashicons-location"></span></button></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td>
			<div class="flex">
				{{place_id}}
				<button @click="location.meta_input.place_id=place_id" v-if="place_id!=location.meta_input.place_id">
					<span class="dashicons dashicons-yes-alt"></span>
				</button>
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{country_code}} 
				<button @click="location.meta_input._country=country_code" v-if="country_code!=location.meta_input._country">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td v-for="(setting_value,settings_key) in location_settings_keys" class="setting-key" :class="settings_key">
			<div class="flex" v-if="settings_key in this.$options.computed">
				{{this[settings_key]}} 
				<button @click="location.meta_input._location_settings[settings_key]=this[settings_key]" v-if="this[settings_key]!=location.meta_input._location_settings[settings_key]">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<!--td>
			<div class="flex">
				{{name}} 
				<button @click="location.meta_input._location_settings.name=name" v-if="name!=location.meta_input._location_settings.name">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{city}} 
				<button @click="location.meta_input._location_settings.city=city" v-if="city!=location.meta_input._location_settings.city">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{state}} 
				<button @click="location.meta_input._location_settings.state=state" v-if="state!=location.meta_input._location_settings.state">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{lat}} 
				<button @click="location.meta_input._location_settings.lat=lat" v-if="lat!=location.meta_input._location_settings.lat">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{lng}} 
				<button @click="location.meta_input._location_settings.lng=lng" v-if="lng!=location.meta_input._location_settings.lng">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{country_name}} 
				<button @click="location.meta_input._location_settings.country_name=country_name" v-if="country_name!=location.meta_input._location_settings.country_name">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{route}} 
				<button @click="location.meta_input._location_settings.route=route" v-if="route!=location.meta_input._location_settings.route">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{street_number}} 
				<button @click="location.meta_input._location_settings.street_number=street_number" v-if="street_number!=location.meta_input._location_settings.street_number">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{zip_code}} 
				<button @click="location.meta_input._location_settings.zip_code=zip_code" v-if="zip_code!=location.meta_input._location_settings.zip_code">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{website}} 
				<button @click="location.meta_input._location_settings.website=website" v-if="website!=location.meta_input._location_settings.website">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td>
		<td>
			<div class="flex">
				{{phone}} 
				<button @click="location.meta_input._location_settings.phone=phone" v-if="phone!=location.meta_input._location_settings.phone">
					<span class="dashicons dashicons-yes-alt"></span>
				</button> 
				<div v-else>
					<span class="dashicons dashicons-yes" style="color:green"></span>
				</div>
			</div>
		</td-->
	</tr>
</template>
<script type="text/javascript">
	const resultRow={
		template: '#result-row',
		props:{
			result:Object,
			location:Object,
			result_key:Number,
			location_settings_keys:Object,
		},//props
		data:()=>({
			//info:false
		}),
		created(){
			//console.warn('created',this,'email' in this)
		},
		computed:{
			match(){
				let match=0,location =this.location;
				if('meta_input' in location){
					if('place_id' in location.meta_input &&  this.place_id==location.meta_input.place_id){
						match++
					}
					if('country_code' in location.meta_input &&  this.country_code==location.meta_input.country_code){
						match++
					}
					if('_location_settings' in location.meta_input){
						for (const settings_key in this.location_settings_keys){
							if(settings_key in this.$options.computed && settings_key in location.meta_input._location_settings){
								match+=this.similarity(this[settings_key],location.meta_input._location_settings[settings_key])
							}
						}
					}
				}
				return Math.round((match / (2 + Object.keys(this.location_settings_keys).length))*100)
			},
			place_id(){
				return this.get_json_prop(this.result,'place_id')
			},
			country_code(){
				return this.get_address_component(this.result,['country'],'short_name')
			},
			name(){
				return this.get_json_prop(this.result,'details.result.name')
			},
			city(){
				return this.get_address_component(this.result,['locality'],'short_name')
			},
			state(){
				return this.get_address_component(this.result,['administrative_area_level_2'],'short_name')
			},
			lat(){
				return this.get_json_prop(this.result,"geometry.location.lat")
			},
			lng(){
				return this.get_json_prop(this.result,"geometry.location.lng")
			},
			country_name(){
				return this.get_address_component(this.result,['country'],'long_name')
			},
			route(){
				return this.get_address_component(this.result,['route'],'short_name')
			},
			street_number(){
				return this.get_address_component(this.result,['street_number'],'short_name')
			},
			zip_code(){
				return this.get_address_component(this.result,['postal_code'],'long_name')
			},
			website(){
				return this.get_json_prop(this.result,"details.result.website")
			},
			phone(){
				return this.get_json_prop(this.result,"details.result.international_phone_number")
			},
		},//computed
		methods:{
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
					return true
				}else{
					return false
				}
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
				//console.warn('props',props,prop_string)
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
				if(typeof res==='string' || typeof res==='number'){
					return res
				}else{
					console.log(res,typeof res)
					return null
				}
			},
			similarity(s1, s2) {
				if(s1==null || s2==null)return 0;
				s1 = s1.toString();
				s2 = s2.toString();
				var longer = s1;
				var shorter = s2;
				if (s1.length < s2.length) {
					longer = s2;
					shorter = s1;
				}
				var longerLength = longer.length;
				if (longerLength == 0) {
					return 1.0;
				}
				return (longerLength - this.editDistance(longer, shorter)) / parseFloat(longerLength);
			},
			editDistance(s1, s2) {
				s1 = s1.toLowerCase();
				s2 = s2.toLowerCase();

				var costs = new Array();
				for (var i = 0; i <= s1.length; i++) {
					var lastValue = i;
					for (var j = 0; j <= s2.length; j++) {
						if (i == 0)
							costs[j] = j;
						else {
							if (j > 0) {
								var newValue = costs[j - 1];
								if (s1.charAt(i - 1) != s2.charAt(j - 1))
									newValue = Math.min(Math.min(newValue, lastValue),
										costs[j]) + 1;
								costs[j - 1] = lastValue;
								lastValue = newValue;
							}
						}
					}
					if (i > 0)
						costs[s2.length] = lastValue;
				}
				return costs[s2.length];
			},


		},//methods
	}
</script>
