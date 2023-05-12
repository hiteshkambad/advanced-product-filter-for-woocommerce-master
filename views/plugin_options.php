<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="apffw-admin-preloader">
    <div class="cssload-loader">
        <div class="cssload-inner cssload-one"></div>
        <div class="cssload-inner cssload-two"></div>
        <div class="cssload-inner cssload-three"></div>
    </div>
</div>

<div class="subsubsub_section <?php _e(($this->is_free_ver) ? "apffw_free" : ""); ?>">

    <div class="apffw_fix12"></div>

    <section class="apffw-section">

        <?php if (isset($_GET['settings_saved'])): ?>
            <div class="apffw-notice"><?php esc_html_e("Your settings have been saved.", 'apffw-products-filter') ?></div>
        <?php endif; ?>


        <div class="apffw-header">
            <div>
                <h3 class="apffw_plugin_name"><?php esc_html_e('Advanced Products Filter For WooCommerce', 'apffw-products-filter') ?></h3>
                
            </div>
            <div>
                
            </div>
        </div>

        <input type="hidden" name="apffw_settings" value="" />
        <input type="hidden" name="apffw_settings[items_order]" value="<?php _e((isset($apffw_settings['items_order']) ? $apffw_settings['items_order'] : ''));?>" />
		<input type="hidden" name="_wpnonce_apffw" value="<?php _e(wp_create_nonce( 'apffw_save_option'));?>">
        <?php if (version_compare(WOOCOMMERCE_VERSION, APFFW_MIN_WOOCOMMERCE_VERSION, '<')): ?>

            <div id="message" class="error fade"><p><strong><?php esc_html_e("ATTENTION! Your version of the woocommerce plugin is too obsolete. There is no warranty for working with APFFW!!", 'apffw-products-filter') ?></strong></p></div>

        <?php endif; ?>


        <div id="tabs" class="apffw-tabs">

            <nav>
                <ul>
                    <li class="tab-current">
                        <a href="#tabs-1">
                            <span><?php esc_html_e("Filter Terms", 'apffw-products-filter') ?></span>
                        </a>
                    </li>
                     <li>
                        <a href="#tabs-2">
                            <span class="icon-cog-outline"></span>
                            <span><?php esc_html_e("Settings", 'apffw-products-filter') ?></span>
                        </a>
                    </li> 
                  

                    <?php
                    if (!empty(APFFW_EXT::$includes['applications'])) {
                        foreach (APFFW_EXT::$includes['applications'] as $obj) {
                            $dir1 = $this->get_custom_ext_path() . $obj->folder_name;
                            $dir2 = APFFW_EXT_PATH . $obj->folder_name;
                            $checked1 = APFFW_EXT::is_ext_activated($dir1);
                            $checked2 = APFFW_EXT::is_ext_activated($dir2);
                            if ($checked1 OR $checked2) {
                                do_action('apffw_print_applications_tabs_' . $obj->folder_name);
                            }
                        }
                    }
                    ?>

                    <li>
                        <a href="#tabs-6">
                            <span><?php esc_html_e("Addons", 'apffw-products-filter') ?></span>
                        </a>
                    </li>
                   
                </ul>
            </nav>
            <div class="content-wrap">

                <section id="tabs-1" class="content-current">

                    <ul id="apffw_options">

                        <?php
                        $items_order = array();
                        $taxonomies = $this->get_taxonomies();
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


                        foreach ($items_order as $key) {
                            if (in_array($key, $this->items_keys)) {
                                apffw_print_item_by_key($key, $apffw_settings);
                            } else {
                                if (isset($taxonomies[$key])) {
                                    apffw_print_tax($key, $taxonomies[$key], $apffw_settings);
                                }
                            }
                        }
                        ?>
                    </ul>

                    <input type="button" class="button btn-warning apffw_reset_order" value="<?php esc_html_e('Reset items order', 'apffw-products-filter') ?>" />

                    <div class="clear"></div>

                </section>

                <section id="tabs-2">

                    <?php woocommerce_admin_fields($this->get_options()); ?>

                </section>

                

                <section id="tabs-4">

                    <div class="apffw-tabs apffw-tabs-style-line">

                        <nav>
                            <ul>
                                <li>
                                    <a href="#tabs-41">
                                        <span class="icon-code"></span>
                                        <span><?php esc_html_e("Code", 'apffw-products-filter') ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tabs-42">
                                        <span class="icon-cog-outline"></span>
                                        <span><?php esc_html_e("Options", 'apffw-products-filter') ?></span>
                                    </a>
                                </li>
                                <?php do_action('apffw_print_applications_tabs_anvanced'); ?>
                            </ul>
                        </nav>

                        <div class="content-wrap">

                            <section id="tabs-41">

                                <table class="form-table">

                                    <tr>
                                        <th scope="row"><label for="custom_css_code"><?php esc_html_e('Custom CSS code', 'apffw-products-filter') ?></label></th>

                                        <td>
                                            <textarea class="wide apffw_custom_css" id="custom_css_code" name="apffw_settings[custom_css_code]"><?php _e(isset($this->settings['custom_css_code']) ? stripcslashes($this->settings['custom_css_code']) : '');?></textarea>
                                            <p class="description"><?php esc_html_e("If you are need to customize something and you don't want to lose your changes after update", 'apffw-products-filter') ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="js_after_ajax_done"><?php esc_html_e('JavaScript code after AJAX is done', 'apffw-products-filter') ?></label></th>
                                        <td>
                                            <textarea class="wide apffw_custom_css" id="js_after_ajax_done" name="apffw_settings[js_after_ajax_done]"><?php _e(isset($this->settings['js_after_ajax_done']) ? stripcslashes($this->settings['js_after_ajax_done']) : '');?></textarea>
                                            <p class="description"><?php esc_html_e('Use it when you are need additional action after AJAX redraw your products in shop page or in page with shortcode! For use when you need additional functionality after AJAX redraw of your products on the shop page or on pages with shortcodes.', 'apffw-products-filter') ?></p>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row"><label for="init_only_on"><?php esc_html_e('Init plugin on the next site pages only ', 'apffw-products-filter') ?></label></th>
                                        <td>
                                            <div class="apffw-control-section">
                                                <div class="apffw-control-container">
                                                    <div class="apffw-control">

                                                        <?php
                                                        $init_only_on_r = array(
                                                            0 => esc_html__("Yes", 'apffw-products-filter'),
                                                            1 => esc_html__("No", 'apffw-products-filter')
                                                        );
                                                        ?>

                                                        <?php
                                                        if (!isset($apffw_settings['init_only_on_reverse']) OR empty($apffw_settings['init_only_on_reverse'])) {
                                                            $apffw_settings['init_only_on_reverse'] = 0;
                                                        }
                                                        ?>
                                                        <div class="select-wrap">
                                                            <select name="apffw_settings[init_only_on_reverse]">
                                                                <?php foreach ($init_only_on_r as $key => $value) : ?>
                                                                    <option value="<?php _e($key);?>" <?php if ($apffw_settings['init_only_on_reverse'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                    </div>
                                                    <div class="apffw-description apffw_fix13">
                                                        <p class="description"><?php esc_html_e("Reverse: deactivate plugin on the next site pages only", 'apffw-products-filter') ?></p>
                                                    </div>
                                                </div>

                                            </div><!--/ .apffw-control-section-->



                                            <?php
                                            if (!isset($this->settings['init_only_on'])) {
                                                $this->settings['init_only_on'] = '';
                                            }
                                            ?>
                                            <textarea class="wide apffw_custom_css" id="init_only_on" name="apffw_settings[init_only_on]"><?php _e(stripcslashes(trim($this->settings['init_only_on'])));?></textarea>
                                            <p class="description"><?php esc_html_e('This option enables or disables initialization of the plugin on all pages of the site except links and link-masks in the textarea. One row - one link (or link-mask)! Example of link: http://site.com/ajaxed-search-7. Example of link-mask: product-category . Leave it empty to allow the plugin initialization on all pages of the site!', 'apffw-products-filter') ?></p>
                                            <p class="description"><?php esc_html_e('Use sign # before link to apply strict compliance. Example: #https://your_site.com/product-category/man/', 'apffw-products-filter') ?></p>
                                        </td>
                                    </tr>


                                    <?php if (class_exists('SitePress') OR class_exists('Polylang')): ?>
                                        <tr>
                                            <th scope="row"><label for="wpml_tax_labels">
                                                    <?php esc_html_e('WPML taxonomies labels translations', 'apffw-products-filter') ?> <img class="help_tip" data-tip="Syntax:
                                                         es:Locations^Ubicaciones
                                                         es:Size^Tamaño
                                                         de:Locations^Lage
                                                         de:Size^Größe" src="<?php _e(APFFW_LINK);?>/img/help.png" height="16" width="16" />
                                                </label></th>
                                            <td>

                                                <?php
                                                $wpml_tax_labels = "";
                                                if (isset($apffw_settings['wpml_tax_labels']) AND is_array($apffw_settings['wpml_tax_labels'])) {
                                                    foreach ($apffw_settings['wpml_tax_labels'] as $lang => $words) {
                                                        if (!empty($words) AND is_array($words)) {
                                                            foreach ($words as $key_word => $translation) {
                                                                $wpml_tax_labels .= $lang . ':' . $key_word . '^' . $translation . PHP_EOL;
                                                            }
                                                        }
                                                    }
                                                }
                                                ?>

                                                <textarea class="wide apffw_custom_css" id="wpml_tax_labels" name="apffw_settings[wpml_tax_labels]"><?php _e($wpml_tax_labels);?></textarea>
                                                <p class="description"><?php esc_html_e('Use it if you can not translate your custom taxonomies labels and attributes labels by another plugins.', 'apffw-products-filter') ?></p>

                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                </table>

                            </section>

                            <section id="tabs-42">

                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e('Search slug', 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">

                                            <?php
                                            if (!isset($apffw_settings['sapffw_search_slug'])) {
                                                $apffw_settings['sapffw_search_slug'] = '';
                                            }
                                            ?>

                                            <input placeholder="sapffw" type="text" name="apffw_settings[sapffw_search_slug]" value="<?php _e($apffw_settings['sapffw_search_slug']);?>" id="sapffw_search_slug" />

                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e('If you do not like search key "sapffw" in the search link you can replace it by your own word. But be care to avoid conflicts with any themes and plugins, + never define it as symbol "s". Not understood? Simply do not touch it!', 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e('Products per page', 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">
                                            <?php
                                            if (!isset($apffw_settings['per_page'])) {
                                                $apffw_settings['per_page'] = -1;
                                            }
                                            ?>

                                            <input type="text" name="apffw_settings[per_page]" value="<?php _e($apffw_settings['per_page']);?>" id="per_page" />
                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e('Products per page when searching is going only. Set here -1 to prevent pagination managing from here!', 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e("Optimize loading of APFFW JavaScript files", 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">

                                            <?php
                                            $optimize_js_files = array(
                                                0 => esc_html__("No", 'apffw-products-filter'),
                                                1 => esc_html__("Yes", 'apffw-products-filter')
                                            );
                                            ?>

                                            <?php
                                            if (!isset($apffw_settings['optimize_js_files']) OR empty($apffw_settings['optimize_js_files'])) {
                                                $apffw_settings['optimize_js_files'] = 0;
                                            }
                                            ?>

                                            <select name="apffw_settings[optimize_js_files]">
                                                <?php foreach ($optimize_js_files as $key => $value) : ?>
                                                    <option value="<?php _e($key);?>" <?php if ($apffw_settings['optimize_js_files'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                <?php endforeach; ?>
                                            </select>


                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e("This option place APFFW JavaScript files on the site footer. Use it for page loading optimization. Be care with this option, and always after enabling of it test your site frontend!", 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e('Override no products found content', 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">

                                            <?php
                                            if (!isset($apffw_settings['override_no_products'])) {
                                                $apffw_settings['override_no_products'] = '';
                                            }
                                            ?>

                                            <textarea name="apffw_settings[override_no_products]" id="override_no_products" ><?php _e($apffw_settings['override_no_products']);?></textarea>

                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e('Place in which you can paste text or/and any shortcodes which will be displayed when customer will not find any products by his search criterias. Example:', 'apffw-products-filter') ?> <i class="apffw_orangered">&lt;center&gt;&lt;h2>Where are the products?&lt;/h2&gt;&lt;/center&gt;&lt;h4&gt;Perhaps you will like next products&lt;/h4&gt;[recent_products limit="3" columns="4" ]</i> (<?php esc_html_e('do not use shortcodes here in turbo mode', 'apffw-products-filter') ?>)</p>
                                        </div>
                                    </div>

                                </div>
                                <div class="apffw-control-section apffw_premium_only">
                                    <?php
                                    $show_images_by_attr = array(
                                        0 => esc_html__("No", 'apffw-products-filter'),
                                        1 => esc_html__("Yes", 'apffw-products-filter')
                                    );
                                    if (!isset($apffw_settings['show_images_by_attr_show']) OR empty($apffw_settings['show_images_by_attr_show'])) {
                                        $apffw_settings['show_images_by_attr_show'] = 0;
                                    }
                                    ?>

                                    <h5><?php esc_html_e("Show image of variation", 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">

                                            <select name="apffw_settings[show_images_by_attr_show]">
                                                <?php foreach ($show_images_by_attr as $key => $value) : ?>
                                                    <option value="<?php _e($key);?>" <?php if ($apffw_settings['show_images_by_attr_show'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                <?php endforeach; ?>
                                            </select>


                                            <?php
                                            $attributes = wc_get_attribute_taxonomies();
                                            ?>

                                            <?php
                                            if (!isset($apffw_settings['show_images_by_attr']) OR empty($apffw_settings['show_images_by_attr'])) {
                                                $apffw_settings['show_images_by_attr'] = array();
                                            }
                                            ?>
                                            <div class="select-wrap chosen_select" <?php _e((!$apffw_settings['show_images_by_attr_show']) ? "style='display:none;'" : "");?> >
                                                <select  class="chosen_select" multiple name="apffw_settings[show_images_by_attr][]">
                                                    <?php foreach ($attributes as $attr) : ?>
                                                        <option value="pa_<?php _e($attr->attribute_name);?>" <?php if (in_array('pa_' . $attr->attribute_name, $apffw_settings['show_images_by_attr'])): ?>selected="selected"<?php endif; ?>><?php _e($attr->attribute_label);?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                        </div>


                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e("For variable products you can show an image depending on the current filter selection. For example you have variation with red color, and that varation has its own preview image - if on the site front user will select red color this imag will be shown. You can select attributes by which images will be selected", 'apffw-products-filter') ?></p>
                                        </div>

                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <div class="apffw-control-section apffw_premium_only">

                                    <h5><?php esc_html_e("Hide terms count text", 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">

                                            <?php
                                            $hide_terms_count_txt = array(
                                                0 => esc_html__("No", 'apffw-products-filter'),
                                                1 => esc_html__("Yes", 'apffw-products-filter')
                                            );
                                            ?>

                                            <?php
                                            if (!isset($apffw_settings['hide_terms_count_txt']) OR empty($apffw_settings['hide_terms_count_txt'])) {
                                                $apffw_settings['hide_terms_count_txt'] = 0;
                                            }
                                            ?>

                                            <select name="apffw_settings[hide_terms_count_txt]">
                                                <?php foreach ($hide_terms_count_txt as $key => $value) : ?>
                                                    <option value="<?php _e($key);?>" <?php if ($apffw_settings['hide_terms_count_txt'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                <?php endforeach; ?>
                                            </select>


                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e("If you want show relevant tags on the categories pages you should activate show count, dynamic recount and hide empty terms in the tab Options. But if you do not want show count (number) text near each term - set Yes here.", 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e("Listen catalog visibility", 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">

                                            <?php
                                            $listen_catalog_visibility = array(
                                                0 => esc_html__("No", 'apffw-products-filter'),
                                                1 => esc_html__("Yes", 'apffw-products-filter')
                                            );
                                            ?>

                                            <?php
                                            if (!isset($apffw_settings['listen_catalog_visibility']) OR empty($apffw_settings['listen_catalog_visibility'])) {
                                                $apffw_settings['listen_catalog_visibility'] = 0;
                                            }
                                            ?>

                                            <select name="apffw_settings[listen_catalog_visibility]">
                                                <?php foreach ($listen_catalog_visibility as $key => $value) : ?>
                                                    <option value="<?php _e($key);?>" <?php if ($apffw_settings['listen_catalog_visibility'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                <?php endforeach; ?>
                                            </select>


                                        </div>
                                        <div class="apffw-description">
                                            <p class="description">
                                                <?php esc_html_e("Listen catalog visibility - options in each product backend page in 'Publish' sidebar widget.", 'apffw-products-filter') ?><br />
                                                <a href="<?php _e(APFFW_LINK);?>img/plugin_options/listen_catalog_visibility.png" target="_blank"><img src="<?php _e(APFFW_LINK);?>img/plugin_options/listen_catalog_visibility.png" width="150" alt="" /></a>
                                            </p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->


                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e("Disable sapffw influence", 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">

                                            <?php
                                            $disable_sapffw_influence = array(
                                                0 => esc_html__("No", 'apffw-products-filter'),
                                                1 => esc_html__("Yes", 'apffw-products-filter')
                                            );
                                            ?>

                                            <?php
                                            if (!isset($apffw_settings['disable_sapffw_influence']) OR empty($apffw_settings['disable_sapffw_influence'])) {
                                                $apffw_settings['disable_sapffw_influence'] = 0;
                                            }
                                            ?>

                                            <select name="apffw_settings[disable_sapffw_influence]">
                                                <?php foreach ($disable_sapffw_influence as $key => $value) : ?>
                                                    <option value="<?php _e($key);?>" <?php if ($apffw_settings['disable_sapffw_influence'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                <?php endforeach; ?>
                                            </select>


                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e("Sometimes code 'wp_query->is_post_type_archive = true' does not necessary. Try to disable this and try apffw-search on your site. If all is ok - leave its disabled. Disabled code by this option you can find in index.php by mark disable_sapffw_influence.", 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <?php if (!isset($apffw_settings['apffw_turbo_mode']['enable']) OR $apffw_settings['apffw_turbo_mode']['enable'] != 1 OR!class_exists("APFFW_EXT_TURBO_MODE")) { ?>
                                    <div class="apffw-control-section">

                                        <h5><?php esc_html_e("Cache dynamic recount number for each item in filter", 'apffw-products-filter') ?></h5>

                                        <div class="apffw-control-container">
                                            <div class="apffw-control">

                                                <?php
                                                $cache_count_data = array(
                                                    0 => esc_html__("No", 'apffw-products-filter'),
                                                    1 => esc_html__("Yes", 'apffw-products-filter')
                                                );
                                                ?>

                                                <?php
                                                if (!isset($apffw_settings['cache_count_data']) OR empty($apffw_settings['cache_count_data'])) {
                                                    $apffw_settings['cache_count_data'] = 0;
                                                }
                                                ?>

                                                <select name="apffw_settings[cache_count_data]">
                                                    <?php foreach ($cache_count_data as $key => $value) : ?>
                                                        <option value="<?php _e($key);?>" <?php if ($apffw_settings['cache_count_data'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                    <?php endforeach; ?>
                                                </select>


                                                <?php if ($apffw_settings['cache_count_data']): ?>
                                                    <br />
                                                    <br /><a href="#" class="button js_cache_count_data_clear"><?php esc_html_e("clear cache", 'apffw-products-filter') ?></a>&nbsp;<span class="apffw_green"></span><br />
                                                    <br />
                                                    <?php
                                                    $clean_period = 'days7';
                                                    if (isset($this->settings['cache_count_data_auto_clean'])) {
                                                        $clean_period = $this->settings['cache_count_data_auto_clean'];
                                                    }
                                                    $periods = array(
                                                        0 => esc_html__("do not clean cache automatically", 'apffw-products-filter'),
                                                        'hourly' => esc_html__("clean cache automatically hourly", 'apffw-products-filter'),
                                                        'twicedaily' => esc_html__("clean cache automatically twicedaily", 'apffw-products-filter'),
                                                        'daily' => esc_html__("clean cache automatically daily", 'apffw-products-filter'),
                                                        'days2' => esc_html__("clean cache automatically each 2 days", 'apffw-products-filter'),
                                                        'days3' => esc_html__("clean cache automatically each 3 days", 'apffw-products-filter'),
                                                        'days4' => esc_html__("clean cache automatically each 4 days", 'apffw-products-filter'),
                                                        'days5' => esc_html__("clean cache automatically each 5 days", 'apffw-products-filter'),
                                                        'days6' => esc_html__("clean cache automatically each 6 days", 'apffw-products-filter'),
                                                        'days7' => esc_html__("clean cache automatically each 7 days", 'apffw-products-filter')
                                                    );
                                                    ?>

                                                    <select name="apffw_settings[cache_count_data_auto_clean]">
                                                        <?php foreach ($periods as $key => $txt): ?>
                                                            <option <?php selected($clean_period, $key) ?> value="<?php _e($key);?>"><?php _e($txt);?></option>
                                                        <?php endforeach; ?>
                                                    </select>


                                                <?php endif; ?>

                                            </div>
                                            <div class="apffw-description">

                                                <?php
                                                global $wpdb;

                                                $charset_collate = '';
                                                if (method_exists($wpdb, 'has_cap') AND $wpdb->has_cap('collation')) {
                                                    if (!empty($wpdb->charset)) {
                                                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                                                    }
                                                    if (!empty($wpdb->collate)) {
                                                        $charset_collate .= " COLLATE $wpdb->collate";
                                                    }
                                                }
                                                
                                                $sql = "CREATE TABLE IF NOT EXISTS `" . APFFW::$query_cache_table . "` (
                                                    `mkey` varchar(64) NOT NULL,
                                                    `mvalue` text NOT NULL,
                                                    KEY `mkey` (`mkey`)
                                                  ) {$charset_collate}";

                                                if ($wpdb->query($sql) === false) {
                                                    ?>
                                                    <p class="description"><?php esc_html_e("APFFW cannot create the database table! Make sure that your mysql user has the CREATE privilege! Do it manually using your host panel&phpmyadmin!", 'apffw-products-filter') ?></p>
                                                    <code><?php _e($sql);?></code>
                                                    <input type="hidden" name="apffw_settings[cache_count_data]" value="0" />
                                                    <?php
                                                    _e($wpdb->last_error);
                                                }
                                                ?>

                                                <p class="description"><?php esc_html_e("Useful thing when you already set your site IN THE PRODUCTION MODE and use dynamic recount -> it make recount very fast! Of course if you added new products which have to be in search results you have to clean this cache OR you can set time period for auto cleaning!", 'apffw-products-filter') ?></p>
                                            </div>
                                        </div>

                                    </div><!--/ .apffw-control-section-->



                                    <div class="apffw-control-section">

                                        <h5><?php esc_html_e("Cache terms", 'apffw-products-filter') ?></h5>

                                        <div class="apffw-control-container">
                                            <div class="apffw-control">

                                                <?php
                                                $cache_terms = array(
                                                    0 => esc_html__("No", 'apffw-products-filter'),
                                                    1 => esc_html__("Yes", 'apffw-products-filter')
                                                );
                                                ?>

                                                <?php
                                                if (!isset($apffw_settings['cache_terms']) OR empty($apffw_settings['cache_terms'])) {
                                                    $apffw_settings['cache_terms'] = 0;
                                                }
                                                ?>

                                                <select name="apffw_settings[cache_terms]">
                                                    <?php foreach ($cache_terms as $key => $value) : ?>
                                                        <option value="<?php _e($key);?>" <?php if ($apffw_settings['cache_terms'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                    <?php endforeach; ?>
                                                </select>


                                                <?php if ($apffw_settings['cache_terms']): ?>
                                                    <br />
                                                    <br /><a href="#" class="button js_cache_terms_clear"><?php esc_html_e("clear terms cache", 'apffw-products-filter') ?></a>&nbsp;<span class="apffw_green"></span><br />
                                                    <br />
                                                    <?php
                                                    $clean_period = 'days7';
                                                    if (isset($this->settings['cache_terms_auto_clean'])) {
                                                        $clean_period = $this->settings['cache_terms_auto_clean'];
                                                    }
                                                    $periods = array(
                                                        0 => esc_html__("do not clean cache automatically", 'apffw-products-filter'),
                                                        'hourly' => esc_html__("clean cache automatically hourly", 'apffw-products-filter'),
                                                        'twicedaily' => esc_html__("clean cache automatically twicedaily", 'apffw-products-filter'),
                                                        'daily' => esc_html__("clean cache automatically daily", 'apffw-products-filter'),
                                                        'days2' => esc_html__("clean cache automatically each 2 days", 'apffw-products-filter'),
                                                        'days3' => esc_html__("clean cache automatically each 3 days", 'apffw-products-filter'),
                                                        'days4' => esc_html__("clean cache automatically each 4 days", 'apffw-products-filter'),
                                                        'days5' => esc_html__("clean cache automatically each 5 days", 'apffw-products-filter'),
                                                        'days6' => esc_html__("clean cache automatically each 6 days", 'apffw-products-filter'),
                                                        'days7' => esc_html__("clean cache automatically each 7 days", 'apffw-products-filter')
                                                    );
                                                    ?>
                                                    <div class="select-wrap">
                                                        <select name="apffw_settings[cache_terms_auto_clean]">
                                                            <?php foreach ($periods as $key => $txt): ?>
                                                                <option <?php selected($clean_period, $key) ?> value="<?php _e($key);?>"><?php _e($txt);?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                <?php endif; ?>

                                            </div>
                                            <div class="apffw-description">
                                                <p class="description"><?php esc_html_e("Useful thing when you already set your site IN THE PRODUCTION MODE - its getting terms for filter faster without big MySQL queries! If you actively adds new terms every day or week you can set cron period for cleaning. Another way set: 'not clean cache automatically'!", 'apffw-products-filter') ?></p>
                                            </div>
                                        </div>

                                    </div><!--/ .apffw-control-section-->

                                    <div class="apffw-control-section">

                                        <h5><?php esc_html_e("Optimize price filter", 'apffw-products-filter') ?></h5>

                                        <div class="apffw-control-container">
                                            <div class="apffw-control">

                                                <?php
                                                $price_transient = array(
                                                    0 => esc_html__("No", 'apffw-products-filter'),
                                                    1 => esc_html__("Yes", 'apffw-products-filter')
                                                );
                                                ?>

                                                <?php
                                                if (!isset($apffw_settings['price_transient']) OR empty($apffw_settings['price_transient'])) {
                                                    $apffw_settings['price_transient'] = 0;
                                                }
                                                ?>

                                                <select name="apffw_settings[price_transient]">
                                                    <?php foreach ($price_transient as $key => $value) : ?>
                                                        <option value="<?php _e($key);?>" <?php if ($apffw_settings['price_transient'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                    <?php endforeach; ?>
                                                </select>


                                                <?php if ($apffw_settings['price_transient']): ?>
                                                    <br />
                                                    <br /><a href="#" class="button js_price_transient_clear"><?php esc_html_e("clear", 'apffw-products-filter') ?></a>&nbsp;<span class="apffw_green"></span><br />
                                                    <br />
                                                <?php endif; ?>

                                            </div>
                                            <div class="apffw-description">
                                                <p class="description"><?php esc_html_e("Helps to more quickly find the minimum and maximum values for the filter by price on the site front and minimize server loading.", 'apffw-products-filter') ?></p>
                                            </div>
                                        </div>

                                    </div><!--/ .apffw-control-section-->   

                                <?php } ?>
                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e("Show blocks helper button", 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">

                                            <?php
                                            $show_apffw_edit_view = array(
                                                0 => esc_html__("No", 'apffw-products-filter'),
                                                1 => esc_html__("Yes", 'apffw-products-filter')
                                            );
                                            ?>

                                            <?php
                                            if (!isset($apffw_settings['show_apffw_edit_view'])) {
                                                $apffw_settings['show_apffw_edit_view'] = 0;
                                            }
                                            ?>

                                            <select id="show_apffw_edit_view" name="apffw_settings[show_apffw_edit_view]">
                                                <?php foreach ($show_apffw_edit_view as $key => $value) : ?>
                                                    <option value="<?php _e($key);?>" <?php if ($apffw_settings['show_apffw_edit_view'] == $key): ?>selected="selected"<?php endif; ?>><?php _e($value);?></option>
                                                <?php endforeach; ?>
                                            </select>


                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e("Show helper button for shortcode [apffw] on the front when 'Set filter automatically' is Yes", 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e('Custom extensions folder', 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">
                                            <?php
                                            if (!isset($apffw_settings['custom_extensions_path'])) {
                                                $apffw_settings['custom_extensions_path'] = '';
                                            }
                                            ?>

                                            <input type="text" name="apffw_settings[custom_extensions_path]" value="<?php _e($apffw_settings['custom_extensions_path']);?>" id="custom_extensions_path" placeholder="Example: my_apffw_extensions" />
                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php printf(__('Custom extensions folder path relative to: %s', 'apffw-products-filter'), WP_CONTENT_DIR . DIRECTORY_SEPARATOR) ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e('Result count css selector', 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">
                                            <?php
                                            if (!isset($apffw_settings['result_count_redraw'])) {
                                                $apffw_settings['result_count_redraw'] = "";
                                            }
                                            ?>

                                            <input type="text" name="apffw_settings[result_count_redraw]" value="<?php _e($apffw_settings['result_count_redraw']);?>"  />
                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e('Css class of result-count container. Is needed for ajax compatibility with wp themes. If you do not understand, leave it blank.', 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e('Order dropdown css selector', 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">
                                            <?php
                                            if (!isset($apffw_settings['order_dropdown_redraw'])) {
                                                $apffw_settings['order_dropdown_redraw'] = "";
                                            }
                                            ?>

                                            <input type="text" name="apffw_settings[order_dropdown_redraw]" value="<?php _e($apffw_settings['order_dropdown_redraw']);?>"  />
                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e('Css class of ordering dropdown container. Is needed for ajax compatibility with wp themes. If you do not understand, leave it blank.', 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->
                                <div class="apffw-control-section">

                                    <h5><?php esc_html_e('Per page css selector', 'apffw-products-filter') ?></h5>

                                    <div class="apffw-control-container">
                                        <div class="apffw-control">
                                            <?php
                                            if (!isset($apffw_settings['per_page_redraw'])) {
                                                $apffw_settings['per_page_redraw'] = "";
                                            }
                                            ?>

                                            <input type="text" name="apffw_settings[per_page_redraw]" value="<?php _e($apffw_settings['per_page_redraw']);?>"  />
                                        </div>
                                        <div class="apffw-description">
                                            <p class="description"><?php esc_html_e('Css class of per page dropdown container. Is needed for ajax compatibility with wp themes. If you do not understand, leave it blank.', 'apffw-products-filter') ?></p>
                                        </div>
                                    </div>

                                </div><!--/ .apffw-control-section-->

                            </section>

                            <?php do_action('apffw_print_applications_tabs_content_advanced'); ?>

                        </div>

                    </div>

                </section>



                <?php
                if (!empty(APFFW_EXT::$includes['applications'])) {
                    foreach (APFFW_EXT::$includes['applications'] as $obj) {
                        $dir1 = $this->get_custom_ext_path() . $obj->folder_name;
                        $dir2 = APFFW_EXT_PATH . $obj->folder_name;
                        $checked1 = APFFW_EXT::is_ext_activated($dir1);
                        $checked2 = APFFW_EXT::is_ext_activated($dir2);
                        if ($checked1 OR $checked2) {
                            do_action('apffw_print_applications_tabs_content_' . $obj->folder_name);
                        }
                    }
                }
                ?>



                <section id="tabs-6">

                    <div class="apffw-tabs apffw-tabs-style-line">

                      

                        <div class="content-wrap">


                            <section id="tabs-61">

                              

                                <input type="hidden" name="apffw_settings[activated_extensions]" value="" />

                                <br><br>


                                <?php if (true): ?>


                                    <!-- ----------------------------------------- -->
                                    <?php if (isset($this->settings['custom_extensions_path']) AND!empty($this->settings['custom_extensions_path'])): ?>




                                        <div class="apffw-section-title">
                                            <div class="col-title">

                                                <h4><?php esc_html_e('Custom extensions installation', 'apffw-products-filter') ?></h4>

                                            </div>
                                            <div class="col-button">

                                                <?php
                                                $is_custom_extensions = false;
                                                if (is_dir($this->get_custom_ext_path())) {
                                                    //$dir_writable = substr(sprintf('%o', fileperms($this->get_custom_ext_path())), -4) == "0774" ? true : false;
                                                    $dir_writable = is_writable($this->get_custom_ext_path());
                                                    if ($dir_writable) {
                                                        $is_custom_extensions = true;
                                                    }
                                                } else {
                                                    if (!empty($this->settings['custom_extensions_path'])) {
                                                        //ext dir auto creation
                                                        $dir = $this->get_custom_ext_path();
                                                        try {
                                                            mkdir($dir, 0777);
                                                            $dir_writable = is_writable($this->get_custom_ext_path());
                                                            if ($dir_writable) {
                                                                $is_custom_extensions = true;
                                                            }
                                                        } catch (Exception $e) {
                                                            
                                                        }
                                                    }
                                                }
                                                
                                                if ($is_custom_extensions):
                                                    ?>
                                                    <input type="button" id="upload-btn" class="button apffw-button-outline-secondary" value="<?php esc_html_e('Choose an extension zip', 'apffw-products-filter') ?>">

                                                    <div id="errormsg" class="clearfix redtext"></div>

                                                    <div id="pic-progress-wrap" class="progress-wrap"></div>

                                                    <div id="picbox" class="clear"></div>

                                                <?php else: ?>
                                                    <span class="apffw_orangered"><?php printf(__('Note for admin: Folder %s for extensions is not writable OR doesn exists! Ignore this message if you not planning using APFFW custom extensions!', 'apffw-products-filter'), $this->get_custom_ext_path()) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php if (!empty($this->settings['custom_extensions_path'])): ?>
                                            <span class="apffw_orangered"><?php esc_html_e('Note for admin: Create folder for custom extensions in wp-content folder: tab Advanced -> Options -> Custom extensions folder', 'apffw-products-filter') ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <!-- ----------------------------------------- -->

                                    <?php
                                    if (!isset($apffw_settings['activated_extensions']) OR!is_array($apffw_settings['activated_extensions'])) {
                                        $apffw_settings['activated_extensions'] = array();
                                    }
                                    ?>
                                    <?php if (!empty($extensions) AND is_array($extensions)): ?>

                                        <input type="hidden" id="rm-ext-nonce" value="<?php _e(wp_create_nonce('rm-ext-nonce'));?>">
                                        <ul class="apffw_extensions apffw_custom_extensions">

                                            <?php foreach ($extensions['custom'] as $dir): ?>
                                                <?php
                                                $checked = APFFW_EXT::is_ext_activated($dir);
                                                $idx = APFFW_EXT::get_ext_idx_new($dir);
                                                ?>
                                                <li class="apffw_ext_li <?php _e(($checked ? 'is_enabled' : 'is_disabled'));?>">
                                                    <?php
                                                    $info = array();
                                                    if (file_exists($dir . DIRECTORY_SEPARATOR . 'info.dat')) {
                                                        $info = APFFW_HELPER::parse_ext_data($dir . DIRECTORY_SEPARATOR . 'info.dat');
                                                    }
                                                    ?>
                                                    <div class="apffw_ext-cell">
                                                        <label for="<?php _e(esc_attr($idx));?>">
                                                            <input type="checkbox" id="<?php _e(esc_attr($idx));?>" <?php if (isset($info['status']) AND $info['status'] == 'premium' AND $this->is_free_ver): ?>disabled="disabled"<?php endif; ?> <?php if ($checked): ?>checked=""<?php endif; ?> value="<?php _e($idx);?>" name="apffw_settings[activated_extensions][]" />
                                                            <?php
                                                            _e('<h5>' . esc_html($info['title']) . '</h5>');
                                                            if (isset($info['link'])) {
                                                                _e('<a href="' . esc_attr($info['link']) . '" class="apffw_ext_title" target="_blank"><span class="icon-link"></span></a>');
                                                            }
                                                            ?>
                                                            <span class="apffw_ext_ver">
                                                                <?php
                                                                if (isset($info['version'])) {
                                                                    printf(__('<i>ver.</i> %s', 'apffw-products-filter'), $info['version']);
                                                                }
                                                                ?>
                                                            </span>
                                                        </label>

                                                        <?php
                                                        if (!empty($info)) {
                                                            if (!empty($info) AND is_array($info)) {
                                                                if (isset($info['description'])) {
                                                                    _e('<p class="description">' . $info['description'] . '</p>');
                                                                }
                                                            } else {
                                                                _e($dir);
                                                                _e('You should write extension info in info.dat file!', 'apffw-products-filter');
                                                            }
                                                        } else {
                                                            printf(__('Looks like its not the APFFW extension here %s!', 'apffw-products-filter'), $dir);
                                                        }
                                                        ?>
                                                    </div>

                                                    <div class="apffw_ext-cell">
                                                        <a href="javascript:void(0)" class="apffw_ext_remove" data-idx="<?php _e($idx);?>" title="<?php _e('remove extension', 'apffw-products-filter') ?>">
                                                            <span class="icon-plus-circle"></span>
                                                        </a>
                                                    </div>

                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="clear clearfix"></div>
                                    <br />
                                    <hr />

                                    <?php if (!empty($extensions['default'])): ?>


                                        <div class="apffw-section-title">
                                            <div class="col-title">

                                                <h4><?php esc_html_e('Addons', 'apffw-products-filter') ?></h4>

                                            </div>
                                            <div class="col-button">&nbsp;</div>
                                        </div>


                                        <ul class="apffw_extensions">
                                            <?php foreach ($extensions['default'] as $dir): ?>
                                                <?php
                                                $checked = APFFW_EXT::is_ext_activated($dir);
                                                $idx = APFFW_EXT::get_ext_idx_new($dir);
                                                ?>
                                                <li class="apffw_ext_li <?php _e($checked ? 'is_enabled' : 'is_disabled');?>">
                                                    <?php
                                                    $info = array();
                                                    if (file_exists($dir . DIRECTORY_SEPARATOR . 'info.dat')) {
                                                        $info = APFFW_HELPER::parse_ext_data($dir . DIRECTORY_SEPARATOR . 'info.dat');
                                                    }
                                                    ?>
                                                    <div class="apffw_ext-cell">
                                                        <?php
                                                        if (!empty($info)) {
                                                            $info = APFFW_HELPER::parse_ext_data($dir . DIRECTORY_SEPARATOR . 'info.dat');
                                                            if (!empty($info) AND is_array($info)) {
                                                                ?>
                                                                <label for="<?php _e(esc_attr($idx));?>">
                                                                    <input type="checkbox" id="<?php _e(esc_attr($idx));?>" <?php if (isset($info['status']) AND $info['status'] == 'premium'): ?>disabled="disabled"<?php endif; ?> <?php if ($checked): ?>checked=""<?php endif; ?> value="<?php _e(esc_attr($idx));?>" name="apffw_settings[activated_extensions][]" />
                                                                    <?php
                                                                    _e('<h5>' . esc_html($info['title']) . '</h5>');                                                                    
                                                                    ?>                                                                    
                                                                </label>
                                                                <?php
                                                                
                                                            } else {
                                                                _e($dir);
                                                                _e('You should write extension info in info.dat file!', 'apffw-products-filter');
                                                            }
                                                        } else {
                                                            _e($dir);
                                                        }
                                                        ?>
                                                    </div>

                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                <?php endif; ?>
                                <div class="clear"></div>


                            </section>


                            <section id="tabs-62">

                                <div class="apffw-tabs apffw-tabs-style-line">

                                    <nav class="apffw_ext_nav">
                                        <ul>
                                            <?php
                                            $is_custom_extensions = false;
                                            if (is_dir($this->get_custom_ext_path())) {
                                                $dir_writable = is_writable($this->get_custom_ext_path());
                                                if ($dir_writable) {
                                                    $is_custom_extensions = true;
                                                }
                                            }

                                            if ($is_custom_extensions) {
                                                if (!empty(APFFW_EXT::$includes['applications'])) {
                                                    foreach (APFFW_EXT::$includes['applications'] as $obj) {

                                                        $dir = $this->get_custom_ext_path() . $obj->folder_name;
                                                        $checked = APFFW_EXT::is_ext_activated($dir);
                                                        if (!$checked) {
                                                            continue;
                                                        }
                                                        ?>
                                                        <li>

                                                            <?php
                                                            if (file_exists($dir . DIRECTORY_SEPARATOR . 'info.dat')) {
                                                                $info = APFFW_HELPER::parse_ext_data($dir . DIRECTORY_SEPARATOR . 'info.dat');
                                                                if (!empty($info) AND is_array($info)) {
                                                                    $name = $info['title'];
                                                                } else {
                                                                    $name = $obj->folder_name;
                                                                }
                                                            } else {
                                                                $name = $obj->folder_name;
                                                            }
                                                            ?>
                                                            <a href="#tabs-<?php _e(sanitize_title($obj->folder_name));?>" title="<?php printf(esc_html__("%s", 'apffw-products-filter'), $name) ?>">
                                                                <span><?php printf(esc_html__("%s", 'apffw-products-filter'), $name) ?></span>
                                                            </a>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>


                                        </ul>
                                    </nav>


                                    <div class="content-wrap apffw_ext_opt">

                                        <?php
                                        if ($is_custom_extensions) {
                                            if (!empty(APFFW_EXT::$includes['applications'])) {
                                                foreach (APFFW_EXT::$includes['applications'] as $obj) {

                                                    $dir = $this->get_custom_ext_path() . $obj->folder_name;
                                                    $checked = APFFW_EXT::is_ext_activated($dir);
                                                    if (!$checked) {
                                                        continue;
                                                    }
                                                    do_action('apffw_print_applications_options_' . $obj->folder_name);
                                                }
                                            }
                                        }
                                        ?>

                                    </div>


                                    <div class="clear"></div>

                                </div>




                            </section>

                        </div>

                    </div>

                </section>



                

            </div>

           

        </div>


    </section><!--/ .apffw-section-->

    <div id="apffw-modal-content" style="display: none;">

        <div class="apffw_option_container apffw_option_all">

            <div class="apffw-form-element-container">

                <div class="apffw-name-description">
                    <strong><?php esc_html_e('Show title label', 'apffw-products-filter') ?></strong>
                    <span><?php esc_html_e('Show/Hide taxonomy block title on the front', 'apffw-products-filter') ?></span>
                </div>

                <div class="apffw-form-element">

                    <div class="select-wrap">
                        <select class="apffw_popup_option" data-option="show_title_label">
                            <option value="0"><?php esc_html_e('No', 'apffw-products-filter') ?></option>
                            <option value="1"><?php esc_html_e('Yes', 'apffw-products-filter') ?></option>
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

                    <div class="select-wrap">
                        <select class="apffw_popup_option" data-option="show_toggle_button">
                            <option value="0"><?php esc_html_e('No', 'apffw-products-filter') ?></option>
                            <option value="1"><?php esc_html_e('Yes, show as closed', 'apffw-products-filter') ?></option>
                            <option value="2"><?php esc_html_e('Yes, show as opened', 'apffw-products-filter') ?></option>
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

        </div>


        <div class="apffw_option_container apffw_option_all">

            <div class="apffw-form-element-container">

                <div class="apffw-name-description">
                    <strong><?php esc_html_e('Not toggled terms count', 'apffw-products-filter') ?></strong>
                    <span><?php esc_html_e('Enter count of terms which should be visible to make all other collapsible. "Show more" button will be appeared. This feature is works with: radio, checkboxes, labels, colors.', 'apffw-products-filter') ?></span>
                    <span><?php printf(__('Advanced info is <a href="%s" target="_blank">here</a>', 'apffw-products-filter'), 'https://demofilter.vrinsoft.in/hook/apffw_get_more_less_button_xxxx/') ?></span>
                </div>

                <div class="apffw-form-element">
                    <input type="text" class="apffw_popup_option regular-text code" data-option="not_toggled_terms_count" placeholder="<?php esc_html_e('leave it empty to show all terms', 'apffw-products-filter') ?>" value="0" />
                </div>

            </div>

        </div>

        <div class="apffw_option_container apffw_option_all">

            <div class="apffw-form-element-container">

                <div class="apffw-name-description">
                    <strong><?php esc_html_e('Taxonomy custom label', 'apffw-products-filter') ?></strong>
                    <span><?php esc_html_e('For example you want to show title of Product Categories as "My Products". Just for your convenience.', 'apffw-products-filter') ?></span>
                </div>

                <div class="apffw-form-element">
                    <input type="text" class="apffw_popup_option regular-text code" data-option="custom_tax_label" placeholder="<?php esc_html_e('leave it empty to use native taxonomy name', 'apffw-products-filter') ?>" value="0" />
                </div>

            </div>

        </div>

        <div class="apffw_option_container apffw_option_radio apffw_option_checkbox apffw_option_label">

            <div class="apffw-form-element-container">

                <div class="apffw-name-description">
                    <strong><?php esc_html_e('Max height of the block', 'apffw-products-filter') ?></strong>
                    <span><?php esc_html_e('Container max-height (px). 0 means no max-height.', 'apffw-products-filter') ?></span>
                </div>

                <div class="apffw-form-element">
                    <input type="text" class="apffw_popup_option regular-text code" data-option="tax_block_height" placeholder="<?php esc_html_e('Max height of  the block', 'apffw-products-filter') ?>" value="0" />
                </div>

            </div>

        </div>

        <div class="apffw_option_container apffw_option_radio apffw_option_checkbox">

            <div class="apffw-form-element-container">

                <div class="apffw-name-description">
                    <strong><?php esc_html_e('Display items in a row', 'apffw-products-filter') ?></strong>
                    <span><?php esc_html_e('Works for radio and checkboxes only. Allows show radio/checkboxes in 1 row!', 'apffw-products-filter') ?></span>
                </div>

                <div class="apffw-form-element">

                    <div class="select-wrap">
                        <select class="apffw_popup_option" data-option="dispay_in_row">
                            <option value="0"><?php esc_html_e('No', 'apffw-products-filter') ?></option>
                            <option value="1"><?php esc_html_e('Yes', 'apffw-products-filter') ?></option>
                        </select>
                    </div>

                </div>

            </div>

        </div>

        <div class="apffw_option_container  apffw_option_all">

            <div class="apffw-form-element-container">

                <div class="apffw-name-description">
                    <strong><?php esc_html_e('Sort terms', 'apffw-products-filter') ?></strong>
                    <span><?php esc_html_e('How to sort terms inside of filter block', 'apffw-products-filter') ?></span>
                </div>

                <div class="apffw-form-element">

                    <div class="select-wrap">
                        <select class="apffw_popup_option" data-option="orderby">
                            <option value="-1"><?php esc_html_e('Default', 'apffw-products-filter') ?></option>
                            <option value="id"><?php esc_html_e('Id', 'apffw-products-filter') ?></option>
                            <option value="name"><?php esc_html_e('Title', 'apffw-products-filter') ?></option>
                            <option value="numeric"><?php esc_html_e('Numeric.', 'apffw-products-filter') ?></option>

                        </select>
                    </div>

                </div>

            </div>

        </div>
        <div class="apffw_option_container  apffw_option_all">

            <div class="apffw-form-element-container">

                <div class="apffw-name-description">
                    <strong><?php esc_html_e('Sort terms', 'apffw-products-filter') ?></strong>
                    <span><?php esc_html_e('Direction of terms sorted inside of filter block', 'apffw-products-filter') ?></span>
                </div>

                <div class="apffw-form-element">

                    <div class="select-wrap">
                        <select class="apffw_popup_option" data-option="order">
                            <option value="ASC"><?php esc_html_e('ASC', 'apffw-products-filter') ?></option>
                            <option value="DESC"><?php esc_html_e('DESC', 'apffw-products-filter') ?></option>
                        </select>
                    </div>

                </div>

            </div>

        </div>

        <div class="apffw_option_container apffw_option_all ">

            <div class="apffw-form-element-container">

                <div class="apffw-name-description">
                    <strong><?php esc_html_e('Logic of filtering', 'apffw-products-filter') ?></strong>
                    <span><?php esc_html_e('AND or OR: if to select AND and on the site front select 2 terms - will be found products which contains both terms on the same time.', 'apffw-products-filter') ?></span>
                    <span><?php esc_html_e('If to select NOT IN will be found items which not has selected terms!! Means vice versa to the the concept of including: excluding', 'apffw-products-filter') ?></span>
                </div>
                <div class="apffw-form-element">

                    <div class="select-wrap">
                        <select class="apffw_popup_option" data-option="comparison_logic">
                            <option value="OR"><?php esc_html_e('OR', 'apffw-products-filter') ?></option>
                            <option class="apffw_option_checkbox apffw_option_mselect apffw_option_image apffw_option_color apffw_option_label apffw_option_select_radio_check" value="AND" style="display: none;"><?php esc_html_e('AND', 'apffw-products-filter') ?></option>
                            <option value="NOT IN"><?php esc_html_e('NOT IN', 'apffw-products-filter') ?></option>
                        </select>
                    </div>

                </div>

            </div>

        </div>
        <!------------- options for extensions ------------------------>

        <?php
        if (!empty(APFFW_EXT::$includes['taxonomy_type_objects'])) {
            foreach (APFFW_EXT::$includes['taxonomy_type_objects'] as $obj) {
                if (!empty($obj->taxonomy_type_additional_options)) {
                    foreach ($obj->taxonomy_type_additional_options as $key => $option) {
                        switch ($option['type']) {
                            case 'select':
                                ?>
                                <div class="apffw_option_container apffw_option_<?php _e($obj->html_type);?>">

                                    <div class="apffw-form-element-container">

                                        <div class="apffw-name-description">
                                            <strong><?php _e($option['title']);?></strong>
                                            <span><?php _e($option['tip']);?></span>
                                        </div>

                                        <div class="apffw-form-element">

                                            <div class="select-wrap">
                                                <select class="apffw_popup_option" data-option="<?php _e($key);?>">
                                                    <?php foreach ($option['options'] as $val => $title): ?>
                                                        <option value="<?php _e($val);?>"><?php _e($title);?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                                <?php
                                break;

                            case 'text':
                                ?>
                                <div class="apffw_option_container apffw_option_<?php _e($obj->html_type);?>">

                                    <div class="apffw-form-element-container">

                                        <div class="apffw-name-description">
                                            <strong><?php _e($option['title']);?></strong>
                                            <span><?php _e($option['tip']);?></span>
                                        </div>

                                        <div class="apffw-form-element">
                                            <input type="text" class="apffw_popup_option regular-text code" data-option="<?php _e($key);?>" placeholder="<?php _e(isset($option['placeholder']) ? $option['placeholder'] : '');?>" value="" />
                                        </div>

                                    </div>

                                </div>
                                <?php
                                break;

                            case 'image':
                                ?>
                                <div class="apffw_option_container apffw_option_<?php _e($obj->html_type);?>">

                                    <div class="apffw-form-element-container">

                                        <div class="apffw-name-description">
                                            <strong><?php _e($option['title']);?></strong>
                                            <span><?php _e($option['tip']);?></span>
                                        </div>

                                        <div class="apffw-form-element">
                                            <input type="text" class="apffw_popup_option regular-text code" data-option="<?php _e($key);?>" placeholder="<?php _e($option['placeholder']);?>" value="" />
                                            <a href="#" class="button apffw_select_image"><?php esc_html_e('select image', 'apffw-products-filter') ?></a>
                                        </div>

                                    </div>

                                </div>
                                <?php
                                break;

                            default:
                                break;
                        }
                    }
                }
            }
        }
        ?>

    </div>

    <div id="apffw_ext_tpl" style="display: none;">
        <li class="apffw_ext_li is_disabled">

            <table class="apffw_width_100p">
                <tbody>
                    <tr>
                        <td class="apffw_valign_top">
                            <img alt="ext cover" src="<?php _e(APFFW_LINK);?>img/apffw_ext_cover.png" width="85">
                        </td>
                        <td><div class="apffw_width_5px"></div></td>
                        <td class="apffw_fix16">
                            <a href="#" class="apffw_ext_remove" data-title="__TITLE__" data-idx="__IDX__" title="<?php esc_html_e('remove extension', 'apffw-products-filter') ?>"><img src="<?php _e($this->settings['delete_image']);?>" alt="<?php esc_html_e('remove extension', 'apffw-products-filter') ?>" /></a>
                            <label for="__IDX__">
                                <input type="checkbox" name="__NAME__" value="__IDX__" id="__IDX__">
                                __TITLE__
                            </label><br>
                            ver.: __VERSION__<br><p class="description">__DESCRIPTION__</p>
                        </td>
                    </tr>
                </tbody>
            </table>

        </li>
    </div>

    <div id="apffw-modal-content-by_price" style="display: none;">

        <div class="apffw-form-element-container">

            <div class="apffw-name-description">
                <strong><?php esc_html_e('Show button', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('Show button for woocommerce filter by price inside apffw search form when it is dispayed As range-slider', 'apffw-products-filter') ?></span>
            </div>

            <div class="apffw-form-element">

                <?php
                $show_button = array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                );
                ?>

                <div class="select-wrap">
                    <select class="apffw_popup_option" data-option="show_button">
                        <?php foreach ($show_button as $key => $value) : ?>
                            <option value="<?php _e($key);?>"><?php _e($value);?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

        </div>

        <div class="apffw-form-element-container">

            <div class="apffw-name-description">
                <strong><?php esc_html_e('Title text', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('Text before the price filter range slider. Leave it empty if you not need it!', 'apffw-products-filter') ?></span>
            </div>

            <div class="apffw-form-element">
                <input type="text" class="apffw_popup_option" data-option="title_text" placeholder="" value="" />
            </div>

        </div>
        <div class="apffw-form-element-container">

            <div class="apffw-name-description">
                <strong><?php esc_html_e('Show toggle button', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('Show toggle button near the title on the front above the block of html-items', 'apffw-products-filter') ?></span>
            </div>

            <div class="apffw-form-element">
                <div class="select-wrap">
                    <select class="apffw_popup_option" data-option="show_toggle_button">
                        <option value="0"><?php esc_html_e('No', 'apffw-products-filter') ?></option>
                        <option value="1"><?php esc_html_e('Yes, show as closed', 'apffw-products-filter') ?></option>
                        <option value="2"><?php esc_html_e('Yes, show as opened', 'apffw-products-filter') ?></option>
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
                <h3><?php esc_html_e('Drop-down OR radio', 'apffw-products-filter') ?></h3>
                <strong><?php esc_html_e('Drop-down OR radio price filter ranges', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('Ranges for price filter.', 'apffw-products-filter') ?></span>
                <span><?php _e(esc_html__('Example: 0-50,51-100,101-i. Where "i" is infinity.', 'apffw-products-filter'));?></span>
            </div>

            <div class="apffw-form-element">
                <input type="text" class="apffw_popup_option" data-option="ranges" placeholder="" value="" />
            </div>

        </div>

        <div class="apffw-form-element-container">

            <div class="apffw-name-description">
                <strong><?php esc_html_e('Drop-down price filter text', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('Drop-down price filter first option text', 'apffw-products-filter') ?></span>
            </div>

            <div class="apffw-form-element">
                <input type="text" class="apffw_popup_option" data-option="first_option_text" placeholder="" value="" />
            </div>

        </div>

        <div class="apffw-form-element-container">

            <div class="apffw-name-description">
                <h3><?php esc_html_e('Ion Range slider', 'apffw-products-filter') ?></h3>
                <strong><?php esc_html_e('Step', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('predifined step', 'apffw-products-filter') ?></span>
            </div>

            <div class="apffw-form-element">
                <input type="text" class="apffw_popup_option" data-option="ion_slider_step" placeholder="" value="" />
            </div>

        </div>
        <div class="apffw-form-element-container">

            <div class="apffw-name-description">
                <strong><?php esc_html_e('Show price text inputs', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('This works with ionSlider only', 'apffw-products-filter') ?></span>
            </div>

            <div class="apffw-form-element">
                <div class="select-wrap">
                    <select class="apffw_popup_option" data-option="show_text_input">
                        <option value="0"><?php esc_html_e('No', 'apffw-products-filter') ?></option>
                        <option value="1"><?php esc_html_e('Yes', 'apffw-products-filter') ?></option>
                    </select>
                </div>

            </div>

        </div>		
        <div class="apffw-form-element-container">

            <div class="apffw-name-description">
                <h3><?php esc_html_e('Taxes', 'apffw-products-filter') ?></h3>
                <strong><?php esc_html_e('Tax', 'apffw-products-filter') ?></strong>
                <span><?php esc_html_e('It will be counted in the filter( Only for ion-slider )', 'apffw-products-filter') ?></span>
            </div>

            <div class="apffw-form-element">
                <input type="text" class="apffw_popup_option" data-option="price_tax" placeholder="" value="" />
            </div>

        </div>


    </div>



    <div id="apffw_buffer" style="display: none;"></div>

    <div id="apffw_html_buffer" class="apffw_info_popup" style="display: none;"></div>




</div>


<svg xmlns="http://www.w3.org/2000/svg" hidden>
<symbol id="svg-apffw" viewBox="0 0 487 512">

    <g>
    <path d="M433.564 329.127c-.446 17.167-33.615 29.09-20.112 46.152c29.805 4.185 16.249 23.939 32.578 30.662c-28.865 47.002-75.448 52.873-119.742 23.44c-12.886-7.684-27.396-10.94-39.291-7.736c-6.776 10.84 3.305 12.118 9.732 13.688c31.15 6.201 62.553 54.347 167.968 8.487c14.046-5.912 11.916-23.308 5.834-36.985c11.683-4.978 12.384-17.96 14.106-34.646c11.46-37.616-26.181-34.365-51.073-43.062zm-165.728-81.688c-12.854.288-31.777 12.464-44.28 18.524c17.643 10.378 24.651 22.727 44.28 18.87c19.636-3.856 19.278-16.5 11.553-31.189c-2.423-4.607-6.523-6.317-11.553-6.205zM350.783.014c-23.986 1.1-44.619 65.358-69.083 110.904c-70.332 29.251-87.93-8.677-110.895-2.439C131.69 28.753 73.613-51.179 57.92 43.822c3.47 71.804-25.19 77.72-22.655 180.173c0 0-51.119 102.887-30.263 193.927C19.697 482.073 71.373 506.284 106.082 512c3.592-26.24 44.78-85.41 33.515-99.217c-28.108-34.451-19.29-118.846 51.948-127.569c9.732-70.992 58.03-57.629 53.089-78.164c-15.482-64.332 51.32-34.302 62.531-13.222c20.144 33.427 34.308 66.281 50.774 81.853c20.941 19.805 49.347 30.623 51.01 31.247c-5.968-3.73-57.183-47.055-72.168-81.854c-9.012-20.93-8.106-68.08-8.106-68.08c25.985-4.065 36.469 39.655 64.69 79.32c1.168 2.857.962 5.144 0 7.05c-3.893-2.053-5.246-7.202-9.918-7.82c-4.254 0-7.703 6.036-7.703 13.479s5.614 10.1 13.347 13.58c10.781 18.973 21.986 25.945 32.767 30.699L387.07 191.65c-1.29-63.662 5.444-127.327-31.442-190.99a13.923 13.923 0 0 0-4.845-.646zm.333 20.297c1.927-.033 3.707 1.267 5.282 4.227c19.253 36.195 19.963 121.15 19.831 151.184c-9.143-20.808-22.843-55.803-66.035-66.473c0 0 25.231-88.665 40.922-88.938zM92.392 28.385c8.777-.037 18.752 5.106 30.324 26.207c18.515 33.762 31.801 56.222 38.25 67.847c13.201 23.796-5.78 4.98-11.722 66.035c-1.174 12.056-5.726 17.854-14.65 23.973c-19.315-19.306-32.938-52.81-42.343-92.643c-18.44 27.387 11.933 69.735 12.473 105.95c-20.488 4.683-33.772 3.045-41.352.518c-9.505-4.752-12.637-62.723 4.994-107.725C83.052 81.063 76.071 33.4 82.6 31.323c4.492-1.43 4.525-2.916 9.792-2.938z"/>
    </g>

</symbol>


<symbol id="svg-apffw" viewBox="0 0 487 512">

    <g>
    <path d="M433.564 329.127c-.446 17.167-33.615 29.09-20.112 46.152c29.805 4.185 16.249 23.939 32.578 30.662c-28.865 47.002-75.448 52.873-119.742 23.44c-12.886-7.684-27.396-10.94-39.291-7.736c-6.776 10.84 3.305 12.118 9.732 13.688c31.15 6.201 62.553 54.347 167.968 8.487c14.046-5.912 11.916-23.308 5.834-36.985c11.683-4.978 12.384-17.96 14.106-34.646c11.46-37.616-26.181-34.365-51.073-43.062zm-165.728-81.688c-12.854.288-31.777 12.464-44.28 18.524c17.643 10.378 24.651 22.727 44.28 18.87c19.636-3.856 19.278-16.5 11.553-31.189c-2.423-4.607-6.523-6.317-11.553-6.205zM350.783.014c-23.986 1.1-44.619 65.358-69.083 110.904c-70.332 29.251-87.93-8.677-110.895-2.439C131.69 28.753 73.613-51.179 57.92 43.822c3.47 71.804-25.19 77.72-22.655 180.173c0 0-51.119 102.887-30.263 193.927C19.697 482.073 71.373 506.284 106.082 512c3.592-26.24 44.78-85.41 33.515-99.217c-28.108-34.451-19.29-118.846 51.948-127.569c9.732-70.992 58.03-57.629 53.089-78.164c-15.482-64.332 51.32-34.302 62.531-13.222c20.144 33.427 34.308 66.281 50.774 81.853c20.941 19.805 49.347 30.623 51.01 31.247c-5.968-3.73-57.183-47.055-72.168-81.854c-9.012-20.93-8.106-68.08-8.106-68.08c25.985-4.065 36.469 39.655 64.69 79.32c1.168 2.857.962 5.144 0 7.05c-3.893-2.053-5.246-7.202-9.918-7.82c-4.254 0-7.703 6.036-7.703 13.479s5.614 10.1 13.347 13.58c10.781 18.973 21.986 25.945 32.767 30.699L387.07 191.65c-1.29-63.662 5.444-127.327-31.442-190.99a13.923 13.923 0 0 0-4.845-.646zm.333 20.297c1.927-.033 3.707 1.267 5.282 4.227c19.253 36.195 19.963 121.15 19.831 151.184c-9.143-20.808-22.843-55.803-66.035-66.473c0 0 25.231-88.665 40.922-88.938zM92.392 28.385c8.777-.037 18.752 5.106 30.324 26.207c18.515 33.762 31.801 56.222 38.25 67.847c13.201 23.796-5.78 4.98-11.722 66.035c-1.174 12.056-5.726 17.854-14.65 23.973c-19.315-19.306-32.938-52.81-42.343-92.643c-18.44 27.387 11.933 69.735 12.473 105.95c-20.488 4.683-33.772 3.045-41.352.518c-9.505-4.752-12.637-62.723 4.994-107.725C83.052 81.063 76.071 33.4 82.6 31.323c4.492-1.43 4.525-2.916 9.792-2.938z"/>
    </g>

</symbol>

</svg>




<?php

function apffw_print_tax($key, $tax, $apffw_settings) {
    global $APFFW;
    ?>
    <li data-key="<?php _e($key);?>" class="apffw_options_li">
        <span class="icon-arrow-combo help_tip apffw_drag_and_drope" data-tip="<?php esc_html_e("drag and drope", 'apffw-products-filter'); ?>"></span>

        <div class="select-wrap">
            <select name="apffw_settings[tax_type][<?php _e($key);?>]" class="apffw_select_tax_type">
                <?php foreach ($APFFW->html_types as $type => $type_text) : ?>
                    <option value="<?php _e($type);?>" <?php if (isset($apffw_settings['tax_type'][$key])) _e(selected($apffw_settings['tax_type'][$key], $type)); ?>><?php _e($type_text);?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <span class="icon-question help_tip" data-tip="<?php esc_html_e('View of the taxonomies terms on the front', 'apffw-products-filter') ?>"></span>

        <?php
        $excluded_terms = '';
        if (isset($apffw_settings['excluded_terms'][$key])) {
            $excluded_terms = $apffw_settings['excluded_terms'][$key];
        }

        $excluded_terms_reverse = 0;
        if (isset($apffw_settings['excluded_terms_reverse'][$key])) {
            $excluded_terms_reverse = $apffw_settings['excluded_terms_reverse'][$key];
        }
        ?>

        <input type="text" class="apffw_excluded_terms" name="apffw_settings[excluded_terms][<?php _e($key);?>]" placeholder="<?php esc_html_e('excluded terms ids', 'apffw-products-filter') ?>" value="<?php _e($excluded_terms);?>" />
        <?php $rev_id = uniqid('re-') ?>
        <input <?php _e(((isset($APFFW->settings['excluded_terms_reverse']) ? is_array($APFFW->settings['excluded_terms_reverse']) : FALSE) ? in_array($key, (array) array_keys($APFFW->settings['excluded_terms_reverse'])) : false) ? 'checked="checked"' : '');?> type="checkbox" name="apffw_settings[excluded_terms_reverse][<?php _e($key);?>]" id="<?php _e($rev_id);?>" value="1" />
        <label class="apffw_fix17" for="<?php _e($rev_id);?>"><?php esc_html_e('Reverse', 'apffw-products-filter') ?></label>


        <span class="icon-question help_tip" data-tip="<?php esc_html_e('If you want to exclude some current taxonomies terms from the search form! Use Reverse if you want include only instead of exclude! Example: 11,23,77', 'apffw-products-filter') ?>"></span>
        <a href="#" data-taxonomy="<?php _e($key);?>" data-taxonomy-name="<?php _e($tax->labels->name);?>" class="apffw-button js_apffw_add_options help_tip" data-tip="<?php esc_html_e('additional options', 'apffw-products-filter') ?>"><span class="icon-cog-outline"></span></a>



        <div style="display: none;">
            <?php
            $max_height = 0;
            if (isset($apffw_settings['tax_block_height'][$key])) {
                $max_height = $apffw_settings['tax_block_height'][$key];
            }
            ?>
            <input type="text" name="apffw_settings[tax_block_height][<?php _e($key);?>]" placeholder="" value="<?php _e($max_height);?>" />
            <?php
            $show_title_label = 0;
            if (isset($apffw_settings['show_title_label'][$key])) {
                $show_title_label = $apffw_settings['show_title_label'][$key];
            }
            ?>
            <input type="text" name="apffw_settings[show_title_label][<?php _e($key);?>]" placeholder="" value="<?php _e($show_title_label);?>" />


            <?php
            $show_toggle_button = 0;
            if (isset($apffw_settings['show_toggle_button'][$key])) {
                $show_toggle_button = $apffw_settings['show_toggle_button'][$key];
            }
            ?>
            <input type="text" name="apffw_settings[show_toggle_button][<?php _e($key);?>]" placeholder="" value="<?php _e($show_toggle_button);?>" />


            <?php
            $tooltip_text = "";
            if (isset($apffw_settings['tooltip_text'][$key])) {
                $tooltip_text = stripcslashes($apffw_settings['tooltip_text'][$key]);
            }
            ?>
            <input type="text" name="apffw_settings[tooltip_text][<?php _e($key);?>]" placeholder="" value="<?php _e($tooltip_text);?>" />

            <?php
            $dispay_in_row = 0;
            if (isset($apffw_settings['dispay_in_row'][$key])) {
                $dispay_in_row = $apffw_settings['dispay_in_row'][$key];
            }
            ?>
            <input type="text" name="apffw_settings[dispay_in_row][<?php _e($key);?>]" placeholder="" value="<?php _e($dispay_in_row);?>" />


            <?php
            $orderby = '-1';
            if (isset($apffw_settings['orderby'][$key])) {
                $orderby = $apffw_settings['orderby'][$key];
            }
            ?>
            <input type="text" name="apffw_settings[orderby][<?php _e($key);?>]" placeholder="" value="<?php _e($orderby);?>" />

            <?php
            $order = 'ASC';
            if (isset($apffw_settings['order'][$key])) {
                $order = $apffw_settings['order'][$key];
            }
            ?>
            <input type="text" name="apffw_settings[order][<?php _e($key);?>]" placeholder="" value="<?php _e($order);?>" />
            <?php
            $comparison_logic = 'OR';
            $logic_restriction = array('checkbox', 'mselect', 'label', 'color', 'image', 'slider', 'select_hierarchy');
            if (isset($apffw_settings['comparison_logic'][$key])) {
                $comparison_logic = $apffw_settings['comparison_logic'][$key];
            }
            if (isset($apffw_settings['tax_type'][$key]) AND!in_array($apffw_settings['tax_type'][$key], $logic_restriction) AND $comparison_logic == 'AND') {
                $comparison_logic = 'OR';
            }

            if ($comparison_logic == 'NOT IN' AND $apffw_settings['tax_type'][$key] == 'select_hierarchy') {
                $comparison_logic = 'OR';
            }
            ?>
            <input type="text" name="apffw_settings[comparison_logic][<?php _e($key);?>]" placeholder="" value="<?php _e($comparison_logic);?>" />

            <?php
            $custom_tax_label = '';
            if (isset($apffw_settings['custom_tax_label'][$key])) {
                $custom_tax_label = stripcslashes($apffw_settings['custom_tax_label'][$key]);
            }
            ?>
            <input type="text" name="apffw_settings[custom_tax_label][<?php _e($key);?>]" placeholder="" value="<?php _e($custom_tax_label);?>" />


            <?php
            $not_toggled_terms_count = '';
            if (isset($apffw_settings['not_toggled_terms_count'][$key])) {
                $not_toggled_terms_count = $apffw_settings['not_toggled_terms_count'][$key];
            }
            ?>
            <input type="text" name="apffw_settings[not_toggled_terms_count][<?php _e($key);?>]" placeholder="" value="<?php _e($not_toggled_terms_count);?>" />


            <!------------- options for extensions ------------------------>
            <?php
            if (!empty(APFFW_EXT::$includes['taxonomy_type_objects'])) {
                foreach (APFFW_EXT::$includes['taxonomy_type_objects'] as $obj) {
                    if (!empty($obj->taxonomy_type_additional_options)) {
                        foreach ($obj->taxonomy_type_additional_options as $option_key => $option) {
                            $option_val = 0;
                            if (isset($apffw_settings[$option_key][$key])) {
                                $option_val = $apffw_settings[$option_key][$key];
                            }
                            ?>
                            <input type="text" name="apffw_settings[<?php _e($option_key);?>][<?php _e($key);?>]" value="<?php _e($option_val);?>" />
                            <?php
                        }
                    }
                }
            }
            ?>




        </div>



        <input <?php _e(((isset($APFFW->settings['tax']) ? is_array($APFFW->settings['tax']) : FALSE) ? in_array($key, (array) array_keys($APFFW->settings['tax'])) : false) ? 'checked="checked"' : '');?> type="checkbox" name="apffw_settings[tax][<?php _e($key);?>]" id="tax_<?php _e(md5($key));?>" value="1" />
        <label for="tax_<?php _e(md5($key));?>"><b><?php _e($tax->labels->name);?></b></label>
        <?php
        if (isset($apffw_settings['tax_type'][$key])) {
            do_action('apffw_print_tax_additional_options_' . $apffw_settings['tax_type'][$key], $key);
        }
        ?>
    </li>
    <?php
}



function apffw_print_item_by_key($key, $apffw_settings) {

    switch ($key) {
        case 'by_price':

            if (!isset($apffw_settings[$key])) {
                $apffw_settings[$key] = [];
            }

            if (!is_array($apffw_settings)) {
                break;
            }
            ?>
            <li data-key="<?php _e($key);?>" class="apffw_options_li">

                <?php
                $show = 0;
                if (isset($apffw_settings[$key]['show'])) {
                    $show = $apffw_settings[$key]['show'];
                }
                ?>

                <span class="icon-arrow-combo help_tip apffw_drag_and_drope" data-tip="<?php esc_html_e("drag and drope", 'apffw-products-filter'); ?>"></span>


                <strong class="apffw_fix1"><?php esc_html_e("Search by Price", 'apffw-products-filter'); ?>:</strong>

                <span class="icon-question help_tip" data-tip="<?php esc_html_e('Show woocommerce filter by price inside apffw search form', 'apffw-products-filter') ?>"></span>


                <div class="select-wrap">
                    <select name="apffw_settings[<?php _e($key);?>][show]" class="apffw_setting_select">
                        <option value="0" <?php _e(selected($show, 0));?>><?php esc_html_e('No', 'apffw-products-filter') ?></option>
                        <option value="1" <?php _e(selected($show, 1));?>><?php esc_html_e('As range-slider', 'apffw-products-filter') ?></option>
                        <option value="2" <?php _e(selected($show, 2));?>><?php esc_html_e('As drop-down', 'apffw-products-filter') ?></option>
                        <option value="5" <?php _e(selected($show, 5));?>><?php esc_html_e('As radio button', 'apffw-products-filter') ?></option>
                        <option value="4" <?php _e(selected($show, 4));?>><?php esc_html_e('As textinputs', 'apffw-products-filter') ?></option>
                        <option value="3" <?php _e(selected($show, 3));?>><?php esc_html_e('As ion range-slider', 'apffw-products-filter') ?></option>

                    </select>
                </div>

                <a href="#" data-key="<?php _e($key);?>" data-name="<?php esc_html_e("Search by Price", 'apffw-products-filter'); ?>" class="apffw-button js_apffw_options js_apffw_options_<?php _e($key);?> help_tip" data-tip="<?php esc_html_e('additional options', 'apffw-products-filter') ?>"><span class="icon-cog-outline"></span></a>

                <?php
                if (!isset($apffw_settings[$key]['show_button'])) {
                    $apffw_settings[$key]['show_button'] = 0;
                }

                if (!isset($apffw_settings[$key]['title_text'])) {
                    $apffw_settings[$key]['title_text'] = '';
                }

                if (!isset($apffw_settings[$key]['show_toggle_button'])) {
                    $apffw_settings[$key]['show_toggle_button'] = 0;
                }
                if (!isset($apffw_settings[$key]['ranges'])) {
                    $apffw_settings[$key]['ranges'] = '';
                }

                if (!isset($apffw_settings[$key]['first_option_text'])) {
                    $apffw_settings[$key]['first_option_text'] = '';
                }

                if (!isset($apffw_settings[$key]['ion_slider_step'])) {
                    $apffw_settings[$key]['ion_slider_step'] = 0;
                }
                if (!isset($apffw_settings[$key]['price_tax'])) {
                    $apffw_settings[$key]['price_tax'] = 0;
                }
                if (!isset($apffw_settings[$key]['show_text_input'])) {
                    $apffw_settings[$key]['show_text_input'] = 0;
                }

                if (!isset($apffw_settings[$key]['tooltip_text'])) {
                    $apffw_settings[$key]['tooltip_text'] = "";
                }
                ?>
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][tooltip_text]" placeholder="" value="<?php _e(stripcslashes($apffw_settings[$key]['tooltip_text']));?>" />
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][show_button]" value="<?php _e($apffw_settings[$key]['show_button']);?>" />
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][title_text]" value="<?php _e($apffw_settings[$key]['title_text']);?>" />
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][show_toggle_button]" value="<?php _e($apffw_settings[$key]['show_toggle_button']);?>" />
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][ranges]" value="<?php _e($apffw_settings[$key]['ranges']);?>" />
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][first_option_text]" value="<?php _e($apffw_settings[$key]['first_option_text']);?>" />
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][ion_slider_step]" value="<?php _e($apffw_settings[$key]['ion_slider_step']);?>" />
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][price_tax]" value="<?php _e($apffw_settings[$key]['price_tax']);?>" />
                <input type="hidden" name="apffw_settings[<?php _e($key);?>][show_text_input]" value="<?php _e($apffw_settings[$key]['show_text_input']);?>" />
            </li>
            <?php
            break;

        default:
            do_action('apffw_print_html_type_options_' . $key);
            break;
    }
}
