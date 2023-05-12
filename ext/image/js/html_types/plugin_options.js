"use strict";
jQuery(function ($) {
    $('.apffw_toggle_images').on('click', function () {
        $(this).parent().find('ul.apffw_image_list').toggleClass('apffw_hide_options');
    });
});
