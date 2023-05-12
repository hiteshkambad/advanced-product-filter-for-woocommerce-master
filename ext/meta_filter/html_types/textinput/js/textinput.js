"use strict";
var apffw_text_do_submit = false;

function apffw_init_meta_text_input() {
    jQuery('.apffw_meta_filter_textinput').keyup(function (e) {
        var val = jQuery(this).val();
        val=val.replace("\'","\&#039;");
        var uid = jQuery(this).data('uid');
        if (e.keyCode == 13) {
            apffw_text_do_submit = true;
            apffw_text_direct_search(jQuery(this).attr('name'), val);
            return true;
        }

        if (apffw_autosubmit) {
            apffw_current_values[jQuery(this).attr('name')] = val;
        } else {
            apffw_text_direct_search(jQuery(this).attr('name'), val);
        }



        if (val.length > 0) {
            jQuery('.apffw_textinput_go.' + uid).show(222);
        } else {
            jQuery('.apffw_textinput_go.' + uid).hide();
        }



    });

    
    jQuery('.apffw_textinput_go').on('click', function () {
        var uid = jQuery(this).data('uid');
        apffw_text_do_submit = true;
        var textinput=jQuery('.apffw_meta_filter_textinput.'+ uid);
        apffw_text_direct_search(textinput.attr('name'), textinput.val());
    });
}

function apffw_text_direct_search(name, slug) {
     slug = encodeURIComponent(slug);
    jQuery.each(apffw_current_values, function (index, value) {
        if (index == name) {
            delete apffw_current_values[name];
            return;
        }
    });

    if (slug != 0) {
        apffw_current_values[name] = slug;
    }

    apffw_ajax_page_num = 1;
    if (apffw_autosubmit || apffw_text_do_submit) {
        apffw_text_do_submit = false;
        apffw_submit_link(apffw_get_submit_link());
    }
}

