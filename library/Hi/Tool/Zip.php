<?php
class Hi_Tool_Zip
{
    /**
     * 创建一个压缩包
     *
     * @param string $dest 压缩包的全路径
     * @param string $src  要压缩的路径
     */
    public static function create ($zipFile, $src)
    {
        if (! is_dir($src)) {
            return false;
        }
        $src = Hi_Tool_Dir::standard($src);
        
        $zip = new ZipArchive();
        $res = $zip->open($zipFile, ZipArchive::CREATE);
        if ($res !== true) {
            return false;
        }
        $files = Hi_Tool_Dir::read($src);
        foreach ($files as $file) {
            $localFile = str_replace($src, '', $file);
            $localFile = preg_replace('/^[\/\\\\]/', '', $localFile);
            $zip->addFile($file, $localFile);
        }
        $zip->close();
        return true;
    }
    
    /**
     * 将zipFile抽取到路径$to，如果$to不存在将自动创建
     * 
     * @param unknown_type $zipFile
     * @param unknown_type $to
     */
    public function extract($zipFile, $to)
    {
        Hi_Tool_Dir::check($to);
        $to = Hi_Tool_Dir::standard($to);
        $zip = new ZipArchive();
        $res = $zip->open($zipFile);
        if ($res !== true) {
            return false;
        }
        $zip->extractTo($to);
    }
}