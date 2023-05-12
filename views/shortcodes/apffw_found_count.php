<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');



global $wp_query;
$show = false;
if ($this->is_isset_in_request_data($this->get_sapffw_search_slug())) {
    $show = true;
}
if (isset($this->settings['apffw_turbo_mode']['enable']) AND $this->settings['apffw_turbo_mode']['enable']) {
    $show = true;
}
?>
<?php if ($show): ?>
    <span class="apffw_found_count"><?php _e(esc_html(isset($_REQUEST['apffw_wp_query_found_posts']) ? sanitize_text_field($_REQUEST['apffw_wp_query_found_posts']) : $wp_query->found_posts));?></span>
<?php else: ?>
	<span class="apffw_found_count"></span>
<?php endif; ?>
