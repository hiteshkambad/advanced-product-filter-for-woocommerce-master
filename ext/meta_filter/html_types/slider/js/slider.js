"use strict";
function apffw_init_meta_slider() {

    jQuery.each(jQuery('.apffw_metarange_slider'), function (index, input) {
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
                    apffw_current_values[jQuery(input).attr('name')] = parseFloat(ui.from, 10) + "^" + parseFloat(ui.to, 10);
		     
                    
		    var top_panel = jQuery('input[data-anchor^="apffw_n_' + jQuery(input).attr('name') + '"]');
		    var title = jQuery(input).parents('.apffw_container_inner').find('h4').text();
		    jQuery(top_panel).val(title + ': ' + parseFloat(ui.from, 10) + "-" + parseFloat(ui.to, 10));
		    jQuery(top_panel).attr('data-anchor','apffw_n_' + jQuery(input).attr('name')+ '_' + parseFloat(ui.from, 10) + "^" + parseFloat(ui.to, 10));

                    if (apffw_autosubmit || jQuery(input).within('.apffw').length == 0) {
                        apffw_submit_link(apffw_get_submit_link());
                    }
                    return false;
                }
            });
        } catch (e) {

        }
    });
}
