"use strict";
function apffw_init_meta_datepicker() {
    try {
        jQuery.each(jQuery(".apffw_calendar"), function (i, item_calend) {
            jQuery(item_calend).datepicker(
                    {
                        showWeek: true,
                        firstDay: 1,
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: jQuery(item_calend).data('format'),
                        showButtonPanel: true,
                        isRTL: false,
                        showAnim: 'fadeIn',
                        onSelect: function (selectedDate, self) {
                            var css_class = 'apffw_calendar_from';
                            var meta_key = jQuery(this).data("meta-key");
                            if (jQuery(this).hasClass('apffw_calendar_from')) {
                                var date = new Date(parseInt(self.currentYear, 10), parseInt(self.currentMonth, 10), parseInt(self.currentDay, 10), 0, 0, 1);
                                var mktime = (date.getTime() / 1000);
                                css_class = 'apffw_calendar_to';
                                jQuery(this).parent().find('.' + css_class).datepicker("option", "minDate", selectedDate);
                                jQuery(this).prev('input[name=' + meta_key + '_from]').val(mktime);
                            } else {
                                var date = new Date(parseInt(self.currentYear, 10), parseInt(self.currentMonth, 10), parseInt(self.currentDay, 10), 23, 59, 59);
                                var mktime = (date.getTime() / 1000);
                                jQuery(this).parent().find('.' + css_class).datepicker("option", "maxDate", selectedDate);
                                jQuery(this).prev('input[name=' + meta_key + '_to]').val(mktime);

                            }

                            apffw_meta_datepicker_check_data(meta_key);
                            //***
                            apffw_ajax_page_num = 1;
                            apffw_meta_datepicker_reset_check();

                            if (apffw_autosubmit) {
                                apffw_submit_link(apffw_get_submit_link());
                            }

                            return false;

                        }
                    }
            );

        });

        jQuery('body').on('keyup', ".apffw_calendar", function (e) {
            if (e.keyCode == 8 || e.keyCode == 46) {
                jQuery.datepicker._clearDate(this);
                jQuery(this).prev('input[type=hidden]').val("");
                apffw_meta_datepicker_check_data(jQuery(this).data('meta-key'));
                apffw_meta_datepicker_reset_check();

            }
        });
        jQuery('body').on('click', ".apffw_meta_datepicker_reset", function (e) {
            var name = jQuery(this).data('name');
            jQuery("input[name='" + name + "']").val("");
            jQuery(this).prev('input.apffw_calendar').datepicker('setDate', null);
            apffw_meta_datepicker_reset_check();
            jQuery(this).hide();
            apffw_meta_datepicker_check_data(jQuery(this).data('meta-key'), 1);
            if (apffw_autosubmit || jQuery(input).within('.apffw').length == 0) {
                apffw_submit_link(apffw_get_submit_link());
            }

            return false;
        });



        function apffw_meta_datepicker_check_data(meta_key, is_reset) {

            var from = 'i';
            var to = 'i';

            if (jQuery('input[name=' + meta_key + '_from]').val()) {
                from = jQuery('input[name=' + meta_key + '_from]').val();
            }
            if (jQuery('input[name=' + meta_key + '_to]').val()) {
                to = jQuery('input[name=' + meta_key + '_to]').val();
            }

            apffw_current_values['datepicker_' + meta_key] = from + "-" + to;

            if (from == "i" && to == "i") {
                delete apffw_current_values['datepicker_' + meta_key];
            }
            if (typeof is_reset != 'undefined' && is_reset) {
                delete apffw_current_values['datepicker_' + meta_key];
            }

        }

        function apffw_meta_datepicker_reset_check() {

            var inputs = jQuery('.apffw_meta_datepicker_data');

            jQuery.each(inputs, function (ind, input) {

                if (parseInt(jQuery(input).val(), 10) > 0 && jQuery(input).val() != "i") {
                    var name = jQuery(input).attr('name');
                    jQuery(".apffw_meta_datepicker_reset[data-name='" + name + "']").show();
                } else {
                    jQuery(".apffw_meta_datepicker_reset[data-name='" + name + "']").hide();
                }

            });

        }
        //+++
        jQuery(".apffw_calendar").each(function () {
            var mktime = parseInt(jQuery(this).prev('input[type=hidden]').val(), 10);
            if (mktime > 0) {
                var date = new Date(mktime * 1000);
                jQuery(this).datepicker('setDate', new Date(date));
                //+++
                var css_class = 'apffw_calendar_from';
                var selectedDate = jQuery(this).datepicker('getDate');
                if (jQuery(this).hasClass('mdf_calendar_from')) {
                    css_class = 'apffw_calendar_to';
                    jQuery(this).parent().find('.' + css_class).datepicker("option", "minDate", selectedDate);
                } else {
                    jQuery(this).parent().find('.' + css_class).datepicker("option", "maxDate", selectedDate);
                }
            }
        });
        jQuery('#ui-datepicker-div').hide();
        apffw_meta_datepicker_reset_check();
    } catch (e) {

    }

}
