<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
if (!empty($meta_options)) {
    $meta_options = explode($options_separator, $meta_options);
} else {
    $meta_options = array();
}

global $APFFW;
$request = $APFFW->get_request_data();
$apffw_value = "";
if (isset($request['select_' . $meta_key])) {
    $apffw_value = $request['select_' . $meta_key];
}
$show_title_label = (isset($meta_settings['show_title_label'])) ? $meta_settings['show_title_label'] : 1;
$css_classes = "apffw_block_html_items";
$show_toggle = 0;
$shown_options_tags = 0;
if (isset($meta_settings['show_toggle_button'])) {
    $show_toggle = (int) $meta_settings['show_toggle_button'];
}

$block_is_closed = true;
if (!empty($apffw_value)) {
    $block_is_closed = false;
}
if ($show_toggle === 1 AND empty($apffw_value)) {
    $css_classes .= " apffw_closed_block";
}

if ($show_toggle === 2 AND empty($apffw_value)) {
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

if (isset($_REQUEST['hide_terms_count_txt_short']) AND $_REQUEST['hide_terms_count_txt_short'] != -1) {
    if ((int) $_REQUEST['hide_terms_count_txt_short'] == 1) {
        $_REQUEST['hide_terms_count_txt'] = 1;
    } else {
        $_REQUEST['hide_terms_count_txt'] = 0;
    }
}

$all_options = array();
$show_count = get_option('apffw_show_count', 0);
$show_count_dynamic = get_option('apffw_show_count_dynamic', 0);
$hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos', 0);

if (is_array($meta_options)) {
    foreach ($meta_options as $key => $option) {

        if (!$option) {
            continue;
        }
        $option_title = $option;
        $custom_title = explode('^', $option, 2);
        if (count($custom_title) > 1) {
            $option = $custom_title[1];
            $option_title = $custom_title[0];
        }
        $count_string = "";
        $count = 0;

        if (intval($apffw_value) != $key + 1) {
            if ($show_count) {
                $meta_field = array(
                    'key' => $meta_key,
                    'value' => $option,
                );
                if ($show_count_dynamic) {
                    $count_data = array();
                    $count = $APFFW->dynamic_count(array(), 'select', (isset($_REQUEST['additional_taxes'])) ? sanitize_post($_REQUEST['additional_taxes']) : "", $meta_field);
                    $count_string = '(' . $count . ')';
                } else {
                    $count = 1;                    
                }
            }
            
            if ($hide_dynamic_empty_pos AND $count == 0) {
                continue;
            }
        }

        if (isset($_REQUEST['hide_terms_count_txt']) AND $_REQUEST['hide_terms_count_txt']) {
            $count_string = "";
        }
        $all_options[$key + 1] = array(
            'name' => APFFW_HELPER::wpml_translate(null, $option_title) . $count_string,
            'count' => $count
        );
    }
}

if (!count($all_options)) {
    return "";
}
?>
<div data-css-class="apffw_meta_select_container" class="apffw_meta_select_container apffw_container apffw_container_<?php _e($meta_key);?>  apffw_container_<?php _e("select_" . $meta_key);?>">
    <div class="apffw_container_inner">
        <div class="apffw_container_inner apffw_container_inner_meta_select">
<?php if ($show_title_label) {
    ?>
                <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
    <?php _e(APFFW_HELPER::wpml_translate(null, $options['title']));?>
    <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate(null, $options['title']), $tooltip_text));?>
    <?php APFFW_HELPER::draw_title_toggle($show_toggle, $block_is_closed); ?></<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
    <?php }
?>
            <div class="<?php _e($css_classes);?>">
                <select class="apffw_meta_select apffw_meta_select_<?php _e($meta_key);?>" name="<?php _e("select_" . $meta_key);?>">
                    <option value="0"><?php _e(APFFW_HELPER::wpml_translate(null, $options['title']));?></option>

            <?php if (!empty($all_options)): ?>
                <?php foreach ($all_options as $key => $option) : ?>

                            <option <?php if ($show_count AND $option['count'] == 0 AND $key != $apffw_value): ?>disabled=""<?php endif; ?> value="<?php _e($key);?>" <?php _e(selected($key == intval($apffw_value)));?>>
                    <?php
                    _e($option['name']);
                    ?>
                            </option>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </select> 
                        <?php
                        $curr_title = "";
                        if (isset($meta_options[intval($apffw_value) - 1])) {
                            $op_title = explode('^', $meta_options[intval($apffw_value) - 1], 2);
                            if (count($op_title) > 1) {
                                $curr_title = $op_title[0];
                            } else {
                                $curr_title = $meta_options[intval($apffw_value) - 1];
                            }
                        }
                        ?>   
                <input type="hidden" value="<?php _e(APFFW_HELPER::wpml_translate(null, $curr_title));?>" data-anchor="apffw_n_<?php _e("select_" . $meta_key);?>_<?php _e($apffw_value);?>" />

            </div>    
        </div>        
    </div>
</div>
