"use strict";
function apffw_init_colors() {
    jQuery('.apffw_color_term').each(function () {

        var color = jQuery(this).data('color');
        var img = jQuery(this).data('img');

        var bg = '';
        if (img.length > 0) {
            bg = 'background: url(' + img + ')';
        } else {
            bg = 'background:' + color + ' !important';
        }

        var span = jQuery('<span style="' + bg + '" class="' + jQuery(this).attr('type') + ' ' + jQuery(this).attr('class') + '" title=""></span>').on('click', apffw_color_do_check).mousedown(apffw_color_do_down).mouseup(apffw_color_do_up);
        if (jQuery(this).is(':checked')) {
            span.addClass('checked');
        }
        jQuery(this).wrap(span).hide();
        jQuery(this).after('<span class="apffw_color_checked"></span>');//for checking
    });

    function apffw_color_do_check() {
        var is_checked = false;
        var radio=false;
        if(jQuery(this).parents(".apffw_list_color").data("type")=="radio"){
            radio=true;
        }
        if(radio){
            var elements=jQuery(this).parents(".apffw_list_color").find(".apffw_color_term");
            jQuery(elements).removeClass('checked');
            jQuery(elements).children().prop("checked", false);
        }	
	
        if (jQuery(this).hasClass('checked')) {
            jQuery(this).removeClass('checked');
            jQuery(this).children().prop("checked", false);
        } else {
            jQuery(this).addClass('checked');
            jQuery(this).children().prop("checked", true);
            is_checked = true;
        }

        apffw_color_process_data(this, is_checked,radio);
    }

    function apffw_color_do_down() {
        jQuery(this).addClass('clicked');
    }

    function apffw_color_do_up() {
        jQuery(this).removeClass('clicked');
    }
}

function apffw_color_process_data(_this, is_checked, radio) {
    var tax = jQuery(_this).find('input[type=checkbox]').data('tax');
    var name = jQuery(_this).find('input[type=checkbox]').attr('name');
    var term_id = jQuery(_this).find('input[type=checkbox]').data('term-id');
    apffw_color_direct_search(term_id, name, tax, is_checked, radio);
}

function apffw_color_direct_search(term_id, name, tax, is_checked, radio) {

    var values = '';
    var checked = true;
    if (is_checked) {
	if(!radio){
	    if (tax in apffw_current_values) {
		apffw_current_values[tax] = apffw_current_values[tax] + ',' + name;
	    } else {
		apffw_current_values[tax] = name;
	    }
	}else{
	    apffw_current_values[tax] = name;
	}
        checked = true;
    } else {
	if(!radio){
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
	}else{
	    delete apffw_current_values[tax];
	}
        checked = false;
    }
    jQuery('.apffw_color_term_' + term_id).attr('checked', checked);
    apffw_ajax_page_num = 1;
    if (apffw_autosubmit) {
        apffw_submit_link(apffw_get_submit_link());
    }
}


