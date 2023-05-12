"use strict";
function apffw_init_mselects() {
    try {

        jQuery("select.apffw_mselect").chosen();
    } catch (e) {

    }

    jQuery('.apffw_mselect').change(function (a) {
        var slug = jQuery(this).val();
        var name = jQuery(this).attr('name');

        if (is_apffw_use_chosen) {
            var vals = jQuery(this).chosen().val();
            jQuery('.apffw_mselect[name=' + name + '] option:selected').removeAttr("selected");
            jQuery('.apffw_mselect[name=' + name + '] option').each(function (i, option) {
                var v = jQuery(this).val();
                if (jQuery.inArray(v, vals) !== -1) {
                    jQuery(this).prop("selected", true);
                }
            });
        }

        apffw_mselect_direct_search(name, slug);
        return true;
    });
    var containers = jQuery('.apffw_hide_empty_container_ms');
    jQuery.each(containers, function(i, item){
	var selector= jQuery(item).val();
	if(selector){
	    jQuery(selector).hide();
	}
	
    });    
}

function apffw_mselect_direct_search(name, slug) {
    
    var values = [];
    jQuery('.apffw_mselect[name=' + name + '] option:selected').each(function (i, v) {
        values.push(jQuery(this).val());
    });

    values = values.filter(function (item, pos) {
        return values.indexOf(item) == pos;
    });

    values = values.join(',');
    if (values.length) {
        apffw_current_values[name] = values;
    } else {
        delete apffw_current_values[name];
    }

    apffw_ajax_page_num = 1;
    if (apffw_autosubmit) {
        apffw_submit_link(apffw_get_submit_link());
    }
}


