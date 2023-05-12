"use strict";
var apffw_edit_view = false;
var apffw_current_conatiner_class = '';
var apffw_current_containers_data = {};

jQuery(function () {
    jQuery('.apffw_edit_view').on('click',function () {
        apffw_edit_view = true;
        var sid = jQuery(this).data('sid');
        var sid_tmp = sid.substring(0, sid.indexOf(' '));
        if(sid_tmp){
           sid=sid_tmp; 
        }
        var css_class = 'apffw_sid_' + sid;
        jQuery(this).next('div').html(css_class);
        
        
        jQuery("." + css_class + " .apffw_container_overlay_item").show();
        jQuery("." + css_class + " .apffw_container").addClass('apffw_container_overlay');
        jQuery.each(jQuery("." + css_class + " .apffw_container_overlay_item"), function (index, ul) {
            jQuery(this).html(jQuery(this).parents('.apffw_container').data('css-class'));
        });

        return false;
    });
    
    
    apffw_init_masonry();
    
});

function apffw_init_masonry() {
    return;

}



