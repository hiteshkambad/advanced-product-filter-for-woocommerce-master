<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

abstract class APFFW_META_FILTER_TYPE {

    protected $type_options = array();
    protected $apffw_settings = array();
    protected $type = "";
    protected $meta_key = "";
    public $value_type = '';
    public $options_separator = ',';

    public function __construct($key, $options, $apffw_settings) {
        $this->meta_key = $key;
        $this->type_options = $options;
        $this->apffw_settings = $apffw_settings;
        add_action('init', array($this, 'init_data'));
    }

    abstract public function init();

    abstract public function get_meta_filter_path();

    abstract public function get_meta_filter_link();

    abstract public function get_meta_filter_override_path();

    abstract public function create_meta_query();

    public function get_js_func_name() {
        return false;
    }

    public function init_data() {
        $this->options_separator = apply_filters('apffw_meta_options_separator', $this->options_separator);
    }

    protected function draw_additional_options() {
        return "";
    }

    public function draw_meta_filter_structure() {
        ?><li data-key="<?php _e($this->meta_key);?>" class="apffw_options_li">
        <?php
        $show = 0;
        if (isset($this->apffw_settings[$this->meta_key]['show'])) {
            $show = $this->apffw_settings[$this->meta_key]['show'];
        }
        ?>
            <span class="icon-arrow-combo help_tip apffw_drag_and_drope" data-tip="<?php esc_html_e("drag and drope", 'apffw-products-filter'); ?>"></span>

            <strong class="apffw_fix1"><?php _e($this->apffw_settings['meta_filter'][$this->meta_key]['title']);?>:</strong>


            <span class="icon-question help_tip" data-tip="<?php esc_html_e('Meta filter', 'apffw-products-filter') ?>"></span>

            <div class="select-wrap">
                <select name="apffw_settings[<?php _e($this->meta_key);?>][show]" class="apffw_setting_select">
                    <option value="0" <?php _e(selected($show, 0));?>><?php esc_html_e('No', 'apffw-products-filter') ?></option>
                    <option value="1" <?php _e(selected($show, 1));?>><?php esc_html_e('Yes', 'apffw-products-filter') ?></option>
                </select>
            </div>
            <a href="#" data-key="<?php _e($this->meta_key);?>" data-name="<?php _e($this->apffw_settings['meta_filter'][$this->meta_key]['title']);?>" class="apffw-button js_apffw_options js_apffw_options_<?php _e($this->meta_key);?> help_tip" data-tip="<?php esc_html_e('additional options', 'apffw-products-filter') ?>"><span class="icon-cog-outline"></span></a>
                <?php
                _e($this->draw_additional_options());
                ?></li><?php
    }

    public function apffw_print_html_type_meta() {
        _e("<h1>", $this->meta_key, "</h1>");
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

}
