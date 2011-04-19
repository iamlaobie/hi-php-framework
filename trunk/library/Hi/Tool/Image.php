<?php
class Hi_Tool_Image
{
    /**
     * 将图片文件$img等比例缩放
     * 
     * @param $img 图片路径
     * @param $percent 大于0小于1的缩放比例
     * @return src
     */
    static public function resize ($img, $percent)
    {
        if (! is_file($img)) {
            throw new Hi_Tool_Exception("{$img} is not a file");
        }
        $ext = Hi_Tool_File::getExt($img);
        if (! in_array($ext, array('jpg', 'gif', 'png', 'bmp', 'jpeg'))) {
            throw new Hi_Tool_Exception(
            "Can not support the image type '{$ext}'");
        }
        if ($ext == 'jpg') {
            $ext = 'jpeg';
        }
        $func = "imagecreatefrom{$ext}";
        list ($width, $height) = getimagesize($img);
        $nw = $width * $percent;
        $nh = $height * $percent;
        $newImg = imagecreatetruecolor($nw, $nh);
        $rawImg = $func($img);
        imagecopyresized($newImg, $rawImg, 0, 0, 0, 0, $nw, $nh, $width, 
        $height);
        imagedestroy($rawImg);
        return $newImg;
    }
    static public function crop ($file, $left, $top, $width, $height, $zoom)
    {
        $image = imagecreatetruecolor($width, $height);
        $src = imagecreatefromjpeg($file);
        $res = imagecopyresampled($image, $src, 0, 0, $left / $zoom, $top /
         $zoom, $width, $height, $width / $zoom, $height / $zoom);
    }
    /**
     * 将图片缩放到指定宽和高，如果目标尺寸与当前图片的尺寸不成比例，自动填充背景
     * 
     * @param $img
     * @param $width
     * @param $height
     * @return resource
     */
    static public function resizeTo ($img, $width = null, $height = null, 
    $override = false)
    {
        $size = getimagesize($img);
        $size['width'] = $size[0];
        $size['height'] = $size[1];
        $widthRatio = $heightRatio = 1;
        if ($width) {
            $widthRatio = $width / $size['width'];
        } else {
            $width = $size['width'];
        }
        if ($height) {
            $heightRatio = $height / $size['height'];
        } else {
            $height = $size['height'];
        }
        $ratio = min($widthRatio, $heightRatio);
        $resized = self::resize($img, $ratio);
        if ($override) {
            imagejpeg($resized, $img);
        }
        return $resized;
    }
}