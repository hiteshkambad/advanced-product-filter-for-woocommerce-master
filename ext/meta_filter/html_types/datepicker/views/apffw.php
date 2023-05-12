<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
global $APFFW;

$request = $APFFW->get_request_data();
$current_request_txt = "";
if ($APFFW->is_isset_in_request_data("datepicker_" . $meta_key)) {
    $current_request_txt = $request["datepicker_" . $meta_key];
    $current_request = explode('-', urldecode($current_request_txt));
} else {
    $current_request = array();
}



$from = "";
$to = "";
if (!empty($current_request)) {
    $from = ($current_request[0] != "i") ? $current_request[0] : "";
    $to = ($current_request[1] != "i") ? $current_request[1] : "";
}

$count = 0;
$show = true;
$hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos', 0);
if (empty($current_request)) {
    if ($hide_dynamic_empty_pos) {
        $meta_field = array(
            'key' => $meta_key,
            'value' => array("i", "i"),
        );
        $count_data = array();
        $count = $APFFW->dynamic_count(array(), 'checkbox_ex', (isset($_REQUEST['additional_taxes'])) ? sanitize_post($_REQUEST['additional_taxes']) : "", $meta_field);
    }
    
    if ($hide_dynamic_empty_pos AND $count == 0) {
        $show = false;
    }
}

$format = (isset($meta_settings['format'])) ? $meta_settings['format'] : "mm/dd/yy";
$show_title_label = (isset($meta_settings['show_title_label'])) ? $meta_settings['show_title_label'] : 1;
$css_classes = "apffw_block_html_items";
$show_toggle = 0;
if (isset($meta_settings['show_toggle_button'])) {
    $show_toggle = (int) $meta_settings['show_toggle_button'];
}

$block_is_closed = true;
if (!empty($current_request)) {
    $block_is_closed = false;
}
if ($show_toggle === 1 AND empty($current_request)) {
    $css_classes .= " apffw_closed_block";
}

if ($show_toggle === 2 AND empty($current_request)) {
    $block_is_closed = false;
}
$tooltip_text = "";
if (isset($meta_settings['tooltip_text'])) {
    $tooltip_text = $meta_settings['tooltip_text'];
}
if (in_array($show_toggle, array(1, 2))) {
    $block_is_closed = apply_filters('apffw_block_toggle_state', $block_is_closed);
    if ($block_is_closed) {
        $css_classes .= " apffw_closed_block";
    } else {
        $css_classes = str_replace('apffw_closed_block', '', $css_classes);
    }
}

if ($show):
    $top_panel_txt = "";
    $top_panel_txt = APFFW_HELPER::wpml_translate(null, $options['title']);

    $format_ = $format;
    $format_compatibility = array(
        'mm/dd/yy' => "m/d/y",
        'dd-mm-yy' => 'd-m-y',
        'yy-mm-dd' => 'y-m-d',
        'D, d M, yy' => 'D, d M, Y',
        'd MM, y' => 'd M, y',
    );

    if (isset($format_compatibility[$format_])) {
        $format_ = $format_compatibility[$format_];
    }
    if ($from) {
        $top_panel_txt .= " ";
        $top_panel_txt .= sprintf(esc_html__("from: %s", 'apffw-products-filter'), date($format_, $from));
    }
    if ($to) {
        $top_panel_txt .= " ";
        $top_panel_txt .= sprintf(esc_html__("to: %s", 'apffw-products-filter'), date($format_, $to));
    }
    ?>
    <div data-css-class="apffw_meta_datepicker_container" class="apffw_meta_datepicker_container apffw_container apffw_container_<?php _e("datepicker_" . $meta_key);?>">
        <div class="apffw_container_inner">
            <div class="apffw_container_inner apffw_container_inner_datepicker_slider">
                <?php if ($show_title_label) :
                    ?>
                    <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                    <?php _e(APFFW_HELPER::wpml_translate(null, $options['title']));?>
                    <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate(null, $options['title']), $tooltip_text));?>    
                    <?php APFFW_HELPER::draw_title_toggle($show_toggle, $block_is_closed); ?></<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                    <?php
                endif;
                ?>
                <div class="<?php _e($css_classes);?>">
                    <div class="apffw_meta_datepicker_container">
                        <input class="apffw_meta_datepicker_data" type="hidden" name="<?php _e($meta_key);?>_from" value="<?php _e($from);?>" />
                        <input data-format="<?php _e($format);?>" type="text" readonly="readonly" data-meta-key="<?php _e($meta_key);?>" class="apffw_calendar apffw_calendar_from" placeholder="<?php esc_html_e('from', 'apffw-products-filter') ?>" />
                        <a href="#" data-meta-key="<?php _e($meta_key);?>" data-name="<?php _e($meta_key);?>_from"  class="apffw_meta_datepicker_reset">
                            <img src="<?php _e($APFFW->settings['delete_image']);?>" height="12" width="12" alt="<?php esc_html_e("Сlear", 'apffw-products-filter') ?>" />
                        </a>
                    </div>
                    <div class="apffw_meta_datepicker_container">
                        <input class="apffw_meta_datepicker_data" type="hidden" name="<?php _e($meta_key);?>_to" value="<?php _e($to);?>" />
                        <input data-format="<?php _e($format);?>" type="text" readonly="readonly" data-meta-key="<?php _e($meta_key);?>" class="apffw_calendar apffw_calendar_to" placeholder="<?php esc_html_e('to', 'apffw-products-filter') ?>" />
                        <a href="#" data-meta-key="<?php _e($meta_key);?>"  data-name="<?php _e($meta_key);?>_to"   class="apffw_meta_datepicker_reset">
                            <img src="<?php _e($APFFW->settings['delete_image']);?>" height="12" width="12" alt="<?php esc_html_e("Clear", 'apffw-products-filter') ?>" />
                        </a>
                    </div>

                </div>
                <input type="hidden" value="<?php _e($top_panel_txt);?>" data-anchor="apffw_n_<?php _e("datepicker_" . $meta_key);?>_<?php _e($current_request_txt);?>" />
            </div>
        </div>
    </div>
<?php endif; ?>
