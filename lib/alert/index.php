<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

add_action('init', function() {

    if (intval(get_option('apffw_manage_rate_alert', 0)) === -2) {
        return;
    }

}, 1);
