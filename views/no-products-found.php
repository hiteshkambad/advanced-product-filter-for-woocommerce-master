<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $APFFW;
_e(do_shortcode(stripcslashes($APFFW->settings['override_no_products'])));
