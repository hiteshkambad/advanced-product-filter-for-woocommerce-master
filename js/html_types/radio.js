"use strict";
function apffw_init_radios() {
    if (icheck_skin != 'none') {
        jQuery('.apffw_radio_term').iCheck('destroy');

        jQuery('.apffw_radio_term').iCheck({
            radioClass: 'iradio_' + icheck_skin.skin + '-' + icheck_skin.color,      
        });

        jQuery('.apffw_radio_term').off('ifChecked');
        jQuery('.apffw_radio_term').on('ifChecked', function (event) {
            jQuery(this).attr("checked", true);
            jQuery(this).parents('.apffw_list').find('.apffw_radio_term_reset').removeClass('apffw_radio_term_reset_visible');
            jQuery(this).parents('.apffw_list').find('.apffw_radio_term_reset').hide();
            jQuery(this).parents('li').eq(0).find('.apffw_radio_term_reset').eq(0).addClass('apffw_radio_term_reset_visible');
            var slug = jQuery(this).data('slug');
            var name = jQuery(this).attr('name');
            var term_id = jQuery(this).data('term-id');
            apffw_radio_direct_search(term_id, name, slug);
        });         
    } else {
        jQuery('.apffw_radio_term').on('change', function (event) {
            jQuery(this).attr("checked", true);
            var slug = jQuery(this).data('slug');
            var name = jQuery(this).attr('name');
            var term_id = jQuery(this).data('term-id');
			
			jQuery(this).parents('.apffw_list').find('.apffw_radio_term_reset').removeClass('apffw_radio_term_reset_visible');
            jQuery(this).parents('.apffw_list').find('.apffw_radio_term_reset').hide();
            jQuery(this).parents('li').eq(0).find('.apffw_radio_term_reset').eq(0).addClass('apffw_radio_term_reset_visible');
			
            apffw_radio_direct_search(term_id, name, slug);
        });
    }
    jQuery('.apffw_radio_term_reset').on('click',function () {
        apffw_radio_direct_search(jQuery(this).data('term-id'), jQuery(this).attr('data-name'), 0);
        jQuery(this).parents('.apffw_list').find('.checked').removeClass('checked');
        jQuery(this).parents('.apffw_list').find('input[type=radio]').removeAttr('checked');
        jQuery(this).removeClass('apffw_radio_term_reset_visible');
        return false;
    });
}
function apffw_radio_direct_search(term_id, name, slug) {

    jQuery.each(apffw_current_values, function (index, value) {
        if (index == name) {
            delete apffw_current_values[name];
            return;
        }
    });

    if (slug != 0) {
        apffw_current_values[name] = slug;
        jQuery('a.apffw_radio_term_reset_' + term_id).hide();
        jQuery('apffw_radio_term_' + term_id).filter(':checked').parents('li').find('a.apffw_radio_term_reset').show();
        jQuery('apffw_radio_term_' + term_id).parents('ul.apffw_list').find('label').css({'fontWeight': 'normal'});
        jQuery('apffw_radio_term_' + term_id).filter(':checked').parents('li').find('label.apffw_radio_label_' + slug).css({'fontWeight': 'bold'});
    } else {
        jQuery('a.apffw_radio_term_reset_' + term_id).hide();
        jQuery('apffw_radio_term_' + term_id).attr('checked', false);
        jQuery('apffw_radio_term_' + term_id).parent().removeClass('checked');
        jQuery('apffw_radio_term_' + term_id).parents('ul.apffw_list').find('label').css({'fontWeight': 'normal'});
    }

    apffw_ajax_page_num = 1;
    if (apffw_autosubmit) {
        apffw_submit_link(apffw_get_submit_link());
    }
}


