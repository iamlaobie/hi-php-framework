<?php
class Hi_Cache_File implements Hi_Cache_Interface
{
    /**
     * 缓存的生存期
     * 
     * @var int
     */
    protected $_lifetime = 0;
    /**
     * 缓存位置
     * 
     * @var string
     */
    protected $_dir;
    /**
     * 是否对缓存id进行hash
     * 
     * @var bool
     */
    protected $_isIdEncode = true;
    /**
     * 缓存文件路径深度
     * 
     * @var int
     */
    protected $_dirLevel = 2;
    /**
     * 构造函数
     * 
     * @param array $options 
     * @throws Hi_Cache_Exception
     */
    public function __construct ($options = array())
    {
        if (! is_array($options)) {
            throw new Hi_Cache_Exception("The params must be a array");
        }
        $this->setOptions($options);
    }
    /**
     * 配置可选参数
     * 选项：
     * dir：缓存位置
     * lifetime：生存周期
     * idEncode：是否对缓存文件名进行hash
     * dirLeve：缓存路径深度
     * 
     * @param array $options
     */
    public function setOptions ($options = array())
    {
        if (! isset($options['dir'])) {
            $options['dir'] = '/tmp';
        }
        $this->setCacheDir($options['dir']);
        if (isset($options['lifetime']) && intval($options['lifetime'])) {
            $this->_lifetime = intval($options['lifetime']);
        }
        if (isset($options['idEncode'])) {
            $this->_isIdEncode = (bool) $options['idEncode'];
        }
        if (isset($options['dirLevel'])) {
            $this->_dirLevel = intval($options['dirLevel']);
        }
        return true;
    }
    /**
     * (non-PHPdoc)
     * @see Hi_Cache_Interface::save()
     */
    public function set ($id, $content, $lifetime = null)
    {
        if (is_array($content) || is_object($content)) {
            $content = serialize($content);
        }
        if (! is_string($content)) {
            throw new Hi_Cache_Exception("Content is not a string");
        }
        $filePath = $this->getFilePath($id);
        $fileDir = substr($filePath, 0, strrpos($filePath, DIRECTORY_SEPARATOR));
        Hi_Tool_Dir::check($fileDir, true);
        $this->_write($filePath, $content);
        if ($lifetime === null) {
            $lifetime = $this->_lifetime;
        }
        if (intval($lifetime) !== 0) {
            $expireTime = $lifetime + time();
            $this->_setMeta($filePath, strval($expireTime));
        }
        return $filePath;
    }

    /**
     * 判断缓存是否过期
     * 
     * @param string $id
     */
    public function expired ($id)
    {
        $file = $this->getFilePath($id);
        if (! file_exists($file)) {
            return true;
        }
        $expireTime = intval($this->_getMeta($file));
        if ($expireTime && intval($expireTime) < time()) {
            return true;
        }
        return false;
    }
    /**
     * (non-PHPdoc)
     * @see Hi_Cache_Interface::get()
     */
    public function get ($id, $unserialize = false)
    {
        if ($this->expired($id)) {
            return null;
        }
        $filePath = $this->getFilePath($id);
        $c = $this->_read($filePath);
        if ($unserialize) {
            $c = unserialize($c);
        }
        return $c;
    }
    /**
     * 判断缓存是否存在
     * 
     */
    public function exist ($id)
    {
        return (bool) $this->expired($id);
    }
    /**
     * (non-PHPdoc)
     * @see Hi_Cache_Interface::delete()
     */
    public function delete ($id)
    {
        $filePath = $this->getFilePath($id);
        if (file_exists($filePath)) {
            @ unlink($filePath);
        }
        $metaFile = $filePath . '----meta';
        if (file_exists($metaFile)) {
            @ unlink($metaFile);
        }
        return true;
    }
    /**
     * 设置缓存路径
     * 
     * @param string $cacheDir
     * @throws Hi_Cache_Exception
     */
    public function setCacheDir ($cacheDir)
    {
        if (Hi_Tool_Dir::is($cacheDir) && ! Hi_Tool_Dir::writable($cacheDir)) {
            throw new Hi_Cache_Exception('Cache path must be a writable');
        }
        if (substr($cacheDir, - 1) != DIRECTORY_SEPARATOR) {
            $cacheDir = $cacheDir . DIRECTORY_SEPARATOR;
        }
        $this->_dir = $cacheDir;
        return true;
    }
    /**
     * 取缓存id的md5 hash
     * 
     * @param string $id
     */
    public function idEncode ($id)
    {
        if (! $this->_isIdEncode) {
            return $id;
        }
        return md5($id);
    }
    /**
     * 取缓存id对应的缓存的子路径（非绝对路径）
     * 
     * @param string $id
     */
    public function getSubDir ($id)
    {
        $hash = md5($id);
        $subDir = '';
        for ($i = 0; $i < $this->_dirLevel; $i ++) {
            $subDir .= substr($hash, $i * 2, 2) . DIRECTORY_SEPARATOR;
        }
        return $subDir;
    }
    /**
     * 取id对应的缓存文件的绝对路径
     * 
     * @param string $id
     */
    public function getFilePath ($id)
    {
        $subDir = $this->getSubDir($id);
        $id = $this->idEncode($id);
        return $this->_dir . $subDir . $id;
    }
    /**
     * 取缓存文件的元数据
     * 
     * @param unknown_type $file
     */
    protected function _getMeta ($file)
    {
        $metaFile = $file . '----meta';
        return $this->_read($metaFile);
    }
    /**
     * 设置缓存文件的元数据
     * 
     * @param unknown_type $file
     * @param unknown_type $meta
     */
    protected function _setMeta ($file, $meta)
    {
        $metaFile = $file . '----meta';
        return $this->_write($metaFile, $meta);
    }
    protected function _write ($file, $content)
    {
        $fp = fopen($file, 'wb');
        flock($fp, LOCK_EX);
        @ fwrite($fp, $content);
        fclose($fp);
        return true;
    }
    protected function _read ($file)
    {
        if (! is_file($file)) {
            return null;
        }
        $fp = fopen($file, 'rb');
        flock($fp, LOCK_SH);
        $content = '';
        while (! feof($fp)) {
            $content .= fread($fp, 2048);
        }
        fclose($fp);
        return $content;
    }
}
?>