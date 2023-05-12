<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

abstract class APFFW_EXT {

    public static $includes = array();
    public $type = NULL;
    public $html_type = NULL;
    public $index = NULL;
    public $html_type_dynamic_recount_behavior = 2;
    public $folder_name = NULL;
    public $options = array();
    public $taxonomy_type_additional_options = array();
    public static $ext_count = 0;
    public $apffw_settings = array();

    public function __construct() {
        $this->apffw_settings = get_option('apffw_settings', array());

        if (!isset(self::$includes['html_type_objects'])) {
            self::$includes['html_type_objects'] = array(); 
        }

        if (!isset(self::$includes['taxonomy_type_objects'])) {
            self::$includes['taxonomy_type_objects'] = array();
        }

        if (!isset(self::$includes['js'])) {
            self::$includes['js'] = array();
        }

        if (!isset(self::$includes['css'])) {
            self::$includes['css'] = array();
        }

        if (!isset(self::$includes['js_init_functions'])) {
            self::$includes['js_init_functions'] = array();
        }

        if ($this->type === NULL) {
            wp_die('APFFW EXTENSION TYPE SHOULD BE DEFINED!');
        }

        self::$ext_count++;
    }

    public function get_html_type_view() {
        if (file_exists($this->get_ext_override_path() . 'views' . DIRECTORY_SEPARATOR . 'apffw.php')) {
            return $this->get_ext_override_path() . 'views' . DIRECTORY_SEPARATOR . 'apffw.php';
        }
        return $this->get_ext_path() . 'views' . DIRECTORY_SEPARATOR . 'apffw.php';
    }

    public function print_html_type() {
        global $APFFW;
        _e($APFFW->render_html($this->get_html_type_view()));
    }

    public static function draw_options($options, $folder_name = '') {
        global $APFFW;
        foreach ($options as $key => $value) {
            _e($APFFW->render_html(APFFW_PATH . 'views' . DIRECTORY_SEPARATOR . 'ext_options.php', array(
                'options' => $value,
                'key' => $key,
                'apffw_settings' => $APFFW->settings
                    )
            ));
        }
    }

    public static function is_ext_activated($full_path) {
        $apffw_settings = get_option('apffw_settings', array());
        $idx1 = md5($full_path);
        $idx2 = self::get_ext_idx($full_path);
        $idx3 = self::get_ext_idx_new($full_path);
        $checked1 = $checked2 = $checked3 = FALSE;

        if (isset($apffw_settings['activated_extensions'])) {
            $checked1 = in_array($idx1, (array) $apffw_settings['activated_extensions']);
            $checked2 = in_array($idx2, (array) $apffw_settings['activated_extensions']);
            $checked3 = in_array($idx3, (array) $apffw_settings['activated_extensions']);
        }
        
        if ($checked1 OR $checked2 OR $checked3) {
            return $idx3;
        }
        return false;
    }
    
    public static function get_ext_idx($full_path) {
        return md5(str_replace(ABSPATH, '', $full_path));
    }

    public static function get_ext_idx_new($full_path) {
        $path = substr($full_path, strlen(WP_CONTENT_DIR));

		if(!$path){
			return md5(str_replace(ABSPATH, '', $full_path));
			
		}
        $path_str = preg_replace("@[/\\\]@", "", $path);
        return md5($path_str);
    }

    abstract public function init();

    abstract public function get_ext_path();

    public function get_ext_override_path() {
        return '';
    }
    
    abstract public function get_ext_link();
}
