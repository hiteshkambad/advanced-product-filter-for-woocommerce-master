<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
if (!isset($additional_taxes)) {
    $additional_taxes = '';
}
$price2_filter_data = APFFW_HELPER::get_price2_filter_data($additional_taxes);

$show_count = get_option('apffw_show_count', 0);
$show_count_dynamic = get_option('apffw_show_count_dynamic', 0);
$hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos', 0);
$hide_count_text = isset($this->settings['hide_terms_count_txt']) ? $this->settings['hide_terms_count_txt'] : 0;

if (isset($_REQUEST['hide_terms_count_txt_short']) AND $_REQUEST['hide_terms_count_txt_short'] != -1) {
    if ((int) $_REQUEST['hide_terms_count_txt_short'] == 1) {
        $hide_count_text = 1;
    } else {
        $hide_count_text = 0;
    }
}
?>


<div data-css-class="apffw_price_filter_radio_container" class="apffw_checkbox_authors_container ">
    <div class="apffw_container_overlay_item"></div>
    <div class="apffw_container_inner">
        <ul class='apffw_authors '>
            <?php
            if (!isset($price2_filter_data['ranges']['options']) OR!is_array($price2_filter_data['ranges']['options'])) {
                esc_html_e('Not possible. Enter options ranges in the plugin settings -> tab Structure -> Search by price -> additional options', 'apffw-products-filter');
            } else {
                foreach ($price2_filter_data['ranges']['options'] as $k => $value): $value = trim($value);
                    ?>
                    <?php
                    $c = 0;
                    $cs = '';
                    if ($show_count) {
                        $c = (int) $price2_filter_data['ranges']['count'][$k];
                        $cs = '(' . $c . ')';
                    }
                    if ($hide_count_text) {
                        $cs = '';
                    }

                    if ($show_count_dynamic AND $c == 0) {
                        if ($hide_dynamic_empty_pos) {
                            continue;
                        }
                    }
                    

                    $unique_id = uniqid('wr_');
                    ?>
                    <li class="apffw_list">
                        <input type="radio" <?php if ($c == 0 AND $show_count): ?>disabled=""<?php endif; ?> class="apffw_price_filter_radio"  <?php _e(checked($price2_filter_data['selected'], $k));?>  name="apffw_price_radio" id="apffw_price_radio_<?php _e($unique_id);?>" value="<?php _e($k);?>"  />
                        <span>&nbsp;&nbsp;</span><label for="apffw_price_radio_<?php _e($unique_id);?>"><?php _e($value);?> <?php _e($cs);?>  </label>
                        <a href="" data-tax="price" style="display: none;" class="apffw_radio_price_reset <?php if ($price2_filter_data['selected'] == $k): ?> apffw_radio_term_reset_visible <?php endif; ?> apffw_radio_term_reset_<?php _e($k);?>"><img src="<?php _e($this->settings['delete_image']);?>" height="12" width="12" alt="<?php esc_html_e("Delete", 'apffw-products-filter') ?>" /></a>
                    </li>
                    <?php
                endforeach;
            }
            ?>
        </ul>
    </div>
</div>


<?php

