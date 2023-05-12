"use strict";
jQuery(function ($) {    
    $('.apffw_toggle_colors').on('click',function () {
        $(this).parent().find('ul.apffw_color_list').toggleClass('apffw_hide_options'); //toggle
    });
});
