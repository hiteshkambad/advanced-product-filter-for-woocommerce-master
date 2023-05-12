"use strict";
var apffw_redirect = '';
var apffw_reset_btn_action = false;
jQuery(function () {
try
{
apffw_current_values = JSON.parse(apffw_current_values);
} catch (e)
{
apffw_current_values = null;
}
if (apffw_current_values == null || apffw_current_values.length == 0) {
apffw_current_values = {};
}

});

if (typeof apffw_lang_custom == 'undefined') {
    var apffw_lang_custom = {};
}
if (typeof apffw_ext_filter_titles != 'undefined') {
    apffw_lang_custom = Object.assign({}, apffw_lang_custom, apffw_ext_filter_titles);
}

jQuery(function ($) {
    jQuery('body').append('<div id="apffw_html_buffer" class="apffw_info_popup" style="display: none;"></div>');

    jQuery.extend(jQuery.fn, {
        within: function (pSelector) {
            return this.filter(function () {
                return jQuery(this).closest(pSelector).length;
            });
        }
    });

    

    if (jQuery('#apffw_results_by_ajax').length > 0) {
        apffw_is_ajax = 1;
    }

    
    apffw_autosubmit = parseInt(jQuery('.apffw').eq(0).data('autosubmit'), 10);
    apffw_ajax_redraw = parseInt(jQuery('.apffw').eq(0).data('ajax-redraw'), 10);



    

    apffw_ext_init_functions = JSON.parse(apffw_ext_init_functions);

    
    apffw_init_native_woo_price_filter();


    jQuery('body').on('price_slider_change', function (event, min, max) {

        if (apffw_autosubmit && !apffw_show_price_search_button && jQuery('.price_slider_wrapper').length < 3) {

            jQuery('.apffw .widget_price_filter form').trigger('submit');

        } else {
            var min_price = jQuery(this).find('.price_slider_amount #min_price').val();
            var max_price = jQuery(this).find('.price_slider_amount #max_price').val();
            apffw_current_values.min_price = min_price;
            apffw_current_values.max_price = max_price;
        }
    });

    jQuery('body').on('change', '.apffw_price_filter_dropdown', function () {
        var val = jQuery(this).val();
        if (parseInt(val, 10) == -1) {
            delete apffw_current_values.min_price;
            delete apffw_current_values.max_price;
        } else {
            var val = val.split("-");
            apffw_current_values.min_price = val[0];
            apffw_current_values.max_price = val[1];
        }

        if (apffw_autosubmit || jQuery(this).within('.apffw').length == 0) {
            apffw_submit_link(apffw_get_submit_link());
        }
    });

    
    apffw_recount_text_price_filter();
    
    jQuery('body').on('change', '.apffw_price_filter_txt', function () {

        var from = parseInt(jQuery(this).parent().find('.apffw_price_filter_txt_from').val(), 10);
        var to = parseInt(jQuery(this).parent().find('.apffw_price_filter_txt_to').val(), 10);

        if (to < from || from < 0) {
            delete apffw_current_values.min_price;
            delete apffw_current_values.max_price;
        } else {
            if (typeof apffw_current_currency !== 'undefined') {
                from = Math.ceil(from / parseFloat(apffw_current_currency.rate));
                to = Math.ceil(to / parseFloat(apffw_current_currency.rate));
            }

            apffw_current_values.min_price = from;
            apffw_current_values.max_price = to;
        }

        if (apffw_autosubmit || jQuery(this).within('.apffw').length == 0) {
            apffw_submit_link(apffw_get_submit_link());
        }
    });


    

    jQuery('body').on('click', '.apffw_open_hidden_li_btn', function () {
        var state = jQuery(this).data('state');
        var type = jQuery(this).data('type');

        if (state == 'closed') {
            jQuery(this).parents('.apffw_list').find('.apffw_hidden_term').addClass('apffw_hidden_term2');
            jQuery(this).parents('.apffw_list').find('.apffw_hidden_term').removeClass('apffw_hidden_term');
            if (type == 'image') {
                jQuery(this).find('img').attr('src', jQuery(this).data('opened'));
            } else {
                jQuery(this).html(jQuery(this).data('opened'));
            }

            jQuery(this).data('state', 'opened');
        } else {
            jQuery(this).parents('.apffw_list').find('.apffw_hidden_term2').addClass('apffw_hidden_term');
            jQuery(this).parents('.apffw_list').find('.apffw_hidden_term2').removeClass('apffw_hidden_term2');

            if (type == 'image') {
                jQuery(this).find('img').attr('src', jQuery(this).data('closed'));
            } else {
                jQuery(this).text(jQuery(this).data('closed'));
            }

            jQuery(this).data('state', 'closed');
        }


        return false;
    });
    
    apffw_open_hidden_li();

    
    jQuery('.widget_rating_filter li.wc-layered-nav-rating a').on('click', function () {
        var is_chosen = jQuery(this).parent().hasClass('chosen');
        var parsed_url = apffw_parse_url(jQuery(this).attr('href'));
        var rate = 0;
        if (parsed_url.query !== undefined) {
            if (parsed_url.query.indexOf('min_rating') !== -1) {
                var arrayOfStrings = parsed_url.query.split('min_rating=');
                rate = parseInt(arrayOfStrings[1], 10);
            }
        }
        jQuery(this).parents('ul').find('li').removeClass('chosen');
        if (is_chosen) {
            delete apffw_current_values.min_rating;
        } else {
            apffw_current_values.min_rating = rate;
            jQuery(this).parent().addClass('chosen');
        }

        apffw_submit_link(apffw_get_submit_link());

        return false;
    });

    
    jQuery('body').on('click', '.apffw_start_filtering_btn', function () {

        var shortcode = jQuery(this).parents('.apffw').data('shortcode');
        jQuery(this).html(apffw_lang_loading);
        jQuery(this).addClass('apffw_start_filtering_btn2');
        jQuery(this).removeClass('apffw_start_filtering_btn');
        
        var data = {
            action: "apffw_draw_products",
            page: 1,
            shortcode: 'apffw_nothing', 
            apffw_shortcode: shortcode
        };

        jQuery.post(apffw_ajaxurl, data, function (content) {
            content = JSON.parse(content);
            jQuery('div.apffw_redraw_zone').replaceWith(jQuery(content.form).find('.apffw_redraw_zone'));
            apffw_mass_reinit();
	    apffw_init_tooltip();
        });


        return false;
    });

    
    var str = window.location.href;
    window.onpopstate = function (event) {
        try {
            if (Object.keys(apffw_current_values).length) {

                var temp = str.split('?');
                var get1 = "";
                if (temp[1] != undefined) {
                    get1 = temp[1].split('#');
                }
                var str2 = window.location.href;
                var temp2 = str2.split('?');
                if (temp2[1] == undefined) {
                    var get2 = {0: "", 1: ""};

                } else {
                    var get2 = temp2[1].split('#');
                }

                if (get2[0] != get1[0]) {
                    apffw_show_info_popup(apffw_lang_loading);
                    window.location.reload();
                }
                return false;
            }
        } catch (e) {
            console.log(e);
        }
    };
    

    
    apffw_init_ion_sliders();

    

    apffw_init_show_auto_form();
    apffw_init_hide_auto_form();

    
    apffw_remove_empty_elements();

    apffw_init_search_form();
    apffw_init_pagination();
    apffw_init_orderby();
    apffw_init_reset_button();
    apffw_init_beauty_scroll();
    
    apffw_draw_products_top_panel();
    apffw_shortcode_observer();

    
    apffw_init_tooltip();

    
    apffw_init_mobile_filter();



    
    if (!apffw_is_ajax) {
        apffw_redirect_init();
    }

    apffw_init_toggles();

});


function apffw_redirect_init() {

    try {
        if (jQuery('.apffw').length) {
            if (undefined !== jQuery('.apffw').val()) {
                apffw_redirect = jQuery('.apffw').eq(0).data('redirect');
                if (apffw_redirect.length > 0) {
                    apffw_shop_page = apffw_current_page_link = apffw_redirect;
                }
                return apffw_redirect;
            }
        }
    } catch (e) {
        console.log(e);
    }

}

function apffw_init_orderby() {
    jQuery('body').on('submit', 'form.woocommerce-ordering', function () {
        if (!jQuery("#is_woo_shortcode").length) {
            return false;
        }        
    });
    jQuery('body').on('change', 'form.woocommerce-ordering select.orderby', function () {
        if (!jQuery("#is_woo_shortcode").length) {
            apffw_current_values.orderby = jQuery(this).val();
            apffw_ajax_page_num = 1;
            apffw_submit_link(apffw_get_submit_link(), 0);
            return false;
        }        
    });
}

function apffw_init_reset_button() {

    jQuery('body').on('click', '.apffw_reset_search_form', function () {
        apffw_ajax_page_num = 1;
        apffw_ajax_redraw = 0;
        apffw_reset_btn_action = true;
        if (apffw_is_permalink) {
            apffw_current_values = {};	   
            apffw_submit_link(apffw_get_submit_link().split("page/")[0]);

        } else {
            var link = apffw_shop_page;
            if (apffw_current_values.hasOwnProperty('page_id')) {
                link = location.protocol + '//' + location.host + "/?page_id=" + apffw_current_values.page_id;
                apffw_current_values = {'page_id': apffw_current_values.page_id};
                apffw_get_submit_link();
            }
            
            apffw_submit_link(link);
            if (apffw_is_ajax) {
                history.pushState({}, "", link);
                if (apffw_current_values.hasOwnProperty('page_id')) {
                    apffw_current_values = {'page_id': apffw_current_values.page_id};
                } else {
                    apffw_current_values = {};
                }
            }
        }
        return false;
    });
}

function apffw_init_pagination() {

    if (apffw_is_ajax === 1) {
        jQuery('body').on('click', '.woocommerce-pagination a.page-numbers', function () {
            var l = jQuery(this).attr('href');

            if (apffw_ajax_first_done) {
                var res = l.split("paged=");
                if (typeof res[1] !== 'undefined') {
                    apffw_ajax_page_num = parseInt(res[1]);
                } else {
                    apffw_ajax_page_num = 1;
                }
                var res2 = l.split("product-page=");
                if (typeof res2[1] !== 'undefined') {
                    apffw_ajax_page_num = parseInt(res2[1]);
                }
            } else {
                var res = l.split("page/");
                if (typeof res[1] !== 'undefined') {
                    apffw_ajax_page_num = parseInt(res[1]);
                } else {
                    apffw_ajax_page_num = 1;
                }
                var res2 = l.split("product-page=");
                if (typeof res2[1] !== 'undefined') {
                    apffw_ajax_page_num = parseInt(res2[1]);
                }
            }

            


            {
                apffw_submit_link(apffw_get_submit_link(), 0);
            }

            return false;
        });
    }
}

function apffw_init_search_form() {
    apffw_init_checkboxes();
    apffw_init_mselects();
    apffw_init_radios();
    apffw_price_filter_radio_init();
    apffw_init_selects();


    
    if (apffw_ext_init_functions !== null) {
        jQuery.each(apffw_ext_init_functions, function (type, func) {
            eval(func + '()');
        });
    }

    
    jQuery('.apffw_submit_search_form').on('click', function () {

        if (apffw_ajax_redraw) {
            apffw_ajax_redraw = 0;
            apffw_is_ajax = 0;
        }
        
        apffw_submit_link(apffw_get_submit_link());
        return false;
    });



    
    jQuery('ul.apffw_childs_list').parent('li').addClass('apffw_childs_list_li');

    

    apffw_remove_class_widget();
    apffw_checkboxes_slide();
}

var apffw_submit_link_locked = false;
function apffw_submit_link(link, ajax_redraw) {


    if (apffw_submit_link_locked) {
        return;
    }
    if (typeof APFFWTurboMode != 'undefined') {
        APFFWTurboMode.apffw_submit_link(link);

        return;
    }
    if (typeof ajax_redraw == 'undefined') {
        ajax_redraw = apffw_ajax_redraw;
    }

    apffw_submit_link_locked = true;
    apffw_show_info_popup(apffw_lang_loading);

    if (apffw_is_ajax === 1 && !ajax_redraw) {

        apffw_ajax_first_done = true;
        var data = {
            action: "apffw_draw_products",
            link: link,
            page: apffw_ajax_page_num,
            shortcode: jQuery('#apffw_results_by_ajax').data('shortcode'),
            apffw_shortcode: jQuery('div.apffw').data('shortcode')
        };

        jQuery.post(apffw_ajaxurl, data, function (content) {
            content = JSON.parse(content);
            if (jQuery('.apffw_results_by_ajax_shortcode').length) {
                if (typeof content.products != "undefined") {
                    jQuery('#apffw_results_by_ajax').replaceWith(content.products);

                    var found_count = jQuery('.apffw_found_count');
                    jQuery(found_count).show();
                    if (found_count.length > 0) {
                        var count_prod = jQuery("#apffw_results_by_ajax").data('count');
                        if (typeof count_prod != "undefined") {
                            jQuery(found_count).text(count_prod);
                        }

                    }

                }
            } else {
                if (typeof content.products != "undefined") {
                    jQuery('.apffw_shortcode_output').replaceWith(content.products);
                }
            }
            if (typeof content.additional_fields != "undefined") {
                jQuery.each(content.additional_fields, function (selector, html_data) {
                    jQuery(selector).replaceWith(html_data);
                });
            }


            jQuery('div.apffw_redraw_zone').replaceWith(jQuery(content.form).find('.apffw_redraw_zone'));
            apffw_draw_products_top_panel();
            apffw_mass_reinit();
            apffw_submit_link_locked = false;
            
            jQuery.each(jQuery('#apffw_results_by_ajax'), function (index, item) {
                if (index == 0) {
                    return;
                }

                jQuery(item).removeAttr('id');
            });
            
            jQuery('.apffw_hide_mobile_filter').trigger('click');



            
            apffw_infinite();
            
            apffw_js_after_ajax_done();
            
            apffw_change_link_addtocart();

            
            apffw_init_tooltip();

            document.dispatchEvent(new CustomEvent('apffw-ajax-form-redrawing', {detail: {
                    link: link
                }}));

        });

    } else {

        if (ajax_redraw) {
            var data = {
                action: "apffw_draw_products",
                link: link,
                page: 1,
                shortcode: 'apffw_nothing', 
                apffw_shortcode: jQuery('div.apffw').eq(0).data('shortcode')
            };
            jQuery.post(apffw_ajaxurl, data, function (content) {
                content = JSON.parse(content);
                jQuery('div.apffw_redraw_zone').replaceWith(jQuery(content.form).find('.apffw_redraw_zone'));
                apffw_mass_reinit();
                apffw_submit_link_locked = false;
                apffw_init_tooltip();

                document.dispatchEvent(new CustomEvent('apffw-ajax-form-redrawing', {detail: {
                        link: link
                    }}));
            });
        } else {

            window.location = link;
            apffw_show_info_popup(apffw_lang_loading);
        }
    }
}

function apffw_remove_empty_elements() {
    jQuery.each(jQuery('.apffw_container select'), function (index, select) {
        var size = jQuery(select).find('option').length;
        if (size === 0) {
            jQuery(select).parents('.apffw_container').remove();
        }
    });
    
    jQuery.each(jQuery('ul.apffw_list'), function (index, ch) {
        var size = jQuery(ch).find('li').length;
        if (size === 0) {
            jQuery(ch).parents('.apffw_container').remove();
        }
    });
}

function apffw_get_submit_link() {
    if (apffw_is_ajax) {
        apffw_current_values.page = apffw_ajax_page_num;
    }

    if (Object.keys(apffw_current_values).length > 0) {
        jQuery.each(apffw_current_values, function (index, value) {
            if (index == sapffw_search_slug) {
                delete apffw_current_values[index];
            }
            if (index == 's') {
                delete apffw_current_values[index];
            }
            if (index == 'product') {
                delete apffw_current_values[index];
            }
            if (index == 'really_curr_tax') {
                delete apffw_current_values[index];
            }
        });
    }


    
    if (Object.keys(apffw_current_values).length === 2) {
        if (('min_price' in apffw_current_values) && ('max_price' in apffw_current_values)) {
            apffw_current_page_link = apffw_current_page_link.replace(new RegExp(/page\/(\d+)/), "");
            var l = apffw_current_page_link + '?min_price=' + apffw_current_values.min_price + '&max_price=' + apffw_current_values.max_price;
            if (apffw_is_ajax) {
                history.pushState({}, "", l);
            }
            return l;
        }
    }



    

    if (Object.keys(apffw_current_values).length === 0) {
        if (apffw_is_ajax) {
            history.pushState({}, "", apffw_current_page_link);
        }
        return apffw_current_page_link;
    }
    
    if (Object.keys(apffw_really_curr_tax).length > 0) {
        apffw_current_values['really_curr_tax'] = apffw_really_curr_tax.term_id + '-' + apffw_really_curr_tax.taxonomy;
    }
    
    var link = apffw_current_page_link + "?" + sapffw_search_slug + "=1";

    if (!apffw_is_permalink) {

        if (apffw_redirect.length > 0) {
            link = apffw_redirect + "?" + sapffw_search_slug + "=1";
            if (apffw_current_values.hasOwnProperty('page_id')) {
                delete apffw_current_values.page_id;
            }
        } else {
            link = location.protocol + '//' + location.host + "?" + sapffw_search_slug + "=1";

        }
    }

    var apffw_exclude_accept_array = ['path'];

    if (Object.keys(apffw_current_values).length > 0) {
        jQuery.each(apffw_current_values, function (index, value) {
            if (index == 'page' && apffw_is_ajax) {
                index = 'paged';
            }
            if (index == "product-page") {
                return;
            }

            
            if (typeof value !== 'undefined') {
                if ((typeof value && value.length > 0) || typeof value == 'number')
                {
                    if (jQuery.inArray(index, apffw_exclude_accept_array) == -1) {

                        link = link + "&" + index + "=" + value;
                    }
                }
            }

        });
    }

    
    
    link = link.replace(new RegExp(/page\/(\d+)/), "");
    if (apffw_is_ajax) {
        history.pushState({}, "", link);

    }

    return link;
}



function apffw_show_info_popup(text) {
    if (apffw_overlay_skin == 'default') {
        jQuery("#apffw_html_buffer").text(text);
        jQuery("#apffw_html_buffer").fadeTo(200, 0.9);
    } else {
        switch (apffw_overlay_skin) {
            case 'loading-balls':
            case 'loading-bars':
            case 'loading-bubbles':
            case 'loading-cubes':
            case 'loading-cylon':
            case 'loading-spin':
            case 'loading-spinning-bubbles':
            case 'loading-spokes':
                jQuery('body').plainOverlay('show', {progress: function () {
                        return jQuery('<div id="apffw_svg_load_container"><img style="height: 100%; width: 100%" src="' + apffw_link + 'img/loading-master/' + apffw_overlay_skin + '.svg" alt=""></div>');
                    }});
                break;
            default:
                jQuery('body').plainOverlay('show', {duration: -1});
                break;
        }
    }
}


function apffw_hide_info_popup() {
    if (apffw_overlay_skin == 'default') {
        window.setTimeout(function () {
            jQuery("#apffw_html_buffer").fadeOut(400);
        }, 200);
    } else {
        jQuery('body').plainOverlay('hide');
    }
}

function apffw_draw_products_top_panel() {

    if (apffw_is_ajax) {
        jQuery('#apffw_results_by_ajax').prev('.apffw_products_top_panel').remove();
    }

    var panel = jQuery('.apffw_products_top_panel');

    panel.html('');
    if (Object.keys(apffw_current_values).length > 0) {
        panel.show();
        panel.html('<ul></ul>');
        panel.find('ul').attr('class', 'apffw_products_top_panel_ul');
        var is_price_in = false;
        

        jQuery.each(apffw_current_values, function (index, value) {
            
            if (jQuery.inArray(index, apffw_accept_array) == -1 && jQuery.inArray(index.replace("rev_", ""), apffw_accept_array) == -1) {
                return;
            }

            

            if ((index == 'min_price' || index == 'max_price') && is_price_in) {
                return;
            }

            if ((index == 'min_price' || index == 'max_price') && !is_price_in) {
                is_price_in = true;
                index = 'price';
                value = apffw_lang_pricerange;
            }
            
            value = value.toString().trim();
            if (value.search(',')) {
                value = value.split(',');
            }
            
            jQuery.each(value, function (i, v) {
                if (index == 'page') {
                    return;
                }

                if (index == 'post_type') {
                    return;
                }

                var txt = v;
                if (index == 'orderby') {
                    if (apffw_lang[v] !== undefined) {
                        txt = apffw_lang.orderby + ': ' + apffw_lang[v];
                    } else {
                        txt = apffw_lang.orderby + ': ' + v;
                    }
                } else if (index == 'perpage') {
                    txt = apffw_lang.perpage;
                } else if (index == 'price') {
                    txt = apffw_lang.pricerange;
                } else {

                    var is_in_custom = false;
                    if (Object.keys(apffw_lang_custom).length > 0) {
                        jQuery.each(apffw_lang_custom, function (i, tt) {
                            if (i == index) {
                                is_in_custom = true;
                                txt = tt;
                                if (index == 'apffw_sku') {
                                    txt += " " + v;
                                }
                            }
                        });
                    }

                    if (!is_in_custom) {

                        try {
                            txt = jQuery("input[data-anchor='apffw_n_" + index + '_' + v + "']").val();
                        } catch (e) {
                            console.log(e);
                        }

                        if (typeof txt === 'undefined')
                        {
                            txt = v;
                        }
                    }


                }
                if (typeof apffw_filter_titles[index] != 'undefined') {

                    var cont_item = panel.find('ul.apffw_products_top_panel_ul li ul[data-container=' + index + ']');

                    if (cont_item.length) {
                        cont_item.append(
                                jQuery('<li>').append(
                                jQuery('<a>').attr('href', "").attr('data-tax', index).attr('data-slug', v).append(
                                jQuery('<span>').attr('class', 'apffw_remove_ppi').append(txt)
                                )));
                    } else {
                        panel.find('ul.apffw_products_top_panel_ul').append(
                                jQuery('<li>').append(
                                jQuery('<ul>').attr('data-container', index).append(
                                jQuery('<li>').text(apffw_filter_titles[index] + ":")).append(
                                jQuery('<li>').append(
                                jQuery('<a>').attr('href', "").attr('data-tax', index).attr('data-slug', v).append(
                                jQuery('<span>').attr('class', 'apffw_remove_ppi').append(txt)
                                )))));
                    }
                } else {
                    panel.find('ul.apffw_products_top_panel_ul').append(
                            jQuery('<li>').append(
                            jQuery('<a>').attr('href', "").attr('data-tax', index).attr('data-slug', v).append(
                            jQuery('<span>').attr('class', 'apffw_remove_ppi').append(txt)
                            )));
                }

            });


        });
    }


    if (jQuery(panel).find('li').length == 0 || !jQuery('.apffw_products_top_panel').length) {
        panel.hide();
    } else {
        panel.find('ul.apffw_products_top_panel_ul').prepend(
                jQuery('<li>').append(
                jQuery('<button>').attr('class', "apffw_reset_button_2").append(apffw_lang.clear_all))
                );
    }

    jQuery('.apffw_reset_button_2').on('click', function () {
        apffw_ajax_page_num = 1;
        apffw_ajax_redraw = 0;
        apffw_reset_btn_action = true;

        if (apffw_is_permalink) {
            apffw_current_values = {};
            apffw_submit_link(apffw_get_submit_link().split("page/")[0]);
        } else {
            var link = apffw_shop_page;
            if (apffw_current_values.hasOwnProperty('page_id')) {
                link = location.protocol + '//' + location.host + "/?page_id=" + apffw_current_values.page_id;
                apffw_current_values = {'page_id': apffw_current_values.page_id};
                apffw_get_submit_link();
            }
            
            apffw_submit_link(link);
            if (apffw_is_ajax) {
                history.pushState({}, "", link);
                if (apffw_current_values.hasOwnProperty('page_id')) {
                    apffw_current_values = {'page_id': apffw_current_values.page_id};
                } else {
                    apffw_current_values = {};
                }
            }
        }
        return false;
    });
    
    jQuery('.apffw_remove_ppi').parent().on('click', function () {
        event.preventDefault();
        var tax = jQuery(this).data('tax');
        var name = jQuery(this).data('slug');

        

        if (tax != 'price') {

            var values = apffw_current_values[tax];
            values = values.split(',');
            var tmp = [];
            jQuery.each(values, function (index, value) {
                if (value != name) {
                    tmp.push(value);
                }
            });
            values = tmp;

            if (values.length) {
                apffw_current_values[tax] = values.join(',');
            } else {
                delete apffw_current_values[tax];
            }
        } else {
            delete apffw_current_values['min_price'];
            delete apffw_current_values['max_price'];
        }
        apffw_ajax_page_num = 1;
        apffw_reset_btn_action = true;
        {
            apffw_submit_link(apffw_get_submit_link());
        }
        jQuery('.apffw_products_top_panel').find("[data-tax='" + tax + "'][href='" + name + "']").hide(333);
        return false;

    });

}

function apffw_shortcode_observer() {

    var redirect = true;
    if (jQuery('.apffw_shortcode_output').length || (jQuery('.woocommerce .products').length && !jQuery('.single-product').length)) {
        redirect = false;
    }
    if (jQuery('.woocommerce .woocommerce-info').length) {
        redirect = false;
    }
    if (typeof apffw_not_redirect !== 'undefined' && apffw_not_redirect == 1) {
        redirect = false;
    }

    if (jQuery('.apffw-data-table').length) {
        redirect = false;
    }

    if (!redirect) {
        apffw_current_page_link = location.protocol + '//' + location.host + location.pathname;
    }

    if (jQuery('#apffw_results_by_ajax').length) {
        apffw_is_ajax = 1;
    }
}



function apffw_init_beauty_scroll() {
    if (apffw_use_beauty_scroll) {
        try {
            var anchor = ".apffw_section_scrolled, .apffw_sid_auto_shortcode .apffw_container_radio .apffw_block_html_items, .apffw_sid_auto_shortcode .apffw_container_checkbox .apffw_block_html_items, .apffw_sid_auto_shortcode .apffw_container_label .apffw_block_html_items";
            jQuery("" + anchor).addClass('apffw_use_beauty_scroll');
        } catch (e) {
            console.log(e);
        }
    }
}

function apffw_remove_class_widget() {
    jQuery('.apffw_container_inner').find('.widget').removeClass('widget');
}

function apffw_init_show_auto_form() {
    jQuery('.apffw_show_auto_form').off('click');

    if (jQuery('.apffw_show_auto_form.apffw_btn').length) {
        jQuery('.apffw_btn_default').remove();
    }

    jQuery('.apffw_show_auto_form').on('click', function () {
        var _this = this;
        jQuery(_this).addClass('apffw_hide_auto_form').removeClass('apffw_show_auto_form');
        jQuery(".apffw_auto_show").show().animate(
                {
                    height: (jQuery(".apffw_auto_show_indent").height() + 20) + "px",
                    opacity: 1
                }, 377, function () {
            apffw_init_hide_auto_form();
            jQuery('.apffw_auto_show').removeClass('apffw_overflow_hidden');
            jQuery('.apffw_auto_show_indent').removeClass('apffw_overflow_hidden');
            jQuery(".apffw_auto_show").height('auto');
        });


        return false;
    });


}

function apffw_init_hide_auto_form() {
    jQuery('.apffw_hide_auto_form').off('click');
    jQuery('.apffw_hide_auto_form').on('click', function () {
        var _this = this;
        jQuery(_this).addClass('apffw_show_auto_form').removeClass('apffw_hide_auto_form');
        jQuery(".apffw_auto_show").show().animate(
                {
                    height: "1px",
                    opacity: 0
                }, 377, function () {

            jQuery('.apffw_auto_show').addClass('apffw_overflow_hidden');
            jQuery('.apffw_auto_show_indent').addClass('apffw_overflow_hidden');
            apffw_init_show_auto_form();
        });

        return false;
    });


}

function apffw_checkboxes_slide() {
    if (apffw_checkboxes_slide_flag == true) {
        var childs = jQuery('ul.apffw_childs_list');
        if (childs.length) {
            jQuery.each(childs, function (index, ul) {

                if (jQuery(ul).parents('.apffw_no_close_childs').length) {
                    return;
                }


                var span_class = 'apffw_is_closed';
                if (apffw_supports_html5_storage()) {
                    var preulstate = localStorage.getItem(jQuery(ul).closest('li').attr("class"));
                    if (preulstate && preulstate == 'apffw_is_opened') {
                        var span_class = 'apffw_is_opened';
                        jQuery(ul).show();
                    }
                    jQuery(ul).parent('li').children('label').after('<a href="javascript:void(0);" class="apffw_childs_list_opener" ><span class="' + span_class + '"></span></a>');
                       
                } else {
                    if (jQuery(ul).find('input[type=checkbox],input[type=radio]').is(':checked')) {
                        jQuery(ul).show();
                        span_class = 'apffw_is_opened';
                    }
                    jQuery(ul).parent('li').children('label').after('<a href="javascript:void(0);" class="apffw_childs_list_opener" ><span class="' + span_class + '"></span></a>');

                }

            });
	    
	    

            jQuery.each(jQuery('a.apffw_childs_list_opener span'), function (index, a) {

                jQuery(a).on('click', function () {
                    var span = jQuery(this);
                    var this_ = jQuery(this).parent(".apffw_childs_list_opener");
                    if (span.hasClass('apffw_is_closed')) {
                        jQuery(this_).parent().find('ul.apffw_childs_list').first().show(333);
                        span.removeClass('apffw_is_closed');
                        span.addClass('apffw_is_opened');
                    } else {
                        jQuery(this_).parent().find('ul.apffw_childs_list').first().hide(333);
                        span.removeClass('apffw_is_opened');
                        span.addClass('apffw_is_closed');
                    }

                    if (apffw_supports_html5_storage()) {
                        var ullabel = jQuery(this_).closest('li').attr("class");
                        var ullstate = jQuery(this_).children("span").attr("class");
                        localStorage.setItem(ullabel, ullstate);
                    }
                    return false;
                });
            });
        }
    }
}

function apffw_init_ion_sliders() {

    jQuery.each(jQuery('.apffw_range_slider'), function (index, input) {
        try {


            jQuery(input).ionRangeSlider({
                min: jQuery(input).data('min'),
                max: jQuery(input).data('max'),
                from: jQuery(input).data('min-now'),
                to: jQuery(input).data('max-now'),
                type: 'double',
                prefix: jQuery(input).data('slider-prefix'),
                postfix: jQuery(input).data('slider-postfix'),
                prettify: true,
                hideMinMax: false,
                hideFromTo: false,
                grid: true,
                step: jQuery(input).data('step'),
                onFinish: function (ui) {
                    var tax = jQuery(input).data('taxes');
                    apffw_current_values.min_price = (parseFloat(ui.from, 10) / tax);
                    apffw_current_values.max_price = (parseFloat(ui.to, 10) / tax);
                   
                    apffw_ajax_page_num = 1;
                    if (apffw_autosubmit || jQuery(input).within('.apffw').length == 0) {
                        apffw_submit_link(apffw_get_submit_link());
                    }
                    return false;
                },
                onChange: function (data) {
                    if (jQuery('.apffw_price_filter_txt')) {
                        var tax = jQuery(input).data('taxes');
                        jQuery('.apffw_price_filter_txt_from').val(parseInt(data.from, 10) / tax);
                        jQuery('.apffw_price_filter_txt_to').val(parseInt(data.to, 10) / tax);
                        if (typeof apffw_current_currency !== 'undefined') {
                            jQuery('.apffw_price_filter_txt_from').val(Math.ceil(jQuery('.apffw_price_filter_txt_from').val() / parseFloat(apffw_current_currency.rate)));
                            jQuery('.apffw_price_filter_txt_to').val(Math.ceil(jQuery('.apffw_price_filter_txt_to').val() / parseFloat(apffw_current_currency.rate)));
                        }
                    }
                },
            });
        } catch (e) {

        }
    });
}

function apffw_init_native_woo_price_filter() {
    jQuery('.widget_price_filter form').off('submit');
    jQuery('.widget_price_filter form').on('submit', function () {

        var min_price = jQuery(this).find('.price_slider_amount #min_price').val();
        var max_price = jQuery(this).find('.price_slider_amount #max_price').val();
        apffw_current_values.min_price = min_price;
        apffw_current_values.max_price = max_price;
        apffw_ajax_page_num = 1;
        if (apffw_autosubmit) {
            apffw_submit_link(apffw_get_submit_link());
        }
        return false;
    });

}


function apffw_reinit_native_woo_price_filter() {

    
    if (typeof woocommerce_price_slider_params === 'undefined') {

        return false;
    }

    
    jQuery('input#min_price, input#max_price').hide();
    jQuery('.price_slider, .price_label').show();

    
    var min_price = jQuery('.price_slider_amount #min_price').data('min'),
            max_price = jQuery('.price_slider_amount #max_price').data('max'),
            current_min_price = parseInt(min_price, 10),
            current_max_price = parseInt(max_price, 10);

    if (apffw_current_values.hasOwnProperty('min_price')) {
        current_min_price = parseInt(apffw_current_values.min_price, 10);
        current_max_price = parseInt(apffw_current_values.max_price, 10);
    } else {
        if (woocommerce_price_slider_params.min_price) {
            current_min_price = parseInt(woocommerce_price_slider_params.min_price, 10);
        }
        if (woocommerce_price_slider_params.max_price) {
            current_max_price = parseInt(woocommerce_price_slider_params.max_price, 10);
        }
    }

    

    var currency_symbol = woocommerce_price_slider_params.currency_symbol;
    if (typeof currency_symbol == 'undefined') {
        currency_symbol = woocommerce_price_slider_params.currency_format_symbol;
    }

    jQuery(document.body).on('price_slider_create price_slider_slide', function (event, min, max) {

        if (typeof apffw_current_currency !== 'undefined') {
            var label_min = min;
            var label_max = max;
            if (typeof currency_symbol == 'undefined') {

                currency_symbol = apffw_current_currency.symbol
            }


            if (apffw_current_currency.rate !== 1) {
                label_min = Math.ceil(label_min * parseFloat(apffw_current_currency.rate));
                label_max = Math.ceil(label_max * parseFloat(apffw_current_currency.rate));
            }

            
            label_min = apffw_front_number_format(label_min, 2, '.', ',');
            label_max = apffw_front_number_format(label_max, 2, '.', ',');
            if (jQuery.inArray(apffw_current_currency.name, apffw_array_no_cents) || apffw_current_currency.hide_cents == 1) {
                label_min = label_min.replace('.00', '');
                label_max = label_max.replace('.00', '');
            }
            


            if (apffw_current_currency.position === 'left') {

                jQuery('.price_slider_amount span.from').html(currency_symbol + label_min);
                jQuery('.price_slider_amount span.to').html(currency_symbol + label_max);

            } else if (apffw_current_currency.position === 'left_space') {

                jQuery('.price_slider_amount span.from').html(currency_symbol + " " + label_min);
                jQuery('.price_slider_amount span.to').html(currency_symbol + " " + label_max);

            } else if (apffw_current_currency.position === 'right') {

                jQuery('.price_slider_amount span.from').html(label_min + currency_symbol);
                jQuery('.price_slider_amount span.to').html(label_max + currency_symbol);

            } else if (apffw_current_currency.position === 'right_space') {

                jQuery('.price_slider_amount span.from').html(label_min + " " + currency_symbol);
                jQuery('.price_slider_amount span.to').html(label_max + " " + currency_symbol);

            }

        } else {

            if (woocommerce_price_slider_params.currency_pos === 'left') {

                jQuery('.price_slider_amount span.from').html(currency_symbol + min);
                jQuery('.price_slider_amount span.to').html(currency_symbol + max);

            } else if (woocommerce_price_slider_params.currency_pos === 'left_space') {

                jQuery('.price_slider_amount span.from').html(currency_symbol + ' ' + min);
                jQuery('.price_slider_amount span.to').html(currency_symbol + ' ' + max);

            } else if (woocommerce_price_slider_params.currency_pos === 'right') {

                jQuery('.price_slider_amount span.from').html(min + currency_symbol);
                jQuery('.price_slider_amount span.to').html(max + currency_symbol);

            } else if (woocommerce_price_slider_params.currency_pos === 'right_space') {

                jQuery('.price_slider_amount span.from').html(min + ' ' + currency_symbol);
                jQuery('.price_slider_amount span.to').html(max + ' ' + currency_symbol);

            }
        }

        jQuery(document.body).trigger('price_slider_updated', [min, max]);
    });

    jQuery('.price_slider').slider({
        range: true,
        animate: true,
        min: min_price,
        max: max_price,
        values: [current_min_price, current_max_price],
        create: function () {

            jQuery('.price_slider_amount #min_price').val(current_min_price);
            jQuery('.price_slider_amount #max_price').val(current_max_price);

            jQuery(document.body).trigger('price_slider_create', [current_min_price, current_max_price]);
        },
        slide: function (event, ui) {

            jQuery('input#min_price').val(ui.values[0]);
            jQuery('input#max_price').val(ui.values[1]);

            jQuery(document.body).trigger('price_slider_slide', [ui.values[0], ui.values[1]]);
        },
        change: function (event, ui) {
            jQuery(document.body).trigger('price_slider_change', [ui.values[0], ui.values[1]]);
        }
    });


    
    apffw_init_native_woo_price_filter();
}

function apffw_mass_reinit() {
    apffw_remove_empty_elements();
    apffw_open_hidden_li();
    apffw_init_search_form();
    apffw_hide_info_popup();
    apffw_init_beauty_scroll();
    apffw_init_ion_sliders();
    apffw_reinit_native_woo_price_filter();
    apffw_recount_text_price_filter();
    apffw_draw_products_top_panel();
}

function apffw_recount_text_price_filter() {
    if (typeof apffw_current_currency !== 'undefined') {
        jQuery.each(jQuery('.apffw_price_filter_txt_from, .apffw_price_filter_txt_to'), function (i, item) {
            jQuery(this).val(Math.ceil(jQuery(this).data('value')));
        });
    }
}

function apffw_init_toggles() {

    jQuery('body').on('click', '.apffw_front_toggle', function () {
        if (jQuery(this).data('condition') == 'opened') {
            jQuery(this).removeClass('apffw_front_toggle_opened');
            jQuery(this).addClass('apffw_front_toggle_closed');
            jQuery(this).data('condition', 'closed');

            if (apffw_toggle_type == 'text') {
                jQuery(this).text(apffw_toggle_closed_text);
            } else {
                jQuery(this).find('img').prop('src', apffw_toggle_closed_image);
            }
        } else {

            jQuery(this).addClass('apffw_front_toggle_opened');
            jQuery(this).removeClass('apffw_front_toggle_closed');
            jQuery(this).data('condition', 'opened');
            if (apffw_toggle_type == 'text') {
                jQuery(this).text(apffw_toggle_opened_text);
            } else {
                jQuery(this).find('img').prop('src', apffw_toggle_opened_image);
            }
        }

        jQuery(this).parents('.apffw_container_inner').find('.apffw_block_html_items').slideToggle(500);

        
        var is_chosen_here = jQuery(this).parents('.apffw_container_inner').find('.chosen-container');
        if (is_chosen_here.length && jQuery(this).hasClass('apffw_front_toggle_opened')) {
            jQuery(this).parents('.apffw_container_inner').find('select').chosen('destroy').trigger("liszt:updated");
            jQuery(this).parents('.apffw_container_inner').find('select').chosen(/*{disable_search_threshold: 10}*/);
        }

        return false;
    });
}

function apffw_open_hidden_li() {
    if (jQuery('.apffw_open_hidden_li_btn').length > 0) {
        jQuery.each(jQuery('.apffw_open_hidden_li_btn'), function (i, b) {
            if (jQuery(b).parents('ul').find('li.apffw_hidden_term input[type=checkbox],li.apffw_hidden_term input[type=radio]').is(':checked')) {
                jQuery(b).trigger('click');
            }
        });
    }
}

function $_apffw_GET(q, s) {
    s = (s) ? s : window.location.search;
    var re = new RegExp('&' + q + '=([^&]*)', 'i');
    return (s = s.replace(/^\?/, '&').match(re)) ? s = s[1] : s = '';
}

function apffw_parse_url(url) {
    var pattern = RegExp("^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\\?([^#]*))?(#(.*))?");
    var matches = url.match(pattern);
    return {
        scheme: matches[2],
        authority: matches[4],
        path: matches[5],
        query: matches[7],
        fragment: matches[9]
    };
}


function apffw_price_filter_radio_init() {
    if (icheck_skin != 'none') {
        jQuery('.apffw_price_filter_radio').iCheck('destroy');

        jQuery('.apffw_price_filter_radio').iCheck({
            radioClass: 'iradio_' + icheck_skin.skin + '-' + icheck_skin.color,

        });

        jQuery('.apffw_price_filter_radio').siblings('div').removeClass('checked');

        jQuery('.apffw_price_filter_radio').off('ifChecked');
        jQuery('.apffw_price_filter_radio').on('ifChecked', function (event) {
            jQuery(this).attr("checked", true);
            jQuery('.apffw_radio_price_reset').removeClass('apffw_radio_term_reset_visible');
            jQuery(this).parents('.apffw_list').find('.apffw_radio_price_reset').removeClass('apffw_radio_term_reset_visible');
            jQuery(this).parents('.apffw_list').find('.apffw_radio_price_reset').hide();
            jQuery(this).parents('li').eq(0).find('.apffw_radio_price_reset').eq(0).addClass('apffw_radio_term_reset_visible');
            var val = jQuery(this).val();
            if (parseInt(val, 10) == -1) {
                delete apffw_current_values.min_price;
                delete apffw_current_values.max_price;
                jQuery(this).removeAttr('checked');
                jQuery(this).siblings('.apffw_radio_price_reset').removeClass('apffw_radio_term_reset_visible');
            } else {
                var val = val.split("-");
                apffw_current_values.min_price = val[0];
                apffw_current_values.max_price = val[1];
                jQuery(this).siblings('.apffw_radio_price_reset').addClass('apffw_radio_term_reset_visible');
                jQuery(this).attr("checked", true);
            }
            if (apffw_autosubmit || jQuery(this).within('.apffw').length == 0) {
                apffw_submit_link(apffw_get_submit_link());
            }
        });

    } else {
        jQuery('body').on('change', '.apffw_price_filter_radio', function () {
            var val = jQuery(this).val();
            jQuery('.apffw_radio_price_reset').removeClass('apffw_radio_term_reset_visible');
            if (parseInt(val, 10) == -1) {
                delete apffw_current_values.min_price;
                delete apffw_current_values.max_price;
                jQuery(this).removeAttr('checked');
                jQuery(this).siblings('.apffw_radio_price_reset').removeClass('apffw_radio_term_reset_visible');
            } else {
                var val = val.split("-");
                apffw_current_values.min_price = val[0];
                apffw_current_values.max_price = val[1];
                jQuery(this).siblings('.apffw_radio_price_reset').addClass('apffw_radio_term_reset_visible');
                jQuery(this).attr("checked", true);
            }
            if (apffw_autosubmit || jQuery(this).within('.apffw').length == 0) {
                apffw_submit_link(apffw_get_submit_link());
            }
        });
    }
    
    jQuery('.apffw_radio_price_reset').on('click', function () {
        delete apffw_current_values.min_price;
        delete apffw_current_values.max_price;
        jQuery(this).siblings('div').removeClass('checked');
        jQuery(this).parents('.apffw_list').find('input[type=radio]').removeAttr('checked');

        jQuery(this).removeClass('apffw_radio_term_reset_visible');
        if (apffw_autosubmit) {
            apffw_submit_link(apffw_get_submit_link());
        }
        return false;
    });
}



function apffw_serialize(serializedString) {
    var str = decodeURI(serializedString);
    var pairs = str.split('&');
    var obj = {}, p, idx, val;
    for (var i = 0, n = pairs.length; i < n; i++) {
        p = pairs[i].split('=');
        idx = p[0];

        if (idx.indexOf("[]") == (idx.length - 2)) {
            var ind = idx.substring(0, idx.length - 2)
            if (obj[ind] === undefined) {
                obj[ind] = [];
            }
            obj[ind].push(p[1]);
        } else {
            obj[idx] = p[1];
        }
    }
    return obj;
}


function apffw_infinite() {

    if (typeof yith_infs === 'undefined') {
        return;
    }


    
    var infinite_scroll1 = {
        'nextSelector': '.woocommerce-pagination li .next',
        'navSelector': yith_infs.navSelector,
        'itemSelector': yith_infs.itemSelector,
        'contentSelector': yith_infs.contentSelector,
        'loader': '<img src="' + yith_infs.loader + '">',
        'is_shop': yith_infs.shop
    };
    var curr_l = window.location.href;
    var curr_link = curr_l.split('?');
    var get = "";
    if (curr_link[1] != undefined) {
        var temp = apffw_serialize(curr_link[1]);
        delete temp['paged'];
        get = decodeURIComponent(jQuery.param(temp))
    }

    var page_link = jQuery('.woocommerce-pagination li .next').attr("href");

    if (page_link == undefined) {
        page_link = curr_link + "page/1/"
    }

    var ajax_link = page_link.split('?');
    var page = "";
    if (ajax_link[1] != undefined) {
        var temp1 = apffw_serialize(ajax_link[1]);
        if (temp1['paged'] != undefined) {
            page = "page/" + temp1['paged'] + "/";
        }
    }

    page_link = curr_link[0] + page + '?' + get;

    jQuery('.woocommerce-pagination li .next').attr('href', page_link);

    jQuery(window).off("yith_infs_start"), jQuery(yith_infs.contentSelector).yit_infinitescroll(infinite_scroll1)
}

function apffw_change_link_addtocart() {
    if (!apffw_is_ajax) {
        return;
    }
    jQuery(".add_to_cart_button").each(function (i, elem) {
        var link = jQuery(elem).attr('href');
        if (link) {
            var link_items = link.split("?");
            var site_link_items = window.location.href.split("?");
            if (link_items[1] != undefined) {
                link = site_link_items[0] + "?" + link_items[1];
                jQuery(elem).attr('href', link);
            }
        }
    });

}

function apffw_front_number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '')
            .replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + (Math.round(n * k) / k)
                        .toFixed(prec);
            };

    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
            .split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
            .length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1)
                .join('0');
    }
    return s.join(dec);
}


function apffw_supports_html5_storage() {
    try {
        return 'localStorage' in window && window['localStorage'] !== null;
    } catch (e) {
        return false;
    }
}

function apffw_init_tooltip() {
    var tooltips = jQuery(".apffw_tooltip_header");

    if (tooltips.length) {

        jQuery(tooltips).tooltipster({
            theme: 'tooltipster-noir',
            side: 'right',
	    trigger: 'click'
        });
    }

}

function apffw_init_mobile_filter() {
    var show_btn = jQuery('.apffw_show_mobile_filter');
    var show_btn_container = jQuery('.apffw_show_mobile_filter_container');
    var def_container = jQuery(apffw_m_b_container);
    if (!show_btn_container.length) {
        show_btn_container = def_container;
    }
    if (show_btn && show_btn_container) {
        jQuery(show_btn_container).append(show_btn);
    }


    jQuery('.apffw_show_mobile_filter').on('click', function (e) {
        var sid = jQuery(this).data('sid');
        jQuery('.apffw.apffw_sid_' + sid).toggleClass('apffw_show_filter_for_mobile');
	setTimeout(function(){
	try {
	    jQuery('.apffw.apffw_sid_' + sid).find("select.apffw_mselect").chosen('destroy');
	    jQuery('.apffw.apffw_sid_' + sid).find("select.apffw_select").chosen('destroy');	
	    jQuery('.apffw.apffw_sid_' + sid).find("select.apffw_mselect").chosen();
	    jQuery('.apffw.apffw_sid_' + sid).find("select.apffw_select").chosen();
	} catch (e) {

	}	
	}, 300);

    });
    jQuery('.apffw_hide_mobile_filter').on('click', function (e) {
        jQuery(this).parents('.apffw').toggleClass('apffw_show_filter_for_mobile');
    });
    
    
}