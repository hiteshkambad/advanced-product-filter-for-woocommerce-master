"use strict";
function apffw_init_meta_checkbox() {
    if (icheck_skin != 'none') {
        
        jQuery('.apffw_meta_checkbox').iCheck({
            checkboxClass: 'icheckbox_' + icheck_skin.skin + '-' + icheck_skin.color,
        });
        
        jQuery('.apffw_meta_checkbox').on('ifChecked', function (event) {
            jQuery(this).attr("checked", true);
            apffw_current_values[jQuery(this).attr("name")] = 1;
            apffw_ajax_page_num = 1;
            if (apffw_autosubmit) {
                apffw_submit_link(apffw_get_submit_link());
            }
        });

        jQuery('.apffw_meta_checkbox').on('ifUnchecked', function (event) {
            jQuery(this).attr("checked", false);
            delete apffw_current_values[jQuery(this).attr("name")];
            apffw_ajax_page_num = 1;
            if (apffw_autosubmit) {
                apffw_submit_link(apffw_get_submit_link());
            }
        });

    } else {
        jQuery('.apffw_meta_checkbox').on('change', function (event) {
            if (jQuery(this).is(':checked')) {
                jQuery(this).attr("checked", true);
                apffw_current_values[jQuery(this).attr("name")] = 1;
                apffw_ajax_page_num = 1;
                if (apffw_autosubmit) {
                    apffw_submit_link(apffw_get_submit_link());
                }
            } else {
                jQuery(this).attr("checked", false);
                delete apffw_current_values[jQuery(this).attr("name")];
                apffw_ajax_page_num = 1;
                if (apffw_autosubmit) {
                    apffw_submit_link(apffw_get_submit_link());
                }
            }
        });
    }
}


