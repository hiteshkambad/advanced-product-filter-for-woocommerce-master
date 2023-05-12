"use strict";
function apffw_init_selects() {
    if (is_apffw_use_chosen) {
        try {
            jQuery("select.apffw_select, select.apffw_price_filter_dropdown").chosen();
        } catch (e) {

        }
    }

    jQuery('.apffw_select').change(function () {
        var slug = jQuery(this).val();
        var name = jQuery(this).attr('name');
        apffw_select_direct_search(this, name, slug);
    });

    var containers = jQuery('.apffw_hide_empty_container');
    jQuery.each(containers, function(i, item){
	var selector= jQuery(item).val();
	if(selector){
	    jQuery(selector).hide();
	}
	
    });
    
}

function apffw_select_direct_search(_this, name, slug) {

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
    if (apffw_autosubmit || jQuery(_this).within('.apffw').length == 0) {
        apffw_submit_link(apffw_get_submit_link());
    }

}


