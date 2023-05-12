<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
?>

<input type="hidden" name="apffw_settings[<?php _e($key);?>][show_title_label]" value="<?php _e((isset($settings[$key]['show_title_label']) ? $settings[$key]['show_title_label'] : 1));?>" /> 
<input type="hidden" name="apffw_settings[<?php _e($key);?>][show_toggle_button]" value="<?php _e((isset($settings[$key]['show_toggle_button']) ? $settings[$key]['show_toggle_button'] : 0));?>" /> 
<input type="hidden" name="apffw_settings[<?php _e($key);?>][tooltip_text]" value="<?php _e((isset($settings[$key]['tooltip_text']) ? stripcslashes($settings[$key]['tooltip_text']) : ""));?>" />
<input type="hidden" name="apffw_settings[<?php _e($key);?>][step]" value="<?php _e((isset($settings[$key]['step']) ? $settings[$key]['step'] : 1));?>" /> 
<input type="hidden" name="apffw_settings[<?php _e($key);?>][range]" value="<?php _e((isset($settings[$key]['range']) ? $settings[$key]['range'] : "1-100"));?>" /> 
<input type="hidden" name="apffw_settings[<?php _e($key);?>][prefix]" value="<?php _e((isset($settings[$key]['prefix']) ? $settings[$key]['prefix'] : ""));?>" /> 
<input type="hidden" name="apffw_settings[<?php _e($key);?>][postfix]" value="<?php _e((isset($settings[$key]['postfix']) ? $settings[$key]['postfix'] : ""));?>" /> 
<div id="apffw-modal-content-<?php _e($key);?>" style="display: none;">
    <div class="apffw-form-element-container">
        <div class="apffw-name-description">
            <strong><?php esc_html_e('Show title label', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('Show/Hide meta block title on the front', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">
            <?php
            $show_title = array(
                0 => esc_html__('No', 'apffw-products-filter'),
                1 => esc_html__('Yes', 'apffw-products-filter')
            );
            ?>

            <div class="select-wrap">
                <select class="apffw_popup_option" data-option="show_title_label">
                    <?php foreach ($show_title as $id => $value) : ?>
                        <option value="<?php _e($id);?>"><?php _e($value);?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

    </div> 
    <div class="apffw-form-element-container">
        <div class="apffw-name-description">
            <strong><?php esc_html_e('Show toggle button', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('Show toggle button near the title on the front above the block of html-items', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">
            <?php
            $show_toogle = array(
                0 => esc_html__('No', 'apffw-products-filter'),
                1 => esc_html__('Yes, show as closed', 'apffw-products-filter'),
                2 => esc_html__('Yes, show as opened', 'apffw-products-filter')
            );
            ?>

            <div class="select-wrap">
                <select class="apffw_popup_option" data-option="show_toggle_button">
                    <?php foreach ($show_toogle as $id => $value) : ?>
                        <option value="<?php _e($id);?>"><?php _e($value);?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

    </div>    
    <div class="apffw-form-element-container">

        <div class="apffw-name-description">
            <strong><?php esc_html_e('Tooltip', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('Show tooltip', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">

            <div class="select-wrap">
                <textarea class="apffw_popup_option" data-option="tooltip_text" ></textarea>
            </div>

        </div>

    </div>
    <div class="apffw-form-element-container">

        <div class="apffw-name-description">
            <strong><?php esc_html_e('Step', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">
            <input type="text" class="apffw_popup_option" data-option="step" placeholder="" value="" />
        </div>
    </div>
    <div class="apffw-form-element-container">
        <div class="apffw-name-description">
            <strong><?php esc_html_e('Range', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('Example: 1^100', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">
            <input type="text" class="apffw_popup_option" data-option="range" placeholder="" value="" />
        </div>
    </div>
    <div class="apffw-form-element-container"> 

        <div class="apffw-name-description">
            <strong><?php esc_html_e('Prefix', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">
            <input type="text" class="apffw_popup_option" data-option="prefix" placeholder="" value="" />
        </div>

    </div>
    <div class="apffw-form-element-container">

        <div class="apffw-name-description">
            <strong><?php esc_html_e('Postfix', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">
            <input type="text" class="apffw_popup_option" data-option="postfix" placeholder="" value="" />
        </div>
    </div>
</div>

