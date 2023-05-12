<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

class APFFW_META_FILTER_SELECT extends APFFW_META_FILTER_TYPE {

    public $type = 'select';
    public $js_func_name = 'apffw_init_meta_selects';

    public function __construct($key, $options, $apffw_settings) {
        parent::__construct($key, $options, $apffw_settings);
        $this->value_type = (isset($this->apffw_settings['meta_filter'][$this->meta_key]['type'])) ? $this->apffw_settings['meta_filter'][$this->meta_key]['type'] : 'string';
        $this->init();
    }

    public function init() {
        add_action('apffw_print_html_type_options_' . $this->meta_key, array($this, 'draw_meta_filter_structure'));
        add_action('apffw_print_html_type_' . $this->meta_key, array($this, 'apffw_print_html_type_meta'));
        add_action('wp_footer', array($this, 'wp_footer'));
        add_filter('apffw_extensions_type_index', array($this, 'add_type_index'));
    }

    public function wp_footer() {
        wp_enqueue_script('meta-select-js', $this->get_meta_filter_link() . 'js/select.js', array('jquery'), APFFW_VERSION, true);
        wp_enqueue_style('meta-select-css', $this->get_meta_filter_link() . 'css/select.css', array(), APFFW_VERSION);
    }

    public function get_meta_filter_path() {
        return plugin_dir_path(__FILE__);
    }

    public function get_meta_filter_override_path() {
        return get_stylesheet_directory() . DIRECTORY_SEPARATOR . "apffw" . DIRECTORY_SEPARATOR . "ext" . DIRECTORY_SEPARATOR . 'meta_filter' . DIRECTORY_SEPARATOR . "html_types" . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public function get_meta_filter_link() {
        return plugin_dir_url(__FILE__);
    }

    public function add_type_index($indexes) {
        $indexes[] = '"' . $this->type . "_" . $this->meta_key . '"';
        return $indexes;
    }

    protected function draw_additional_options() {
        $data = array();
        $data['key'] = $this->meta_key;
        $data['settings'] = $this->apffw_settings;
        return $this->render_html($this->get_meta_filter_path() . '/views/additional_options.php', $data);
    }

    public function apffw_print_html_type_meta() {

        $data['meta_key'] = $this->meta_key;
        $data['options'] = $this->type_options;
        $data['meta_options'] = (isset($this->type_options["options"])) ? $this->type_options["options"] : "";
        $data['meta_settings'] = (isset($this->apffw_settings[$this->meta_key])) ? $this->apffw_settings[$this->meta_key] : "";
		$data['options_separator'] = $this->options_separator;
        if (isset($this->apffw_settings[$this->meta_key]["show"]) AND $this->apffw_settings[$this->meta_key]["show"]) {
            if (file_exists($this->get_meta_filter_override_path() . 'views' . DIRECTORY_SEPARATOR . 'apffw.php')) {
                _e($this->render_html($this->get_meta_filter_override_path() . 'views' . DIRECTORY_SEPARATOR . 'apffw.php', $data));
            } else {
                _e($this->render_html($this->get_meta_filter_path() . '/views/apffw.php', $data));
            }
        }
    }

    protected function check_current_request() {
        global $APFFW;
        $request = $APFFW->get_request_data();
        if (isset($request[$this->type . "_" . $this->meta_key]) AND $request[$this->type . "_" . $this->meta_key]) {
            return $request[$this->type . "_" . $this->meta_key];
        }
        return false;
    }

    public function create_meta_query() {
        $curr_text = "";
        $curr_index = $this->check_current_request();
        $options = (isset($this->type_options["options"])) ? $this->type_options["options"] : "";
        $options = explode($this->options_separator, $options);
        if (isset($options[intval($curr_index) - 1])) {
            $curr_text = $options[intval($curr_index) - 1];
            $custom_title = explode('^', $options[intval($curr_index) - 1], 2);
            if (count($custom_title) > 1) {
                $curr_text = $custom_title[1];
            }
        }
        if ($curr_text) {
            $meta = array(
                'key' => $this->meta_key,
                'value' => $curr_text,
                'compare' => '=',
                'type' => $this->value_type,
            );

            return $meta;
        } else {
            return false;
        }
    }

    public function get_js_func_name() {
        return $this->js_func_name;
    }

    public static function get_option_name($value, $key = NULL) {
        $option_txt = "";
        global $APFFW;
        if ($key) {
            $meta_key = str_replace("select_", "", $key);
            $options = explode($this->options_separator, (isset($APFFW->settings['meta_filter'][$meta_key]["options"])) ? $APFFW->settings['meta_filter'][$meta_key]["options"] : "");
            if (!empty($options) AND isset($options[intval($value - 1)])) {
                $op_title = explode('^', $options[intval($value) - 1], 2);
                if (count($op_title) > 1) {
                    $curr_title = $op_title[0];
                } else {
                    $curr_title = $options[intval($value) - 1];
                }
                $option_txt = APFFW_HELPER::wpml_translate(null, $curr_title);
            }
        }
        return $option_txt;
    }

}
