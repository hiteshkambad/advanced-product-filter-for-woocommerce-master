<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

final class APFFW_EXT_COLOR extends APFFW_EXT {

    public $type = 'html_type';
    public $html_type = 'color';
    public $html_type_dynamic_recount_behavior = 'multi';

    public function __construct() {
        parent::__construct();
        $this->init();
    }

    public function get_ext_path() {
        return plugin_dir_path(__FILE__);
    }

    public function get_ext_override_path() {
        return get_stylesheet_directory() . DIRECTORY_SEPARATOR . "apffw" . DIRECTORY_SEPARATOR . "ext" . DIRECTORY_SEPARATOR . $this->html_type . DIRECTORY_SEPARATOR;
    }

    public function get_ext_link() {
        return plugin_dir_url(__FILE__);
    }

    public function init() {
        add_filter('apffw_add_html_types', array($this, 'apffw_add_html_types'));
        add_action('wp_enqueue_scripts', array($this, 'wp_head'), 9);
        add_action('woocommerce_settings_tabs_apffw', array($this, 'woocommerce_settings_tabs_apffw'), 51);
        add_action('apffw_print_tax_additional_options_color', array($this, 'print_additional_options'), 10, 1);
        add_action('apffw_print_design_additional_options', array($this, 'apffw_print_design_additional_options'), 10, 1);
        self::$includes['js']['apffw_' . $this->html_type . '_html_items'] = $this->get_ext_link() . 'js/html_types/' . $this->html_type . '.js';
        self::$includes['css']['apffw_' . $this->html_type . '_html_items'] = $this->get_ext_link() . 'css/html_types/' . $this->html_type . '.css';
        self::$includes['js_init_functions'][$this->html_type] = 'apffw_init_colors';


        add_action('admin_head', array($this, 'admin_head'), 50);

        $this->taxonomy_type_additional_options = array(
            'show_tooltip' => array(
                'title' => esc_html__('Tooltip text', 'apffw-products-filter'),
                'tip' => esc_html__('Enter tooltip text if necessary', 'apffw-products-filter'),
                'type' => 'select',
                'options' => array(
                    1 => esc_html__('Yes', 'apffw-products-filter'),
                    0 => esc_html__('No', 'apffw-products-filter')
                )
            ),
            'show_title_column' => array(
                'title' => esc_html__('Show in one column', 'apffw-products-filter'),
                'tip' => esc_html__('Show in one column with title', 'apffw-products-filter'),
                'type' => 'select',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter'),
                )
            ),
            'as_radio_color' => array(
                'title' => esc_html__('Behavior as radio button', 'apffw-products-filter'),
                'tip' => esc_html__('Use image as radio button', 'apffw-products-filter'),
                'type' => 'select',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter'),
                )
            ),			
        );
    }

    public function admin_head() {
        if (isset($_GET['tab']) AND $_GET['tab'] == 'apffw') {
            wp_enqueue_style('apffw_color', $this->get_ext_link() . 'css/admin.css', array(), APFFW_VERSION);
            wp_enqueue_script('apffw_color', $this->get_ext_link() . 'js/html_types/plugin_options.js', array('jquery'), APFFW_VERSION);
        }
    }

    public function apffw_add_html_types($types) {
        $types[$this->html_type] = esc_html__('Color', 'apffw-products-filter');
        return $types;
    }

    public function woocommerce_settings_tabs_apffw() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    public function wp_head() {

        self::$includes['css_code_custom'][$this->index] = $this->get_style();
    }

    public function get_style() {
        global $APFFW;
        ob_start();
        if (isset($APFFW->settings['checked_color_img'])) {
            if (!empty($APFFW->settings['checked_color_img'])) {
                ?>
                .checked .apffw_color_checked{
                background: url(<?php _e($APFFW->settings['checked_color_img']);?>) !important;
                } 
                <?php
            }
        }
        return ob_get_clean();
    }

    public function print_additional_options($key) {
        global $APFFW;
        $apffw_settings = $APFFW->settings;
        $terms = APFFW_HELPER::get_terms($key, 0, 0, 0, 0);
        if (!empty($terms)) {
            ?>
            <br /><a href="javascript:void(0);" class="button apffw-button-outline-secondary apffw_toggle_colors"><?php esc_html_e('toggle color terms', 'apffw-products-filter') ?></a><br />
            <ul class="apffw_color_list apffw_hide_options">
                <?php
                foreach ($terms as $t) {
                    $color = '#000000';
                    if (isset($apffw_settings['color'][$key][$t['slug']])) {
                        $color = $apffw_settings['color'][$key][$t['slug']];
                    }

                    $color_img = '';
                    if (isset($apffw_settings['color_img'][$key][$t['slug']])) {
                        $color_img = $apffw_settings['color_img'][$key][$t['slug']];
                    }
                    ?>
                    <li>
                        <table>
                            <tr>
                                <td valign="top">
                                    <input type="text" name="apffw_settings[color][<?php _e($key);?>][<?php _e($t['slug']);?>]" value="<?php _e($color);?>" id="apffw_color_picker_<?php _e($t['slug']);?>" class="apffw-color-picker" >
                                </td>
                                <td>
                                    <input type="text" name="apffw_settings[color_img][<?php _e($key);?>][<?php _e($t['slug']);?>]" value="<?php _e($color_img);?>" placeholder="<?php esc_html_e('background image url 25x25', 'apffw-products-filter') ?>" class="text" style="width: 600px;" />
                                    <a href="#" class="apffw-button apffw_select_image"><?php esc_html_e('Select Image', 'apffw-products-filter') ?></a>
                                </td>
                                <td class="apffw_fix8">
                                    <p class="description"> [ <?php _e(APFFW_HELPER::strtolower($t['name']));?> ]</p>
                                </td>
                            </tr>
                        </table>
                    </li>
                    <?php
                }
                _e('</ul>');
            }
        }

        public function apffw_print_design_additional_options() {
            global $APFFW;
            $apffw_settings = $APFFW->settings;

            if (!isset($apffw_settings['checked_color_img'])) {
                $apffw_settings['checked_color_img'] = '';
            }
        }

    }

    APFFW_EXT::$includes['taxonomy_type_objects']['color'] = new APFFW_EXT_COLOR();
    