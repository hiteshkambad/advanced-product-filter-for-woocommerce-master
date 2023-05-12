<?php
if (!class_exists('APFFW_Resize')) {
    class APFFW_Resize {

        static private $instance = null;

        private function __construct() {            
        }

        private function __clone() {            
        }

        static public function getInstance() {
            if (self::$instance == null) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        public function process($url, $width = null, $height = null, $crop = null, $single = true, $upscale = false) {

            if (!$url || (!$width && !$height ))
                return false;


            if (true === $upscale)
                add_filter('image_resize_dimensions', array($this, 'aq_upscale'), 10, 6);


            $upload_info = wp_upload_dir();
            $upload_dir = $upload_info['basedir'];
            $upload_url = $upload_info['baseurl'];

            $http_prefix = "http://";
            $https_prefix = "https://";


            if (!strncmp($url, $https_prefix, strlen($https_prefix))) {
                $upload_url = str_replace($http_prefix, $https_prefix, $upload_url);
            } elseif (!strncmp($url, $http_prefix, strlen($http_prefix))) { 
                $upload_url = str_replace($https_prefix, $http_prefix, $upload_url);
            }



            if (false === strpos($url, $upload_url))
                return false;


            $rel_path = str_replace($upload_url, '', $url);
            $img_path = $upload_dir . $rel_path;


            if (!file_exists($img_path) or!getimagesize($img_path))
                return false;


            $info = pathinfo($img_path);
            $ext = $info['extension'];

            try {
                list( $orig_w, $orig_h ) = getimagesize($img_path);
            } catch (Exception $e) {
                return false;
            }



            $dims = image_resize_dimensions($orig_w, $orig_h, $width, $height, $crop);
            $dst_w = $dims[4];
            $dst_h = $dims[5];


            if (!$dims && ( ( ( null === $height && $orig_w == $width ) xor ( null === $width && $orig_h == $height ) ) xor ( $height == $orig_h && $width == $orig_w ) )) {
                $img_url = $url;
                $dst_w = $orig_w;
                $dst_h = $orig_h;
            } else {
                $suffix = "{$dst_w}x{$dst_h}";
                $dst_rel_path = str_replace('.' . $ext, '', $rel_path);
                $destfilename = "{$upload_dir}{$dst_rel_path}-{$suffix}.{$ext}";

                if (!$dims || ( true == $crop && false == $upscale && ( $dst_w < $width || $dst_h < $height ) )) {                
                    return false;
                }
                elseif (file_exists($destfilename) && getimagesize($destfilename)) {
                    $img_url = "{$upload_url}{$dst_rel_path}-{$suffix}.{$ext}";
                }
                else {

                    $resized_img_path = $this->image_resize($img_path, $width, $height, $crop); // Fallback foo.
                    if (!is_wp_error($resized_img_path)) {
                        $resized_rel_path = str_replace($upload_dir, '', $resized_img_path);
                        $img_url = $upload_url . $resized_rel_path;
                    } else {
                        return false;
                    }
                }
            }


            if (true === $upscale)
                remove_filter('image_resize_dimensions', array($this, 'aq_upscale'));


            if ($single) {
                $image = $img_url;
            } else {
                $image = array(
                    0 => $img_url,
                    1 => $dst_w,
                    2 => $dst_h
                );
            }

            return $image;
        }

        public function image_resize($file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90) {

            $editor = wp_get_image_editor($file);
            if (is_wp_error($editor))
                return $editor;
            $editor->set_quality($jpeg_quality);

            $resized = $editor->resize($max_w, $max_h, $crop);
            if (is_wp_error($resized))
                return $resized;

            $dest_file = $editor->generate_filename($suffix, $dest_path);
            $saved = $editor->save($dest_file);

            if (is_wp_error($saved))
                return $saved;

            return $dest_file;
        }

        public function aq_upscale($default, $orig_w, $orig_h, $dest_w, $dest_h, $crop) {
            if (!$crop)
                return null; 


            $aspect_ratio = $orig_w / $orig_h;
            $new_w = $dest_w;
            $new_h = $dest_h;

            if (!$new_w) {
                $new_w = intval($new_h * $aspect_ratio);
            }

            if (!$new_h) {
                $new_h = intval($new_w / $aspect_ratio);
            }

            $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

            $crop_w = round($new_w / $size_ratio);
            $crop_h = round($new_h / $size_ratio);

            $s_x = floor(( $orig_w - $crop_w ) / 2);
            $s_y = floor(( $orig_h - $crop_h ) / 2);

            return array(0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h);
        }

    }
}
if (!function_exists('apffw_aq_resize')) {
    function apffw_aq_resize($url, $width = null, $height = null, $crop = null, $single = true, $upscale = false) {
        $aq_resize = APFFW_Resize::getInstance();
        return $aq_resize->process($url, $width, $height, $crop, $single, $upscale);
    }

}