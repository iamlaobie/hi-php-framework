<?php
/**
 * 目录操纵工具
 *
 * @package Hi_Tool_Dir
 * @author 刘军<tiandiou@163.com>
 * @since 2008-07-27
 */
class Hi_Tool_Dir
{
    public static function create ($directory)
    {
        if (! is_string($directory)) {
            return false;
        }
        $directory = self::standard($directory);
        if (Hi_Tool_Dir::is($directory)) {
            return true;
        }
        if (preg_match('/^win/i', PHP_OS)) {
            $pat = '/^[a-z]:\\' . DIRECTORY_SEPARATOR . '/i';
        } else {
            $pat = '/^\\' . DIRECTORY_SEPARATOR . '/';
        }
        //如果不是绝对路径，取当前请求的文件所在路径问父路径
        if (! preg_match($pat, $directory)) {
            $fileName = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, 
            $_SERVER['SCRIPT_FILENAME']);
            $pd = substr($fileName, 0, strrpos($fileName, DIRECTORY_SEPARATOR));
            $directory = $pd . DIRECTORY_SEPARATOR . $directory;
        }
        $arrDir = explode(DIRECTORY_SEPARATOR, $directory);
        $created = '';
        for ($i = 0; $i < sizeof($arrDir); $i ++) {
            if (preg_match('/^win/i', PHP_OS)) {
                $created .= $arrDir[$i] . DIRECTORY_SEPARATOR;
            } else {
                $created .= DIRECTORY_SEPARATOR . $arrDir[$i];
            }
            if (Hi_Tool_Dir::is($created)) {
                continue;
            }
            @mkdir($created);
        }
        return true;
    }
    /**
     * 检查目录是否存在，如果目录不存在且$autoCreate参数为true，创建该目录
     *
     * @param string $directory
     * @param bool $autoCreate
     * @return mix
     */
    public static function check ($directory, $autoCreate = true)
    {
        if (is_dir($directory)) {
            return true;
        }
        if (! $autoCreate) {
            return false;
        }
        return Hi_Tool_Dir::create($directory);
    }
    public static function is ($directory)
    {
        return is_dir($directory);
    }
    public static function writable ($directory)
    {
        if (! Hi_Tool_Dir::is($directory)) {
            return false;
        }
        return is_writable($directory);
    }
    public static function isReadable ($directory)
    {
        if (! Hi_Tool_Dir::isDirectory($directory)) {
            return false;
        }
        return is_readable($directory);
    }
    public static function setDirMod ($directory, $mod = '0755')
    {
        if (! Hi_Tool_Dir::isDirectory($directory)) {
            return false;
        }
        return @ chmod($directory, $mod);
    }
    public static function isAbsolute ($directory)
    {
        $directory = self::standardDirectory($directory);
        if (preg_match('/^win/i', PHP_OS)) {
            $pat = '/^[a-z]:\\' . DIRECTORY_SEPARATOR . '/i';
        } else {
            $pat = '/^\\' . DIRECTORY_SEPARATOR . '/';
        }
        if (preg_match($pat, $directory)) {
            return true;
        }
        return false;
    }
    public static function standard ($directory)
    {
        $directory = str_replace('/', DS, $directory);
        $directory = str_replace("\\", DS, $directory);
        $directory = preg_replace('/\\\\' . DS . '\\\\' . DS . '/', DS, 
        $directory);
        return $directory;
    }
    /**
     * 读取文件夹下所有的文件装入数组$files中
     * 如果$onlyFile == false,也将子目录装入$files中
     *
     * @param string $directory
     * @param array $files
     * @param bool $onlyFile
     * @return array
     */
    public static function read ($directory, $files = array(), $onlyFile = true)
    {
        $directory = self::standard($directory);
        if (! self::is($directory)) {
            return $files;
        }
        $rp = opendir($directory);
        while (($file = readdir($rp)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (substr($directory, - 1) != DIRECTORY_SEPARATOR) {
                $directory .= DIRECTORY_SEPARATOR;
            }
            $file = $directory . $file;
            if (self::is($file)) {
                if (! $onlyFile) {
                    $files[] = $file;
                }
                $files = self::read($file, $files, $onlyFile);
            } else {
                $files[] = $file;
            }
        }
        closedir($rp);
        return $files;
    }
    /**
     * 警告：该方法将移除目录所有文件
     * 移除目录，相当于linux上的 rm -rf，DOS的deltree
     *
     * @param string $directory
     * @return bool;
     */
    public static function rm ($directory)
    {
        $directory = self::standard($directory);
        $files = self::read($directory, array(), false);
        //翻转数组，使文件在前，路径在后
        $files = array_reverse($files);
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } else {
                rmdir($file);
            }
        }
        rmdir($directory);
        return true;
    }
    /**
     * 递归地将目录$src中的所有内容复制到$dest中
     *
     * @param string $src
     * @param string $dest
     * @return book
     */
    public static function copy ($src, $dest)
    {
        $src = self::standard($src);
        $dest = self::standard($dest);
        if (! is_dir($src)) {
            return false;
        }
        if (! is_dir($dest)) {
            self::create($dest);
        }
        $files = self::read($src, array(), false);
        foreach ($files as $file) {
            $tsrc = str_replace('\\', '\/', $src);
            $tfile = str_replace('\\', '/', $file);
            $dfile = preg_replace("/^{$tsrc}/", '', $tfile);
            $dfile = str_replace('/', '\\', $dfile);
            $dfile = $dest . $dfile;
            if (is_file($file)) {
                copy($file, $dfile);
            } else {
                self::create($dfile);
            }
        }
        return true;
    }
    static public function createByHash ($path, $level = 2, $seed = null, 
    $hash = true)
    {
        if ($seed === null) {
            $seed = time() . rand(10000, 99999);
        }
        if ($hash) {
            $seed = md5($seed);
        }
        $d = '';
        for ($i = 0; $i < $level; $i ++) {
            $start = $i * 2;
            $d .= substr($seed, $start, 2) . DIRECTORY_SEPARATOR;
        }
        $path = self::standard($path);
        $dir = $path . DIRECTORY_SEPARATOR . $d;
        self::create($dir);
        return $dir . DIRECTORY_SEPARATOR;
    }
    public function createById ($id, $length, $level = 2, $path = null)
    {
        if (strlen($id) < $length) {
            $id = str_pad($id, $length, '0', STR_PAD_LEFT);
        }
        if (empty($path)) {
            $path = $_SERVER['DOCUMENT_ROOT'];
        }
        return self::createByHash($path, $level, $id, false);
    }
}