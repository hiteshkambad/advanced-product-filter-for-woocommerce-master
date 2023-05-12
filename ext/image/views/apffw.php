<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
global $APFFW;
$show_count = get_option('apffw_show_count', 0);
$show_count_dynamic = get_option('apffw_show_count_dynamic', 0);
$hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos', 0);
$apffw_autosubmit = get_option('apffw_autosubmit', 0);
$image_type = "checkbox";
if (isset($APFFW->settings['as_radio'][$tax_slug]) AND $APFFW->settings['as_radio'][$tax_slug]) {
    $image_type = "radio";
}

$add_description = apply_filters('apffw_image_allow_term_desc', true, $tax_slug);
?>

<ul class = "apffw_list apffw_list_image" data-type="<?php _e($image_type);?>">
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

    

    $terms = apply_filters('apffw_sort_terms_before_out', $terms, 'image');
    $terms_count_printed = 0;
    $hide_next_term_li = false;
    ?>
    <?php if (!empty($terms)): ?>
        <?php foreach ($terms as $term) : $inique_id = uniqid(); ?>
            <?php
            $inreverse = true;
            if (isset($APFFW->settings['excluded_terms_reverse'][$tax_slug]) AND $APFFW->settings['excluded_terms_reverse'][$tax_slug]) {
                $inreverse = !$inreverse;
            }
            if (in_array($term['term_id'], $hidden_terms) == $inreverse) {
                continue;
            }

            $term_key = 'images_term_' . $term['term_id'];
            $images = isset($apffw_settings[$term_key]) ? $apffw_settings[$term_key] : array();

            $image = '';

            if (empty($image = apply_filters('apffw_taxonomy_image', $image, $term))) {
                if (isset($images['image_url']) AND!empty($images['image_url'])) {
                    $image = $images['image_url'];
                } else {
                    continue;
                }

                if ($images['image_url'] == 'hide') {
                    continue;
                }
            }

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


            if ($add_description) {
                $term_desc = strip_tags(term_description($term['term_id'], $term['taxonomy']));
            }
            

            if (isset($images['image_styles'])) {
                $styles = trim($images['image_styles']);
            }

            

            if ($not_toggled_terms_count > 0 AND $terms_count_printed === $not_toggled_terms_count) {
                $hide_next_term_li = true;
            }
            ?>
            <li class="apffw_image_term_li_<?php _e($term['term_id']);?> <?php if ($hide_next_term_li): ?>apffw_hidden_term<?php endif; ?>">
                <p class="apffw_tooltip"><span class="apffw_tooltip_data"><?php _e($term['name']);?> <?php _e($count_string);?><?php _e((!empty($term_desc) ? '<br /><i>' . $term_desc . '</i>' : ''));?></span>
                    <input type="checkbox" data-styles="<?php _e($styles);?>" <?php checked(in_array($term['slug'], $current_request)) ?> id="<?php _e('apffw_' . $term['term_id'] . '_' . $inique_id);?>" class="apffw_image_term apffw_image_term_<?php _e($term['term_id']);?> <?php if (in_array($term['slug'], $current_request)): ?>checked<?php endif; ?>" data-image="<?php _e($image);?>" data-tax="<?php _e($APFFW->check_slug($tax_slug));?>" name="<?php _e($term['slug']);?>" value="<?php _e($term['term_id']);?>" data-term-id="<?php _e($term['term_id']);?>" <?php _e(checked(in_array($term['slug'], $current_request)));?> /></p>
                <input type="hidden" value="<?php _e($term['name']);?>" data-anchor="apffw_n_<?php _e($APFFW->check_slug($tax_slug));?>_<?php _e($term['slug']);?>" />
                <?php if (isset($APFFW->settings['show_title'][$tax_slug]) AND $APFFW->settings['show_title'][$tax_slug]): ?>
                    <p class="apffw_image_text_term">
                        <?php _e($term['name']);?> <?php _e($count_string);?>
                    </p>
                <?php endif; ?>
            </li>
            <?php
            $terms_count_printed++;
        endforeach;
        
        if ($not_toggled_terms_count > 0 AND $terms_count_printed > $not_toggled_terms_count):
            ?>
            <li class="apffw_open_hidden_li"><?php APFFW_HELPER::draw_more_less_button('image') ?></li>
        <?php endif; ?>
    <?php endif; ?>
</ul>
<div class="clear clearfix"></div>
<?php
unset($_REQUEST['additional_taxes']);

