<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

final class APFFW_EXT_IMAGE extends APFFW_EXT {

    public $type = 'html_type';
    public $html_type = 'image';
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
        add_action('apffw_print_tax_additional_options_image', array($this, 'print_additional_options'), 10, 1);
        add_action('apffw_print_design_additional_options', array($this, 'apffw_print_design_additional_options'), 10, 1);
        self::$includes['js']['apffw_' . $this->html_type . '_html_items'] = $this->get_ext_link() . 'js/html_types/' . $this->html_type . '.js';
        self::$includes['css']['apffw_' . $this->html_type . '_html_items'] = $this->get_ext_link() . 'css/html_types/' . $this->html_type . '.css';
        self::$includes['js_init_functions'][$this->html_type] = 'apffw_init_image';

        add_action('admin_head', array($this, 'admin_head'), 50);

        $this->taxonomy_type_additional_options = array(
            'show_title' => array(
                'title' => esc_html__('Show image title', 'apffw-products-filter'),
                'tip' => esc_html__('Show image title below picture', 'apffw-products-filter'),
                'type' => 'select',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter'),
                )
            ),
            'as_radio' => array(
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
            wp_enqueue_style('apffw_image', $this->get_ext_link() . 'css/admin.css', array(), APFFW_VERSION);
            wp_enqueue_script('apffw_image', $this->get_ext_link() . 'js/html_types/plugin_options.js', array('jquery'), APFFW_VERSION);
        }
    }

    public function apffw_add_html_types($types) {
        $types[$this->html_type] = esc_html__('Image', 'apffw-products-filter');
        return $types;
    }

    public function print_additional_options($key) {
        global $APFFW;
        $apffw_settings = $APFFW->settings;
        $terms = APFFW_HELPER::get_terms($key, 0, 0, 0, 0);
        if (!empty($terms)) {
            ?>
            <br /><a href="javascript:void(0);" class="button apffw-button-outline-secondary apffw_toggle_images"><?php esc_html_e('toggle image terms', 'apffw-products-filter') ?></a><br />
            <ul class="apffw_image_list apffw_hide_options">
                <?php
                foreach ($terms as $t) {
                    $term_key = 'images_term_' . $t['term_id'];
                    $image = '';
                    if (isset($apffw_settings[$term_key]['image_url'])) {
                        $image = $apffw_settings[$term_key]['image_url'];
                    }

                    if (!isset($apffw_settings[$term_key]['image_styles'])) {
                        //init value
                        $apffw_settings[$term_key]['image_styles'] = 'width: 100px;
height:50px;
margin: 0 3px 3px 0;
background-size: 100% 100%;
background-clip: content-box;
border: 2px solid #e2e6e7;
padding: 2px;
color: #292f38;
font-size: 0;
text-align: center;
cursor: pointer;
border-radius: 4px;
transition: border-color .35s ease;';
                    }
                    ?>
                    <li>
                        <table>
                            <tr>

                                <td style="padding-top: 0;">
                                    <input type="text" name="apffw_settings[<?php _e($term_key);?>][image_url]" value="<?php _e($image);?>" placeholder="<?php esc_html_e('set link to the image', 'apffw-products-filter') ?>" class="text" style="width: 600px;" />
                                    <a href="#" class="apffw-button apffw_select_image"><?php esc_html_e('Select Image', 'apffw-products-filter') ?></a>
                                </td>
                                <td>
                                    <input type="button" value="&#xea49" data-key="<?php _e($term_key);?>" data-name="<?php printf(__('Image settings for term %s', 'apffw-products-filter'), $t['name']) ?>" class="apffw-button js_apffw_options js_apffw_options_image icon-book">
                                    <input type="hidden" name="apffw_settings[<?php _e($term_key);?>][image_styles]" value="<?php _e($apffw_settings[$term_key]['image_styles']);?>" />

                                    <div id="apffw-modal-content-<?php _e($term_key);?>" style="display: none;">

                                        <div class="apffw-form-element-container">

                                            <div class="apffw-name-description apffw_width_30p">
                                                <strong><?php esc_html_e('Image styles', 'apffw-products-filter') ?></strong>
                                                <span><?php esc_html_e('This option should be set', 'apffw-products-filter') ?></span>

                                                <b><?php esc_html_e('Example', 'apffw-products-filter') ?>:</b><br />
                                                <code>width: 100px;<br />
                                                    height:50px;<br />
                                                    margin: 0 3px 3px 0;<br />
                                                    background-size: 100% 100%;<br />
                                                    background-clip: content-box;<br />
                                                    border: 2px solid #e2e6e7;<br />
                                                    padding: 2px;<br />
                                                    color: #292f38;<br />
                                                    font-size: 0;<br />
                                                    text-align: center;<br />
                                                    cursor: pointer;<br />
                                                    border-radius: 4px;<br />
                                                    transition: border-color .35s ease;</code>
                                            </div>                                       


                                            <div class="apffw-form-element apffw_width_70p">
                                                <textarea class="apffw_popup_option apffw_fix11" data-option="image_styles"></textarea><br />

                                            </div>

                                        </div>



                                    </div>
                                </td>
                                <td class="apffw_fix8">
                                    <p class="description"> [ <?php _e(APFFW_HELPER::strtolower($t['slug']));?> ]</p>
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

            if (!isset($apffw_settings['checked_image'])) {
                $apffw_settings['checked_image'] = '';
            }
        }

    }

    APFFW_EXT::$includes['taxonomy_type_objects']['image'] = new APFFW_EXT_IMAGE();
    