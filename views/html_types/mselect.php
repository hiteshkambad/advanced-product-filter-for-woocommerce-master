<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
global $APFFW;
$collector = array();
$_REQUEST['additional_taxes'] = $additional_taxes;
$_REQUEST['hide_terms_count_txt'] = isset($this->settings['hide_terms_count_txt']) ? $this->settings['hide_terms_count_txt'] : 0;

if(isset($_REQUEST['hide_terms_count_txt_short']) AND $_REQUEST['hide_terms_count_txt_short']!=-1){
    if((int)$_REQUEST['hide_terms_count_txt_short']==1){
        $_REQUEST['hide_terms_count_txt']=1;
    }else{
        $_REQUEST['hide_terms_count_txt']=0;
    }
}

$apffw_hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos');
if (!function_exists('apffw_draw_mselect_childs'))
{

    function apffw_draw_mselect_childs(&$collector, $taxonomy_info, $term_id, $tax_slug, $childs, $level, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos)
    {
        $do_not_show_childs = (int) apply_filters('apffw_terms_where_hidden_childs', $term_id);

        if ($do_not_show_childs == 1)
        {
            return "";
        }

        

        global $APFFW;
        $request = $APFFW->get_request_data();
        $apffw_hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos');

        $current_request = array();
        if ($APFFW->is_isset_in_request_data($APFFW->check_slug($tax_slug)))
        {
            $current_request = $request[$APFFW->check_slug($tax_slug)];
            $current_request = explode(',', urldecode($current_request));
        }

        //excluding hidden terms
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

        $childs = apply_filters('apffw_sort_terms_before_out', $childs, 'mselect');
        ?>
        <?php if (!empty($childs)): ?>
            <?php foreach ($childs as $term) : ?>
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
                        $count_string = '(' . $count . ')';
                    }
                    //+++
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
                <option <?php if ($show_count AND $count == 0 AND ! in_array($term['slug'], $current_request)): ?>disabled=""<?php endif; ?> value="<?php _e($term['slug']);?>" <?php _e(selected(in_array($term['slug'], $current_request)));?> class="apffw-padding-<?php _e($level);?>"><?php /* str_repeat('&nbsp;&nbsp;&nbsp;', $level) */ ?><?php
                    if (has_filter('apffw_before_term_name'))
                        _e(apply_filters('apffw_before_term_name', $term, $taxonomy_info));
                    else
                        _e($term['name']);
                    ?> <?php _e($count_string);?></option>
                <?php
                if (!isset($collector[$tax_slug]))
                {
                    $collector[$tax_slug] = array();
                }
                $collector[$tax_slug][] = array('name' => $term['name'], 'slug' => $term['slug'], 'term_id' => $term['term_id']);

                if (!empty($term['childs']))
                {
                    apffw_draw_mselect_childs($collector, $taxonomy_info, $term['term_id'], $tax_slug, $term['childs'], $level + 1, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
                }
                ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php
    }

}
?>
<select class="apffw_mselect apffw_mselect_<?php _e($tax_slug);?>" data-placeholder="<?php _e(APFFW_HELPER::wpml_translate($taxonomy_info));?>" multiple="" size="<?php _e($this->is_apffw_use_chosen() ? 1 : '');?>" name="<?php _e($this->check_slug($tax_slug));?>">
    <option value="0"></option>
    <?php
    $apffw_tax_values = array();
    $current_request = array();
    $request = $this->get_request_data();
    $shown_options_tags = 0;
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

    $terms = apply_filters('apffw_sort_terms_before_out', $terms, 'mselect');
    ?>
    <?php if (!empty($terms)): ?>
        <?php foreach ($terms as $term) : ?>
            <?php
            $count_string = "";
            $count = 0;
            if (!in_array($term['slug'], $current_request))
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
                    $count_string = '(' . $count . ')';
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
            <option <?php if ($show_count AND $count == 0 AND ! in_array($term['slug'], $current_request)): ?>disabled=""<?php endif; ?> value="<?php _e($term['slug']);?>" <?php _e(selected(in_array($term['slug'], $current_request)));?>><?php
                if (has_filter('apffw_before_term_name'))
                    _e(apply_filters('apffw_before_term_name', $term, $taxonomy_info));
                else
                    _e($term['name']);
                ?> <?php _e($count_string);?></option>
            <?php
            if (!isset($collector[$tax_slug]))
            {
                $collector[$tax_slug] = array();
            }

            $collector[$tax_slug][] = array('name' => $term['name'], 'slug' => $term['slug'], 'term_id' => $term['term_id']);

            

            if (!empty($term['childs']))
            {
                apffw_draw_mselect_childs($collector, $taxonomy_info, $term['term_id'], $tax_slug, $term['childs'], 1, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
            }

            $shown_options_tags++;
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
<?php if ($shown_options_tags == 0): ?>
	<input type="hidden" class="apffw_hide_empty_container_ms" value=".apffw_container_<?php _e($tax_slug);?>">
<?php endif; ?>

<?php
if (!empty($collector))
{
    foreach ($collector as $ts => $values)
    {
        if (!empty($values))
        {
            foreach ($values as $value)
            {
                ?>
                <input type="hidden" value="<?php _e($value['name']);?>" data-anchor="apffw_n_<?php _e($this->check_slug($ts));?>_<?php _e($value['slug']);?>" />
                <?php
            }
        }
    }
}
unset($_REQUEST['additional_taxes']);
