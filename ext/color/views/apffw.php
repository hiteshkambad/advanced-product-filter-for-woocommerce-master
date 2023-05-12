<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
global $APFFW;
$colors = isset($apffw_settings['color'][$tax_slug]) ? $apffw_settings['color'][$tax_slug] : array();
$colors_imgs = isset($apffw_settings['color_img'][$tax_slug]) ? $apffw_settings['color_img'][$tax_slug] : array();
$show_count = get_option('apffw_show_count', 0);
$show_count_dynamic = get_option('apffw_show_count_dynamic', 0);
$hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos', 0);
$apffw_autosubmit = get_option('apffw_autosubmit', 0);

$show_tooltip = $this->settings['show_tooltip'][$tax_slug];
$color_type = "checkbox";
if (isset($APFFW->settings['as_radio_color'][$tax_slug]) AND $APFFW->settings['as_radio_color'][$tax_slug]) {
    $color_type = "radio";
}

$show_title = 0;

if (isset($this->settings['show_title_column'][$tax_slug])) {
    $show_title = (int) $this->settings['show_title_column'][$tax_slug];
}

$show_title_class = "";
if ($show_title) {
    $show_title_class = "apffw_color_title_col";
}
?>

<ul class = "apffw_list apffw_list_color <?php _e($show_title_class);?>" data-type="<?php _e($color_type);?>">
    <?php
    $apffw_tax_values = array();
    $current_request = array();
    $request = $APFFW->get_request_data();
    $_REQUEST['additional_taxes'] = $additional_taxes;
    $_REQUEST['hide_terms_count_txt'] = isset($APFFW->settings['hide_terms_count_txt']) ? $APFFW->settings['hide_terms_count_txt'] : 0;
    
    if (isset($_REQUEST['hide_terms_count_txt_short']) AND $_REQUEST['hide_terms_count_txt_short'] != -1) {
        if ((int) $_REQUEST['hide_terms_count_txt_short'] == 1) {
            $_REQUEST['hide_terms_count_txt'] = 1;
        } else {
            $_REQUEST['hide_terms_count_txt'] = 0;
        }
    }
    
    if ($APFFW->is_isset_in_request_data($APFFW->check_slug($tax_slug))) {
        $current_request = $request[$APFFW->check_slug($tax_slug)];
        $current_request = explode(',', urldecode($current_request));
    }

    $hidden_terms = array();
    if (!isset($_REQUEST['apffw_shortcode_excluded_terms'])) {
        if (isset($APFFW->settings['excluded_terms'][$tax_slug])) {
            $hidden_terms = explode(',', $APFFW->settings['excluded_terms'][$tax_slug]);
        }
    } else {
        $hidden_terms = explode(',', sanitize_text_field($_REQUEST['apffw_shortcode_excluded_terms']));
    }


    

    $not_toggled_terms_count = 0;
    if (isset($APFFW->settings['not_toggled_terms_count'][$tax_slug])) {
        $not_toggled_terms_count = intval($APFFW->settings['not_toggled_terms_count'][$tax_slug]);
    }
    

    $terms = apply_filters('apffw_sort_terms_before_out', $terms, 'color');
    $terms_count_printed = 0;
    $hide_next_term_li = false;
    ?>
    <?php if (!empty($terms)): ?>
        <?php foreach ($terms as $term) : $inique_id = uniqid(); ?>
            <?php
            $count_string = "";
            $count = 0;
            if (!in_array($term['slug'], $current_request)) {
                if ($show_count) {
                    if ($show_count_dynamic) {
                        $count = $APFFW->dynamic_count($term, 'multi', sanitize_post($_REQUEST['additional_taxes']));
                    } else {
                        $count = $term['count'];
                    }
                    $count_string = '<span>(' . $count . ')</span>';
                }
                
                if ($hide_dynamic_empty_pos AND $count == 0) {
                    continue;
                }
            }

            if ($_REQUEST['hide_terms_count_txt']) {
                $count_string = "";
            }

            $color = '#000000';
            if (isset($colors[$term['slug']])) {
                $color = $colors[$term['slug']];
            }

            $color_img = '';
            if (isset($colors_imgs[$term['slug']]) AND!empty($colors_imgs[$term['slug']])) {
                $color_img = $colors_imgs[$term['slug']];
            }

            $inreverse = true;
            if (isset($APFFW->settings['excluded_terms_reverse'][$tax_slug]) AND $APFFW->settings['excluded_terms_reverse'][$tax_slug]) {
                $inreverse = !$inreverse;
            }
            if (in_array($term['term_id'], $hidden_terms) == $inreverse) {
                continue;
            }


            if ($not_toggled_terms_count > 0 AND $terms_count_printed === $not_toggled_terms_count) {
                $hide_next_term_li = true;
            }


            $term_desc = strip_tags(term_description($term['term_id'], $term['taxonomy']));
            ?>
            <li class="apffw_color_term_<?php _e(sanitize_title($color));?> apffw_color_term_<?php _e($term['term_id']);?> <?php if ($hide_next_term_li): ?>apffw_hidden_term<?php endif; ?>">


                <p class="apffw_tooltip">
                    <?php if ($show_tooltip): ?>
                        <span class="apffw_tooltip_data"><?php _e($term['name']);?> 
                            <?php _e($count_string);?><?php _e(!empty($term_desc) ? '<br /><i>' . $term_desc . '</i>' : '') ?>
                        </span>
                    <?php endif; ?>
                    <input type="checkbox" <?php checked(in_array($term['slug'], $current_request)) ?> id="<?php _e('apffw_' . $term['term_id'] . '_' . $inique_id);?>" class="apffw_color_term apffw_color_term_<?php _e($term['term_id']);?> <?php if (in_array($term['slug'], $current_request)): ?>checked<?php endif; ?>" data-color="<?php _e($color);?>" data-img="<?php _e($color_img);?>" data-tax="<?php _e($APFFW->check_slug($tax_slug));?>" name="<?php _e($term['slug']);?>" data-term-id="<?php _e($term['term_id']);?>" value="<?php _e($term['term_id']);?>" <?php _e(checked(in_array($term['slug'], $current_request)));?> /></p>

                <input type="hidden" value="<?php _e($term['name']);?>" data-anchor="apffw_n_<?php _e($APFFW->check_slug($tax_slug));?>_<?php _e($term['slug']);?>" />

                <?php
                if ($show_title) {
                    ?>
                    <span class="apffw_color_title <?php _e((in_array($term['slug'], $current_request)) ? "apffw_checkbox_label_selected" : "");?>"><?php _e($term['name']);?><?php _e($count_string);?></span>
                    <?php
                }
                ?>
            </li>
            <?php
            $terms_count_printed++;
        endforeach;
        
        if ($not_toggled_terms_count > 0 AND $terms_count_printed > $not_toggled_terms_count):
            ?>
            <li class="apffw_open_hidden_li"><?php APFFW_HELPER::draw_more_less_button('color') ?></li>
        <?php endif; ?>
    <?php endif; ?>
</ul>
<div class="clear clearfix"></div>
<?php
unset($_REQUEST['additional_taxes']);

