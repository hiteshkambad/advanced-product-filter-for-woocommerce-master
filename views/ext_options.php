<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

extract($options);
?>
<div class="apffw-control-section">

    <h4><?php _e($title);?></h4>

    <div class="apffw-control-container">
        <div class="apffw-control">
            <?php
            if (!isset($apffw_settings[$key]))
            {
                $apffw_settings[$key] = $default;
            }
            
            switch ($type)
            {
                case 'textinput':
                    ?>
                    <input type="text" placeholder="<?php _e($placeholder);?>" name="apffw_settings[<?php _e($key);?>]" value="<?php _e(stripcslashes($apffw_settings[$key]));?>" id="<?php _e($key);?>" />
                    <?php
                    break;
                case 'color':
                    ?>
                    <input type="text" placeholder="<?php _e($placeholder);?>" class="apffw-color-picker" name="apffw_settings[<?php _e($key);?>]" value="<?php _e($apffw_settings[$key]);?>" id="<?php _e($key);?>" />
                    <?php
                    break;
                case 'select':
                    ?>
                    <select name="apffw_settings[<?php _e($key);?>]" id="<?php _e($key);?>">
                        <?php
                        if (!empty($select_options))
                        {
                            foreach ($select_options as $opt_key => $opt_title)
                            {
                                ?>
                                <option <?php _e(selected($apffw_settings[$key], $opt_key));?> value="<?php _e($opt_key);?>"><?php _e($opt_title);?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <?php
                    break;
                case 'image':
                    ?>
                        <input type="text" name="apffw_settings[<?php _e($key);?>]" value="<?php _e($apffw_settings[$key]);?>" id="<?php _e($key);?>" />
                        <a href="#" class="apffw-button apffw_select_image"><?php _e($placeholder);?></a>                    
                    <?php
                    break;

                default:
                    break;
            }
            ?>


        </div>
        <div class="apffw-description">
            <p class="description"><?php _e($description);?></p>
        </div>
    </div>

</div><!--/ .apffw-control-section-->
