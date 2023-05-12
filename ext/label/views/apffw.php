<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
global $APFFW;
$_REQUEST['additional_taxes'] = $additional_taxes;
$_REQUEST['hide_terms_count_txt'] = isset($this->settings['hide_terms_count_txt']) ? $this->settings['hide_terms_count_txt'] : 0;

if(isset($_REQUEST['hide_terms_count_txt_short']) AND $_REQUEST['hide_terms_count_txt_short']!=-1){
    if((int)$_REQUEST['hide_terms_count_txt_short']==1){
        $_REQUEST['hide_terms_count_txt']=1;
    }else{
        $_REQUEST['hide_terms_count_txt']=0;
    }
}

if (!function_exists('apffw_draw_label_childs'))
{

    function apffw_draw_label_childs($taxonomy_info, $tax_slug, $childs, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos)
    {
        $do_not_show_childs = (int) apply_filters('apffw_terms_where_hidden_childs', $term_id);

        if ($do_not_show_childs == 1)
        {
            return "";
        }

        $current_request = array();
        global $APFFW;
        $request = $APFFW->get_request_data();
        if ($APFFW->is_isset_in_request_data($APFFW->check_slug($tax_slug)))
        {
            $current_request = $request[$APFFW->check_slug($tax_slug)];
            $current_request = explode(',', urldecode($current_request));
        }
        
        static $hide_childs = -1;
        if ($hide_childs == -1)
        {
            $hide_childs = (int) get_option('apffw_checkboxes_slide');
        }

        $hidden_terms = array();
        if (!isset($_REQUEST['apffw_shortcode_excluded_terms']))
        {
            if (isset($APFFW->settings['excluded_terms'][$tax_slug]))
            {
                $hidden_terms = explode(',', $APFFW->settings['excluded_terms'][$tax_slug]);
            }
        } else
        {
            $hidden_terms = explode(',', sanitize_text_field($_REQUEST['apffw_shortcode_excluded_terms']));
        }

        $childs = apply_filters('apffw_sort_terms_before_out', $childs, 'label');
        ?>
        <?php if (!empty($childs)): ?>
            <ul class="apffw_childs_list" <?php if ($hide_childs == 1): ?>style="display: none;"<?php endif; ?>>
                <?php foreach ($childs as $term) : $inique_id = uniqid(); ?>
                    <?php
                    $count_string = "";
                    $count = 0;
                    if (!in_array($term['slug'], $current_request))
                    {
                        if ($show_count)
                        {
                            if ($show_count_dynamic)
                            {
                                $count = $APFFW->dynamic_count($term, 'multi', sanitize_post($_REQUEST['additional_taxes']));
                            } else
                            {
                                $count = $term['count'];
                            }
                            $count_string = '<span>(' . $count . ')</span>';
                        }
                        
                        if ($hide_dynamic_empty_pos AND $count == 0)
                        {
                            continue;
                        }
                    }

                    if ($_REQUEST['hide_terms_count_txt'])
                    {
                        $count_string = "";
                    }

                    $inreverse=true;
                    if (isset($APFFW->settings['excluded_terms_reverse'][$tax_slug]) AND $APFFW->settings['excluded_terms_reverse'][$tax_slug])
                    {
                         $inreverse=!$inreverse;
                    }  
                    if (in_array($term['term_id'], $hidden_terms)==$inreverse)
                    {
                        continue;
                    }
                    ?>
                    <li <?php if ($APFFW->settings['dispay_in_row'][$tax_slug] AND empty($term['childs'])): ?>style="display: inline-block !important;"<?php endif; ?>>
                        <input type="checkbox" <?php if (!$count AND ! in_array($term['slug'], $current_request) AND $show_count): ?>disabled=""<?php endif; ?> id="<?php _e('apffw_' . $term['term_id'] . '_' . $inique_id);?>" class="apffw_label_term" data-tax="<?php _e($APFFW->check_slug($tax_slug));?>" name="<?php _e($term['slug']);?>" value="<?php _e($term['term_id']);?>" <?php _e(checked(in_array($term['slug'], $current_request)));?> />&nbsp;<label for="<?php _e('apffw_' . $term['term_id'] . '_' . $inique_id);?>" <?php if (checked(in_array($term['slug'], $current_request))):?>style="font-weight: bold;"<?php endif; ?>><?php
                            if (has_filter('apffw_before_term_name'))
                                _e(apply_filters('apffw_before_term_name', $term, $taxonomy_info));
                            else
                                _e($term['name']);
                            ?> <?php _e($count_string);?></label>
                        <?php
                        if (!empty($term['childs']))
                        {
                            apffw_draw_label_childs($taxonomy_info, $tax_slug, $term['childs'], $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
                        }
                        ?>
                        <input type="hidden" value="<?php _e($term['name']);?>" data-anchor="apffw_n_<?php _e($APFFW->check_slug($tax_slug));?>_<?php _e($term['slug']);?>" />

                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php
    }

}
?>
<ul class="apffw_list apffw_list_label">
    <?php
    $apffw_tax_values = array();
    $current_request = array();
    $request = $this->get_request_data();
    if ($this->is_isset_in_request_data($this->check_slug($tax_slug)))
    {
        $current_request = $request[$this->check_slug($tax_slug)];
        $current_request = explode(',', urldecode($current_request));
    }

    $hidden_terms = array();
    if (!isset($_REQUEST['apffw_shortcode_excluded_terms']))
    {
        if (isset($APFFW->settings['excluded_terms'][$tax_slug]))
        {
            $hidden_terms = explode(',', $APFFW->settings['excluded_terms'][$tax_slug]);
        }
    } else
    {
        $hidden_terms = explode(',', sanitize_text_field($_REQUEST['apffw_shortcode_excluded_terms']));
    }

    $not_toggled_terms_count = 0;
    if (isset($APFFW->settings['not_toggled_terms_count'][$tax_slug]))
    {
        $not_toggled_terms_count = intval($APFFW->settings['not_toggled_terms_count'][$tax_slug]);
    }

    $terms = apply_filters('apffw_sort_terms_before_out', $terms, 'label');
    $terms_count_printed = 0;
    $hide_next_term_li = false;
    ?>
    <?php if (!empty($terms)): ?>
        <?php foreach ($terms as $term) : $inique_id = uniqid(); ?>
            <?php
            $count_string = "";
            $count = 0;
            $term_slug = $term['slug'];
            if (!in_array($term_slug, $current_request))
            {
                if ($show_count)
                {
                    if ($show_count_dynamic)
                    {
                        $count = $this->dynamic_count($term, 'multi', sanitize_post($_REQUEST['additional_taxes']));
                    } else
                    {
                        $count = $term['count'];
                    }
                    $count_string = '<span class="apffw_label_count">' . $count . '</span>';
                }
                
                if ($hide_dynamic_empty_pos AND $count == 0)
                {
                    continue;
                }
            }

            if ($_REQUEST['hide_terms_count_txt'])
            {
                $count_string = "";
            }

            $inreverse=true;
            if (isset($APFFW->settings['excluded_terms_reverse'][$tax_slug]) AND $APFFW->settings['excluded_terms_reverse'][$tax_slug])
            {
                 $inreverse=!$inreverse;
            }  
            if (in_array($term['term_id'], $hidden_terms)==$inreverse)
            {
                continue;
            }

            if ($not_toggled_terms_count > 0 AND $terms_count_printed === $not_toggled_terms_count)
            {
                $hide_next_term_li = true;
            }

            $checked = in_array($term_slug, $current_request);
            ?>
            <li class="apffw_term_<?php _e($term['term_id']);?> <?php if ($hide_next_term_li): ?>apffw_hidden_term<?php endif; ?>" style="<?php if ($this->settings['dispay_in_row'][$tax_slug] AND empty($term['childs'])): ?>display: inline-block !important;<?php endif; ?>">
                <?php _e($count_string);?>
                <span class="checkbox apffw_label_term <?php if ($checked) _e('checked'); ?>">
                    <?php _e($term['name']);?>
                    <input style="display: none;" type="checkbox" <?php if (!$count AND ! in_array($term_slug, $current_request) AND $show_count): ?>disabled=""<?php endif; ?> id="<?php _e('apffw_' . $term['term_id'] . '_' . $inique_id);?>" class="apffw_label_term apffw_label_term_<?php _e($term['term_id']);?>" data-tax="<?php _e($this->check_slug($tax_slug));?>" name="<?php _e($term_slug);?>" data-name="<?php _e($term['name']);?>" data-term-id="<?php _e($term['term_id']);?>" value="<?php _e($term['term_id']);?>" <?php _e(checked($checked));?> />
                    <input type="hidden" value="<?php _e($term['name']);?>" data-anchor="apffw_n_<?php _e($this->check_slug($tax_slug));?>_<?php _e($term['slug']);?>" />
                </span>
                <?php
                if (!empty($term['childs'])){                    
                }
                ?>
            </li>
            <?php
            $terms_count_printed++;
        endforeach;
        
        if ($not_toggled_terms_count > 0 AND $terms_count_printed > $not_toggled_terms_count):
            ?>
            <li class="apffw_open_hidden_li"><?php APFFW_HELPER::draw_more_less_button('label') ?></li>
            <?php endif; ?>
        <?php endif; ?>
</ul>
<?php
unset($_REQUEST['additional_taxes']);
