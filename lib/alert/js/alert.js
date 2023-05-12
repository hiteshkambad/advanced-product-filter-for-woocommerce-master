"use strict";

function pn_apffw_set_review(yes) {
    document.getElementById('pn_apffw_review_suggestion').style.display = 'none';
    if (yes) {
        document.getElementById('pn_apffw_review_yes').style.display = 'block';
    } else {
        document.getElementById('pn_apffw_review_no').style.display = 'block';
    }
}

function pn_apffw_dismiss_review(what = 1) {
    jQuery('#pn_apffw_ask_favour').fadeOut();

    if (what === 1) {
        jQuery.post(ajaxurl, {
            action: 'apffw_later_rate_alert'
        });
    } else {
        jQuery.post(ajaxurl, {
            action: 'apffw_dismiss_rate_alert'
        });
    }

    return true;
}



