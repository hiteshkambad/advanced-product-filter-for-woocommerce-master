<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
?>

<input type="hidden" name="apffw_settings[<?php _e($key);?>][text_conditional]" value="<?php _e((isset($settings[$key]['text_conditional'])? $settings[$key]['text_conditional']:'LIKE'));?>" /> 
<input type="hidden" name="apffw_settings[<?php _e($key);?>][text_autocomplate]" value="<?php _e((isset($settings[$key]['text_autocomplate'])? $settings[$key]['text_autocomplate']:0));?>" /> 

<div id="apffw-modal-content-<?php _e($key);?>" style="display: none;">

    <div class="apffw-form-element-container">

        <div class="apffw-name-description">
            <strong><?php esc_html_e('Text search conditional', 'apffw-products-filter') ?></strong>
            <span><?php esc_html_e('TEXT', 'apffw-products-filter') ?></span>
        </div>

        <div class="apffw-form-element">
            <?php
            $text_conditional = array(
                'LIKE' => esc_html__('LIKE', 'apffw-products-filter'),
                '=' => esc_html__('EXACT', 'apffw-products-filter')
            );
            ?>

            <div class="select-wrap">
                <select class="apffw_popup_option" data-option="text_conditional">
                    <?php foreach ($text_conditional  as $id => $value) : ?>
                        <option value="<?php _e($id);?>"><?php _e($value);?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

    </div>        

</div>

