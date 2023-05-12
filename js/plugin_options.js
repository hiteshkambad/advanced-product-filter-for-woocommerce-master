"use strict";

(function ($, window) {

    'use strict';

    $.fn.apffwTabs = function (options) {

        if (!this.length)
            return;

        return this.each(function () {

            var $this = $(this);

            ({
                init: function () {
                    this.tabsNav = $this.children('nav');
                    this.items = $this.children('.content-wrap').children('section');
                    this._show();
                    this._initEvents();
                },
                _initEvents: function () {
                    var self = this;
                    this.tabsNav.on('click', 'a', function (e) {
                        e.preventDefault();
                        self._show($(this));
                    });
                },
                _show: function (element) {

                    if (element == undefined) {
                        this.firsTab = this.tabsNav.find('li').first();
                        this.firstSection = this.items.first();

                        if (!this.firsTab.hasClass('tab-current')) {
                            this.firsTab.addClass('tab-current');
                        }

                        if (!this.firstSection.hasClass('content-current')) {
                            this.firstSection.addClass('content-current');
                        }
                    }

                    var $this = $(element),
                            $to = $($this.attr('href'));

                    if ($to.length) {
                        $this.parent('li').siblings().removeClass().end().addClass('tab-current');
                        $to.siblings().removeClass().end().addClass('content-current');
                    }

                }

            }).init();

        });
    };

})(jQuery, window);

(function ($) {

    $.apffw_popup_prepare = function (el, options) {
        this.el = el;
        this.options = $.extend({}, $.apffw_popup_prepare.DEFAULTS, options);
        this.init();
    };

    $.apffw_popup_prepare.DEFAULTS = {};
    $.apffw_popup_prepare.openInstance = [];

    $.apffw_popup_prepare.prototype = {
        init: function () {

            $.apffw_popup_prepare.openInstance.unshift(this);

            var base = this;
            base.scope = false;
            base.body = $('body');
            base.wrap = $('#wpwrap');
            base.modal = $('<div class="apffw-modal apffw-style"></div>');
            base.overlay = $('<div class="apffw-modal-backdrop"></div>');
            base.container = $('.apffw-tabs');
            base.instance = $.apffw_popup_prepare.openInstance.length;
            base.namespace = '.popup_modal_' + base.instance;
            base.eventtype = 'click';
            base.loadPopup();
        },
        loadPopup: function () {
            this.container.on(this.eventtype, this.el, (e) => {
                if (!this.scope) {
                    this.body.addClass('apffw-noscroll');
                    this.openPopup(e);
                }
                this.scope = true;
            });
        },
        openPopup: function (e) {
            e.preventDefault();

            
            if (e.target.classList.contains('icon-cog-outline')) {
                var el = $(e.target).parent();
            } else {
                var el = $(e.target);
            }

            var base = this,
                    data = el.data();

            if (el.hasClass('js_apffw_options')) {
               
                var key = data['key'],
                        name = data['name'] + ' [' + data['key'] + ']',
                        type = false,
                        info = $("#apffw-modal-content-" + key),
                        content = info.html();
            } else {
               
                var type = el.parent().find('.apffw_select_tax_type').val();
                var key = data['taxonomy'];
                var name = data['taxonomyName'] + ' [' + key + ']';
                var info = $("#apffw-modal-content");
                info.find('.apffw_option_container').hide();
                info.find('.apffw_option_all').show();
                info.find('.apffw_option_' + type).show();
                var content = info.html();
            }

            base.create_html(key, name, content, info, type);
            base.add_behavior(key, name, content, info, type);
        },
        create_html: function (key, name, content, info, type) {

            var base = this,
                    title = name ? '<h3 class="apffw-modal-title"> ' + name + '</h3>' : '',
                    loading = ' preloading ',
                    output = '<div class="apffw-modal-inner">';
            output += '<div class="apffw-modal-inner-header">' + title + '<a href="javascript:void(0)" class="apffw-modal-close"></a></div>';
            output += '<div class="apffw-modal-inner-content ' + loading + '">' + content + '</div>';
            output += '<div class="apffw-modal-inner-footer">';
            output += '<a href="javascript:void(0)" class="apffw-modal-save button button-primary button-large">Apply</a>';
            output += '</div>';
            output += '</div>';

            base.wrap.append(base.modal).append(base.overlay);
            base.modal.html(output);
            base.modal.find('.apffw-modal-inner-content').removeClass('preloading');

            var multiplier = base.instance - 1,
                    old = parseInt(base.modal.css('zIndex'), 10);
            base.modal.css({margin: (30 * multiplier), zIndex: (old + multiplier + 1)});
            base.overlay.css({zIndex: (old + multiplier)});

            base.on_load_callback(key, name, content, info, type);
        },
        closeModal: function () {
            var base = this;

            $.apffw_popup_prepare.openInstance.shift();

            base.modal.remove();
            base.overlay.remove();

            base.body.removeClass('apffw-noscroll');
            base.scope = false;
        },
        add_behavior: function (key, name, content, info, type) {
            var base = this;

            base.modal.on(base.eventtype + base.namespace, '.apffw-modal-save', function (e) {
                e.preventDefault();
                base.on_close_callback(key, name, content, info, type);
                base.closeModal();
            });
            $(document).keydown(function (e) {
                
                if (e.keyCode == 27) {
                    base.closeModal();
                }
            });

            base.modal.on(base.eventtype + base.namespace, '.apffw-modal-close', function (e) {
                e.preventDefault();
                base.closeModal();
            });

            base.overlay.on(base.eventtype + base.namespace, function (e) {
                e.preventDefault();
                base.closeModal();
            });

        },
        on_load_callback: function (key, name, content, info, type) {

            if (type) {

                info.find('.apffw_option_container').hide();
                info.find('.apffw_option_all').show();
                info.find('.apffw_option_' + type).show();

                $.each($('.apffw_popup_option', this.modal), function () {
                    var option = $(this).data('option'),
                            val = $('input[name="apffw_settings[' + option + '][' + key + ']"]').val();
                    $(this).val(val);
                });

            } else {

                $.each($('.apffw_popup_option', this.modal), function () {
                    var option = $(this).data('option'),
                            val = $('input[name="apffw_settings[' + key + '][' + option + ']"]').val();
                    $(this).val(val);
                });

            }

        },
        on_close_callback: function (key, name, content, info, type) {

            if (type) {

                $.each($('.apffw_popup_option', this.modal), function () {
                    var option = $(this).data('option'), val = $(this).val();
                    $('input[name="apffw_settings[' + option + '][' + key + ']"]').val(val);
                });

            } else {

                $.each($('.apffw_popup_option', this.modal), function () {
                    var option = $(this).data('option'), val = $(this).val();
                    $('input[name="apffw_settings[' + key + '][' + option + ']"]').val(val);
                });

            }

        }
    };

})(jQuery);

var apffw_sort_order = [];

(function ($) {


    $.apffw_mod = $.apffw_mod || {};

    $.apffw_mod.popup_prepare = function () {
        new $.apffw_popup_prepare('.js_apffw_options');
        new $.apffw_popup_prepare('.js_apffw_add_options');
    };

    $(function () {

        $('.apffw-tabs').apffwTabs();

        $.apffw_mod.popup_prepare();

        try {
            $('.apffw-color-picker').wpColorPicker();
        } catch (e) {
            console.log(e);
        }

        $("#apffw_options").sortable({
            update: function (event, ui) {
                apffw_sort_order = [];
                $.each($('#apffw_options').children('li'), function (index, value) {
                    var key = $(this).data('key');
                    apffw_sort_order.push(key);
                });
                $('input[name="apffw_settings[items_order]"]').val(apffw_sort_order.toString());
            },
            opacity: 0.8,
            cursor: "crosshair",
            handle: '.apffw_drag_and_drope',
            placeholder: 'apffw-options-highlight'
        });


        
        $('#mainform').on('submit', function () {
            $('input[name=save]').hide();
            apffw_show_info_popup(apffw_lang_saving);
            var data = {
                action: "apffw_save_options",
                formdata: $(this).serialize()
            };
            $.post(ajaxurl, data, function () {
                window.location = apffw_save_link;
            });

            return false;
        });


        $('.apffw_reset_order').on('click', function () {
            if (prompt('To reset order of items write word "reset". The page will be reloaded!') == 'reset') {
                $('input[name="apffw_settings[items_order]"]').val('');
                $('.woocommerce-save-button').trigger('click');
            }
        });


        $('.js_cache_count_data_clear').on('click', function () {
            $(this).next('span').html('clearing ...');
            var _this = this;
            var data = {
                action: "apffw_cache_count_data_clear"
            };
            $.post(ajaxurl, data, function () {
                $(_this).next('span').html('cleared!');
            });

            return false;
        });


        $('.js_cache_terms_clear').on('click', function () {
            $(this).next('span').html('clearing ...');
            var _this = this;
            var data = {
                action: "apffw_cache_terms_clear"
            };
            $.post(ajaxurl, data, function () {
                $(_this).next('span').html('cleared!');
            });

            return false;
        });

        $('.js_price_transient_clear').on('click', function () {
            $(this).next('span').html('clearing ...');
            var _this = this;
            var data = {
                action: "apffw_price_transient_clear"
            };
            $.post(ajaxurl, data, function () {
                $(_this).next('span').html('cleared!');
            });

            return false;
        });


        
        $('#apffw_manipulate_with_ext').change(function () {
            var val = parseInt($(this).val(), 10);
            switch (val) {
                case 1:
                    $('ul.apffw_extensions li').hide();
                    $('ul.apffw_extensions li.is_enabled').show();
                    break;
                case 2:
                    $('ul.apffw_extensions li').hide();
                    $('ul.apffw_extensions li.is_disabled').show();
                    break;
                default:
                    $('ul.apffw_extensions li').show();
                    break;
            }
        });

        

        jQuery('body').on('click', '.apffw_select_image', function ()
        {
            var input_object = jQuery(this).prev('input[type=text]');

            var image = wp.media({
                title: 'Media for APFFW',
                multiple: false,
                library: {
                    type: ['image']
                }
            }).open().on('select', function (e) {
                let uploaded_image = image.state().get('selection').first();
                uploaded_image = uploaded_image.toJSON();

                if (typeof uploaded_image.sizes.thumbnail !== 'undefined') {
                    jQuery(input_object).val(uploaded_image.sizes.thumbnail.url);
                } else {
                    jQuery(input_object).val(uploaded_image.url);
                }

                jQuery(input_object).trigger('change');
                return false;

            });


            return false;
        });

        

        $('body').on('click', '.apffw_ext_remove', function () {

            if (confirm('Sure?')) {
                apffw_show_info_popup('Extension removing ...');
                var _this = this;
                var data = {
                    action: "apffw_remove_ext",
                    idx: $(this).data('idx'),
                    rm_ext_nonce: $('#rm-ext-nonce').val(),
                };
                $.post(ajaxurl, data, function (e) {
                    apffw_show_info_popup('Extension is removed!');
                    $(_this).parents('.apffw_ext_li').remove();
                    apffw_hide_info_popup();
                });
            }

            return false;
        });

        

        $('#toggle_type').change(function () {
            if ($(this).val() == 'text') {
                $('.toggle_type_text').show(200);
                $('.toggle_type_image').hide(200);
            } else {
                $('.toggle_type_image').show(200);
                $('.toggle_type_text').hide(200);
            }
        });

        
        
        $('#apffw_hide_dynamic_empty_pos').change(function () {
            if ($(this).val() == 1) {
                $('#apffw_show_count').val(1);
                $('#apffw_show_count_dynamic').val(1);
            }
        });

        $('#apffw_show_count_dynamic').change(function () {
            if ($(this).val() == 1) {
                $('#apffw_show_count').val(1);
            } else {
                $('#apffw_hide_dynamic_empty_pos').val(0);
            }
        });

        $('#apffw_show_count').change(function () {
            if ($(this).val() == 0) {
                $('#apffw_show_count_dynamic').val(0);
                $('#apffw_hide_dynamic_empty_pos').val(0);
            }
        });

        


        
        $(".apffw-admin-preloader").fadeOut("slow");

    });

    $('select[name="apffw_settings[show_images_by_attr_show]"]').change(function () {

        if ($(this).val() == 0) {
            $('select[name="apffw_settings[show_images_by_attr][]"]').parents('.select-wrap').hide();
        } else {
            $('select[name="apffw_settings[show_images_by_attr][]"]').parents('.select-wrap').show();
        }
    });

})(jQuery);


function apffw_show_info_popup(text) {
    jQuery("#apffw_html_buffer").text(text);
    jQuery("#apffw_html_buffer").fadeTo(333, 0.9);
}

function apffw_hide_info_popup() {
    window.setTimeout(function () {
        jQuery("#apffw_html_buffer").fadeOut(500);
    }, 333);
}


jQuery(document).ready(function () {
    if (apffw_ext_custom) {
        apffw_init_ext_uploader(apffw_abspath, apffw_ext_path, apffw_ext_url);
    }
    if (apffw_is_free_ver) {
        jQuery(function () {
            
            jQuery('#apffw_filter_btn_txt').prop('disabled', true);
            jQuery('#override_no_products').prop('disabled', true);
            jQuery('#apffw_filter_btn_txt').val('In the premium version');
            jQuery('#apffw_reset_btn_txt').prop('disabled', true);
            jQuery('#apffw_reset_btn_txt').val('In the premium version');
            jQuery('#apffw_hide_dynamic_empty_pos').prop('disabled', true);
            jQuery('#apffw_hide_dynamic_empty_pos_turbo_mode').prop('disabled', true);
            jQuery('select[name="apffw_settings[hide_terms_count_txt]"]').prop('disabled', true);
            jQuery('select[name="apffw_settings[show_images_by_attr_show]"]').prop('disabled', true);
            
            jQuery('#sapffw_search_slug').prop('disabled', true);
            jQuery('#sapffw_search_slug').val('In the premium version');
            jQuery('#sapffw_search_slug').parents('.apffw-control-section').addClass('apffw_premium_only');
            jQuery('#override_no_products').parents('.apffw-control-section').addClass('apffw_premium_only');
            jQuery('#hide_terms_count_txt').prop('disabled', true);
            jQuery('#hide_terms_count_txt').parents('.apffw-control-section').addClass('apffw_premium_only');
        });
    }
});