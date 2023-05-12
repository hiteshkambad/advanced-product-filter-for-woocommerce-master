<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

class APFFW_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(__CLASS__, esc_html__('APFFW - Advanced Product Filter For WooCommerce', 'apffw-products-filter'), array(
            'classname' => __CLASS__,
            'description' => esc_html__('Advanced Product Filter For WooCommerce by Vrinsoft', 'apffw-products-filter')
                )
        );
    }

    public function widget($args, $instance) {
        $args['instance'] = $instance;
        $args['sidebar_id'] = (isset($args['id'])) ? $args['id'] : 0;
        $args['sidebar_name'] = (isset($args['name'])) ? $args['name'] : "";
        
        global $APFFW;
        $price_filter = 0;
        if (isset($APFFW->settings['by_price']['show'])) {
            $price_filter = (int) $APFFW->settings['by_price']['show'];
        }

        if (isset($args['before_widget'])) {
            _e($args['before_widget']);
        }
        ?>
        <div class="widget widget-apffw">
            <?php
            if (!empty($instance['title'])) {
                $instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
                if (isset($args['before_title'])) {
                    _e($args['before_title']);
                    _e($instance['title']);
                    _e($args['after_title']);
                } else {
                    ?>
                    <<?php _e(apply_filters('apffw_widget_title_tag', 'h3'));?> class="widget-title"><?php _e($instance['title']);?></<?php _e(apply_filters('apffw_widget_title_tag', 'h3'));?>>
                    <?php
                }
            }
            ?>


            <?php
            if (isset($instance['additional_text_before'])) {
                _e(do_shortcode($instance['additional_text_before']));
            }

            $redirect = '';
            if (isset($instance['redirect'])) {
                $redirect = $instance['redirect'];
            }

            

            $apffw_start_filtering_btn = 0;
            if (isset($instance['apffw_start_filtering_btn'])) {
                $apffw_start_filtering_btn = (int) $instance['apffw_start_filtering_btn'];
            }

            

            $ajax_redraw = '';
            if (isset($instance['ajax_redraw'])) {
                $ajax_redraw = $instance['ajax_redraw'];
            }

            $dynamic_recount = -1;
            if (isset($instance['dynamic_recount'])) {
                $dynamic_recount = $instance['dynamic_recount'];
            }

            $btn_position = 'b';
            if (isset($instance['btn_position'])) {
                $btn_position = $instance['btn_position'];
            }
            $autosubmit = -1;
            if (isset($instance['autosubmit'])) {
                $autosubmit = $instance['autosubmit'];
            }
            $mobile_mode = 0;
            if (isset($instance['mobile_mode'])) {
                $mobile_mode = $instance['mobile_mode'];
            }
            ?>

            <?php _e(do_shortcode('[apffw sid="widget" mobile_mode="' . $mobile_mode . '" autosubmit="' . $autosubmit . '" start_filtering_btn=' . $apffw_start_filtering_btn . ' price_filter=' . $price_filter . ' redirect="' . $redirect . '" ajax_redraw="' . $ajax_redraw . '" btn_position="' . $btn_position . '" dynamic_recount="' . $dynamic_recount . '" ]'));?>
        </div>
        <?php
        if (isset($args['after_widget'])) {
            _e($args['after_widget']);
        }
    }

    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['additional_text_before'] = $new_instance['additional_text_before'];
        $instance['redirect'] = $new_instance['redirect'];
        $instance['apffw_start_filtering_btn'] = $new_instance['apffw_start_filtering_btn'];
        $instance['ajax_redraw'] = $new_instance['ajax_redraw'];
        $instance['btn_position'] = $new_instance['btn_position'];
        $instance['mobile_mode'] = $new_instance['mobile_mode'];
        $instance['dynamic_recount'] = $new_instance['dynamic_recount'];
        $instance['autosubmit'] = $new_instance['autosubmit'];
        return $instance;
    }

    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Advanced Product Filter For WooCommerce', 'apffw-products-filter'),
            'additional_text_before' => '',
            'redirect' => '',
            'apffw_start_filtering_btn' => 0,
            'ajax_redraw' => 0,
            'dynamic_recount' => -1,
            'btn_position' => 'b',
            'mobile_mode' => 0,
            'autosubmit' => -1
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $args = array();
        $args['instance'] = $instance;
        $args['widget'] = $this;
        ?>
        <p>
            <label for="<?php _e($this->get_field_id('title'));?>"><?php esc_html_e('Title', 'apffw-products-filter') ?>:</label>
            <input class="widefat" type="text" id="<?php _e($this->get_field_id('title'));?>" name="<?php _e($this->get_field_name('title'));?>" value="<?php _e($instance['title']);?>" />
        </p>
        
        <?php
    }

}
