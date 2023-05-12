<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

global $APFFW;
$apffw_meta_title=APFFW_HELPER::wpml_translate(null,$options['title']);
    
    if(isset($_REQUEST['hide_terms_count_txt_short']) AND $_REQUEST['hide_terms_count_txt_short']!=-1){
        if((int)$_REQUEST['hide_terms_count_txt_short']==1){
            $_REQUEST['hide_terms_count_txt']=1;
        }else{
            $_REQUEST['hide_terms_count_txt']=0;
        }
    }
    
if (isset($APFFW->settings[$meta_key ]) AND $APFFW->settings[$meta_key ]['show'])
{
    $count_string = "";
    $count = 0;
    $show_count = get_option('apffw_show_count', 0);
    $show_count_dynamic = get_option('apffw_show_count_dynamic', 0);
    $hide_dynamic_empty_pos = get_option('apffw_hide_dynamic_empty_pos', 0);
    $show=true;
    $disable="";
    $additional_tax=(isset($_REQUEST['additional_taxes']))?sanitize_post($_REQUEST['additional_taxes']):"";
    if (!$APFFW->is_isset_in_request_data("checkbox_".$meta_key))
    {
        if ($show_count)
        {
            $value=1;
            $type='checkbox';
            if($search_option==1){
               $type='checkbox_ex'; 
            }else{
               $type='checkbox'; 
            }
            if($type!='numeric' AND !empty($search_value)){
                $value=$search_value;
            }
            $meta_field=array(
                'key'=>$meta_key,
                'value'=>$value,
            );
            if ($show_count_dynamic) {
                $count_data = array();
                $count = $APFFW->dynamic_count(array(), $type,$additional_tax , $meta_field);
                $count_string = '(' . $count . ')';
                if ($count == 0) {
                    $disable = "disabled=''";
                }
            } else {                
            }

        }
        if ($hide_dynamic_empty_pos AND $count == 0)
        {
             $show=false;
        }
    }

    if (isset($_REQUEST['hide_terms_count_txt']) AND $_REQUEST['hide_terms_count_txt'])
    {
        $count_string = "";
    }
    ?>
<?php if($show):?>
    <div data-css-class="apffw_meta_checkbox_container" class="apffw_meta_checkbox_container apffw_container apffw_container_<?php _e("checkbox_".$meta_key);?>">
        <div class="apffw_container_overlay_item"></div>
        <div class="apffw_container_inner">
            <input type="checkbox" class="apffw_meta_checkbox" <?php _e($disable);?> id="apffw_meta_checkbox_<?php _e($meta_key);?>" <?php ?>  name="<?php _e("checkbox_".$meta_key);?>" value="0" <?php checked(1, $APFFW->is_isset_in_request_data("checkbox_".$meta_key) ? 1 : '', true) ?> />&nbsp;&nbsp;<label for="apffw_meta_checkbox_<?php _e($meta_key);?>"><?php _e($apffw_meta_title);?><?php _e($count_string);?></label><br />
        </div>
    </div>
<?php endif; ?>
    <?php
}

