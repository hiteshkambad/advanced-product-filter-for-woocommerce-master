<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
wp_enqueue_script('ion.range-slider', APFFW_LINK . 'js/ion.range-slider/js/ion-rangeSlider/ion.rangeSlider.min.js', array('jquery'), APFFW_VERSION);
wp_enqueue_style('ion.range-slider', APFFW_LINK . 'js/ion.range-slider/css/ion.rangeSlider.css', array(), APFFW_VERSION);
$ion_slider_skin = 'skinNice';
if (isset($this->settings['ion_slider_skin'])) {
    $ion_slider_skin = $this->settings['ion_slider_skin'];
}
wp_enqueue_style('ion.range-slider-skin', APFFW_LINK . 'js/ion.range-slider/css/ion.rangeSlider.' . $ion_slider_skin . '.css', array(), APFFW_VERSION);

$request = $this->get_request_data();
$uniqid = uniqid();
if (!isset($additional_taxes)) {
    $additional_taxes = "";
}
$preset_min = APFFW_HELPER::get_min_price($additional_taxes);
$preset_max = APFFW_HELPER::get_max_price($additional_taxes);
if (wc_tax_enabled() && 'incl' === get_option('woocommerce_tax_display_shop') && !wc_prices_include_tax()) {
    $tax_classes = array_merge(array(''), WC_Tax::get_tax_classes());
    $class_max = $preset_max;
	$class_min = $preset_min;
    foreach ($tax_classes as $tax_class) {
        if ($tax_rates = WC_Tax::get_rates($tax_class)) {
            $class_max = ceil($preset_max + WC_Tax::get_tax_total(WC_Tax::calc_exclusive_tax($preset_max, $tax_rates)));
			$class_min = floor($preset_min + WC_Tax::get_tax_total(WC_Tax::calc_exclusive_tax($preset_min, $tax_rates)));
        }
    }
	$preset_min = $class_min;
    $preset_max = $class_max;
}

$min_price = $this->is_isset_in_request_data('min_price') ? esc_attr($request['min_price']) : $preset_min;
$max_price = $this->is_isset_in_request_data('max_price') ? esc_attr($request['max_price']) : $preset_max;


$slider_step = 1;
if (isset($this->settings['by_price']['ion_slider_step'])) {
    $slider_step = $this->settings['by_price']['ion_slider_step'];
    if (!$slider_step) {
        $slider_step = 1;
    }
}

$slider_prefix = '';
$slider_postfix = '';

    $currency_pos = get_option('woocommerce_currency_pos');

switch ($currency_pos) {
    case 'left':
        $slider_prefix = get_woocommerce_currency_symbol();
        break;
    case 'left_space':
        $slider_prefix = get_woocommerce_currency_symbol() . ' ';
        break;
    case 'right':
        $slider_postfix = get_woocommerce_currency_symbol();
        break;
    case 'right_space':
        $slider_postfix = ' ' . get_woocommerce_currency_symbol();
        break;

    default:
        break;
}


if ($preset_max < $max_price) {
    $max = $max_price;
} else {
    $max = $preset_max;
}
if ($preset_min > $min_price) {
    $min = $min_price;
} else {
    $min = $preset_min;
}
$tax = 1.0;
if (isset($this->settings['by_price']['price_tax']) AND $this->settings['by_price']['price_tax'] != 0) {
    $tax = $tax + floatval($this->settings['by_price']['price_tax']) / 100.00;
    $min_tax = floor($min * $tax);
    $max_tax = ceil($max * $tax);

    if ($min != $min_price) {
        $min_price = ($min_price * $tax);
    } else {
        $min_price = $min_tax;
    }
    if ($max != $max_price) {
        $max_price = ($max_price * $tax);
    } else {
        $max_price = $max_tax;
    }
    $min = $min_tax;
    $max = $max_tax;
}

if ($min == $max) {
    return false;
}

if (isset($this->settings['by_price']['show_text_input']) AND $this->settings['by_price']['show_text_input']) {
    ?>
    <div class="apffw_price_filter_txt_slider">
        <input type="number" class="apffw_price_filter_txt apffw_price_filter_txt_from" placeholder="<?php _e($min)?>" data-value="<?php _e($min);?>" value="<?php _e($min_price);?>" />&nbsp;
        <input type="number" class="apffw_price_filter_txt apffw_price_filter_txt_to" placeholder="<?php _e($max);?>" name="max_price" data-value="<?php _e($max);?>" value="<?php _e($max_price);?>" />        
        <div class="apffw_float_none"></div>	
    </div>	
<?php } ?>
<input class="apffw_range_slider" id="<?php _e($uniqid);?>" data-taxes="<?php _e($tax);?>" data-min="<?php _e($min);?>" data-max="<?php _e($max);?>" data-min-now="<?php _e($min_price);?>" data-max-now="<?php _e($max_price);?>" data-step="<?php _e($slider_step);?>" data-slider-prefix="<?php _e($slider_prefix);?>" data-slider-postfix="<?php _e($slider_postfix);?>" value="" />
