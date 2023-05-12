<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

final class APFFW_EXT_SELECT_HIERARCHY extends APFFW_EXT {

    public $type = 'html_type';
    public $html_type = 'select_hierarchy';
    public $html_type_dynamic_recount_behavior = 'single';

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    public function get_ext_path()
    {
        return plugin_dir_path(__FILE__);
    }
    public function get_ext_override_path()
    {
        return get_stylesheet_directory(). DIRECTORY_SEPARATOR ."apffw". DIRECTORY_SEPARATOR ."ext". DIRECTORY_SEPARATOR .$this->html_type. DIRECTORY_SEPARATOR;
    }
    public function get_ext_link()
    {
        return plugin_dir_url(__FILE__);
    }

    public function apffw_add_html_types($types)
    {
        $types[$this->html_type] = esc_html__('Hierarchy drop-down', 'apffw-products-filter');
        return $types;
    }

    public function init()
    {
        add_filter('apffw_add_html_types', array($this, 'apffw_add_html_types'));
        self::$includes['js']['apffw_' . $this->html_type . '_html_items'] = $this->get_ext_link() . 'js/html_types/' . $this->html_type . '.js';
        self::$includes['css']['apffw_' . $this->html_type . '_html_items'] = $this->get_ext_link() . 'css/html_types/' . $this->html_type . '.css';
        self::$includes['js_init_functions'][$this->html_type] = 'apffw_init_' . $this->html_type;

        $this->taxonomy_type_additional_options = array(
            'show_chain_always' => array(
                'title' => esc_html__('Show chain always', 'apffw-products-filter'),
                'tip' => esc_html__('Allows show disabled drop-downs with its custom name. Necessary changing custom taxonomy label to title like: Country+City+District^My Locations', 'apffw-products-filter'),
                'type' => 'select',
                'options' => array(
                    0 => esc_html__('No', 'apffw-products-filter'),
                    1 => esc_html__('Yes', 'apffw-products-filter')
                )
            )
        );
    }

}

APFFW_EXT::$includes['taxonomy_type_objects']['select_hierarchy'] = new APFFW_EXT_SELECT_HIERARCHY();
