<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
$request_data = $this->get_request_data();
$min_price = 0;
$max_price = APFFW_HELPER::get_max_price();

$min_price_txt = esc_html__('min price', 'apffw-products-filter');
$max_price_txt = esc_html__('max price', 'apffw-products-filter');



if (isset($request_data['min_price'])) {
    $min_price = $request_data['min_price'];
}

if (isset($request_data['max_price'])) {
    $max_price = $request_data['max_price'];
}

//+++
$min_price_data = $min_price;
$max_price_data = $max_price;

?>


<div class="apffw_price_filter_txt_container">

    <input type="text" class="apffw_price_filter_txt apffw_price_filter_txt_from" placeholder="<?php _e($min_price_txt);?>" data-value="<?php _e($min_price_data);?>" value="<?php _e($min_price);?>" />&nbsp;<input type="text" class="apffw_price_filter_txt apffw_price_filter_txt_to" placeholder="<?php _e($max_price_txt);?>" name="max_price" data-value="<?php _e($max_price_data);?>" value="<?php _e($max_price);?>" />


</div>


<?php
