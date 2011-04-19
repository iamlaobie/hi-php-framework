<?php
class Hi_Tool_File
{
    public static function download ($file)
    {
        $fileName = substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1);
        header("Content-type: application/zip");
        header("Accept-Ranges:bytes");
        header('Content-Disposition: attachment; filename=' . $fileName);
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
        $fp = fopen($file, 'rb');
        while (! feof($fp)) {
            echo fread($fp, 1024);
        }
        fclose($fp);
    }
    public static function downloadCsv ($fileName, $header, $data)
    {
        $fileName = iconv('UTF-8', 'GBK', $fileName);
        header("Accept-Ranges: bytes");
        header('Content-Disposition: attachment; filename=' . $fileName);
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
        $fields = array_keys($header);
        $titles = array_values($header);
        $t = join(',', $titles) . "\n";
        echo iconv('UTF-8', 'GBK', $t);
        foreach ($data as $val) {
            $row = '';
            foreach ($fields as $field) {
                $row .= trim($val[$field]) . ',';
            }
            $t = substr($row, 0, - 1);
            $t = trim($t) . "\n";
            echo iconv('UTF-8', 'GBK', $t);
        }
    }
    static public function save ($file, $content)
    {
        $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
        $path = substr($file, 0, strrpos($file, DIRECTORY_SEPARATOR));
        Hi_Tool_Dir::check($path);
        file_put_contents($file, $content);
        return true;
    }
    static public function getExt ($file, $default = null)
    {
        if (preg_match('/\.(\w+)$/i', $file, $regs)) {
            return strtolower($regs[1]);
        }
        return $default;
    }
    /**
     * 上传一个文件
     * 
     * @param $file 对应html中的一个html file控件
     * @param $path 存储路径
     * @param $seed 分级路径的种子，种子将被MD5后，从左至右每两位作为一级路径
     * @param $hashLevel 文件路径层级，将
     * @return unknown_type
     */
    static public function upload ($file, $path, $allowType = '', $rename = true, 
    $level = 0, $seed = null)
    {
        $ext = strtolower(self::getExt($file['name']));
        if (! empty($allowType) && strstr($allowType, $ext) === false) {
            throw new Hi_Tool_Exception("不上传允许的文件格式[{$ext}]");
        }
        if ($rename) {
            $name = time() . '_' . rand(10000, 99999) . '.' . $ext;
        } else {
            $name = $file['name'];
        }
        if ($seed) {
            $name = $seed . '_' . $name;
        }
        $path = Hi_Tool_Dir::standard($path);
        $level = intval($level);
        if ($level != 0) {
            $path = Hi_Tool_Dir::createByHash($path, 2, $seed);
        } else {
            Hi_Tool_Dir::create($path);
        }
        if (substr($path, - 1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
        $fp = $path . $name;
        copy($file['tmp_name'], $fp);
        return $fp;
    }
    /**
     * 将文件的磁盘路径转换为相对url
     * 
     * @param $file
     * @return string
     */
    static public function getFileUrl ($file)
    {
        $dc = $_SERVER['DOCUMENT_ROOT'];
        $file = str_replace(DOC_ROOT, '', $file);
        $file = str_replace('\\', '/', $file);
        $file = str_replace('//', '/', $file);
        if (substr($file, 0, 1) != '/') {
            $file = '/' . $file;
        }
        return '/' . $file;
    }
    public static function getUrlFile ($url)
    {
        if (preg_match('/^http:\/\/.+?\/([^\?]+)/i', $url, $regs)) {
            $url = $regs[1];
        }
        $file = DOC_ROOT . $url;
        $file = Hi_Tool_Dir::standard($file);
        return $file;
    }
}