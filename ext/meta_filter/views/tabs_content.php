<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

global $APFFW;
?>

<section id="tabs-meta-filter">
    <div class="apffw-tabs apffw-tabs-style-line">

        <?php global $wp_locale; ?>

        <div class="content-wrap">

            <section>

                <div class="apffw-section-title">
                    <div class="col-title">

                        <h4><?php esc_html_e('Meta Fields', 'apffw-products-filter') ?></h4>

                    </div>
                </div>



                <div class="apffw-control-section">

                    <div class="apffw-control-container">
                        <div class="apffw-control">

                            <h5><?php esc_html_e('Add Custom key by hands', 'apffw-products-filter') ?>:</h5>
                            <input type="text" value="" class="apffw_meta_key_input apffw_width_75p">&nbsp;<a href="#" id="apffw_meta_add_new_btn" class="button button-primary button-large"><?php esc_html_e('Add', 'apffw-products-filter') ?></a> 


                        </div>

                    </div>

                </div>




                <div class="clear"></div>

                <br />

                <div id="metaform" method="post" action="">
                    <input type="hidden" name="apffw_meta_fields[]" value="" />
                    <ul id="apffw_meta_list" class="ui-sortable apffw_fields">

                        <?php
                        if (!empty($metas)) {
                            foreach ($metas as $m) {
                                if ($m['meta_key'] == "__META_KEY__") {
                                    continue;
                                }
                                apffw_meta_print_li($m, $meta_types);
                            }
                        }
                        ?>

                    </ul>


                    <br />


                </div>

                <div style="display: none;" id="apffw_meta_li_tpl">
                    <?php
                    apffw_meta_print_li(array(
                        'meta_key' => '__META_KEY__',
                        'title' => '__TITLE__',
                        'search_view' => '',
                        'type' => '',
                        'options' => ''
                            ), $meta_types);
                    ?>
                </div>

                <?php

                function apffw_meta_print_li($m, $meta_types) {
                    ?>
                    <li class="apffw_options_li">
                        <span class="icon-arrow-combo help_tip2 apffw_drag_and_drope" data-tip2="<?php esc_html_e("drag and drope", 'apffw-products-filter'); ?>"></span>

                        <div class="apffw_options_item">
                            <input type="text" name="apffw_settings[meta_filter][<?php _e($m['meta_key']);?>][meta_key]" value="<?php _e($m['meta_key']);?>" readonly="" class="apffw_column_li_option" />
                        </div>
                        <div class="apffw_options_item">
                            <input type="text" name="apffw_settings[meta_filter][<?php _e($m['meta_key']);?>][title]" placeholder="<?php esc_html_e('enter title', 'apffw-products-filter') ?>" value="<?php _e($m['title']);?>" class="apffw_column_li_option apffw_fix2" />

                        </div>
                        <div class="apffw_options_item">
                            <div class="select-wrap">
                                <select name="apffw_settings[meta_filter][<?php _e($m['meta_key']);?>][search_view]" class="apffw_meta_view_selector apffw_width_99p">
                                    <?php
                                    foreach ($meta_types as $key => $type):
                                        if (!is_array($type['hide_if'])) {
                                            $type['hide_if'] = array($type['hide_if']);
                                        }
                                        if ($m['search_view'] == $key AND in_array($m['type'], $type['hide_if'])) {
                                            $m['search_view'] = 'textinput';
                                        }
                                        ?> 
                                        <option  <?php selected($m['search_view'], $key) ?> value="<?php _e($key);?>" data-show-options="<?php _e(($type['show_options']) ? 'yes' : 'no');?>" data-hideif="<?php _e(implode(',', $type['hide_if']));?>" <?php _e((in_array($m['type'], $type['hide_if'])) ? "style='display:none;'" : "");?>  >
                                            <?php _e($type['title']);?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php
                        $show_options = false;
                        if (isset($meta_types[$m['search_view']]['show_options'])) {
                            $show_options = $meta_types[$m['search_view']]['show_options'];
                        }
                        ?>
                        <div class="apffw_options_item_options" <?php if (!$show_options): ?> style="display:none;" <?php endif; ?> >
                            <div class="textarea-wrap">
                                <textarea name="apffw_settings[meta_filter][<?php _e($m['meta_key']);?>][options]" class="apffw_column_li_option" ><?php _e((isset($m['options'])) ? $m['options'] : "");?></textarea>
                            </div>
                            <div class="apffw-meta-description">
                                <p><i><?php esc_html_e('Use comma as in example: 1,2,3,4,5. If you want structure like title->value use next syntax example: France^1,Germany^2,USA^3. Countries are titles here.', 'apffw-products-filter') ?></i></p>
                            </div>
                        </div>
                        <div class="apffw_options_item">
                            <div class="select-wrap" <?php if (in_array($m['search_view'], array('popupeditor', 'switcher'))): ?>style="display: none;"<?php endif; ?>>
                                <select name="apffw_settings[meta_filter][<?php _e($m['meta_key']);?>][type]" class="apffw_meta_type_selector">
                                    <option <?php selected($m['type'], 'NUMERIC') ?> value="NUMERIC"><?php esc_html_e('number', 'apffw-products-filter') ?></option>
                                    <option <?php selected($m['type'], 'string') ?> value="string"><?php esc_html_e('string', 'apffw-products-filter') ?></option>
                                    <option <?php selected($m['type'], 'DATE') ?> value="DATE"><?php esc_html_e('date', 'apffw-products-filter') ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="apffw_options_item">
                            <a href="#" class="button button-primary apffw_meta_delete" title="<?php esc_html_e('delete', 'apffw-products-filter') ?>"><span class="dashicons dashicons-trash"></span></a>
                        </div>

                        <div class="clear clearfix"></div>
                    </li>
                    <?php
                }
                ?>
            </section>

        </div>

    </div>
</section>




