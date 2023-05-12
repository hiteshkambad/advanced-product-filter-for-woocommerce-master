<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
?>

<input type="hidden" name="apffw_settings[<?php _e($key);?>][search_option]" value="<?php _e((isset($settings[$key]['search_option'])) ? $settings[$key]['search_option'] : 0);?>" /> 
<input type="hidden" name="apffw_settings[<?php _e($key);?>][search_value]" value="<?php _e((isset($settings[$key]['search_value'])) ? $settings[$key]['search_value'] : "");?>" /> 
<div id="apffw-modal-content-<?php _e($key);?>" style="display: none;">
    <div class="apffw-form-element-container">
        <div class="apffw-name-description">
            <strong><?php esc_html_e('Search option', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('Search by exact value OR if meta key exists', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">
            <?php
            $show_title = array(
                0 => esc_html__('Exact value', 'apffw-products-filter'),
                1 => esc_html__('Value exists', 'apffw-products-filter')
            );
            ?>

            <div class="select-wrap">
                <select class="apffw_popup_option" data-option="search_option">
                    <?php foreach ($show_title as $id => $value) : ?>
                        <option value="<?php _e($id);?>"><?php _e($value);?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

    </div> 
    <?php if ($type != 'numeric'): ?>
        <div class="apffw-form-element-container">

            <div class="apffw-name-description">
                <strong><?php esc_html_e('Search value', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('TRUE value, all another are FALSE. Example: yes or true or 1. By default if this textinput empty 1 is true and 0 is false', 'apffw-products-filter') ?></span>
            </div>

            <div class="apffw-form-element">
                <input type="text" class="apffw_popup_option" data-option="search_value" placeholder="" value="" />
            </div>
        </div>
    <?php endif; ?>
</div>

