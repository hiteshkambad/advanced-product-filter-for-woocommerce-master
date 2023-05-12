<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
 
 class APFFW_META_FILTER_SLIDER extends APFFW_META_FILTER_TYPE {
    public $type='slider';
    public $js_func_name="apffw_init_meta_slider";
    public $range="1^100";
    public function __construct($key,$options,$apffw_settings) {
        parent::__construct($key,$options,$apffw_settings);
        $this->init(); 
    } 
    public  function init(){
        add_action('apffw_print_html_type_options_' . $this->meta_key,array($this, 'draw_meta_filter_structure'));
        add_action('apffw_print_html_type_' .$this->meta_key,array($this, 'apffw_print_html_type_meta'));
        add_action('wp_footer',array($this, 'wp_footer') ); 
        if(isset($this->apffw_settings[$this->meta_key]['range'])){
            $this->range=$this->apffw_settings[$this->meta_key]['range'];
        }else{
            $this->apffw_settings[$this->meta_key]['range']="1^100";
            $this->apffw_settings[$this->meta_key]['step']=1;
            $this->apffw_settings[$this->meta_key]['prefix']=$this->apffw_settings[$this->meta_key]['postfix']="";
        }
        add_filter('apffw_extensions_type_index',array($this, 'add_type_index'));
    } 
     
    public function get_meta_filter_path(){
        return plugin_dir_path(__FILE__);
    }
    public function get_meta_filter_override_path()
    {
        return get_stylesheet_directory(). DIRECTORY_SEPARATOR ."apffw". DIRECTORY_SEPARATOR ."ext". DIRECTORY_SEPARATOR .'meta_filter'. DIRECTORY_SEPARATOR ."html_types". DIRECTORY_SEPARATOR .$this->type. DIRECTORY_SEPARATOR;
    }
    public function get_meta_filter_link(){
        return plugin_dir_url(__FILE__);
    }
    public function add_type_index($indexes){
        $indexes[]='"'.$this->type."_".$this->meta_key.'"' ;
        return $indexes;
        
    }    
    public function wp_footer(){
        wp_enqueue_script('ion.range-slider', APFFW_LINK . 'js/ion.range-slider/js/ion-rangeSlider/ion.rangeSlider.min.js', array('jquery'),APFFW_VERSION);
        wp_enqueue_style('ion.range-slider', APFFW_LINK . 'js/ion.range-slider/css/ion.rangeSlider.css',array(),APFFW_VERSION);   
        $ion_slider_skin = $this->apffw_settings['ion_slider_skin'];
        wp_enqueue_style('ion.range-slider-skin', APFFW_LINK . 'js/ion.range-slider/css/ion.rangeSlider.' . $ion_slider_skin . '.css',array(),APFFW_VERSION);
        wp_enqueue_script( 'meta-slider-js',  $this->get_meta_filter_link(). 'js/slider.js', array('jquery') ,APFFW_VERSION, true );
        wp_enqueue_style( 'meta-slider-css',  $this->get_meta_filter_link(). 'css/slider.css',array(),APFFW_VERSION );
    } 
    public function apffw_print_html_type_meta(){
        $data['meta_key']=$this->meta_key;
        $data['options']=$this->type_options;
        $data['meta_settings']= $data['meta_options']= (isset($this->apffw_settings[$this->meta_key]))?$this->apffw_settings[$this->meta_key]:"";
        $data['range']=$this->range;
        if(isset($this->apffw_settings[$this->meta_key]["show"]) AND $this->apffw_settings[$this->meta_key]["show"]){
            if(file_exists($this->get_meta_filter_override_path(). 'views' . DIRECTORY_SEPARATOR . 'apffw.php')){
                _e($this->render_html($this->get_meta_filter_override_path() . 'views' .DIRECTORY_SEPARATOR . 'apffw.php', $data));
            }else{
                _e($this->render_html($this->get_meta_filter_path().'/views/apffw.php', $data));
            }
        }
    }    
    protected function draw_additional_options(){
        $data=array();
        $data['key']=$this->meta_key;
        $data['settings']=$this->apffw_settings;
        return $this->render_html($this->get_meta_filter_path().'/views/additional_options.php', $data);
    }
    protected function check_current_request(){
        global $APFFW;
        $request = $APFFW->get_request_data();
        if(isset($request[$this->type."_".$this->meta_key]) AND $request[$this->type."_".$this->meta_key]){
            return $request[$this->type."_".$this->meta_key];
        }
        return false;    
    }    
    public function create_meta_query(){
        $curr_request=$this->check_current_request();
        if($curr_request){  
            $curr_range=array();
            $curr_range=explode("^",$curr_request);
            $from=0;
            $to=0;
            $from= floatval($curr_range[0]); 
            if(count($curr_range)>1){
                $to= floatval($curr_range[1]);                 
            }else{
                $range=explode("^",$this->range,2);
                $to=$range[1];
            }
            $type=apply_filters('apffw_slider_meta_query_type','numeric',$this->meta_key);
            $meta=array(
                'key' => $this->meta_key,
                'value'   => array( $from, $to ),
				'type'    => $type,
                'compare'=>'BETWEEN',
            );    
            return $meta;
        }else{
            return false;
        }
    }
    public function get_js_func_name(){
        return $this->js_func_name;
    }
    public static function get_option_name($value,$key=NULL){
        global $APFFW;
        $value_txt="";
        $prefix="";
        $postfix="";
        $arr_val= explode("^", $value,2);
        if(count($arr_val)>1){
            if($key){
                $meta_key=str_replace("slider_", "",$key);
                $prefix=(isset($APFFW->settings[$meta_key]['prefix']))?$APFFW->settings[$meta_key]['prefix']:"";
                $postfix=(isset($APFFW->settings[$meta_key]['postfix']))?$APFFW->settings[$meta_key]['postfix']:"";             
            }
           $value_txt = sprintf(__('from %s %s to %s %s', 'apffw-products-filter'),$prefix, $arr_val[0], $arr_val[1],$postfix); 
        }
        
        return $value_txt;
    }
}
