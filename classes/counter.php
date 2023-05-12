<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

final class APFFW_QueryCounter
{
    public $post_count = 0;
    public $found_posts = 0;
    public $key_string = "";
    public $table = "";

    public function __construct($query)
    {
        $saving_memory=apply_filters('apffw_counter_method',false);
        global $wpdb;
        global $APFFW;
        $query = (array) $query;
        if($saving_memory){
            $query["nopaging"]=false;
            $query["posts_per_page"]=1;
        }
        
        $key = md5(json_encode($query));
        
        $this->key_string = 'apffw_count_cache_' . $key;
        $this->table = APFFW::$query_cache_table;
        
        $apffw_settings = get_option('apffw_settings', array());

        $_REQUEST['apffw_before_recount_query'] = 1;
        if ($apffw_settings['cache_count_data'])
        {
            $value = $this->get_value();
            if ($value != -1)
            {
                $this->post_count = $this->found_posts = $value;
            } else
            {
                $q = new APFFW_QueryCounterIn($query);
                if($saving_memory){
                    $this->post_count = $this->found_posts = $q->found_posts;  
                }else{
                    $this->post_count = $this->found_posts = $q->post_count;
                }
                unset($q);
                $this->set_value();
            }
        } else
        {
            $q = new APFFW_QueryCounterIn($query);
            if($saving_memory){
                $this->post_count = $this->found_posts = $q->found_posts;
            }else{
                $this->post_count = $this->found_posts = $q->post_count;
            }
            unset($q);
        }
        unset($_REQUEST['apffw_before_recount_query']);
    }

    private function set_value()
    {
        global $wpdb;
        $data=array(
            array(
                'type'=>'string',
                'val'=>$this->key_string
            ),
            array(
                'type'=>'int',
                'val'=>$this->post_count,
            ),
        );
        $wpdb->query(APFFW_HELPER::apffw_prepare("INSERT INTO {$this->table} (mkey, mvalue) VALUES (%s, %d)", $data));
    }

    private function get_value()
    {
        global $wpdb;
        $result = -1;
        $data=array(
            array(
                'type'=>'string',
                'val'=>$this->key_string
            )
        );        
        $sql = APFFW_HELPER::apffw_prepare("SELECT mkey,mvalue FROM {$this->table} WHERE mkey='%s'", $data);
        $value = $wpdb->get_results($sql);

        if (!empty($value))
        {
            $value = end($value);
            if (isset($value->mkey))
            {
                $result = $value->mvalue;
            }
        }

        return $result;
    }

}

final class APFFW_QueryCounterIn extends WP_Query
{

    function __construct($query = '')
    {
        parent::__construct($query);
    }

    function set_found_posts($q, $limits)
    {
        return false;
    }

    function setup_postdata($post)
    {
        return false;
    }

    function the_post()
    {
        return FALSE;
    }

    function have_posts()
    {
        return FALSE;
    }

}
