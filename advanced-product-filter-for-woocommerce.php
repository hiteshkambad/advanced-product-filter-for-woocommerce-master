<?php
/**
 * Plugin Name: Advanced Product Filter For WooCommerce
 * Plugin URI : https://github.com/cmssoft/advanced-product-filter-for-woocommerce
 * Description: Advanced Product Filter For WooCommerce
 * Version: 1.0.0
 * Author: cmssoft
 * Author URI : https://github.com/cmssoft/
 * Text Domain : advanced-product-filter-for-woocommerce
*/
defined( 'ABSPATH' ) OR exit;

define('APFFW_PATH', plugin_dir_path(__FILE__));
define('APFFW_LINK', plugin_dir_url(__FILE__));
define('APFFW_PLUGIN_NAME', plugin_basename(__FILE__));
define('APFFW_EXT_PATH', APFFW_PATH . 'ext/');
define('APFFW_VERSION', '1.0.0');
define('APFFW_MIN_WOOCOMMERCE_VERSION', '3.6');

include APFFW_PATH . 'classes/storage.php';
include APFFW_PATH . 'classes/helper.php';
include APFFW_PATH . 'classes/cron.php';
include APFFW_PATH . 'classes/hooks.php';
include APFFW_PATH . 'classes/ext.php';
include APFFW_PATH . 'classes/counter.php';
include APFFW_PATH . 'classes/widgets.php';
include APFFW_PATH . 'lib/alert/index.php';

final class APFFW {

    public $settings = array();
    public $html_types = array(
        'radio' => 'Radio',
        'checkbox' => 'Checkbox',
        'select' => 'Drop-down',
        'mselect' => 'Multi drop-down'
    );
    public $items_keys = array(
        'by_price'
    );
    public static $query_cache_table = 'apffw_query_cache';
    public $is_activated = true;
    private $session_rct_key = 'apffw_really_current_term';
    public $storage = null;
    public $storage_type = 'transient';
    public $is_free_ver = false;

    public function __construct() {
        global $wpdb;
        self::$query_cache_table = $wpdb->prefix . self::$query_cache_table;
        add_action('woocommerce_init', array($this, 'replacing_template_loop_product_thumbnail'));
        add_action('wp_ajax_apffw_upload_ext', array($this, 'apffw_upload_ext'));
        add_action('wp_ajax_nopriv_apffw_upload_ext', array($this, 'apffw_upload_ext'));

        $this->init_settings();
        if (!$this->is_should_init()) {
            $this->is_activated = false;
            return NULL;
        }
        $this->init_extensions();
        $this->storage = new APFFW_STORAGE($this->storage_type);

        if (!defined('DOING_AJAX')) {
            global $wp_query;
            if (isset($wp_query->query_vars['taxonomy']) AND in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product'))) {
                $this->set_really_current_term();
            }
        }

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('widgets_init', array($this, 'widgets_init'));
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            return;
        }

        $first_init = (int) get_option('apffw_first_init', 0);
        if ($first_init != 1) {
            update_option('apffw_first_init', 1);
            update_option('apffw_set_automatically', 0);
            update_option('apffw_autosubmit', 1);
            update_option('apffw_show_count', 1);
            update_option('apffw_show_count_dynamic', 0);
            update_option('apffw_hide_dynamic_empty_pos', 0);
            update_option('apffw_try_ajax', 0);
            update_option('apffw_checkboxes_slide', 1);
            update_option('apffw_hide_red_top_panel', 0);
            update_option('apffw_sort_terms_checked', 0);
            update_option('apffw_filter_btn_txt', '');
            update_option('apffw_reset_btn_txt', '');
            $first_options = array(
                'use_chosen' => 1,
            );
            update_option('apffw_settings', $first_options);
            $this->settings = $first_options;
            update_option('image_default_link_type', 'file');
        }

        $this->settings['delete_image'] = apply_filters('apffw_delete_img_url', APFFW_LINK . "img/delete.png");

        load_plugin_textdomain('apffw-products-filter', false, dirname(plugin_basename(__FILE__)) . '/languages');
        add_filter('plugin_action_links_' . APFFW_PLUGIN_NAME, array($this, 'plugin_action_links'), 50);
        add_action('woocommerce_settings_tabs_array', array($this, 'woocommerce_settings_tabs_array'), 50);
        add_action('woocommerce_settings_tabs_apffw', array($this, 'print_plugin_options'), 50);

        if (isset($this->settings['optimize_js_files']) AND $this->settings['optimize_js_files']) {
            add_action('wp_head', array($this, 'wp_head'), 999);
            add_action('wp_footer', array($this, 'wp_load_js'), 11);
        } else {
            add_action('wp_head', array($this, 'wp_head'), 999);
            add_action('wp_head', array($this, 'wp_load_js'), 999);
        }
        add_action('wp_footer', array($this, 'wp_footer'), 999);
        if (!isset($_REQUEST['legacy-widget-preview'])) {
            add_shortcode('apffw', array($this, 'apffw_shortcode'));
            add_shortcode('apffw_btn', array($this, 'show_btn'));
            add_shortcode('apffw_mobile', array($this, 'show_mobile_btn'));
        }
        
        add_action('wp_ajax_apffw_save_options', array($this, 'apffw_save_options'), 1);
        add_action('wp_ajax_apffw_draw_products', array($this, 'apffw_draw_products'));
        add_action('wp_ajax_nopriv_apffw_draw_products', array($this, 'apffw_draw_products'));
        add_action('wp_ajax_apffw_redraw_apffw', array($this, 'apffw_redraw_apffw'));
        add_action('wp_ajax_nopriv_apffw_redraw_apffw', array($this, 'apffw_redraw_apffw'));
        
        add_filter('widget_text', 'do_shortcode');
        add_action('parse_query', array($this, "parse_query"), 9999);
        add_filter('woocommerce_product_query', array($this, "woocommerce_product_query"), 9999);
        add_action('body_class', array($this, 'body_class'), 9999);
        
        add_action('woocommerce_before_shop_loop', array($this, 'woocommerce_before_shop_loop'), 2);
        add_action('woocommerce_after_shop_loop', array($this, 'woocommerce_after_shop_loop'), 10);

        add_shortcode('apffw_products', array($this, 'apffw_products'));
        add_shortcode('apffw_products_ids_prediction', array($this, 'apffw_products_ids_prediction'));
        add_shortcode('apffw_price_filter', array($this, 'apffw_price_filter'));

        add_shortcode('apffw_search_options', array($this, 'apffw_search_options'));
        add_shortcode('apffw_found_count', array($this, 'apffw_found_count'));
        
        add_action('wp_ajax_apffw_cache_count_data_clear', array($this, 'cache_count_data_clear'));
        add_action('wp_ajax_apffw_cache_terms_clear', array($this, 'apffw_cache_terms_clear'));
        add_action('wp_ajax_apffw_price_transient_clear', array($this, 'apffw_price_transient_clear'));
        
        add_action('wp_ajax_apffw_remove_ext', array($this, 'apffw_remove_ext'));
        add_filter('sidebars_widgets', array($this, 'sidebars_widgets'));
        add_filter('apffw_modify_query_args', array($this, 'apffw_modify_query_args'), 1);
        if ($this->get_option('cache_count_data_auto_clean')) {
            add_action('apffw_cache_count_data_auto_clean', array($this, 'cache_count_data_clear'));
            if (!wp_next_scheduled('apffw_cache_count_data_auto_clean')) {
                wp_schedule_event(time(), $this->get_option('cache_count_data_auto_clean'), 'apffw_cache_count_data_auto_clean');
            }
        }

        if ($this->get_option('per_page') > 0 AND $this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
            if (version_compare(PHP_VERSION, '5.3.0', '<=')) {
                add_filter('loop_shop_per_page', create_function('$cols', "return {$this->get_option('per_page')};"), 9999);
            } else {
                add_filter('loop_shop_per_page', function ($cols) {
                    return $this->get_option('per_page');
                }, 9999);
            }
        }

        add_filter('cron_schedules', array($this, 'cron_schedules'), 10, 1);
        if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
            $this->storage->set_val('apffw_last_search_request', $this->get_request_data());
        }
        if (get_option('apffw_sort_terms_checked', 0)) {
            add_filter('apffw_sort_terms_before_out', array($this, 'apffw_sort_terms_is_checked'), 10, 2);
        }

        add_filter('woocommerce_is_filtered', array($this, 'woocommerce_is_filtered'), 20);
        add_action('woocommerce_product_query', array($this, 'woocommerce_parse_query'));
        add_filter('woocommerce_locate_template', array($this, 'apffw_overide_template'), 99, 3);
        add_filter('wc_get_template_part', array($this, 'apffw_overide_template'), 99, 3);
        add_filter('woocommerce_shortcode_products_query', array($this, 'woocommerce_shortcode_products_query'), 99, 3);
        $this->activate_woo_shortcodes();
        add_filter('apffw_sort_terms_before_out', array($this, "sort_terms_before_out"), 5, 2);
        add_filter('apffw_main_query_tax_relations', array($this, 'change_query_tax_relations'), 5, 1);
        add_filter('woopt_get_query_args', array($this, 'woopt_set_query_args'), 5, 1);
    }

    public function admin_init() {
        include_once APFFW_PATH . 'classes/alert.php';
        (new APFFW_ADV())->init();
    }

    public function admin_enqueue_scripts() {
        if (isset($_GET['tab']) AND $_GET['tab'] == 'apffw') {

            APFFW_HELPER::hide_admin_notices();

            wp_enqueue_style('open_sans_font', 'https://fonts.googleapis.com/css?family=Open+Sans');
            wp_enqueue_style('apffw', APFFW_LINK . 'css/plugin_options.css', array(), APFFW_VERSION);

            wp_enqueue_style('apffw_fontello', APFFW_LINK . 'css/fontello.css', array(), APFFW_VERSION);
            wp_enqueue_script('SimpleAjaxUploader', APFFW_LINK . 'lib/simple-ajax-uploader/SimpleAjaxUploader.js', array(), APFFW_VERSION);
            wp_enqueue_script('SimpleAjaxUploader-action', APFFW_LINK . 'lib/simple-ajax-uploader/action.js', array(), APFFW_VERSION);
        }
    }

    public function apffw_save_options() {

        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        

        $data = array();
        parse_str(sanitize_post($_REQUEST['formdata']), $data);

        if (true) {
            if (isset($data['apffw_settings'])) {
				if(!isset($data['_wpnonce_apffw']) || !wp_verify_nonce( $data['_wpnonce_apffw'], 'apffw_save_option')){
					return;
				}
                $_POST = $data;
                WC_Admin_Settings::save_fields($this->get_options());
                
                if (class_exists('SitePress') OR class_exists("Polylang")) {
                    if (class_exists('SitePress')) {
                        $lang = ICL_LANGUAGE_CODE;
                    }
                    if (class_exists('Polylang')) {
                        $lang = get_locale();
                    }
                    if (isset($data['apffw_settings']['wpml_tax_labels']) AND!empty($data['apffw_settings']['wpml_tax_labels'])) {
                        $translations_string = $data['apffw_settings']['wpml_tax_labels'];
                        $translations_string = explode(PHP_EOL, $translations_string);
                        $translations = array();
                        if (!empty($translations_string) AND is_array($translations_string)) {
                            foreach ($translations_string as $line) {
                                if (empty($line)) {
                                    continue;
                                }

                                $line = explode(':', $line);
                                if (!isset($translations[$line[0]])) {
                                    $translations[$line[0]] = array();
                                }
                                $tmp = explode('^', $line[1]);
                                $translations[$line[0]][$tmp[0]] = $tmp[1];
                            }
                        }

                        $data['apffw_settings']['wpml_tax_labels'] = $translations;
                    }
                }
				
				$data['apffw_settings'] = APFFW_HELPER::sanitize_array($data['apffw_settings']);

                
                if (is_array($data['apffw_settings'])) {
                    $data['apffw_settings']['default_overlay_skin_word'] = APFFW_HELPER::escape($data['apffw_settings']['default_overlay_skin_word']);
                    update_option('apffw_settings', $data['apffw_settings']);
                }
                wp_cache_flush();
            }
        }

        die('done');
    }

    public function print_plugin_options() {

        wp_enqueue_script('media-upload');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_script('apffw', APFFW_LINK . 'js/plugin_options.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), APFFW_VERSION);

        $is_custom_extensions = false;
        if (is_dir($this->get_custom_ext_path())) {
            $dir_writable = is_writable($this->get_custom_ext_path());
            if ($dir_writable) {
                $is_custom_extensions = true;
            }
        }
        $add_ext_url = sprintf("%s?action=apffw_upload_ext&extnonce=%s", admin_url('admin-ajax.php'), wp_create_nonce('add-ext-nonce'));
        ob_start();
        ?>
        var apffw_save_link = "<?php _e(admin_url('admin.php?page=wc-settings&tab=apffw&settings_saved=1'));?>";
        var apffw_lang_saving = "<?php esc_html_e('APFFW settings saving ...', 'apffw-products-filter')?>";
        var apffw_abspath = "<?php _e(ABSPATH);?>";
        var apffw_ext_path = "<?php _e($this->get_custom_ext_path());?>";
        var apffw_ext_url = "<?php _e($add_ext_url);?>";
        var apffw_ext_custom = "<?php _e($is_custom_extensions);?>";
        var apffw_is_free_ver = "<?php _e($this->is_free_ver);?>";
        <?php
        $stxt = ob_get_clean();
        wp_add_inline_script('apffw', $stxt, 'before');
        
        if (apply_filters('apffw_init_archive_by_default', true)) {
            global $wpdb;
            $data_sql = array(
                array(
                    'type' => 'int',
                    'val' => 1,
                )
            );
            $wpdb->query(APFFW_HELPER::apffw_prepare("UPDATE {$wpdb->prefix}woocommerce_attribute_taxonomies SET attribute_public=%d", $data_sql));
            flush_rewrite_rules();
            delete_transient('wc_attribute_taxonomies');
        }
        

        $args = array(
            "apffw_settings" => $this->settings,
            "extensions" => $this->get_ext_directories()
        );

        _e($this->render_html(APFFW_PATH . 'views/plugin_options.php', $args));
    }

    public function enqueue_scripts_styles() {
        if (isset($this->settings['custom_front_css']) AND!empty($this->settings['custom_front_css'])) {
            wp_enqueue_style('apffw', $this->settings['custom_front_css']);
        } else {
            wp_enqueue_style('apffw', APFFW_LINK . 'css/front.css', array(), APFFW_VERSION);
        }

        $css_data = "";

        $btn_url = '';
        if (isset($this->settings['apffw_auto_hide_button_img']) AND!empty($this->settings['apffw_auto_hide_button_img'])) {
            $btn_url = $this->settings['apffw_auto_hide_button_img'];
        }
        $css_data .= PHP_EOL . ".apffw_products_top_panel li span, .apffw_products_top_panel2 li span{"
                . "background: url(" . $this->settings['delete_image'] . ");"
                . "background-size: 14px 14px;"
                . "background-repeat: no-repeat;"
                . "background-position: right;"
                . "}";

        if ($btn_url != 'none' && $btn_url) {

            $css_data .= PHP_EOL . ".apffw_show_auto_form,.apffw_hide_auto_form{ background-image: url('$btn_url'); }";
        } elseif ($btn_url == 'none') {
            $css_data .= PHP_EOL . ".apffw_show_auto_form,.apffw_hide_auto_form{ background-image: none ;}";
        }

        if (isset($this->settings['overlay_skin_bg_img'])) {
            if (!empty($this->settings['overlay_skin_bg_img'])) {
                $css_data .= PHP_EOL . ".plainoverlay {
                        background-image: url('" . $this->settings['overlay_skin_bg_img'] . "');
                    }";
            }
        }

        if (isset($this->settings['plainoverlay_color'])) {
            if (!empty($this->settings['plainoverlay_color'])) {
                $css_data .= PHP_EOL . ".jQuery-plainOverlay-progress {
                        border-top: 12px solid " . $this->settings['plainoverlay_color'] . " !important;
                    }";
            }
        }

        if (isset($this->settings['apffw_auto_subcats_plus_img'])) {
            if (!empty($this->settings['apffw_auto_subcats_plus_img'])) {
                $css_data .= PHP_EOL . ".apffw_childs_list_opener span.apffw_is_closed{
                        background: url(" . $this->settings['apffw_auto_subcats_plus_img'] . ");
                    }";
            }
        }

        if (isset($this->settings['apffw_auto_subcats_minus_img'])) {
            if (!empty($this->settings['apffw_auto_subcats_minus_img'])) {
                $css_data .= PHP_EOL . ".apffw_childs_list_opener span.apffw_is_opened{
                        background: url(" . $this->settings['apffw_auto_subcats_minus_img'] . ");
                    }";
            }
        }
        if (!current_user_can('create_users')) {
            $css_data .= PHP_EOL . ".apffw_edit_view{
                    display: none;
                }";
        }

        $show_price_search_button = 0;
        if (isset($this->settings['by_price']['show_button'])) {
            $show_price_search_button = (int) $this->settings['by_price']['show_button'];
        }
        if (isset($this->settings['by_price']['show']) AND (int) $this->settings['by_price']['show'] == 1) {
            if (!$show_price_search_button == 1) {
                $css_data .= PHP_EOL . ".apffw_price_search_container .price_slider_amount button.button{
                        display: none;
                    }";
            }
        }

        if (isset($this->settings['custom_css_code'])) {
            $css_data .= PHP_EOL . stripcslashes($this->settings['custom_css_code']);
        }

        if (!empty(APFFW_EXT::$includes['css_code_custom'])) {
            foreach (APFFW_EXT::$includes['css_code_custom'] as $css_key_code => $css_code) {
                $css_data .= PHP_EOL . $css_code;
            }
        }
        if ($css_data) {
            wp_add_inline_style('apffw', $css_data);
        }
        


        if ($this->is_apffw_use_chosen()) {
            wp_enqueue_style('chosen-drop-down', APFFW_LINK . 'js/chosen/chosen.min.css', array(), APFFW_VERSION);
        }

        if (isset($this->settings['overlay_skin']) AND $this->settings['overlay_skin'] != 'default') {
            wp_enqueue_style('plainoverlay', APFFW_LINK . 'css/plainoverlay.css', array(), APFFW_VERSION);
        }

        if ($this->get_option('use_beauty_scroll', 0)) {            
        }

        $icheck_skin = 'none';
        if (isset($this->settings['icheck_skin'])) {
            $icheck_skin = $this->settings['icheck_skin'];
        }
        if ($icheck_skin != 'none') {
            if (!$icheck_skin) {
                $icheck_skin = 'square_green';
            }

            if ($icheck_skin != 'none') {
                $icheck_skin = explode('_', $icheck_skin);
                wp_enqueue_style('icheck-jquery-color', APFFW_LINK . 'js/icheck/skins/' . $icheck_skin[0] . '/' . $icheck_skin[1] . '.css', array(), APFFW_VERSION);
            }
        }
        if (!empty(APFFW_EXT::$includes['css'])) {
            foreach (APFFW_EXT::$includes['css'] as $css_key => $css_link) {
                wp_enqueue_style($css_key, $css_link, array(), APFFW_VERSION);
            }
        }
    }

    public function body_class($classes) {
        if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
            $classes[] = 'apffw_search_is_going';
        }

        return $classes;
    }

    public function woocommerce_is_filtered($is_filtered) {
        if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
            $is_filtered = true;
        }

        return $is_filtered;
    }

    public function widgets_init() {
        if ($this->is_should_init()) {
            register_widget('APFFW_Widget');
        } else {
            $this->is_activated = false;
        }
    }

    public function change_woo_att_data($taxonomy_data) {
        $taxonomy_data['query_var'] = true;
        return $taxonomy_data;
    }

    public function sidebars_widgets($sidebars_widgets) {
        $price_filter = 0;
        if (isset($this->settings['by_price']['show'])) {
            $price_filter = (int) $this->settings['by_price']['show'];
        }

        if ($price_filter) {
            $sidebars_widgets['sidebar-apffw'] = array('woocommerce_price_filter');
        }

        return $sidebars_widgets;
    }

    public function cron_schedules($schedules) {
        for ($i = 2; $i <= 7; $i++) {
            $schedules['days' . $i] = array(
                'interval' => $i * DAY_IN_SECONDS,
                'display' => sprintf(esc_html__("each %s days", 'apffw-products-filter'), $i)
            );
        }

        return (array) $schedules;
    }

    public function plugin_action_links($links) {
        $buttons = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=apffw') . '">' . esc_html__('Settings', 'apffw-products-filter') . '</a>'
        );

      

        return array_merge($buttons, $links);
    }

    public function get_sapffw_search_slug() {
        $slug = 'sapffw';

        if (isset($this->settings['sapffw_search_slug']) AND!empty($this->settings['sapffw_search_slug'])) {
            $slug = $this->settings['sapffw_search_slug'];
        }

        return $slug;
    }

    public function woocommerce_product_query($q) {
        
        $meta_query = $q->get('meta_query');        
        if (!empty(APFFW_EXT::$includes['html_type_objects'])) {
            foreach (APFFW_EXT::$includes['html_type_objects'] as $obj) {
                if (method_exists($obj, 'assemble_query_params')) {
                    $q->set('meta_query', $obj->assemble_query_params($meta_query, $q));
                }
            }
        }


        return $q;
    }

    function woocommerce_parse_query($q) {
        $meta_query = $q->get('meta_query');
        $meta_query = apply_filters('apffw_get_meta_query', $meta_query);

        $q->set('meta_query', $meta_query);

        $tax_query = $q->get('tax_query');
        $tax_query = $this->parse_tax_query($tax_query);

        $q->set('tax_query', $tax_query); 
    }

    public function parse_query($wp_query) {
        $_REQUEST['apffw_parse_query'] = 1;

        if (!defined('DOING_AJAX')) {
            if (isset($_REQUEST['apffw_products_doing'])) {
                return $wp_query;
            }
        }

        $request = $this->get_request_data();

        
        if ($wp_query->is_main_query()) {
            if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {

                if (!isset($wp_query->query['post_type']) OR $wp_query->query['post_type'] != 'product') {
                    global $wp;
                    if (home_url($wp->request) != home_url()) {                        
                    }
                }

                if (!empty($wp_query->tax_query) AND isset($wp_query->tax_query->queries)) {

                    $tax_relations = apply_filters('apffw_main_query_tax_relations', array());

                    if (!empty($tax_relations)) {

                        $tax_query = $wp_query->tax_query->queries;
                        foreach ($tax_query as $key => $value) {
                            if (in_array($value['taxonomy'], array_keys($tax_relations))) {
                                if (count($tax_query[$key]['terms'])) {
                                    $tax_query[$key]['operator'] = $tax_relations[$value['taxonomy']];
                                    $tax_query[$key]['include_children'] = 0;
                                }
                            }
                        }

                        if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=')) {
                            $this->product_visibility_for_parse_query();
                        }

                        $wp_query->set('tax_query', $tax_query);
                    }
                }

                
                $disable_sapffw_influence = false;
                if (isset($this->settings['disable_sapffw_influence'])) {
                    $disable_sapffw_influence = (bool) $this->settings['disable_sapffw_influence'];
                }
                $is_divi = false;

                if (!$disable_sapffw_influence) {
                    if (!is_page()) {
                        $wp_query->set('post_type', 'product');
                        if (!$is_divi) {
                            $wp_query->is_post_type_archive = true;
                        }
                    }
                    if ($is_divi) {
                        if (!isset($_GET['really_curr_tax'])) {
                            $wp_query->is_tax = false;
                            $wp_query->is_tag = false;
                        }
                    } else {
                        $wp_query->is_tax = false;
                        $wp_query->is_tag = false;
                    }

                    $wp_query->is_home = false;
                    $wp_query->is_single = false;
                    $wp_query->is_posts_page = false;
                    $wp_query->is_search = false;
                }

                $meta_query = array();
                if (isset($wp_query->query_vars['meta_query'])) {
                    $meta_query = $wp_query->query_vars['meta_query'];
                }

            
                if (!empty(APFFW_EXT::$includes['html_type_objects'])) {
                    foreach (APFFW_EXT::$includes['html_type_objects'] as $obj) {
                        if (method_exists($obj, 'assemble_query_params')) {
                            if (is_page()) {
                                if (!isset($_REQUEST['apffw_products_doing'])) {

                                    $wp_query->set('meta_query', $meta_query);
                                    return $wp_query;
                                }
                            }
                            $obj->assemble_query_params($meta_query, $wp_query);
                        }
                    }
                }

                if (version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
                    $meta_query = $this->listen_catalog_visibility($meta_query, true);
                }
                
                $wp_query->set('meta_query', $meta_query);
                if (class_exists('Woocommerce_Products_Per_Page')) {
                    $wp_query->set('posts_per_page', $this->get_wppp_per_page());
                }
            }
        }

        return $wp_query;
    }

    private function assemble_price_params(&$meta_query) {
        $request = $this->get_request_data();
        if (isset($request['min_price']) AND isset($request['max_price'])) {

       

            if (wc_tax_enabled() && 'incl' === get_option('woocommerce_tax_display_shop') && !wc_prices_include_tax()) {
                $tax_classes = array_merge(array(''), WC_Tax::get_tax_classes());
                $class_min = $request['min_price'];
                foreach ($tax_classes as $tax_class) {
                    if ($tax_rates = WC_Tax::get_rates($tax_class)) {
                        $class_min = $request['min_price'] - WC_Tax::get_tax_total(WC_Tax::calc_exclusive_tax($request['min_price'], $tax_rates));
                    }
                }
                $request['min_price'] = $class_min;
            }
            if ($request['min_price'] <= $request['max_price']) {
                $meta_query[] = array(
                    'key' => '_price',
                    'value' => array(floatval($request['min_price']), floatval($request['max_price'])),
                    'type' => 'DECIMAL',
                    'compare' => 'BETWEEN'
                );
            }
        }

        return $meta_query;
    }

    public function woocommerce_settings_tabs_array($tabs) {
        $tabs['apffw'] = esc_html__('Advanced Products Filter', 'apffw-products-filter');
        return $tabs;
    }

    public function wp_head() {
        if (!defined('DOING_AJAX') AND!is_page()) {
            global $wp_query;
            $queried_obj = get_queried_object();
            if (isset($wp_query->query_vars['taxonomy']) AND is_object($queried_obj) AND get_class(get_queried_object()) == 'WP_Term' AND!isset($request_data['really_curr_tax'])) {

                if (is_object($queried_obj)) {
                    $this->set_really_current_term($queried_obj);
                }
            } elseif (isset($request_data['really_curr_tax'])) {
                $tmp = explode('-', $request_data['really_curr_tax'], 2);
                $res = get_term($tmp[0], $tmp[1]);
                $this->set_really_current_term($res);
            } else {
                $this->set_really_current_term();
            }
        } else {
            if ($this->is_really_current_term_exists()) {
                $this->set_really_current_term();
            }
        }
    }

    public function wp_load_js() {
        global $is_edge, $is_gecko;

        global $wp_query;
        
        ob_start();
        ?>
        var apffw_is_permalink =<?php _e(intval((bool) $this->is_permalink_activated()));?>;
        var apffw_shop_page = "";
        <?php if (!$this->is_permalink_activated()): ?>
            apffw_shop_page = "<?php _e(home_url('/?post_type=product'));?>";
        <?php endif; ?>
        var apffw_m_b_container ="<?php _e(apply_filters('apffw_mobile_btn_place_container', '.woocommerce-products-header'));?>";
        var apffw_really_curr_tax = {};
        var apffw_current_page_link = location.protocol + '//' + location.host + location.pathname;
        apffw_current_page_link = apffw_current_page_link.replace(/\page\/[0-9]+/, "");
        <?php
        if (!isset($wp_query->query_vars['taxonomy'])) {
            $page_id = get_option('woocommerce_shop_page_id');
            if ($page_id > 0) {
                if (!$this->is_permalink_activated()) {
                    $link = home_url('/?post_type=product');
                } else {
                    $link = get_permalink($page_id);
                }
            }

            if (isset($link) AND!empty($link) AND is_string($link)) {
                ?>
                apffw_current_page_link = "<?php _e($link);?>";
                <?php
            }
        }

        ?>
        var apffw_link = '<?php _e(APFFW_LINK);?>';
        <?php
        $curr_tax = $this->get_really_current_term();
        if ($curr_tax && is_object($curr_tax)) {
            ?>
            apffw_really_curr_tax = {term_id:<?php _e(intval($curr_tax->term_id));?>, taxonomy: "<?php _e($curr_tax->taxonomy);?>"};
            <?php
        }
        ?>

        var apffw_ajaxurl = "<?php _e(admin_url('admin-ajax.php'));?>";

        var apffw_lang = {
        'orderby': "<?php esc_html_e('orderby', 'apffw-products-filter') ?>",
        'date': "<?php esc_html_e('date', 'apffw-products-filter') ?>",
        'perpage': "<?php esc_html_e('per page', 'apffw-products-filter') ?>",
        'pricerange': "<?php esc_html_e('price range', 'apffw-products-filter') ?>",
        'menu_order': "<?php esc_html_e('menu order', 'apffw-products-filter') ?>",
        'popularity': "<?php esc_html_e('popularity', 'apffw-products-filter') ?>",
        'rating': "<?php esc_html_e('rating', 'apffw-products-filter') ?>",
        'price': "<?php esc_html_e('price low to high', 'apffw-products-filter') ?>",
        'price-desc': "<?php esc_html_e('price high to low', 'apffw-products-filter') ?>",
        'clear_all': "<?php _e(apply_filters('apffw_clear_all_text', esc_html__('Clear All', 'apffw-products-filter')));?>"
        };

        if (typeof apffw_lang_custom == 'undefined') {
        var apffw_lang_custom = {};
        }

        var apffw_is_mobile = 0;
        <?php if (APFFW_HELPER::is_mobile_device()): ?>
            apffw_is_mobile = 1;
        <?php endif; ?>



        var apffw_show_price_search_button = 0;
        var apffw_show_price_search_type = 0;
        <?php
        $show_price_search_button = 0;
        $show_price_search_type = 0;
        if (isset($this->settings['by_price']['show_button'])) {
            $show_price_search_button = (int) $this->settings['by_price']['show_button'];
        }

        if (isset($this->settings['by_price']['show'])) {
            $show_price_search_type = (int) $this->settings['by_price']['show'];
        }

        if ($show_price_search_button == 1):
            ?>
            apffw_show_price_search_button = 1;
        <?php endif; ?>

        var apffw_show_price_search_type = <?php _e($show_price_search_type);?>;

        var sapffw_search_slug = "<?php _e($this->get_sapffw_search_slug());?>";

        <?php
        $icheck_skin = 'none';
        if (isset($this->settings['icheck_skin'])) {
            $icheck_skin = $this->settings['icheck_skin'];
        }
        ?>

        var icheck_skin = {};
        <?php if ($icheck_skin != 'none'): ?>
            <?php $icheck_skin = explode('_', $icheck_skin); ?>
            icheck_skin.skin = "<?php _e($icheck_skin[0]);?>";
            icheck_skin.color = "<?php _e($icheck_skin[1]);?>";
            if (window.navigator.msPointerEnabled && navigator.msMaxTouchPoints > 0) {
            /*icheck_skin = 'none';*/
            }
        <?php else: ?>
            icheck_skin = 'none';
        <?php endif; ?>

        var is_apffw_use_chosen =<?php _e(intval($this->is_apffw_use_chosen()));?>;

        <?php $apffw_use_beauty_scroll = $this->get_option('use_beauty_scroll', 0); ?>
        var apffw_current_values = '[]';
        <?php if ($this->get_request_data()) { ?>
            apffw_current_values = '<?php _e(json_encode($this->get_request_data()));?>';
        <?php } ?>
        var apffw_lang_loading = "<?php esc_html_e('Loading ...', 'apffw-products-filter') ?>";

        <?php if (isset($this->settings['default_overlay_skin_word']) AND!empty($this->settings['default_overlay_skin_word'])): ?>
            apffw_lang_loading = "<?php _e(esc_html__($this->settings['default_overlay_skin_word'], 'apffw-products-filter'));?>";
        <?php endif; ?>

        var apffw_lang_show_products_filter = "<?php esc_html_e('show products filter', 'apffw-products-filter') ?>";
        var apffw_lang_hide_products_filter = "<?php esc_html_e('hide products filter', 'apffw-products-filter') ?>";
        var apffw_lang_pricerange = "<?php esc_html_e('price range', 'apffw-products-filter') ?>";

        var apffw_use_beauty_scroll =<?php _e($apffw_use_beauty_scroll);?>;

        var apffw_autosubmit =<?php _e((int) get_option('apffw_autosubmit', 0));?>;
        var apffw_ajaxurl = "<?php _e(admin_url('admin-ajax.php'));?>";
        /*var apffw_submit_link = "";*/
        var apffw_is_ajax = 0;
        var apffw_ajax_redraw = 0;
        var apffw_ajax_page_num =<?php _e((get_query_var('page')) ? get_query_var('page') : 1) ?>;
        var apffw_ajax_first_done = false;
        var apffw_checkboxes_slide_flag = <?php _e(((int) get_option('apffw_checkboxes_slide') == 1 ? 'true' : 'false')); ?>;


        /*toggles*/
        var apffw_toggle_type = "<?php esc_html_e((isset($this->settings['toggle_type']) AND!empty($this->settings['toggle_type'])) ? $this->settings['toggle_type'] : 'text') ?>";

        var apffw_toggle_closed_text = "<?php esc_html_e((isset($this->settings['toggle_closed_text']) AND!empty($this->settings['toggle_closed_text'])) ? trim(APFFW_HELPER::wpml_translate(null, $this->settings['toggle_closed_text'])) : '-') ?>";
        var apffw_toggle_opened_text = "<?php esc_html_e((isset($this->settings['toggle_opened_text']) AND!empty($this->settings['toggle_opened_text'])) ? trim(APFFW_HELPER::wpml_translate(null, $this->settings['toggle_opened_text'])) : '+') ?>";

        var apffw_toggle_closed_image = "<?php esc_html_e((isset($this->settings['toggle_closed_image']) AND!empty($this->settings['toggle_closed_image'])) ? $this->settings['toggle_closed_image'] : APFFW_LINK . 'img/plus3.png') ?>";
        var apffw_toggle_opened_image = "<?php esc_html_e((isset($this->settings['toggle_opened_image']) AND!empty($this->settings['toggle_opened_image'])) ? $this->settings['toggle_opened_image'] : APFFW_LINK . 'img/minus3.png') ?>";


        /*indexes which can be displayed in red buttons panel*/
        <?php
        $taxonomies = $this->get_taxonomies();
        $taxonomies_keys = array_keys($taxonomies);
        if (version_compare(PHP_VERSION, '5.3.0', '<=')) {
            array_walk($taxonomies_keys, create_function('&$str', '$str = "\"$str\"";'));
        } else {
            array_walk($taxonomies_keys, function (&$str) {
                $str = "\"$str\"";
            });
        }
        $taxonomies_keys = implode(',', $taxonomies_keys);
        $extensions_html_type_indexes = array();

        if (!empty(APFFW_EXT::$includes['html_type_objects'])) {
            foreach (APFFW_EXT::$includes['html_type_objects'] as $obj) {
                if ($obj->index !== NULL) {
                    $extensions_html_type_indexes[] = $obj->index;
                }
            }
        }
        $extensions_html_type_indexes[] = "min_rating";
        if (version_compare(PHP_VERSION, '5.3.0', '<=')) {
            array_walk($extensions_html_type_indexes, create_function('&$str', '$str = "\"$str\"";'));
        } else {
            array_walk($extensions_html_type_indexes, function (&$str) {
                $str = "\"$str\"";
            });
        }


        $extensions_html_type_indexes = implode(',', apply_filters('apffw_extensions_type_index', $extensions_html_type_indexes));
        ?>
        var apffw_accept_array = ["min_price", "orderby", "perpage", <?php _e($extensions_html_type_indexes);?>,<?php _e($taxonomies_keys);?>];

        <?php if (isset($request_data['really_curr_tax'])): ?>
            <?php
            $tmp = explode('-', $request_data['really_curr_tax']);
            ?>
            apffw_really_curr_tax = {term_id:<?php _e(intval($tmp[0]));?>, taxonomy: "<?php _e(APFFW_HELPER::escape($tmp[1]));?>"};
        <?php endif; ?>

        /*for extensions*/

        var apffw_ext_init_functions = null;
        <?php if (!empty(APFFW_EXT::$includes['js_init_functions'])) : ?>
            apffw_ext_init_functions = '<?php _e(json_encode(APFFW_EXT::$includes['js_init_functions']));?>';

        <?php endif; ?>


        <?php
        if ($is_gecko AND (int) get_option('apffw_try_ajax', 0) === 0 AND isset($this->settings['overlay_skin']) AND $this->settings['overlay_skin'] != 'default') {
            $this->settings['overlay_skin'] = 'plainoverlay'; 
        }
        ?>

        var apffw_overlay_skin = "<?php _e(isset($this->settings['overlay_skin']) ? $this->settings['overlay_skin'] : 'default');?>";


        function apffw_js_after_ajax_done() {
        jQuery(document).trigger('apffw_ajax_done');
        <?php _e(isset($this->settings['js_after_ajax_done']) ? stripcslashes($this->settings['js_after_ajax_done']) : '');?>
        }

        <?php
        $stxt = ob_get_clean();

        if (!isset($this->settings['use_tooltip'])) {
            $show_tooltip = 1;
        } else {
            $show_tooltip = $this->settings['use_tooltip'];
        }
        if ($show_tooltip) {
            wp_enqueue_style('apffw_tooltip-css', APFFW_LINK . 'js/tooltip/css/tooltipster.bundle.min.css', array(), APFFW_VERSION);
            wp_enqueue_style('apffw_tooltip-css-noir', APFFW_LINK . 'js/tooltip/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-noir.min.css', array(), APFFW_VERSION);
            wp_enqueue_script('apffw_tooltip-js', APFFW_LINK . 'js/tooltip/js/tooltipster.bundle.min.js', array('jquery'), APFFW_VERSION);
        }
        if ($icheck_skin != 'none') {
            wp_enqueue_script('icheck-jquery', APFFW_LINK . 'js/icheck/icheck.min.js', array('jquery'), APFFW_VERSION);
        }
        if (isset($this->settings['optimize_js_files']) AND $this->settings['optimize_js_files']) {
            wp_enqueue_script('apffw_front', APFFW_LINK . 'js/front_comprssd.js', array('jquery'), APFFW_VERSION);
        } else {
            wp_enqueue_script('apffw_front', APFFW_LINK . 'js/front.js', array('jquery'), APFFW_VERSION);
            wp_enqueue_script('apffw_radio_html_items', APFFW_LINK . 'js/html_types/radio.js', array('jquery'), APFFW_VERSION);
            wp_enqueue_script('apffw_checkbox_html_items', APFFW_LINK . 'js/html_types/checkbox.js', array('jquery'), APFFW_VERSION);
            wp_enqueue_script('apffw_select_html_items', APFFW_LINK . 'js/html_types/select.js', array('jquery'), APFFW_VERSION);
            wp_enqueue_script('apffw_mselect_html_items', APFFW_LINK . 'js/html_types/mselect.js', array('jquery'), APFFW_VERSION);
        }
        wp_add_inline_script('apffw_front', $stxt, 'before');
        wp_localize_script('apffw_front', 'apffw_filter_titles', $this->get_all_filter_titles());
        $text_data = array();
        if (!empty(APFFW_EXT::$includes['js_lang_custom'])) {
            foreach (APFFW_EXT::$includes['js_lang_custom'] as $js_key_lang => $js_text) {
                $text_data[$js_key_lang] = $js_text;
            }
            wp_localize_script('apffw_front', 'apffw_ext_filter_titles', $text_data);
        }
        $js_data = "";
        if (!empty(APFFW_EXT::$includes['js_code_custom'])) {
            foreach (APFFW_EXT::$includes['js_code_custom'] as $js_key_code => $js_code) {
                $js_data .= PHP_EOL . $js_code;
            }
            wp_add_inline_script('apffw_front', $js_data, 'before');
        }

        if (!empty(APFFW_EXT::$includes['js'])) {
            foreach (APFFW_EXT::$includes['js'] as $js_key => $js_link) {
                wp_enqueue_script($js_key, $js_link, array('jquery'), APFFW_VERSION);
            }
        }


        if ($this->is_apffw_use_chosen()) {
            wp_enqueue_script('chosen-drop-down', APFFW_LINK . 'js/chosen/chosen.jquery.js', array('jquery'), APFFW_VERSION);
        }

        if (isset($this->settings['overlay_skin']) AND $this->settings['overlay_skin'] != 'default') {
            wp_enqueue_script('plainoverlay', APFFW_LINK . 'js/plainoverlay/jquery.plainoverlay.min.js', array('jquery'), APFFW_VERSION);
        }

        if ($apffw_use_beauty_scroll) {
            /* removed */
        }

        $price_filter = 0;
        if (isset($this->settings['by_price']['show'])) {
            $price_filter = (int) $this->settings['by_price']['show'];
        }

        if ($price_filter == 1) {
            wp_enqueue_script('jquery-ui-core', array('jquery'));
            wp_enqueue_script('jquery-ui-slider', array('jquery-ui-core'));
            wp_enqueue_script('wc-jquery-ui-touchpunch', array('jquery-ui-core', 'jquery-ui-slider'));
            wp_enqueue_script('wc-price-slider', array('jquery-ui-slider', 'wc-jquery-ui-touchpunch'));
        }
    }

    public function get_all_filter_titles() {
        $options = array();
        $items_order = array();
        $taxonomies = $this->get_taxonomies();
        $taxonomies_keys = array_keys($taxonomies);
        if (isset($this->settings['items_order']) AND!empty($this->settings['items_order'])) {
            $items_order = explode(',', $this->settings['items_order']);
        } else {
            $items_order = array_merge($this->items_keys, $taxonomies_keys);
        }

        foreach (array_merge($this->items_keys, $taxonomies_keys) as $key) {
            if (!in_array($key, $items_order)) {
                $items_order[] = $key;
            }
        }
        
        foreach ($items_order as $key) {
            if (in_array($key, $this->items_keys)) {
                if (isset($this->settings['meta_filter']) AND isset($this->settings['meta_filter'][$key])) {
                    if (isset($this->settings[$key]['show']) && $this->settings[$key]['show'] != 0) {
                        $options[$key] = $this->settings['meta_filter'][$key]['title'];

                        if (in_array($this->settings['meta_filter'][$key]["search_view"], array('select', 'mselect'))) {
                            $options[$this->settings['meta_filter'][$key]["search_view"] . "_" . $key] = $this->settings['meta_filter'][$key]['title'];
                        }
                    }
                } else {
                    if (isset($this->settings[$key]['show']) && $this->settings[$key]['show'] != 0) {
                        $options[$key] = $key;
                    }
                }
            } else {
                if (isset($taxonomies[$key])) {
                    if (isset($this->settings['tax'][$key]) && $this->settings['tax'][$key] != 0) {

                        if (isset($this->settings["custom_tax_label"][$key]) AND $this->settings["custom_tax_label"][$key]) {
                            $title = $this->settings["custom_tax_label"][$key];

                            if ( isset($this->settings["tax_type"]) && $this->settings["tax_type"][$key] == 'select_hierarchy') {
                                $tmp_title = explode("^", $title);
                                if (isset($tmp_title[1]) && $tmp_title[1]) {
                                    $title = $tmp_title[1];
                                } elseif (strrpos($title, "+") !== false) {
                                    $title = $taxonomies[$key]->labels->name;
                                }
                            }

                            $options[$key] = APFFW_HELPER::wpml_translate(null, $title);
                        } else {
                            $options[$key] = APFFW_HELPER::wpml_translate($taxonomies[$key]);
                        }
                        if (isset($this->settings['comparison_logic'][$key]) AND $this->settings['comparison_logic'][$key] == "NOT IN") {
                            $options["rev_" . $key] = $options[$key];
                        }
                    }
                }
            }
        }

        return $options;
    }

    public function wp_footer() {

        if (isset($this->settings['overlay_skin']) AND ( $this->settings['overlay_skin'] != 'default' AND $this->settings['overlay_skin'] != 'plainoverlay')) {
            ?>

            <img  style="display: none;" src="<?php _e(APFFW_LINK);?>img/loading-master/<?php _e($this->settings['overlay_skin']);?>.svg" alt="preloader" />

            <?php
        }
    }

    private function init_settings() {
        $this->settings = get_option('apffw_settings', array());
    }

    public function get_taxonomies() {
        static $taxonomies = array();
        if (empty($taxonomies)) {
            $taxonomies = get_object_taxonomies('product', 'objects');
            unset($taxonomies['product_shipping_class']);
            unset($taxonomies['product_type']);
        }
        return $taxonomies;
    }

    public function get_options() {
        $options = array
            (array(
                'name' => '',
                'type' => 'title',
                'desc' => '',
                'id' => 'apffw_general_settings'
            ),
            array(
                'name' => esc_html__('Set filter automatically', 'apffw-products-filter'),
                'desc' => esc_html__('Set filter automatically on the shop page', 'apffw-products-filter'),
                'id' => 'apffw_set_automatically',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    1 => esc_html__('Yes', 'apffw-products-filter'),
                    2 => esc_html__('Yes, but only for mobile devices', 'apffw-products-filter'),
                    3 => esc_html__('Yes, but only for desktop', 'apffw-products-filter'),
                    0 => esc_html__('No', 'apffw-products-filter'),
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Autosubmit', 'apffw-products-filter'),
                'desc' => esc_html__('Start searching just after changing any of the elements on the search form', 'apffw-products-filter'),
                'id' => 'apffw_autosubmit',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Show count', 'apffw-products-filter'),
                'desc' => esc_html__('Show count of items near taxonomies terms on the front', 'apffw-products-filter'),
                'id' => 'apffw_show_count',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Dynamic recount', 'apffw-products-filter'),
                'desc' => esc_html__('Show count of items near taxonomies terms on the front dynamically. Must be switched on "Show count". In turbo mode if filter is very big better select "Yes, only for PC"', 'apffw-products-filter'),
                'id' => 'apffw_show_count_dynamic',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Hide empty terms', 'apffw-products-filter'),
                'desc' => esc_html__('Hide empty terms in "Dynamic recount" mode', 'apffw-products-filter'),
                'id' => 'apffw_hide_dynamic_empty_pos',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Try to ajaxify the shop', 'apffw-products-filter'),
                'desc' => esc_html__('Select "Yes" if you want to TRY make filtering in your shop by AJAX. Not compatible for 100% of all wp themes, so test it well if you are going to buy premium version of the plugin because incompatibility is not fixable!', 'apffw-products-filter'),
                'id' => 'apffw_try_ajax',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Hide childs in checkboxes and radio', 'apffw-products-filter'),
                'desc' => esc_html__('Hide childs in checkboxes and radio. Near checkbox/radio which has childs will be plus icon to show childs.', 'apffw-products-filter'),
                'id' => 'apffw_checkboxes_slide',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Hide apffw top panel buttons', 'apffw-products-filter'),
                'desc' => esc_html__('Red buttons on the top of the shop page when searching done', 'apffw-products-filter'),
                'id' => 'apffw_hide_red_top_panel',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Lets checked terms will be on the top', 'apffw-products-filter'),
                'desc' => esc_html__('Selected terms will always be displayed on the top (for parent-terms only, child will be on the top but under parent-term as it was)', 'apffw-products-filter'),
                'id' => 'apffw_sort_terms_checked',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Filter button text', 'apffw-products-filter'),
                'desc' => esc_html__('Filter button text in the search form', 'apffw-products-filter'),
                'id' => 'apffw_filter_btn_txt',
                'type' => 'text',
                'class' => 'text',
                'css' => 'min-width:300px;',
                'desc_tip' => true
            ),
            array(
                'name' => esc_html__('Reset button text', 'apffw-products-filter'),
                'desc' => esc_html__('Reset button text in the search form. Write "none" to hide this button on front.', 'apffw-products-filter'),
                'id' => 'apffw_reset_btn_txt',
                'type' => 'text',
                'class' => 'text',
                'css' => 'min-width:300px;',
                'desc_tip' => true
            ),
            array('type' => 'sectionend', 'id' => 'apffw_general_settings')
        );

        return apply_filters('wc_settings_tab_apffw_settings', $options);
    }

    public function dynamic_count($curr_term, $type, $additional_taxes = '', $meta_term = array(), $custom_type = "") {
        $request = $this->get_request_data();
        $_REQUEST['apffw_current_recount'] = $curr_term;
        $opposition_terms = array();
        global $wp_query;
        
        if (!is_array($curr_term)) {
            $curr_term = array();
        }
        if (!isset($curr_term['taxonomy'])) {
            $curr_term['taxonomy'] = "";
        }
        
        if (!empty($additional_taxes)) {
            $opposition_terms = $this->_expand_additional_taxes_string($additional_taxes);
        }
        if (!empty($opposition_terms)) {
            $tmp = array();
            foreach ($opposition_terms as $t) {
                $tmp[$t['taxonomy']] = $t['terms'];
            }
            $opposition_terms = $tmp;
            unset($tmp);
        }

        
        if ($this->is_really_current_term_exists()) {
            $o = $this->get_really_current_term();
            $opposition_terms[$o->taxonomy] = array($o->slug);
        }

        $in_query_terms = array();
        static $product_taxonomies = null;
        if (!$product_taxonomies) {
            $product_taxonomies = $this->get_taxonomies();
            $product_taxonomies = array_keys($product_taxonomies);
        }

        if (!empty($request) AND is_array($request)) {
            foreach ($request as $tax_slug => $terms_string) {
                $tax_slug_t = $this->uncheck_slug($tax_slug);
                if ($tax_slug_t != $tax_slug) {
                    $request[$tax_slug_t] = $terms_string;
                    unset($request[$tax_slug]);
                    $tax_slug = $tax_slug_t;
                }

                if (in_array($tax_slug, $product_taxonomies)) {
                    $in_query_terms[$tax_slug] = explode(',', $terms_string);
                }
            }
        }

        $term_is_in_query = false;
        if (empty($meta_term)) {
            if ($curr_term AND isset($in_query_terms[$curr_term['taxonomy']]) AND isset($curr_term['slug'])) {
                if (in_array($curr_term['slug'], $in_query_terms[$curr_term['taxonomy']])) {
                    $term_is_in_query = true;
                }
            }
        }

        if ($term_is_in_query) {
            return 0;
        }

        $term_is_in_opposition = false;
        if (empty($meta_term)) {
            if ($curr_term AND isset($opposition_terms[$curr_term['taxonomy']])) {
                if (in_array($curr_term['slug'], $opposition_terms[$curr_term['taxonomy']])) {
                    $term_is_in_opposition = true;
                }
            }
        }
        

        $terms_to_query = array();
        if (empty($meta_term) AND empty($custom_type)) {
            $default_types = array('radio', 'select', 'price2', 'checkbox', 'mselect');
            if (!in_array($type, $default_types)) {
                if (isset(APFFW_EXT::$includes['taxonomy_type_objects'][$type])) {
                    $obj = APFFW_EXT::$includes['taxonomy_type_objects'][$type];
                    $type = $obj->html_type_dynamic_recount_behavior;
                }
            }
            
            switch ($type) {
                case 'single':

                    if (isset($in_query_terms[$curr_term['taxonomy']])) {
                        $in_query_terms[$curr_term['taxonomy']] = array($curr_term['slug']);
                    } else {
                        $terms_to_query[$curr_term['taxonomy']] = array($curr_term['slug']);
                    }


                    break;

                case 'multi':

                    if (isset($in_query_terms[$curr_term['taxonomy']])) {
                        $in_query_terms[$curr_term['taxonomy']] = array($curr_term['slug']);
                    } else {
                        $terms_to_query[$curr_term['taxonomy']][] = $curr_term['slug'];
                    }


                    break;

                default:
                    break;
            }
        }
        

        $taxonomies = array();
        if (!empty($opposition_terms)) {
            foreach ($opposition_terms as $tax_slug => $terms) {
                if (!empty($terms)) {
                    $taxonomies[] = array(
                        'taxonomy' => $tax_slug,
                        'terms' => $terms,
                        'field' => 'slug',
                        'operator' => 'IN',
                        'include_children' => true
                    );
                }
            }
        }


        if (!empty($in_query_terms)) {
            foreach ($in_query_terms as $tax_slug => $terms) {
                if (!empty($terms)) {
                    $logic_arr = array();
                    if (isset($this->settings['comparison_logic'])) {
                        $logic_arr = $this->settings['comparison_logic'];
                    }
                    if (isset($logic_arr[$tax_slug]) AND $logic_arr[$tax_slug] == "AND") {
                        $request = $this->get_request_data();
                        $terms_t = array();
                        if (isset($request[$tax_slug])) {
                            $terms_t = explode(",", $request[$tax_slug]);
                        }
                        $terms = array_merge($terms, $terms_t);
                        $taxonomies[] = array(
                            'taxonomy' => $tax_slug,
                            'terms' => $terms,
                            'field' => 'slug',
                            "operator" => "AND",
                            "include_children" => false
                        );
                    } elseif (isset($logic_arr[$tax_slug]) AND $logic_arr[$tax_slug] == "NOT IN" AND (!$curr_term OR $curr_term['taxonomy'] != $tax_slug)) {

                        $taxonomies[] = array(
                            'taxonomy' => $tax_slug,
                            'terms' => $terms,
                            'field' => 'slug',
                            "operator" => "NOT IN",
                            "include_children" => false
                        );
                    } else {
                        $taxonomies[] = array(
                            'taxonomy' => $tax_slug,
                            'terms' => $terms,
                            'field' => 'slug',
                            'operator' => 'IN',
                            'include_children' => 1
                        );
                    }
                }
            }
        }

        if (!empty($terms_to_query)) {
            foreach ($terms_to_query as $tax_slug => $terms) {
                if (!empty($terms)) {
                    $taxonomies[] = array(
                        'taxonomy' => $tax_slug,
                        'terms' => $terms,
                        'field' => 'slug',
                        'operator' => 'IN',
                        'include_children' => 1
                    );
                }
            }
        }

        

        if (!empty($taxonomies)) {
            $taxonomies['relation'] = 'AND';
        }
        
        $args = array(
            'nopaging' => true,
            'fields' => 'ids',
            'post_type' => 'product',
            'post_status' => 'publish'
        );

        $args['tax_query'] = $taxonomies;
        $args['meta_query'] = array();

        if ($this->is_isset_in_request_data('min_price') AND $this->is_isset_in_request_data('max_price')) {
            $this->assemble_price_params($args['meta_query']);
            $args['meta_query']['relation'] = 'AND';
        }
        
        if ($this->is_isset_in_request_data('min_rating')) {
            $min = $request['min_rating'];
            if ($min == 4) {
                $max = 10;
            } else {
                $max = $min + 1 - 0.001;
            }
            $args['meta_query'][] = array(
                'key' => '_wc_average_rating',
                'value' => array($min, $max),
                'type' => 'DECIMAL',
                'compare' => 'BETWEEN'
            );
            $args['meta_query']['relation'] = 'AND';
        }
        
        $args['meta_query'] = apply_filters('apffw_get_meta_query', $args['meta_query']);
        if (!empty($meta_term) AND empty($custom_type)) {
            switch ($type) {
                case 'select':
                case 'mselect':
                    if (!isset($meta_term['relation'])) {
                        $meta_term['relation'] = "OR";
                    }

                    if ($meta_term['relation'] == "OR") {

                        APFFW_HELPER::recursiveRemoval($args['meta_query'], $meta_term['key']); 
                    }

                    $args['meta_query'][] = array(
                        'key' => $meta_term['key'],
                        'value' => $meta_term['value'],
                        'compare' => '='
                    );
                    break;
                case 'checkbox_ex':
                    $args['meta_query'][] = $meta = array(
                        'key' => $meta_term['key'],
                        'compare' => 'EXISTS'
                    );
                    break;
                case 'checkbox':
                    $args['meta_query'][] = $meta = array(
                        'key' => $meta_term['key'],
                        'compare' => 'EXISTS'
                    );
                    break;
                case 'slider':
                    $args['meta_query'][] = $meta = array(
                        'key' => $meta_term['key'],
                        'value' => $meta_term['value'],
                        'type' => 'numeric',
                        'compare' => 'BETWEEN',
                    );
                    break;
                default:
                    
                    break;
            }
        }
        if (class_exists('SitePress')) {
            $args['lang'] = ICL_LANGUAGE_CODE;
        }
        
        $atts = array();
        if (!isset($args['meta_query'])) {
            $args['meta_query'] = array();
        }

        if (!empty(APFFW_EXT::$includes['html_type_objects'])) {
            foreach (APFFW_EXT::$includes['html_type_objects'] as $obj) {
                if (method_exists($obj, 'assemble_query_params')) {
                    $obj->assemble_query_params($args['meta_query'], $args);
                }
            }
        }

        
        $_REQUEST['apffw_dyn_recount_going'] = 1;
        remove_filter('posts_clauses', array(WC()->query, 'order_by_popularity_post_clauses'));
        remove_filter('posts_clauses', array(WC()->query, 'order_by_rating_post_clauses'));

        if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=')) {
            if ($this->get_option('listen_catalog_visibility')) {
                $args['tax_query'] = $this->product_visibility_not_in($args['tax_query'], $this->generate_visibility_keys(true));
            }
        } elseif (version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
            if ($this->get_option('listen_catalog_visibility')) {
                $args['meta_query'][] = array(
                    'key' => '_visibility',
                    'value' => array('search', 'visible'),
                    'compare' => 'IN'
                );
            }
        }

        
        $args = apply_filters('apffw_dynamic_count_attr', $args, $custom_type);
        $query = new APFFW_QueryCounter($args);
        unset($_REQUEST['apffw_current_recount']);
        unset($_REQUEST['apffw_dyn_recount_going']);
        return $query->found_posts;
    }

    public function woocommerce_shortcode_products_query($query_args, $attr, $type = "") {
        if (isset($_REQUEST['override_no_products']) AND $_REQUEST['override_no_products']) {
            return $query_args;
        }
        if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
            $_REQUEST['apffw_products_doing'] = 1;
            $query_args['tax_query'] = array_merge($query_args['tax_query'], $this->get_tax_query(''));
            $query_args['meta_query'] = array_merge($query_args['meta_query'], $this->get_meta_query());

            $tax_relations = apply_filters('apffw_main_query_tax_relations', array());
            if (!empty($tax_relations)) {
                $tax_query = $query_args['tax_query'];
                foreach ($tax_query as $key => $value) {
                    if (isset($value['taxonomy'])) {
                        if (in_array($value['taxonomy'], array_keys($tax_relations))) {
                            if (count($tax_query[$key]['terms'])) {
                                $tax_query[$key]['operator'] = $tax_relations[$value['taxonomy']];
                                $tax_query[$key]['include_children'] = 0;
                            }
                        }
                    }
                }
                $query_args['tax_query'] = $tax_query;
            }

            $query_args = apply_filters('apffw_products_query', $query_args);

            if (isset($_GET['paged'])) {
                $query_args['paged'] = intval($_GET['paged']);
            }
            
            if (isset($_GET['orderby'])) {
                $ordering_args = WC()->query->get_catalog_ordering_args();
            } else {
                $ordering_args = WC()->query->get_catalog_ordering_args($query_args['orderby'], $query_args['order']);
            }
            $query_args['orderby'] = $ordering_args['orderby'];
            $query_args['order'] = $ordering_args['order'];
            if ($ordering_args['meta_key']) {
                $query_args['meta_key'] = $ordering_args['meta_key'];
            }
        }


        return $query_args;
    }

    public function is_apffw_use_chosen() {
        $is = $this->get_option('use_chosen', 1);
        $is = apply_filters('apffw_use_chosen', $is);
        return $is;
    }

    public function woocommerce_before_shop_loop() {

        $apffw_set_automatically = 0;
        
        $mobile_behavior = intval(get_option('apffw_set_automatically', 0));
        if (($mobile_behavior == 1) OR ( $mobile_behavior == 2 AND wp_is_mobile()) OR ( $mobile_behavior == 3 AND!wp_is_mobile())) {
            $apffw_set_automatically = 1;
        }
        
        if ($apffw_set_automatically === 1 AND!isset($_REQUEST['apffw_before_shop_loop_done'])) {
            $_REQUEST['apffw_before_shop_loop_done'] = true;
            $shortcode_hide = false;
            if (isset($this->settings['apffw_auto_hide_button'])) {
                $shortcode_hide = intval($this->settings['apffw_auto_hide_button']);
            }

            $price_filter = 0;
            if (isset($this->settings['by_price']['show'])) {
                $price_filter = (int) $this->settings['by_price']['show'];
            }
            $shortcode_id = "auto_shortcode";
            if (isset($this->settings['apffw_auto_filter_skins']) AND $this->settings['apffw_auto_filter_skins']) {
                $shortcode_id = $this->settings['apffw_auto_filter_skins'];
            }

            _e(do_shortcode('[apffw sid="' . $shortcode_id . '" autohide=' . $shortcode_hide . ' price_filter=' . $price_filter . ']'));
        }
        ?>



        <?php
        $is_wc_shortcode = false;
        if (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {
            $is_wc_shortcode = wc_get_loop_prop('is_shortcode');
        }
        
        if (get_option('apffw_try_ajax', 0) AND!isset($_REQUEST['apffw_products_doing'])AND!$is_wc_shortcode) {
            _e('<div class="woocommerce woocommerce-page apffw_shortcode_output">');
            $shortcode_txt = "apffw_products is_ajax=1";
            if ($this->is_really_current_term_exists()) {
                $o = $this->get_really_current_term();
                $shortcode_txt = "apffw_products taxonomies={$o->taxonomy}:{$o->term_id} is_ajax=1 predict_ids_and_continue=1";
                $_REQUEST['APFFW_IS_TAX_PAGE'] = $o->taxonomy;
            }
            _e('<div id="apffw_results_by_ajax" data-shortcode="' . $shortcode_txt . '">');
        }

        if (get_option('apffw_hide_red_top_panel', 0) == 0) {
            _e(do_shortcode('[apffw_search_options]'));
        }
    }

    public function woocommerce_after_shop_loop() {
        $is_wc_shortcode = false;
        if (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {
            $is_wc_shortcode = wc_get_loop_prop('is_shortcode');
        }
        if (get_option('apffw_try_ajax', 0) AND!isset($_REQUEST['apffw_products_doing'])AND!$is_wc_shortcode) {
            _e('</div>');
            _e('</div>');
        }
    }

    public function get_request_data($apply_filters = true) {
        $data = $_GET;
        $apffw_text_urlencode = apply_filters('apffw_text_urlencode', 0);
        if (isset($data['gclid'])) {
            unset($data['gclid']);
        }
        
        if (!empty($data) AND is_array($data)) {
            $tmp = array();
            foreach ($data as $key => $value) {
                if (!is_string($key) OR!is_string($value)) {
                    continue;
                }
                if ($apffw_text_urlencode) {
                    $tmp[APFFW_HELPER::escape($key)] = urlencode(APFFW_HELPER::escape($value));
                } else {
                    $tmp[APFFW_HELPER::escape($key)] = APFFW_HELPER::escape($value);
                }
            }
            $data = $tmp;
        }


        if ($apply_filters) {
            $data = apply_filters('apffw_get_request_data', $data);
        }

        return $data;
    }

    public function is_isset_in_request_data($key, $apply_filters = true) {
        $request = $this->get_request_data($apply_filters);
        return isset($request[$key]);
    }

    public function get_catalog_orderby($orderby = '', $order = 'ASC') {
        if (empty($orderby) OR $orderby == 'no') {
            $orderby = get_option('woocommerce_default_catalog_orderby');
        }
        $meta_key = '';
        global $wpdb;
        switch ($orderby) {
            case 'price-desc':
                $orderby = "meta_value_num {$wpdb->posts}.ID";
                $order = 'DESC';
                $meta_key = '_price';
                break;
            case 'price':
                $orderby = "meta_value_num {$wpdb->posts}.ID";
                $order = 'ASC';
                $meta_key = '_price';
                break;
            case 'popularity' :
                add_filter('posts_clauses', array(WC()->query, 'order_by_popularity_post_clauses'));
                $meta_key = 'total_sales';
                break;
            case 'rating' :
                $orderby = "meta_value_num {$wpdb->posts}.ID";
                $order = 'DESC';
                $meta_key = '_wc_average_rating';
                break;
            case 'title' :
                $orderby = 'title';
                break;
            case 'title-desc':
                $orderby = "title";
                $order = 'DESC';
                break;
            case 'title-asc':
                $orderby = "title";
                $order = 'ASC';
                break;
            case 'rand' :
                $orderby = 'rand';
                break;
            case 'date' :
                $order = 'DESC';
                $orderby = 'date';
                break;
            default:
                $orderby = 'menu_order title';
                break;
        }

        return apply_filters('apffw_order_catalog', compact('order', 'orderby', 'meta_key'));
    }

    public function get_tax_query($additional_taxes = '') {
        $data = $this->get_request_data();
        $res = array();

        $woo_taxonomies = NULL;
        {
            $woo_taxonomies = get_object_taxonomies('product');
        }

        

        if (!empty($data) AND is_array($data)) {
            foreach ($data as $tax_slug => $value) {
                if (in_array($tax_slug, $woo_taxonomies)) {
                    $value = explode(',', $value);
                    $res[] = array(
                        'taxonomy' => $tax_slug,
                        'field' => 'slug',
                        'terms' => $value,
                    );
                }
            }
        }
                
        $res = $this->_expand_additional_taxes_string($additional_taxes, $res);
        
        if (!empty($res)) {
            $res = array_merge(array('relation' => 'AND'), $res);
        }

        $res = $this->parse_tax_query($res);
        return apply_filters('apffw_get_tax_query', $res);
    }

    private function _expand_additional_taxes_string($additional_taxes, $res = array()) {
        if (!empty($additional_taxes)) {
            $t = explode('+', $additional_taxes);
            if (!empty($t) AND is_array($t)) {
                foreach ($t as $string) {
                    $tmp = explode(':', $string);
                    $tax_slug = $tmp[0];
                    $tax_terms = explode(',', $tmp[1]);
                    $slugs = array();
                    foreach ($tax_terms as $term_id) {
                        $term = get_term(intval($term_id), $tax_slug);
                        if (is_object($term) AND!is_wp_error($term)) {
                            $slugs[] = $term->slug;
                        }
                    }

                    
                    if (!empty($slugs)) {
                        $res[] = array(
                            'taxonomy' => $tax_slug,
                            'field' => 'slug',
                            'terms' => $slugs
                        );
                    }
                }
            }
        }

        return $res;
    }

    private function listen_catalog_visibility($meta_query, $is_search = false) {
        if ($this->get_option('listen_catalog_visibility')) {
            if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
                if (!empty($meta_query)) {
                    foreach ($meta_query as $key => $value) {
                        if (isset($value['key'])) {
                            if ($value['key'] == '_visibility') {
                                unset($meta_query[$key]);
                                $meta_query = array_values($meta_query);
                                break;
                            }
                        }
                        if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=')) {
                            if (isset($value['taxonomy'])) {
                                if ($value['taxonomy'] == 'product_visibility') {
                                    unset($meta_query[$key]);
                                    $meta_query = array_values($meta_query);
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($is_search) {
                    global $wp_query;
                    $wp_query->is_search = true;
                }
                if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=')) {
                    
                } elseif (version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
                    $meta_query[] = array(
                        'key' => '_visibility',
                        'value' => array('search', 'visible'),
                        'compare' => 'IN'
                    );
                }
            }
        }

        return $meta_query;
    }

    public function get_meta_query($args = array()) {
        $meta_query = WC()->query->get_meta_query();
        $meta_query = array_merge(array('relation' => 'AND'), $meta_query);
        
        $this->assemble_price_params($meta_query);
        if (!empty(APFFW_EXT::$includes['html_type_objects'])) {
            foreach (APFFW_EXT::$includes['html_type_objects'] as $obj) {
                if (method_exists($obj, 'assemble_query_params')) {
                    $obj->assemble_query_params($meta_query);
                }
            }
        }
        if (version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
            $meta_query = $this->listen_catalog_visibility($meta_query);
        }
            
        $meta_query = apply_filters('apffw_get_meta_query', $meta_query);
        return $meta_query;
    }

    public function apffw_products_ids_prediction($atts) {
        return $this->apffw_products($atts, true);
    }
    
    public function apffw_products($atts, $is_prediction = false) {
        $_REQUEST['apffw_products_doing'] = 1;

        $shortcode_txt = 'apffw_products';
        if (!empty($atts) AND is_array($atts)) {
            foreach ($atts as $key => $value) {
                $shortcode_txt .= ' ' . $key . '=' . $value;
            }
        }
        
        $data = $this->get_request_data();
        $catalog_orderby = $this->get_catalog_orderby(isset($data['orderby']) ? $data['orderby'] : '');
        
        extract(shortcode_atts(array(
            'columns' => apply_filters('loop_shop_columns', 4),
            'orderby' => 'no',
            'order' => 'no',
            'page' => 1,
            'per_page' => 0,
            'is_ajax' => 0,
            'taxonomies' => '',
            'sid' => '',
            'behavior' => '', 
            'custom_tpl' => '',
            'tpl_index' => '', 
            'predict_ids_and_continue' => false,
            'get_args_only' => false,
            'shortcode' => '',
            'product_ids' => "",
            'post__in' => "",
            'display_on_search' => 0,
                        ), $atts));

        $order_by_defined_in_atts = false;
        if ($orderby == 'no') {
            $orderby = $catalog_orderby['orderby'];
            $order = $catalog_orderby['order'];
        } elseif ($orderby == 'price' OR $orderby == 'price-desc') {
            $catalog_orderby = $this->get_catalog_orderby(isset($orderby) ? $orderby : '');
        } else {
            $order_by_defined_in_atts = true;
        }        
        
        $_REQUEST['apffw_additional_taxonomies_string'] = $taxonomies;
        
        $tax_query = array();
        if (empty($product_ids)) {
            $tax_query = $this->get_tax_query($taxonomies);
        }

        if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=') AND version_compare(WOOCOMMERCE_VERSION, '3.3', '<')) {
            $tax_query = $this->listen_catalog_visibility($tax_query);
        } elseif (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {         
            $search = false;
            if($this->is_isset_in_request_data($this->get_sapffw_search_slug()) && count($data) > 1){
                $search = true;
            }

            $tax_query = $this->product_visibility_not_in($tax_query, $this->generate_visibility_keys($search));
        }
        
        

        $args = array(
            'post_type' => array('product'/* ,'product_variation' */),
            'post_status' => 'publish',
            'orderby' => $orderby,
            'order' => $order,
            'tax_query' => $tax_query
        );
        if (empty($product_ids)) {
            $args['meta_query'] = $this->get_meta_query();
            if ($post__in) {
                $args['post__in'] = explode(",", $post__in);
            }
        } else {
            $args['post__in'] = explode(",", $product_ids);
        }

        if ($per_page > 0) {
            $args['posts_per_page'] = $per_page;
        } else {
            if (intval($this->settings['per_page']) > 0) {
                $args['posts_per_page'] = intval($this->settings['per_page']);
            }
            
            if (class_exists('Woocommerce_Products_Per_Page')) {
                $args['posts_per_page'] = $this->get_wppp_per_page();
            }
        }
        
        if (isset($_REQUEST['perpage'])) {
            if (is_integer($_REQUEST['perpage']))
            {
                $args['posts_per_page'] = sanitize_text_field($_REQUEST['perpage']);
            }
        }
        
        if (!isset($args['posts_per_page']) OR empty($args['posts_per_page'])) {
            if ($this->get_option('per_page') > 0) {
                $args['posts_per_page'] = $this->get_option('per_page');
            } else {
                $args['posts_per_page'] = get_option('posts_per_page');
            }
        }

        
        if (!$order_by_defined_in_atts) {
            if (!empty($catalog_orderby['meta_key'])) {
                $args['meta_key'] = $catalog_orderby['meta_key'];
                $args['orderby'] = $catalog_orderby['orderby'];
                if (!empty($catalog_orderby['order'])) {
                    $args['order'] = $catalog_orderby['order'];
                }
            } else {
                $args['orderby'] = $catalog_orderby['orderby'];
                if (!empty($catalog_orderby['order'])) {
                    $args['order'] = $catalog_orderby['order'];
                }
            }
        }
        
        $pp = $page;
        if (get_query_var('page')) {
            $pp = get_query_var('page');
        }
        if (get_query_var('paged')) {
            $pp = get_query_var('paged');
        }

        if ($pp > 1) {
            $args['paged'] = $pp;
        } else {
            $args['paged'] = ((get_query_var('page')) ? get_query_var('page') : $page);
        }
        

        if (!empty($behavior)) {
            switch ($behavior) {
                case 'recent':
                    $args['orderby'] = 'date';
                    $args['order'] = 'desc';
                    break;

                default:
                    break;
            }
        }
        
        $wr = $args;

        global $products, $wp_query;
        
        $tax_relations = apply_filters('apffw_main_query_tax_relations', array());
        if (!empty($tax_relations)) {
            $tax_query = $wr['tax_query'];
            foreach ($tax_query as $key => $value) {
                if (isset($value['taxonomy'])) {
                    if (in_array($value['taxonomy'], array_keys($tax_relations))) {
                        if (count($tax_query[$key]['terms'])) {
                            $tax_query[$key]['operator'] = $tax_relations[$value['taxonomy']];
                            $tax_query[$key]['include_children'] = 0;
                        }
                    }
                }
            }

            $wr['tax_query'] = $tax_query;
        }
        

        $wr = apply_filters('apffw_products_query', $wr);

        

        if ($get_args_only) {
            $_REQUEST['apffw_query_args'] = $wr;
            return $wr;
        }
        $hide_products = false;
        if ($display_on_search) {
            $hide_products = true;
            $get_array = $this->get_request_data();
            
            if (isset($this->settings['items_order'])) {
                $key_array = explode(',', $this->settings['items_order']);
                $by_only_array = array('apffw_text', 'stock', 'onsales', 'apffw_sku', 'product_visibility', 'min_price', 'max_price');
                $tax_array = array_keys($this->settings['excluded_terms']);
                $key_array = array_merge($by_only_array, $key_array, $tax_array);
                $real_query = array_intersect(array_keys($get_array), $key_array);
                if (count($real_query)) {
                    $hide_products = false;
                }
                
                if (isset($this->settings['meta_filter'])) {
                    if (!is_array($this->settings['meta_filter'])) {
                        $this->settings['meta_filter'] = array();
                    }
                    foreach ($this->settings['meta_filter'] as $item) {
                        $key = $item['search_view'] . "_" . $item['meta_key'];
                        if (in_array($key, array_keys($get_array))) {
                            $hide_products = false;
                        }
                    }
                }
            }
        }

        if (!$is_prediction) {
            $_REQUEST['apffw_wp_query'] = $wp_query = $products = new WP_Query($wr);

            $_REQUEST['apffw_wp_query_found_posts'] = $wp_query->found_posts;
            if ($predict_ids_and_continue) {
                $_REQUEST['predict_ids_and_continue'] = 1;
                $_REQUEST['apffw_wp_query_ids'] = new WP_Query(array_merge($wr, array('fields' => 'ids')));
                $_REQUEST['apffw_wp_query_ids'] = $_REQUEST['apffw_wp_query_ids']->posts;
            }
        } else {
            $_REQUEST['apffw_wp_query_ids'] = new WP_Query(array_merge($wr, array('fields' => 'ids')));
            $_REQUEST['apffw_wp_query_ids'] = $_REQUEST['apffw_wp_query_ids']->posts;
            return;
        }


        if ($this->get_option('listen_catalog_visibility')
                AND $this->is_isset_in_request_data($this->get_sapffw_search_slug())) {            
            $wp_query->is_search = true;
        }

        $wp_query->is_post_type_archive = true;
        $_REQUEST['apffw_wp_query_args'] = $wr;
        
        ob_start();
        global $woocommerce_loop;
        $woocommerce_loop['columns'] = $columns;
        $woocommerce_loop['loop'] = 0;
        if (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {
            $this->set_loop_properties($products, $columns);
        }
        ?>

        <?php if ($is_ajax == 1): ?>
            <?php ?>
            <div id="apffw_results_by_ajax" data-count="<?php _e($products->found_posts);?>"  class="apffw_results_by_ajax_shortcode" data-shortcode="<?php _e($shortcode_txt);?>">
                <?php
                $_REQUEST["apffw_redraw_elements"] = array();
                if (isset($this->settings['result_count_redraw']) AND $this->settings['result_count_redraw']) {
                    ob_start();
                    woocommerce_result_count();
                    $_REQUEST["apffw_redraw_elements"][$this->settings['result_count_redraw']] = ob_get_contents();
                    ob_end_clean();
                }
                if (isset($this->settings['order_dropdown_redraw']) AND $this->settings['order_dropdown_redraw']) {
                    ob_start();
                    woocommerce_catalog_ordering();
                    $_REQUEST["apffw_redraw_elements"][$this->settings['order_dropdown_redraw']] = ob_get_contents();
                    ob_end_clean();
                }
                if (isset($this->settings['per_page_redraw']) AND $this->settings['per_page_redraw']) {
                    ob_start();
                    woocommerce_pagination();
                    $_REQUEST["apffw_redraw_elements"][$this->settings['per_page_redraw']] = ob_get_contents();
                    ob_end_clean();
                }
                $_REQUEST["apffw_redraw_elements"] = apply_filters('apffw_redraw_elements_after_ajax', $_REQUEST["apffw_redraw_elements"], $products);
                
                ?>
            <?php endif; ?>
            <?php
            if ($products->have_posts()) :
                add_filter('post_class', array($this, 'woo_post_class'));
                $_REQUEST['apffw_before_shop_loop_done'] = true;
                ?>

                <div class="woocommerce columns-<?php _e($columns);?> woocommerce-page apffw_shortcode_output">

                    <?php
                    $show_loop_filters = true; 
                    if (!empty($behavior)) {
                        if ($behavior == 'recent') {
                            $show_loop_filters = false;
                        }
                    }
                    if (isset($_GET['action']) AND $_GET['action'] == 'elementor') {
                        $show_loop_filters = false;
                    }
                    if (!$hide_products) {
                        if ($show_loop_filters) {
                            do_action('woocommerce_before_shop_loop');
                        }

                        



                        if (function_exists('woocommerce_product_loop_start')) {
                            woocommerce_product_loop_start();
                        }
                        ?>

                        <?php
                        global $woocommerce_loop;
                        $woocommerce_loop['columns'] = $columns;
                        $woocommerce_loop['loop'] = 0;                        
                        ?>

                        <?php
                        $template_part = apply_filters('apffw_template_part', 'product');
                        
                        if (empty($custom_tpl) AND empty($tpl_index)) {
                            while ($products->have_posts()) : $products->the_post();
                                wc_get_template_part('content', $template_part);
                            endwhile;
                        } else {
                            if (!empty($tpl_index)) {
                                if (isset(APFFW_EXT::$includes['applications'][$tpl_index])) {
                                    APFFW_EXT::$includes['applications'][$tpl_index]->draw($products);
                                }
                            } else {
                                $custom_tpl = str_replace('.' . pathinfo($custom_tpl, PATHINFO_EXTENSION), '', str_replace("..", "", $custom_tpl));
                                _e($this->render_html(get_theme_file_path($custom_tpl . ".php"), array(
                                    'the_products' => $products
                                )));
                            }
                        }
                        ?>


                        <?php
                        if (function_exists('woocommerce_product_loop_end')) {
                            woocommerce_product_loop_end();
                        }
                        ?>

                        <?php
                        if ($show_loop_filters) {
                            do_action('woocommerce_after_shop_loop');
                        }
                    }
                    ?>
                </div>


                <?php
            else:
                if ($is_ajax == 1) { {
                        ?>
                        <div id="apffw_results_by_ajax" class="apffw_results_by_ajax_shortcode" data-shortcode="<?php _e($shortcode_txt);?>">
                            <?php
                        }
                    }
                    ?>
                    <div class="woocommerce woocommerce-page apffw_shortcode_output">

                        <?php
                        if (!$is_ajax) {
                            wc_get_template('loop/no-products-found.php');
                        } else {
                            ?>
                            <div id="apffw_results_by_ajax" class="apffw_results_by_ajax_shortcode" data-shortcode="<?php _e($shortcode_txt);?>">
                                <?php
                                wc_get_template('loop/no-products-found.php');
                                ?>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                    <?php
                    if ($is_ajax == 1) {
                        if (!get_option('apffw_try_ajax', 0)) {
                            _e('</div>');
                        }
                    }

                endif;
                ?>

                <?php if ($is_ajax == 1): ?>
                    <?php if (!get_option('apffw_try_ajax', 0)): ?>
                    </div>

                <?php endif; ?>
            <?php endif; ?>
            <?php
            wp_reset_postdata();
            wp_reset_query();
            
            if (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {
                wc_reset_loop();
            }
            
            unset($_REQUEST['apffw_products_doing']);
            return ob_get_clean();
        }
        
        public function woo_post_class($classes) {
            global $post;
            $classes[] = 'product';
            $classes[] = 'type-product';
            $classes[] = 'status-publish';
            $classes[] = 'has-post-thumbnail';
            $classes[] = 'post-' . $post->ID;
            return $classes;
        }
        
        public function apffw_draw_products() {
            if (isset($_REQUEST['link'])) {
                $link = parse_url(sanitize_url($_REQUEST['link']), PHP_URL_QUERY);
                parse_str($link, $_GET);
                $_GET = apply_filters('apffw_draw_products_get_args', $_GET, sanitize_url($_REQUEST['link']));
            }
            $product_ids = "";
            if (isset($_REQUEST['turbo_mode_ids'])) {
                $product_ids = " product_ids='" . sanitize_text_field($_REQUEST['turbo_mode_ids']) . "' ";
            }

            $shortcode_str = $this->check_shortcode("apffw_products", "[" . sanitize_text_field($_REQUEST['shortcode']) . " page=" . sanitize_text_field($_REQUEST['page']) . $product_ids . "]");

            $products = do_shortcode($shortcode_str);

            $additional_fields = array();

            if (isset($_REQUEST["apffw_redraw_elements"]) AND $_REQUEST["apffw_redraw_elements"]) {
                
                $additional_fields = APFFW_HELPER::sanitize_html_fields_array($_REQUEST["apffw_redraw_elements"]);
            }
            if (isset($_GET["apffw_redraw_elements"]) AND $_GET["apffw_redraw_elements"]) {
                $additional_fields = array_merge($additional_fields, APFFW_HELPER::sanitize_html_fields_array($_GET["apffw_redraw_elements"]));
            }

            
            $form = '';
            if (isset($_REQUEST['apffw_shortcode'])) {
                $text = "";
                $shortcode_str = "";

                if (empty($_REQUEST['apffw_additional_taxonomies_string'])) {
                    $text = "[" . sanitize_text_field($_REQUEST['apffw_shortcode']) . "]";
                } else {
                    $text = "[" . sanitize_text_field($_REQUEST['apffw_shortcode']) . " taxonomies={".sanitize_text_field($_REQUEST['apffw_additional_taxonomies_string'])."}]";
                }
                $shortcode_str = $this->check_shortcode("apffw", $text);
                
                if (!empty($shortcode_str)) {
                    $_REQUEST['apffw_shortcode_txt'] = sanitize_text_field($_REQUEST['apffw_shortcode']);
                }
                
                $form = trim(do_shortcode($shortcode_str));
            }


            wp_die(json_encode(compact('products', 'form', 'additional_fields')));
        }

        public function show_btn($atts) {
            $args = $atts;

            return $this->render_html(APFFW_PATH . 'views/shortcodes/apffw_show_btn.php', $args);
        }

        public function show_mobile_btn($atts) {
            $args = $atts;
            if (wp_is_mobile()) {
                return $this->render_html(APFFW_PATH . 'views/shortcodes/apffw_mobile_btn.php', $args);
            }


            return "";
        }

    public function apffw_shortcode($atts) {
        $args = array();
        
        if (isset($atts['taxonomies'])) {
            $args['additional_taxes'] = apply_filters('apffw_set_shortcode_taxonomyattr_behaviour', $atts['taxonomies']);
        } else {
            $args['additional_taxes'] = '';
        }
        
        unset($_REQUEST['apffw_shortcode_excluded_terms']);
        if (isset($atts['excluded_terms'])) {
            $_REQUEST['apffw_shortcode_excluded_terms'] = $atts['excluded_terms'];
        }

        if (isset($atts['mobile_mode'])) {
            $args['mobile_mode'] = $atts['mobile_mode'];
        }

        
        $taxonomies = $this->get_taxonomies();
        $allow_taxonomies = (array) (isset($this->settings['tax']) ? $this->settings['tax'] : array());
        $args['taxonomies'] = array();
        $hide_empty = (bool) get_option('apffw_hide_dynamic_empty_pos', 0);
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $tax_key => $tax) {
                if (!in_array($tax_key, array_keys($allow_taxonomies))) {
                    continue;
                }
                

                $args['taxonomies_info'][$tax_key] = $tax;
                $args['taxonomies'][$tax_key] = APFFW_HELPER::get_terms($tax_key, $hide_empty);
                
                if ($this->is_really_current_term_exists()) {
                    $t = $this->get_really_current_term();
                    if ($tax_key == $t->taxonomy) {
                        if (isset($args['taxonomies'][$tax_key][$t->term_id])) {
                            $args['taxonomies'][$tax_key] = $args['taxonomies'][$tax_key][$t->term_id]['childs'];
                        } else {
                            if ($t->parent != 0) {
                                $parent = get_term($t->parent, $tax_key);
                                $parents_ids = array();
                                $parents_ids[] = $parent->term_id;
                                while ($parent->parent != 0) {
                                    $parent = get_term_by('id', $parent->parent, $tax_key);
                                    $parents_ids[] = $parent->term_id;
                                }
                                $parents_ids = array_reverse($parents_ids);
                                
                                $tmp = $args['taxonomies'][$tax_key];
                                foreach ($parents_ids as $id) {
                                    $tmp = $tmp[$id]['childs'];
                                }
                                if (isset($tmp[$t->term_id])) {
                                    $args['taxonomies'][$tax_key] = $tmp[$t->term_id]['childs'];
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $args['taxonomies'] = array();
        }
        
        if (isset($atts['skin'])) {
            wp_enqueue_style('apffw_skin_' . $atts['skin'], APFFW_LINK . 'css/shortcode_skins/' . $atts['skin'] . '.css', array(), APFFW_VERSION);
        }
        

        if (isset($atts['sid'])) {
            $args['sid'] = $atts['sid'];
            wp_enqueue_script('apffw_sid', APFFW_LINK . 'js/apffw_sid.js', array('jquery'), APFFW_VERSION);
        }


        if (isset($atts['autohide'])) {
            $args['autohide'] = $atts['autohide'];
        } else {
            $args['autohide'] = 0;
        }

        if (isset($atts['redirect'])) {
            $args['redirect'] = $atts['redirect'];
        } else {
            $args['redirect'] = '';
        }

        if (isset($atts['start_filtering_btn'])) {
            $args['start_filtering_btn'] = $atts['start_filtering_btn'];
        } else {
            $args['start_filtering_btn'] = 0;
        }

        if (isset($atts['start_filtering_btn_txt'])) {
            $args['apffw_start_filtering_btn_txt'] = $atts['start_filtering_btn_txt'];
        } else {
            $args['apffw_start_filtering_btn_txt'] = apply_filters('apffw_start_filtering_btn_txt', esc_html__('Show products filter form', 'apffw-products-filter'));
        }


        if (isset($atts['tax_only'])) {
            $args['tax_only'] = explode(',', $atts['tax_only']);
            $args['tax_only'] = array_map('trim', $args['tax_only']);
        } else {
            $args['tax_only'] = array();
        }

        if (isset($atts['tax_exclude'])) {
            $args['tax_exclude'] = explode(',', $atts['tax_exclude']);
            $args['tax_exclude'] = array_map('trim', $args['tax_exclude']);
        } else {
            $args['tax_exclude'] = array();
        }

        if (isset($atts['by_only'])) {
            $args['by_only'] = explode(',', $atts['by_only']);
            $args['by_only'] = array_map('trim', $args['by_only']);
        } else {
            $args['by_only'] = array();
        }


        if (isset($atts['autosubmit']) AND $atts['autosubmit'] != -1) {
            $args['autosubmit'] = $atts['autosubmit'];
        } else {
            $args['autosubmit'] = get_option('apffw_autosubmit', 0);
        }

        $_REQUEST['hide_terms_count_txt_short'] = -1;
        if (isset($atts['hide_terms_count'])) {
            $_REQUEST['hide_terms_count_txt_short'] = (int) $atts['hide_terms_count'];
        }

        if (isset($atts['ajax_redraw'])) {
            $args['ajax_redraw'] = $atts['ajax_redraw'];
        } else {
            $args['ajax_redraw'] = 0;
        }
        if (isset($atts['btn_position'])) {
            $args['btn_position'] = $atts['btn_position'];
        } else {
            $args['btn_position'] = 'b';
        }
        if (isset($atts['dynamic_recount'])) {
            $args['dynamic_recount'] = $atts['dynamic_recount'];
        } else {
            $args['dynamic_recount'] = -1;
        }

        $args['price_filter'] = 0;
        if (isset($this->settings['by_price']['show'])) {
            $args['price_filter'] = (int) $this->settings['by_price']['show'];
        }

        if (isset($atts['by_step'])) {
            $args['by_step'] = $atts['by_step'];
        }
        
        $args['show_apffw_edit_view'] = 0;
        if (current_user_can('create_users')) {
            $args['show_apffw_edit_view'] = isset($this->settings['show_apffw_edit_view']) ? (int) $this->settings['show_apffw_edit_view'] : 0;
        }
        
        $_REQUEST['apffw_shortcode_txt'] = 'apffw ';
        if (!empty($atts)) {
            foreach ($atts as $key => $value) {
                if (($key == 'tax_only' OR $key == 'by_only' OR $key == 'tax_exclude') AND empty($value)) {
                    continue;
                }

                $_REQUEST['apffw_shortcode_txt'] .= $key . "='" . (is_array($value) ? explode(',', $value) : $value) . "' ";
            }
        }
        $args['apffw_settings'] = get_option('apffw_settings', array());

        $args['shortcode_atts'] = $atts;
        return $this->render_html(APFFW_PATH . 'views/apffw.php', apply_filters('apffw_filter_shortcode_args', $args));
    }
    
    public function apffw_price_filter($args = array()) {
        $type = 'slider';
        if (isset($args['type']) AND $args['type'] == 'select') {
            $type = 'select';
        }
        if (isset($args['type']) AND $args['type'] == 'text') {
            $type = 'text';
        }
        if (isset($args['type']) AND $args['type'] == 'radio') {
            $type = 'radio';
        }
        return $this->render_html(APFFW_PATH . 'views/shortcodes/apffw_price_filter_' . $type . '.php', $args);
    }

    public function apffw_search_options($args = array()) {
        return $this->render_html(APFFW_PATH . 'views/shortcodes/apffw_search_options.php', array());
    }

    public function apffw_found_count($args = array()) {
        return $this->render_html(APFFW_PATH . 'views/shortcodes/apffw_found_count.php', array());
    }

    public function apffw_redraw_apffw() {
        $shortcode = sanitize_text_field($_REQUEST['shortcode']);
        $_REQUEST['apffw_shortcode_txt'] = $shortcode ;
        $shortcode_str = $this->check_shortcode("apffw", "[" . $shortcode  . "]");
        wp_die(do_shortcode($shortcode_str));
    }

    public function woocommerce_pagination_args($args) {
        return $args;
    }

    public function get_really_current_term() {
        $res = NULL;
        $key = $this->session_rct_key;
        $request = $this->get_request_data(FALSE);

        if ($this->storage->is_isset($key)) {
            $res = $this->storage->get_val($key);
        }

        if (!$res) {
            if (isset($request['really_curr_tax'])) {
                $tmp = explode('-', $request['really_curr_tax']);
                $res = get_term($tmp[0], $tmp[1]);
            }
        }


        return $res;
    }

    public function is_really_current_term_exists() {
        return (bool) $this->get_really_current_term();
    }

    private function set_really_current_term($queried_obj = NULL) {
        if (defined('DOING_AJAX')) {
            return false;
        }


        $request = $this->get_request_data();
        if (!$queried_obj) {
            if (isset($request['really_curr_tax'])) {
                return false;
            }
        }

        $key = $this->session_rct_key;

        if ($queried_obj === NULL) {
            $this->storage->unset_val($key);
        } else {
            $this->storage->set_val($key, $queried_obj);
        }

        return $queried_obj;
    }

    public function cache_count_data_clear() {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE " . self::$query_cache_table);
    }

    public function apffw_cache_terms_clear() {
        global $wpdb;
        $res = $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name LIKE '_transient_apffw_terms_cache_%'");

        if (!empty($res)) {
            foreach ($res as $transient) {
                delete_transient(str_replace('_transient_', '', $transient->option_name));
            }
        }
    }

    public function apffw_price_transient_clear() {
        delete_transient('apffw_min_max_prices');
    }

    public function apffw_modify_query_args($query_args) {

        if (isset($_REQUEST[$this->get_sapffw_search_slug()])) {
            if (isset($_REQUEST['apffw_wp_query_args'])) {
                $query_args['meta_query'] = sanitize_text_field($_REQUEST['apffw_wp_query_args']['meta_query']);
                $query_args['tax_query'] = sanitize_text_field($_REQUEST['apffw_wp_query_args']['tax_query']);
                $query_args['paged'] = sanitize_text_field($_REQUEST['apffw_wp_query_args']['paged']);
            }
        }

        return $query_args;
    }

    public function get_custom_ext_path($relative = '') {
        if (!isset($this->settings['custom_extensions_path'])) {
            return null;
        }

        

        if (!empty($relative)) {
            $relative = trim($relative, DIRECTORY_SEPARATOR);
            $relative .= DIRECTORY_SEPARATOR;
        }
        return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $this->settings['custom_extensions_path'] . DIRECTORY_SEPARATOR . $relative;
    }

    public function get_custom_ext_link($relative = '') {
        if (!empty($relative)) {
            $relative = trim($relative, DIRECTORY_SEPARATOR);
            $relative .= DIRECTORY_SEPARATOR;
        }
        return WP_CONTENT_URL . DIRECTORY_SEPARATOR . $this->settings['custom_extensions_path'] . DIRECTORY_SEPARATOR . $relative;
    }

    public function get_ext_directories() {
        $directories = array();
        $directories['default'] = glob(APFFW_EXT_PATH . '*', GLOB_ONLYDIR);
        $directories['custom'] = array();
        if (isset($this->settings['custom_extensions_path']) AND!empty($this->settings['custom_extensions_path'])) {
            if ($this->get_custom_ext_path()) {
                $directories['custom'] = glob($this->get_custom_ext_path() . '*', GLOB_ONLYDIR);
            }
        }

        return $directories;
    }

    public function init_extensions() {
        $directories = $this->get_ext_directories();
        if (isset($this->settings['activated_extensions'])) {
            $activated = $this->settings['activated_extensions'];
        } else {
            $activated = array();
        }

        if (!empty($directories['custom']) AND is_array($directories['custom'])) {
            if (!is_array($activated)) {
                $activated = array();
            }

            foreach ($directories['custom'] as $path) {
                if (APFFW_EXT::is_ext_activated($path)) {
                    include_once $path . DIRECTORY_SEPARATOR . 'index.php';
                }
            }
        }

        if (!empty($directories['default']) AND is_array($directories['default'])) {
            if (!is_array($activated)) {
                $activated = array();
            }

            foreach ($directories['default'] as $path) {
                if (APFFW_EXT::is_ext_activated($path)) {
                    include_once $path . DIRECTORY_SEPARATOR . 'index.php';
                }
            }
        }
        
        $this->html_types = apply_filters('apffw_add_html_types', $this->html_types);
        $this->items_keys = apply_filters('apffw_add_items_keys', $this->items_keys);
    }

    public function apffw_remove_ext() {
        if (!current_user_can('manage_woocommerce') OR!current_user_can('activate_plugins')) {
            return;
        }
        check_ajax_referer('rm-ext-nonce', 'rm_ext_nonce');

        if (!wp_verify_nonce($_REQUEST['rm_ext_nonce'], 'rm-ext-nonce'))
            die('Stop!');
        

        $idx = sanitize_text_field($_REQUEST['idx']);

        $directories = array();
        if ($this->get_custom_ext_path()) {
            $directories = glob($this->get_custom_ext_path() . '*', GLOB_ONLYDIR);
        }
        if (!empty($directories)) {
            foreach ($directories as $dir) {

                if (APFFW_EXT::get_ext_idx_new($dir) == $idx) {

                    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach ($files as $file) {
                        if ($file->isDir()) {
                            rmdir($file->getRealPath());
                        } else {
                            unlink($file->getRealPath());
                        }
                    }
                    rmdir($dir);
                }
            }
            die('done');
        }
        die('fail');
    }

    function apffw_upload_ext() {
        if (!current_user_can('manage_woocommerce') OR!current_user_can('activate_plugins')) {
            return;
        }

        check_ajax_referer('add-ext-nonce', 'extnonce');

        if (!wp_verify_nonce($_REQUEST['extnonce'], 'add-ext-nonce'))
            die('Stop!');

        require(APFFW_PATH . 'lib/simple-ajax-uploader/extras/Uploader.php');

        $upload_dir = sanitize_url(esc_url($_SERVER['HTTP_LOCATION']));
        $valid_extensions = array('zip');

        $Upload = new FileUpload('uploadfile');
        $result = $Upload->handleUpload($upload_dir, $valid_extensions);

        

        $zipArchive = new ZipArchive();
        $zip_result = $zipArchive->open($Upload->getSavedFile());
        $ext_info = array();
        if ($zip_result === TRUE) {
            $zipArchive->extractTo($upload_dir);
            $zipArchive->close();
            $dir = $upload_dir . str_replace('.zip', '', $Upload->getFileName());
            $ext_info = APFFW_HELPER::parse_ext_data($dir . '/info.dat');
            $ext_info['idx'] = md5($dir);
            unlink($Upload->getSavedFile());
        }

        if (!$result) {
            die(json_encode(array('success' => false, 'msg' => $Upload->getErrorMsg())));
        } else {
            die(json_encode(array('success' => true, 'ext_info' => $ext_info)));
        }
    }

    public function is_permalink_activated() {
        return get_option('permalink_structure', '');
    }

    public function get_option($key, $default = 0) {
        $res = $default;
        if (isset($this->settings[$key])) {
            $res = $this->settings[$key];
        }

        return $res;
    }

    private function is_should_init() {
        if (is_admin()) {
            return true;
        }
        
        if (isset($this->settings['init_only_on']) AND!empty($this->settings['init_only_on'])) {
            $links = explode(PHP_EOL, trim($this->settings['init_only_on']));
            $server_link = '';
            if (isset($_SERVER['SCRIPT_URI'])) {
                $server_link = sanitize_url(esc_url($_SERVER['SCRIPT_URI']));
            } else {
                if (isset($_SERVER['REQUEST_URI'])) {
                    $server_link = site_url() . sanitize_url(esc_url($_SERVER['REQUEST_URI']));
                }
            }

            
            $removeChar = ["https://", "http://", "/"];
            $init = true;
            if (!empty($server_link)) {
                $server_link_mask = str_replace($removeChar, '', trim(stripcslashes($server_link), " /"));
                if (isset($this->settings['init_only_on_reverse']) AND $this->settings['init_only_on_reverse']) {
                    $init = true;
                } else {
                    $init = false;
                }
                foreach ($links as $key => $pattern_url) {

                    $pattern_url = str_replace($removeChar, '', trim(stripcslashes($pattern_url), " /"));
                    $use_mask = true;
                    if (stripos($pattern_url, '#') === 0) {
                        $pattern_url = trim(ltrim($pattern_url, "#"));
                        $use_mask = false;
                    }

                    if ($use_mask) {
                        preg_match('/(.+)?' . trim($pattern_url) . '(.+)?/', $server_link_mask, $matches);
                        $init_tmp = !empty($matches);
                    } else {
                        $init_tmp = ($pattern_url == $server_link_mask);
                    }

                    if (isset($this->settings['init_only_on_reverse']) AND $this->settings['init_only_on_reverse']) {

                        if ($init_tmp) {
                            $init = false;
                            break;
                        }
                    } else {

                        if ($init_tmp) {
                            $init = true;
                            break;
                        }
                    }
                }
                if ($init) {
                    $this->is_activated = true;
                    return true;
                }
            }
        } else {
            return true;
        }


        return false;
    }

    public function render_html($pagepath, $data = array()) {
        if (isset($data['pagepath'])) {
            unset($data['pagepath']);
        }
        if (is_array($data) AND!empty($data)) {
            extract($data);
        }

        $pagepath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $pagepath);
        ob_start();
        include($pagepath);
        return ob_get_clean();
    }

    public function apffw_sort_terms_is_checked($terms = array(), $type = "checkbox") {
        if (!is_array($terms)) {
            $terms = array();
        }
        $not_sort_terms = apply_filters('apffw_not_sort_checked_terms', array('slider'));

        if (in_array($type, $not_sort_terms)) {
            return $terms;
        }

        $request = $this->get_request_data();
        $temp_term = current($terms);
        if (!is_array($temp_term))
            return $terms;
        if ($this->is_isset_in_request_data($temp_term['taxonomy'])) {
            $current_request = $request[$temp_term['taxonomy']];
            $current_request = explode(',', urldecode($current_request));
        } else {
            return $terms;
        }
        $temp_array = array();
        foreach ($terms as $key => $val) {

            if (in_array($val['slug'], $current_request)) {
                $temp_array[$key] = $val;
            }
        }
        foreach ($temp_array as $key => $val) {
            unset($terms[$key]);
        }
        return array_merge($temp_array, $terms);
    }

    public function set_loop_properties($query, $columns) {
        wc_set_loop_prop('is_paginated', true);
        wc_set_loop_prop('total_pages', $query->max_num_pages);
        wc_set_loop_prop('current_page', (int) max(1, $query->get('paged', 1)));
        wc_set_loop_prop('per_page', (int) $query->get('posts_per_page'));
        wc_set_loop_prop('total', (int) $query->found_posts);
        wc_set_loop_prop('columns', $columns);
        wc_set_loop_prop('is_filtered', true);
    }

    public function product_visibility_not_in($tax_query, $keys) {
        $arr_ads = wc_get_product_visibility_term_ids();
        $product_not_in = array();
        if (!is_array($keys)) {
            $keys = array($keys);
        }
        foreach ($keys as $key) {
            if (isset($arr_ads[$key]) OR!empty($arr_ads[$key])) {
                $product_not_in[] = $arr_ads[$key];
            }
        }
        if (!empty($product_not_in)) {
            $tax_query[] = array(
                'taxonomy' => 'product_visibility',
                'field' => 'term_taxonomy_id',
                'terms' => $product_not_in,
                'operator' => 'NOT IN',
            );
        }

        return $tax_query;
    }

    public function apffw_overide_template($template, $template_name, $template_path) {
        if ($template_name == 'loop/no-products-found.php') {
            if (isset($this->settings['override_no_products']) AND!empty($this->settings['override_no_products'])) {
                $_REQUEST['override_no_products'] = 1;
                $template = APFFW_PATH . 'views/no-products-found.php';
            }
        }
        return $template;
    }

    public function generate_visibility_keys($search = false) {
        $keys = array();
        if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
            $keys[] = 'outofstock';
        }
        if ($this->get_option('listen_catalog_visibility')) {
            $keys[] = 'exclude-from-search';
            if (!$search) {
                $keys[] = 'exclude-from-catalog';
            }
        }
        return $keys;
    }

    public function product_visibility_for_parse_query() {
        add_filter('woocommerce_product_query_tax_query', function ($tax_query, $_this) {
            foreach ($tax_query as $key => $tax) {
                if (isset($tax['taxonomy']) AND $tax['taxonomy'] == 'product_visibility') {
                    unset($tax_query[$key]);
                }
            }
            $tax_query = $this->product_visibility_not_in($tax_query, $this->generate_visibility_keys(true));
            return $tax_query;
        }, 10, 2);
        add_filter('woocommerce_product_is_visible', function ($visible, $id) {
            return true;
        }, 10, 2);
    }

    

    public function check_shortcode($tag = "", $text = "") {
        $tags = array(
            'products_apffw',
            'recent_products_apffw',
            'sale_products_apffw',
            'best_selling_products_apffw',
            'top_rated_products_apffw',
            'featured_products_apffw',
            $tag
        );

        $pattern = get_shortcode_regex($tags);
        preg_match_all("/$pattern/", $text, $matches);
        if (isset($matches[0][0]) AND!empty($matches[0][0])) {
            return $matches[0][0];
        } else {
            return "";
        }
    }

    public function get_wppp_per_page() {
        $per_page = 12;
        if (isset($_REQUEST['wppp_ppp'])) {
            $per_page = intval(sanitize_text_field($_REQUEST['wppp_ppp']));
        } elseif (isset($_REQUEST['ppp'])) {
            $per_page = intval(sanitize_text_field($_REQUEST['ppp']));
        } elseif (isset($_COOKIE['woocommerce_products_per_page'])) {
            $per_page = sanitize_text_field($_COOKIE['woocommerce_products_per_page']);
        } else {
            $per_page = intval(get_option('wppp_default_ppp', '12'));
        }
        return $per_page;
    }

    public function activate_woo_shortcodes() {
        $shortcodes = array(
            'products',
            'recent_products',
            'sale_products',
            'best_selling_products',
            'top_rated_products',
            'featured_products',
        );
        foreach ($shortcodes as $tag) {
            add_shortcode($tag . "_apffw", array($this, 'apffw_ajax_shortcode'));
            add_action('woocommerce_shortcode_' . $tag . '_loop_no_results', function () {
                do_action('woocommerce_no_products_found');
            }, 10, 1);
        }
    }

    public function apffw_ajax_shortcode($atts, $content, $tag) {
        $attr_str = "";
        if (is_array($atts)) {
            foreach ($atts as $key => $val) {
                if (is_int($key)) {
                    $attr_str .= " " . $val;
                } else {
                    $attr_str .= sprintf(" %s='%s'", $key, $val);
                }
            }
        }
        $shortcode = str_replace("_apffw", "", $tag);
        ob_start();
        ?>

        <div id="apffw_results_by_ajax" class="apffw_results_by_ajax_shortcode" data-shortcode="<?php _e($tag . $attr_str);?>" >
            <?php
            _e(do_shortcode("[" . $shortcode . $attr_str . " ]"));
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function sort_terms_before_out($terms, $type) {
        if (!is_array($terms)) {
            $terms = array();
        }
        $term = reset($terms);
        if (!$term) {
            return $terms;
        }
        $tax = $term["taxonomy"];
        $orberby = -1;
        $order = "ASC";
        if (isset($this->settings['orderby'][$tax])) {
            $orberby = $this->settings['orderby'][$tax];
        }
        if (isset($this->settings['order'][$tax])) {
            $orber = $this->settings['order'][$tax];
        }
        if ($orberby != -1) {
            switch ($orberby) {
                case'id':
                    if ($orber == 'ASC') {
                        uasort($terms, function ($a, $b) {
                            if ((int) $a['term_id'] == (int) $b['term_id']) {
                                return 0;
                            }
                            return ((int) $a['term_id'] < (int) $b['term_id']) ? -1 : 1;
                        });
                    } else {
                        uasort($terms, function ($a, $b) {
                            if ((int) $a['term_id'] == (int) $b['term_id']) {
                                return 0;
                            }
                            return ((int) $a['term_id'] > (int) $b['term_id']) ? -1 : 1;
                        });
                    }
                    break;
                case'name':
                    if ($orber == 'ASC') {
                        uasort($terms, function ($a, $b) {
                            return strnatcasecmp($a['name'], $b['name']);
                        });
                    } else {
                        uasort($terms, function ($a, $b) {

                            return strnatcasecmp($b['name'], $a['name']);
                        });
                    }

                    break;
                case'numeric':
                    if ($orber == 'ASC') {
                        uasort($terms, function ($a, $b) {
                            if ((int) $a['slug'] == (int) $b['slug']) {
                                return 0;
                            }
                            return ((int) $a['slug'] < (int) $b['slug']) ? -1 : 1;
                        });
                    } else {
                        uasort($terms, function ($a, $b) {
                            if ((int) $a['slug'] == (int) $b['slug']) {
                                return 0;
                            }
                            return ((int) $a['slug'] > (int) $b['slug']) ? -1 : 1;
                        });
                    }

                    break;
            }
        }

        return $terms;
    }

    public function change_query_tax_relations($logic_array) {
        $logic_arr = array();
        if (isset($this->settings['comparison_logic'])) {
            $logic_arr = $this->settings['comparison_logic'];
        }
        foreach ($logic_arr as $cat => $logic) {
            if ($logic == 'AND' OR $logic == 'NOT IN') {
                $logic_array[$cat] = $logic;
            }
        }
        return $logic_array;
    }

    public function replacing_template_loop_product_thumbnail() {
        $show = 0;
        if (isset($this->settings['show_images_by_attr_show'])) {
            $show = $this->settings['show_images_by_attr_show'];
        }
        if ($show) {
            if (class_exists('Flatsome_Default')) {
                remove_action('flatsome_woocommerce_shop_loop_images', 'woocommerce_template_loop_product_thumbnail', 10);
                add_action('flatsome_woocommerce_shop_loop_images', array($this, 'wc_template_loop_product_replaced_thumb'), 10);
            } else {
                remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
                add_action('woocommerce_before_shop_loop_item_title', array($this, 'wc_template_loop_product_replaced_thumb'), 10);
            }
        }
    }

    public function wc_template_loop_product_replaced_thumb() {
        global $product;
        $needed = array();
        if (isset($this->settings['show_images_by_attr'])) {
            $needed = $this->settings['show_images_by_attr'];
        }

        if (is_array($needed) AND count($needed)) {
            if ($this->is_isset_in_request_data($this->get_sapffw_search_slug()) AND $product->is_type("variable")) {

                $need_array = array();
                $request = $this->get_request_data();

                $need_array = array_intersect_key($request, array_flip($needed));
                $rate = array();
                if (count($need_array)) {
                    $variations = $product->get_available_variations();

                    foreach ($variations as $key => $variant) {
                        if (isset($variant['attributes'])) {
                            $rate[$key] = 0;
                            foreach ($need_array as $attr_name => $values) {
                                if (isset($variant['attributes']["attribute_" . $attr_name]) AND in_array($variant['attributes']["attribute_" . $attr_name], explode(",", $values))) {
                                    $rate[$key]++;
                                }
                            }
                        }
                    }

                    arsort($rate);

                    $attr_key = array_key_first($rate);
                    if (array_shift($rate)) {
                        if (isset($variations[$attr_key]["image_id"]) AND $variations[$attr_key]["image_id"]) {
                            $image_size = apply_filters('single_product_archive_thumbnail_size', 'woocommerce_thumbnail');
                            $image = wp_get_attachment_image($variations[$attr_key]["image_id"], $image_size, false, array());
                            if ($image) {
                                _e($image);
                                return;
                            }
                        }
                    }
                }
            }
        }
        _e(woocommerce_get_product_thumbnail());
    }

    public function woopt_set_query_args($query_args) {
        if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
            $_REQUEST['apffw_products_doing'] = 1;
            $query_args['tax_query'] = array_merge($query_args['tax_query'], $this->get_tax_query(''));
            $query_args['meta_query'] = array_merge($query_args['meta_query'], $this->get_meta_query());
            $query_args = apply_filters('apffw_products_query', $query_args);

            if (isset($_GET['paged'])) {
                $query_args['paged'] = intval($_GET['paged']);
            }
        }
        return $query_args;
    }

    function sync_on_product_save($product_id, $prod) {
        if (isset($this->settings['price_transient']) AND $this->settings['price_transient']) {
            apffw_price_transient_clear();
        }
    }

    public function parse_tax_query($tax_query) {
        $request = $this->get_request_data();
        $array_logic = $this->change_query_tax_relations(array());
        foreach ($array_logic as $key => $logic) {
            if ($logic == "NOT IN" AND isset($request[$this->check_slug($key)])) {
                $terms = explode(",", $request[$this->check_slug($key)]);
                $tax_query[] = array(
                    "taxonomy" => $key,
                    "terms" => $terms,
                    "field" => "slug",
                    "operator" => "NOT IN"
                );
            }
        }
        return $tax_query;
    }

    public function check_slug($slug) {
        $array_logic = $this->change_query_tax_relations(array());
        if (isset($array_logic[$slug]) AND $array_logic[$slug] == 'NOT IN') {
            $slug = 'rev_' . $slug;
        }
        return $slug;
    }

    public function uncheck_slug($slug) {
        $slug = preg_replace("@^rev_@", '', $slug);
        return $slug;
    }
}

if (isset($_GET['P3_NOCACHE'])) {
    return;
}

$init_the_plugin = true;

if (is_admin()) {
    $init_the_plugin = false;
}

if (isset($_GET['action']) AND $_GET['action'] == 'elementor') {
    $init_the_plugin = true;
}

if (defined('DOING_AJAX')) {
    $init_the_plugin = true;
}

if (isset($_GET['page']) AND $_GET['page'] == 'wc-settings') {
    $init_the_plugin = true;
}

if (isset($_SERVER['SCRIPT_URI']) AND function_exists('basename')) {
    $init_pages = array('plugins.php', 'widgets.php', 'term.php', 'edit-tags.php');
    $lastSegment = basename(parse_url(sanitize_url(esc_url($_SERVER['SCRIPT_URI'])), PHP_URL_PATH));
    if (in_array($lastSegment, $init_pages)) {
        $init_the_plugin = true;
    }
} else {
    $init_the_plugin = true;
}

if ($init_the_plugin OR isset($_GET['apffw_cron_key'])) {
    $APFFW = new APFFW();
    if ($APFFW->is_activated) {

        $GLOBALS['APFFW'] = $APFFW;
        add_action('init', array($APFFW, 'init'), 1);
    }
}

add_action('woocommerce_update_product', function ($prod_id, $product = null) {
    delete_transient('apffw_min_max_prices');
}, 10, 2);