<?php
/*
Plugin Name:       WP Locator
Text Domain: wp-locator
Domain Path: /languages
*/
if (! defined('WPINC')) {
    die;
}
define('LOCATION_PT', 'location');
if (! class_exists('WPLocator')) {
    class WPLocator
    {
        protected $templates;
        private static $instance;
        public static function get_instance()
        {
            if (null == self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        private function __construct()
        {
            add_filter('pre_get_posts', [ $this, 'exclude_posts' ], PHP_INT_MAX, 1);
            $this->templates = array();
            // Add a filter to the attributes metabox to inject template into the cache.
            if (version_compare(floatval(get_bloginfo('version')), '4.7', '<')) {
                // 4.6 and older
                add_filter(
                    'page_attributes_dropdown_pages_args',
                    array( $this, 'register_project_templates' )
                );
            } else {
                // Add a filter to the wp 4.7 version attributes metabox
                add_filter(
                    'theme_page_templates',
                    array( $this, 'add_new_template' )
                );
            }
            // Add a filter to the save post to inject out template into the page cache
            add_filter('wp_insert_post_data', array( $this, 'register_project_templates' ));
            // Add a filter to the template include to determine if the page has our// template assigned and return it's path
            add_filter('template_include', array( $this, 'view_project_template' ));
            // Add your templates to this array.
            $this->templates = array( 'templates/locations.php' => "Locations", );
            $this->init();
        }

        public function add_new_template($posts_templates)
        {
            $posts_templates = array_merge($posts_templates, $this->templates);
            return $posts_templates;
        }
        public function register_project_templates($atts)
        {
            // Create the key used for the themes cache
            $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());
            // Retrieve the cache list.
            // If it doesn't exist, or it's empty prepare an array
            $templates = wp_get_theme()->get_page_templates();
            if (empty($templates)) {
                $templates = array();
            }
            // New cache, therefore remove the old one
            wp_cache_delete($cache_key, 'themes');
            // Now add our template to the list of templates by merging our templates
            // with the existing templates array from the cache.
            $templates = array_merge($templates, $this->templates);
            // Add the modified cache to allow WordPress to pick it up for listing
            // available templates
            wp_cache_add($cache_key, $templates, 'themes', 1800);
            return $atts;
        }
        public function view_project_template($template)
        {

            // Get global post
            global $post;

            // Return template if post is empty
            if (! $post) {
                return $template;
            }

            // Return default template if we don't have a custom one defined
            if (! isset($this->templates[ get_post_meta($post->ID, '_wp_page_template', true) ])) {
                return $template;
            }

            $file = $this->WP_LOCATOR_DIRECTORY() . get_post_meta($post->ID, '_wp_page_template', true);

            // Just to be safe, we check if the file exist first
            if (file_exists($file)) {
                return $file;
            } else {
                echo '<div>wp-locator.php:' . $file . '<div>';
            }

            // Return template
            return $template;

        }

        public static function WP_LOCATOR_URL()
        {
            return plugin_dir_url(__FILE__);
        }
        public static function WP_LOCATOR_DIRECTORY()
        {
            return plugin_dir_path(__FILE__);
        }
        public static function get_country()
        {
            global $wp_locator_get_country;
            if (!empty($wp_locator_get_country)) {
                return $wp_locator_get_country;
            }
            if (function_exists('wcpbc_get_woocommerce_country')) {
                $wp_locator_get_country = wcpbc_get_woocommerce_country();
            }
            if (empty($wp_locator_get_country) && class_exists('WC_Geolocation')) {
                $wp_locator_get_country = WC_Geolocation::geolocate_ip()['country'];
            }
            if(!empty($wp_locator_get_country) && is_string($wp_locator_get_country) && strlen($wp_locator_get_country) == 2) {
                return $wp_locator_get_country;
            } else {
                $wp_locator_get_country = null;
            }
            return null;
        }
        public $admin = null;
        public $front = null;

        public function init()
        {
            //echo 'WPLocator_init()';
            $GLOBALS['debug'] = [];
            if (is_admin()) {
                require_once __DIR__ . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'admin.php';
                $this->admin = new WPLocator_admin($this);
                $this->admin->init();
                add_filter('woocommerce_prevent_admin_access', [ $this, 'woocommerce_locations_editor_admin_access' ], 10, 1);
                add_action('admin_footer', [ $this, 'admin_footer' ]);
                add_action('admin_init', [ $this, 'export_csv' ]); //you can use admin_init as well
            } else {
                require_once __DIR__ . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'front.php';
                $this->front = new WPLocator_front($this);
                $this->front->init();
                add_filter('woocommerce_disable_admin_bar', [ $this, 'woocommerce_locations_editor_admin_access' ], 10, 1);
            }
            add_action('init', [ $this, 'WPLocator_setup_post_type' ]);
            add_action('init', [ $this, 'WPLocator_load_text_domain' ]);
            register_activation_hook(__FILE__, [ $this, 'WPLocator_activate' ]);
            register_deactivation_hook(__FILE__, [ $this, 'WPLocator_deactivate' ]);
            add_filter('user_has_cap', [ $this, 'does_user_has_cap' ], 10, 4);
            //add_action('all_admin_notices',[$this,'shutdown']);
            add_action('wp_ajax_wp_locator_query', [ $this, 'wp_locator_query' ]);
            add_action('wp_ajax_nopriv_wp_locator_query', [ $this, 'wp_locator_query' ]);
            //add_filter( 'pll_get_post_types', [$this,'add_cpt_to_pll'], 10, 2 );
            add_filter('bulk_actions-edit-' . LOCATION_PT, [ $this, 'register_my_bulk_actions' ]);
            //add_filter( 'bulk_actions-edit-post', [$this,'register_my_bulk_actions'] );
            add_filter('single_template', [ $this, 'single_location_template' ]);
            add_action('edit_post', [ $this, 'restrict_post_deletion' ], 10, 1);
            add_action('wp_trash_post', [ $this, 'restrict_post_deletion' ], 10, 1);
            add_action('before_delete_post', [ $this, 'restrict_post_deletion' ], 10, 1);
            add_action('personal_options_update', [ $this, 'user_profile_update' ]);
            add_action('edit_user_profile_update', [ $this, 'user_profile_update' ]);
            add_action('user_new_form', [ $this, 'personal_options' ], 99999, 1);
            add_action('edit_user_profile', [ $this, 'personal_options' ], 99999, 1);
            add_action('admin_head', [ $this, 'admin_head' ]);
            add_action('registered_taxonomy', [ $this, 'after_setup_theme' ], 10, 3);
            add_action("save_post_" . LOCATION_PT, [ $this, 'save_post_location_clear_cache' ], 10, 3);
            add_filter('pvbc_get_post_types', [ $this, 'pvbc_get_post_types' ], 10, 1);
        }
        public function pvbc_get_post_types($post_types)
        {
            if (($key = array_search('location', $post_types)) !== false) {
                unset($post_types[ $key ]);
            }
            return $post_types;
        }
        public function save_post_location_clear_cache($post_ID, $post, $update)
        {
            if (LOCATION_PT !== $post->post_type) {
                return;
            }
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
            global $save_post_location_clear_cache;
            if (empty($save_post_location_clear_cache)) {
                $dir = WP_CONTENT_DIR . 'cache' . DIRECTORY_SEPARATOR . 'wp-locator';
                if (file_exists($dir)) {
                    $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
                    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach ($ri as $file) {
                        $file->isDir() ? rmdir($file) : unlink($file);
                    }
                } else {
                    //exit('no dir'.$dir);
                }
                $save_post_location_clear_cache = true;
            }
        }
        public function after_setup_theme($taxonomy, $object_type, $args)
        {
            if (LOCATION_PT . '_category' == $taxonomy) {
                if (function_exists('pll_register_string')) {
                    $terms = get_terms([
                        'taxonomy' => LOCATION_PT . '_category',
                        'hide_empty' => false,
                    ]);
                    //var_dump($terms);
                    foreach ($terms as $key => $term) {
                        pll_register_string(LOCATION_PT . '_category', $term->name, 'wp-locator', false);
                    }
                }
            }
        }
        public function admin_head()
        {
            if (current_user_can('locations_editor') && ! current_user_can('administrator')) {
                $currentScreen = get_current_screen();
                if ($currentScreen->id == "location") {
                    echo '<style>
					#tagsdiv-location_category,#post-body-content,#titlewrap,#wpseo_meta,#edit-slug-box,#authordiv{
						display:none;
					}
					</style>';
                }
            }
        }
        public function WPLocator_load_text_domain()
        {
            load_plugin_textdomain('wp-locator', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        public function personal_options($user)
        {
            // || !class_exists( 'woocommerce' )!current_user_can('administrator') || !is_array($user->roles) ||
            if (! is_a($user, 'WP_User') || ! array_intersect(apply_filters('WPLocator_managed_user_roles', [ 'author', 'locations_editor', 'shop_manager', 'sales_director' ]), (array) $user->roles)) {
                return;
            }
            $country = get_user_meta($user->ID, 'can_edit_locations_countries', true);
            if (! is_array($country)) {
                $country = [];
            }
            ?>
<h2><svg style="width:20px;" aria-hidden="true" focusable="false" data-icon="pencil-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <g>
            <path fill="currentColor" d="M96 352H32l-16 64 80 80 64-16v-64H96zM498 74.26l-.11-.11L437.77 14a48.09 48.09 0 0 0-67.9 0l-46.1 46.1a12 12 0 0 0 0 17l111 111a12 12 0 0 0 17 0l46.1-46.1a47.93 47.93 0 0 0 .13-67.74z">
            </path>
            <path fill="currentColor" d="M.37 483.85a24 24 0 0 0 19.47 27.8 24.27 24.27 0 0 0 8.33 0l67.32-16.16-79-79zM412.3 210.78l-111-111a12.13 12.13 0 0 0-17.1 0L32 352h64v64h64v64l252.27-252.25a12 12 0 0 0 .03-16.97zm-114.41-24.93l-154 154a14 14 0 1 1-19.8-19.8l154-154a14 14 0 1 1 19.8 19.8z">
            </path>
        </g>
    </svg>
    <?= esc_html('Locations Countries', 'wp-locator') ?>
</h2>
<table class="form-table">
    <tbody>
        <tr>
            <th colspan="2">
                <button type="button" id="WPLocator_countries_toggler">Toggle all</button>
                <?= join('	', $country); ?>
                </div>
                </div>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                <?php
        global $woocommerce;
            $countries_obj = new WC_Countries();
            $countries = []; //[''=>'None'];
            $wc_countries = $countries_obj->__get('countries');
            foreach ($country as $key => $value) {
                if (array_key_exists($value, $wc_countries)) {
                    $countries[ $value ] = $wc_countries[ $value ];
                    unset($wc_countries[ $value ]);
                }
            }
            $countries += $wc_countries;
            ?>
                <div id="WPLocator_countries_selector" style="display:flex; flex-wrap:wrap;">
                    <input type="hidden" name="can_edit_locations_countries_form">
                    <?php
                foreach ($countries as $key => $value) {
                    ?>
                    <label class="country-list-item"><span>
                            <?= esc_attr($key) ?>
                        </span> <input type="checkbox" name="can_edit_locations_countries[]" value="<?= esc_attr($key) ?>" <?php if (in_array($key, $country)) { ?>
                        checked="checked" <?php } ?>><?= esc_html($value) ?></label>
                    <?php
                }
            ?>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        $('#WPLocator_countries_toggler').on('click', function(e) {
                            let inputs = $('#WPLocator_countries_selector input')
                            inputs.prop("checked", !inputs.prop("checked"))
                        })
                    })
                </script>
                <style>
                    .country-list-item {
                        padding: .5em;
                        _white-space: nowrap;
                        flex: 1 1 200px;
                    }

                    .country-list-item>span {
                        display: inline-block;
                        width: 21px;
                        min-width: 21px;
                        text-align: right;
                    }
                </style>
            </td>
        </tr>
    </tbody>
</table>
<?php
        }
        public function user_profile_update($user_id)
        {
            if (! current_user_can('administrator')) {
                return false;
            }
            if (isset($_POST['can_edit_locations_countries_form'])) {
                if (! is_array($_POST['can_edit_locations_countries'])) {
                    $_POST['can_edit_locations_countries'] = [];
                }
                sort($_POST['can_edit_locations_countries']);
                update_user_meta($user_id, 'can_edit_locations_countries', $_POST['can_edit_locations_countries']);
            }
        }

        public function export_csv()
        {
            if (! empty($_POST['locations_export_csv'])) {
                if ($this->export_allowed()) {
                    header("Content-type: application/force-download; charset=utf-8");
                    header('Content-Disposition: inline; filename="locations_' . sanitize_title(get_bloginfo('name')) . '_' . date('Y-m-d H:i:s') . '.csv"');
                    // WP_User_Query arguments
                    $args = array(
                        'order' => 'ASC',
                        'orderby' => 'display_name',
                        'fields' => 'all',
                    );

                    $the_query = new WP_Query(
                        [
                        'post_type' => LOCATION_PT,
                        'posts_per_page' => -1,
                        //'post_status'=>'publish',
                        'lang' => pll_current_language()
            ]
                    );
                    $results = [];
                    $keys = [ 'id', 'status', 'country', 'title', 'description', 'place_id', 'categories', 'is_pickup', 'city', 'state', 'lat', 'lng', 'country_name', 'route', 'street_number', 'zip_code', 'website', 'phone', 'email', 'name' ];
                    if ($the_query->have_posts()) {
                        while ($the_query->have_posts()) :
                            $the_query->the_post();
                            $id = get_the_ID();
                            $categories = get_the_terms($id, LOCATION_PT . '_category');
                            $result = [
                                "id" => $id,
                                "status" => get_post_status($id),
                                "country" => get_post_meta($id, '_country', true),
                                "title" => html_entity_decode(get_the_title(), ENT_NOQUOTES, 'UTF-8'),
                                "description" => html_entity_decode(get_the_excerpt(), ENT_NOQUOTES, 'UTF-8'),
                                "place_id" => get_post_meta($id, 'place_id', true),
                                "categories" => is_array($categories) && ! empty($categories) ? join(',', array_map(function ($o) {
                                    return $o->name;
                                }, $categories)) : '',
                                "is_pickup" => get_post_meta($id, '_pickup', true),
                                'city' => '', 'state' => '', 'lat' => '', 'lng' => '', 'country_name' => '', 'route' => '', 'street_number' => '', 'zip_code' => '', 'website' => '', 'phone' => '', 'email' => '', 'name' => ''
                            ];
                            foreach ((array) get_post_meta($id, '_location_settings', true) as $key => $value) {
                                if (! in_array($key, $keys)) {
                                    $keys[] = $key;
                                }
                                $result[ $key ] =
                                //$value;
                                html_entity_decode($value, ENT_NOQUOTES, 'UTF-8');
                                //mb_convert_encoding($value, 'HTML-ENTITIES','UTF-8' );
                            }
                            $results[] = $result;
                        endwhile;
                        $delimiter = ',';
                        $enclosure = '"';
                        $escape_char = "\\";
                        $f = fopen('php://memory', 'r+');
                        fputs($f, chr(0xEF) . chr(0xBB) . chr(0xBF));
                        fputcsv($f, $keys, $delimiter, $enclosure, $escape_char);
                        foreach ($results as $item) {
                            fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
                        }
                        rewind($f);
                        echo stream_get_contents($f);
                        wp_reset_postdata();
                        exit();
                    }
                    echo '"' . $first_name . '","' . $last_name . '","' . $email . '","' . ucfirst($role[0]) . '"' . "\r\n";
                } else {
                    exit('not allowed');
                }
                exit();
            }
        }

        protected function export_allowed()
        {
            return current_user_can('edit_locations') && current_user_can('export');
        }
        public static function import_allowed()
        {
            //return false;
            return current_user_can('edit_locations') && current_user_can('import') && in_array(get_current_user_id(), [ 1, 2 ]);
        }

        public function admin_footer()
        {
            $screen = get_current_screen();
            if ($screen->id != "edit-location") {
                //echo '____'.$screen->id;
                return; // Only add to users.php page
            }

            if ($this->export_allowed() || $this->import_allowed()) {
                ?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        <?php if ($this->export_allowed()) { ?>
        $('.wp-header-end').before('<form action="#" method="POST" style="display:inline-block;"><input type="hidden" id="locations_export_csv" name="locations_export_csv" value="1" /><input class="user_export_button page-title-action" style="margin-top:3px;" type="submit" value="<?= esc_attr('Export All as CSV', 'locations'); ?>" /></form>')
        <?php } ?>
        <?php if ($this->import_allowed()) { ?>
        $('.wp-header-end').before('<a href="<?= esc_attr(admin_url("edit.php?post_type=" . LOCATION_PT . "&page=import")) ?>" class="page-title-action">Import</a>');
        <?php } ?>
    });
</script>
<?php
            }
        }

        public function exclude_posts($query)
        {
            //if(current_user_can('shop_manager'))var_dump($query->get_queried_object());
            //if($query->query)echo $query->query['post_type'];
            //exit(join(',',get_user_meta(get_current_user_id(), 'can_edit_locations_countries', true)));
            //if(!$query->get_queried_object() || !in_array($query->get_queried_object()->name , [LOCATION_PT,'shop_order']) || wp_doing_cron() || wp_doing_ajax() || !is_admin() || current_user_can( 'administrator' ))return $query;
            if (! isset($query->query) || ! isset($query->query['post_type']) || ! in_array($query->query['post_type'], [ LOCATION_PT, 'shop_order' ]) || wp_doing_cron() || wp_doing_ajax() || ! is_admin() || current_user_can('administrator')) {
                return $query;
            }
            if ($query->query['post_type'] == LOCATION_PT) {
                $query->set(
                    'meta_query',
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => '_country',
                            'value' => (array) get_user_meta(get_current_user_id(), 'can_edit_locations_countries', true),
                            'type' => 'CHAR',
                            'compare' => 'IN',
                        )
                    )
                );
            }
            if ($query->query['post_type'] == 'shop_order') {
                $query->set(
                    'meta_query',
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => '_billing_country',
                            'value' => (array) get_user_meta(get_current_user_id(), 'can_edit_locations_countries', true),
                            'type' => 'CHAR',
                            'compare' => 'IN',
                        )
                    )
                );
            }
            return $query;
        }

        public function restrict_post_deletion()
        {
            $user_id = get_current_user_id();
            global $post;
            /*if(1){
                var_dump(current_filter());
                var_dump($post);
                exit();
            }*/
            if (! $post || ! is_object($post) || $post->post_type != LOCATION_PT || current_user_can('administrator') || $post->post_author == $user_id) {
                return;
            }
            $post_country = get_post_meta($post->ID, '_country', true);
            //if(empty($post_country))return;
            $user_countries = (array) get_user_meta($user_id, 'can_edit_locations_countries', true);
            if (is_array($_POST) && array_key_exists('location_settings', $_POST) && array_key_exists('country', $_POST['location_settings'])) {
                if (! in_array($_POST['location_settings']['country'], $user_countries)) {
                    do_action('admin_page_access_denied');
                    wp_die(__('You cannot modify or delete this country.'));
                    exit;
                }
            }
            if (! in_array($post_country, $user_countries)) {
                do_action('admin_page_access_denied');
                wp_die(__('You cannot modify or delete this country.'));
                exit;
            }
        }

        public function single_location_template($single)
        {
            global $post;
            /* Checks for single template by post type */
            if ($post && $post->post_type == LOCATION_PT) {
                $file = $this->WP_LOCATOR_DIRECTORY() . '/templates/single-location.php';
                if (file_exists($file)) {
                    return $file;
                }
            }

            return $single;

        }

        public function register_my_bulk_actions($bulk_actions)
        {
            //var_dump([$bulk_actions]);
            $bulk_actions['trash'] = __('Move to Bin');
            $bulk_actions['delete'] = __("Delete permanently");
            return $bulk_actions;
        }
        public function add_cpt_to_pll($post_types, $is_settings)
        {
            if ($is_settings) {
                // hides 'my_cpt' from the list of custom post types in Polylang settings
                //unset( $post_types[LOCATION_PT] );
            } else {
                // enables language and translation management for 'my_cpt'
                $post_types[ LOCATION_PT ] = LOCATION_PT;
            }
            return $post_types;
        }
        public function wp_locator_query()
        {
            //exit(current_user_can('read_location')?'true':'false');
            //exit('[]');
            $use_cache = false;
            $current_language = pll_current_language('slug');
            $curlang = PLL()->curlang;
            $delault_lang = pll_default_language();
            PLL()->curlang = PLL()->model->get_language($delault_lang);
            wp_mkdir_p(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'wp-locator');
            $cache_file = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'wp-locator' . DIRECTORY_SEPARATOR . 'locations_' . $current_language . '.json';
            if ($use_cache && file_exists($cache_file) && time() - filemtime($cache_file) < 60 * 60) {
                $result = file_get_contents($cache_file);
                echo $result;
                die();
            } else {
                //sleep(10);
                //header("Content-Type: application/json");
                $result = array();
                // https://codex.wordpress.org/Class_Reference/WP_Query
                $the_query = new WP_Query(
                    [
                    'post_type' => LOCATION_PT,
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'lang' => $delault_lang,
                    'meta_key' => '_country',
                    'orderby' => 'meta_value',
                    'order' => 'ASC'
                    //'s' => esc_attr( $_POST['search_string'] )
        ]
                );
                //exit(print_r($the_query,1));
                if ($the_query->have_posts()) {
                    global $post;
                    while ($the_query->have_posts()) :
                        $the_query->the_post();
                        $_id = get_the_ID();
                        $_location_settings = (array) get_post_meta($_id, '_location_settings', true);
                        if (empty($_location_settings['lat']) || empty($_location_settings['lng'])) {
                            continue;
                        }
                        $categories = get_the_terms($post, LOCATION_PT . '_category');
                        $_country = get_post_meta($_id, '_country', true);
                        if (empty($_country) && isset($_location_settings['country'])) {
                            $_country = $_location_settings['country'];
                        }
                        if (empty($_country)) {
                            $_country = 'other';
                        }
                        if (empty($_location_settings['country_name']) && array_key_exists($_country, WC()->countries->countries)) {
                            $_location_settings['country_name'] = WC()->countries->countries[ $_country ];
                        }
                        $result[] = array(
                            "id" => $_id,
                            "title" => get_the_title(),
                            "description" => get_the_excerpt(),
                            "data" => array_merge($_location_settings, [ 'country' => $_country ]),
                            "place_id" => get_post_meta($_id, 'place_id', true),
                            "country" => $_country,
                            "categories" => is_array($categories) && ! empty($categories) ? array_map(function ($o) use ($current_language) {
                                return pll_translate_string($o->name, $current_language);
                            }, $categories) : null
                        );
                    endwhile;
                    $result = json_encode($result);
                    file_put_contents($cache_file, $result);
                    wp_reset_postdata();
                    echo $result;
                    die();
                } else {
                    exit(current_user_can('read_location') ? 'true' : 'false');
                }
            }
        }

        public function shutdown()
        {
            if (current_user_can('administrator') || current_user_can('shop_manager')) {
                ?>
<pre class="" style="display:flex;flex-wrap:wrap;"><?php
                foreach ($GLOBALS['debug'] as $key => $value) {
                    echo '<div>' . $key . ':';
                    if ($key == 'custom') {
                        print_r($value);
                        echo '</div>';
                        continue;
                    }
                    //$value=array_unique($value);
                    print_r($value);
                    echo '</div>';
                }
                //print_r($GLOBALS['debug']);
                ?></pre>
<?php
            }
        }
        public function woocommerce_locations_editor_admin_access($show)
        {
            if (current_user_can('edit_' . LOCATION_PT)) {
                return false;
            }
            return $show;
        }
        public function WPLocator_setup_post_type()
        {
            if(!wp_roles()->is_role('locations_editor')) {
                add_role(
                    'locations_editor',
                    'Locations editor',
                    array_merge(
                        [
                            'edit_location'          => true,
                            'read_location'          => true,
                            'delete_location'        => true,
                            'edit_locations'         => true,
                            'edit_others_locations'  => true,
                            'publish_locations'      => true,
                            'read_private_locations' => true,
                            'create_locations'       => true,
                        ],
                        get_role('subscriber')->capabilities
                    )
                );
            }
            register_post_type(
                LOCATION_PT,
                [
                    'show_in_rest' => true, //needed for gutenberg
                    'public' => true,
                    'exclude_from_search' => true,
                    'publicly_queryable' => true,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'query_var' => true,
                    'rewrite' => array( 'slug' => LOCATION_PT . 's', 'with_front' => false ),
                    'capability_type' => LOCATION_PT, //'post',
                    //'map_meta_cap'=>false,
                    'has_archive' => true,
                    'hierarchical' => false,
                    'menu_position' => 101,
                    'supports' =>
                    [
                        'title',
                        'author',
                        //'editor',//'thumbnail',//'custom-fields',//'excerpt',//'comments',
                    ],
                    'taxonomies' => array( LOCATION_PT . '_category' ),
                    'labels' =>
                    [
                        'name' => _x('Locations', 'Post type general name', 'wp-locator'),
                        'singular_name' => _x('Location', 'Post type singular name', 'wp-locator'),
                        'menu_name' => _x('Locations', 'Admin Menu text', 'wp-locator'),
                        'name_admin_bar' => _x('Location', 'Add New on Toolbar', 'wp-locator'),
                        'add_new' => __('Add New', 'wp-locator'),
                        'add_new_item' => __('Add New Location', 'wp-locator'),
                        'new_item' => __('New Location', 'wp-locator'),
                        'edit_item' => __('Edit Location', 'wp-locator'),
                        'view_item' => __('View Location', 'wp-locator'),
                        'all_items' => __('All Locations', 'wp-locator'),
                        'search_items' => __('Search Locations', 'wp-locator'),
                        'parent_item_colon' => __('Parent Locations:', 'wp-locator'),
                        'not_found' => __('No Locations found.', 'wp-locator'),
                        'not_found_in_trash' => __('No Locations found in Trash.', 'wp-locator'),

                        'insert_into_item' => _x('Insert into Location', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'wp-locator'),
                        'uploaded_to_this_item' => _x('Uploaded to this Location', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'wp-locator'),
                        'filter_items_list' => _x('Filter Locations list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'wp-locator'),
                        'items_list_navigation' => _x('Locations list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'wp-locator'),
                        'items_list' => _x('Locations list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'wp-locator'),
                    ],
                    'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" aria-hidden="true" focusable="false" data-icon="map-marked-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><g><path fill="currentColor" d="M554.06 161.16L416 224v288l139.88-55.95A32 32 0 0 0 576 426.34V176a16 16 0 0 0-21.94-14.84zM20.12 216A32 32 0 0 0 0 245.66V496a16 16 0 0 0 21.94 14.86L160 448V214.92a302.84 302.84 0 0 1-21.25-46.42zM288 359.67a47.78 47.78 0 0 1-36.51-17C231.83 319.51 210.92 293.09 192 266v182l192 64V266c-18.92 27.09-39.82 53.52-59.49 76.72A47.8 47.8 0 0 1 288 359.67z"></path><path fill="currentColor" d="M288 0a126 126 0 0 0-126 126c0 56.26 82.35 158.8 113.9 196a15.77 15.77 0 0 0 24.2 0C331.65 284.8 414 182.26 414 126A126 126 0 0 0 288 0zm0 168a42 42 0 1 1 42-42 42 42 0 0 1-42 42z"></path></g></svg>'),
                ]
            );

            register_taxonomy(LOCATION_PT . '_category', [ LOCATION_PT ], [
                'hierarchical' => true,
                'labels' => [
                    'name' => _x('Locations Categories', 'taxonomy general name'),
                    'singular_name' => _x('Category', 'taxonomy singular name'),
                    'search_items' => __('Search Categories'),
                    'popular_items' => __('Popular Categories'),
                    'all_items' => __('All Categories'),
                    'parent_item' => __('Parent Category'),
                    'parent_item_colon' => __('Parent Category:'),
                    'edit_item' => __('Edit Category'),
                    'update_item' => __('Update Category'),
                    'add_new_item' => __('Add New Category'),
                    'new_item_name' => __('New Category Name'),
                ],
                'hierarchical' => false,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => [ 'slug' => LOCATION_PT . '_category' ],
                /* 'capabilities' => [
                'manage_terms' => 'manage_' . LOCATION_PT . '_category',
                'edit_terms' => 'edit_' . LOCATION_PT . '_category',
                'delete_terms' => 'delete' . LOCATION_PT . '_category',
                'assign_terms' => 'assign_' . LOCATION_PT . '_category',
            ], */
]);


            /*'featured_image'        => _x( 'Location Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'wp-locator' ),'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'wp-locator' ),'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'wp-locator' ),'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'wp-locator' ),'archives'              => _x( 'Location archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'wp-locator' ),*/
            //remove_role('locations_editor');
            /*add_role(
            'locations_editor',
            'Locations editor',
            array_merge(
            [
            'edit_location'          => true,
            'read_location'          => true,
            'delete_location'        => true,
            'edit_locations'         => true,
            'edit_others_locations'  => true,
            'publish_locations'      => true,
            'read_private_locations' => true,
            'create_locations'       => true,
            ],
            get_role('subscriber')->capabilities
            )
);*/

            /*
            $role=get_role('locations_editor');
            $role->remove_cap('edit_location');
            $role->remove_cap('read_location');
            $role->remove_cap('delete_location');
            $role->remove_cap('edit_locations');
            $role->remove_cap('publish_locations');
            $role->remove_cap('read_private_locations');
            $role->remove_cap('create_locations');

            $role->remove_cap('edit_others_locations');

            $role->add_cap('edit_location'          , true);
            $role->add_cap('read_location'          , true);
            $role->add_cap('delete_location'        , true);
            $role->add_cap('edit_locations'         , true);
            $role->add_cap('edit_others_locations'  , true);*/
            //$role->add_cap('publish_locations'      , true);
            //$role->add_cap('read_private_locations' , true);
            //$role->add_cap('create_locations'       , true);

            $role = get_role('administrator');
            if (! $role->has_cap('edit_location')) {
                //$role->add_cap( 'edit_others_posts', true );
                $role->add_cap('edit_location', true);
                $role->add_cap('read_location', true);
                $role->add_cap('delete_location', true);
                $role->add_cap('edit_locations', true);
                $role->add_cap('edit_others_locations', true);
                $role->add_cap('publish_locations', true);
                $role->add_cap('read_private_locations', true);
                $role->add_cap('create_locations', true);
            }
            $role = get_role('shop_manager');
            if (! $role->has_cap('edit_location')) {
                //$role->add_cap( 'edit_others_posts', true );
                $role->add_cap('edit_location', true);
                $role->add_cap('read_location', true);
                $role->add_cap('delete_location', true);
                $role->add_cap('edit_locations', true);
                $role->add_cap('edit_others_locations', true);
                $role->add_cap('publish_locations', true);
                $role->add_cap('read_private_locations', true);
                $role->add_cap('create_locations', true);
            }
            //var_dump($role->capabilities);exit();
            //$role->remove_cap('publish_products');
            /*global $wp_roles;
            $all_roles = $wp_roles->roles;
            $editable_roles = apply_filters('editable_roles', $all_roles);
            //echo '<pre>';
            //var_dump($editable_roles);exit();
            foreach($editable_roles as $key => $role){
                $role = get_role( $key );
                $role->add_cap('read_location'          , true);
            }*/


        }

        public function WPLocator_activate()
        {
            $this->WPLocator_setup_post_type();
            if(!wp_roles()->is_role('locations_editor')) {
                add_role(
                    'locations_editor',
                    'Locations editor',
                    array_merge(
                        [
                            'edit_location'          => true,
                            'read_location'          => true,
                            'delete_location'        => true,
                            'edit_locations'         => true,
                            'edit_others_locations'  => true,
                            'publish_locations'      => true,
                            'read_private_locations' => true,
                            'create_locations'       => true,
                        ],
                        get_role('subscriber')->capabilities
                    )
                );
            }
            flush_rewrite_rules();
        }
        public function WPLocator_deactivate()
        {
            remove_role('locations_editor');
            unregister_post_type(LOCATION_PT);
            flush_rewrite_rules();
        }

        public function does_user_has_cap($allcaps, $caps, $args, $user)
        {
            //if(empty($GLOBALS))$GLOBALS=[];
            if (! array_key_exists('debug', $GLOBALS) || ! is_array($GLOBALS['debug'])) {
                $GLOBALS['debug'] = [];
            }
            //else{var_dump($GLOBALS['debug']);exit();}
            if (! array_key_exists('args', $GLOBALS['debug'])) {
                $GLOBALS['debug']['args'] = [];
            }
            if (! array_key_exists('caps', $GLOBALS['debug']['args'])) {
                $GLOBALS['debug']['caps'] = [];
            }
            if (0) {
                foreach ($args as $value) {
                    if (is_array($value)) {
                        $value = 'ARRAY';
                    }
                    if (! array_key_exists($value, $GLOBALS['debug']['args'])) {
                        $GLOBALS['debug']['args'][ $value ] = 1;
                    } else {
                        $GLOBALS['debug']['args'][ $value ]++;
                    }
                }
                foreach ($caps as $value) {
                    if (is_array($value)) {
                        $value = 'ARRAY';
                    }
                    if (! array_key_exists($value, $GLOBALS['debug']['caps'])) {
                        $GLOBALS['debug']['caps'][ $value ] = 1;
                    } else {
                        $GLOBALS['debug']['caps'][ $value ]++;
                    }
                }
            } else {
                $GLOBALS['debug']['args'] = array_merge($GLOBALS['debug']['args'], $args);
                $GLOBALS['debug']['caps'] = array_merge($GLOBALS['debug']['caps'], $caps);
            }
            //var_dump($GLOBALS['debug']);exit();

            /*if(in_array($args[0],['read_location'])){
                $allcaps['read_location']=1;
            }*/

            if (! in_array(
                $args[0],
                [
                    'edit_post',
                    'read_post',
                    'delete_post',
                    'edit_posts',
                    'edit_others_posts',
                    'publish_posts',
                    'read_private_posts',
                    'create_posts',

                    'publish_locations',
                ]
            )) {
                return $allcaps;
            }
            if (! in_array(
                $caps[0],
                [
                    'edit_location',
                    'read_location',
                    'delete_location',
                    'edit_locations',
                    'publish_locations',
                    'read_private_locations',
                    'create_locations',
                ]
            )) {
                return $allcaps;
            }
            if (array_key_exists(2, $args)) {
                return $allcaps;
            }
            if (is_array($user->roles) && in_array('locations_editor', $user->roles)) {
                $can = 1;
                $allcaps['edit_location'] = $can;
                $allcaps['read_location'] = $can;
                $allcaps['delete_location'] = $can;
                $allcaps['edit_locations'] = $can;
                $allcaps['edit_others_locations'] = $can;
                $allcaps['publish_locations'] = $can;
                $allcaps['read_private_locations'] = $can;
                $allcaps['create_locations'] = $can;

                $allcaps['edit_post'] = $can;
                $allcaps['read_post'] = $can;
                $allcaps['delete_post'] = $can;
                $allcaps['edit_posts'] = $can;
                $allcaps['edit_others_posts'] = $can;
                $allcaps['publish_posts'] = $can;
                $allcaps['read_private_posts'] = $can;
                $allcaps['create_posts'] = $can;
            }
            return $allcaps;
        }
    }
    global $WPLocator;
    add_action('plugins_loaded', array( 'WPLocator', 'get_instance' ));
}
?>