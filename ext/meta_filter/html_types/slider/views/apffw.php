<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
global $APFFW;

$request = $APFFW->get_request_data();
$current_request_txt="";
if ($APFFW->is_isset_in_request_data("slider_".$meta_key))
{
    $current_request_txt = $request["slider_".$meta_key];
    $current_request = explode('^', urldecode($current_request_txt));
}
else{
    $current_request=array();
}



$min=0;
$max=100;
$min_max=explode("^",$range,2);
if(count($min_max)>1){
    $min= floatval($min_max[0]);
    $max= floatval($min_max[1]);   
}

$min_value=$min;
$max_value=$max;
if(!empty($current_request)){
    $min_value= floatval($current_request[0]);
    $max_value= floatval($current_request[1]);
    if($min_value<$min){
        $min_value=$min;
    }
    if($max_value>$max){
        $max_value=$max;
    }    
}

 $count = 0;
 $show=true;
 $hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos', 0);
 if (empty($current_request))
 {
     if ( $hide_dynamic_empty_pos)
     {
         $meta_field=array(
             'key'=>$meta_key,
             'value'=>array( $min, $max ),
         );
         $count_data=array();
         $count = $APFFW->dynamic_count(array(), 'slider', (isset($_REQUEST['additional_taxes']))?sanitize_post($_REQUEST['additional_taxes']): "",$meta_field);
     }
     
     if ($hide_dynamic_empty_pos AND $count == 0)
     {
         $show=false;
     }
 }

$show_title_label= (isset($meta_settings['show_title_label']))?$meta_settings['show_title_label']:1;
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
    if($block_is_closed){
        $css_classes .= " apffw_closed_block";
    }else{
        $css_classes = str_replace('apffw_closed_block', '', $css_classes);
    }
}

if($show):
$top_panel_txt="";    
$top_panel_txt= APFFW_HELPER::wpml_translate(null,$options['title']);
$top_panel_txt.=sprintf(":%s %s %s",$meta_settings['prefix'],str_replace("^", "-",$current_request_txt),$meta_settings['postfix'])
?>
<div data-css-class="apffw_meta_slider_container" class="apffw_meta_slider_container apffw_container apffw_container_<?php _e("slider_".$meta_key);?>">
    <div class="apffw_container_inner">
        <div class="apffw_container_inner apffw_container_inner_meta_slider">
            <?php if ($show_title_label) {
                ?>
            <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
            <?php _e(APFFW_HELPER::wpml_translate(null,$options['title']));?>
            <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate(null,$options['title']),$tooltip_text));?>    
            <?php APFFW_HELPER::draw_title_toggle($show_toggle, $block_is_closed); ?></<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                <?php
            }?>
            <div class="<?php _e($css_classes);?>">
                <input class="apffw_metarange_slider" name="<?php _e("slider_".$meta_key);?>" data-min="<?php _e($min);?>" data-max="<?php _e($max);?>" data-min-now="<?php _e($min_value);?>" data-max-now="<?php _e($max_value);?>" data-step="<?php _e($meta_settings['step']);?>" data-slider-prefix="<?php _e($meta_settings['prefix']);?>" data-slider-postfix="<?php _e($meta_settings['postfix']);?>" value="" />
            </div>
             <input type="hidden" value="<?php _e($top_panel_txt);?>" data-anchor="apffw_n_<?php _e("slider_".$meta_key);?>_<?php _e($current_request_txt);?>" />
        </div>
    </div>
</div>
<?php endif; ?>
