<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

class APFFW_ADV {
    public $notices_list = array();
    public function __construct($alert_list = array()) {
        if (isset($_SERVER['REQUEST_URI'])) {
            if (substr_count($_SERVER['REQUEST_URI'], 'theme-install.php')) {
                return;
            }
        } else {
            if (isset($_SERVER['PHP_SELF'])) {
                if (substr_count($_SERVER['PHP_SELF'], 'theme-install.php')) {
                    return;
                }
            }
        }

        $this->notices_list = array_merge($this->notices_list, $alert_list);
    }

    public function init() {
        if (is_admin()) {
            if (get_option('apffw_version') != APFFW_VERSION) {
                update_option('apffw_version', APFFW_VERSION);

                $alert = (array) get_option('apffw_alert', array());
                foreach ($this->notices_list as $key => $item) {
                    $alert[$key] = "";
                }

                add_option('apffw_alert', $alert, '', 'no');
                update_option('apffw_alert', $alert);
            }

            foreach ($this->notices_list as $key => $item) {
                if (file_exists(WP_PLUGIN_DIR . '/' . $item)) {
                    unset($this->notices_list[$key]);
                }
            }

            global $wp_version;
            if (version_compare($wp_version, '4.2', '>=') && current_user_can('install_plugins') && !empty($this->notices_list)) {
                $alert = (array) get_option('apffw_alert', array());
                foreach ($this->notices_list as $key => $item) {
                    if (empty($alert[$key]) AND method_exists($this, 'alert_' . $key)) {
                        add_action('admin_notices', array($this, 'alert_' . $key));
                        add_action('network_admin_notices', array($this, 'alert_' . $key));
                    }
                }
                add_action('wp_ajax_apffw_dismiss_alert', array($this, 'apffw_dismiss_alert'));
                add_action('admin_enqueue_scripts', array($this, 'apffw_alert_scripts'));
            }
        }
    }

    public function apffw_dismiss_alert() {
        $alert = (array) get_option('apffw_alert', array());
        $alert[$_POST['alert']] = 1;

        add_option('apffw_alert', $alert, '', 'no');
        update_option('apffw_alert', $alert);

        exit;
    }

    public function apffw_alert_scripts() {
        wp_enqueue_script('plugin-install');
        add_thickbox();
        wp_enqueue_script('updates');
    }
    
}
