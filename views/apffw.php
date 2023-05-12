<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php

$args = array();
$args['show_count'] = get_option('apffw_show_count', 0);
if ($dynamic_recount == -1) {
    $args['show_count_dynamic'] = get_option('apffw_show_count_dynamic', 0);
} else {
    $args['show_count_dynamic'] = $dynamic_recount;
}
$args['hide_dynamic_empty_pos'] = get_option('apffw_hide_dynamic_empty_pos', 0);
$args['apffw_autosubmit'] = $autosubmit;


$_REQUEST['tax_only'] = $tax_only;
$_REQUEST['tax_exclude'] = $tax_exclude;
$_REQUEST['by_only'] = $by_only;

if (!function_exists('apffw_show_btn')) {

    function apffw_show_btn($autosubmit = 1, $ajax_redraw = 0) {
        ?>
        <div class="apffw_submit_search_form_container">

            <?php			
            global $APFFW;

			$is_searh_active = $APFFW->is_isset_in_request_data($APFFW->get_sapffw_search_slug());
			$request = $APFFW->get_request_data(true);

			if($is_searh_active AND ($request AND  is_array($request))){
				$not_search_request =[$APFFW->get_sapffw_search_slug(), 'paged', 'really_curr_tax']; 
				$request = array_diff(array_keys($request), $not_search_request);
				
				if(!count($request)){
					$is_searh_active = false;
				}
			}		
			
            if ($is_searh_active  OR $APFFW->is_isset_in_request_data('min_price') OR ( class_exists("APFFW_EXT_TURBO_MODE") AND isset($APFFW->settings["apffw_turbo_mode"]["enable"]) AND $APFFW->settings["apffw_turbo_mode"]["enable"] )):
				global $apffw_link;
                ?>

                <?php
                $apffw_reset_btn_txt = get_option('apffw_reset_btn_txt', '');
                if (empty($apffw_reset_btn_txt)) {
                    $apffw_reset_btn_txt = esc_html__('Reset', 'apffw-products-filter');
                }
                $apffw_reset_btn_txt = APFFW_HELPER::wpml_translate(null, $apffw_reset_btn_txt);
                ?>

                <?php if ($apffw_reset_btn_txt != 'none'): ?>
                    <button  class="button apffw_reset_search_form" data-link="<?php _e($apffw_link);?>"><?php _e($apffw_reset_btn_txt);?></button>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!$autosubmit OR $ajax_redraw): ?>
                <?php
                $apffw_filter_btn_txt = get_option('apffw_filter_btn_txt', '');
                if (empty($apffw_filter_btn_txt)) {
                    $apffw_filter_btn_txt = esc_html__('Filter', 'apffw-products-filter');
                }

                $apffw_filter_btn_txt = APFFW_HELPER::wpml_translate(null, $apffw_filter_btn_txt);
                ?>
                <button class="button apffw_submit_search_form"><?php _e($apffw_filter_btn_txt);?></button>
            <?php endif; ?>

        </div>
        <?php
    }

}

if (!function_exists('apffw_only')) {

    function apffw_only($key_slug, $type = 'taxonomy') {

        switch ($type) {
            case 'taxonomy':

                if (!empty($_REQUEST['tax_only'])) {
                    if (!in_array($key_slug, $_REQUEST['tax_only'])) {
                        return FALSE;
                    }
                }

                if (!empty($_REQUEST['tax_exclude'])) {
                    if (in_array($key_slug, $_REQUEST['tax_exclude'])) {
                        return FALSE;
                    }
                }

                break;

            case 'item':
                if (!empty($_REQUEST['by_only'])) {
                    if (!in_array($key_slug, $_REQUEST['by_only'])) {
                        return FALSE;
                    }
                }
                if (!empty($_REQUEST['tax_exclude'])) {
                    if (in_array($key_slug, $_REQUEST['tax_exclude'])) {
                        return FALSE;
                    }
                }
                break;
        }


        return TRUE;
    }

}

if (!function_exists('apffw_print_tax')) {

    function get_order_by_tax_only($t_order, $t_only) {
        $temp_array = array_intersect($t_order, $t_only);
        $i = 0;
        foreach ($temp_array as $key => $val) {
            $t_order[$key] = $t_only[$i];
            $i++;
        }
        return $t_order;
    }

}

if (!function_exists('apffw_print_tax')) {

    function apffw_print_tax($taxonomies, $tax_slug, $terms, $exclude_tax_key, $taxonomies_info, $additional_taxes, $apffw_settings, $args, $counter) {

        global $APFFW;

        if ($exclude_tax_key == $tax_slug) {
            if (empty($terms)) {
                return;
            }
        }

        

        if (!apffw_only($tax_slug, 'taxonomy')) {
            return;
        }

        


        $args['taxonomy_info'] = $taxonomies_info[$tax_slug];
        $args['tax_slug'] = $tax_slug;
        $args['terms'] = $terms;
        $args['all_terms_hierarchy'] = $taxonomies[$tax_slug];
        $args['additional_taxes'] = $additional_taxes;

        
        $apffw_container_styles = "";
        if ($apffw_settings['tax_type'][$tax_slug] == 'radio' OR $apffw_settings['tax_type'][$tax_slug] == 'checkbox') {
            if ($APFFW->settings['tax_block_height'][$tax_slug] > 0) {
                $apffw_container_styles = "max-height:{$APFFW->settings['tax_block_height'][$tax_slug]}px; overflow-y: auto;";
            }
        }
        
        
        $primax_class = sanitize_key(APFFW_HELPER::wpml_translate($taxonomies_info[$tax_slug]));
        ?>
        <div data-css-class="apffw_container_<?php _e($tax_slug);?>" class="apffw_container apffw_container_<?php _e($apffw_settings['tax_type'][$tax_slug]);?> apffw_container_<?php _e($tax_slug);?> apffw_container_<?php _e($counter);?> apffw_container_<?php _e($primax_class);?>">
            <div class="apffw_container_overlay_item"></div>
            <div class="apffw_container_inner apffw_container_inner_<?php _e($primax_class);?>">
                <?php
                $css_classes = "apffw_block_html_items";
                $show_toggle = 0;
                if (isset($APFFW->settings['show_toggle_button'][$tax_slug])) {
                    $show_toggle = (int) $APFFW->settings['show_toggle_button'][$tax_slug];
                }
                $tooltip_text = "";
                if (isset($APFFW->settings['tooltip_text'][$tax_slug])) {
                    $tooltip_text = $APFFW->settings['tooltip_text'][$tax_slug];
                }
                
                $search_query = $APFFW->get_request_data();
                $block_is_closed = true;
                if (in_array($tax_slug, array_keys($search_query))) {
                    $block_is_closed = false;
                }
                if ($show_toggle === 1 AND!in_array($tax_slug, array_keys($search_query))) {
                    $css_classes .= " apffw_closed_block";
                }

                if ($show_toggle === 2 AND!in_array($tax_slug, array_keys($search_query))) {
                    $block_is_closed = false;
                }

                if (in_array($show_toggle, array(1, 2))) {
                    $block_is_closed = apply_filters('apffw_block_toggle_state', $block_is_closed);
                    if ($block_is_closed) {
                        $css_classes .= " apffw_closed_block";
                    } else {
                        $css_classes = str_replace('apffw_closed_block', '', $css_classes);
                    }
                }
                
                switch ($apffw_settings['tax_type'][$tax_slug]) {
                    case 'checkbox':
                        if ($APFFW->settings['show_title_label'][$tax_slug]) {
                            ?>
                            <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php _e(APFFW_HELPER::wpml_translate($taxonomies_info[$tax_slug]));?>
                            <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate($taxonomies_info[$tax_slug]), $tooltip_text));?>
                            <?php APFFW_HELPER::draw_title_toggle($show_toggle, $block_is_closed); ?>
                            </<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php
                        }

                        if (!empty($apffw_container_styles)) {
                            $css_classes .= " apffw_section_scrolled";
                        }
                        ?>
                        <div class="<?php _e($css_classes);?>" <?php if (!empty($apffw_container_styles)): ?>style="<?php _e($apffw_container_styles);?>"<?php endif; ?>>
                            <?php
                            _e($APFFW->render_html(apply_filters('apffw_html_types_view_checkbox', APFFW_PATH . 'views/html_types/checkbox.php'), $args));
                            ?>
                        </div>
                        <?php
                        break;
                    case 'select':
                        if ($APFFW->settings['show_title_label'][$tax_slug]) {
                            ?>
                            <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php _e(APFFW_HELPER::wpml_translate($taxonomies_info[$tax_slug]));?>
                            <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate($taxonomies_info[$tax_slug]), $tooltip_text));?>
                            <?php APFFW_HELPER::draw_title_toggle($show_toggle, $block_is_closed); ?></<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php
                        }
                        ?>
                        <div class="<?php _e($css_classes);?>">
                            <?php
                            _e($APFFW->render_html(apply_filters('apffw_html_types_view_select', APFFW_PATH . 'views/html_types/select.php'), $args));
                            ?>
                        </div>
                        <?php
                        break;
                    case 'mselect':
                        if ($APFFW->settings['show_title_label'][$tax_slug]) {
                            ?>
                            <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php _e(APFFW_HELPER::wpml_translate($taxonomies_info[$tax_slug]));?>
                            <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate($taxonomies_info[$tax_slug]), $tooltip_text));?>
                            <?php APFFW_HELPER::draw_title_toggle($show_toggle, $block_is_closed); ?></<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php
                        }
                        ?>
                        <div class="<?php _e($css_classes);?>">
                            <?php
                            _e($APFFW->render_html(apply_filters('apffw_html_types_view_mselect', APFFW_PATH . 'views/html_types/mselect.php'), $args));
                            ?>
                        </div>
                        <?php
                        break;

                    default:
                        if ($APFFW->settings['show_title_label'][$tax_slug]) {
                            $title = APFFW_HELPER::wpml_translate($taxonomies_info[$tax_slug]);
                            $title = explode('^', $title); 
                            if (isset($title[1])) {
                                $title = $title[1];
                            } else {
                                $title = $title[0];
                            }
                            ?>
                            <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php _e($title);?>
                            <?php _e(APFFW_HELPER::draw_tooltipe($title, $tooltip_text));?>
                            <?php APFFW_HELPER::draw_title_toggle($show_toggle, $block_is_closed); ?>
                            </<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php
                        }

                        if (!empty($apffw_container_styles)) {
                            $css_classes .= " apffw_section_scrolled";
                        }
                        ?>

                        <div class="<?php _e($css_classes);?>" <?php if (!empty($apffw_container_styles)): ?>style="<?php _e($apffw_container_styles);?>"<?php endif; ?>>
                            <?php
                            if (!empty(APFFW_EXT::$includes['taxonomy_type_objects'])) {
                                $is_custom = false;
                                foreach (APFFW_EXT::$includes['taxonomy_type_objects'] as $obj) {
                                    if ($obj->html_type == $apffw_settings['tax_type'][$tax_slug]) {
                                        $is_custom = true;
                                        $args['apffw_settings'] = $apffw_settings;
                                        $args['taxonomies_info'] = $taxonomies_info;
                                        _e($APFFW->render_html($obj->get_html_type_view(), $args));
                                        break;
                                    }
                                }


                                if (!$is_custom) {
                                    _e($APFFW->render_html(apply_filters('apffw_html_types_view_radio', APFFW_PATH . 'views/html_types/radio.php'), $args));
                                }
                            } else {
                                _e($APFFW->render_html(apply_filters('apffw_html_types_view_radio', APFFW_PATH . 'views/html_types/radio.php'), $args));
                            }
                            ?>

                        </div>
                        <?php
                        break;
                }
                ?>

                <input type="hidden" name="apffw_t_<?php _e($tax_slug);?>" value="<?php _e($taxonomies_info[$tax_slug]->labels->name);?>" /><!-- for red button search nav panel -->

            </div>
        </div>
        <?php
    }

}

if (!function_exists('apffw_print_item_by_key')) {

    function apffw_print_item_by_key($key, $apffw_settings, $additional_taxes) {

        if (!apffw_only($key, 'item')) {
            return;
        }

        

        global $APFFW;
        switch ($key) {
            case 'by_price':
                $price_filter = 0;
                if (isset($APFFW->settings['by_price']['show'])) {
                    $price_filter = (int) $APFFW->settings['by_price']['show'];
                }
                $tooltip_text = "";
                if (isset($APFFW->settings['by_price']['tooltip_text'])) {
                    $tooltip_text = $APFFW->settings['by_price']['tooltip_text'];
                }
                ?>

                <?php if ($price_filter == 1): 
				$price_woo_slider="";	
				ob_start();
				APFFW_HELPER::price_filter($additional_taxes);
				$price_woo_slider = ob_get_clean();	
				if(empty($price_woo_slider)){
					break;
				}
					?>
                    <div data-css-class="apffw_price_search_container" class="apffw_price_search_container apffw_container apffw_price_filter">
                        <div class="apffw_container_overlay_item"></div>
                        <div class="apffw_container_inner">
                            <div class="woocommerce widget_price_filter">
                                <?php if (isset($APFFW->settings['by_price']['title_text']) AND!empty($APFFW->settings['by_price']['title_text'])): ?>
                                    <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                                    <?php _e(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']));?>
                                    <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']), $tooltip_text));?>
                                    </<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                                <?php endif; ?>
                                <?php _e($price_woo_slider);?>
                            </div>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                <?php endif; ?>

                <?php if ($price_filter == 2): ?>
                    <div data-css-class="apffw_price2_search_container" class="apffw_price2_search_container apffw_container apffw_price_filter">
                        <div class="apffw_container_overlay_item"></div>
                        <div class="apffw_container_inner">
                            <?php if (isset($APFFW->settings['by_price']['title_text']) AND!empty($APFFW->settings['by_price']['title_text'])): ?>
                                <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                                <?php _e(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']));?>
                                <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']), $tooltip_text));?>
                                </<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php endif; ?>

                            <?php _e(do_shortcode('[apffw_price_filter type="select" additional_taxes="' . $additional_taxes . '"]'));?>

                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($price_filter == 3): 
					$price_woo_slider="";	
					$price_woo_slider = do_shortcode('[apffw_price_filter type="slider" additional_taxes="' . $additional_taxes . '"]');	
					if(empty(trim($price_woo_slider))){
						break;
					}	
				?>
                    <div data-css-class="apffw_price3_search_container" class="apffw_price3_search_container apffw_container apffw_price_filter">
                        <div class="apffw_container_overlay_item"></div>
                        <div class="apffw_container_inner">
                            <?php if (isset($APFFW->settings['by_price']['title_text']) AND!empty($APFFW->settings['by_price']['title_text'])): ?>
                                <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                                <?php _e(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']));?>
                                <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']), $tooltip_text));?>
                                </<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php endif; ?>

                            <?php _e($price_woo_slider);?>

                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($price_filter == 4): ?>
                    <div data-css-class="apffw_price4_search_container" class="apffw_price4_search_container apffw_container apffw_price_filter">
                        <div class="apffw_container_overlay_item"></div>
                        <div class="apffw_container_inner">
                            <?php if (isset($APFFW->settings['by_price']['title_text']) AND!empty($APFFW->settings['by_price']['title_text'])): ?>
                                <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                                <?php _e(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']));?>
                                <?php _e(APFFW_HELPER::draw_tooltipe(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']), $tooltip_text));?>
                                </<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php endif; ?>

                            <?php _e(do_shortcode('[apffw_price_filter type="text" additional_taxes="' . $additional_taxes . '"]'));?>

                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($price_filter == 5): ?>
                    <div data-css-class="apffw_price5_search_container" class="apffw_price5_search_container apffw_container apffw_price_filter">
                        <div class="apffw_container_overlay_item"></div>
                        <div class="apffw_container_inner">
                            <?php
                            $css_classes = "apffw_block_html_items";
                            $show_toggle = 0;
                            if (isset($APFFW->settings[$key]['show_toggle_button'])) {
                                $show_toggle = (int) $APFFW->settings[$key]['show_toggle_button'];
                            }
                            $tooltip_text = "";
                            if (isset($APFFW->settings['tooltip_text'][$key])) {
                                $tooltip_text = $APFFW->settings['tooltip_text'][$key];
                            }
                            
                            $search_query = $APFFW->get_request_data();
                            $block_is_closed = true;
                            if (in_array("min_price", array_keys($search_query))) {
                                $block_is_closed = false;
                            }
                            if ($show_toggle === 1 AND!in_array("min_price", array_keys($search_query))) {
                                $css_classes .= " apffw_closed_block";
                            }

                            if ($show_toggle === 2 AND!in_array("min_price", array_keys($search_query))) {
                                $block_is_closed = false;
                            }

                            if (in_array($show_toggle, array(1, 2))) {
                                $block_is_closed = apply_filters('apffw_block_toggle_state', $block_is_closed);
                                if ($block_is_closed) {
                                    $css_classes .= " apffw_closed_block";
                                } else {
                                    $css_classes = str_replace('apffw_closed_block', '', $css_classes);
                                }
                            }
                            ?>
                            <?php if (isset($APFFW->settings['by_price']['title_text']) AND!empty($APFFW->settings['by_price']['title_text'])): ?>
                                <<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                                <?php _e(APFFW_HELPER::wpml_translate(null, $APFFW->settings['by_price']['title_text']));?>
                                <?php APFFW_HELPER::draw_title_toggle($show_toggle, $block_is_closed); ?>
                                </<?php _e(apply_filters('apffw_title_tag', 'h4'));?>>
                            <?php endif; ?>
                            <div class="<?php _e($css_classes);?>" <?php if (!empty($apffw_container_styles)): ?>style="<?php _e($apffw_container_styles);?>"<?php endif; ?>>
                                <?php _e(do_shortcode('[apffw_price_filter type="radio" additional_taxes="' . $additional_taxes . '"]'));?>
                            </div>

                        </div>
                    </div>
                <?php endif; ?>

                <?php
                break;

            default:
                do_action('apffw_print_html_type_' . $key);
                break;
        }
    }

}
?>


<?php if ($autohide): ?>
    <div>
        <?php

        
        $apffw_auto_hide_button_txt = '';
        if (isset($this->settings['apffw_auto_hide_button_txt'])) {
            $apffw_auto_hide_button_txt = APFFW_HELPER::wpml_translate(null, $this->settings['apffw_auto_hide_button_txt']);
        }
        ?>
        <a href="javascript:void(0);" class="apffw_show_auto_form apffw_btn_default <?php if (isset($this->settings['apffw_auto_hide_button_img']) AND $this->settings['apffw_auto_hide_button_img'] == 'none') _e('apffw_show_auto_form_txt'); ?>"><?php _e(esc_html__($apffw_auto_hide_button_txt));?></a><br />
        <!-------------------- inline css for js anim ----------------------->
        <div class="apffw_auto_show apffw_overflow_hidden" style="opacity: 0; height: 1px;">
            <div class="apffw_auto_show_indent apffw_overflow_hidden">
         <?php endif; 

		$apffw_class="";
		if (wp_is_mobile() &&  (isset($mobile_mode) && $mobile_mode==1) && isset($sid)) {
			$apffw_class = 'apffw_hide_filter';
		}			

		?>
				

				<div class="apffw <?php if (!empty($sid)): ?>apffw_sid apffw_sid_<?php _e($sid);?><?php endif; ?> <?php _e($apffw_class);?>" <?php if (!empty($sid)): ?>data-sid="<?php _e($sid);?>"<?php endif; ?> data-shortcode="<?php _e(isset($_REQUEST['apffw_shortcode_txt']) ? sanitize_text_field($_REQUEST['apffw_shortcode_txt']) : 'apffw') ?>" data-redirect="<?php _e($redirect);?>" data-autosubmit="<?php _e($autosubmit);?>" data-ajax-redraw="<?php _e($ajax_redraw);?>">
		<?php	
		 
			if (wp_is_mobile() &&  (isset($mobile_mode) && $mobile_mode) && isset($sid)) {
				$image_mb_open = (isset($this->settings['image_mobile_behavior_open']))?$this->settings['image_mobile_behavior_open']:'';
				$image_mb_close = (isset($this->settings['image_mobile_behavior_close']))?$this->settings['image_mobile_behavior_close']:'';
				if($image_mb_open != -1 && empty($image_mb_open)) {
					$image_mb_open = APFFW_LINK . "img/open_filter.png";
				}
				if($image_mb_close != -1 && empty($image_mb_close)) {
					$image_mb_close = APFFW_LINK . "img/close_filter.png";
				}	
				$text_mb_open = (isset($this->settings['text_mobile_behavior_open']))? $this->settings['text_mobile_behavior_open']:esc_html__('Open filter', 'apffw-products-filter');
				$text_mb_close = (isset($this->settings['text_mobile_behavior_close']))? $this->settings['text_mobile_behavior_close']:esc_html__('Close filter', 'apffw-products-filter');			

				?>
				<div class="apffw_show_mobile_filter" data-sid="<?php _e($sid);?>">
					<?php if($image_mb_open!=-1) : ?>
					<img src="<?php _e($image_mb_open);?>">
					<?php endif; ?>
					<?php if($text_mb_open!=-1) : ?>
					<span><?php _e(APFFW_HELPER::wpml_translate(null, $text_mb_open));?></span>
					<?php endif; ?>
				</div>
				<div class="apffw_hide_mobile_filter" >
					<?php if($image_mb_close!=-1) : ?>
					<img src="<?php _e($image_mb_close);?>">
					<?php endif; ?>
					<?php if($text_mb_close!=-1) : ?>
					<span><?php _e(APFFW_HELPER::wpml_translate(null, $text_mb_close));?></span>
					<?php endif; ?>
				</div>
				<?php
			}
			?>
                <?php if ($show_apffw_edit_view AND!empty($sid)): ?>
                    <a href="#" class="apffw_edit_view" data-sid="<?php _e($sid);?>"><?php esc_html_e('show blocks helper', 'apffw-products-filter') ?></a>
                    <div></div>
                <?php endif; ?>

                <!--- here is possible to drop html code which is never redraws by AJAX ---->
                <?php _e(apply_filters('apffw_print_content_before_redraw_zone', ''));?>

                <div class="apffw_redraw_zone" data-apffw-ver="<?php _e(APFFW_VERSION);?>">
                    <?php _e(apply_filters('apffw_print_content_before_search_form', ''));?>
                    <?php
                    if (isset($start_filtering_btn) AND (int) $start_filtering_btn == 1) {
                        $start_filtering_btn = true;
                    } else {
                        $start_filtering_btn = false;
                    }

                    if (is_ajax()) {
                        $start_filtering_btn = false;
                    }

                    if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
                        $start_filtering_btn = false;
                    }
                    ?>

                    <?php if ($start_filtering_btn): ?>
                        <a href="#" class="apffw_button apffw_start_filtering_btn"><?php _e($apffw_start_filtering_btn_txt);?></a>
                    <?php else: ?>
                        <?php
                        if ($btn_position == 't' OR $btn_position == 'tb'OR $btn_position == 'bt') {
                            apffw_show_btn($autosubmit, $ajax_redraw);
                        }
                        global $wp_query;
                        
                        {
                            $exclude_tax_key = '';
                            
                            if ($this->is_really_current_term_exists()) {
                                $o = $this->get_really_current_term();
                                $exclude_tax_key = $o->taxonomy;
                            }
                            
                            if (!empty($wp_query->query)) {
                                if (isset($wp_query->query_vars['taxonomy']) AND in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product'))) {
                                    $taxes = $wp_query->query;
                                    if (isset($taxes['paged'])) {
                                        unset($taxes['paged']);
                                    }

                                    foreach ($taxes as $key => $value) {
                                        if (in_array($key, array_keys($this->get_request_data()))) {
                                            unset($taxes[$key]);
                                        }
                                    }
                                    
                                    if (!empty($taxes)) {
                                        $t = array_keys($taxes);
                                        $v = array_values($taxes);
                                        
                                        $exclude_tax_key = $t[0];
                                        $_REQUEST['APFFW_IS_TAX_PAGE'] = $exclude_tax_key;
                                    }
                                }
                            } 

                            

                            $items_order = array();

                            $taxonomies_keys = array_keys($taxonomies);
							
                            if (isset($apffw_settings['items_order']) AND!empty($apffw_settings['items_order'])) {
                                $items_order = explode(',', $apffw_settings['items_order']);
                            } else {
                                $items_order = array_merge($this->items_keys, $taxonomies_keys);
                            }

                            
                            foreach (array_merge($this->items_keys, $taxonomies_keys) as $key) {
                                if (!in_array($key, $items_order)) {
                                    $items_order[] = $key;
                                }
                            }

                            
                            $counter = 0;

                            if (count($tax_only) > 0) {
                                $items_order = get_order_by_tax_only($items_order, $tax_only);
                            }

                            if (isset($by_step)) {
                                $new_items_order = explode(',', $by_step);
                                $items_order = array_map('trim', $new_items_order);
                            }

                            foreach ($items_order as $key) {
                                do_action('apffw_before_draw_filter', $key, $shortcode_atts);

                                if (in_array($key, $this->items_keys)) {
                                    apffw_print_item_by_key($key, $apffw_settings, $additional_taxes);
                                } else {
                                    if (!isset($apffw_settings['tax'][$key])) {
                                        continue;
                                    }

                                    apffw_print_tax($taxonomies, $key, $taxonomies[$key], $exclude_tax_key, $taxonomies_info, $additional_taxes, $apffw_settings, $args, $counter);
                                }
                                do_action('apffw_after_draw_filter', $key, $shortcode_atts);
                                $counter++;
                            }
                        }
                        ?>


                        <?php
                        if ($btn_position == 'b' OR $btn_position == 'tb'OR $btn_position == 'bt') {
                            apffw_show_btn($autosubmit, $ajax_redraw);
                        }
                        ?>

                    <?php endif; ?>



                </div>

            </div>



            <?php if ($autohide): ?>
            </div>
        </div>

    </div>
<?php endif; ?>