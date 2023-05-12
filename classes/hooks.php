<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

final class APFFW_HOOKS
{
    public static function apffw_get_front_css_file_link()
    {
        return apply_filters('apffw_get_front_css_file_link', APFFW_LINK . 'css/front.css');
    }

}
