<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div data-css-class="apffw_textinput_container" class="apffw_textinput_container apffw_container  apffw_container_<?php _e("textinput_".$meta_key);?>">
    <div class="apffw_container_overlay_item"></div>
    <div class="apffw_container_inner">
        <?php
        global $APFFW;
        $apffw_text = '';
        $request = $APFFW->get_request_data();

        if (isset($request['textinput_'.$meta_key]))
        {
            $apffw_text = $request['textinput_'.$meta_key];
        }
        
        if (!isset($placeholder))
        {
            $p = esc_html__('enter a text here ...', 'apffw-products-filter');
        }

        if (isset($options['title']) AND ! isset($placeholder))
        {
            if (!empty($options['title']))
            {
                $p = $options['title'];
                $p = APFFW_HELPER::wpml_translate(null, $p);
                $p = esc_html__($p, 'apffw-products-filter');
            }

        }
        
        $unique_id = uniqid('apffw_meta_filter_');
        ?>

        <div class="apffw_show_textinput_container ">
            <img width="36" class="apffw_show_text_search_loader" style="display: none;" src="<?php _e($loader_img);?>" alt="loader" />
            <a href="javascript:void(0);" data-uid="<?php _e($unique_id);?>" class="apffw_textinput_go <?php _e($unique_id);?>"></a>
            <input type="search" class="apffw_meta_filter_textinput <?php _e($unique_id);?>" id="<?php _e($unique_id);?>" data-uid="<?php _e($unique_id);?>" data-auto_res_count="<?php _e((isset($auto_res_count) ? $auto_res_count : 0));?>" data-auto_search_by="<?php _e((isset($auto_search_by) ? $auto_search_by : ""));?>" placeholder="<?php _e((isset($placeholder) ? $placeholder : $p));?>" name="textinput_<?php _e($meta_key);?>" value="<?php _e($apffw_text);?>" />
        </div>

    </div>
</div>
