<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
global $APFFW;
$btn_url = '';

if (isset($img_url) && $img_url) {
    $btn_url = $img_url;
}
$style = '';

if ($btn_url != 'none' && $btn_url) {

    $style = "background-image: url('$btn_url');";
} elseif ($btn_url == 'none') {
    $style = "background-image: none ;";
}



$apffw_auto_hide_button_txt = '';
if (isset($APFFW->settings['apffw_auto_hide_button_txt'])) {
    $apffw_auto_hide_button_txt = APFFW_HELPER::wpml_translate(null, $APFFW->settings['apffw_auto_hide_button_txt']);
}
?>

<a href="javascript:void(0);" <?php _e(($style) ? 'style="' . $style . '"' : "");?> class="apffw_show_auto_form apffw_btn <?php if ($btn_url == 'none') _e('apffw_show_auto_form_txt'); ?>">
<?php _e(esc_html__($apffw_auto_hide_button_txt));?>
</a>

