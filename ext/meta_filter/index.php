<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

final class APFFW_META_FILTER extends APFFW_EXT {

    public $type = 'application';
    public $folder_name = 'meta_filter';
    
    protected $excluded_meta = array();
    protected $meta_keys = array();
    
    public $meta_filters_obj = array();
    public $meta_filter_types = array();

    
    public function __construct() {
        parent::__construct();
        $this->init();
    }
    public function get_ext_override_path()
    {
        return get_stylesheet_directory(). DIRECTORY_SEPARATOR ."apffw". DIRECTORY_SEPARATOR ."ext". DIRECTORY_SEPARATOR .$this->html_type. DIRECTORY_SEPARATOR;
    }
    public function get_ext_path() {
        return plugin_dir_path(__FILE__);
    }

    public function get_ext_link() {
        return plugin_dir_url(__FILE__);
    }

    public function init() {

        require_once $this->get_ext_path() . 'classes/apffw_type_meta_filter.php';

        add_action('apffw_print_applications_tabs_' . $this->folder_name, array($this, 'apffw_print_applications_tabs'), 10, 1);
        add_action('apffw_print_applications_tabs_content_' . $this->folder_name, array($this, 'apffw_print_applications_tabs_content'), 10, 1);
        add_action('wp_footer', array($this, 'wp_footer'), 12);
        add_action('wp_ajax_apffw_meta_get_keys', array($this, 'apffw_meta_get_keys'));
        add_filter('apffw_get_meta_query', array($this, 'apffw_get_meta_query'));

        $this->meta_filter_types = array(
            'slider' => array(
                'key' => 'slider',
                'title' => esc_html__('Slider', 'apffw-products-filter'),
                'hide_if' => array('string','DATE'),
                'show_options' => false,
            ),            
            'textinput' => array(
                'key' => 'textinput',
                'title' => esc_html__('Search by text', 'apffw-products-filter'),
                'hide_if' => array('DATE'),
                'show_options' => false,
            ),
            'checkbox' => array(
                'key' => 'checkbox',
                'title' => esc_html__('Checkbox', 'apffw-products-filter'),
                'hide_if' => array('DATE'),
                'show_options' => false,
            ),
            'select' => array(
                'key' => 'select',
                'title' => esc_html__('Drop-down', 'apffw-products-filter'),
                'hide_if' => array('DATE'),
                'show_options' => true,
            ),
            'mselect' => array(
                'key' => 'mselect',
                'title' => esc_html__('Multi Drop-down', 'apffw-products-filter'),
                'hide_if' => array('DATE'),
                'show_options' => true,
            ),
            'datepicker' => array(
                'key' => 'datepicker',
                'title' => esc_html__('Datepicker', 'apffw-products-filter'),
                'hide_if' => array('string'),
                'show_options' => false,
            ), 
        );

        $this->meta_filter_types = apply_filters('apffw_meta_filter_add_types', $this->meta_filter_types);
        global $APFFW;
        if (isset($this->apffw_settings['meta_filter']) AND is_array($this->apffw_settings['meta_filter'])) {
            foreach ($this->apffw_settings['meta_filter'] as $key => $val) {
                if ($key == "__META_KEY__") {
                    continue;
                }

                $this->meta_keys[] = $val['meta_key'];
                $this->conect_activate_meta_filter($key, $val);
            }
        }
        add_filter('apffw_add_items_keys', array($this, 'apffw_add_items_keys'));
    }

    public function conect_activate_meta_filter($key, $options) {
        $class_name = 'APFFW_META_FILTER_' . strtoupper($options['search_view']);
        require_once $this->get_ext_path() . 'html_types/' . $options['search_view'] . '/index.php';
        if (class_exists($class_name)) {
            $this->meta_filters_obj[$key] = new $class_name($key, $options, $this->apffw_settings);
            self::$includes['js_init_functions']["meta_" . $options['search_view']] = $this->meta_filters_obj[$key]->get_js_func_name();
        }
    }

    public function wp_footer() {
        
    }

    public function apffw_print_applications_tabs() {
        ?>
        <li>
            <a href="#tabs-meta-filter">
                <span><?php esc_html_e("Meta Data Fields", 'apffw-products-filter') ?></span>
            </a>
        </li>
        <?php
    }

    public function apffw_print_applications_tabs_content() {
        require_once $this->get_ext_path() . 'classes/apffw_pds_cpt.php';
        if (class_exists('APFFW_PDS_CPT', false)) {
            $pds_cpt = new APFFW_PDS_CPT();
            $this->excluded_meta = array_merge($pds_cpt->get_internal_meta_keys(), $this->excluded_meta);
        }
        wp_enqueue_script('apffw_qs_admin', $this->get_ext_link() . 'js/admin.js',array(),APFFW_VERSION);
        
        global $APFFW;
        $data = array();

        $data['apffw_settings'] = $this->apffw_settings;
        $data['meta_types'] = $this->meta_filter_types;
        $data['metas'] = (isset($data['apffw_settings']['meta_filter'])) ? $data['apffw_settings']['meta_filter'] : array();

        _e($APFFW->render_html($this->get_ext_path() . 'views/tabs_content.php', $data));
    }

    public function apffw_meta_get_keys() {
        $res = '';

        require_once $this->get_ext_path() . 'classes/apffw_pds_cpt.php';
        if (class_exists('APFFW_PDS_CPT', false)) {
            $pds_cpt=new APFFW_PDS_CPT();
            $this->excluded_meta = array_merge($pds_cpt->get_internal_meta_keys(), $this->excluded_meta);
        }
        $product_id = intval(sanitize_text_field($_REQUEST['product_id']));
        if ($product_id > 0) {
            $a1 = array_keys(get_post_meta($product_id, '', true));
            $res = array_diff($a1, $this->excluded_meta);
        }

        die(json_encode(array_values($res)));
    }

    public function apffw_add_items_keys($arr_keys) {
        if (!empty($this->meta_keys)) {
            $arr_keys = array_merge($arr_keys, $this->meta_keys);
        }
        return $arr_keys;
    }

    public function apffw_print_html_type_options_meta() {
        $key = "";
        $key = str_replace('apffw_print_html_type_options_', "", current_filter());
        global $APFFW;
        ?>
        <li data-key="<?php _e($key);?>" class="apffw_options_li">

            <?php
            $show = 0;
            if (isset($this->apffw_settings[$key]['show'])) {
                $show = $this->apffw_settings[$key]['show'];
            }
            ?>

                <span class="icon-arrow-combo help_tip apffw_drag_and_drope" data-tip="<?php esc_html_e("drag and drope", 'apffw-products-filter'); ?>"></span>

            <strong class="apffw_fix1"><?php _e($this->apffw_settings['meta_filter'][$key]['title']);?>:</strong>

            
            <span class="icon-question help_tip" data-tip="<?php esc_html_e('Meta filter', 'apffw-products-filter') ?>"></span`>

            <div class="select-wrap">
                <select name="apffw_settings[<?php _e($key);?>][show]" class="apffw_setting_select">
                    <option value="0" <?php _e(selected($show, 0));?>><?php esc_html_e('No', 'apffw-products-filter') ?></option>
                    <option value="1" <?php _e(selected($show, 1));?>><?php esc_html_e('Yes', 'apffw-products-filter') ?></option>
                </select>
            </div>
            <a href="#" data-key="<?php _e($key);?>" data-name="<?php _e($this->apffw_settings['meta_filter'][$key]['title']);?>" class="apffw-button js_apffw_options js_apffw_options_<?php _e($key);?> help_tip" data-tip="<?php esc_html_e('additional options', 'apffw-products-filter') ?>"><span class="icon-cog-outline"></span></a>
            <?php
            $data = array();
            $data['key'] = $key;
            $data['settings'] = $this->apffw_settings;
            _e($APFFW->render_html($this->get_ext_path() . 'html_types/' . $this->apffw_settings['meta_filter'][$key]['search_view'] . '/views/additional_options.php', $data));
            ?>      
        </li>
        <?php
    }

    public function apffw_get_meta_query($meta_query) {
        $meta_filter_query = array();
        foreach ($this->meta_filters_obj as $obj) {
            $meta = $obj->create_meta_query();
            if ($meta) {
                $meta_filter_query[] = $meta;
            }
        }
        if (!empty($meta_filter_query)) {
            $meta_filter_query['relation'] = 'AND';
            $meta_query = array_merge($meta_query, $meta_filter_query);
        }

        return $meta_query;
    }

    public static function get_meta_filter_name($request_key) {
        global $APFFW;
        foreach ($APFFW->settings['meta_filter'] as $item) {
            $key = $item['search_view'] . "_" . $item['meta_key'];
            if ($key == $request_key) {
                return APFFW_HELPER::wpml_translate(null, $item['title']);
            }
        }
        return false;
    }

    public static function get_meta_filter_option_name($request_key, $request_val) {
        global $APFFW;
        $option_name = "";
        foreach ($APFFW->settings['meta_filter'] as $item) {
            $key = $item['search_view'] . "_" . $item['meta_key'];
            if ($key == $request_key) {
                $class_name = "APFFW_META_FILTER_" . strtoupper($item['search_view']);
                if (class_exists($class_name)) {
                    $option_name = $class_name::get_option_name($request_val, $request_key);
                    return APFFW_HELPER::wpml_translate(null, $option_name);
                }
            }
        }

        return false;
    }

    public static function get_meta_title_messenger($request_val, $request_key) {
        $html = "";
        $title = self::get_meta_filter_name($request_key);
        $option = self::get_meta_filter_option_name($request_key, $request_val);
        if (!$title) {
            return $html;
        }
        $html = $title;
        if ($option) {
            $html .= ":" . $option;
        }
        return "<span class='apffw_terms'>" . $html . "</span><br />";
    }

}

APFFW_EXT::$includes['applications']['meta_filter'] = new APFFW_META_FILTER();
