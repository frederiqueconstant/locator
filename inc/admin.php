<?php
if (!defined('WPINC')) {
	die;
}
if (!class_exists('WPLocator_admin')) {
	class WPLocator_admin
	{
		private $settings = null;
		private $parent = null;
		function __construct($parent = null)
		{
			$this->parent = $parent;
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'wp-locator-settings.php';
			$this->settings = new WPLocator_settings($this);
		}
		function init()
		{
			add_action('admin_menu', [$this, 'admin_menu']);
			add_action('add_meta_boxes', [$this, 'location_settings_meta_box'], 10, 2);
			add_action('save_post', [$this, 'save_location_settings_meta_box_data'], 10, 2);

			add_action('wp_ajax_WPLocator_google_api_get', [$this, 'google_api_get']);
			add_action('wp_ajax_WPLocator_save_location', [$this, 'ajax_save_location']);
			add_filter('manage_location_posts_columns', [$this, 'set_custom_edit_location_columns']);
			add_action('manage_location_posts_custom_column', [$this, 'custom_location_column'], 10, 2);

			add_action('add_meta_boxes', [$this, 'remove_metaboxes'], PHP_INT_MAX);

			add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
			add_action('admin_init', [$this, 'extend_admin_search']);
			//add_filter("pre_get_posts", [$this, "custom_search_query"]);
		}
		function extend_admin_search()
		{
			global $typenow;
			if ($typenow === 'location') {
				add_filter('posts_search', [$this, 'posts_search_custom_post_type'], 10, 2);
			}
		}
		function posts_search_custom_post_type($search, $query)
		{
			global $wpdb;
			if ($query->is_main_query() && !empty($query->query['s'])) {
				$sql    = "
				or exists (
					select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
					and meta_key in ('_location_settings')
					and meta_value like %s
				)
			";
				$like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
				$search = preg_replace(
					"#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
					$wpdb->prepare($sql, $like),
					$search
				);
			}

			return $search;
		}
		function custom_search_query($query)
		{
			global $pagenow;
			if (empty($pagenow) || 'edit.php' != $pagenow || !$query->is_admin || !$query->is_search)
				return $query;
			$searchterm = $query->query_vars['s'];
			if (trim($searchterm) != "") {
				$searchtermq = $GLOBALS['wpdb']->esc_like($searchterm);
				$meta_query = ['relation' => 'OR'];
				array_push($meta_query, [
					'value' => $searchtermq,
					'key' => '_location_settings',
					'compare' => 'LIKE'
				]);
				/* $searchtermq = '%' . $GLOBALS['wpdb']->esc_like(json_encode($searchterm)) . '%';
				array_push($meta_query, [
					'meta_value' => $searchtermq,
					'meta_compare' => 'LIKE'
				]); */
				$query->set("meta_query", $meta_query);
			};
		}

		function enqueue_scripts()
		{
			$screen = get_current_screen();
			if (isset($screen->id) && $screen->id == 'location') {
				wp_register_script(
					'vuejs',
					//$url.'lib/vue.min.js',
					'https://unpkg.com/vue@3',
					['jquery'],
					//filemtime($dir.'lib/vue.min.js'),
					'3',
					false
				);
				wp_enqueue_script('vuejs');
				wp_localize_script('vuejs', 'wp_locator', ['vuejs_ajax_url' => admin_url('admin-ajax.php')]);
				wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $this->settings->get('google_maps_api_key_front') . '&language=' . pll_current_language(), ['jquery', 'vuejs'], false, false);
				wp_enqueue_script('google-maps');
			}
			//var_dump($screen);exit;
		}
		public function remove_metaboxes()
		{
			if (current_user_can('shop_manager')) {
				remove_meta_box('authordiv', 'post', 'normal'); // Author Metabox
				remove_meta_box('commentstatusdiv', 'post', 'normal'); // Comments Status Metabox
				remove_meta_box('commentsdiv', 'post', 'normal'); // Comments Metabox
				remove_meta_box('postcustom', 'post', 'normal'); // Custom Fields Metabox
				remove_meta_box('postexcerpt', 'post', 'normal'); // Excerpt Metabox
				remove_meta_box('revisionsdiv', 'post', 'normal'); // Revisions Metabox
				remove_meta_box('slugdiv', 'post', 'normal'); // Slug Metabox
				remove_meta_box('trackbacksdiv', 'post', 'normal'); // Trackback Metabox
				remove_meta_box('wpseo_meta', LOCATION_PT, apply_filters('wpseo_metabox_prio', 'high'));
				$post_types = WPSEO_Post_Type::get_accessible_post_types();
				foreach ($post_types as $post_type) {
					remove_meta_box('wpseo_meta', $post_type, apply_filters('wpseo_metabox_prio', 'high'));
				}
				remove_meta_box('authordiv', LOCATION_PT, 'normal');
			}
		}

		public function ajax_save_location()
		{
			$user = wp_get_current_user();
			//exit(json_encode(array_intersect( (array) $user->roles,['administrator','shop_manager'] )));
			if (count(array_intersect((array) $user->roles, ['administrator', 'shop_manager'])) < 1) { //if(!property_exists($user, 'roles') || !in_array('locations_editor',$user->roles)){
				header("HTTP/1.1 401 Unauthorized");
				exit;
			}
			if (!empty($_POST['location'])) {
				$location = $_POST['location'];
				$post_location = [
					'ID' => $location['ID'],
					'post_title' => $location['post_title'],
					'post_status' => $location['post_status'],
					'meta_input' => $location['meta_input'],
				];
				$post_id = wp_update_post($post_location, true, false);
				if (is_wp_error($post_id)) {
					exit(json_encode(['wp_error' => $post_id, 'messages' => $post_id->get_error_messages()]));
				}
				$result = wp_set_post_terms($post_id, array_unique(array_map('intval', wp_list_pluck($location['categories'], 'term_id'))), LOCATION_PT . '_category', false); //: array|false|WP_Error
			}
			exit(json_encode($_POST));
		}
		function set_custom_edit_location_columns($columns)
		{
			$columns['place_id'] = __('place_id', 'wp-locator');
			$columns['location_categories'] = __('Categories', 'wp-locator');
			$columns['country'] = __('Country', 'wp-locator');
			$columns['pickup'] = __('Pickup', 'wp-locator');
			if (!current_user_can('shop_manager')) {
				$columns['imported'] = __('imported', 'wp-locator');
				$columns['data'] = __('Data', 'wp-locator');
			}
			return $columns;
		}
		// Add the data to the custom columns for the location post type:
		protected function get_edit_link($args, $link_text, $css_class = '')
		{
			$url = add_query_arg($args, 'edit.php');

			$class_html   = '';
			$aria_current = '';

			if (!empty($css_class)) {
				$class_html = sprintf(
					' class="%s"',
					esc_attr($css_class)
				);

				if ('current' === $css_class) {
					$aria_current = ' aria-current="page"';
				}
			}

			return sprintf(
				'<a href="%s"%s%s>%s</a>',
				esc_url($url),
				$class_html,
				$aria_current,
				$link_text
			);
		}
		function custom_location_column($column, $post_id)
		{
			switch ($column) {
				case 'location_categories':
					$terms           = get_the_terms($post_id, LOCATION_PT . '_category');
					//var_dump($terms);
					$taxonomy_object = get_taxonomy(LOCATION_PT . '_category');
					if (is_array($terms)) {
						$term_links = array();
						foreach ($terms as $t) {
							$posts_in_term_qv = array();
							$posts_in_term_qv['post_type'] = LOCATION_PT;
							if ($taxonomy_object->query_var) {
								$posts_in_term_qv[$taxonomy_object->query_var] = $t->slug;
							} else {
								$posts_in_term_qv['taxonomy'] = LOCATION_PT . '_category';
								$posts_in_term_qv['term']     = $t->slug;
							}
							$label = esc_html(sanitize_term_field('name', $t->name, $t->term_id, LOCATION_PT . '_category', 'display'));
							$term_links[] = $this->get_edit_link($posts_in_term_qv, $label);
						}

						/**
						 * Filters the links in `$taxonomy` column of edit.php.
						 *
						 * @since 5.2.0
						 *
						 * @param string[]  $term_links Array of term editing links.
						 * @param string    $taxonomy   Taxonomy name.
						 * @param WP_Term[] $terms      Array of term objects appearing in the post row.
						 */
						$term_links = apply_filters('post_column_taxonomy_links', $term_links, LOCATION_PT . '_category', $terms);

						echo implode(wp_get_list_item_separator(), $term_links);
					} else {
						echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . 'No terms' . '</span>';
					}
					break;
				case 'place_id':
					echo get_post_meta($post_id, 'place_id', true);
					break;
				case 'imported':
					echo get_post_meta($post_id, 'imported', true) ? 'Y' : 'N';
					break;
				case 'country':
					$country = get_post_meta($post_id, '_country', true);
					if (empty($country) || strlen($country) != 2) {
						$data = (array) get_post_meta($post_id, '_location_settings', true);
						if (array_key_exists('country', $data) && !empty($data['country'])) {
							update_post_meta($post_id, '_country', (string) $data['country']);
							$country = (string) $data['country'];
						}
					}
					echo $country;
					break;
				case 'pickup':
					$pickup = get_post_meta($post_id, '_pickup', true);
					if (empty($pickup)) {
						echo 'No';
					} else {
						echo 'Yes';
					}
					break;
				case 'data':
					echo '<pre>' . preg_replace('/{\s*|(\n)\s*|\s*}/', '$1', json_encode((array) get_post_meta($post_id, '_location_settings', true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre>';
					break;
			}
		}
		public function google_api_get($return = false)
		{
			//https://developers.google.com/maps/documentation/geocoding/start?hl=en
			if ($return) {
				$address = $return;
			} else {
				if (!array_key_exists('address', $_POST)) {
					echo (json_encode([$_POST, $_REQUEST]));
					exit;
				}
				$address = $_POST['address'];
			}

			//global $wpdb;
			if (!empty($address)) {
				$result = [];
				$can_edit_post_country = get_user_meta(get_current_user_id(), 'can_edit_locations_countries', true);
				//exit(json_encode($can_edit_post_country));
				//$retrieveURL = 'https://www.google.com/m8/feeds/contacts/default/full?access_token=' . urlencode($tokens->access_token) . '&v=3.0';
				//$retrieveURL = "https://maps.googleapis.com/maps/api/geocode/json?key=".$this->settings->get('google_maps_api_key')."&fields=international_phone_number&address=".rawurlencode(str_replace([' '], ['+'], (string)$address));
				$retrieveURL = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=" . rawurlencode(str_replace([' '], ['+'], (string) $address)) . "&key=" . $this->settings->get('google_maps_api_key'); //.'&fields=address_components';
				//echo $retrieveURL;
				$cURL = curl_init();
				curl_setopt($cURL, CURLOPT_URL, $retrieveURL);
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($cURL, CURLINFO_HEADER_OUT, 1);
				curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
				$response = json_decode(curl_exec($cURL));
				if ($errno = curl_errno($cURL)) {
					$error_message = curl_strerror($errno);
					echo ("cURL error ({$errno}):\n {$error_message}:\n ");
					var_dump(curl_getinfo($cURL, CURLINFO_HEADER_OUT));
				}
				//$response->test=gettype($response);
				if (is_object($response) && property_exists($response, 'results') && is_array($response->results)) {
					foreach ($response->results as $key => $result) {

						//$result->test=gettype($result);continue;
						if (1) {
							$place_id = $result->place_id;
							$retrieveURL = 'https://maps.googleapis.com/maps/api/place/details/json?key=' . $this->settings->get('google_maps_api_key') . '&place_id=' . $place_id; //.'&fields=formatted_phone_number,international_phone_number,opening_hours,website,price_level,rating,name';
							$cURL = curl_init();
							curl_setopt($cURL, CURLOPT_URL, $retrieveURL);
							curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($cURL, CURLINFO_HEADER_OUT, 1);
							curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
							//$response->results[ $key ]->details = json_decode( curl_exec( $cURL ) );
							$response->results[$key] = (object) array_merge((array) $response->results[$key], (array) json_decode(curl_exec($cURL))->result);
							if ($errno = curl_errno($cURL)) {
								$error_message = curl_strerror($errno);
								//("cURL error ({$errno}):\n {$error_message}:\n ");
								//var_dump(curl_getinfo($cURL, CURLINFO_HEADER_OUT));
								if (!property_exists($response->results[$key], 'errors')) {
									$response->results[$key]->errors = [];
								}
								if (!property_exists($response->results[$key], 'errors_infos')) {
									$response->results[$key]->errors_infos = [];
								}
								$response->results[$key]->errors[] = "cURL error ({$errno}):\n {$error_message}:\n";
								$response->results[$key]->errors_infos[] = json_encode(curl_getinfo($cURL, CURLINFO_HEADER_OUT));
							}
						}
						if (!empty($can_edit_post_country) && is_array($can_edit_post_country)) {
							if (!isset($response->results[$key]->address_components)) {
								unset($response->results[$key]);
								continue;
							}
							foreach ((array) $response->results[$key]->address_components as $address_component_key => $address_component) {
								if (in_array('country', $address_component->types) && !in_array($address_component->short_name, $can_edit_post_country)) {
									unset($response->results[$key]);
									continue 2;
								}
							}
						}
					}
				} else {
					//echo 'blaaaa';
				}
				if ($return)
					return $response;
				echo (json_encode($response));
				//return $response;
			}
			exit(); // this is required to terminate immediately and return a proper response
		}
		function location_settings_meta_box_callback($post)
		{
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'metaboxes' . DIRECTORY_SEPARATOR . 'location_settings_meta_box_callback' . DIRECTORY_SEPARATOR . 'index.php';
		}


		public function admin_menu()
		{
			add_menu_page(
				'WP Locator',
				'WP Locator',
				'manage_options',
				'wp-locator',
				[$this, 'WPLocator_options'],
				'data:image/svg+xml;base64,' . base64_encode('<svg aria-hidden="true" focusable="false" data-prefix="fal" data-icon="map-signs" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-map-signs fa-w-16 fa-7x"><path fill="currentColor" d="M441.37 192c8.49 0 16.62-4.21 22.63-11.72l43.31-54.14c6.25-7.81 6.25-20.47 0-28.29L464 43.71C458 36.21 449.86 32 441.37 32H272V8c0-4.42-3.58-8-8-8h-16c-4.42 0-8 3.58-8 8v24H56c-13.25 0-24 13.43-24 30v100c0 16.57 10.75 30 24 30h184v32H70.63C62.14 224 54 228.21 48 235.71L4.69 289.86c-6.25 7.81-6.25 20.47 0 28.29L48 372.28c6 7.5 14.14 11.72 22.63 11.72H240v120c0 4.42 3.58 8 8 8h16c4.42 0 8-3.58 8-8V384h184c13.25 0 24-13.43 24-30V254c0-16.57-10.75-30-24-30H272v-32h169.37zm6.38 160h-375l-38.4-48 38.45-48h375.19l-.24 96zM64.25 64h375l38.4 48-38.45 48H64.01l.24-96z" class=""></path></svg>')
				//<svg aria-hidden="true" focusable="false" data-prefix="fal" data-icon="dharmachakra" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-dharmachakra fa-w-16 fa-5x"><path fill="currentColor" d="M503.67 232.35l-24.81 1.03c-4.5-44.89-22.28-85.84-49.31-118.94l18.31-16.84c3.35-3.08 3.46-8.33.24-11.54l-22.15-22.15c-3.21-3.21-8.46-3.11-11.54.24l-16.84 18.31c-33.1-27.03-74.05-44.81-118.94-49.31l1.03-24.81a7.997 7.997 0 0 0-7.99-8.33h-31.32c-4.55 0-8.18 3.79-7.99 8.33l1.03 24.81c-44.88 4.5-85.84 22.28-118.94 49.31L97.6 64.15c-3.08-3.35-8.33-3.46-11.54-.24L63.91 86.05a7.99 7.99 0 0 0 .24 11.54l18.31 16.84c-27.04 33.1-44.81 74.05-49.31 118.94l-24.81-1.03c-4.55-.18-8.34 3.45-8.34 8v31.32c0 4.55 3.79 8.18 8.33 7.99l24.81-1.03c4.5 44.89 22.28 85.84 49.31 118.94l-18.3 16.84c-3.35 3.08-3.46 8.33-.24 11.54l22.15 22.15c3.21 3.22 8.46 3.11 11.54-.24l16.84-18.31c33.1 27.04 74.05 44.81 118.94 49.31l-1.03 24.81a7.997 7.997 0 0 0 7.99 8.33h31.32c4.55 0 8.18-3.79 7.99-8.33l-1.03-24.81c44.89-4.5 85.84-22.28 118.94-49.31l16.84 18.31c3.08 3.35 8.33 3.46 11.54.24l22.15-22.15a7.99 7.99 0 0 0-.24-11.54l-18.31-16.84c27.04-33.1 44.81-74.05 49.31-118.94l24.81 1.03c4.55.19 8.33-3.44 8.33-7.99v-31.32c.01-4.55-3.78-8.18-8.32-7.99zm-56.92 2.37l-96.49 4.02c-2.48-13.64-7.74-26.27-15.34-37.24l70.91-65.24c22.13 27.64 36.82 61.45 40.92 98.46zM256 320c-35.29 0-64-28.71-64-64s28.71-64 64-64 64 28.71 64 64-28.71 64-64 64zm119.74-213.83l-65.24 70.91c-10.97-7.6-23.6-12.86-37.24-15.34l4.02-96.49c37.01 4.1 70.82 18.79 98.46 40.92zM234.72 65.25l4.02 96.49c-13.64 2.48-26.27 7.74-37.24 15.34l-65.24-70.91c27.64-22.13 61.45-36.82 98.46-40.92zm-128.55 71.01l70.91 65.24c-7.6 10.97-12.86 23.6-15.34 37.24l-96.49-4.02c4.1-37.01 18.79-70.82 40.92-98.46zM65.25 277.28l96.49-4.02c2.48 13.64 7.74 26.27 15.34 37.24l-70.91 65.24c-22.13-27.64-36.82-61.45-40.92-98.46zm71.01 128.55l65.24-70.91c10.97 7.6 23.6 12.86 37.24 15.34l-4.02 96.49c-37.01-4.1-70.82-18.79-98.46-40.92zm141.02 40.92l-4.02-96.49c13.64-2.48 26.27-7.74 37.24-15.34l65.24 70.91c-27.64 22.13-61.45 36.82-98.46 40.92zm128.55-71.01l-70.91-65.24c7.6-10.97 12.86-23.6 15.34-37.24l96.49 4.02c-4.1 37.01-18.79 70.82-40.92 98.46z" class=""></path></svg>
				,
				100
			);
			if (WPLocator::import_allowed())
				add_submenu_page("edit.php?post_type=" . LOCATION_PT, 'Import', 'Import', 'manage_options', 'import', [$this, 'WPLocator_import'], 100);
			add_submenu_page("edit.php?post_type=" . LOCATION_PT, 'Google compare', 'Google compare', 'manage_woocommerce', 'google_compare', [$this, 'WPLocator_google_compare'], 100);
			//add_submenu_page( "edit.php?post_type=".LOCATION_PT, 'Google compare', 'Google compare', 'shop_manager', 'google_compare', [$this,'WPLocator_google_compare'], 100 );
		}

		public function WPLocator_google_compare()
		{
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'google-compare.php';
		}

		public function WPLocator_import()
		{
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'import.php';
		}
		public function WPLocator_options()
		{
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'options.php';
		}
		function location_settings_meta_box($post_type, $post)
		{
			add_meta_box(
				'location_settings',
				__('Location Settings', 'wp-locator'),
				[$this, 'location_settings_meta_box_callback'],
				LOCATION_PT,
				'advanced',
				'high',
				null
			);
			if ($post && is_a($post, 'WP_Post') && $post_type == 'page') {
				$pageTemplate = get_post_meta($post->ID, '_wp_page_template', true);
				if ($pageTemplate == 'templates/locations.php') {
					//exit($pageTemplate);
					if (1)
						add_meta_box(
							'location_settings_category',
							__('Categories to show', 'wp-locator'),
							[$this, 'location_settings_category_meta_box_callback'],
							'page',
							'normal',
							'high',
							null
						);
				} else {
				}
			} else {
				//var_dump($post);
			}
		}

		public function location_settings_category_meta_box_callback($post)
		{

			wp_nonce_field('location_settings_nonce', 'location_settings_nonce');
			$terms = get_terms(array(
				'taxonomy' => LOCATION_PT . '_category',
				'hide_empty' => false,
			));
			$default_location_category = get_post_meta($post->ID, 'default_location_category', 1);
?>
			<div style="margin-bottom:1em">
				<h2>Default</h2>
				<select name="default_location_category">
					<option></option>
					<?php foreach ($terms as $key => $term) {
						//print_r([$key => $term]);
					?>
						<option<?= $term->name == $default_location_category ? ' selected="selected"' : '' ?>><?= $term->name ?></option>
						<?php
					}
						?>
				</select>
			</div>
			<div style="">
				<?php
				$hidden_location_categories = (array)get_post_meta($post->ID, 'hidden_location_categories', 1);
				if (empty($hidden_location_categories)) $hidden_location_categories = [];
				?>
				<h2>Hidden</h2>
				<?php foreach ($terms as $key => $term) {

				?>
					<label style="margin-right:3em;white-space:nowrap;">
						<input type="checkbox" value="<?= $term->name ?>" name="hidden_location_categories[]" <?= in_array($term->name, $hidden_location_categories) ? ' checked="checked"' : '' ?>><?= $term->name ?>
					</label>
				<?php
				}
				?>
				</select>
			</div>
<?php
		}


		function save_location_settings_meta_box_data($post_id, $post)
		{
			if (
				(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
				!isset($_POST['location_settings_nonce']) ||
				!wp_verify_nonce($_POST['location_settings_nonce'], 'location_settings_nonce')
			) {
				return;
			}
			if (current_user_can('edit_post', $post_id)) {

				$cats = get_terms(array(
					'taxonomy' => LOCATION_PT . '_category',
					'hide_empty' => false,
					'fields' => 'id=>name'
				));
				if (isset($_POST['default_location_category'])) {
					update_post_meta($post_id, 'default_location_category', $_POST['default_location_category']);
				}
				if (isset($_POST['hidden_location_categories'])) {
					update_post_meta($post_id, 'hidden_location_categories', array_filter((array)$_POST['hidden_location_categories'], function ($cat) use ($cats) {
						return in_array($cat, $cats);
					}));
				}
			}
			if (isset($_POST['post_type']) && LOCATION_PT == $_POST['post_type'] && isset($_POST['location_settings'])) {
				if (!current_user_can('edit_location', $post_id)) {
					return;
				} elseif (!is_array($_POST['location_settings'])) {
					return;
				}

				/*
																																																																									   wp_update_post([
																																																																									   'ID' => $post_id,
																																																																									   'post_content' => json_encode($_POST,JSON_PRETTY_PRINT),
																																																																									   ]);
																																																																									   */


				remove_action('save_post', [$this, 'save_location_settings_meta_box_data'], 10, 2);
				$args = [
					'ID' => $post_id,
					'post_name' => sanitize_title($post->post_title),
				];
				if (!current_user_can('administrator')) {
					$args['post_author'] = get_current_user_id();
				}
				wp_update_post($args);
				add_action('save_post', [$this, 'save_location_settings_meta_box_data'], 10, 2);

				foreach ($_POST['location_settings'] as $key => $value) {
					$_POST['location_settings'][$key] = sanitize_text_field($_POST['location_settings'][$key]);
				}
				update_post_meta($post_id, '_pickup', isset($_POST['location_settings_is_pickup']));
				update_post_meta($post_id, 'place_id', sanitize_text_field($_POST['location_settings_place_id']));
				$countries_obj = new WC_Countries();
				$wc_countries = $countries_obj->__get('countries');
				if (array_key_exists('country', $_POST['location_settings']) && array_key_exists($_POST['location_settings']['country'], $wc_countries)) {
					$_POST['location_settings']['country_name'] = $wc_countries[$_POST['location_settings']['country']];
					update_post_meta($post_id, '_country', $_POST['location_settings']['country']);
				} else {
					update_post_meta($post_id, '_country', '');
					$_POST['location_settings']['country'] = '';
				}
				if (isset($_POST['location_settings_categories'])) {
					wp_set_object_terms($post_id, array_unique(array_map('intval', $_POST['location_settings_categories'])), LOCATION_PT . '_category', false);
				}
				update_post_meta($post_id, '_location_settings', $_POST['location_settings']);
			}
		}
	} //class end
}
