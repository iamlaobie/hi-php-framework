<?php
class Hi_Tool_Chinese
{
    public static function wordsCount ($content)
    {
        $content = str_replace('&nbsp;', '', $content);
        $content = preg_replace('/[\s\n\r 　]/', '', strip_tags($content));
        return mb_strlen($content, 'UTF-8');
    }
    public static function typeset ($content)
    {
        $content = trim($content);
        $content = preg_replace('/<br\s*\/?>|&nbsp|　|\r/ims', '', $content);
        $content = preg_replace('/\n+/', "\n", $content);
        $content = preg_replace('/\n$/', '', $content);
        $content = preg_replace('/\n/', '</p><p>', $content);
        $content = '<p>' . $content . '</p>';
        return $content;
    }
    public static function cutListField ($array, $field, $length, 
    $newField = null, $charset = 'UTF-8')
    {
        if (empty($newField)) {
            $newField = $field;
        }
        for ($i = 0, $cnt = sizeof($array); $i < $cnt; $i ++) {
            if (mb_strlen($array[$i][$field], $charset) > $length) {
                $array[$i][$newField] = self::cut($array[$i][$field], $length, 
                true, $charset);
            } else {
                $array[$i][$newField] = '';
            }
        }
        return $array;
    }
    static public function cut ($str, $length, $strip = true, $charset = 'UTF-8')
    {
        if ($strip) {
            $str = strip_tags($str);
        }
        if (mb_strlen($str, $charset) > $length) {
            $str = mb_substr($str, 0, $length - 2, $charset) . '...';
        }
        return $str;
    }
    public static function standard ($content, $allowHtml = false)
    {
        if (! $allowHtml) {
            $content = strip_tags($content);
        }
        $content = str_replace('', '', $content);
        $content = preg_replace('/[\r\t\v\f ]/ms', '', $content); //删除空白符，包括半角空格，全角空格
        $content = preg_replace('/\n+/', "\n", $content); //多个换行合并为一个
        $content = preg_replace('/\n+$/', '', $content); //删除每行最后一个换行
        $content = str_replace("\n", "\r\n", $content);
        return $content;
    }
    /**
     * 递归地转换$content的编码
     *
     * 如果$inCharset == 'UTF-8'，将$content的内容的编码转换为GBK
     * 如果$inCharset == 'GBK'，将$content的内容的编码转换为UTF-8
     *
     * @param mix $content
     * @param string $inCharset
     * @return mix
     */
    public static function encode ($content, $toCharset = 'UTF-8')
    {
        if (! is_array($content) && ! is_string($content)) {
            return $content;
        }

        if (is_string($content) ) {
            $inCharset = self::getCharset($content);
            if(strtolower($inCharset) == strtolower($toCharset)){
                return $content;
            }else{
                return iconv($inCharset, $toCharset, $content);
            }
        }
        
        $tContent = array();
        foreach ($content as $key => $value) {
            //既不是数组也不是字符串，按原样返回
            if (! is_array($value) && ! is_string($value)) {
                $tContent[$key] = $value;
                continue;
            }
            
            $tContent[$key] = self::encode($value, $toCharset);
        }
        return $tContent;
    }
    
    public function getCharset ($text)
    {
        return mb_detect_encoding($text, 'GBK,UTF-8');
    }
}
