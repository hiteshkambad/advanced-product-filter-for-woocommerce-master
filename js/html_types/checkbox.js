"use strict";
function apffw_init_checkboxes() {
    if (icheck_skin != 'none') {
        jQuery('.apffw_checkbox_term').iCheck('destroy');

        jQuery('.apffw_checkbox_term').iCheck({
            checkboxClass: 'icheckbox_' + icheck_skin.skin + '-' + icheck_skin.color,
        });


        jQuery('.apffw_checkbox_term').off('ifChecked');
        jQuery('.apffw_checkbox_term').on('ifChecked', function (event) {
            jQuery(this).attr("checked", true);
            jQuery(".apffw_select_radio_check input").attr('disabled','disabled');
            apffw_checkbox_process_data(this, true);
        });

        jQuery('.apffw_checkbox_term').off('ifUnchecked');
        jQuery('.apffw_checkbox_term').on('ifUnchecked', function (event) {
            jQuery(this).attr("checked", false);
            apffw_checkbox_process_data(this, false);
        });

        jQuery('.apffw_checkbox_label').off();
        jQuery('label.apffw_checkbox_label').on('click', function () {
            if(jQuery(this).prev().find('.apffw_checkbox_term').is(':disabled')){
                return false;
            }
            if (jQuery(this).prev().find('.apffw_checkbox_term').is(':checked')) {
                jQuery(this).prev().find('.apffw_checkbox_term').trigger('ifUnchecked');
                jQuery(this).prev().removeClass('checked');
            } else {
                jQuery(this).prev().find('.apffw_checkbox_term').trigger('ifChecked');
                jQuery(this).prev().addClass('checked');
            }
            
            
        });
        

    } else {
        jQuery('.apffw_checkbox_term').on('change', function (event) {
            if (jQuery(this).is(':checked')) {
                jQuery(this).attr("checked", true);
                apffw_checkbox_process_data(this, true);
            } else {
                jQuery(this).attr("checked", false);
                apffw_checkbox_process_data(this, false);
            }
        });
    }
}
function apffw_checkbox_process_data(_this, is_checked) {
    var tax = jQuery(_this).data('tax');
    var name = jQuery(_this).attr('name');
    var term_id = jQuery(_this).data('term-id');
    apffw_checkbox_direct_search(term_id, name, tax, is_checked);
}
function apffw_checkbox_direct_search(term_id, name, tax, is_checked) {

    var values = '';
    var checked = true;
    if (is_checked) {
        if (tax in apffw_current_values) {
            apffw_current_values[tax] = apffw_current_values[tax] + ',' + name;
        } else {
            apffw_current_values[tax] = name;
        }
        checked = true;
    } else {
        values = apffw_current_values[tax];
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
        checked = false;
    }
    jQuery('.apffw_checkbox_term_' + term_id).attr('checked', checked);
    apffw_ajax_page_num = 1;
   
    if (apffw_autosubmit) {

        apffw_submit_link(apffw_get_submit_link());
    }

}


