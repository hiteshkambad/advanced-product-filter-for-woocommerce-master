<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
 
 class APFFW_META_FILTER_CHECKBOX extends APFFW_META_FILTER_TYPE {
    public $type='checkbox';
    protected $js_func_name="apffw_init_meta_checkbox";
    public function __construct($key,$options,$apffw_settings) {
        parent::__construct($key,$options,$apffw_settings);
        $this->value_type=(isset($this->apffw_settings['meta_filter'][$this->meta_key]['title']))?$this->apffw_settings['meta_filter'][$this->meta_key]['title']:'string';
        $this->init();
    } 
    public  function init(){
        if(!isset($this->apffw_settings[$this->meta_key]['search_option'])){
            $this->apffw_settings[$this->meta_key]['search_option']=0;
        }
        if(!isset($this->apffw_settings[$this->meta_key]['search_value'])){
            $this->apffw_settings[$this->meta_key]['search_value']="";
        }
        
        add_action('apffw_print_html_type_options_' . $this->meta_key,array($this, 'draw_meta_filter_structure'));
        add_action('apffw_print_html_type_' .$this->meta_key,array($this, 'apffw_print_html_type_meta'));
        add_action('wp_footer',array($this, 'wp_footer') );
        add_action('wp_head',array($this, 'wp_head'),9 );
        add_filter('apffw_extensions_type_index',array($this, 'add_type_index'));
    } 
    public function wp_head(){
		APFFW_EXT::$includes['js_lang_custom'][$this->type."_".$this->meta_key] = APFFW_HELPER::wpml_translate(null,$this->apffw_settings['meta_filter'][$this->meta_key]['title']);

    }
    public function add_type_index($indexes){
        $indexes[]='"'.$this->type."_".$this->meta_key.'"' ;
        return $indexes;
        
    }

    public function wp_footer(){
         wp_enqueue_script( 'meta-checkbox-js',  $this->get_meta_filter_link(). 'js/checkbox.js', array('jquery'),APFFW_VERSION, true );
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
    public function apffw_print_html_type_meta(){
        $data['meta_key']=$this->meta_key;
        $data['options']=$this->type_options;
        $data['type']=(isset($this->apffw_settings['meta_filter'][$this->meta_key]['type']))?$this->apffw_settings['meta_filter'][$this->meta_key]['type']:'numeric';
        $data['search_option']=(isset($this->apffw_settings[$this->meta_key]['search_option']))?$this->apffw_settings[$this->meta_key]['search_option']:0;
        $data['search_value']=(isset($this->apffw_settings[$this->meta_key]['search_value']))?$this->apffw_settings[$this->meta_key]['search_value']:"";       
        $data['type']=(isset($this->apffw_settings['meta_filter'][$this->meta_key]['type']))?$this->apffw_settings['meta_filter'][$this->meta_key]['type']:'numeric';
      
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
        $data['type']=(isset($this->apffw_settings['meta_filter'][$this->meta_key]['type']))?$this->apffw_settings['meta_filter'][$this->meta_key]['type']:'numeric';
        $data['search_option']=(isset($this->apffw_settings[$this->meta_key]['search_option']))?$this->apffw_settings[$this->meta_key]['search_option']:0;
        $data['search_value']=(isset($this->apffw_settings[$this->meta_key]['search_value']))?$this->apffw_settings[$this->meta_key]['search_value']:"";
        return $this->render_html($this->get_meta_filter_path().'/views/additional_options.php', $data);
    }
    public function create_meta_query(){
        $curr_text=$this->check_current_request();
        if($curr_text){ 
            $value=1;
            $search_option=(isset($this->apffw_settings[$this->meta_key]['search_option']))?$this->apffw_settings[$this->meta_key]['search_option']:0;
            if($search_option==0){
                 $type=(isset($this->apffw_settings['meta_filter'][$this->meta_key]['type']))?$this->apffw_settings['meta_filter'][$this->meta_key]['type']:'numeric';
                 $search_value=(isset($this->apffw_settings[$this->meta_key]['search_value']))?$this->apffw_settings[$this->meta_key]['search_value']:"";
                 if($type!='numeric' AND !empty($search_value)){
                     $value=$search_value;  
                 }
                $meta=array(
                           'key' => $this->meta_key,
                           'value' => $value,
                           'compare'=>'=',
                           'type'    => $this->value_type,
                       );   
            }else{   
                $meta=array(
                           'key' => $this->meta_key,
                           'compare'=>'EXISTS'
                       );    
            }    
            return $meta;
        }else{
            return false;
        }
    }
    protected function check_current_request(){
        global $APFFW;
        $request = $APFFW->get_request_data();
        if(isset($request[$this->type."_".$this->meta_key]) AND $request[$this->type."_".$this->meta_key]){
            return $request[$this->type."_".$this->meta_key];
        }
        return false;    
    }
    public function get_js_func_name(){
        return $this->js_func_name;
    }
    public static function get_option_name($value,$key=NULL){
        return false;
    }
}
